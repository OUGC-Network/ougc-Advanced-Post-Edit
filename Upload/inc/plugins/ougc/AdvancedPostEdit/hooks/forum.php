<?php

/***************************************************************************
 *
 *    ougc Admin Post Edit plugin (/inc/plugins/ougc/AdvancedPostEdit/hooks/forum.php)
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

namespace ougc\AdvancedPostEdit\Hooks\Forum;

use MyBB;

use function ougc\AdvancedPostEdit\Core\getSetting;
use function ougc\AdvancedPostEdit\Core\getTemplate;
use function ougc\AdvancedPostEdit\Core\languageLoad;

function editpost_end(): void
{
    global $fid;
    global $ougc_adminpostedit;

    $ougc_adminpostedit = '';

    if (!is_moderator($fid, 'caneditposts') || !is_member(getSetting('groups'))) {
        return;
    }

    global $db, $lang;
    global $pid;

    languageLoad();

    $postData = get_post($pid);

    $postDate = gmdate('Y-m-d', (int)$postData['dateline']);//yyyy-mm-dd

    $postTime = gmdate('H:i:s', (int)$postData['dateline']);//HH:mm:ss

    $userName = '';

    $ipAddress = my_inet_ntop($db->unescape_binary($postData['ipaddress']));

    global $mybb;

    $postOptions = $mybb->get_input('ougc_adminpostedit', MyBB::INPUT_ARRAY);

    if ($mybb->request_method === 'post') {
        $postDate = $postOptions['date'];

        $postTime = $postOptions['time'];

        $postOptions['username'] = trim($postOptions['username']);

        $userName = htmlspecialchars_uni($postOptions['username']);

        $ipAddress = $postOptions['ipaddress'];
    }

    $postDate = htmlspecialchars_uni($postDate);

    $postTime = htmlspecialchars_uni($postTime);

    $ipAddress = htmlspecialchars_uni($ipAddress);

    $silentElementChecked = $resetElementChecked = $forceUserChangeElementChecked = '';

    if (!empty($postOptions['silent'])) {
        $silentElementChecked = 'checked="checked"';
    }

    if (!empty($postOptions['reset'])) {
        $resetElementChecked = 'checked="checked"';
    }

    if (!empty($postOptions['forceusername'])) {
        $forceUserChangeElementChecked = 'checked="checked"';
    }

    $ougc_adminpostedit = eval(getTemplate());
}

function editpost_do_editpost_start(): void
{
    global $mybb, $fid;

    if (!is_moderator($fid, 'caneditposts') || !is_member(getSetting('groups'))) {
        return;
    }

    $postOptions = $mybb->get_input('ougc_adminpostedit', MyBB::INPUT_ARRAY);

    if (!empty($postOptions['silent'])) {
        $mybb->settings['showeditedbyadmin'] = 0;
    }
}