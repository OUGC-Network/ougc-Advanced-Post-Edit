<?php

/***************************************************************************
 *
 *    ougc Admin Post Edit plugin (/inc/plugins/ougc_adminpostedit.php)
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

use function ougc\AdvancedPostEdit\Core\hooksAdd;
use function ougc\AdvancedPostEdit\Admin\pluginActivation;
use function ougc\AdvancedPostEdit\Admin\pluginInformation;
use function ougc\AdvancedPostEdit\Admin\pluginIsInstalled;
use function ougc\AdvancedPostEdit\Admin\pluginUninstallation;

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
    require_once ROOT . '/admin.php';
    require_once ROOT . '/hooks/admin.php';

    hooksAdd('ougc\AdvancedPostEdit\Hooks\Admin');
} else {
    require_once ROOT . '/hooks/forum.php';

    hooksAdd('ougc\AdvancedPostEdit\Hooks\Forum');
}

require_once ROOT . '/hooks/shared.php';

hooksAdd('ougc\AdvancedPostEdit\Hooks\Shared');

function ougc_adminpostedit_info(): array
{
    return pluginInformation();
}

function ougc_adminpostedit_activate()
{
    pluginActivation();
}

function ougc_adminpostedit_is_installed(): bool
{
    return pluginIsInstalled();
}

function ougc_adminpostedit_uninstall(): void
{
    pluginUninstallation();
}

class OUGC_AdminPostEdit
{
}

global $adminpostedit;

$adminpostedit = new OUGC_AdminPostEdit();
