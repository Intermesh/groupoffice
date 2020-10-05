#!/usr/bin/env php
<?php
/***********************************************
* File      :   checkshares.php
* Project   :   Z-Push
* Descr     :   This is a small command line
*               tool to check if configured shares
*               for a user are still valid.
*
* Created   :   18.05.2018
*
* Copyright 2007 - 2018 Zarafa Deutschland GmbH
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

define('BASE_PATH_CLI',  __DIR__ . "/../../");
set_include_path(get_include_path() . PATH_SEPARATOR . BASE_PATH_CLI);
require_once ('vendor/autoload.php');
if (!defined('ZPUSH_CONFIG')) define('ZPUSH_CONFIG', BASE_PATH_CLI . 'config.php');
include_once(ZPUSH_CONFIG);
if (!defined('LOGBACKEND_CLASS')) define('LOGBACKEND_CLASS', 'FileLog');

require(__DIR__ . '/mapi/mapi.util.php');
require(__DIR__ . '/mapi/mapidefs.php');
require(__DIR__ . '/mapi/mapicode.php');
require(__DIR__ . '/mapi/mapitags.php');
require(__DIR__ . '/mapi/mapiguid.php');

$check = new CheckShares();
$check->Run();

class CheckShares {
    const SYSTEMUSER = 'system';

    private $server = null;
    private $sslCertFile = null;
    private $sslCertPass = null;
    private $session = null;
    private $logonUser = self::SYSTEMUSER;
    private $password = null;
    private $defaultstore = null;
    private $sharesUser = false;
    private $stores = array();
    private $invalidShares = array();

    /**
     * Constructor.
     *
     * @access public
     */
    public function __construct() {

        if ($this->checkMapiExtVersion('8.0.0')) {
            $this->server = 'default:';
        }
        elseif ($this->checkMapiExtVersion('7.2.0')) {
            $this->server = 'file:///var/run/zarafad/server.sock';
        }
        else {
            $this->server = 'file:///var/run/zarafa';
        }
    }

    /**
     * Main function which calls all other functions.
     *
     * @access public
     * @return void
     */
    public function Run() {
        $this->processArgs();
        $this->logonAndOpenDefaultStore();
        $this->findInvalidShares();
        $this->printInvalidShares();
    }

    /**
     * Finds invalid shares by trying to open the configured shared folder.
     *
     * @access private
     * @return void
     */
    private function findInvalidShares() {
        $devicelist = ZPushAdmin::ListDevices($this->sharesUser);
        if (empty($devicelist)) {
            print("\tno devices/users found\n");
            exit(0);
        }

        foreach ($devicelist as $deviceId) {
            $users = ZPushAdmin::ListUsers($deviceId);
            foreach ($users as $deviceUser) {
                if ($this->sharesUser && strcasecmp($this->sharesUser, $deviceUser) != 0) {
                    continue;
                }
                $device = ZPushAdmin::GetDeviceDetails($deviceId, $deviceUser);
                if ($device instanceof ASDevice) {
                    $sharedFolders = $device->GetAdditionalFolders();
                    foreach ($sharedFolders as $sharedFolder) {
                        if (!isset($sharedFolder['store'], $sharedFolder['folderid'], $sharedFolder['name'])) {
                            printf("User '%s' has a shared folder configured on device '%s', but store, folderid, or name of this share are not set: %s", $this->sharesUser, $deviceId, print_r($sharedFolder, 1));
                            continue;
                        }
                        // If the folder is already in invalid shares, it couldn't be opened for another user and though can be ignored
                        if (isset($this->invalidShares[$sharedFolder['store']]) && array_key_exists($sharedFolder['folderid'], $this->invalidShares[$sharedFolder['store']])) {
                            if (!in_array(
                                    array('user' => $deviceUser, 'name' => $sharedFolder['name']),
                                    $this->invalidShares[$sharedFolder['store']][$sharedFolder['folderid']]['users'])) {
                                $this->invalidShares[$sharedFolder['store']][$sharedFolder['folderid']]['users'][] = array('user' => $deviceUser, 'name' => $sharedFolder['name']);
                            }
                            continue;
                        }
                        $getPublic = (strtolower($sharedFolder['store']) == self::SYSTEMUSER) ? true : false;
                        $deviceUserStore = $this->openMessageStore($sharedFolder['store'], $getPublic);
                        if ($deviceUserStore !== null) {
                            $entryid = mapi_msgstore_entryidfromsourcekey($deviceUserStore, hex2bin($sharedFolder['folderid']));
                            if (mapi_last_hresult() == MAPI_E_NO_ACCESS) {
                                $this->invalidShares[$sharedFolder['store']][$sharedFolder['folderid']] = array('users' => array(array('user' => $deviceUser, 'name' => $sharedFolder['name'])));
                                continue;
                            }
                            $folder = mapi_msgstore_openentry($deviceUserStore, $entryid);
                            if (!$folder) {
                                $this->invalidShares[$sharedFolder['store']][$sharedFolder['folderid']] = array('users' => array(array('user' => $deviceUser, 'name' => $sharedFolder['name'])));
                            }
                        }
                        else {
                            $this->invalidShares[$sharedFolder['store']][$sharedFolder['folderid']] = array('users' => array(array('user' => $deviceUser, 'name' => $sharedFolder['name'])));
                        }
                    }
                }
            }
        }
    }

    /**
     * Nice output for the found invalid shares.
     *
     * @access private
     * @return void
     */
    private function printInvalidShares() {
        if (empty($this->invalidShares)) {
            print("No invalid shares found. All good.\n");
            exit(0);
        }

        print("Invalid configured shares:\n");
        printf("%-30s %-30s %-48s %s\n", "Store", "Username", "Foldername", "Folder id");
        foreach ($this->invalidShares as $store => $invalidFolders) {
            foreach ($invalidFolders as $folderId => $invalidShare) {
                foreach ($invalidShare['users'] as $shareUsers) {
                    printf("%-30s %-30s %-48s %s\n", $store, $shareUsers['user'], $shareUsers['name'], $folderId);
                }
            }
        }
    }

    /**
     * Logons into kopano and opens the default store.
     *
     * @access private
     * @return void
     */
    private function logonAndOpenDefaultStore() {
        $this->session = @mapi_logon_zarafa($this->logonUser, $this->password, $this->server, $this->sslCertFile, $this->sslCertPass);

        if (!$this->session) {
            printf("User '%s' could not login. The script will exit. Errorcode: 0x%08X\n", $this->logonUser, mapi_last_hresult());
            exit(1);
        }

        $this->defaultstore = $this->openMessageStore($this->logonUser);
    }

    /**
     * Returns the private store of the user or the public store.
     *
     * @param string    $user               User whose store should be opened
     * @param boolean   $getPublic          Whether to open public store
     *
     * @access private
     * @return MAPIStore
     */
    private function openMessageStore($user, $getPublic = false) {
        $luser = strtolower($user);
        // There are 3 possible cases:
        //  1. Regular user which store has already been opened - simply return
        //  2. Public store - it's cached under 'public' key of the SYSTEMUSER store in stores array
        //  3. SYSTEMUSER store - it's cached under 'default' key of the SYSTEMUSER store in stores array
        if (isset($this->stores[$luser])) {
            if ($luser != self::SYSTEMUSER) {
                return $this->stores[$luser];
            }
            if ($luser == self::SYSTEMUSER && isset($this->stores[$luser]['public']) && $getPublic) {
                return $this->stores[$luser]['public'];
            }
            if ($luser == self::SYSTEMUSER && isset($this->stores[$luser]['default']) && !$getPublic) {
                return $this->stores[$luser]['default'];
            }
        }

        if (strcasecmp($user, $this->logonUser) == 0 || $getPublic === true) {
            $storestables = mapi_getmsgstorestable($this->session);
            $result = mapi_last_hresult();

            if ($result == NOERROR){
                $rows = mapi_table_queryallrows($storestables, array(PR_ENTRYID, PR_DEFAULT_STORE, PR_MDB_PROVIDER));

                foreach($rows as $row) {
                    if (!$getPublic && isset($row[PR_DEFAULT_STORE]) && $row[PR_DEFAULT_STORE] == true) {
                        $entryid = $row[PR_ENTRYID];
                        break;
                    }
                    if ($getPublic && isset($row[PR_MDB_PROVIDER]) && $row[PR_MDB_PROVIDER] == ZARAFA_STORE_PUBLIC_GUID) {
                        $entryid = $row[PR_ENTRYID];
                        break;
                    }
                }
            }
        }
        else {
            $entryid = @mapi_msgstore_createentryid($this->defaultstore, $user);
        }

        if ($entryid) {
            $store = @mapi_openmsgstore($this->session, $entryid);

            if (!$store) {
                printf("Could not open store of '%s'\n", $this->logonUser);
                return null;
            }

            // add this store to the cache
            if ($luser != self::SYSTEMUSER) {
                $this->stores[$luser] = $store;
            }
            elseif ($luser == self::SYSTEMUSER && $getPublic) {
                $this->stores[$luser]['public'] = $store;
            }
            elseif ($luser == self::SYSTEMUSER && !$getPublic) {
                $this->stores[$luser]['default'] = $store;
            }
            return $store;
        }
        printf("No store found for the user '%s'\n", $user);
        return null;
    }

    /**
     * Processes the script parameters.
     *
     * @access private
     * @return void
     */
    private function processArgs() {
        $options = getopt('hu:s:U:P:C:W:', array('help', 'user:', 'server:', 'remoteuser:', 'password:', 'certpath:', 'certpassword:'));

        if (isset($options['h']) || isset($options['help'])) {
            $this->printUsage();
        }

        if (!empty($options['u'])) {
            $this->sharesUser = strtolower($options['u']);
        }
        elseif (!empty($options['user'])) {
            $this->sharesUser = strtolower($options['user']);
        }

        if (!empty($options['s'])) {
            $this->server = $options['s'];
        }
        elseif (!empty($options['server'])) {
            $this->server = $options['server'];
        }

        if (!empty($options['U'])) {
            $this->logonUser = $options['U'];
        }
        elseif (!empty($options['remoteuser'])) {
            $this->logonUser = $options['remoteuser'];
        }

        if (!empty($options['P'])) {
            $this->password = $options['P'];
        }
        elseif (!empty($options['password'])) {
            $this->password = $options['password'];
        }

        if (!empty($options['C'])) {
            $this->sslCertFile = $options['C'];
        }
        elseif (!empty($options['certpath'])) {
            $this->sslCertFile = $options['certpath'];
        }

        if (!empty($options['W'])) {
            $this->sslCertPass = $options['W'];
        }
        elseif (!empty($options['certpassword'])) {
            $this->sslCertPass = $options['certpassword'];
        }
    }

    /**
     * Prints the usage instructions for the script.
     *
     * @access private
     * @return void
     */
    private function printUsage() {
        $usage =
<<<USAGE
checkshares.php [OPTIONS] Checks if there are invalid shares for the provided user or all users if no user was provided.
Available options:
-h|--help           - print this help text and exit.
-u|--user user      - check for invalid shares for this user.
-s|--server         - KC server location URI or path, e.g http://localhost:236 or default:.
-U|--remoteuser     - login as authenticated administration user.
-P|--password       - password for the administration user.
-C|--certpath       - login with a ssl certificate located in this location, e.g. /etc/kopano/ssl/client.pem.
-W|--certpassword   - password for the ssl certificate for login.

USAGE;
        print($usage);
        exit(0);
    }

    /**
     * Checks if requested PHP-MAPI version is higher than installed.
     *
     * @param string $version
     *
     * @access private
     * @return boolean
     */
    private function checkMapiExtVersion($version = "") {
        // compare build number if requested
        if (preg_match('/^\d+$/', $version) && strlen($version) > 3) {
            $vs = preg_split('/-/', phpversion("mapi"));
            return ($version <= $vs[1]);
        }

        if (!extension_loaded("mapi") || version_compare(phpversion("mapi"), $version) == -1) {
            return false;
        }

        return true;
    }
}