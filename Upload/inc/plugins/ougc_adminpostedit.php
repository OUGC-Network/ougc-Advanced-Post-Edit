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

// Die if IN_MYBB is not defined, for security reasons.
defined('IN_MYBB') or die('Direct initialization of this file is not allowed.');

// PLUGINLIBRARY
defined('PLUGINLIBRARY') or define('PLUGINLIBRARY', MYBB_ROOT . 'inc/plugins/pluginlibrary.php');

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
    private $update_dateline = null;
    private $update_user = null;

    function __construct()
    {
        global $plugins;

        // Tell MyBB when to run the hook
        if (defined('IN_ADMINCP')) {
            $plugins->add_hook('admin_style_templates_set', array($this, 'load_language'));
        } else {
            $plugins->add_hook('editpost_end', array($this, 'hook_editpost_end'));
            $plugins->add_hook('datahandler_post_update', array($this, 'hook_editpost_end'));
            $plugins->add_hook('editpost_do_editpost_start', array($this, 'hook_editpost_do_editpost_start'));
        }
    }

    // Plugin API:_info() routine
    function _info()
    {
        global $lang;

        $this->load_language();

        return array(
            'name' => 'OUGC Admin Post Edit',
            'description' => $lang->setting_group_ougc_adminpostedit_desc,
            'website' => 'https://ougc.network',
            'author' => 'Omar G.',
            'authorsite' => 'https://ougc.network',
            'version' => '1.8.22',
            'versioncode' => 1822,
            'compatibility' => '18*',
            'codename' => 'ougc_adminpostedit',
            'pl' => array(
                'version' => 13,
                'url' => 'https://community.mybb.com/mods.php?action=view&pid=573'
            )
        );
    }

    // Plugin API:_activate() routine
    function _activate()
    {
        global $PL, $lang, $mybb;
        $this->load_pluginlibrary();

        $PL->templates('ougcadminpostedit', 'OUGC Admin Post Edit', array(
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
        ));

        $PL->settings(
            'ougc_adminpostedit',
            $lang->setting_group_ougc_adminpostedit,
            $lang->setting_group_ougc_adminpostedit_desc,
            array(
                'groups' => array(
                    'title' => $lang->setting_ougc_adminpostedit_groups,
                    'description' => $lang->setting_ougc_adminpostedit_groups_desc,
                    'optionscode' => 'groupselect',
                    'value' => 4
                ),
            )
        );

        require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';
        find_replace_templatesets('editpost', '#' . preg_quote('{$pollbox}') . '#i', '{$pollbox}{$ougc_adminpostedit}');

        // Insert/update version into cache
        $plugins = $mybb->cache->read('ougc_plugins');
        if (!$plugins) {
            $plugins = array();
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
    function _deactivate()
    {
        require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';
        find_replace_templatesets('editpost', '#' . preg_quote('{$ougc_adminpostedit}') . '#i', '', 0);
    }

    // Plugin API:_is_installed() routine
    function _is_installed()
    {
        global $cache;

        $plugins = $cache->read('ougc_plugins');

        return isset($plugins['adminpostedit']);
    }

    // Plugin API:_uninstall() routine
    function _uninstall()
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

    // Load language file
    function load_language()
    {
        global $lang;

        isset($lang->setting_group_ougc_adminpostedit) or $lang->load('ougc_adminpostedit');
    }

    // Build plugin info
    function load_plugin_info()
    {
        $this->plugin_info = ougc_adminpostedit_info();
    }

    // PluginLibrary requirement check
    function load_pluginlibrary()
    {
        global $lang;
        $this->load_plugin_info();
        $this->load_language();

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

    // Hook: editpost_end/datahandler_post_update
    function hook_editpost_end(&$dh)
    {
        global $fid, $ougc_adminpostedit, $mybb;

        $ougc_adminpostedit = '';

        if (!is_moderator($fid, 'caneditposts') || !is_member($mybb->settings['ougc_adminpostedit_groups'])) {
            return;
        }

        global $lang, $templates, $pid, $db;

        $this->load_language();

        $post = get_post($pid);

        $p = array(
            'dateline' => $post['dateline'],
            'uid' => $post['uid'],
            'username' => $post['username'],
            'ipaddress' => my_inet_ntop($db->unescape_binary($post['ipaddress'])),
            'silent' => '',
            'reset' => '',
            'forceusername' => ''
        );

        $timestamp = (int)$p['dateline'];

        $search_username = '';

        if ($mybb->request_method == 'post') {
            $input = $mybb->get_input('ougc_adminpostedit', MyBB::INPUT_ARRAY);
            $input['username'] = trim($input['username']);

            $post_update_data = array();

            if (!empty($input['timestamp'])) {
                $timestamp = (int)$input['timestamp'];

                if ($this->is_timestamp(
                        $input['timestamp']
                    ) && $p['dateline'] != $input['timestamp'] && TIME_NOW >= $input['timestamp']) // don't allow "future" posts
                {
                    $p['dateline'] = $post_update_data['dateline'] = (int)$input['timestamp'];

                    $this->update_dateline = true;
                }
            }

            $search_username = htmlspecialchars_uni($input['username']);

            if (!empty($input['username'])/* && $p['username'] != $input['username']*/) {
                $user = get_user_by_username($input['username'], array('fields' => array('username')));

                $user['uid'] = (int)$user['uid'];
                $user['username'] = !empty($user['username']) ? $user['username'] : $input['username'];

                $change_user = !empty($user['uid']) ? $user['uid'] != $p['uid'] : !empty($input['forceusername']);

                if ($change_user) {
                    $p['uid'] = $post_update_data['uid'] = (int)$user['uid'];
                    $p['username'] = $user['username'];
                    $post_update_data['username'] = $db->escape_string($p['username']);

                    $this->update_user = true;
                }
            }

            if (isset($input['ipaddress']) && $p['ipaddress'] != $input['ipaddress']) {
                if (preg_match('#^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$#', $input['ipaddress'])) {
                    $ipaddress = array_map('intval', explode('.', $input['ipaddress']));
                    if (!($ipaddress[0] > 255 || $ipaddress[1] > 255 || $ipaddress[2] > 255 || $ipaddress[3] > 255)) {
                        $p['ipaddress'] = implode('.', $ipaddress);
                        $post_update_data['ipaddress'] = $db->escape_binary(my_inet_pton($p['ipaddress']));
                    }
                }
            }

            if (!empty($input['silent'])) {
                $p['silent'] = ' checked="checked"';
            }

            if (!empty($input['reset'])) {
                $p['reset'] = ' checked="checked"';

                $post_update_data['edituid'] = $post_update_data['edittime'] = 0;
            }

            if (!empty($input['forceusername'])) {
                $p['forceusername'] = ' checked="checked"';
            }

            if ($dh instanceof PostDataHandler) {
                $dh->post_update_data = array_merge($dh->post_update_data, $post_update_data);

                if (!empty($this->update_dateline) || !empty($this->update_user)) {
                    global $plugins;

                    $plugins->add_hook('datahandler_post_update_end', array($this, 'hook_datahandler_post_update_end'));
                }
            }
        }

        $ougc_adminpostedit = eval($templates->render('ougcadminpostedit'));
    }

    // Hook: editpost_end/datahandler_post_update
    function hook_datahandler_post_update_end(&$dh)
    {
        global $db;

        $forum = get_forum($dh->data['fid']);

        $thread = get_thread($dh->data['tid']);
        $thread['tid'] = (int)$thread['tid'];

        $query = $db->simple_select(
            'posts',
            'pid',
            "tid='{$thread['tid']}'",
            array('limit' => 1, 'order_by' => 'dateline', 'order_dir' => 'asc')
        );
        $firstpost = $db->fetch_field($query, 'pid');

        $post = get_post($dh->data['pid']);

        $new_user = get_user($dh->post_update_data['uid']);
        $new_user['uid'] = (int)$new_user['uid'];

        $thread_update = array();

        if ($this->update_user) {
            $update_query = array();
            if ($forum['usepostcounts']) {
                $update_query['postnum'] = '+1';
            }
            if ($forum['usethreadcounts'] && $firstpost == $post['pid']) {
                $update_query['threadnum'] = '+1';
            }

            if (!empty($update_query)) {
                update_user_counters($new_user['uid'], $update_query);
            }
        }

        if ($firstpost == $post['pid']) {
            $thread_update = array(
                'uid' => $this->update_user ? $dh->post_update_data['uid'] : (int)$post['uid'],
                'username' => $this->update_user ? $dh->post_update_data['username'] : $db->escape_string(
                    $post['username']
                )
            );
        }

        if ($this->update_dateline) {
            $query = $db->simple_select(
                'posts',
                'dateline',
                "uid='{$new_user['uid']}' AND visible=1",
                array('limit' => 1, 'order_by' => 'dateline', 'order_dir' => 'desc')
            );
            $new_lastpost = $db->fetch_field($query, 'dateline');

            $db->update_query('users', array('lastpost' => (int)$new_lastpost), "uid='{$new_user['uid']}'");
        }

        if ($firstpost == $post['pid']) {
            $thread_update['dateline'] = $this->update_dateline ? $dh->post_update_data['dateline'] : (int)$post['dateline'];
        }

        if (!empty($thread_update)) {
            $db->update_query('threads', $thread_update, "tid='{$thread['tid']}'");
        }

        $old_user = get_user($dh->data['uid']);
        $old_user['uid'] = (int)$old_user['uid'];

        if ($this->update_user) {
            $update_query = array();
            if ($forum['usepostcounts']) {
                $update_query['postnum'] = '-1';
            }
            if ($forum['usethreadcounts'] && $firstpost == $post['pid']) {
                $update_query['threadnum'] = '-1';
            }

            if (!empty($update_query)) {
                update_user_counters($old_user['uid'], $update_query);
            }
        }

        if ($this->update_dateline && $new_user['uid'] != $old_user['uid']) {
            $query = $db->simple_select(
                'posts',
                'dateline',
                "uid='{$old_user['uid']}' AND visible=1",
                array('limit' => 1, 'order_by' => 'dateline', 'order_dir' => 'desc')
            );
            $new_lastpost = $db->fetch_field($query, 'dateline');

            $db->update_query('users', array('lastpost' => (int)$new_lastpost), "uid='{$old_user['uid']}'");
        }

        update_last_post($dh->data['tid']);
        update_forum_lastpost($dh->data['fid']);
    }

    // Hook: editpost_do_editpost_start
    function hook_editpost_do_editpost_start()
    {
        global $mybb, $fid;

        if (!is_moderator($fid, 'caneditposts') || !is_member($mybb->settings['ougc_adminpostedit_groups'])) {
            return;
        }

        $input = $mybb->get_input('ougc_adminpostedit', MyBB::INPUT_ARRAY);

        if (!empty($input['silent'])) {
            $mybb->settings['showeditedbyadmin'] = 0;
        }
    }

    // Helper function to check for valid timestamps
    // @sepehr at https://gist.github.com/sepehr/6351385
    function is_timestamp($timestamp)
    {
        $check = (is_int($timestamp) or is_float($timestamp))
            ? $timestamp
            : (string)(int)$timestamp;
        return ($check === $timestamp)
            and ((int)$timestamp <= PHP_INT_MAX)
            and ((int)$timestamp >= ~PHP_INT_MAX);
    }
}

global $adminpostedit;

$adminpostedit = new OUGC_AdminPostEdit;
