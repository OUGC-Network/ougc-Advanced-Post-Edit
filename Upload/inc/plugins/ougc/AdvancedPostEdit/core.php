<?php

/***************************************************************************
 *
 *    ougc Admin Post Edit plugin (/inc/plugins/ougc/AdvancedPostEdit/core.php)
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

namespace ougc\AdvancedPostEdit\Core;

use const ougc\AdvancedPostEdit\SETTINGS;
use const ougc\AdvancedPostEdit\DEBUG;
use const ougc\AdvancedPostEdit\ROOT;

function hooksAdd(string $namespace): void
{
    global $plugins;

    $namespaceLowercase = strtolower($namespace);
    $definedUserFunctions = get_defined_functions()['user'];

    foreach ($definedUserFunctions as $callable) {
        $namespaceWithPrefixLength = strlen($namespaceLowercase) + 1;

        if (substr($callable, 0, $namespaceWithPrefixLength) == $namespaceLowercase . '\\') {
            $hookName = substr_replace($callable, '', 0, $namespaceWithPrefixLength);

            $priority = substr($callable, -2);

            $isNegative = substr($hookName, -3, 1) === '_';

            if (is_numeric(substr($hookName, -2))) {
                $hookName = substr($hookName, 0, -2);
            } else {
                $priority = 10;
            }

            if ($isNegative) {
                $plugins->add_hook($hookName, $callable, -$priority);
            } else {
                $plugins->add_hook($hookName, $callable, $priority);
            }
        }
    }
}

function languageLoad(): void
{
    global $lang;

    isset($lang->ougcAdvancedPostEdit) || $lang->load('ougc_adminpostedit');
}

function getSetting(string $settingKey = ''): bool|string|int
{
    global $mybb;

    return SETTINGS[$settingKey] ?? (
        $mybb->settings['ougc_adminpostedit_' . $settingKey] ?? false
    );
}

function recountUserThreads(int $userID): void
{
    global $db;

    $whereClauses = ["t.uid='{$userID}'", 't.visible>0', "t.closed NOT LIKE 'moved|%'"];

    $forumIDs = [];

    $query = $db->simple_select('forums', 'fid', "usethreadcounts='0'");

    while ($forumData = $db->fetch_array($query)) {
        $forumIDs[] = (int)$forumData['fid'];
    }

    if (!empty($forumIDs)) {
        $forumIDs = implode("','", $forumIDs);

        $whereClauses[] = "t.fid NOT IN('{$forumIDs}')";
    }

    $query = $db->simple_select(
        'threads t',
        'COUNT(t.tid) AS total_threads',
        implode(' AND ', $whereClauses),
        ['limit' => 1]
    );

    $db->update_query(
        'users',
        ['threadnum' => (int)$db->fetch_field($query, 'total_threads')],
        "uid='{$userID}'"
    );
}

function recountUserPosts(int $userID): void
{
    global $db;

    $whereClauses = ["p.uid='{$userID}'", 't.visible>0', 'p.visible > 0'];

    $forumIDs = [];

    $query = $db->simple_select('forums', 'fid', "usepostcounts='0'");

    while ($forumData = $db->fetch_array($query)) {
        $forumIDs[] = (int)$forumData['fid'];
    }

    if (!empty($forumIDs)) {
        $forumIDs = implode("','", $forumIDs);

        $whereClauses[] = "p.fid NOT IN('{$forumIDs}')";
    }

    $query = $db->simple_select(
        "posts p LEFT JOIN {$db->table_prefix}threads t ON (t.tid=p.tid)",
        'COUNT(p.pid) AS total_posts',
        implode(' AND ', $whereClauses),
        ['limit' => 1]
    );

    $db->update_query(
        'users',
        ['postnum' => (int)$db->fetch_field($query, 'total_posts')],
        "uid='{$userID}'"
    );
}