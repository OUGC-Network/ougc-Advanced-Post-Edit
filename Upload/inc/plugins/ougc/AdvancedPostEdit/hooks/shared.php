<?php

/***************************************************************************
 *
 *    ougc Admin Post Edit plugin (/inc/plugins/ougc/AdvancedPostEdit/hooks/shared.php)
 *    Author: Omar Gonzalez
 *    Copyright: © 2015 Omar Gonzalez
 *
 *    Website: https://ougc.network
 *
 *    Allows administrators to edit additional post data.
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
use function ougc\AdvancedPostEdit\Core\recountUserPosts;
use function ougc\AdvancedPostEdit\Core\recountUserThreads;

function datahandler_post_update(PostDataHandler &$dataHandler): PostDataHandler
{
    if (!is_moderator($dataHandler->data['fid'], 'caneditposts') || !is_member(getSetting('groups'))) {
        return $dataHandler;
    }

    global $mybb, $db;
    global $ougcAdvancedPostEditDateline, $ougcAdvancedPostEditUser;

    $postID = (int)$dataHandler->data['pid'];

    $postData = get_post($postID);

    $editOptions = $mybb->get_input('ougc_adminpostedit', MyBB::INPUT_ARRAY);

    $editOptions['username'] = trim($editOptions['username']);

    if (!empty($editOptions['date']) && !empty($editOptions['time'])) {
        $timeSpanStartStamp = (new DateTimeImmutable("{$editOptions['date']} {$editOptions['time']}"))->getTimestamp();

        if ($timeSpanStartStamp && $timeSpanStartStamp <= TIME_NOW) {
            $dataHandler->post_update_data['dateline'] = $timeSpanStartStamp;

            $ougcAdvancedPostEditDateline = true;
        }
    }

    if (!empty($editOptions['username'])) {
        $userData = get_user_by_username($editOptions['username'], ['fields' => ['username']]);

        $userID = (int)$userData['uid'];

        if (!empty($userData['uid']) && $userID !== (int)$postData['uid'] || !empty($editOptions['forceusername'])) {
            $dataHandler->post_update_data['uid'] = $userID;

            $dataHandler->post_update_data['username'] = $db->escape_string(
                $userData['username'] ?? $editOptions['username']
            );

            $ougcAdvancedPostEditUser = true;
        }
    }

    if (isset($editOptions['ipaddress']) &&
        my_inet_ntop($db->unescape_binary($postData['ipaddress'])) !== $editOptions['ipaddress'] &&
        (
            filter_var($editOptions['ipaddress'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ||
            filter_var($editOptions['ipaddress'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
        )) {
        $dataHandler->post_update_data['ipaddress'] = $db->escape_binary(my_inet_pton($editOptions['ipaddress']));
    }

    if (!empty($editOptions['reset'])) {
        $dataHandler->post_update_data['edituid'] = $dataHandler->post_update_data['edittime'] = 0;
    }

    return $dataHandler;
}

function datahandler_post_update_end(PostDataHandler &$dataHandler): PostDataHandler
{
    global $ougcAdvancedPostEditDateline, $ougcAdvancedPostEditUser;

    if (empty($ougcAdvancedPostEditDateline) && empty($ougcAdvancedPostEditUser)) {
        return $dataHandler;
    }

    global $db;

    $postID = (int)$dataHandler->data['pid'];

    $threadID = (int)$dataHandler->data['tid'];

    $newUserID = (int)$dataHandler->post_update_data['uid'];

    $oldUserID = (int)$dataHandler->data['uid'];

    $query = $db->simple_select(
        'posts',
        'pid',
        "tid='{$threadID}'",
        ['limit' => 1, 'limit_start' => 0, 'order_by' => 'dateline, pid']
    );

    $firstPostID = (int)$db->fetch_field($query, 'pid');

    $threadUpdateData = [];

    if ($firstPostID === $postID && !empty($ougcAdvancedPostEditUser)) {
        $threadUpdateData['uid'] = $dataHandler->post_update_data['uid'];

        $threadUpdateData['username'] = $dataHandler->post_update_data['username'];
    }

    if ($firstPostID === $postID && !empty($ougcAdvancedPostEditDateline)) {
        $threadUpdateData['dateline'] = $dataHandler->post_update_data['dateline'];
    }

    if (!empty($threadUpdateData)) {
        $db->update_query('threads', $threadUpdateData, "tid='{$threadID}'");
    }

    if (!empty($ougcAdvancedPostEditDateline)) {
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

    recountUserThreads($newUserID);

    recountUserPosts($newUserID);

    if ($newUserID !== $oldUserID) {
        recountUserThreads($oldUserID);

        recountUserPosts($oldUserID);
    }

    update_last_post($dataHandler->data['tid']);

    update_forum_lastpost($dataHandler->data['fid']);

    return $dataHandler;
}