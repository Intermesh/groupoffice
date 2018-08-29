<?php
/***********************************************
* File      :   webservicedevice.php
* Project   :   Z-Push
* Descr     :   Device remote administration tasks
*               used over webservice e.g. by the
*               Mobile Device Management Plugin for Kopano.
*
* Created   :   23.12.2011
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

class WebserviceDevice {

    /**
     * Returns a list of all known devices of the requested user.
     *
     * @access public
     * @return array
     */
    public function ListDevicesDetails() {
        $user = Request::GetImpersonatedUser() ? Request::GetImpersonatedUser() : Request::GetGETUser();
        $devices = ZPushAdmin::ListDevices($user);
        $output = array();

        ZLog::Write(LOGLEVEL_INFO, sprintf("WebserviceDevice::ListDevicesDetails(): found %d devices of user '%s'", count($devices), $user));
        ZPush::GetTopCollector()->AnnounceInformation(sprintf("Retrieved details of %d devices", count($devices)), true);

        foreach ($devices as $devid)
            $output[] = ZPushAdmin::GetDeviceDetails($devid, $user);

        return $output;
    }

    /**
     * Returns the details of a given deviceid of the requested user.
     *
     * @param boolean   $withHierarchyCache (opt) includes the HierarchyCache - default: false
     *
     * @access public
     * @return ASDevice object
     */
    public function GetDeviceDetails($deviceId, $withHierarchyCache = false) {
        $user = Request::GetImpersonatedUser() ? Request::GetImpersonatedUser() : Request::GetGETUser();
        $deviceId = preg_replace("/[^A-Za-z0-9]/", "", $deviceId);
        ZLog::Write(LOGLEVEL_INFO, sprintf("WebserviceDevice::GetDeviceDetails('%s'): getting device details from state of user '%s'", $deviceId, $user));

        ZPush::GetTopCollector()->AnnounceInformation(sprintf("Retrieved details of device '%s'", $deviceId), true);
        return ZPushAdmin::GetDeviceDetails($deviceId, $user, $withHierarchyCache);
    }

    /**
     * Remove all state data for a device of the requested user.
     *
     * @param string    $deviceId       the device id
     *
     * @access public
     * @return boolean
     * @throws SoapFault
     */
    public function RemoveDevice($deviceId) {
        $user = Request::GetImpersonatedUser() ? Request::GetImpersonatedUser() : Request::GetGETUser();
        $deviceId = preg_replace("/[^A-Za-z0-9]/", "", $deviceId);
        ZLog::Write(LOGLEVEL_INFO, sprintf("WebserviceDevice::RemoveDevice('%s'): remove device state data of user '%s'", $deviceId, $user));

        if (! ZPushAdmin::RemoveDevice($user, $deviceId)) {
            ZPush::GetTopCollector()->AnnounceInformation(ZLog::GetLastMessage(LOGLEVEL_ERROR), true);
            throw new SoapFault("ERROR", ZLog::GetLastMessage(LOGLEVEL_ERROR));
        }

        ZPush::GetTopCollector()->AnnounceInformation(sprintf("Removed device id '%s'", $deviceId), true);
        return true;
    }

    /**
     * Marks a device of the requested user to be remotely wiped.
     *
     * @param string    $deviceId       the device id
     *
     * @access public
     * @return boolean
     * @throws SoapFault
     */
    public function WipeDevice($deviceId) {
        if (Request::GetImpersonatedUser()) {
            throw new SoapFault("ERROR", "Impersonated user is not allowed to wipe devices.");
        }
        $deviceId = preg_replace("/[^A-Za-z0-9]/", "", $deviceId);
        $user = Request::GetGETUser();
        ZLog::Write(LOGLEVEL_INFO, sprintf("WebserviceDevice::WipeDevice('%s'): mark device of user '%s' for remote wipe", $deviceId, $user));

        if (! ZPushAdmin::WipeDevice(Request::GetAuthUser(), $user, $deviceId)) {
            ZPush::GetTopCollector()->AnnounceInformation(ZLog::GetLastMessage(LOGLEVEL_ERROR), true);
            throw new SoapFault("ERROR", ZLog::GetLastMessage(LOGLEVEL_ERROR));
        }

        ZPush::GetTopCollector()->AnnounceInformation(sprintf("Wipe requested - device id '%s'", $deviceId), true);
        return true;
    }

    /**
     * Sets device options of the requested user.
     *
     * @param string    $deviceId       the device id
     * @param int       $filtertype     SYNC_FILTERTYPE_1DAY to SYNC_FILTERTYPE_ALL, false to ignore
     *
     * @access public
     * @return boolean
     * @throws SoapFault
     */
    public function SetDeviceOptions($deviceId, $filtertype) {
        $deviceId = preg_replace("/[^A-Za-z0-9]/", "", $deviceId);
        $user = Request::GetImpersonatedUser() ? Request::GetImpersonatedUser() : Request::GetGETUser();
        ZLog::Write(LOGLEVEL_INFO, sprintf("WebserviceDevice::SetDeviceOptions('%s', '%s'): set FilterType to '%s'", $deviceId, $user, Utils::PrintAsString($filtertype)));

        if (! ZPushAdmin::SetDeviceOptions($user, $deviceId, $filtertype)) {
            ZPush::GetTopCollector()->AnnounceInformation(ZLog::GetLastMessage(LOGLEVEL_ERROR), true);
            throw new SoapFault("ERROR", ZLog::GetLastMessage(LOGLEVEL_ERROR));
        }

        ZPush::GetTopCollector()->AnnounceInformation(sprintf("FilterType set to '%s' - device id '%s'", Utils::PrintAsString($filtertype), $deviceId), true);
        return true;
    }

    /**
     * Marks a device of the requested user for resynchronization.
     *
     * @param string    $deviceId       the device id
     *
     * @access public
     * @return boolean
     * @throws SoapFault
     */
    public function ResyncDevice($deviceId) {
        $deviceId = preg_replace("/[^A-Za-z0-9]/", "", $deviceId);
        $user = Request::GetImpersonatedUser() ? Request::GetImpersonatedUser() : Request::GetGETUser();
        ZLog::Write(LOGLEVEL_INFO, sprintf("WebserviceDevice::ResyncDevice('%s'): mark device of user '%s' for resynchronization", $deviceId, $user));

        if (! ZPushAdmin::ResyncDevice($user, $deviceId)) {
            ZPush::GetTopCollector()->AnnounceInformation(ZLog::GetLastMessage(LOGLEVEL_ERROR), true);
            throw new SoapFault("ERROR", ZLog::GetLastMessage(LOGLEVEL_ERROR));
        }

        ZPush::GetTopCollector()->AnnounceInformation(sprintf("Resync requested - device id '%s'", $deviceId), true);
        return true;
    }

    /**
     * Marks a folder of a device of the requested user for resynchronization.
     *
     * @param string    $deviceId       the device id
     * @param string    $folderId       the folder id
     *
     * @access public
     * @return boolean
     * @throws SoapFault
     */
    public function ResyncFolder($deviceId, $folderId) {
        $deviceId = preg_replace("/[^A-Za-z0-9]/", "", $deviceId);
        $folderId = preg_replace("/[^A-Za-z0-9]/", "", $folderId);
        $user = Request::GetImpersonatedUser() ? Request::GetImpersonatedUser() : Request::GetGETUser();
        ZLog::Write(LOGLEVEL_INFO, sprintf("WebserviceDevice::ResyncFolder('%s','%s'): mark folder of a device of user '%s' for resynchronization", $deviceId, $folderId, $user));

        if (! ZPushAdmin::ResyncFolder($user, $deviceId, $folderId)) {
            ZPush::GetTopCollector()->AnnounceInformation(ZLog::GetLastMessage(LOGLEVEL_ERROR), true);
            throw new SoapFault("ERROR", ZLog::GetLastMessage(LOGLEVEL_ERROR));
        }

        ZPush::GetTopCollector()->AnnounceInformation(sprintf("Folder resync requested - device id '%s', folder id '%s", $deviceId, $folderId), true);
        return true;
    }

    /**
     * Returns a list of all additional folders of the given device and the requested user.
     *
     * @param string    $deviceId       device id that should be listed.
     *
     * @access public
     * @return array
     */
    public function AdditionalFolderList($deviceId) {
        $user = Request::GetImpersonatedUser() ? Request::GetImpersonatedUser() : Request::GetGETUser();
        $deviceId = preg_replace("/[^A-Za-z0-9]/", "", $deviceId);
        $folders = ZPushAdmin::AdditionalFolderList($user, $deviceId);
        if ($folders === false) {
             $folders = array();
        }
        ZLog::Write(LOGLEVEL_INFO, sprintf("WebserviceDevice::AdditionalFolderList(): found %d folders for device '%s' of user '%s'", count($folders), $deviceId, $user));
        // retrieve the permission flags from the backend and convert associative array into stdClass object for PHP7 support
        $folderObjects = array();
        $backend = ZPush::GetBackend();
        foreach($folders as $folder) {
            $folderObject = new stdClass();
            $folderObject->store = $folder['store'];
            $folderObject->folderid = $folder['folderid'];
            $folderObject->parentid = (isset($folder['parentid'])) ? $folder['parentid'] : "0";
            $folderObject->syncfolderid = $folder['syncfolderid'];
            $folderObject->name = $folder['name'];
            $folderObject->type = $folder['type'];
            $folderObject->origin = $folder['origin'];
            $folderObject->flags = $folder['flags'];
            $folderObject->readable = $backend->Setup($folder['store'], true, $folder['folderid'], true);
            $folderObject->writeable = $backend->Setup($folder['store'], true, $folder['folderid']);
            $folderObjects[] = $folderObject;
        }
        ZPush::GetTopCollector()->AnnounceInformation(sprintf("Retrieved details of %d folders", count($folderObjects)), true);
        return $folderObjects;
    }

    /**
     * Adds an additional folder to the given device and the requested user.
     *
     * @param string    $deviceId       device id the folder should be added to.
     * @param string    $add_store      the store where this folder is located, e.g. "SYSTEM" (for public folder) or an username/email address.
     * @param string    $add_folderid   the folder id of the additional folder.
     * @param string    $add_name       the name of the additional folder (has to be unique for all folders on the device).
     * @param string    $add_type       AS foldertype of SYNC_FOLDER_TYPE_USER_*
     * @param int       $add_flags      Additional flags, like DeviceManager::FLD_FLAGS_SENDASOWNER
     *
     * @access public
     * @return boolean
     */
    public function AdditionalFolderAdd($deviceId, $add_store, $add_folderid, $add_name, $add_type, $add_flags) {
        $user = Request::GetImpersonatedUser() ? Request::GetImpersonatedUser() : Request::GetGETUser();
        $deviceId = preg_replace("/[^A-Za-z0-9]/", "", $deviceId);
        $add_folderid = preg_replace("/[^A-Za-z0-9]/", "", $add_folderid);
        $add_type = preg_replace("/[^0-9]/", "", $add_type);
        $add_flags = preg_replace("/[^0-9]/", "", $add_flags);

        $status = ZPushAdmin::AdditionalFolderAdd($user, $deviceId, $add_store, $add_folderid, $add_name, $add_type, $add_flags);
        if (!$status) {
            ZPush::GetTopCollector()->AnnounceInformation(ZLog::GetLastMessage(LOGLEVEL_ERROR), true);
            throw new SoapFault("ERROR", ZLog::GetLastMessage(LOGLEVEL_ERROR));
        }
        ZLog::Write(LOGLEVEL_INFO, sprintf("WebserviceDevice::AdditionalFolderAdd(): added folder for device '%s' of user '%s': %s", $deviceId, $user, Utils::PrintAsString($status)));
        ZPush::GetTopCollector()->AnnounceInformation("Added additional folder", true);

        return $status;
    }

    /**
     * Updates the name of an additional folder to the given device and the requested user.
     *
     * @param string    $deviceId       device id of where the folder should be updated.
     * @param string    $add_folderid   the folder id of the additional folder.
     * @param string    $add_name       the name of the additional folder (has to be unique for all folders on the device).
     * @param int       $add_flags      Additional flags, like DeviceManager::FLD_FLAGS_SENDASOWNER
     *
     * @access public
     * @return boolean
     */
    public function AdditionalFolderEdit($deviceId, $add_folderid, $add_name, $add_flags) {
        $user = Request::GetImpersonatedUser() ? Request::GetImpersonatedUser() : Request::GetGETUser();
        $deviceId = preg_replace("/[^A-Za-z0-9]/", "", $deviceId);
        $add_folderid = preg_replace("/[^A-Za-z0-9]/", "", $add_folderid);
        $add_flags = preg_replace("/[^0-9]/", "", $add_flags);

        $status = ZPushAdmin::AdditionalFolderEdit($user, $deviceId, $add_folderid, $add_name, $add_flags);
        if (!$status) {
            ZPush::GetTopCollector()->AnnounceInformation(ZLog::GetLastMessage(LOGLEVEL_ERROR), true);
            throw new SoapFault("ERROR", ZLog::GetLastMessage(LOGLEVEL_ERROR));
        }
        ZLog::Write(LOGLEVEL_INFO, sprintf("WebserviceDevice::AdditionalFolderEdit(): edited folder for device '%s' of user '%s': %s", $deviceId, $user, Utils::PrintAsString($status)));
        ZPush::GetTopCollector()->AnnounceInformation("Edited additional folder", true);

        return $status;
    }

    /**
     * Removes an additional folder from the given device and the requested user.
     *
     * @param string    $deviceId       device id of where the folder should be removed.
     * @param string    $add_folderid   the folder id of the additional folder.
     *
     * @access public
     * @return boolean
     */
    public function AdditionalFolderRemove($deviceId, $add_folderid) {
        $user = Request::GetImpersonatedUser() ? Request::GetImpersonatedUser() : Request::GetGETUser();
        $deviceId = preg_replace("/[^A-Za-z0-9]/", "", $deviceId);
        $add_folderid = preg_replace("/[^A-Za-z0-9]/", "", $add_folderid);

        $status = ZPushAdmin::AdditionalFolderRemove($user, $deviceId, $add_folderid);
        if (!$status) {
            ZPush::GetTopCollector()->AnnounceInformation(ZLog::GetLastMessage(LOGLEVEL_ERROR), true);
            throw new SoapFault("ERROR", ZLog::GetLastMessage(LOGLEVEL_ERROR));
        }
        ZLog::Write(LOGLEVEL_INFO, sprintf("WebserviceDevice::AdditionalFolderRemove(): removed folder for device '%s' of user '%s': %s", $deviceId, $user, Utils::PrintAsString($status)));
        ZPush::GetTopCollector()->AnnounceInformation("Removed additional folder", true);

        return $status;
    }

    /**
     * Sets a list of additional folders of one store to the given device and the requested user.
     * If there are additional folders for this store, that are not in the list they will be removed.
     *
     * @param string    $deviceId       device id the folder should be added to.
     * @param string    $set_store      the store where this folder is located, e.g. "SYSTEM" (for public folder) or an username/email address.
     * @param array     $set_folders    a list of folders to be set for this user. Other existing additional folders (that are not in this list)
     *                                  will be removed. The list is an array containing folders, where each folder is an array with the following keys:
     *                                  'folderid'  (string) the folder id of the additional folder.
     *                                  'parentid'  (string) the folderid of the parent folder. If no parent folder is set or the parent folder is not defined, '0' (main folder) is used.
     *                                  'name'      (string) the name of the additional folder (has to be unique for all folders on the device).
     *                                  'type'      (string) AS foldertype of SYNC_FOLDER_TYPE_USER_*
     *                                  'flags'     (int)    Additional flags, like DeviceManager::FLD_FLAGS_SENDASOWNER
     *
     * @access public
     * @return boolean
     */
    public function AdditionalFolderSetList($deviceId, $set_store, $set_folders) {
        $user = Request::GetImpersonatedUser() ? Request::GetImpersonatedUser() : Request::GetGETUser();
        $deviceId = preg_replace("/[^A-Za-z0-9]/", "", $deviceId);
        array_walk($set_folders, function(&$folder) {
            if (!isset($folder['folderid']))    $folder['folderid'] = "";
            if (!isset($folder['parentid']))    $folder['parentid'] = "0";
            if (!isset($folder['type']))        $folder['type'] = SYNC_FOLDER_TYPE_USER_MAIL;
            if (!isset($folder['flags']))       $folder['flags'] = 0;

            $folder['folderid'] = preg_replace("/[^A-Za-z0-9]/", "", $folder['folderid']);
            $folder['parentid'] = preg_replace("/[^A-Za-z0-9]/", "", $folder['parentid']);
            $folder['type'] = preg_replace("/[^0-9]/", "", $folder['type']);
            $folder['flags'] = preg_replace("/[^0-9]/", "", $folder['flags']);
        });

        $status = ZPushAdmin::AdditionalFolderSetList($user, $deviceId, $set_store, $set_folders);
        if (!$status) {
            ZPush::GetTopCollector()->AnnounceInformation(ZLog::GetLastMessage(LOGLEVEL_ERROR), true);
            throw new SoapFault("ERROR", ZLog::GetLastMessage(LOGLEVEL_ERROR));
        }
        ZLog::Write(LOGLEVEL_INFO, sprintf("WebserviceDevice::AdditionalFolderSetList(): set '%d' folders for device '%s' of user '%s': %s", count($set_folders), $deviceId, $user, Utils::PrintAsString($status)));
        ZPush::GetTopCollector()->AnnounceInformation("Set additional folders", true);

        return $status;
    }
}
