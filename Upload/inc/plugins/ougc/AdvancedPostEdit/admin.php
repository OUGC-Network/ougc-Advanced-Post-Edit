<?php

/***************************************************************************
 *
 *    ougc Advanced Post Edit plugin (/inc/plugins/ougc/AdvancedPostEdit/admin.php)
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

namespace ougc\AdvancedPostEdit\Admin;

use DirectoryIterator;
use PluginLibrary;
use stdClass;

use function ougc\AdvancedPostEdit\Core\languageLoad;

use const ougc\AdvancedPostEdit\ROOT;

function pluginInformation(): array
{
    global $lang;

    languageLoad();

    return [
        'name' => 'ougc Advanced Post Edit',
        'description' => $lang->ougcAdvancedPostEditDescription,
        'website' => 'https://ougc.network',
        'author' => 'Omar G.',
        'authorsite' => 'https://ougc.network',
        'version' => '1.8.22',
        'versioncode' => 1822,
        'compatibility' => '18*',
        'codename' => 'ougc_adminpostedit',
        'pl' => [
            'version' => 13,
            'url' => 'https://community.mybb.com/mods.php?action=view&pid=573'
        ]
    ];
}

function pluginActivation(): void
{
    global $PL, $cache, $lang;

    languageLoad();

    $pluginInfo = pluginInformation();

    loadPluginLibrary();

    $settingsContents = file_get_contents(ROOT . '/settings.json');

    $settingsData = json_decode($settingsContents, true);

    foreach ($settingsData as $settingKey => &$settingData) {
        if (empty($lang->{"setting_ougc_adminpostedit_{$settingKey}"})) {
            continue;
        }

        if ($settingData['optionscode'] == 'select' || $settingData['optionscode'] == 'checkbox') {
            foreach ($settingData['options'] as $optionKey) {
                $settingData['optionscode'] .= "\n{$optionKey}={$lang->{"setting_ougc_adminpostedit_{$settingKey}_{$optionKey}"}}";
            }
        }

        $settingData['title'] = $lang->{"setting_ougc_adminpostedit_{$settingKey}"};

        $settingData['description'] = $lang->{"setting_ougc_adminpostedit_{$settingKey}_desc"};
    }

    $PL->settings(
        'ougc_adminpostedit',
        $lang->setting_group_ougc_adminpostedit,
        $lang->setting_group_ougc_adminpostedit_desc,
        $settingsData
    );

    $templates = [];

    if (file_exists($templateDirectory = ROOT . '/templates')) {
        $templatesDirIterator = new DirectoryIterator($templateDirectory);

        foreach ($templatesDirIterator as $template) {
            if (!$template->isFile()) {
                continue;
            }

            $pathName = $template->getPathname();

            $pathInfo = pathinfo($pathName);

            if ($pathInfo['extension'] === 'html') {
                $templates[$pathInfo['filename']] = file_get_contents($pathName);
            }
        }
    }

    if ($templates) {
        $PL->templates('ougcadminpostedit', 'ougc Advanced Post Edit', $templates);
    }

    $plugins = $cache->read('ougc_plugins');

    if (!$plugins) {
        $plugins = [];
    }

    if (!isset($plugins['adminpostedit'])) {
        $plugins['adminpostedit'] = $pluginInfo['versioncode'];
    }

    /*~*~* RUN UPDATES START *~*~*/

    /*~*~* RUN UPDATES END *~*~*/

    $plugins['adminpostedit'] = $pluginInfo['versioncode'];

    $cache->update('ougc_plugins', $plugins);
}

function pluginIsInstalled(): bool
{
    global $cache;

    $plugins = $cache->read('ougc_plugins');

    return isset($plugins['adminpostedit']);
}

function pluginUninstallation(): void
{
    global $db, $PL, $cache;

    loadPluginLibrary();

    $PL->settings_delete('ougc_adminpostedit');

    $PL->templates_delete('ougcadminpostedit');

    // Delete version from cache
    $plugins = (array)$cache->read('ougc_plugins');

    if (isset($plugins['adminpostedit'])) {
        unset($plugins['adminpostedit']);
    }

    if (!empty($plugins)) {
        $cache->update('ougc_plugins', $plugins);
    } else {
        $cache->delete('ougc_plugins');
    }
}

function pluginLibraryRequirements(): stdClass
{
    return (object)pluginInformation()['pl'];
}

function loadPluginLibrary(): void
{
    global $PL, $lang;

    languageLoad();

    $fileExists = file_exists(PLUGINLIBRARY);

    if ($fileExists && !($PL instanceof PluginLibrary)) {
        require_once PLUGINLIBRARY;
    }

    if (!$fileExists || $PL->version < pluginLibraryRequirements()->version) {
        flash_message(
            $lang->sprintf(
                $lang->ougcAdvancedPostEditPluginLibrary,
                pluginLibraryRequirements()->url,
                pluginLibraryRequirements()->version
            ),
            'error'
        );

        admin_redirect('index.php?module=config-plugins');
    }
}