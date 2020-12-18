#!/usr/bin/env php
<?php
/***********************************************
* File      :   z-push-admin.php
* Project   :   Z-Push
* Descr     :   This is a small command line
*               client to see and modify the
*               wipe status of Kopano users.
*
* Created   :   14.05.2010
*
* Copyright 2007 - 2016 Zarafa Deutschland GmbH
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU Affero General Public License, version 3,
* as published by the Free Software Foundation.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU Affero General Public License for more details.
*
* You should have received a copy of the GNU Affero General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* Consult LICENSE file for details
************************************************/

require_once 'vendor/autoload.php';

/**
 * //TODO resync of single folders of a users device
 */

/************************************************
 * MAIN
 */
    define('BASE_PATH_CLI',  dirname(__FILE__) ."/");
    set_include_path(get_include_path() . PATH_SEPARATOR . BASE_PATH_CLI);

    if (!defined('ZPUSH_CONFIG')) define('ZPUSH_CONFIG', BASE_PATH_CLI . 'config.php');
    include_once(ZPUSH_CONFIG);

    try {
        ZPush::CheckConfig();
        ZPushAdminCLI::CheckEnv();
        ZPushAdminCLI::CheckOptions();

        if (! ZPushAdminCLI::SureWhatToDo()) {
            // show error message if available
            if (ZPushAdminCLI::GetErrorMessage())
                fwrite(STDERR, ZPushAdminCLI::GetErrorMessage() . "\n");

            echo ZPushAdminCLI::UsageInstructions();
            if (ZPushAdminCLI::$help) {
                exit(0);
            }
            exit(1);
        }

        ZPushAdminCLI::RunCommand();
    }
    catch (ZPushException $zpe) {
        fwrite(STDERR, get_class($zpe) . ": ". $zpe->getMessage() . "\n");
        exit(1);
    }


/************************************************
 * Z-Push-Admin CLI
 */
class ZPushAdminCLI {
    const COMMAND_SHOWALLDEVICES = 1;
    const COMMAND_SHOWDEVICESOFUSER = 2;
    const COMMAND_SHOWUSERSOFDEVICE = 3;
    const COMMAND_WIPEDEVICE = 4;
    const COMMAND_REMOVEDEVICE = 5;
    const COMMAND_RESYNCDEVICE = 6;
    const COMMAND_CLEARLOOP = 7;
    const COMMAND_SHOWLASTSYNC = 8;
    const COMMAND_RESYNCFOLDER = 9;
    const COMMAND_FIXSTATES = 10;
    const COMMAND_RESYNCHIERARCHY = 11;
    const COMMAND_ADDSHARED = 12;
    const COMMAND_REMOVESHARED = 13;
    const COMMAND_LISTALLSHARES = 14;
    const COMMAND_LISTSTORESHARES = 15;
    const COMMAND_LISTFOLDERSHARES = 16;
    const COMMAND_LISTFOLDERS = 17;
    const COMMAND_LISTDETAILS = 18;

    const TYPE_OPTION_EMAIL = "email";
    const TYPE_OPTION_CALENDAR = "calendar";
    const TYPE_OPTION_CONTACT = "contact";
    const TYPE_OPTION_TASK = "task";
    const TYPE_OPTION_NOTE = "note";
    const TYPE_OPTION_HIERARCHY = "hierarchy";
    const TYPE_OPTION_GAB = "gab";

    static private $command;
    static private $user = false;
    static private $device = false;
    static private $type = false;
    static private $errormessage;
    static private $daysold = false;
    static private $shared = false;
    static private $devicedriven = false;
    static private $foldername = false;
    static private $store = false;
    static private $folderid = false;
    static private $flags = 0;

    static public $help = false;

    /**
     * Returns usage instructions
     *
     * @return string
     * @access public
     */
    static public function UsageInstructions() {
        return  "Usage:\n\tz-push-admin.php -a ACTION [options]\n\n" .
                "Parameters:\n\t-a list/lastsync/listdetails/wipe/remove/resync/clearloop/fixstates/addshared/removeshared/listshares\n\t[-u] username\n\t[-d] deviceid\n" .
                "\t[-t] type\tthe following types are available: '".self::TYPE_OPTION_EMAIL."', '".self::TYPE_OPTION_CALENDAR."', '".self::TYPE_OPTION_CONTACT."', '".self::TYPE_OPTION_TASK."', '".self::TYPE_OPTION_NOTE."', '".self::TYPE_OPTION_HIERARCHY."' of '".self::TYPE_OPTION_GAB."' (for KOE) or a folder id.\n" .
                "\t[--shared|-s]\tshow detailed information about shared folders of a user in list.\n".
                "\t[--days-old] n\tshow or remove profiles older than n days with lastsync or remove. n must be a positive integer.\n".
                "\t[--devicedriven]\texecute the fixstates device driven. Recommended for specific enviroments with high traffic.\n\n".
                "Actions:\n" .
                "\tlist\t\t\t\t\t Lists all devices and synchronized users.\n" .
                "\tlist -u USER\t\t\t\t Lists all devices of user USER.\n" .
                "\tlist -d DEVICE\t\t\t\t Lists all users of device DEVICE.\n" .
                "\tlastsync\t\t\t\t Lists all devices and synchronized users and the last synchronization time.\n" .
                "\tlistdetails\t\t\t\t Lists all synchronized devices-users and their details in a tab separated list.\n" .
                "\twipe -u USER\t\t\t\t Remote wipes all devices of user USER.\n" .
                "\twipe -d DEVICE\t\t\t\t Remote wipes device DEVICE.\n" .
                "\twipe -u USER -d DEVICE\t\t\t Remote wipes device DEVICE of user USER.\n" .
                "\tremove -u USER\t\t\t\t Removes all state data of all devices of user USER.\n" .
                "\tremove -d DEVICE\t\t\t Removes all state data of all users synchronized on device DEVICE.\n" .
                "\tremove -u USER -d DEVICE\t\t Removes all related state data of device DEVICE of user USER.\n" .
                "\tresync -u USER -d DEVICE\t\t Resynchronizes all data of device DEVICE of user USER.\n" .
                "\tresync -t TYPE \t\t\t\t Resynchronizes all folders of type (possible values above) for all devices and users.\n" .
                "\tresync -t TYPE -u USER \t\t\t Resynchronizes all folders of type (possible values above) for the user USER.\n" .
                "\tresync -t TYPE -u USER -d DEVICE\t Resynchronizes all folders of type (possible values above) for a specified device and user.\n" .
                "\tresync -t FOLDERID -u USER\t\t Resynchronize the specified folder id only. The USER should be specified for better performance.\n" .
                "\tresync -t hierarchy -u USER -d DEVICE\t Resynchronize the folder hierarchy data for an optional USER and optional DEVICE.\n" .
                "\tclearloop\t\t\t\t Clears system wide loop detection data.\n" .
                "\tclearloop -d DEVICE -u USER\t\t Clears all loop detection data of a device DEVICE and an optional user USER.\n" .
                "\tfixstates\t\t\t\t Checks the states for integrity and fixes potential issues.\n" .
                "\tfixstates -u USER\t\t\t Checks the states for integrity and fixes potential issues of user USER.\n\n" .
                "\taddshared -u USER -d DEVICE -n FOLDERNAME -o STORE -t TYPE -f FOLDERID -g FLAGS\n" .
                        "\t\t\t\t\t\t Adds a shared folder for a user.\n" .
                        "\t\t\t\t\t\t USER is required. If no DEVICE is given, the shared folder will be added to all of the devices of the user.\n" .
                        "\t\t\t\t\t\t FOLDERNAME the name of the shared folder. STORE - where this folder is located, e.g. \"SYSTEM\" (for public folder) or a username.\n" .
                        "\t\t\t\t\t\t TYPE is the folder type of the shared folder (possible values above, except 'hierarchy' and 'gab').\n" .
                        "\t\t\t\t\t\t FOLDERID is the id of shared folder.\n" .
                        "\t\t\t\t\t\t FLAGS is optional (default: '0'). Make sure you separate -g and value with \"=\", e.g. -g=4.\n" .
                        "\t\t\t\t\t\t Possible values for FLAGS: 0(none), 1 (Send-As from this folder), 4 (show calendar reminders for this folder), 8 (don't send notification emails for changes\n" .
                        "\t\t\t\t\t\t if the folder is read-only) and all bitwise or combinations of these flags.\n" .
                "\tremoveshared -u USER -d DEVICE -f FOLDERID\n" .
                        "\t\t\t\t\t\t Removes a shared folder for a user.\n" .
                        "\t\t\t\t\t\t USER is required. If no DEVICE is given, the shared folder will be removed from all of the devices of the user.\n" .
                        "\t\t\t\t\t\t FOLDERID is the id of shared folder.\n" .
                "\tlistshares -o STORE -f FOLDERID\n".
                        "\t\t\t\t\t\t Lists opened shared folders and who opened them on which device.\n" .
                        "\t\t\t\t\t\t STORE and FOLDERID are optional. If they're not provided then the script will display all open shares.\n" .
                        "\t\t\t\t\t\t STORE - whose shared folders to list, e.g. \"SYSTEM\" (for public folders) or a username.\n" .
                        "\t\t\t\t\t\t FOLDERID - list who opened the shared folder.\n" .
                        "\t\t\t\t\t\t If both STORE and FOLDERID are provided the script will only list who opened the folder ignoring the STORE parameter.\n" .
                "\tlistfolders -u USER -d DEVICE\n".
                        "\t\t\t\t\t\t Returns each folder and FOLDERID of user USER and device DEVICE. Useful for getting FOLDERID to be used with the command: resync -t FOLDERID -u USER.\n".
                        "\t\t\t\t\t\t Note that if a device is offline, broken or not being synched for some time, this list will not be updated. If folders were created/renamed/removed\n".
                        "\t\t\t\t\t\t after the last synchronization, this will not be reflected in this list.\n".
                "\n";
    }

    /**
     * Checks the environment
     *
     * @return
     * @access public
     */
    static public function CheckEnv() {
        if (php_sapi_name() != "cli")
            self::$errormessage = "This script can only be called from the CLI.";

        if (!function_exists("getopt"))
            self::$errormessage = "PHP Function getopt not found. Please check your PHP version and settings.";
    }

    /**
     * Checks the options from the command line
     *
     * @return
     * @access public
     */
    static public function CheckOptions() {
        if (self::$errormessage)
            return;

        $options = getopt("u:d:a:t:sn:o:f:g::h", array('user:', 'device:', 'action:', 'type:', 'days-old:', 'days-ago:', 'shared', 'foldername:', 'store', 'folderid:', 'flags::', 'devicedriven', 'help'));

        // get 'user'
        if (isset($options['u']) && !empty($options['u']))
            self::$user = strtolower(trim($options['u']));
        else if (isset($options['user']) && !empty($options['user']))
            self::$user = strtolower(trim($options['user']));

        // get 'device'
        if (isset($options['d']) && !empty($options['d']))
            self::$device = strtolower(trim($options['d']));
        else if (isset($options['device']) && !empty($options['device']))
            self::$device = strtolower(trim($options['device']));

        // get 'action'
        $action = false;
        if (isset($options['a']) && !empty($options['a']))
            $action = strtolower(trim($options['a']));
        elseif (isset($options['action']) && !empty($options['action']))
            $action = strtolower(trim($options['action']));

        // get 'type'
        if (isset($options['t']) && !empty($options['t']))
            self::$type = strtolower(trim($options['t']));
        elseif (isset($options['type']) && !empty($options['type']))
            self::$type = strtolower(trim($options['type']));

        if (isset($options['days-ago']) && !empty($options['days-ago'])) {
            $options['days-old'] = $options['days-ago'];
        }

        if (isset($options['days-old']) && !empty($options['days-old'])) {
            if (!is_numeric($options['days-old']) || $options['days-old'] < 0) {
                self::$errormessage = "--days-old parameter must be a positive integer\n";
                self::$command = null;
                return;
            }
            self::$daysold = trim($options['days-old']);
        }

        if (isset($options['s']) || isset($options['shared'])) {
            self::$shared = true;
        }

        if (isset($options['devicedriven'])) {
            if (self::$user){
                self::$errormessage = "--devicedriven doesn't accept the user -u parameter.\n";
                return;
            }
            self::$devicedriven = true;
        }

        // get 'foldername'
        if (isset($options['n']) && !empty($options['n']))
            self::$foldername = trim($options['n']);
        elseif (isset($options['foldername']) && !empty($options['foldername']))
            self::$foldername = trim($options['foldername']);

        // get 'store'
        if (isset($options['o']) && !empty($options['o']))
            self::$store = trim($options['o']);
        elseif (isset($options['store']) && !empty($options['store']))
            self::$store = trim($options['store']);

        // get 'folderid'
        if (isset($options['f']) && !empty($options['f']))
            self::$folderid = trim($options['f']);
        elseif (isset($options['folderid']) && !empty($options['folderid']))
            self::$folderid = trim($options['folderid']);

        // get 'flags'
        if (isset($options['flags'])) {
            $options['g'] = $options['flags'];
        }

        if (isset($options['g'])) {
            $flags = intval($options['g']);
            if ($flags == DeviceManager::FLD_FLAGS_NONE || ($flags & (DeviceManager::FLD_FLAGS_SENDASOWNER | DeviceManager::FLD_FLAGS_CALENDARREMINDERS | DeviceManager::FLD_FLAGS_NOREADONLYNOTIFY))) {
                self::$flags = $flags;
            }
            else {
                self::$flags = false;
                self::$errormessage = "Possible values for FLAGS: 0(none), 1 (Send-As from this folder), 4 (show calendar reminders for this folder), 5 (combination of Send-as and calendar reminders).\n";
            }
        }

        // if type is set, it must be one of known types or a 44 or 48 byte long folder id
        if (self::$type !== false) {
            if (self::$type !== self::TYPE_OPTION_EMAIL &&
                self::$type !== self::TYPE_OPTION_CALENDAR &&
                self::$type !== self::TYPE_OPTION_CONTACT &&
                self::$type !== self::TYPE_OPTION_TASK &&
                self::$type !== self::TYPE_OPTION_NOTE &&
                self::$type !== self::TYPE_OPTION_HIERARCHY &&
                self::$type !== self::TYPE_OPTION_GAB &&
                strlen(self::$type) !== 6 &&       // like U1f38d
                strlen(self::$type) !== 44 &&
                strlen(self::$type) !== 48) {
                    self::$errormessage = "Wrong 'type'. Possible values are: ".
                        "'".self::TYPE_OPTION_EMAIL."', '".self::TYPE_OPTION_CALENDAR."', '".self::TYPE_OPTION_CONTACT."', '".self::TYPE_OPTION_TASK."', '".self::TYPE_OPTION_NOTE."', '".self::TYPE_OPTION_HIERARCHY."', '".self::TYPE_OPTION_GAB."' ".
                        "or a 6, 44 or 48 byte long folder id (as hex).";
                    return;
                }
        }

        if ((isset($options['h']) || isset($options['help'])) && $action === false) {
            self::$help = true;
            $action = 'help';
        }

        // get a command for the requested action
        switch ($action) {
            // list data
            case "list":
                if (self::$user === false && self::$device === false)
                    self::$command = self::COMMAND_SHOWALLDEVICES;

                if (self::$user !== false)
                    self::$command = self::COMMAND_SHOWDEVICESOFUSER;

                if (self::$device !== false)
                    self::$command = self::COMMAND_SHOWUSERSOFDEVICE;
                break;

            // list data
            case "lastsync":
                self::$command = self::COMMAND_SHOWLASTSYNC;
                break;

            // list details
            case "listdetails":
                self::$command = self::COMMAND_LISTDETAILS;
                break;

            // remove wipe device
            case "wipe":
                if (self::$user === false && self::$device === false)
                    self::$errormessage = "Not possible to execute remote wipe. Device, user or both must be specified.";
                else
                    self::$command = self::COMMAND_WIPEDEVICE;
                break;

            // remove device data of user
            case "remove":
                if (self::$user === false && self::$device === false)
                    self::$errormessage = "Not possible to remove data. Device, user or both must be specified.";
                else
                    self::$command = self::COMMAND_REMOVEDEVICE;
                break;

            // resync a device
            case "resync":
            case "re-sync":
            case "sync":
            case "resynchronize":
            case "re-synchronize":
            case "synchronize":
                // full resync
                if (self::$type === false) {
                    if (self::$user === false || self::$device === false)
                        self::$errormessage = "Not possible to resynchronize device. Device and user must be specified.";
                    else
                        self::$command = self::COMMAND_RESYNCDEVICE;
                }
                else if (self::$type === self::TYPE_OPTION_HIERARCHY) {
                    self::$command = self::COMMAND_RESYNCHIERARCHY;
                }
                else {
                    self::$command = self::COMMAND_RESYNCFOLDER;
                }
                break;

            // clear loop detection data
            case "clearloop":
            case "clearloopdetection":
                self::$command = self::COMMAND_CLEARLOOP;
                break;

            // fix states
            case "fixstates":
            case "fix":
                self::$command = self::COMMAND_FIXSTATES;
                break;

            case "addshared":
                if (self::$user === false || self::$foldername === false || self::$store === false || self::$type === false || self::$folderid === false || self::$flags === false) {
                    if (!self::$errormessage) {
                        self::$errormessage = 'USER, FOLDERNAME, STORE, TYPE and FOLDERID are required for addshared command.';
                    }
                    return;
                }
                else {
                    if (in_array(self::$type, array(self::TYPE_OPTION_CALENDAR, self::TYPE_OPTION_CONTACT, self::TYPE_OPTION_EMAIL, self::TYPE_OPTION_NOTE, self::TYPE_OPTION_TASK))) {
                        self::$command = self::COMMAND_ADDSHARED;
                    }
                    elseif (self::$type == self::TYPE_OPTION_HIERARCHY || self::$type == self::TYPE_OPTION_GAB) {
                        self::$errormessage = "'hierarchy' and 'gab' are not valid types for addshared action.";
                        return;
                    }
                    else {
                        self::$errormessage = sprintf("Adding folder of type '%s' is not supported.", self::$type);
                        return;
                    }
                }
                break;

            case "removeshared":
                if (self::$user === false || self::$folderid === false) {
                    self::$errormessage = 'USER and FOLDERID are required for removeshared command.';
                    return;
                }
                self::$command = self::COMMAND_REMOVESHARED;
                break;

            case "listshares":
                if (self::$store === false && self::$device === false)
                    self::$command = self::COMMAND_LISTALLSHARES;

                if (self::$store !== false)
                    self::$command = self::COMMAND_LISTSTORESHARES;

                if (self::$folderid !== false)
                    self::$command = self::COMMAND_LISTFOLDERSHARES;
                break;

            case "listfolders":
                self::$command = self::COMMAND_LISTFOLDERS;
                break;

            case "help":
                break;

            default:
                self::UsageInstructions();
                self::$help = false;
        }
    }

    /**
     * Indicates if the options from the command line
     * could be processed correctly
     *
     * @return boolean
     * @access public
     */
    static public function SureWhatToDo() {
        return isset(self::$command);
    }

    /**
     * Returns a errormessage of things which could have gone wrong
     *
     * @return string
     * @access public
     */
    static public function GetErrorMessage() {
        return (isset(self::$errormessage))?self::$errormessage:"";
    }

    /**
     * Runs a command requested from an action of the command line
     *
     * @return
     * @access public
     */
    static public function RunCommand() {
        echo "\n";
        switch(self::$command) {
            case self::COMMAND_SHOWALLDEVICES:
                self::CommandShowDevices();
                break;

            case self::COMMAND_SHOWDEVICESOFUSER:
                self::CommandShowDevices();
                break;

            case self::COMMAND_SHOWUSERSOFDEVICE:
                self::CommandDeviceUsers();
                break;

            case self::COMMAND_SHOWLASTSYNC:
                self::CommandShowLastSync();
                break;

            case self::COMMAND_LISTDETAILS:
                self::CommandListDetails();
                break;

            case self::COMMAND_WIPEDEVICE:
                if (self::$device)
                    echo sprintf("Are you sure you want to REMOTE WIPE device '%s' [y/N]: ", self::$device);
                else
                    echo sprintf("Are you sure you want to REMOTE WIPE all devices of user '%s' [y/N]: ", self::$user);

                $confirm  =  strtolower(trim(fgets(STDIN)));
                if ( $confirm === 'y' || $confirm === 'yes')
                    self::CommandWipeDevice();
                else
                    echo "Aborted!\n";
                break;

            case self::COMMAND_REMOVEDEVICE:
                self::CommandRemoveDevice();
                break;

            case self::COMMAND_RESYNCDEVICE:
                if (self::$device == false) {
                    echo sprintf("Are you sure you want to re-synchronize all devices of user '%s' [y/N]: ", self::$user);
                    $confirm  =  strtolower(trim(fgets(STDIN)));
                    if ( !($confirm === 'y' || $confirm === 'yes')) {
                        echo "Aborted!\n";
                        exit(1);
                    }
                }
                self::CommandResyncDevices();
                break;

            case self::COMMAND_RESYNCFOLDER:
                if (self::$device == false && self::$user == false) {
                    echo "Are you sure you want to re-synchronize this folder type of all devices and users [y/N]: ";
                    $confirm  =  strtolower(trim(fgets(STDIN)));
                    if ( !($confirm === 'y' || $confirm === 'yes')) {
                        echo "Aborted!\n";
                        exit(1);
                    }
                }
                self::CommandResyncFolder();
                break;

            case self::COMMAND_RESYNCHIERARCHY:
                if (self::$device == false && self::$user == false) {
                    echo "Are you sure you want to re-synchronize the hierarchy of all devices and users [y/N]: ";
                    $confirm  =  strtolower(trim(fgets(STDIN)));
                    if ( !($confirm === 'y' || $confirm === 'yes')) {
                        echo "Aborted!\n";
                        exit(1);
                    }
                }
                self::CommandResyncHierarchy();
                break;

            case self::COMMAND_CLEARLOOP:
                self::CommandClearLoopDetectionData();
                break;

            case self::COMMAND_FIXSTATES:
                if (self::$user === false) {
                    self::CommandFixStates();
                }
                else {
                    self::CommandFixStates(self::$user);
                }
                break;

            case self::COMMAND_ADDSHARED:
                self::CommandAddShared();
                break;

            case self::COMMAND_REMOVESHARED:
                self::CommandRemoveShared();
                break;

            case self::COMMAND_LISTALLSHARES:
            case self::COMMAND_LISTSTORESHARES:
            case self::COMMAND_LISTFOLDERSHARES:
                self::CommandListShares();
                break;

            case self::COMMAND_LISTFOLDERS:
                self::CommandListFolders();
                break;
        }
        echo "\n";
    }

    /**
     * Command "Show all devices" and "Show devices of user"
     * Prints the device id of/and connected users
     *
     * @return
     * @access public
     */
    static public function CommandShowDevices() {
        $devicelist = ZPushAdmin::ListDevices(self::$user);
        if (empty($devicelist))
            echo "\tno devices found\n";
        else {
            if (self::$user === false) {
                echo "All synchronized devices\n\n";
                echo str_pad("Device id", 36). "Synchronized users\n";
                echo "-----------------------------------------------------\n";
            }
            else
                echo "Synchronized devices of user: ". self::$user. "\n";
        }

        foreach ($devicelist as $deviceId) {
            if (self::$user === false) {
                echo str_pad($deviceId, 36) . implode (",", ZPushAdmin::ListUsers($deviceId)) ."\n";
            }
            else
                self::printDeviceData($deviceId, self::$user);
        }
    }

    /**
     * Command "Show all devices and users with last sync time"
     * Prints the device id of/and connected users
     *
     * @return
     * @access public
     */
     static public function CommandShowLastSync() {
        $devicelist = ZPushAdmin::ListDevices(false);
        if (empty($devicelist))
            echo "\tno devices found\n";
        else {
            echo "All known devices and users and their last synchronization time\n\n";
            echo str_pad("Device id", 36) . str_pad("Synchronized user", 31) . str_pad("Last sync time", 33) . "Short Ids\n";
            echo "------------------------------------------------------------------------------------------------------------------\n";
        }

        $now = time();
        foreach ($devicelist as $deviceId) {
            $users = ZPushAdmin::ListUsers($deviceId);
            foreach ($users as $user) {
                $device = ZPushAdmin::GetDeviceDetails($deviceId, $user);
                $daysOld = floor(($now - $device->GetLastSyncTime()) / 86400);
                if (self::$daysold > $daysOld) {
                    continue;
                }
                $lastsync = $device->GetLastSyncTime() ? strftime("%Y-%m-%d %H:%M", $device->GetLastSyncTime()) . ' (' . str_pad($daysOld, 3, ' ', STR_PAD_LEFT) . ' days ago)' : "never";
                $hasShortFolderIds = $device->HasFolderIdMapping() ? "Yes":"No";
                echo str_pad($deviceId, 36) . str_pad($user, 30) . " " . str_pad($lastsync, 33) . $hasShortFolderIds . "\n";
            }
        }
    }

    /**
     * Command "Lists all synchronized devices-users and their details in a tab separated list"
     *
     * Prints the device id of/and connected users:
     * - Device id
     * - Synchronized users
     * - Last sync time
     * - deviceType
     * - deviceModel
     * - deviceOS
     * - ASVersion
     * - KoeVersion
     * - Total folders
     * - Synchronized folders
     * - Not synchronized folders
     * - Shared/impersonated folders
     * - Ignored messages
     * - KOE inactive
     *
     * @return
     * @access public
     */
    static public function CommandListDetails() {

        $devicelist = ZPushAdmin::ListDevices();
        if (empty($devicelist))
            echo "\tno devices found\n";
        else {
            echo "All synchronized devices\n\n",
            "Device id\t",
            "Synchronized user\t",
            "Last sync time\t",
            "deviceType\t",
            "UserAgent\t",
            "deviceModel\t",
            "deviceOS\t",
            "ASVersion\t",
            "KoeVersion\t",
            "Total folders\t",
            "Synchronized folders\t",
            "Not synchronized folders\t",
            "Shared/impersonated folders\t",
            "Ignored messages\t",
            "KOE inactive\t\n",
            str_repeat('-', 163), "\n";
        }
        $now = time();
        foreach ($devicelist as $deviceId) {
            $users = ZPushAdmin::ListUsers($deviceId);
            foreach ($users as $usr) {
                $device = ZPushAdmin::GetDeviceDetails($deviceId, $usr);
                $daysOld = floor(($now - $device->GetLastSyncTime()) / 86400);
                if (self::$daysold > $daysOld) {
                    continue;
                }
                $lastsync = $device->GetLastSyncTime() ? strftime("%Y-%m-%d %H:%M", $device->GetLastSyncTime()) . ' (' . str_pad($daysOld, 3, ' ', STR_PAD_LEFT) . ' days ago)' : "never";
                $data = self::ListDeviceFolders($deviceId, $usr);
                echo $deviceId, "\t",
                $usr, "\t",
                $lastsync, "\t",
                ($device->GetDeviceType() !== ASDevice::UNDEFINED ? $device->GetDeviceType() : "unknown"), "\t",
                ($device->GetDeviceUserAgent()!== ASDevice::UNDEFINED ? $device->GetDeviceUserAgent() : "unknown"), "\t",
                $device->GetDeviceModel(), "\t",
                $device->GetDeviceOS(), "\t",
                ($device->GetASVersion() ? $device->GetASVersion() : "unknown"), "\t",
                $device->GetKoeVersion(), "\t",
                $data[0], "\t",
                $data[1], "\t",
                $data[2], "\t",
                $data[3], "\t",
				( (isset($device->ignoredmessages) && !empty($device->ignoredmessages)) ? count($device->ignoredmessages) : 0), "\t",
                (($device->GetKoeLastAccess() && $device->GetKoeLastAccess() + 25260 < $device->GetLastSyncTime()) ? "KOE inactive":""), "\n";
            }
        }
    }

    /**
     * Returns an array with the folders stats of a device id:
     * - Total folders
     * - Synchronized folders
     * - Not synchronized folders
     * - Shared/impersonated folders
     *
     * @return
     * @access private
     */
    static private function ListDeviceFolders($deviceId, $user) {

        $device = ZPushAdmin::GetDeviceDetails($deviceId, $user, true);
        if (! $device instanceof ASDevice) {
            printf("Folder details failed: %s\n", ZLog::GetLastMessage(LOGLEVEL_ERROR));
            return false;
        }
        $folders = $device->GetAllFolderIds();
        $synchedFolders = 0;
        $notSynchedFolders = 0;
        $sharedFolders = 0;
        $hc = $device->GetHierarchyCache();
        foreach ($folders as $folderid) {
            if ($device->GetFolderUUID($folderid)) {
                $synchedFolders++;
            }
            else {
                $notSynchedFolders++;
            }
            $folder = $hc->GetFolder($folderid);
            $name = $folder ? $folder->displayname : "unknown";
            if (Utils::GetFolderOriginFromId($folderid) != DeviceManager::FLD_ORIGIN_USER) {
                $sharedFolders++;
            }
        }
        return array(count($folders), $synchedFolders, $notSynchedFolders, $sharedFolders);
    }

    /**
     * Command "Show users of device"
     * Prints informations about all users which use a device
     *
     * @return
     * @access public
     */
    static public function CommandDeviceUsers() {
        $users = ZPushAdmin::ListUsers(self::$device);

        if (empty($users)) {
            echo "\tno user data synchronized to device\n";
        }
        // if a user is specified, we only want to see the devices of this one
        else if (self::$user !== false && !in_array(self::$user, $users)) {
            printf("\tuser '%s' not known in device data '%s'\n", self::$user, self::$device);
        }

        foreach ($users as $user) {
            if (self::$user !== false && strtolower($user) !== self::$user) {
                continue;
            }
            echo "Synchronized by user: ". $user. "\n";
            self::printDeviceData(self::$device, $user);
        }
    }

    /**
     * Command "Wipe device"
     * Marks a device of that user to be remotely wiped
     *
     * @return
     * @access public
     */
    static public function CommandWipeDevice() {
        $stat = ZPushAdmin::WipeDevice($_SERVER["LOGNAME"], self::$user, self::$device);

        if (self::$user !== false && self::$device !== false) {
            echo sprintf("Mark device '%s' of user '%s' to be wiped: %s", self::$device, self::$user, ($stat)?'OK':ZLog::GetLastMessage(LOGLEVEL_ERROR)). "\n";

            if ($stat) {
                echo "Updated information about this device:\n";
                self::printDeviceData(self::$device, self::$user);
            }
        }
        elseif (self::$user !== false) {
            echo sprintf("Mark devices of user '%s' to be wiped: %s", self::$user, ($stat)?'OK':ZLog::GetLastMessage(LOGLEVEL_ERROR)). "\n";
            self::CommandShowDevices();
        }
    }

    /**
     * Command "Remove device".
     * Removes a device of that user from the device list.
     *
     * @return
     * @access public
     */
    static public function CommandRemoveDevice() {
        $stat = ZPushAdmin::RemoveDevice(self::$user, self::$device, self::$daysold, time());
        if (self::$user === false)
           echo sprintf("State data of device '%s' removed: %s", self::$device, ($stat)?'OK':ZLog::GetLastMessage(LOGLEVEL_ERROR)). "\n";
        elseif (self::$device === false)
           echo sprintf("State data of all devices of user '%s' removed: %s", self::$user, ($stat)?'OK':ZLog::GetLastMessage(LOGLEVEL_ERROR)). "\n";
        else
           echo sprintf("State data of device '%s' of user '%s' removed: %s", self::$device, self::$user, ($stat)?'OK':ZLog::GetLastMessage(LOGLEVEL_ERROR)). "\n";

        if (ZPushAdmin::$status == ZPushAdmin::STATUS_DEVICE_SYNCED_AFTER_DAYSOLD) {
            print("Some devices might not have been removed because of --days-old parameter. Check Z-Push log file for more details.\n");
        }
    }

    /**
     * Command "Resync device(s)"
     * Resyncs one or all devices of that user
     *
     * @return
     * @access public
     */
    static public function CommandResyncDevices() {
        $stat = ZPushAdmin::ResyncDevice(self::$user, self::$device);
        echo sprintf("Resync of device '%s' of user '%s': %s", self::$device, self::$user, ($stat)?'Requested':ZLog::GetLastMessage(LOGLEVEL_ERROR)). "\n";
    }

    /**
     * Command "Resync folder(s)"
     * Resyncs a folder type of a specific device/user or of all users
     *
     * @return
     * @access public
     */
    static public function CommandResyncFolder() {
        // if no device is specified, search for all devices of a user. If user is not set, all devices are returned.
        if (self::$device === false) {
            $devicelist = ZPushAdmin::ListDevices(self::$user);
            if (empty($devicelist)) {
                echo "\tno devices/users found\n";
                return true;
            }
        }
        else
            $devicelist = array(self::$device);

        foreach ($devicelist as $deviceId) {
            $users = ZPushAdmin::ListUsers($deviceId);
            foreach ($users as $user) {
                if (self::$user && self::$user != $user)
                    continue;
                self::resyncFolder($deviceId, $user, self::$type);
            }
        }

    }

    /**
     * Command "Resync hierarchy"
     * Resyncs a folder type of a specific device/user or of all users
     *
     * @return
     * @access public
     */
    static public function CommandResyncHierarchy() {
        // if no device is specified, search for all devices of a user. If user is not set, all devices are returned.
        if (self::$device === false) {
            $devicelist = ZPushAdmin::ListDevices(self::$user);
            if (empty($devicelist)) {
                echo "\tno devices/users found\n";
                return true;
            }
        }
        else
            $devicelist = array(self::$device);

        foreach ($devicelist as $deviceId) {
            $users = ZPushAdmin::ListUsers($deviceId);
            foreach ($users as $user) {
                if (self::$user && self::$user != $user)
                    continue;
                self::resyncHierarchy($deviceId, $user);
            }
        }

    }

    /**
     * Command to clear the loop detection data
     * Mobiles may enter loop detection (one-by-one synchring due to timeouts / erros).
     *
     * @return
     * @access public
     */
    static public function CommandClearLoopDetectionData() {
        $stat = false;
        $stat = ZPushAdmin::ClearLoopDetectionData(self::$user, self::$device);
        if (self::$user === false && self::$device === false)
           echo sprintf("System wide loop detection data removed: %s", ($stat)?'OK':ZLog::GetLastMessage(LOGLEVEL_ERROR)). "\n";
        elseif (self::$user === false)
           echo sprintf("Loop detection data of device '%s' removed: %s", self::$device, ($stat)?'OK':ZLog::GetLastMessage(LOGLEVEL_ERROR)). "\n";
        elseif (self::$device === false && self::$user !== false)
           echo sprintf("Error: %s", ($stat)?'OK':ZLog::GetLastMessage(LOGLEVEL_WARN)). "\n";
        else
           echo sprintf("Loop detection data of device '%s' of user '%s' removed: %s", self::$device, self::$user, ($stat)?'OK':ZLog::GetLastMessage(LOGLEVEL_ERROR)). "\n";
    }

    /**
     * Command to add a shared folder for a user.
     *
     * @return
     * @access public
     */
    static public function CommandAddShared() {
        // If no device is specified, search for all devices of a user.
        if (self::$device === false) {
            $devicelist = ZPushAdmin::ListDevices(self::$user);
            if (empty($devicelist)) {
                echo "\tno devices/users found\n";
                return true;
            }
        }
        else {
            $devicelist = array(self::$device);
        }
        switch (self::$type) {
            case self::TYPE_OPTION_EMAIL:
                $type = SYNC_FOLDER_TYPE_USER_MAIL;
                break;
            case self::TYPE_OPTION_CALENDAR:
                $type = SYNC_FOLDER_TYPE_USER_APPOINTMENT;
                break;
            case self::TYPE_OPTION_CONTACT:
                $type = SYNC_FOLDER_TYPE_USER_CONTACT;
                break;
            case self::TYPE_OPTION_TASK:
                $type = SYNC_FOLDER_TYPE_USER_TASK;
                break;
            case self::TYPE_OPTION_NOTE:
                $type = SYNC_FOLDER_TYPE_USER_NOTE;
                break;
        }

        foreach ($devicelist as $devid) {
            $status = ZPushAdmin::AdditionalFolderAdd(self::$user, $devid, self::$store, self::$folderid, self::$foldername, $type, self::$flags);
            if ($status) {
                printf("Successfully added folder '%s' for user '%s' on device '%s'.\n", self::$foldername, self::$user, $devid);
            }
            else {
                printf("Failed adding folder '%s' for user '%s' on device '%s'. %s.\n", self::$foldername, self::$user, $devid, ZLog::GetLastMessage(LOGLEVEL_ERROR));
            }
        }

    }

    /**
     * Command to remove a shared folder for a user.
     *
     * @return
     * @access public
     */
    static public function CommandRemoveShared() {
        // if no device is specified, search for all devices of a user. If user is not set, all devices are returned.
        if (self::$device === false) {
            $devicelist = ZPushAdmin::ListDevices(self::$user);
            if (empty($devicelist)) {
                echo "\tno devices/users found\n";
                return true;
            }
        }
        else {
            $devicelist = array(self::$device);
        }

        foreach ($devicelist as $devid) {
            $status = ZPushAdmin::AdditionalFolderRemove(self::$user, $devid, self::$folderid);
            if ($status) {
                printf("Successfully removed folder with id '%s' for user '%s' on device '%s'.\n", self::$folderid, self::$user, $devid);
            }
            else {
                printf("Failed removing folder with id '%s' for user '%s' on device '%s'. %s.\n", self::$folderid, self::$user, $devid, ZLog::GetLastMessage(LOGLEVEL_ERROR));
            }
        }

    }

    /**
     * Command to list all configured shares.
     *
     * @access public
     * @return void
     */
    static public function CommandListShares() {
        $devicelist = ZPushAdmin::ListDevices();
        if (empty($devicelist)) {
                echo "\tno devices/users found\n";
                return true;
        }
        $shares = array();
        $folderToStore = array();

        // It's necessary to always get all users and devices and then the shares of the device
        // as the shares are only available in the device.
        foreach ($devicelist as $deviceId) {
            $users = ZPushAdmin::ListUsers($deviceId);
            foreach ($users as $user) {
                $device = ZPushAdmin::GetDeviceDetails($deviceId, $user);
                if ($device instanceof ASDevice) {
                    $sharedFolders = $device->GetAdditionalFolders();
                    if (!empty($sharedFolders)) {
                        foreach ($sharedFolders as $sharedFolder) {
                            if (!isset($sharedFolder['store'], $sharedFolder['folderid'], $sharedFolder['name'])) {
                                printf("User '%s' has a shared folder configured on device '%s', but store, folderid, or name of this share are not set: %s", $user, $deviceId, print_r($sharedFolder, 1));
                                continue;
                            }
                            $folderToStore[$sharedFolder['folderid']] = strtolower($sharedFolder['store']);
                            $shares[strtolower($sharedFolder['store'])][$sharedFolder['folderid']][] = array('user' => $user, 'deviceId' => $deviceId, 'name' => $sharedFolder['name']);
                        }
                    }
                }
            }
        }


        if (empty($shares)) {
            print("There currently aren't any opened shares.\n");
            return;
        }

        if (self::$command == self::COMMAND_LISTFOLDERSHARES) {
            if (!isset($folderToStore[self::$folderid])) {
                printf("The folder with the requested id '%s' isn't currently opened by anyone.\n", self::$folderid);
                return;
            }
            printf("Displaying opened shares for folderid %s.\n", self::$folderid);
            self::printShares(array($folderToStore[self::$folderid] => array(self::$folderid => $shares[$folderToStore[self::$folderid]][self::$folderid])));
        }
        elseif (self::$command == self::COMMAND_LISTSTORESHARES) {
            $store = strtolower(self::$store);
            if (!isset($shares[$store])) {
                printf("None of the folders of the requested store '%s' is currently opened.\n", self::$store);
                return;
            }
            self::printShares(array($store => $shares[$store]));
        }
        else {
            print("Displaying opened shares of all users.\n");
            self::printShares($shares);
        }
    }

    /**
     * Command to list each folder and FOLDERID of user USER and device DEVICE.
     *
     * @access public
     * @return void
     */
    static public function CommandListFolders() {

        $device = ZPushAdmin::GetDeviceDetails(self::$device, self::$user, true);
        if (! $device instanceof ASDevice) {
            printf("Folder details failed: %s\n", ZLog::GetLastMessage(LOGLEVEL_ERROR));
            return false;
        }

        echo "Folders list of DeviceId: ".self::$device."\n";
        echo "-----------------------------------------------------------------------\n";
        $folders = $device->GetAllFolderIds();
        $synchedFolders = 0;
        $notSynchedFolders = 0;
        $sharedFolders = 0;
        $hc = $device->GetHierarchyCache();
        echo "FolderID\t\t\t\t\tShortID\tDisplay Name\n";
        echo "-----------------------------------------------------------------------\n";
        foreach ($folders as $folderid) {
            if ($device->GetFolderUUID($folderid)) {
                $synchedFolders++;
                $notSynced = '';
            }
            else {
                $notSynchedFolders++;
                $notSynced = "\t"."NOT SYNCHED";
            }
            $folder = $hc->GetFolder($folderid);
            $name = $folder ? $folder->displayname : "unknown";
            if (strcmp($name, 'unknown') == 0) {
                echo "\t\t\t\t\t";
            }
            echo $folder->BackendId."\t".$folderid."\t".$name.$notSynced."\n";
            if (Utils::GetFolderOriginFromId($folderid) != DeviceManager::FLD_ORIGIN_USER) {
                $sharedFolders++;
            }
        }
        echo "\nTotal folders: ".count($folders)."\n";
        echo "Synchronized folders: ".$synchedFolders."\n";
        echo "Not synchronized folders: ".$notSynchedFolders."\n";
        echo "Shared/impersonated folders: ".$sharedFolders."\n";
        echo "Short folder Ids: ". ($device->HasFolderIdMapping() ? "Yes":"No") ."\n";
    }

    /**
     * Resynchronizes a folder type of a device & user
     *
     * @param string    $deviceId       the id of the device
     * @param string    $user           the user
     * @param string    $type           the folder type
     *
     * @return
     * @access private
     */
    static private function resyncFolder($deviceId, $user, $type) {
        $device = ZPushAdmin::GetDeviceDetails($deviceId, $user);

        if (! $device instanceof ASDevice) {
            echo sprintf("Folder resync failed: %s\n", ZLog::GetLastMessage(LOGLEVEL_ERROR));
            return false;
        }

        $folders = array();
        $searchFor = $type;
        // get the KOE gab folderid
        if ($type == self::TYPE_OPTION_GAB) {
            if (@constant('KOE_GAB_FOLDERID') !== '') {
                $gab = KOE_GAB_FOLDERID;
            }
            else {
                $gab = $device->GetKoeGabBackendFolderId();
            }
            if (!$gab) {
                printf("Could not find KOE GAB folderid for device '%s' of user '%s'\n", $deviceId, $user);
                return false;
            }
            $searchFor = $gab;
        }
        // potential long ids are converted to folderids here, incl. the gab id
        $searchFor = strtolower($device->GetFolderIdForBackendId($searchFor, false, false, null));

        foreach ($device->GetAllFolderIds() as $folderid) {
            // if  submitting a folderid as type to resync a specific folder.
            if (strtolower($folderid) === $searchFor) {
                printf("Found and resynching requested folderid '%s' on device '%s' of user '%s'\n", $folderid, $deviceId, $user);
                $folders[] = $folderid;
                break;
            }

            if ($device->GetFolderUUID($folderid)) {
                $foldertype = $device->GetFolderType($folderid);
                switch($foldertype) {
                    case SYNC_FOLDER_TYPE_APPOINTMENT:
                    case SYNC_FOLDER_TYPE_USER_APPOINTMENT:
                        if ($searchFor == "calendar")
                            $folders[] = $folderid;
                        break;
                    case SYNC_FOLDER_TYPE_CONTACT:
                    case SYNC_FOLDER_TYPE_USER_CONTACT:
                        if ($searchFor == "contact")
                            $folders[] = $folderid;
                        break;
                    case SYNC_FOLDER_TYPE_TASK:
                    case SYNC_FOLDER_TYPE_USER_TASK:
                        if ($searchFor == "task")
                            $folders[] = $folderid;
                        break;
                    case SYNC_FOLDER_TYPE_NOTE:
                    case SYNC_FOLDER_TYPE_USER_NOTE:
                        if ($searchFor == "note")
                            $folders[] = $folderid;
                        break;
                    default:
                        if ($searchFor == "email")
                            $folders[] = $folderid;
                        break;
                }
            }
        }

        $stat = ZPushAdmin::ResyncFolder($user, $deviceId, $folders);
        echo sprintf("Resync of %d folders of type '%s' on device '%s' of user '%s': %s\n", count($folders), $type, $deviceId, $user, ($stat)?'Requested':ZLog::GetLastMessage(LOGLEVEL_ERROR));
    }

    /**
     * Resynchronizes the hierarchy of a device & user
     *
     * @param string    $deviceId       the id of the device
     * @param string    $user           the user
     *
     * @return
     * @access private
     */
    static private function resyncHierarchy($deviceId, $user) {
        $stat = ZPushAdmin::ResyncHierarchy($user, $deviceId);
        echo sprintf("Removing hierarchy information for resync on device '%s' of user '%s': %s\n",  $deviceId, $user, ($stat)?'Requested':ZLog::GetLastMessage(LOGLEVEL_ERROR));
    }

    /**
     * Fixes the states for potential issues.
     *
     * @param string    $username       the user
     *
     * @return
     * @access private
     */
    static private function CommandFixStates($username=false) {
        echo "Validating and fixing states (this can take some time):\n";
        if(!self::$devicedriven){

            echo "\t".date('H:i:s')." Checking username casings: ";
            if ($stat = ZPushAdmin::FixStatesDifferentUsernameCases($username))
                printf("Processed: %d - Converted: %d - Removed: %d\n", $stat[0], $stat[1], $stat[2]);
            else
                echo ZLog::GetLastMessage(LOGLEVEL_ERROR) . "\n";

            // fixes ZP-339
            echo "\t".date('H:i:s')." Checking available devicedata & user linking: ";
            if ($stat = ZPushAdmin::FixStatesDeviceToUserLinking($username))
                printf("Processed: %d - Fixed: %d\n", $stat[0], $stat[1]);
            else
                echo ZLog::GetLastMessage(LOGLEVEL_ERROR) . "\n";

            echo "\t".date('H:i:s')." Checking for unreferenced (obsolete) state files: ";
            if (($stat = ZPushAdmin::FixStatesUserToStatesLinking($username)) !== false)
                printf("Processed: %d - Deleted: %d\n",  $stat[0], $stat[1]);
            else
                echo ZLog::GetLastMessage(LOGLEVEL_ERROR) . "\n";

            echo "\t".date('H:i:s')." Checking for hierarchy folder data state: ";
            if (($stat = ZPushAdmin::FixStatesHierarchyFolderData($username)) !== false)
                printf("Devices: %d - Processed: %d - Fixed: %d - Device+User without hierarchy: %d\n",  $stat[0], $stat[1], $stat[2], $stat[3]);
            else
                echo ZLog::GetLastMessage(LOGLEVEL_ERROR) . "\n";

            echo "\t".date('H:i:s')." Checking flags of shared folders: ";
            if (($stat = ZPushAdmin::FixStatesAdditionalFolders($username)) !== false)
                printf("Devices: %d - Devices with additional folders: %d - Fixed: %d\n",  $stat[0], $stat[1], $stat[2]);
            else
                echo ZLog::GetLastMessage(LOGLEVEL_ERROR) . "\n";
        } else{
            //used for recording the total process time
            $fsStartTime= new DateTime("now");
            //load devices list
            $devices = ZPushAdmin::GetAllDevices();
            $devicesCount = count($devices);
            echo "Found ".$devicesCount." devices\r\n";
            $processedDevices = 0;
            //structure for hold stats
            $stats = array(
                // Results of ZPushAdmin::FixStatesDifferentUsernameCases
                array( "processed" => 0, "converted" => 0, "removed" => 0 ),
                // Results of ZPushAdmin::FixStatesDeviceToUserLinking
                array( "processed" => 0, "fixed" => 0 ),
                // Results of ZPushAdmin::FixStatesUserToStatesLinking
                array( "processed" => 0, "deleted" => 0 ),
                // Results of ZPushAdmin::FixStatesHierarchyFolderData
                array( "devices" => 0, "processed" => 0, "fixed" => 0, "noHierarchy" =>0 ),
                // Results of ZPushAdmin::FixStatesAdditionalFolders
                array( "devices" => 0, "devicesWithAddFolders" => 0, "fixed" => 0)
            );

            // loop every device
            foreach ($devices as $devid) {
                $processedDevices++;
                if (defined('LOGFIXSTATES') && LOGFIXSTATES === true) {
                    echo "\tProcessing ".$processedDevices."/".$devicesCount." devices: ".$devid."\r\n";
                    ZLog::Write(LOGLEVEL_INFO, sprintf("FixStatesDeviceDriven(): Processing %d of %d . Device %s", $processedDevices , $devicesCount, $devid));
                }

                if ($stat = ZPushAdmin::FixStatesDifferentUsernameCases(false,$devid)){
                    $stats[0]["processed"] += $stat[0];
                    $stats[0]["converted"] += $stat[1];
                    $stats[0]["removed"] += $stat[2];
                }
                else
                    echo ZLog::GetLastMessage(LOGLEVEL_ERROR) . "\n";

                // fixes ZP-339
                if ($stat = ZPushAdmin::FixStatesDeviceToUserLinking(false,$devid)){
                    $stats[1]["processed"] += $stat[0];
                    $stats[1]["fixed"] += $stat[1];
                }
                else
                    echo ZLog::GetLastMessage(LOGLEVEL_ERROR) . "\n";

                if (($stat = ZPushAdmin::FixStatesUserToStatesLinking(false,$devid)) !== false){
                    $stats[2]["processed"] += $stat[0];
                    $stats[2]["deleted"] += $stat[1];
                }
                else
                    echo ZLog::GetLastMessage(LOGLEVEL_ERROR) . "\n";

                if (($stat = ZPushAdmin::FixStatesHierarchyFolderData(false,$devid)) !== false){
                    $stats[3]["devices"] += $stat[0];
                    $stats[3]["processed"] += $stat[1];
                    $stats[3]["fixed"] += $stat[2];
                    $stats[3]["noHierarchy"] += $stat[3];
                }
                else
                    echo ZLog::GetLastMessage(LOGLEVEL_ERROR) . "\n";

                if (($stat = ZPushAdmin::FixStatesAdditionalFolders(false,$devid)) !== false){
                    $stats[4]["devices"] += $stat[0];
                    $stats[4]["devicesWithAddFolders"] += $stat[1];
                    $stats[4]["fixed"] += $stat[2];
                }
                else
                    echo ZLog::GetLastMessage(LOGLEVEL_ERROR) . "\n";
            }
            //used for recording the total process time
            $fsEndTime = new DateTime("now");
            $timeInterval = date_diff($fsStartTime, $fsEndTime)->format('%H:%I:%S');
            echo "Total process time: ".$timeInterval."\n"."\n";
            ZLog::Write(LOGLEVEL_INFO, sprintf("FixStatesDeviceDriven(): Finished. Total process time: ".$timeInterval));
            printf("Check username casings. Processed: %d - Converted: %d - Removed: %d\n",
                $stats[0]["processed"], $stats[0]["converted"], $stats[0]["removed"] );
            printf("Check available devicedata & user linking. Processed: %d - Fixed: %d\n",
                $stats[1]["processed"], $stats[1]["fixed"] );
            printf("Check for unreferenced (obsolete) state files. Processed: %d - Deleted: %d\n",
                $stats[2]["processed"], $stats[2]["deleted"] );
            printf("Check for hierarchy folder data state. Devices: %d - Processed: %d - Fixed: %d - Device+User without hierarchy: %d\n",
                $stats[3]["devices"], $stats[3]["processed"], $stats[3]["fixed"], $stats[3]["noHierarchy"] );
            printf("Check flags of shared folders. Devices: %d - Devices with additional folders: %d - Fixed: %d\n",
                $stats[4]["devices"], $stats[4]["devicesWithAddFolders"], $stats[4]["fixed"] );
        }
    }

    /**
     * Prints detailed informations about a device
     *
     * @param string    $deviceId       the id of the device
     * @param string    $user           the user
     *
     * @return
     * @access private
     */
    static private function printDeviceData($deviceId, $user) {
        global $additionalFolders;
        $device = ZPushAdmin::GetDeviceDetails($deviceId, $user, true);

        if (! $device instanceof ASDevice) {
            echo sprintf("Folder resync failed: %s\n", ZLog::GetLastMessage(LOGLEVEL_ERROR));
            return false;
        }

        echo "-----------------------------------------------------\n";
        echo "DeviceId:\t\t$deviceId\n";
        echo "Device type:\t\t". ($device->GetDeviceType() !== ASDevice::UNDEFINED ? $device->GetDeviceType() : "unknown") ."\n";
        echo "UserAgent:\t\t".($device->GetDeviceUserAgent()!== ASDevice::UNDEFINED ? $device->GetDeviceUserAgent() : "unknown") ."\n";
        // TODO implement $device->GetDeviceUserAgentHistory()

        if (!self::$shared) {
            // Gather some statistics about synchronized folders
            $folders = $device->GetAllFolderIds();
            $synchedFolders = 0;
            $synchedFolderTypes = array();
            $syncedFoldersInProgress = 0;
            $hc = $device->GetHierarchyCache();
            foreach ($folders as $folderid) {
                if ($device->GetFolderUUID($folderid)) {
                    $synchedFolders++;
                    $type = $device->GetFolderType($folderid);
                    $folder = $hc->GetFolder($folderid);
                    $name = $folder ? $folder->displayname : "unknown";
                    switch($type) {
                        case SYNC_FOLDER_TYPE_APPOINTMENT:
                        case SYNC_FOLDER_TYPE_USER_APPOINTMENT:
                            if ($name == KOE_GAB_NAME) {
                                $gentype = "GAB";
                            }
                            else {
                                $gentype = "Calendars";
                            }
                            break;
                        case SYNC_FOLDER_TYPE_CONTACT:
                        case SYNC_FOLDER_TYPE_USER_CONTACT:
                            $gentype = "Contacts";
                            break;
                        case SYNC_FOLDER_TYPE_TASK:
                        case SYNC_FOLDER_TYPE_USER_TASK:
                            $gentype = "Tasks";
                            break;
                        case SYNC_FOLDER_TYPE_NOTE:
                        case SYNC_FOLDER_TYPE_USER_NOTE:
                            $gentype = "Notes";
                            break;
                        default:
                            $gentype = "Emails";
                            break;
                    }
                    if (!isset($synchedFolderTypes[$gentype]))
                        $synchedFolderTypes[$gentype] = 0;
                    $synchedFolderTypes[$gentype]++;

                    // set the folder name for all folders which are not fully synchronized yet
                    $fstatus = $device->GetFolderSyncStatus($folderid);
                    if ($fstatus !== false && is_array($fstatus)) {
                        $fstatus['name'] = $name ? $name : $gentype;
                        $device->SetFolderSyncStatus($folderid, $fstatus);
                        $syncedFoldersInProgress++;
                    }
                }
            }
            $folderinfo = "";
            foreach ($synchedFolderTypes as $gentype=>$count) {
                $folderinfo .= $gentype;
                if ($count>1) $folderinfo .= "($count)";
                $folderinfo .= " ";
            }
            if (!$folderinfo) $folderinfo = "None available";

            // device information transmitted during Settings command
            if ($device->GetDeviceModel())
                echo "Device Model:\t\t". $device->GetDeviceModel(). "\n";
            if ($device->GetDeviceIMEI())
                echo "Device IMEI:\t\t". $device->GetDeviceIMEI(). "\n";
            if ($device->GetDeviceFriendlyName())
                echo "Device friendly name:\t". $device->GetDeviceFriendlyName(). "\n";
            if ($device->GetDeviceOS())
                echo "Device OS:\t\t". $device->GetDeviceOS(). "\n";
            if ($device->GetDeviceOSLanguage())
                echo "Device OS Language:\t". $device->GetDeviceOSLanguage(). "\n";
            if ($device->GetDevicePhoneNumber())
                echo "Device Phone nr:\t". $device->GetDevicePhoneNumber(). "\n";
            if ($device->GetDeviceMobileOperator())
                echo "Device Operator:\t". $device->GetDeviceMobileOperator(). "\n";
            if ($device->GetDeviceEnableOutboundSMS())
                echo "Device Outbound SMS:\t". $device->GetDeviceEnableOutboundSMS(). "\n";

            echo "ActiveSync version:\t".($device->GetASVersion() ? $device->GetASVersion() : "unknown") ."\n";
            echo "First sync:\t\t". strftime("%Y-%m-%d %H:%M", $device->GetFirstSyncTime()) ."\n";
            echo "Last sync:\t\t". ($device->GetLastSyncTime() ? strftime("%Y-%m-%d %H:%M", $device->GetLastSyncTime()) : "never")."\n";


            $filterType = (defined('SYNC_FILTERTIME_MAX') && SYNC_FILTERTIME_MAX > SYNC_FILTERTYPE_ALL) ? SYNC_FILTERTIME_MAX : SYNC_FILTERTYPE_ALL;
            $maxDevice = $device->GetSyncFilterType();
            if ($maxDevice !== false && $maxDevice > SYNC_FILTERTYPE_ALL && ($filterType == SYNC_FILTERTYPE_ALL || $maxDevice < $filterType)) {
                $filterType = $maxDevice;
            }
            switch($filterType) {
                case SYNC_FILTERTYPE_1DAY:
                    $filterTypeString = "1 day back";
                    break;
                case SYNC_FILTERTYPE_3DAYS:
                    $filterTypeString = "3 days back";
                    break;
                case SYNC_FILTERTYPE_1WEEK:
                    $filterTypeString = "1 week back";
                    break;
                case SYNC_FILTERTYPE_2WEEKS:
                    $filterTypeString = "2 weeks back";
                    break;
                case SYNC_FILTERTYPE_1MONTH:
                    $filterTypeString = "1 month back";
                    break;
                case SYNC_FILTERTYPE_3MONTHS:
                    $filterTypeString = "3 months back";
                    break;
                case SYNC_FILTERTYPE_6MONTHS:
                    $filterTypeString = "6 months back";
                    break;
                default:
                    $filterTypeString = "unlimited";
            }
            echo "Sync Period:\t\t". $filterTypeString . " (".$filterType.")\n";
            echo "Total folders:\t\t". count($folders). "\n";
            echo "Short folder Ids:\t". ($device->HasFolderIdMapping() ? "Yes":"No") ."\n";
            echo "Synchronized folders:\t". $synchedFolders;
            if ($syncedFoldersInProgress > 0)
                echo " (". $syncedFoldersInProgress. " in progress)";
            echo "\n";
            echo "Synchronized data:\t$folderinfo\n";
            if ($syncedFoldersInProgress > 0) {
                echo "Synchronization progress:\n";
                foreach ($folders as $folderid) {
                    $d = $device->GetFolderSyncStatus($folderid);
                    if ($d) {
                        $status = "";
                        if ($d['total'] > 0) {
                            $percent = round($d['done']*100/$d['total']);
                            $status = sprintf("Status: %s%d%% (%d/%d)", ($percent < 10)?" ":"", $percent, $d['done'], $d['total']);
                        }
                        if (strlen($d['name']) > 20) {
                            $d['name'] = substr($d['name'], 0, 18) . "..";
                        }
                        printf("\tFolder: %s Sync: %s %s\n", str_pad($d['name'], 20), str_pad($d['status'], 13), $status);
                    }
                }
            }
        }
        // additional folders
        $addFolders = array();
        $sharedFolders = $device->GetAdditionalFolders();
        array_walk($sharedFolders, function (&$key) { $key["origin"] = 'Shared'; });
        // $additionalFolders comes directly from the config
        array_walk($additionalFolders, function (&$key) { $key["origin"] = 'Configured'; });
        foreach(array_merge($additionalFolders,$sharedFolders) as $df) {
            $df['additional'] = '';
            $syncfolderid = $device->GetFolderIdForBackendId($df['folderid'], false, false, null);
            switch($df['type']) {
                case SYNC_FOLDER_TYPE_USER_APPOINTMENT:
                    if ($df['name'] == KOE_GAB_NAME) {
                        $gentype = "GAB";
                    }
                    else {
                        $gentype = "Calendar";
                    }
                    break;
                case SYNC_FOLDER_TYPE_USER_CONTACT:
                    $gentype = "Contact";
                    break;
                case SYNC_FOLDER_TYPE_USER_TASK:
                    $gentype = "Task";
                    break;
                case SYNC_FOLDER_TYPE_USER_NOTE:
                    $gentype = "Note";
                    break;
                default:
                    $gentype = "Email";
                    break;
            }
            if ($device->GetFolderType($syncfolderid) == SYNC_FOLDER_TYPE_UNKNOWN) {
                $df['additional'] = "(KOE patching incomplete)";
            }
            $df['type'] = $gentype;
            $df['synched'] = ($device->GetFolderUUID($syncfolderid)) ? 'Active' : 'Inactive (not yet synchronized or no permissions)';
            $addFolders[] = $df;
        }
        $addFoldersTotal = !empty($addFolders) ? count($addFolders) : 'none';
        echo "Additional Folders:\t$addFoldersTotal\n";
        if ($addFoldersTotal != 'none') {
            if (!self::$shared) {
                print("\tFolder name                    Store          Type     Origin     Synched\n");
            }
        }
        foreach ($addFolders as $folder) {
            // Configured folders are always under root
            if (!isset($folder['parentid'])) $folder['parentid'] = '0';

            if (!self::$shared) {
                if (strlen($folder['store']) > 14) {
                    $folder['store'] = substr($folder['store'], 0, 12) . "..";
                }
                if (strlen($folder['name']) > 30) {
                    $folder['name'] = substr($folder['name'], 0, 28) . "..";
                }
                printf("\t%s %s %s %s %s %s\n", str_pad($folder['name'], 30), str_pad($folder['store'], 14), str_pad($folder['type'], 8), str_pad($folder['origin'], 10), $folder['synched'], $folder['additional']);
            }
            else {
                printf("\tFolder name:\t%s\n", $folder['name']);
                printf("\tStore:\t\t%s\n", $folder['store']);
                printf("\tType:\t\t%s\n", $folder['type']);
                printf("\tOrigin:\t\t%s\n", $folder['origin']);
                printf("\tFolder id:\t%s\n", $folder['folderid']);
                printf("\tParent id:\t%s\n", $folder['parentid']);
                printf("\tSynched:\t%s\n", $folder['synched']);
                if (!empty($folder['additional'])) printf("\tAdditional:\t%s\n", $folder['additional']);
                echo "\t------------------------\n";
            }
        }

        if (!self::$shared) {
            echo "Status:\t\t\t";
            switch ($device->GetWipeStatus()) {
                case SYNC_PROVISION_RWSTATUS_OK:
                    echo "OK\n";
                    break;
                case SYNC_PROVISION_RWSTATUS_PENDING:
                    echo "Pending wipe\n";
                    break;
                case SYNC_PROVISION_RWSTATUS_REQUESTED:
                    echo "Wipe requested on device\n";
                    break;
                case SYNC_PROVISION_RWSTATUS_WIPED:
                    echo "Wiped\n";
                    break;
                default:
                    echo "Not available\n";
                    break;
            }
            echo "WipeRequest on:\t\t". ($device->GetWipeRequestedOn() ? strftime("%Y-%m-%d %H:%M", $device->GetWipeRequestedOn()) : "not set")."\n";
            echo "WipeRequest by:\t\t". ($device->GetWipeRequestedBy() ? $device->GetWipeRequestedBy() : "not set")."\n";
            echo "Wiped on:\t\t". ($device->GetWipeActionOn() ? strftime("%Y-%m-%d %H:%M", $device->GetWipeActionOn()) : "not set")."\n";
            echo "Policy name:\t\t". ($device->GetPolicyName() ? $device->GetPolicyName() : ASDevice::DEFAULTPOLICYNAME)."\n";
        }

        if ($device->GetKoeVersion()) {
            echo "Kopano Outlook Extension:\n";
            echo "\tVersion:\t". $device->GetKoeVersion() ."\n";
            echo "\tBuild:\t\t". $device->GetKoeBuild() ."\n";
            echo "\tBuild Date:\t". strftime("%Y-%m-%d %H:%M", $device->GetKoeBuildDate()) ."\n";
            echo "\tCapabilities:\t". (count($device->GetKoeCapabilities()) ? implode(',', $device->GetKoeCapabilities()) : 'unknown') ."\n";
            echo "\tLast access:\t". ($device->GetKoeLastAccess() ? strftime("%Y-%m-%d", $device->GetKoeLastAccess()) : 'unknown') ."\n";
        }

        echo "Attention needed:\t";

        if ($device->GetDeviceError()) {
            echo $device->GetDeviceError() ."\n";
        }
        // if KOE's access time is older than 7:01 h than the last successful sync it's probably inactive
        elseif ($device->GetKoeLastAccess() && $device->GetKoeLastAccess() + 25260 < $device->GetLastSyncTime()) {
            echo "KOE seems to be inactive on client\n";
        }
        if (!isset($device->ignoredmessages) || empty($device->ignoredmessages)) {
            echo "No errors known\n";
        }
        elseif (!self::$shared) {
            printf("%d messages need attention because they could not be synchronized\n", count($device->ignoredmessages));
            foreach ($device->ignoredmessages as $im) {
                $info = "";
                if (isset($im->asobject->subject))
                    $info .= sprintf("Subject: '%s'", $im->asobject->subject);
                if (isset($im->asobject->fileas))
                    $info .= sprintf("FileAs: '%s'", $im->asobject->fileas);
                if (isset($im->asobject->from))
                    $info .= sprintf(" - From: '%s'", $im->asobject->from);
                if (isset($im->asobject->starttime))
                    $info .= sprintf(" - On: '%s'", strftime("%Y-%m-%d %H:%M", $im->asobject->starttime));
                $reason = $im->reasonstring;
                if ($im->reasoncode == 2)
                    $reason = "Message was causing loop";
                printf("\tBroken object:\t'%s' ignored on '%s'\n", $im->asclass,  strftime("%Y-%m-%d %H:%M", $im->timestamp));
                printf("\tInformation:\t%s\n", $info);
                printf("\tReason: \t%s (%s)\n", $reason, $im->reasoncode);
                printf("\tItem/Parent id: %s/%s\n", $im->id, $im->folderid);
                echo "\n";
            }
        }
        else {
            print("There are some messages which need attention because they could not be synchronized. Run z-push-admin without -s or --shared.\n");
        }

    }

    /**
     * Prints information about opened shares.
     *
     * @param array $shares
     *
     * @access private
     * @return void
     */
    static private function printShares($shares) {
        $dashes = str_repeat('-', 145);
        foreach ($shares as $user => $userShares) {
            printf("Shares of user %s\n\n", $user);

            printf("%s\n%-30s %-48s %-30s Device id\n%s", $dashes, "Foldername", "Folder id", "Username", $dashes);
            foreach ($userShares as $folderid => $folderShares) {
                foreach ($folderShares as $share) {
                    if (strlen($share['name']) > 26) {
                       $share['name'] = substr($share['name'], 0, 26) . '...';
                    }
                    printf("\n%-30s %-48s %-30s %-10s", $share['name'], $folderid, $share['user'], $share['deviceId']);
                }
            }
            printf("\n%s\n\n", $dashes);
        }
    }
}
