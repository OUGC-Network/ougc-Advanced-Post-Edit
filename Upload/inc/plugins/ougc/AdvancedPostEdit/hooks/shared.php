<?php

/***************************************************************************
 *
 *    ougc Advanced Post Edit plugin (/inc/plugins/ougc/AdvancedPostEdit/hooks/shared.php)
 *    Author: Omar Gonzalez
 *    Copyright: © 2015 Omar Gonzalez
 *
 *    Website: https://ougc.network
 *
 *    Allow administrators to edit additional post data.
 *
 ***************************************************************************
 ****************************************************************************
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 ****************************************************************************/

declare(strict_types=1);

namespace ougc\AdvancedPostEdit\Hooks\Shared;

use MyBB;
use PostDataHandler;
use DateTimeImmutable;

use function ougc\AdvancedPostEdit\Core\getSetting;
use function ougc\AdvancedPostEdit\Core\languageLoad;
use function ougc\AdvancedPostEdit\Core\recountUserPosts;
use function ougc\AdvancedPostEdit\Core\recountUserThreads;

function datahandler_post_validate_post(PostDataHandler &$dataHandler): PostDataHandler
{
    if (!is_moderator($dataHandler->data['fid'], 'caneditposts') || !is_member(getSetting('groups'))) {
        return $dataHandler;
    }

    global $mybb, $db;

    $postID = (int)$dataHandler->data['pid'];

    $postData = get_post($postID);

    $editOptions = $mybb->get_input('ougc_adminpostedit', MyBB::INPUT_ARRAY);

    if (!empty($editOptions['date']) && !empty($editOptions['time'])) {
        $timeSpanStartStamp = (new DateTimeImmutable("{$editOptions['date']} {$editOptions['time']}"))->getTimestamp();

        if (!$timeSpanStartStamp || $timeSpanStartStamp > TIME_NOW) {
            global $lang;

            languageLoad(true);

            $dataHandler->set_error($lang->ougcAdvancedPostDataHandlerInvalidDateTime);
        } else {
            $dataHandler->data['ougcAdvancedPostEditDateTime'] = $timeSpanStartStamp;
        }
    }

    $editOptions['username'] = trim($editOptions['username']);

    if (!empty($editOptions['username'])) {
        $userData = get_user_by_username($editOptions['username'], ['fields' => ['username']]);

        if (empty($userData['uid']) && empty($editOptions['forceusername'])) {
            global $lang;

            languageLoad(true);

            $dataHandler->set_error($lang->ougcAdvancedPostDataHandlerInvalidUser);
        } else {
            $dataHandler->data['ougcAdvancedPostEditUserID'] = $userData['uid'] ?? 0;

            $dataHandler->data['ougcAdvancedPostEditUserName'] = $userData['username'] ?? $editOptions['username'];
        }
    }

    if (isset($editOptions['ipaddress']) &&
        my_inet_ntop($db->unescape_binary($postData['ipaddress'])) !== $editOptions['ipaddress']

    ) {
        if (!filter_var($editOptions['ipaddress'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) &&
            !filter_var($editOptions['ipaddress'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            global $lang;

            languageLoad(true);

            $dataHandler->set_error($lang->ougcAdvancedPostDataHandlerInvalidIpAddress);
        } else {
            $dataHandler->data['ougcAdvancedPostEditIpAddress'] = $editOptions['ipaddress'];
        }
    }

    if (!empty($editOptions['reset'])) {
        $dataHandler->data['ougcAdvancedPostEditResetEditData'] = true;
    }

    return $dataHandler;
}

function datahandler_post_update(PostDataHandler &$dataHandler): PostDataHandler
{
    if (!is_moderator($dataHandler->data['fid'], 'caneditposts') || !is_member(getSetting('groups'))) {
        return $dataHandler;
    }

    global $db;

    if (isset($dataHandler->data['ougcAdvancedPostEditDateTime'])) {
        $dataHandler->post_update_data['dateline'] = $dataHandler->data['ougcAdvancedPostEditDateTime'];
    }

    if (isset($dataHandler->data['ougcAdvancedPostEditUserID'])) {
        $dataHandler->post_update_data['uid'] = (int)$dataHandler->data['ougcAdvancedPostEditUserID'];

        $dataHandler->post_update_data['username'] = $db->escape_string(
            $dataHandler->data['ougcAdvancedPostEditUserName']
        );
    }

    if (isset($dataHandler->data['ougcAdvancedPostEditIpAddress'])) {
        $dataHandler->post_update_data['ipaddress'] = $db->escape_binary(
            my_inet_pton($dataHandler->data['ougcAdvancedPostEditIpAddress'])
        );
    }

    if (!empty($dataHandler->data['ougcAdvancedPostEditResetEditData'])) {
        $dataHandler->post_update_data['edituid'] = $dataHandler->post_update_data['edittime'] = 0;
    }

    return $dataHandler;
}

function datahandler_post_update_end(PostDataHandler &$dataHandler): PostDataHandler
{
    if (!isset($dataHandler->post_update_data['dateline']) && isset($dataHandler->data['ougcAdvancedPostEditUserID'])) {
        return $dataHandler;
    }

    global $db;

    $postID = (int)$dataHandler->data['pid'];

    $threadID = (int)$dataHandler->data['tid'];

    $query = $db->simple_select(
        'posts',
        'pid',
        "tid='{$threadID}'",
        ['limit' => 1, 'limit_start' => 0, 'order_by' => 'dateline, pid']
    );

    $firstPostID = (int)$db->fetch_field($query, 'pid');

    $threadUpdateData = [];

    if ($firstPostID === $postID) {
        if (isset($dataHandler->data['ougcAdvancedPostEditUserID'])) {
            $threadUpdateData['uid'] = $dataHandler->post_update_data['uid'];

            $threadUpdateData['username'] = $dataHandler->post_update_data['username'];
        }

        if (isset($dataHandler->post_update_data['dateline'])) {
            $threadUpdateData['dateline'] = $dataHandler->post_update_data['dateline'];
        }

        if (empty($dataHandler->return_values['firstpost'])) {
            $query = $db->simple_select(
                'posts',
                'uid',
                "tid='{$threadID}'",
                ['limit' => 1, 'limit_start' => 1, 'order_by' => 'dateline, pid']
            );

            $oldFirstPostUserID = (int)$db->fetch_field($query, 'uid');

            recountUserThreads($oldFirstPostUserID);

            recountUserPosts($oldFirstPostUserID);
        }
    }

    if (!empty($threadUpdateData)) {
        $db->update_query('threads', $threadUpdateData, "tid='{$threadID}'");
    }

    $oldUserID = (int)$dataHandler->data['uid'];

    if (isset($dataHandler->post_update_data['dateline']) && isset($dataHandler->post_update_data['uid'])) {
        $newUserID = (int)$dataHandler->post_update_data['uid'];

        $query = $db->simple_select(
            'posts',
            'dateline',
            "uid='{$newUserID}' AND visible=1",
            ['limit' => 1, 'order_by' => 'dateline', 'order_dir' => 'desc']
        );

        $db->update_query(
            'users',
            ['lastpost' => (int)$db->fetch_field($query, 'dateline')],
            "uid='{$newUserID}'"
        );

        if ($newUserID !== $oldUserID) {
            $query = $db->simple_select(
                'posts',
                'dateline',
                "uid='{$oldUserID}' AND visible=1",
                ['limit' => 1, 'order_by' => 'dateline', 'order_dir' => 'desc']
            );

            $db->update_query(
                'users',
                ['lastpost' => (int)$db->fetch_field($query, 'dateline')],
                "uid='{$oldUserID}'"
            );
        }
    }

    if (isset($newUserID)) {
        recountUserThreads($newUserID);

        recountUserPosts($newUserID);
    }

    if (!isset($newUserID) || $newUserID !== $oldUserID) {
        recountUserThreads($oldUserID);

        recountUserPosts($oldUserID);
    }

    update_last_post($dataHandler->data['tid']);

    update_forum_lastpost($dataHandler->data['fid']);

    return $dataHandler;
}