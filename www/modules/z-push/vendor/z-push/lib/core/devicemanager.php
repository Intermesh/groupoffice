<?php
/***********************************************
* File      :   devicemanager.php
* Project   :   Z-Push
* Descr     :   Manages device relevant data, provisioning,
*               loop detection and device states.
*               The DeviceManager uses a IStateMachine
*               implementation with IStateMachine::DEVICEDATA
*               to save device relevant data.
*
* Created   :   11.04.2011
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

class DeviceManager {
    // broken message indicators
    const MSG_BROKEN_UNKNOWN = 1;
    const MSG_BROKEN_CAUSINGLOOP = 2;
    const MSG_BROKEN_SEMANTICERR = 4;

    const FLD_SYNC_INITIALIZED = 1;
    const FLD_SYNC_INPROGRESS = 2;
    const FLD_SYNC_COMPLETED = 4;

    // new types need to be added to Request::HEX_EXTENDED2 filter
    const FLD_ORIGIN_USER = "U";
    const FLD_ORIGIN_CONFIG = "C";
    const FLD_ORIGIN_SHARED = "S";
    const FLD_ORIGIN_GAB = "G";
    const FLD_ORIGIN_IMPERSONATED = "I";

    const FLD_FLAGS_NONE = 0;
    const FLD_FLAGS_SENDASOWNER = 1;
    const FLD_FLAGS_TRACKSHARENAME = 2;
    const FLD_FLAGS_CALENDARREMINDERS = 4;
    const FLD_FLAGS_NOREADONLYNOTIFY = 8;

    private $device;
    private $deviceHash;
    private $saveDevice;
    private $statemachine;
    private $stateManager;
    private $incomingData = 0;
    private $outgoingData = 0;

    private $windowSize;
    private $latestFolder;

    private $loopdetection;
    private $hierarchySyncRequired;
    private $additionalFoldersHash;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct() {
        $this->statemachine = ZPush::GetStateMachine();
        $this->deviceHash = false;
        $this->devid = Request::GetDeviceID();
        $this->saveDevice = true;
        $this->windowSize = array();
        $this->latestFolder = false;
        $this->hierarchySyncRequired = false;

        // only continue if deviceid is set
        if ($this->devid) {
            $this->device = new ASDevice($this->devid, Request::GetDeviceType(), Request::GetGETUser(), Request::GetUserAgent());
            $this->loadDeviceData();

            ZPush::GetTopCollector()->SetUserAgent($this->device->GetDeviceUserAgent());
        }
        else
            throw new FatalNotImplementedException("Can not proceed without a device id.");

        $this->loopdetection = new LoopDetection();
        $this->loopdetection->ProcessLoopDetectionInit();
        $this->loopdetection->ProcessLoopDetectionPreviousConnectionFailed();

        $this->stateManager = new StateManager();
        $this->stateManager->SetDevice($this->device);

        $this->additionalFoldersHash = $this->getAdditionalFoldersHash();

        if ($this->IsKoe() && $this->device->GetKoeVersion() !== false) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("KOE: %s / %s / %s", $this->device->GetKoeVersion(), $this->device->GetKoeBuild(), strftime("%Y-%m-%d %H:%M", $this->device->GetKoeBuildDate())));
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("KOE Capabilities: %s ", count($this->device->GetKoeCapabilities()) ? implode(',', $this->device->GetKoeCapabilities()) : 'unknown'));
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("KOE Last confirmed access: %s (may be up to 7h old)", ($this->device->GetKoeLastAccess() ? strftime("%Y-%m-%d %H:%M", $this->device->GetKoeLastAccess()) : 'unknown')));
        }
    }

    /**
     * Load another different device.
     * @param ASDevice $asDevice
     */
    public function SetDevice($asDevice) {
        $this->device = $asDevice;
        $this->loadDeviceData();
        $this->stateManager->SetDevice($this->device);
    }

    /**
     * Returns the StateManager for the current device
     *
     * @access public
     * @return StateManager
     */
    public function GetStateManager() {
        return $this->stateManager;
    }

    /**----------------------------------------------------------------------------------------------------------
     * Device operations
     */

    /**
     * Announces amount of transmitted data to the DeviceManager
     *
     * @param int           $datacounter
     *
     * @access public
     * @return boolean
     */
    public function SentData($datacounter) {
        // TODO save this somewhere
        $this->incomingData = Request::GetContentLength();
        $this->outgoingData = $datacounter;
    }

    /**
     * Called at the end of the request
     * Statistics about received/sent data is saved here
     *
     * @access public
     * @return boolean
     */
    public function Save() {
        // TODO save other stuff

        // check if previousily ignored messages were synchronized for the current folder
        // on multifolder operations of AS14 this is done by setLatestFolder()
        if ($this->latestFolder !== false)
            $this->checkBrokenMessages($this->latestFolder);

        // update the user agent and AS version on the device
        $this->device->SetUserAgent(Request::GetUserAgent());
        $this->device->SetASVersion(Request::GetProtocolVersion());

        // update data from the OL plugin (if available)
        if (Request::HasKoeStats()) {
            $this->device->SetKoeVersion(Request::GetKoeVersion());
            $this->device->SetKoeBuild(Request::GetKoeBuild());
            $this->device->SetKoeBuildDate(Request::GetKoeBuildDate());
            $this->device->SetKoeCapabilities(Request::GetKoeCapabilities());
            // update KOE last access time if it's at least 6h old
            if ($this->device->GetKoeLastAccess() < time() - 21600) {
                $this->device->SetKoeLastAccess(time());
            }
        }

        // data to be saved
        $data = $this->device->GetData();
        if ($data && Request::IsValidDeviceID() && $this->saveDevice) {
            ZLog::Write(LOGLEVEL_DEBUG, "DeviceManager->Save(): Device data changed");

            try {
                // check if this is the first time the device data is saved and it is authenticated. If so, link the user to the device id
                if ($this->device->IsNewDevice() && RequestProcessor::isUserAuthenticated()) {
                    ZLog::Write(LOGLEVEL_INFO, sprintf("Linking device ID '%s' to user '%s'", $this->devid, $this->device->GetDeviceUser()));
                    $this->statemachine->LinkUserDevice($this->device->GetDeviceUser(), $this->devid);
                }

                if (RequestProcessor::isUserAuthenticated() || $this->device->GetForceSave() ) {
                    $this->statemachine->SetState($data, $this->devid, IStateMachine::DEVICEDATA);
                    ZLog::Write(LOGLEVEL_DEBUG, "DeviceManager->Save(): Device data saved");
                }
            }
            catch (StateNotFoundException $snfex) {
                ZLog::Write(LOGLEVEL_ERROR, "DeviceManager->Save(): Exception: ". $snfex->getMessage());
            }
        }

        // remove old search data
        $oldpid = $this->loopdetection->ProcessLoopDetectionGetOutdatedSearchPID();
        if ($oldpid) {
            ZPush::GetBackend()->GetSearchProvider()->TerminateSearch($oldpid);
        }

        // we terminated this process
        if ($this->loopdetection)
            $this->loopdetection->ProcessLoopDetectionTerminate();

        return true;
    }

    /**
     * Sets if the AS Device should automatically be saved when terminating the request.
     *
     * @param boolean $doSave
     *
     * @access public
     * @return void
     */
    public function DoAutomaticASDeviceSaving($doSave) {
        ZLog::Write(LOGLEVEL_DEBUG, "DeviceManager->DoAutomaticASDeviceSaving(): save automatically: ". Utils::PrintAsString($doSave));
        $this->saveDevice = $doSave;
    }

    /**
     * Newer mobiles send extensive device informations with the Settings command
     * These informations are saved in the ASDevice
     *
     * @param SyncDeviceInformation     $deviceinformation
     *
     * @access public
     * @return boolean
     */
    public function SaveDeviceInformation($deviceinformation) {
        ZLog::Write(LOGLEVEL_DEBUG, "Saving submitted device information");

        // set the user agent
        if (isset($deviceinformation->useragent))
            $this->device->SetUserAgent($deviceinformation->useragent);

        // save other informations
        foreach (array("model", "imei", "friendlyname", "os", "oslanguage", "phonenumber", "mobileoperator", "enableoutboundsms") as $info) {
            if (isset($deviceinformation->$info) && $deviceinformation->$info != "") {
                $this->device->__set("device".$info, $deviceinformation->$info);
            }
        }
        return true;
    }

    /**----------------------------------------------------------------------------------------------------------
     * Provisioning operations
     */

    /**
     * Checks if the sent policykey matches the latest policykey
     * saved for the device
     *
     * @param string        $policykey
     * @param boolean       $noDebug        (opt) by default, debug message is shown
     * @param boolean       $checkPolicies  (opt) by default check if the provisioning policies changed
     *
     * @access public
     * @return boolean
     */
    public function ProvisioningRequired($policykey, $noDebug = false, $checkPolicies = true) {
        $this->loadDeviceData();

        // check if a remote wipe is required
        if ($this->device->GetWipeStatus() > SYNC_PROVISION_RWSTATUS_OK) {
            ZLog::Write(LOGLEVEL_INFO, sprintf("DeviceManager->ProvisioningRequired('%s'): YES, remote wipe requested", $policykey));
            return true;
        }

        $p = ( ($this->device->GetWipeStatus() != SYNC_PROVISION_RWSTATUS_NA && $policykey != $this->device->GetPolicyKey()) ||
              Request::WasPolicyKeySent() && $this->device->GetPolicyKey() == ASDevice::UNDEFINED );

        if (!$noDebug || $p)
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("DeviceManager->ProvisioningRequired('%s') saved device key '%s': %s", $policykey, $this->device->GetPolicyKey(), Utils::PrintAsString($p)));

        if ($checkPolicies) {
            $policyHash = $this->GetProvisioningObject()->GetPolicyHash();
            if ($this->device->hasPolicyhash() && $this->device->getPolicyhash() != $policyHash) {
                $p = true;
                ZLog::Write(LOGLEVEL_INFO, sprintf("DeviceManager->ProvisioningRequired(): saved policy hash '%s' changed '%s'. Provisioning required.", $this->device->getPolicyhash(), $policyHash));
            }
        }

        return $p;
    }

    /**
     * Generates a new Policykey
     *
     * @access public
     * @return int
     */
    public function GenerateProvisioningPolicyKey() {
        return mt_rand(100000000, 999999999);
    }

    /**
     * Attributes a provisioned policykey to a device
     *
     * @param int           $policykey
     *
     * @access public
     * @return boolean      status
     */
    public function SetProvisioningPolicyKey($policykey) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("DeviceManager->SetPolicyKey('%s')", $policykey));
        return $this->device->SetPolicyKey($policykey);
    }

    /**
     * Builds a Provisioning SyncObject with policies
     *
     * @param boolean   $logPolicies  optional, determines if the policies and values should be logged. Default: false
     *
     * @access public
     * @return SyncProvisioning
     */
    public function GetProvisioningObject($logPolicies = false) {
        $policyName = $this->getPolicyName();
        $p = SyncProvisioning::GetObjectWithPolicies($this->getProvisioningPolicies($policyName), $logPolicies);
        $p->PolicyName = $policyName;
        return $p;
    }

    /**
     * Returns the status of the remote wipe policy
     *
     * @access public
     * @return int          returns the current status of the device - SYNC_PROVISION_RWSTATUS_*
     */
    public function GetProvisioningWipeStatus() {
        return $this->device->GetWipeStatus();
    }

    /**
     * Updates the status of the remote wipe
     *
     * @param int           $status - SYNC_PROVISION_RWSTATUS_*
     *
     * @access public
     * @return boolean      could fail if trying to update status to a wipe status which was not requested before
     */
    public function SetProvisioningWipeStatus($status) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("DeviceManager->SetProvisioningWipeStatus() change from '%d' to '%d'",$this->device->GetWipeStatus(), $status));

        if ($status > SYNC_PROVISION_RWSTATUS_OK && !($this->device->GetWipeStatus() > SYNC_PROVISION_RWSTATUS_OK)) {
            ZLog::Write(LOGLEVEL_ERROR, "Not permitted to update remote wipe status to a higher value as remote wipe was not initiated!");
            return false;
        }
        $this->device->SetWipeStatus($status);
        return true;
    }

    /**
     * Saves the policy hash and name in device's state.
     *
     * @param SyncProvisioning  $provisioning
     *
     * @access public
     * @return void
     */
    public function SavePolicyHashAndName($provisioning) {
        // save policies' hash and name
        $this->device->SetPolicyname($provisioning->PolicyName);
        $this->device->SetPolicyhash($provisioning->GetPolicyHash());
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("DeviceManager->SavePolicyHashAndName(): Set policy: %s with hash: %s", $this->device->GetPolicyname(), $this->device->GetPolicyhash()));
    }


    /**----------------------------------------------------------------------------------------------------------
     * LEGACY AS 1.0 and WRAPPER operations
     */

    /**
     * Returns a wrapped Importer & Exporter to use the
     * HierarchyChache
     *
     * @see ChangesMemoryWrapper
     * @access public
     * @return object           HierarchyCache
     */
    public function GetHierarchyChangesWrapper() {
        return $this->device->GetHierarchyCache();
    }

    /**
     * Initializes the HierarchyCache for legacy syncs
     * this is for AS 1.0 compatibility:
     *      save folder information synched with GetHierarchy()
     *
     * @param string    $folders            Array with folder information
     *
     * @access public
     * @return boolean
     */
    public function InitializeFolderCache($folders) {
        $this->stateManager->SetDevice($this->device);
        return $this->stateManager->InitializeFolderCache($folders);
    }

    /**
     * Returns the ActiveSync folder type for a FolderID
     *
     * @param string    $folderid
     *
     * @access public
     * @return int/boolean        boolean if no type is found
     */
    public function GetFolderTypeFromCacheById($folderid) {
        return $this->device->GetFolderType($folderid);
    }

    /**
     * Returns a FolderID of default classes
     * this is for AS 1.0 compatibility:
     *      this information was made available during GetHierarchy()
     *
     * @param string    $class              The class requested
     *
     * @access public
     * @return string
     * @throws NoHierarchyCacheAvailableException
     */
    public function GetFolderIdFromCacheByClass($class) {
        $folderidforClass = false;
        // look at the default foldertype for this class
        $type = ZPush::getDefaultFolderTypeFromFolderClass($class);

        if ($type && $type > SYNC_FOLDER_TYPE_OTHER && $type < SYNC_FOLDER_TYPE_USER_MAIL) {
            $folderids = $this->device->GetAllFolderIds();
            foreach ($folderids as $folderid) {
                if ($type == $this->device->GetFolderType($folderid)) {
                    $folderidforClass = $folderid;
                    break;
                }
            }

            // Old Palm Treos always do initial sync for calendar and contacts, even if they are not made available by the backend.
            // We need to fake these folderids, allowing a fake sync/ping, even if they are not supported by the backend
            // if the folderid would be available, they would already be returned in the above statement
            if ($folderidforClass == false && ($type == SYNC_FOLDER_TYPE_APPOINTMENT || $type == SYNC_FOLDER_TYPE_CONTACT))
                $folderidforClass = SYNC_FOLDER_TYPE_DUMMY;
        }

        ZLog::Write(LOGLEVEL_DEBUG, sprintf("DeviceManager->GetFolderIdFromCacheByClass('%s'): '%s' => '%s'", $class, $type, $folderidforClass));
        return $folderidforClass;
    }

    /**
     * Returns a FolderClass for a FolderID which is known to the mobile
     *
     * @param string    $folderid
     *
     * @access public
     * @return int
     * @throws NoHierarchyCacheAvailableException, NotImplementedException
     */
    public function GetFolderClassFromCacheByID($folderid) {
        //TODO check if the parent folder exists and is also beeing synchronized
        $typeFromCache = $this->device->GetFolderType($folderid);
        if ($typeFromCache === false)
            throw new NoHierarchyCacheAvailableException(sprintf("Folderid '%s' is not fully synchronized on the device", $folderid));

        $class = ZPush::GetFolderClassFromFolderType($typeFromCache);
        if ($class === false)
            throw new NotImplementedException(sprintf("Folderid '%s' is saved to be of type '%d' but this type is not implemented", $folderid, $typeFromCache));

        return $class;
    }

    /**
     * Returns the backend folder id of the KOE GAB folder.
     * This comes either from the configuration or from the device data.
     *
     * @return string|boolean   returns false if not set or found
     */
    public function GetKoeGabBackendFolderId() {
        $gabid = false;
        if (KOE_CAPABILITY_GAB) {
            if (KOE_GAB_FOLDERID) {
                $gabid = KOE_GAB_FOLDERID;
            }
            else if (KOE_GAB_STORE && KOE_GAB_NAME) {
                $gabid = $this->device->GetKoeGabBackendFolderId();
            }
        }
        return $gabid;
    }

    /**
     * Returns the additional folders as SyncFolder objects.
     *
     * @access public
     * @return array of SyncFolder with backendids as keys
     */
    public function GetAdditionalUserSyncFolders() {
        $folders = array();

        // In impersonated stores, no additional folders will be synchronized
        if (Request::GetImpersonatedUser()) {
            return $folders;
        }

        foreach($this->device->GetAdditionalFolders() as $df) {
            if (!isset($df['flags'])) {
                $df['flags'] = 0;
                ZLog::Write(LOGLEVEL_WARN, sprintf("DeviceManager->GetAdditionalUserSyncFolders(): Additional folder '%s' has no flags. Please run 'z-push-admin -a fixstates' to fix this issue.", $df['name']));
            }
            if (!isset($df['parentid'])) {
                $df['parentid'] = '0';
                ZLog::Write(LOGLEVEL_WARN, sprintf("DeviceManager->GetAdditionalUserSyncFolders(): Additional folder '%s' has no parentid. Please run 'z-push-admin -a fixstates' to fix this issue.", $df['name']));
            }

            $folder = $this->BuildSyncFolderObject($df['store'], $df['folderid'], $df['parentid'], $df['name'], $df['type'], $df['flags'], DeviceManager::FLD_ORIGIN_SHARED);
            $folders[$folder->BackendId] = $folder;
        }

        // ZO-40: add KOE GAB folder
        if (KOE_CAPABILITY_GAB && $this->IsKoe() && KOE_GAB_STORE != "" && KOE_GAB_NAME != "") {
            // if KOE_GAB_FOLDERID is set, use it
            if (KOE_GAB_FOLDERID != "") {
                $folder = $this->BuildSyncFolderObject(KOE_GAB_STORE, KOE_GAB_FOLDERID, '0', KOE_GAB_NAME, SYNC_FOLDER_TYPE_USER_APPOINTMENT, 0, DeviceManager::FLD_ORIGIN_GAB);
                $folders[$folder->BackendId] = $folder;
            }
            else {
                // get the GAB id from the device and from the backend
                $deviceGabId = $this->device->GetKoeGabBackendFolderId();
                if (!ZPush::GetBackend()->Setup(KOE_GAB_STORE)) {
                    ZLog::Write(LOGLEVEL_WARN, sprintf("DeviceManager->GetAdditionalUserSyncFolders(): setup for store '%s' failed. Unable to search for KOE GAB folder.", KOE_GAB_STORE));
                }
                else {
                    $backendGabId = ZPush::GetBackend()->GetKoeGabBackendFolderId(KOE_GAB_NAME);
                    if ($deviceGabId !== $backendGabId) {
                        ZLog::Write(LOGLEVEL_DEBUG, sprintf("DeviceManager->GetAdditionalUserSyncFolders(): Backend found different KOE GAB backend folderid: '%s'. Updating ASDevice.", $backendGabId));
                        $this->device->SetKoeGabBackendFolderId($backendGabId);
                    }

                    if ($backendGabId) {
                        $folders[$backendGabId] = $this->BuildSyncFolderObject(KOE_GAB_STORE, $backendGabId, '0', KOE_GAB_NAME, SYNC_FOLDER_TYPE_USER_APPOINTMENT, 0, DeviceManager::FLD_ORIGIN_GAB);
                    }
                }
            }
        }
        return $folders;
    }

    /**
     * Get the store of an additional folder.
     *
     * @param string    $folderid
     *
     * @access public
     * @return boolean|string
     */
    public function GetAdditionalUserSyncFolder($folderid) {
        // is this the KOE GAB folder?
        if ($folderid && $folderid === $this->GetKoeGabBackendFolderId()) {
            return KOE_GAB_STORE;
        }

        $f = $this->device->GetAdditionalFolder($folderid);
        if ($f) {
            return $f;
        }

        return false;
    }

    /**
     * Checks if the message should be streamed to a mobile
     * Should always be called before a message is sent to the mobile
     * Returns true if there is something wrong and the content could break the
     * synchronization
     *
     * @param string        $id         message id
     * @param SyncObject    &$message   the method could edit the message to change the flags
     *
     * @access public
     * @return boolean          returns true if the message should NOT be send!
     */
    public function DoNotStreamMessage($id, &$message) {
        $folderid = $this->getLatestFolder();

        if (isset($message->parentid))
            $folder = $message->parentid;

        // message was identified to be causing a loop
        if ($this->loopdetection->IgnoreNextMessage(true, $id, $folderid)) {
            $this->AnnounceIgnoredMessage($folderid, $id, $message, self::MSG_BROKEN_CAUSINGLOOP);
            return true;
        }

        // message is semantically incorrect
        if (!$message->Check(true)) {
            $this->AnnounceIgnoredMessage($folderid, $id, $message, self::MSG_BROKEN_SEMANTICERR);
            return true;
        }

        // check if this message is broken
        if ($this->device->HasIgnoredMessage($folderid, $id)) {
            // reset the flags so the message is always streamed with <Add>
            $message->flags = false;

            // track the broken message in the loop detection
            $this->loopdetection->SetBrokenMessage($folderid, $id);
        }
        return false;
    }

    /**
     * Removes device information about a broken message as it is been removed from the mobile.
     *
     * @param string        $id         message id
     *
     * @access public
     * @return boolean
     */
    public function RemoveBrokenMessage($id) {
        $folderid = $this->getLatestFolder();
        if ($this->device->RemoveIgnoredMessage($folderid, $id)) {
            ZLog::Write(LOGLEVEL_INFO, sprintf("DeviceManager->RemoveBrokenMessage('%s', '%s'): cleared data about previously ignored message", $folderid, $id));
            return true;
        }
        return false;
    }

    /**
     * Amount of items to me synchronized
     *
     * @param string    $folderid
     * @param string    $type
     * @param int       $queuedmessages;
     * @access public
     * @return int
     */
    public function GetWindowSize($folderid, $uuid, $statecounter, $queuedmessages) {
        if (isset($this->windowSize[$folderid]))
            $items = $this->windowSize[$folderid];
        else
            $items = WINDOW_SIZE_MAX; // 512 by default

        $this->setLatestFolder($folderid);

        // detect if this is a loop condition
        $loop = $this->loopdetection->Detect($folderid, $uuid, $statecounter, $items, $queuedmessages);
        if ($loop !== false) {
            if ($loop === true) {
                $items = ($items == 0) ? 0: 1+($this->loopdetection->IgnoreNextMessage(false)?1:0) ;
            }
            else {
                // we got a new suggested window size
                $items = $loop;
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("Mobile loop pre stage detected! Forcing smaller window size of %d before entering loop detection mode", $items));
            }
        }

        if ($items >= 0 && $items <= 2)
            ZLog::Write(LOGLEVEL_WARN, sprintf("Mobile loop detected! Messages sent to the mobile will be restricted to %d items in order to identify the conflict", $items));

        return $items;
    }

    /**
     * Sets the amount of items the device is requesting
     *
     * @param string    $folderid
     * @param int       $maxItems
     *
     * @access public
     * @return boolean
     */
    public function SetWindowSize($folderid, $maxItems) {
        $this->windowSize[$folderid] = $maxItems;

        return true;
    }

     /**
     * Sets the supported fields transmitted by the device for a certain folder
     *
     * @param string    $folderid
     * @param array     $fieldlist          supported fields
     *
     * @access public
     * @return boolean
     */
    public function SetSupportedFields($folderid, $fieldlist) {
        return $this->device->SetSupportedFields($folderid, $fieldlist);
    }

    /**
     * Gets the supported fields transmitted previousely by the device
     * for a certain folder
     *
     * @param string    $folderid
     *
     * @access public
     * @return array/boolean
     */
    public function GetSupportedFields($folderid) {
        return $this->device->GetSupportedFields($folderid);
    }

    /**
     * Returns the maximum filter type for a folder.
     * This might be limited globally, per device or per folder.
     *
     * @param string    $folderid
     *
     * @access public
     * @return int
     */
    public function GetFilterType($folderid, $backendFolderId) {
        global $specialSyncFilter;
        // either globally configured SYNC_FILTERTIME_MAX or ALL (no limit)
        $maxAllowed = (defined('SYNC_FILTERTIME_MAX') && SYNC_FILTERTIME_MAX > SYNC_FILTERTYPE_ALL) ? SYNC_FILTERTIME_MAX : SYNC_FILTERTYPE_ALL;

        // TODO we could/should check for a specific value for the folder, if it's available
        $maxDevice = $this->device->GetSyncFilterType();

        // ALL has a value of 0, all limitations have higher integer values, see SYNC_FILTERTYPE_ALL definition
        if ($maxDevice !== false && $maxDevice > SYNC_FILTERTYPE_ALL && ($maxAllowed == SYNC_FILTERTYPE_ALL || $maxDevice < $maxAllowed)) {
            $maxAllowed = $maxDevice;
        }

        if (is_array($specialSyncFilter)) {
            $store = ZPush::GetAdditionalSyncFolderStore($backendFolderId);
            // the store is only available when this is a shared folder (but might also be statically configured)
            if ($store) {
                $origin = Utils::GetFolderOriginFromId($folderid);
                // do not limit when the owner or impersonated user is synching!
                if ($origin == DeviceManager::FLD_ORIGIN_USER || $origin == DeviceManager::FLD_ORIGIN_IMPERSONATED) {
                    ZLog::Write(LOGLEVEL_DEBUG, "Not checking for specific sync limit as this is the owner/impersonated user.");
                }
                else {
                    $spKey = false;
                    $spFilter = false;
                    // 1. step: check if there is a general limitation for the store
                    if (array_key_exists($store, $specialSyncFilter)) {
                        $spFilter = $specialSyncFilter[$store];
                        ZLog::Write(LOGLEVEL_DEBUG, sprintf("Limit sync due to configured limitation on the store: '%s': %s",$store, $spFilter));
                    }

                    // 2. step: check if there is a limitation for the hashed ID (for shared/configured stores)
                    $spKey= $store .'/'. $folderid;
                    if (array_key_exists($spKey, $specialSyncFilter)) {
                        $spFilter = $specialSyncFilter[$spKey];
                        ZLog::Write(LOGLEVEL_DEBUG, sprintf("Limit sync due to configured limitation on the folder: '%s': %s", $spKey, $spFilter));
                    }

                    // 3. step: check if there is a limitation for the backendId
                    $spKey= $store .'/'. $backendFolderId;
                    if (array_key_exists($spKey, $specialSyncFilter)) {
                        $spFilter = $specialSyncFilter[$spKey];
                        ZLog::Write(LOGLEVEL_DEBUG, sprintf("Limit sync due to configured limitation on the folder: '%s': %s", $spKey, $spFilter));
                    }
                    if ($spFilter) {
                        $maxAllowed = $spFilter;
                    }
                }
            }
        }

        return $maxAllowed;
    }

    /**
     * Removes all linked states of a specific folder.
     * During next request the folder is resynchronized.
     *
     * @param string    $folderid
     *
     * @access public
     * @return boolean
     */
    public function ForceFolderResync($folderid) {
        ZLog::Write(LOGLEVEL_INFO, sprintf("DeviceManager->ForceFolderResync('%s'): folder resync", $folderid));

        // delete folder states
        StateManager::UnLinkState($this->device, $folderid);

        return true;
    }

    /**
     * Removes all linked states from a device.
     * During next requests a full resync is triggered.
     *
     * @access public
     * @return boolean
     */
    public function ForceFullResync() {
        ZLog::Write(LOGLEVEL_INFO, "Full device resync requested");

        // delete all other uuids
        foreach ($this->device->GetAllFolderIds() as $folderid)
            $uuid = StateManager::UnLinkState($this->device, $folderid);

        // delete hierarchy states
        StateManager::UnLinkState($this->device, false);

        return true;
    }

    /**
     * Indicates if the hierarchy should be resynchronized based on the general folder state and
     * if additional folders changed.
     *
     * @access public
     * @return boolean
     */
    public function IsHierarchySyncRequired() {
        $this->loadDeviceData();

        if ($this->loopdetection->ProcessLoopDetectionIsHierarchySyncAdvised()) {
            return true;
        }

        // if the hash of the additional folders changed, we have to sync the hierarchy
        if ($this->additionalFoldersHash != $this->getAdditionalFoldersHash()) {
            $this->hierarchySyncRequired = true;
        }

        // check if a hierarchy sync might be necessary
        if ($this->device->GetFolderUUID(false) === false)
            $this->hierarchySyncRequired = true;

        return $this->hierarchySyncRequired;
    }

    private function getAdditionalFoldersHash() {
        return md5(serialize($this->device->GetAdditionalFolders()));
    }

    /**
     * Indicates if a full hierarchy resync should be triggered due to loops
     *
     * @access public
     * @return boolean
     */
    public function IsHierarchyFullResyncRequired() {
        // do not check for loop detection, if the foldersync is not yet complete
        if ($this->GetFolderSyncComplete() === false) {
            ZLog::Write(LOGLEVEL_INFO, "DeviceManager->IsHierarchyFullResyncRequired(): aborted, as exporting of folders has not yet completed");
            return false;
        }
        // check for potential process loops like described in ZP-5
        return $this->loopdetection->ProcessLoopDetectionIsHierarchyResyncRequired();
    }

    /**
     * Indicates if an Outlook via ActiveSync is connected.
     *
     * @access public
     * @return boolean
     */
    public function IsKoe() {
        if (Request::IsOutlook() && ($this->device->GetKoeVersion() !== false || Request::HasKoeStats())) {
            return true;
        }
        return false;
    }

    /**
     * Indicates if the KOE client supports a feature.
     *
     * @param string $feature
     *
     * @access public
     * @return boolean
     */
    public function HasKoeFeature($feature) {
        $capabilities = $this->device->GetKoeCapabilities();
        // in a settings request the capabilities might not yet be stored in the device
        if (empty($capabilities)) {
            $capabilities = Request::GetKoeCapabilities();
        }
        return in_array($feature, $capabilities);
    }

    /**
     * Indicates if the connected device is Outlook + KOE and supports the
     * secondary contact folder synchronization.
     *
     *  @access public
     *  @return boolean
     */
    public function IsKoeSupportingSecondaryContacts() {
        return defined('KOE_CAPABILITY_SECONDARYCONTACTS') && KOE_CAPABILITY_SECONDARYCONTACTS && $this->IsKoe() && $this->HasKoeFeature('secondarycontacts');
    }

    /**
     * Adds an Exceptions to the process tracking
     *
     * @param Exception     $exception
     *
     * @access public
     * @return boolean
     */
    public function AnnounceProcessException($exception) {
        return $this->loopdetection->ProcessLoopDetectionAddException($exception);
    }

    /**
     * Adds a non-ok status for a folderid to the process tracking.
     * On 'false' a hierarchy status is assumed
     *
     * @access public
     * @return boolean
     */
    public function AnnounceProcessStatus($folderid, $status) {
        return $this->loopdetection->ProcessLoopDetectionAddStatus($folderid, $status);
    }

    /**
     * Announces that the current process is a push connection to the process loop
     * detection and to the Top collector
     *
     * @access public
     * @return boolean
     */
    public function AnnounceProcessAsPush() {
        ZLog::Write(LOGLEVEL_DEBUG, "Announce process as PUSH connection");

        return $this->loopdetection->ProcessLoopDetectionSetAsPush() && ZPush::GetTopCollector()->SetAsPushConnection();
    }

    /**
     * Checks if the given counter for a certain uuid+folderid was already exported or modified.
     * This is called when a heartbeat request found changes to make sure that the same
     * changes are not exported twice, as during the heartbeat there could have been a normal
     * sync request.
     *
     * @param string $folderid          folder id
     * @param string $uuid              synkkey
     * @param string $counter           synckey counter
     *
     * @access public
     * @return boolean                  indicating if an uuid+counter were exported (with changes) before
     */
    public function CheckHearbeatStateIntegrity($folderid, $uuid, $counter) {
        return $this->loopdetection->IsSyncStateObsolete($folderid, $uuid, $counter);
    }

    /**
     * Marks a syncstate as obsolete for Heartbeat, as e.g. an import was started using it.
     *
     * @param string $folderid          folder id
     * @param string $uuid              synkkey
     * @param string $counter           synckey counter
     *
     * @access public
     * @return
     */
    public function SetHeartbeatStateIntegrity($folderid, $uuid, $counter) {
        return $this->loopdetection->SetSyncStateUsage($folderid, $uuid, $counter);
    }

    /**
     * Checks the data integrity of the data in the hierarchy cache and the data of the content data (synchronized folders).
     * If a folder is deleted, the sync states could still be on the server (and being loaded by PING) while
     * the folder is not being synchronized anymore. See also https://jira.z-hub.io/browse/ZP-1077
     *
     * @access public
     * @return boolean
     */
    public function CheckFolderData() {
        ZLog::Write(LOGLEVEL_DEBUG, "DeviceManager->CheckFolderData() checking integrity of hierarchy cache with synchronized folders");

        $hc = $this->device->GetHierarchyCache();
        $notInCache = array();
        foreach ($this->device->GetAllFolderIds() as $folderid) {
            $uuid = $this->device->GetFolderUUID($folderid);
            if ($uuid) {
                // has a UUID but is not in the cache?! This is deleted, remove the states.
                if (! $hc->GetFolder($folderid)) {
                    ZLog::Write(LOGLEVEL_WARN, sprintf("DeviceManager->CheckFolderData(): Folder '%s' has sync states but is not in the hierarchy cache. Removing states.", $folderid));
                    StateManager::UnLinkState($this->device, $folderid);
                }
            }
        }
        return true;
    }

    /**
     * Sets the current status of the folder
     *
     * @param string     $folderid          folder id
     * @param int        $statusflag        current status: DeviceManager::FLD_SYNC_INITIALIZED, DeviceManager::FLD_SYNC_INPROGRESS, DeviceManager::FLD_SYNC_COMPLETED
     *
     * @access public
     * @return
     */
    public function SetFolderSyncStatus($folderid, $statusflag) {
        $currentStatus = $this->device->GetFolderSyncStatus($folderid);

        // status available or just initialized
        if (isset($currentStatus[ASDevice::FOLDERSYNCSTATUS]) || $statusflag == self::FLD_SYNC_INITIALIZED) {
            // only update if there is a change
	        if ((!$currentStatus || $statusflag !== $currentStatus[ASDevice::FOLDERSYNCSTATUS] && $statusflag) != self::FLD_SYNC_COMPLETED) {
                $this->device->SetFolderSyncStatus($folderid, array(ASDevice::FOLDERSYNCSTATUS => $statusflag));
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("SetFolderSyncStatus(): set %s for %s", $statusflag, $folderid));
            }
            // if completed, remove the status
            else if ($statusflag == self::FLD_SYNC_COMPLETED) {
                $this->device->SetFolderSyncStatus($folderid, false);
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("SetFolderSyncStatus(): completed for %s", $folderid));
            }
        }

        return true;
    }

    /**
     * Indicates if a folder is synchronizing by the saved status.
     *
     * @param string     $folderid          folder id
     *
     * @access public
     * @return boolean
     */
    public function HasFolderSyncStatus($folderid) {
        $currentStatus = $this->device->GetFolderSyncStatus($folderid);

        // status available ?
        $hasStatus = isset($currentStatus[ASDevice::FOLDERSYNCSTATUS]);
        if ($hasStatus) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("HasFolderSyncStatus(): saved folder status for %s: %s", $folderid, $currentStatus[ASDevice::FOLDERSYNCSTATUS]));
        }

        return $hasStatus;
    }

    /**
     * Returns the indicator if the FolderSync was completed successfully  (all folders synchronized)
     *
     * @access public
     * @return boolean
     */
    public function GetFolderSyncComplete() {
        return $this->device->GetFolderSyncComplete();
    }

    /**
     * Sets if the FolderSync was completed successfully (all folders synchronized)
     *
     * @param boolean   $complete   indicating if all folders were sent
     *
     * @access public
     * @return boolean
     */
    public function SetFolderSyncComplete($complete, $user = false, $devid = false) {
        $this->device->SetFolderSyncComplete($complete);
    }

    /**
     * Removes the Loop detection data for a user & device
     *
     * @param string    $user
     * @param string    $devid
     *
     * @access public
     * @return boolean
     */
    public function ClearLoopDetectionData($user, $devid) {
        if ($user == false || $devid == false) {
            return false;
        }
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("DeviceManager->ClearLoopDetectionData(): clearing data for user '%s' and device '%s'", $user, $devid));
        return $this->loopdetection->ClearData($user, $devid);
    }

    /**
     * Indicates if the device needs an AS version update
     *
     * @access public
     * @return boolean
     */
    public function AnnounceASVersion() {
        $latest = ZPush::GetSupportedASVersion();
        $announced = $this->device->GetAnnouncedASversion();
        $this->device->SetAnnouncedASversion($latest);

        return ($announced != $latest);
    }

    /**
     * Returns the User Agent. This data is consolidated with data from Request::GetUserAgent()
     * and the data saved in the ASDevice.
     *
     * @access public
     * @return string
     */
    public function GetUserAgent() {
        return $this->device->GetDeviceUserAgent();
    }

    /**
     * Returns the backend folder id from the AS folderid known to the mobile.
     * If the id is not known, it's returned as is.
     *
     * @param mixed     $folderid
     *
     * @access public
     * @return int/boolean  returns false if the type is not set
     */
    public function GetBackendIdForFolderId($folderid) {
        $backendId = $this->device->GetFolderBackendId($folderid);
        if (!$backendId) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("DeviceManager->GetBackendIdForFolderId(): no backend-folderid available for '%s', returning as is.", $folderid));
            return $folderid;
        }
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("DeviceManager->GetBackendIdForFolderId(): folderid %s => %s", $folderid, $backendId));
        return $backendId;
    }

    /**
     * Gets the AS folderid for a backendFolderId.
     * If there is no known AS folderId a new one is being created.
     *
     * @param string    $backendid              Backend folder id
     * @param boolean   $generateNewIdIfNew     Generates a new AS folderid for the case the backend folder is not known yet, default: false.
     * @param string    $folderOrigin           Folder type is one of   'U' (user)
     *                                                                  'C' (configured)
     *                                                                  'S' (shared)
     *                                                                  'G' (global address book)
     *                                                                  'I' (impersonated)
     * @param string    $folderName             Folder name of the backend folder
     *
     * @access public
     * @return string/boolean  returns false if there is folderid known for this backendid and $generateNewIdIfNew is not set or false.
     */
    public function GetFolderIdForBackendId($backendid, $generateNewIdIfNew = false, $folderOrigin = self::FLD_ORIGIN_USER, $folderName = null) {
        if (!in_array($folderOrigin, array(DeviceManager::FLD_ORIGIN_CONFIG, DeviceManager::FLD_ORIGIN_GAB, DeviceManager::FLD_ORIGIN_SHARED, DeviceManager::FLD_ORIGIN_USER, DeviceManager::FLD_ORIGIN_IMPERSONATED))) {
            ZLog::Write(LOGLEVEL_WARN, sprintf("ASDevice->GetFolderIdForBackendId(): folder type '%' is unknown in DeviceManager", $folderOrigin));
        }
        return $this->device->GetFolderIdForBackendId($backendid, $generateNewIdIfNew, $folderOrigin, $folderName);
    }


    /**----------------------------------------------------------------------------------------------------------
     * private DeviceManager methods
     */

    /**
     * Loads devicedata from the StateMachine and loads it into the device
     *
     * @access public
     * @return boolean
     */
    private function loadDeviceData() {
        if (!Request::IsValidDeviceID())
            return false;
        try {
            $deviceHash = $this->statemachine->GetStateHash($this->devid, IStateMachine::DEVICEDATA);
            if ($deviceHash != $this->deviceHash) {
                if ($this->deviceHash)
                    ZLog::Write(LOGLEVEL_DEBUG, "DeviceManager->loadDeviceData(): Device data was changed, reloading");
                $this->device->SetData($this->statemachine->GetState($this->devid, IStateMachine::DEVICEDATA));
                $this->deviceHash = $deviceHash;
            }
        }
        catch (StateNotFoundException $snfex) {
            $this->hierarchySyncRequired = true;
        }
        catch (UnavailableException $uaex) {
            // This is temporary and can be ignored e.g. in PING - see https://jira.z-hub.io/browse/ZP-1054
            // If the hash was not available before we treat it like a StateNotFoundException.
            if ($this->deviceHash === false) {
                $this->hierarchySyncRequired = true;
            }
        }
        return true;
    }

    /**
     * Called when a SyncObject is not being streamed to the mobile.
     * The user can be informed so he knows about this issue
     *
     * @param string        $folderid   id of the parent folder (may be false if unknown)
     * @param string        $id         message id
     * @param SyncObject    $message    the broken message
     * @param string        $reason     (self::MSG_BROKEN_UNKNOWN, self::MSG_BROKEN_CAUSINGLOOP, self::MSG_BROKEN_SEMANTICERR)
     *
     * @access public
     * @return boolean
     */
    public function AnnounceIgnoredMessage($folderid, $id, SyncObject $message, $reason = self::MSG_BROKEN_UNKNOWN) {
        if ($folderid === false)
            $folderid = $this->getLatestFolder();

        $class = get_class($message);

        $brokenMessage = new StateObject();
        $brokenMessage->id = $id;
        $brokenMessage->folderid = $folderid;
        $brokenMessage->ASClass = $class;
        $brokenMessage->folderid = $folderid;
        $brokenMessage->reasonCode = $reason;
        $brokenMessage->reasonString = 'unknown cause';
        $brokenMessage->timestamp = time();
        $brokenMessage->asobject = $message;
        $brokenMessage->reasonString = ZLog::GetLastMessage(LOGLEVEL_WARN);

        $this->device->AddIgnoredMessage($brokenMessage);

        ZLog::Write(LOGLEVEL_ERROR, sprintf("Ignored broken message (%s). Reason: '%s' Folderid: '%s' message id '%s'", $class, $reason, $folderid, $id));
        return true;
    }

    /**
     * Called when a SyncObject was streamed to the mobile.
     * If the message could not be sent before this data is obsolete
     *
     * @param string        $folderid   id of the parent folder
     * @param string        $id         message id
     *
     * @access public
     * @return boolean          returns true if the message was ignored before
     */
    private function announceAcceptedMessage($folderid, $id) {
        if ($this->device->RemoveIgnoredMessage($folderid, $id)) {
            ZLog::Write(LOGLEVEL_INFO, sprintf("DeviceManager->announceAcceptedMessage('%s', '%s'): cleared previously ignored message as message is sucessfully streamed",$folderid, $id));
            return true;
        }
        return false;
    }

    /**
     * Checks if there were broken messages streamed to the mobile.
     * If the sync completes/continues without further erros they are marked as accepted
     *
     * @param string    $folderid       folderid which is to be checked
     *
     * @access private
     * @return boolean
     */
    private function checkBrokenMessages($folderid) {
        // check for correctly synchronized messages of the folder
        foreach($this->loopdetection->GetSyncedButBeforeIgnoredMessages($folderid) as $okID) {
            $this->announceAcceptedMessage($folderid, $okID);
        }
        return true;
    }

    /**
     * Setter for the latest folder id
     * on multi-folder operations of AS 14 this is used to set the new current folder id
     *
     * @param string    $folderid       the current folder
     *
     * @access private
     * @return boolean
     */
    private function setLatestFolder($folderid) {
        // this is a multi folder operation
        // check on ignoredmessages before discaring the folderid
        if ($this->latestFolder !== false)
            $this->checkBrokenMessages($this->latestFolder);

        $this->latestFolder = $folderid;

        return true;
    }

    /**
     * Getter for the latest folder id
     *
     * @access private
     * @return string    $folderid       the current folder
     */
    private function getLatestFolder() {
        return $this->latestFolder;
    }

    /**
     * Loads Provisioning policies from the policies file.
     *
     * @param string    $policyName     The name of the policy
     *
     * @access private
     * @return array
     */
    private function getProvisioningPolicies($policyName) {
        $policies = ZPush::GetPolicies();

        if (!isset($policies[$policyName]) && $policyName != ASDevice::DEFAULTPOLICYNAME) {
            ZLog::Write(LOGLEVEL_WARN, sprintf("The '%s' policy is configured, but it is not available in the policies' file. Please check %s file. Loading default policy.", $policyName, PROVISIONING_POLICYFILE));
            return $policies[ASDevice::DEFAULTPOLICYNAME];
        }
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("DeviceManager->getProvisioningPolicies(): loaded '%s' policy.", $policyName));

        // Always load default policies, so that if a policy extends a default policy it doesn't have to copy all the values
        if ($policyName != ASDevice::DEFAULTPOLICYNAME) {
            $policies[$policyName] = array_replace_recursive($policies[ASDevice::DEFAULTPOLICYNAME], $policies[$policyName]);
        }
        return $policies[$policyName];
    }

    /**
     * Gets the policy name set in the backend or in device data.
     *
     * @access private
     * @return string
     */
    private function getPolicyName() {
        $policyName = ZPush::GetBackend()->GetUserPolicyName();
        $policyName = ((!empty($policyName) && $policyName !== false) ? $policyName : ASDevice::DEFAULTPOLICYNAME);
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("DeviceManager->getPolicyName(): determined policy name: '%s'", $policyName));
        return $policyName;
    }

    /**
     * Generates and SyncFolder object and returns it.
     *
     * @param string    $store
     * @param string    $folderid
     * @param string    $name
     * @param int       $type
     * @param int       $flags
     * @param string    $folderOrigin
     *
     * @access public
     * @returns SyncFolder
     */
    public function BuildSyncFolderObject($store, $folderid, $parentid, $name, $type, $flags, $folderOrigin) {
        $folder = new SyncFolder();
        $folder->BackendId = $folderid;
        $folder->serverid = $this->GetFolderIdForBackendId($folder->BackendId, true, $folderOrigin, $name);
        $folder->parentid = $this->GetFolderIdForBackendId($parentid);
        $folder->displayname = $name;
        $folder->type = $type;
        // save store as custom property which is not streamed directly to the device
        $folder->NoBackendFolder = true;
        $folder->Store = $store;
        $folder->Flags = $flags;

        // adjust additional folders so it matches not yet processed KOE type UNKNOWN folders
        $synctype = $this->device->GetFolderType($folder->serverid);
        if ($this->IsKoeSupportingSecondaryContacts() && $synctype !== $folder->type && $synctype == SYNC_FOLDER_TYPE_UNKNOWN) {
            ZLog::Write(LOGLEVEL_DEBUG, "DeviceManager->BuildSyncFolderObject(): Modifying additional folder so it matches an unprocessed KOE folder");
            $folder = Utils::ChangeFolderToTypeUnknownForKoe($folder);
        }
        return $folder;
    }

    /**
     * Returns the device id.
     *
     * @access public
     * @return string
     */
    public function GetDevid() {
        return $this->devid;
    }
}
