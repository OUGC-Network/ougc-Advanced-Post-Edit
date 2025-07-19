<?php

/***************************************************************************
 *
 *    ougc Admin Post Edit plugin (/inc/plugins/ougc_adminpostedit.php)
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

use function ougc\AdvancedPostEdit\Core\hooksAdd;
use function ougc\AdvancedPostEdit\Core\languageLoad;

use const ougc\AdvancedPostEdit\ROOT;

defined('IN_MYBB') || die('This file cannot be accessed directly.');

// You can uncomment the lines below to avoid storing some settings in the DB
define('ougc\AdvancedPostEdit\SETTINGS', [
    //'key' => '',
]);

define('ougc\AdvancedPostEdit\DEBUG', false);

define('ougc\AdvancedPostEdit\ROOT', MYBB_ROOT . 'inc/plugins/ougc/AdvancedPostEdit');

require_once ROOT . '/core.php';

defined('PLUGINLIBRARY') || define('PLUGINLIBRARY', MYBB_ROOT . 'inc/plugins/pluginlibrary.php');

if (defined('IN_ADMINCP')) {
} else {
    require_once ROOT . '/hooks/forum.php';

    hooksAdd('ougc\AdvancedPostEdit\Hooks\Forum');
}

require_once ROOT . '/hooks/shared.php';

hooksAdd('ougc\AdvancedPostEdit\Hooks\Shared');

// Cache template
if (defined('THIS_SCRIPT') && THIS_SCRIPT == 'editpost.php') {
    global $templatelist;

    if (!isset($templatelist)) {
        $templatelist = '';
    }

    $templatelist .= ',ougcadminpostedit';
}

// Plugin API
function ougc_adminpostedit_info()
{
    global $adminpostedit;

    return $adminpostedit->_info();
}

// _activate() routine
function ougc_adminpostedit_activate()
{
    global $adminpostedit;

    return $adminpostedit->_activate();
}

// _deactivate() routine
function ougc_adminpostedit_deactivate()
{
    global $adminpostedit;

    return $adminpostedit->_deactivate();
}

// _install() routine
function ougc_adminpostedit_install()
{
}

// _is_installed() routine
function ougc_adminpostedit_is_installed()
{
    global $adminpostedit;

    return $adminpostedit->_is_installed();
}

// _uninstall() routine
function ougc_adminpostedit_uninstall()
{
    global $adminpostedit;

    return $adminpostedit->_uninstall();
}

// Plugin class
class OUGC_AdminPostEdit
{
    // Plugin API:_info() routine
    public function _info()
    {
        global $lang;

        languageLoad();

        return [
            'name' => 'ougc Admin Post Edit',
            'description' => $lang->setting_group_ougc_adminpostedit_desc,
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

    // Plugin API:_activate() routine
    public function _activate()
    {
        global $PL, $lang, $mybb;
        $this->load_pluginlibrary();

        $PL->templates('ougcadminpostedit', 'ougc Admin Post Edit', [
            '' => '<tr>
<td class="tcat" colspan="2"><strong>{$lang->ougc_adminpostedit_post}</strong></td>
</tr>
<tr>
<td class="trow2" valign="top"><strong>{$lang->ougc_adminpostedit_post_time}</strong></td>
<td class="trow2">
	<input type="text" class="textbox" name="ougc_adminpostedit[timestamp]" style="width: 8em;" value="{$timestamp}" size="14" maxlength="10" />
</td>
</tr>
<tr>
<td class="trow2" valign="top"><strong>{$lang->ougc_adminpostedit_post_author}</strong></td>
<td class="trow2">
	<div style="width: 16em;">
		<input type="text" class="textbox" name="ougc_adminpostedit[username]" id="username" style="width: 16em;" value="{$search_username}" size="28" />
<link rel="stylesheet" href="{$mybb->asset_url}/jscripts/select2/select2.css">
<script type="text/javascript" src="{$mybb->asset_url}/jscripts/select2/select2.min.js?ver=1804"></script>
<script type="text/javascript">
<!--
if(use_xmlhttprequest == "1")
{
	MyBB.select2();
	$("#username").select2({
		placeholder: "{$lang->search_user}",
		minimumInputLength: 3,
		maximumSelectionSize: 3,
		multiple: false,
		ajax: {
			url: "xmlhttp.php?action=get_users",
			dataType: \'json\',
			data: function (term, page) {
				return {
					query: term,
				};
			},
			results: function (data, page) {
				return {results: data};
			}
		},
		initSelection: function(element, callback) {
			var value = $(element).val();
			if (value !== "") {
				callback({
					id: value,
					text: value
				});
			}
		},
       // Allow the user entered text to be selected as well
       createSearchChoice:function(term, data) {
			if ( $(data).filter( function() {
				return this.text.localeCompare(term)===0;
			}).length===0) {
				return {id:term, text:term};
			}
		},
	});

  	$(\'[for=username]\').click(function(){
		$("#username").select2(\'open\');
		return false;
	});
}
// -->
</script>
	</div>
</td>
</tr>
<tr>
<td class="trow2" valign="top"><strong>{$lang->ougc_adminpostedit_post_ip}</strong></td>
<td class="trow2"><input type="text" class="textbox" name="ougc_adminpostedit[ipaddress]" style="width: 8em;" value="{$p[\'ipaddress\']}" size="14" maxlength="16" /></td>
</tr>
<tr>
<td class="trow2" colspan="2"><span class="smalltext"><label><input type="checkbox" class="checkbox" name="ougc_adminpostedit[silent]" value="1" {$p[\'silent\']} /> {$lang->ougc_adminpostedit_post_silentedit}</label><br />
<label><input type="checkbox" class="checkbox" name="ougc_adminpostedit[reset]" value="1" {$p[\'reset\']} /> {$lang->ougc_adminpostedit_post_resetedit}</label><br />
<label><input type="checkbox" class="checkbox" name="ougc_adminpostedit[forceusername]" value="1" {$p[\'forceusername\']} /> {$lang->ougc_adminpostedit_post_forceusername}</label></span>
</td>
</tr>',
        ]);

        $PL->settings(
            'ougc_adminpostedit',
            $lang->setting_group_ougc_adminpostedit,
            $lang->setting_group_ougc_adminpostedit_desc,
            [
                'groups' => [
                    'title' => $lang->setting_ougc_adminpostedit_groups,
                    'description' => $lang->setting_ougc_adminpostedit_groups_desc,
                    'optionscode' => 'groupselect',
                    'value' => 4
                ],
            ]
        );

        require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';
        find_replace_templatesets('editpost', '#' . preg_quote('{$pollbox}') . '#i', '{$pollbox}{$ougc_adminpostedit}');

        // Insert/update version into cache
        $plugins = $mybb->cache->read('ougc_plugins');
        if (!$plugins) {
            $plugins = [];
        }

        $this->load_plugin_info();

        if (!isset($plugins['adminpostedit'])) {
            $plugins['adminpostedit'] = $this->plugin_info['versioncode'];
        }

        /*~*~* RUN UPDATES START *~*~*/

        /*~*~* RUN UPDATES END *~*~*/

        $plugins['adminpostedit'] = $this->plugin_info['versioncode'];
        $mybb->cache->update('ougc_plugins', $plugins);
    }

    // Plugin API:_deactivate() routine
    public function _deactivate()
    {
        require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';
        find_replace_templatesets('editpost', '#' . preg_quote('{$ougc_adminpostedit}') . '#i', '', 0);
    }

    // Plugin API:_is_installed() routine
    public function _is_installed()
    {
        global $cache;

        $plugins = $cache->read('ougc_plugins');

        return isset($plugins['adminpostedit']);
    }

    // Plugin API:_uninstall() routine
    public function _uninstall()
    {
        global $PL, $cache;
        $this->load_pluginlibrary();

        // Delete settings
        $PL->templates_delete('ougcadminpostedit');
        $PL->settings_delete('ougc_adminpostedit');

        // Delete version from cache
        $plugins = (array)$cache->read('ougc_plugins');

        if (isset($plugins['adminpostedit'])) {
            unset($plugins['adminpostedit']);
        }

        if (!empty($plugins)) {
            $cache->update('ougc_plugins', $plugins);
        } else {
            $PL->cache_delete('ougc_plugins');
        }
    }

    // Build plugin info
    public function load_plugin_info()
    {
        $this->plugin_info = ougc_adminpostedit_info();
    }

    // PluginLibrary requirement check
    public function load_pluginlibrary()
    {
        global $lang;
        $this->load_plugin_info();
        languageLoad();

        if (!file_exists(PLUGINLIBRARY)) {
            flash_message(
                $lang->sprintf(
                    $lang->ougc_adminpostedit_pluginlibrary_required,
                    $this->plugin_info['pl']['ulr'],
                    $this->plugin_info['pl']['version']
                ),
                'error'
            );
            admin_redirect('index.php?module=config-plugins');
        }

        global $PL;
        $PL or require_once PLUGINLIBRARY;

        if ($PL->version < $this->plugin_info['pl']['version']) {
            global $lang;

            flash_message(
                $lang->sprintf(
                    $lang->ougc_adminpostedit_pluginlibrary_old,
                    $PL->version,
                    $this->plugin_info['pl']['version'],
                    $this->plugin_info['pl']['ulr']
                ),
                'error'
            );
            admin_redirect('index.php?module=config-plugins');
        }
    }
}

global $adminpostedit;

$adminpostedit = new OUGC_AdminPostEdit();
