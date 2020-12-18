<?php
/***********************************************
* File      :   zpushadmin.php
* Project   :   Z-Push
* Descr     :   Administration tasks for users and devices
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

class ZPushAdmin {
    /**
     * //TODO resync of a foldertype for all users (e.g. Appointment)
     */

    const STATUS_SUCCESS = 0;
    const STATUS_DEVICE_SYNCED_AFTER_DAYSOLD = 1;

    public static $status = self::STATUS_SUCCESS;
    public static $devices;
    public static $userDevices;

    /**
     * List devices known to Z-Push.
     * If no user is given, all devices are listed
     *
     * @param string    $user       devices of that user, if false all devices of all users
     *
     * @return array
     * @access public
     */
    static public function ListDevices($user = false) {
        return ZPush::GetStateMachine()->GetAllDevices($user);
    }

    /**
     * List users of a device known to Z-Push.
     *
     * @param string    $devid      users of that device
     *
     * @return array
     * @access public
     */
    static public function ListUsers($devid) {
        try {
            $devState = ZPush::GetStateMachine()->GetState($devid, IStateMachine::DEVICEDATA);

            if ($devState instanceof StateObject && isset($devState->devices) && is_array($devState->devices))
                return array_keys($devState->devices);
            else
                return array();
        }
        catch (StateNotFoundException $stnf) {
            return array();
        }
    }

    /**
     * Returns details of a device like synctimes,
     * policy and wipe status, synched folders etc
     *
     * @param string    $devid      device id
     * @param string    $user       user to be looked up
     * @param boolean   $withHierarchyCache (opt) includes the HierarchyCache - default: false
     *
     * @return ASDevice object
     * @access public
     */
    static public function GetDeviceDetails($devid, $user, $withHierarchyCache = false) {

        try {
            $device = new ASDevice($devid, ASDevice::UNDEFINED, $user, ASDevice::UNDEFINED);
            $device->SetData(ZPush::GetStateMachine()->GetState($devid, IStateMachine::DEVICEDATA), false);

            try {
                // we need a StateManager for this operation
                $stateManager = new StateManager();
                $stateManager->SetDevice($device);

                $sc = new SyncCollections();
                $sc->SetStateManager($stateManager);

                // load all collections of device also loading states and loading hierarchy, but not checking permissions
                $sc->LoadAllCollections(true, true, false, true);
            }
            catch (StateInvalidException $sive) {
                ZLog::Write(LOGLEVEL_WARN, sprintf("ZPushAdmin::GetDeviceDetails(): device '%s' of user '%s' has invalid states. Please sync to solve this issue.", $devid, $user));
                $device->SetDeviceError("Invalid states. Please force synchronization!");
            }
            catch (StatusException $ste) {
                ZLog::Write(LOGLEVEL_WARN, sprintf("ZPushAdmin::GetDeviceDetails(): device '%s' of user '%s' has status exceptions. Please sync to solve this issue.", $devid, $user));
                $device->SetDeviceError("State exceptions. Please force synchronization or remove device to fix!");
            }

            if ($sc) {
                if ($sc->GetLastSyncTime())
                    $device->SetLastSyncTime($sc->GetLastSyncTime());

                // get information about the folder synchronization status from SyncCollections
                $folders = $device->GetAllFolderIds();

                // indicate short folderids
                $device->hasFolderIdMapping = $device->HasFolderIdMapping();

                foreach ($folders as $folderid) {
                    $fstatus = $device->GetFolderSyncStatus($folderid);

                    if ($fstatus !== false && isset($fstatus[ASDevice::FOLDERSYNCSTATUS])) {
                        $spa = $sc->GetCollection($folderid);
                        if ($spa) {
                            $total = $spa->GetFolderSyncTotal();
                            $todo = $spa->GetFolderSyncRemaining();
                            $fstatus['status'] = ($fstatus[ASDevice::FOLDERSYNCSTATUS] == 1) ? 'Initialized' : 'Synchronizing';
                            $fstatus['total'] = $total;
                            $fstatus['done'] = $total - $todo;
                            $fstatus['todo'] = $todo;

                            $device->SetFolderSyncStatus($folderid, $fstatus);
                        }
                    }
                }
            }
            $device->StripData(!$withHierarchyCache);
            return $device;
        }
        catch (StateNotFoundException $e) {
            ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::GetDeviceDetails(): device '%s' of user '%s' can not be found", $devid, $user));
            return false;
        }
    }

    /**
     * Wipes 'a' or all devices of a user.
     * If no user is set, the device is generally wiped.
     * If no device id is set, all devices of the user will be wiped.
     * Device id or user must be set!
     *
     * @param string    $requestedBy    user which requested this operation
     * @param string    $user           (opt)user of the device
     * @param string    $devid          (opt) device id which should be wiped
     *
     * @return boolean
     * @access public
     */
    static public function WipeDevice($requestedBy, $user, $devid = false) {
        if ($user === false && $devid === false)
            return false;

        if ($devid === false) {
            $devicesIds = ZPush::GetStateMachine()->GetAllDevices($user);
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZPushAdmin::WipeDevice(): all '%d' devices for user '%s' found to be wiped", count($devicesIds), $user));
            foreach ($devicesIds as $deviceid) {
                if (!self::WipeDevice($requestedBy, $user, $deviceid)) {
                    ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::WipeDevice(): wipe devices failed for device '%s' of user '%s'. Aborting.", $deviceid, $user));
                    return false;
                }
            }
        }

        // wipe a device completely (for connected users to this device)
        else if ($devid !== false && $user === false) {
            $users = self::ListUsers($devid);
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZPushAdmin::WipeDevice(): device '%d' is used by '%d' users and will be wiped", $devid, count($users)));
            if (count($users) == 0)
                ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::WipeDevice(): no user found on device '%s'. Aborting.", $devid));

            return self::WipeDevice($requestedBy, $users[0], $devid);
        }

        else {
            // load device data
            $device = new ASDevice($devid, ASDevice::UNDEFINED, $user, ASDevice::UNDEFINED);
            try {
                $device->SetData(ZPush::GetStateMachine()->GetState($devid, IStateMachine::DEVICEDATA), false);
            }
            catch (StateNotFoundException $e) {
                ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::WipeDevice(): device '%s' of user '%s' can not be found", $devid, $user));
                return false;
            }

            // set wipe status
            if ($device->GetWipeStatus() == SYNC_PROVISION_RWSTATUS_WIPED)
                ZLog::Write(LOGLEVEL_INFO, sprintf("ZPushAdmin::WipeDevice(): device '%s' of user '%s' was alread sucessfully remote wiped on %s", $devid , $user, strftime("%Y-%m-%d %H:%M", $device->GetWipeActionOn())));
            else
                $device->SetWipeStatus(SYNC_PROVISION_RWSTATUS_PENDING, $requestedBy);

            // save device data
            try {
                if ($device->IsNewDevice() || $device->GetData() === false) {
                    ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::WipeDevice(): data of user '%s' not synchronized on device '%s'. Aborting.", $user, $devid));
                    return false;
                }

                ZPush::GetStateMachine()->SetState($device->GetData(), $devid, IStateMachine::DEVICEDATA);
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZPushAdmin::WipeDevice(): device '%s' of user '%s' marked to be wiped", $devid, $user));
            }
            catch (StateNotFoundException $e) {
                ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::WipeDevice(): state for device '%s' of user '%s' can not be saved", $devid, $user));
                return false;
            }
        }
        return true;
    }


    /**
     * Removes device details from the z-push directory.
     * If device id is not set, all devices of a user are removed.
     * If the user is not set, the details of the device (independently if used by several users) is removed.
     * Device id or user must be set!
     *
     * @param string    $user           (opt) user of the device
     * @param string    $devid          (opt) device id which should be removed
     * @param int       $daysOld        (opt) devices which haven't synced for $daysOld days
     * @param int       $time           (opt) unix timestamp to use with $daysOld
     *
     * @return boolean
     * @access public
     */
    static public function RemoveDevice($user = false, $devid = false, $daysOld = false, $time = false) {
        if ($user === false && $devid === false)
            return false;

        // remove all devices for user
        if ($devid === false && $user !== false) {
            $devicesIds = ZPush::GetStateMachine()->GetAllDevices($user);
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZPushAdmin::RemoveDevice(): all '%d' devices for user '%s' found to be removed", count($devicesIds), $user));
            foreach ($devicesIds as $deviceid) {
                if (!self::RemoveDevice($user, $deviceid, $daysOld, $time)) {
                    ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::RemoveDevice(): removing devices failed for device '%s' of user '%s'. Aborting", $deviceid, $user));
                    return false;
                }
            }
        }
        // remove a device completely (for connected users to this device)
        else if ($devid !== false && $user === false) {
            $users = self::ListUsers($devid);
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZPushAdmin::RemoveDevice(): device '%d' is used by '%d' users and will be removed", $devid, count($users)));
            foreach ($users as $aUser) {
                if (!self::RemoveDevice($aUser, $devid, $daysOld, $time)) {
                    ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::RemoveDevice(): removing user '%s' from device '%s' failed. Aborting", $aUser, $devid));
                    return false;
                }
            }
        }

        // user and deviceid set
        else {
            // load device data
            $device = new ASDevice($devid, ASDevice::UNDEFINED, $user, ASDevice::UNDEFINED);
            $devices = array();
            try {
                $devicedata = ZPush::GetStateMachine()->GetState($devid, IStateMachine::DEVICEDATA);
                $device->SetData($devicedata, false);
                if (!isset($devicedata->devices))
                    throw new StateInvalidException("No devicedata stored in ASDevice");

                if ($daysOld) {
                    if (!$time) {
                        $time = time();
                    }
                    $lastSynced = floor(($time - $device->getLastupdatetime()) / 86400);
                    if ($daysOld > $lastSynced) {
                        ZLog::Write(LOGLEVEL_INFO,
                                sprintf("ZPushAdmin::RemoveDevice(): device '%s' of user '%s' synced %d day(s) ago but only devices which synced more than %d days ago will be removed. Skipping.",
                                        $devid, $user, $lastSynced, $daysOld));
                        self::$status = self::STATUS_DEVICE_SYNCED_AFTER_DAYSOLD;
                        return true;
                    }
                }

                $devices = $devicedata->devices;
            }
            catch (StateNotFoundException $e) {
                ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::RemoveDevice(): device '%s' of user '%s' can not be found", $devid, $user));
                return false;
            }

            // remove all related states
            foreach ($device->GetAllFolderIds() as $folderid)
                StateManager::UnLinkState($device, $folderid);

            // remove hierarchcache
            StateManager::UnLinkState($device, false);

            // remove backend storage permanent data
            ZPush::GetStateMachine()->CleanStates($device->GetDeviceId(), IStateMachine::BACKENDSTORAGE, false, $device->GetFirstSyncTime(), true);

            // remove devicedata and unlink user from device
            unset($devices[$user]);
            if (isset($devicedata->devices))
                $devicedata->devices = $devices;
            ZPush::GetStateMachine()->UnLinkUserDevice($user, $devid);

            // no more users linked for device - remove device data
            if (count($devices) == 0)
                ZPush::GetStateMachine()->CleanStates($devid, IStateMachine::DEVICEDATA, false);

            // save data if something left
            else
                ZPush::GetStateMachine()->SetState($devicedata, $devid, IStateMachine::DEVICEDATA);

            ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZPushAdmin::RemoveDevice(): data of device '%s' of user '%s' removed", $devid, $user));
        }
        return true;
    }

    /**
     * Sets options for a device.
     * First argument is the FilterType.
     * This value is superseeded by the globally configured SYNC_FILTERTIME_MAX.
     * If set to false the value will not be modified in the device.
     *
     * @param string    $user           user of the device
     * @param string    $devid          device id that should be modified
     * @param int       $filtertype     SYNC_FILTERTYPE_1DAY to SYNC_FILTERTYPE_ALL, false to ignore
     *
     * @access public
     * @return boolean
     */
    public static function SetDeviceOptions($user, $devid, $filtertype) {
        if ($user === false || $devid === false) {
            ZLog::Write(LOGLEVEL_ERROR, "ZPushAdmin::SetDeviceOptions(): user and device must be specified");
            return false;
        }

        if ($filtertype !== false && $filtertype < SYNC_FILTERTYPE_ALL || $filtertype > SYNC_FILTERTYPE_INCOMPLETETASKS) {
            ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::SetDeviceOptions(): specified FilterType '%s' is out of bounds", $filtertype));
            return false;
        }

        // load device data
        $device = new ASDevice($devid, ASDevice::UNDEFINED, $user, ASDevice::UNDEFINED);
        try {
            $device->SetData(ZPush::GetStateMachine()->GetState($devid, IStateMachine::DEVICEDATA), false);
        }
        catch (StateNotFoundException $e) {
            ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::SetDeviceOptions(): device '%s' of user '%s' can not be found", $devid, $user));
            return false;
        }

        if ($filtertype !== false) {
            if ($filtertype === $device->GetSyncFilterType()) {
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZPushAdmin::SetDeviceOptions(): device '%s' of user '%s' - device FilterType already at '%s'. Terminating.", $devid, $user, $filtertype));
            }
            else {
                $device->SetSyncFilterType($filtertype);
            }
        }

        // save device data
        if ($device->IsDataChanged()) {
            try {
                if ($device->IsNewDevice() || $device->GetData() === false) {
                    ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::SetDeviceOptions(): data of user '%s' not synchronized on device '%s'. Aborting.", $user, $devid));
                    return false;
                }
                ZPush::GetStateMachine()->SetState($device->GetData(), $devid, IStateMachine::DEVICEDATA);
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZPushAdmin::SetDeviceOptions(): device '%s' of user '%s' - device FilterType updated to '%s'", $devid, $user, $filtertype));
            }
            catch (StateNotFoundException $e) {
                ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::SetDeviceOptions(): state for device '%s' of user '%s' can not be saved", $devid, $user));
                return false;
            }
        }
        return true;
    }

    /**
     * Marks a folder of a device of a user for re-synchronization.
     *
     * @param string    $user           user of the device
     * @param string    $devid          device id which should be re-synchronized
     * @param mixed     $folderid       a single folder id or an array of folder ids
     *
     * @access public
     * @return boolean
     */
    static public function ResyncFolder($user, $devid, $folderid) {
        // load device data
        $device = new ASDevice($devid, ASDevice::UNDEFINED, $user, ASDevice::UNDEFINED);
        try {
            $device->SetData(ZPush::GetStateMachine()->GetState($devid, IStateMachine::DEVICEDATA), false);

            if ($device->IsNewDevice()) {
                ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::ResyncFolder(): data of user '%s' not synchronized on device '%s'. Aborting.",$user, $devid));
                return false;
            }

            if (!$folderid || (is_array($folderid) && empty($folderid))) {
                ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::ResyncFolder(): no folders requested for user '%s' on device '%s'. Aborting.",$user, $devid));
                return false;
            }

            // remove folder state
            if (is_array($folderid)) {
                foreach ($folderid as $fid) {
                    StateManager::UnLinkState($device, $fid);
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZPushAdmin::ResyncFolder(): folder '%s' on device '%s' of user '%s' marked to be re-synchronized.", $fid, $devid, $user));
                }
            }
            else {
                StateManager::UnLinkState($device, $folderid);
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZPushAdmin::ResyncFolder(): folder '%s' on device '%s' of user '%s' marked to be re-synchronized.", $folderid, $devid, $user));
            }

            if ($device->GetData() === false) {
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZPushAdmin::ResyncFolder(): nothing changed for device '%s' of user '%s'", $devid, $user));
                return false;
            }
            ZPush::GetStateMachine()->SetState($device->GetData(), $devid, IStateMachine::DEVICEDATA);
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZPushAdmin::ResyncFolder(): saved updated device data of device '%s' of user '%s'", $devid, $user));
        }
        catch (StateNotFoundException $e) {
            ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::ResyncFolder(): state for device '%s' of user '%s' can not be found or saved", $devid, $user));
            return false;
        }
        return true;
    }


    /**
     * Marks a all folders synchronized to a device for re-synchronization
     * If no user is set all user which are synchronized for a device are marked for re-synchronization.
     * If no device id is set all devices of that user are marked for re-synchronization.
     * If no user and no device are set then ALL DEVICES are marked for resynchronization (use with care!).
     *
     * @param string    $user           (opt) user of the device
     * @param string    $devid          (opt)device id which should be re-synchronized
     *
     * @return boolean
     * @access public
     */
    static public function ResyncDevice($user, $devid = false) {

        // search for target devices
        if ($devid === false) {
            $devicesIds = ZPush::GetStateMachine()->GetAllDevices($user);
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZPushAdmin::ResyncDevice(): all '%d' devices for user '%s' found to be re-synchronized", count($devicesIds), $user));
            foreach ($devicesIds as $deviceid) {
                if (!self::ResyncDevice($user, $deviceid)) {
                    ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::ResyncDevice(): wipe devices failed for device '%s' of user '%s'. Aborting", $deviceid, $user));
                    return false;
                }
            }
        }
        else {
            // get devicedata
            try {
                $devicedata = ZPush::GetStateMachine()->GetState($devid, IStateMachine::DEVICEDATA);
            }
            catch (StateNotFoundException $e) {
                ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::ResyncDevice(): state for device '%s' can not be found", $devid));
                return false;

            }

            // loop through all users which currently use this device
            if ($user === false && $devicedata instanceof StateObject && isset($devicedata->devices) &&
                is_array($devicedata->devices) && count($devicedata->devices) > 1) {
                foreach (array_keys($devicedata) as $aUser) {
                    if (!self::ResyncDevice($aUser, $devid)) {
                        ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::ResyncDevice(): re-synchronization failed for device '%s' of user '%s'. Aborting", $devid, $aUser));
                        return false;
                    }
                }
            }

            // load device data
            $device = new ASDevice($devid, ASDevice::UNDEFINED, $user, ASDevice::UNDEFINED);
            try {
                $device->SetData($devicedata, false);

                if ($device->IsNewDevice()) {
                    ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::ResyncDevice(): data of user '%s' not synchronized on device '%s'. Aborting.",$user, $devid));
                    return false;
                }

                // delete all uuids
                foreach ($device->GetAllFolderIds() as $folderid)
                    StateManager::UnLinkState($device, $folderid);

                // remove hierarchcache
                StateManager::UnLinkState($device, false);

                if ($device->GetData() !== false) {
                    ZPush::GetStateMachine()->SetState($device->GetData(), $devid, IStateMachine::DEVICEDATA);
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZPushAdmin::ResyncDevice(): all folders synchronized to device '%s' of user '%s' marked to be re-synchronized.", $devid, $user));
                }
            }
            catch (StateNotFoundException $e) {
                ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::ResyncDevice(): state for device '%s' of user '%s' can not be found or saved", $devid, $user));
                return false;
            }
        }
        return true;
    }

    /**
     * Removes the hierarchydata of a device of a user so it will be re-synchronized.
     *
     * @param string    $user           user of the device
     * @param string    $devid          device id which should be re-synchronized.
     *
     * @return boolean
     * @access public
     */
    static public function ResyncHierarchy($user, $devid) {
        // load device data
        $device = new ASDevice($devid, ASDevice::UNDEFINED, $user, ASDevice::UNDEFINED);
        try {
            $device->SetData(ZPush::GetStateMachine()->GetState($devid, IStateMachine::DEVICEDATA), false);

            if ($device->IsNewDevice()) {
                ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::ResyncHierarchy(): data of user '%s' not synchronized on device '%s'. Aborting.",$user, $devid));
                return false;
            }

            // remove hierarchcache, but don't update the device, as the folder states are invalidated
            StateManager::UnLinkState($device, false, false);

            ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZPushAdmin::ResyncHierarchy(): deleted hierarchy states of device '%s' of user '%s'", $devid, $user));
        }
        catch (StateNotFoundException $e) {
            ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::ResyncHierarchy(): state for device '%s' of user '%s' can not be found or saved", $devid, $user));
            return false;
        }
        return true;
    }

    /**
     * Lists all additional folders for the user and device.
     *
     * @param string    $user           user of the device.
     * @param string    $devid          device id that should be listed.
     *
     * @access public
     * @return array
     */
    static public function AdditionalFolderList($user, $devid) {
        $list = array();
        // load device data
        $device = new ASDevice($devid, ASDevice::UNDEFINED, $user, ASDevice::UNDEFINED);
        try {
            $device->SetData(ZPush::GetStateMachine()->GetState($devid, IStateMachine::DEVICEDATA), false);

            if ($device->IsNewDevice()) {
                ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::AdditionalFolderList(): data of user '%s' not synchronized on device '%s'. Aborting.", $user, $devid));
                return false;
            }

            // init deviceManage with correct device
            ZPush::GetDeviceManagerWithDevice($device);

            // unify the lists saved for the user/device and the staticly configured one
            $new_list = array();
            foreach ($device->GetAdditionalFolders() as $folder) {
                $syncfolderid = $device->GetFolderIdForBackendId($folder['folderid'], false, false, null);
                $folder['syncfolderid'] = $syncfolderid;
                $folder['origin'] = ($syncfolderid !== $folder['folderid'])?Utils::GetFolderOriginStringFromId($syncfolderid):'unknown';
                $new_list[$folder['folderid']] = $folder;
            }
            foreach (ZPush::GetAdditionalSyncFolders() as $fid => $so) {
                // if this is not part of the device list
                if (!isset($new_list[$fid])) {
                    $syncfolderid = $device->GetFolderIdForBackendId($fid, false, false, null);
                    $new_list[$fid] = array(
                                        'store' => $so->Store,
                                        'folderid' => $fid,
                                        'parentid' => $so->parentid,
                                        'syncfolderid' => $syncfolderid,
                                        'name' => $so->displayname,
                                        'type' => $so->type,
                                        'origin' => ($syncfolderid !== $fid)?Utils::GetFolderOriginStringFromId($syncfolderid):'unknown',
                                        'flags' => $so->Flags,
                                    );
                }
            }
            $list = array_values($new_list);
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZPushAdmin::AdditionalFolderList(): listing data of %d additional folders of device '%s' of user '%s'", count($list), $devid, $user));
        }
        catch (StateNotFoundException $e) {
            ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::AdditionalFolderList(): state for device '%s' of user '%s' can not be found", $devid, $user));
            return false;
        }
        return $list;
    }

    /**
     * Adds an additional folder for the user and device.
     *
     * @param string    $user           user of the device.
     * @param string    $devid          device id the folder should be added to.
     * @param string    $add_store      the store where this folder is located, e.g. "SYSTEM" (for public folder) or a username.
     * @param string    $add_folderid   the folder id of the additional folder.
     * @param string    $add_name       the name of the additional folder (has to be unique for all folders on the device).
     * @param string    $add_type       AS foldertype of SYNC_FOLDER_TYPE_USER_*
     * @param int       $add_flags      Additional flags, like DeviceManager::FLD_FLAGS_SENDASOWNER
     *
     * @access public
     * @return boolean
     */
    static public function AdditionalFolderAdd($user, $devid, $add_store, $add_folderid, $add_name, $add_type, $add_flags) {
        // load device data
        $device = new ASDevice($devid, ASDevice::UNDEFINED, $user, ASDevice::UNDEFINED);
        try {
            // set device data
            $device->SetData(ZPush::GetStateMachine()->GetState($devid, IStateMachine::DEVICEDATA), false);
            // get the last hierarchy counter
            $spa = ZPush::GetStateMachine()->GetState($devid, IStateMachine::FOLDERDATA, $device->GetFolderUUID());
            list($uuid, $counter) = StateManager::ParseStateKey($spa->GetSyncKey());
            // instantiate hierarchycache
            $device->SetHierarchyCache(ZPush::GetStateMachine()->GetState($devid, IStateMachine::HIERARCHY, $device->GetFolderUUID(), $counter, false));

            if ($device->IsNewDevice()) {
                ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::AdditionalFolderAdd(): data of user '%s' not synchronized on device '%s'. Aborting.", $user, $devid));
                return false;
            }

            $status = $device->AddAdditionalFolder($add_store, $add_folderid, $add_name, $add_type, $add_flags);
            if ($status && $device->GetData() !== false) {
                ZPush::GetStateMachine()->SetState($device->GetData(), $devid, IStateMachine::DEVICEDATA);
            }
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZPushAdmin::AdditionalFolderAdd(): added folder '%s' to additional folders list of device '%s' of user '%s' with status: %s", $add_name, $devid, $user, Utils::PrintAsString($status)));
            return $status;
        }
        catch (StateNotFoundException $e) {
            ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::AdditionalFolderAdd(): state for device '%s' of user '%s' can not be found or saved", $devid, $user));
            return false;
        }
        return false;
    }

    /**
     * Updates an additional folder for the user and device.
     *
     * @param string    $user           user of the device.
     * @param string    $devid          device id of where the folder should be updated.
     * @param string    $add_folderid   the folder id of the additional folder.
     * @param string    $add_name       the name of the additional folder (has to be unique for all folders on the device).
     * @param int       $add_flags      Additional flags, like DeviceManager::FLD_FLAGS_SENDASOWNER
     *
     * @access public
     * @return boolean
     */
    static public function AdditionalFolderEdit($user, $devid, $add_folderid, $add_name, $add_flags) {
        // load device data
        $device = new ASDevice($devid, ASDevice::UNDEFINED, $user, ASDevice::UNDEFINED);
        try {
            // set device data
            $device->SetData(ZPush::GetStateMachine()->GetState($devid, IStateMachine::DEVICEDATA), false);
            // get the last hierarchy counter
            $spa = ZPush::GetStateMachine()->GetState($devid, IStateMachine::FOLDERDATA, $device->GetFolderUUID());
            list($uuid, $counter) = StateManager::ParseStateKey($spa->GetSyncKey());
            // instantiate hierarchycache
            $device->SetHierarchyCache(ZPush::GetStateMachine()->GetState($devid, IStateMachine::HIERARCHY, $device->GetFolderUUID(), $counter, false));

            if ($device->IsNewDevice()) {
                ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::AdditionalFolderEdit(): data of user '%s' not synchronized on device '%s'. Aborting.", $user, $devid));
                return false;
            }

            $static_folders = ZPush::GetAdditionalSyncFolders();
            if (isset($static_folders[$add_folderid])) {
                ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::AdditionalFolderEdit(): the folder id '%s' can not be edited as it is a statically configured folder. Aborting.", $add_folderid));
                return false;
            }

            $status = $device->EditAdditionalFolder($add_folderid, $add_name, $add_flags);
            if ($status  && $device->GetData() !== false) {
                ZPush::GetStateMachine()->SetState($device->GetData(), $devid, IStateMachine::DEVICEDATA);
            }
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZPushAdmin::AdditionalFolderEdit(): updated folder '%s' in additional folders list of device '%s' of user '%s' with status: %s", $add_name, $devid, $user, Utils::PrintAsString($status)));
            return $status;
        }
        catch (StateNotFoundException $e) {
            ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::AdditionalFolderEdit(): state for device '%s' of user '%s' can not be found or saved", $devid, $user));
            return false;
        }
        return false;
    }

    /**
     * Removes an additional folder for the user and device.
     *
     * @param string    $user           user of the device.
     * @param string    $devid          device id of where the folder should be removed.
     * @param string    $add_folderid   the folder id of the additional folder.
     *
     * @access public
     * @return boolean
     */
    static public function AdditionalFolderRemove($user, $devid, $add_folderid) {
        // load device data
        $device = new ASDevice($devid, ASDevice::UNDEFINED, $user, ASDevice::UNDEFINED);
        try {
            $device->SetData(ZPush::GetStateMachine()->GetState($devid, IStateMachine::DEVICEDATA), false);

            if ($device->IsNewDevice()) {
                ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::AdditionalFolderRemove(): data of user '%s' not synchronized on device '%s'. Aborting.", $user, $devid));
                return false;
            }

            $status = $device->RemoveAdditionalFolder($add_folderid);
            if ($status && $device->GetData() !== false) {
                ZPush::GetStateMachine()->SetState($device->GetData(), $devid, IStateMachine::DEVICEDATA);
            }
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZPushAdmin::AdditionalFolderRemove(): removed folder '%s' in additional folders list of device '%s' of user '%s' with status: %s", $add_folderid, $devid, $user, Utils::PrintAsString($status)));
            return $status;
        }
        catch (StateNotFoundException $e) {
            ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::AdditionalFolderRemove(): state for device '%s' of user '%s' can not be found or saved", $devid, $user));
            return false;
        }
        return false;
    }

    /**
     * Sets a list of additional folders of one store to the given device and user.
     * If there are additional folders for this store, that are not in the list they will be removed.
     *
     * @param string    $user           user of the device.
     * @param string    $devid          device id of where the folder should be set.
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
    static public function AdditionalFolderSetList($user, $devid, $set_store, $set_folders) {
        // load device data
        $device = new ASDevice($devid, ASDevice::UNDEFINED, $user, ASDevice::UNDEFINED);
        try {
            // set device data
            $device->SetData(ZPush::GetStateMachine()->GetState($devid, IStateMachine::DEVICEDATA), false);
            // get the last hierarchy counter
            $spa = ZPush::GetStateMachine()->GetState($devid, IStateMachine::FOLDERDATA, $device->GetFolderUUID());
            list($uuid, $counter) = StateManager::ParseStateKey($spa->GetSyncKey());
            // instantiate hierarchycache
            $device->SetHierarchyCache(ZPush::GetStateMachine()->GetState($devid, IStateMachine::HIERARCHY, $device->GetFolderUUID(), $counter, false));

            if ($device->IsNewDevice()) {
                ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::AdditionalFolderSetList(): data of user '%s' not synchronized on device '%s'. Aborting.", $user, $devid));
                return false;
            }

            // check if any of the folders sent is in the statically configured list
            $set_folders_checked = array();
            $current_folders = ZPush::GetAdditionalSyncFolders();
            foreach($set_folders as $f) {
                if (isset($current_folders[$f['folderid']]) && substr($current_folders[$f['folderid']]->serverid, 0, 1) == DeviceManager::FLD_ORIGIN_CONFIG) {
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZPushAdmin::AdditionalFolderSetList(): Ignoring folder '%s' id '%s' as it's a statically configured folder", $f['name'], $f['folderid']));
                    continue;
                }
                $set_folders_checked[] = $f;
            }

            $status = $device->SetAdditionalFolderList($set_store, $set_folders_checked);
            if ($status && $device->GetData() !== false) {
                ZPush::GetStateMachine()->SetState($device->GetData(), $devid, IStateMachine::DEVICEDATA);
            }
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZPushAdmin::AdditionalFolderSetList(): added '%s' folders of '%s' to additional folders list of device '%s' of user '%s' with status: %s", count($set_folders), $set_store, $devid, $user, Utils::PrintAsString($status)));
            return $status;
        }
        catch (StateNotFoundException $e) {
            ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::AdditionalFolderSetList(): state for device '%s' of user '%s' can not be found or saved", $devid, $user));
            return false;
        }
        return false;
    }

    /**
     * Clears loop detection data
     *
     * @param string    $user           (opt) user which data should be removed - user may not be specified without device id
     * @param string    $devid          (opt) device id which data to be removed
     *
     * @return boolean
     * @access public
     */
    static public function ClearLoopDetectionData($user = false, $devid = false) {
        $loopdetection = new LoopDetection();
        return $loopdetection->ClearData($user, $devid);
    }

    /**
     * Returns loop detection data of a user & device
     *
     * @param string    $user
     * @param string    $devid
     *
     * @return array/boolean            returns false if data is not available
     * @access public
     */
    static public function GetLoopDetectionData($user, $devid) {
        $loopdetection = new LoopDetection();
        return $loopdetection->GetCachedData($user, $devid);
    }

    /**
     * Fixes states with usernames in different cases.
     *
     * @param string    $username . Has precedence over $deviceId
     * @param string    $deviceId
     *
     * @return boolean
     * @access public
     */
    static public function FixStatesDifferentUsernameCases($username=false, $deviceId=false) {
        $processed = 0;
        $dropedUsers = 0;
        $fixedUsers = 0;
        $processedDevices = 0;

        if ($username) {
            $devices = self::GetUserDevices($username);
        }
        else if ($deviceId){
            $devices = array($deviceId);
        }
        else {
            $devices = self::GetAllDevices();
        }
        $devicesCount = count($devices);
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZPushAdmin::FixStatesDifferentUsernameCases(): found %d devices", $devicesCount));
        foreach ($devices as $devid) {
            if (defined('LOGFIXSTATES') && LOGFIXSTATES === true) {
                ZLog::Write(LOGLEVEL_INFO, sprintf("ZPushAdmin::FixStatesDifferentUsernameCases(): Processing %d of %d. Device %s", ++$processedDevices, $devicesCount, $devid));
            }
            $users = self::ListUsers($devid);
            $obsoleteUsers = array();

            // find obsolete uppercase users
            foreach ($users as $username) {
                $processed++;
                $lowUsername = strtolower($username);
                if ($lowUsername === $username)
                    continue; // default case

                $obsoleteUsers[] = $username;
            }

            // remove or transform obsolete users
            if (!empty($obsoleteUsers)) {
                // load the device data
                try {
                    $devData = ZPush::GetStateMachine()->GetState($devid, IStateMachine::DEVICEDATA);

                    $devices = $devData->devices;
                    $knownUsers = array_keys($devData->devices);

                    foreach ($obsoleteUsers as $ouser) {
                        $lowerOUser = strtolower($ouser);
                        // there is a lowercase user, drop the uppercase one
                        if (in_array($lowerOUser, $knownUsers)) {
                            unset($devices[$ouser]);
                            $dropedUsers++;
                            ZLog::Write(LOGLEVEL_DEBUG, print_r(array_keys($devices),1));

                            ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZPushAdmin::FixStatesDifferentUsernameCases(): user '%s' of device '%s' is obsolete as a lowercase username is known", $ouser, $devid));
                        }
                        // there is only an uppercase user, save it as lowercase
                        else {
                            $devices[$lowerOUser] = $devices[$ouser];
                            unset($devices[$ouser]);
                            $fixedUsers++;

                            ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZPushAdmin::FixStatesDifferentUsernameCases(): user '%s' of device '%s' was saved as '%s'", $ouser, $devid, $lowerOUser));
                        }
                    }

                    unset($devData->device);
                    // save the devicedata
                    $devData->devices = $devices;
                    ZPush::GetStateMachine()->SetState($devData, $devid, IStateMachine::DEVICEDATA);

                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZPushAdmin::FixStatesDifferentUsernameCases(): updated device '%s' and user(s) %s were dropped or converted", $devid, implode(", ", $obsoleteUsers)));
                }
                catch (StateNotFoundException $e) {
                    ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::FixStatesDifferentUsernameCases(): state for device '%s' can not be found", $devid));
                }
            }
        }

        return array($processed, $fixedUsers, $dropedUsers);
    }

    /**
     * Fixes states of available device data to the user linking.
     *
     * @param string    $username . Has precedence over $deviceId
     * @param string    $deviceId
     *
     * @return int
     * @access public
     */
    static public function FixStatesDeviceToUserLinking($username=false, $deviceId=false) {
        $seen = 0;
        $fixed = 0;
        $processedDevices = 0;

        if ($username) {
            $devices = self::GetUserDevices($username);
        }
        else if ($deviceId){
            $devices = array($deviceId);
        }
        else {
            $devices = self::GetAllDevices();
        }
        $devicesCount = count($devices);
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZPushAdmin::FixStatesDeviceToUserLinking(): found %d devices", $devicesCount));
        foreach ($devices as $devid) {
            if (defined('LOGFIXSTATES') && LOGFIXSTATES === true) {
                ZLog::Write(LOGLEVEL_INFO, sprintf("ZPushAdmin::FixStatesDeviceToUserLinking(): Processing %d of %d. Device %s", ++$processedDevices, $devicesCount, $devid));
            }
            $users = self::ListUsers($devid);
            foreach ($users as $username) {
                $seen++;
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZPushAdmin::FixStatesDeviceToUserLinking(): linking user '%s' to device '%s'", $username, $devid));

                if (ZPush::GetStateMachine()->LinkUserDevice($username, $devid))
                    $fixed++;
            }
        }
        return array($seen, $fixed);
    }

    /**
     * Fixes states of the user linking to the states
     * and removes all obsolete states.
     *
     * @param string    $username . Has precedence over $deviceId
     * @param string    $deviceId
     *
     * @return boolean
     * @access public
     */
    static public function FixStatesUserToStatesLinking($username=false, $deviceId=false) {
        $processed = 0;
        $deleted = 0;
        $processedDevices = 0;
        $processedStates = 0;
        $deletedStates = 0;
        if ($username) {
            $devices = self::GetUserDevices($username);
        }
        else if ($deviceId){
            $devices = array($deviceId);
        }
        else {
            $devices = self::GetAllDevices();
        }
        $devicesCount = count($devices);
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZPushAdmin::FixStatesUserToStatesLinking(): found %d devices", $devicesCount));
        foreach ($devices as $devid) {
            try {
                if (defined('LOGFIXSTATES') && LOGFIXSTATES === true) {
                    ZLog::Write(LOGLEVEL_INFO, sprintf("ZPushAdmin::FixStatesUserToStatesLinking(): Processing %d of %d. Device %s", ++$processedDevices, $devicesCount, $devid));
                }
                // we work on device level
                $devicedata = ZPush::GetStateMachine()->GetState($devid, IStateMachine::DEVICEDATA);
                $knownUuids = array();

                // get all known UUIDs for this device
                foreach (self::ListUsers($devid) as $username) {
                    $device = new ASDevice($devid, ASDevice::UNDEFINED, $username, ASDevice::UNDEFINED);
                    $device->SetData($devicedata, false);

                    // get all known uuids of this device
                    $folders = $device->GetAllFolderIds();

                    // add a "false" folder id so the hierarchy UUID is retrieved
                    $folders[] = false;

                    foreach ($folders as $folderid) {
                        $uuid = $device->GetFolderUUID($folderid);
                        if ($uuid)
                            $knownUuids[] = $uuid;
                    }

                }
            }
            catch (StateNotFoundException $e) {}

            // get all uuids for deviceid from statemachine
            $existingStates = ZPush::GetStateMachine()->GetAllStatesForDevice($devid);
            $processed = count($existingStates);
            $deleted = 0;
            if (defined('LOGFIXSTATES') && LOGFIXSTATES === true) {
                ZLog::Write(LOGLEVEL_INFO, sprintf("ZPushAdmin::FixStatesUserToStatesLinking(): found %d valid uuids and %d states for device '%s'", count($knownUuids), $processed, $devid));
            }
            else {
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZPushAdmin::FixStatesUserToStatesLinking(): found %d valid uuids and %d states for device '%s'", count($knownUuids), $processed, $devid));
            }
            // remove states for all unknown uuids
            foreach ($existingStates as $obsoleteState) {
                if ($obsoleteState['type'] === IStateMachine::DEVICEDATA)
                    continue;

                if (!in_array($obsoleteState['uuid'], $knownUuids)) {
                    if (is_numeric($obsoleteState['counter']))
                        $obsoleteState['counter']++;
                    if (defined('LOGFIXSTATES') && LOGFIXSTATES === true) {
                        ZLog::Write(LOGLEVEL_INFO, sprintf("ZPushAdmin::FixStatesUserToStatesLinking(): obsoleteState TYPE '%s' UUID '%s' COUNTER %d for device '%s'", $obsoleteState['type'], $obsoleteState['uuid'], $obsoleteState['counter'], $devid));
                    }
                    ZPush::GetStateMachine()->CleanStates($devid, $obsoleteState['type'], $obsoleteState['uuid'], $obsoleteState['counter']);
                    $deleted++;
                }
            }
            $processedStates +=  $processed;
            $deletedStates += $deleted;
        }
        return array($processedStates, $deletedStates);
    }

    /**
     * Fixes hierarchy states writing folderdata states.
     *
     * @param string    $username . Has precedence over $deviceId
     * @param string    $deviceId
     *
     * @access public
     * @return array(seenDevices, seenHierarchyStates, fixedHierarchyStates, usersWithoutHierarchy)
     */
    static public function FixStatesHierarchyFolderData($username=false, $deviceId=false) {
        $devices = 0;
        $seen = 0;
        $nouuid = 0;
        $fixed = 0;
        $processedDevices = 0;

        if ($username) {
            $asdevices = self::GetUserDevices($username);
        }
        else if ($deviceId){
            $asdevices = array($deviceId);
        }
        else {
            $asdevices = self::GetAllDevices();
        }
        $devicesCount = count($asdevices);
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZPushAdmin::FixStatesHierarchyFolderData(): found %d devices", $devicesCount));
        foreach ($asdevices as $devid) {
            try {
                if (defined('LOGFIXSTATES') && LOGFIXSTATES === true) {
                    ZLog::Write(LOGLEVEL_INFO, sprintf("ZPushAdmin::FixStatesHierarchyFolderData(): Processing %d of %d. Device %s", ++$processedDevices, $devicesCount, $devid));
                }
                // get the device
                $devicedata = ZPush::GetStateMachine()->GetState($devid, IStateMachine::DEVICEDATA);
                $devices++;

                // get hierarchy UUID, check if FD is there else create it
                foreach (self::ListUsers($devid) as $username) {
                    $device = new ASDevice($devid, ASDevice::UNDEFINED, $username, ASDevice::UNDEFINED);
                    $device->SetData($devicedata, false);

                    // get hierarchy UUID
                    $hierarchyUuid = $device->GetFolderUUID(false);
                    if ($hierarchyUuid == false) {
                        ZLog::Write(LOGLEVEL_WARN, sprintf("ZPushAdmin::FixStatesHierarchyFolderData(): device %s user '%s' has no hierarchy synchronized! Ignoring.", $devid, $username));
                        $nouuid++;
                        continue;
                    }
                    $seen++;
                    $needsFixing = false;
                    $spa = false;

                    // try getting the FOLDERDATA for that state
                    try {
                        $spa = ZPush::GetStateMachine()->GetState($device->GetDeviceId(), IStateMachine::FOLDERDATA, $hierarchyUuid);
                    }
                    catch(StateNotFoundException $snfe) {
                        $needsFixing = true;
                        ZLog::Write(LOGLEVEL_INFO, sprintf("ZPushAdmin::FixStatesHierarchyFolderData(): device %s user '%s' needs fixing.", $devid, $username));
                    }
                    // Search all states, and find the highest counter for the hierarchy UUID
                    $allStates = ZPush::GetStateMachine()->GetAllStatesForDevice($devid);
                    $maxCounter = 1;
                    foreach ($allStates as $state) {
                        if ($state["uuid"] == $hierarchyUuid && $state['counter'] > $maxCounter && ($state['type'] == "" || $state['type'] == false)) {
                            $maxCounter = $state['counter'];
                        }
                    }
                    $hierarchySyncKey = StateManager::BuildStateKey($hierarchyUuid, $maxCounter);

                    if ($spa) {
                        //Don't fix if $spa->GetUuidCounter() > $maxCounter
                        if ($spa->GetSyncKey() !== $hierarchySyncKey && $spa->GetUuidCounter() < $maxCounter) {
                            $needsFixing = true;
                            ZLog::Write(LOGLEVEL_INFO, sprintf("ZPushAdmin::FixStatesHierarchyFolderData(): device %s user '%s' needs fixing. spa->GetSyncKey()='%s' hierarchySyncKey='%s'.", $devid, $username, $spa->GetSyncKey(), $hierarchySyncKey));
                        }
                    }
                    else {
                        $spa = new SyncParameters();
                    }

                    if ($needsFixing) {
                        // generate FOLDERDATA
                        $spa->SetSyncKey($hierarchySyncKey);
                        $spa->SetFolderId(false);
                        ZLog::Write(LOGLEVEL_INFO, sprintf("ZPushAdmin::FixStatesHierarchyFolderData(): device %s user '%s' needs fixing. write data for %s", $devid, $username, $spa->GetSyncKey()));
                        ZPush::GetStateMachine()->SetState($spa, $device->GetDeviceId(), IStateMachine::FOLDERDATA, $hierarchyUuid);
                        $fixed++;
                    }
                }
            }
            catch (StateNotFoundException $e) {}
        }
        return array($devices, $seen, $fixed, $nouuid);
    }

    /**
     * Fixes missing flags or parentids on additional folders.
     *
     * @param string    $username . Has precedence over $deviceId
     * @param string    $deviceId
     *
     * @access public
     * @return array(seenDevices, devicesWithAdditionalFolders, fixedAdditionalFolders)
     */
    static public function FixStatesAdditionalFolders($username=false, $deviceId=false) {
        $devices = 0;
        $devicesWithAddFolders = 0;
        $fixed = 0;
        $processedDevices = 0;

        if ($username) {
            $asdevices = self::GetUserDevices($username);
        }
        else if ($deviceId){
            $asdevices = array($deviceId);
        }
        else {
            $asdevices = self::GetAllDevices();
        }
        $devicesCount = count($asdevices);
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZPushAdmin::FixStatesAdditionalFolders(): found %d devices", $devicesCount));
        foreach ($asdevices as $devid) {
            try {
                if (defined('LOGFIXSTATES') && LOGFIXSTATES === true) {
                    ZLog::Write(LOGLEVEL_INFO, sprintf("ZPushAdmin::FixStatesAdditionalFolders(): Processing %d of %d. Device %s", ++$processedDevices, $devicesCount, $devid));
                }
                // get the device
                $devicedata = ZPush::GetStateMachine()->GetState($devid, IStateMachine::DEVICEDATA);
                $devices++;
                $needsFixing = false;

                foreach (self::ListUsers($devid) as $username) {
                    $device = new ASDevice($devid, ASDevice::UNDEFINED, $username, ASDevice::UNDEFINED);
                    $device->SetData($devicedata, false);

                    $addFolders = $device->GetAdditionalFolders();
                    if ($addFolders) {
                        $devicesWithAddFolders++;
                        foreach($addFolders as $df) {
                            if (!isset($df['flags']) || !isset($df['parentid']) ) {
                                if (!isset($df['flags'])) {
                                    $df['flags'] = 0;
                                }
                                if (!isset($df['parentid'])) {
                                    $df['parentid'] = 0;
                                }
                                $device->EditAdditionalFolder($df['folderid'], $df['name'], $df['flags'], $df['parentid']);
                                $needsFixing = true;
                            }
                        }
                    }
                    if ($device->GetData() !== false) {
                        $devicedata = $device->GetData();
                    }
                }
                if ($needsFixing) {
                    ZPush::GetStateMachine()->SetState($devicedata, $devid, IStateMachine::DEVICEDATA);
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("ZPushAdmin::FixStatesAdditionalFolders(): updated device '%s' because flags or parentids were fixed", $devid));
                    $fixed++;
                }
            }
            catch (StateNotFoundException $e) {
                ZLog::Write(LOGLEVEL_ERROR, sprintf("ZPushAdmin::FixStatesAdditionalFolders(): state for device '%s' can not be found", $devid));
            }
        }
        return array($devices, $devicesWithAddFolders, $fixed);
    }

    /**
     * Returns the list of all devices.
     *
     * @access public
     * @return array
     */
    public static function GetAllDevices() {
        if (empty(self::$devices)) {
            self::$devices = ZPush::GetStateMachine()->GetAllDevices(false);
            if (defined('LOGFIXSTATES') && LOGFIXSTATES === true) {
                ZLog::Write(LOGLEVEL_INFO, sprintf("ZPushAdmin::GetAllDevices(): found %d devices", count(self::$devices)));
            }
        }
        return self::$devices;
    }

     /**
     * Returns the list of all devices of a specific user.
     *
     * @access public
     * @return array
     */
    public static function GetUserDevices($username) {
        if (empty(self::$userDevices) && empty(self::$devices)) {
            if (defined('LOGFIXSTATES') && LOGFIXSTATES === true){
                ZLog::Write(LOGLEVEL_INFO, sprintf("ZPushAdmin::GetUserDevices(): Getting all devices for '%s'", $username));
            }
            $devices = self::GetAllDevices();
            $devicesCount = count($devices);
            self::$userDevices = array();
            $processed = 0;

            foreach ($devices as $devid) {
                if (defined('LOGFIXSTATES') && LOGFIXSTATES === true){
                    ZLog::Write(LOGLEVEL_INFO, sprintf("ZPushAdmin::GetUserDevices(): Processing %d on %d . Device %s", ++$processed , $devicesCount, $devid));
                }
                $users = self::ListUsers($devid);
                foreach ($users as $user) {
                    if ($user === $username){
                        ZLog::Write(LOGLEVEL_INFO, sprintf("ZPushAdmin::GetUserDevices(): Found user '%s' into device %s", $username , $devid));
                        self::$userDevices[] = $devid;
                        continue;
                    }
                }
            }

            if (empty(self::$userDevices) && $username!==false && defined('LOGFIXSTATES') && LOGFIXSTATES === true){
                ZLog::Write(LOGLEVEL_INFO, sprintf("ZPushAdmin::GetUserDevices(): No devices found for '%s'", $username));
            }else if(defined('LOGFIXSTATES') && LOGFIXSTATES === true){
                ZLog::Write(LOGLEVEL_INFO, sprintf("ZPushAdmin::GetUserDevices(): Found user '%s' into '%d' devices", $username , count(self::$userDevices)));
            }
        }
        return self::$userDevices;
    }

}
