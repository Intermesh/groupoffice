<?php
/***********************************************
* File      :   synccollections.php
* Project   :   Z-Push
* Descr     :   This is basically a list of synched folders with it's
*               respective SyncParameters, while some additional parameters
*               which are not stored there can be kept here.
*               The class also provides CheckForChanges which is basically
*               a loop through all collections checking for changes.
*               SyncCollections is used for Sync (with and without heartbeat)
*               and Ping connections.
*               To check for changes in Heartbeat and Ping requeste the same
*               sync states as for the default synchronization are used.
*
* Created   :   06.01.2012
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

class SyncCollections implements Iterator {
    const ERROR_NO_COLLECTIONS = 1;
    const ERROR_WRONG_HIERARCHY = 2;
    const OBSOLETE_CONNECTION = 3;
    const HIERARCHY_CHANGED = 4;

    private $stateManager;

    private $collections = array();
    private $addparms = array();
    private $changes = array();
    private $saveData = true;

    private $refPolicyKey = false;
    private $refLifetime = false;

    private $globalWindowSize;
    private $lastSyncTime;

    private $waitingTime = 0;
    private $hierarchyExporterChecked = false;
    private $loggedGlobalWindowSizeOverwrite = false;


    /**
     * Invalidates all pingable flags for all folders.
     *
     * @access public
     * @return boolean
     */
    static public function InvalidatePingableFlags() {
        ZLog::Write(LOGLEVEL_DEBUG, "SyncCollections::InvalidatePingableFlags(): Invalidating now");
        try {
            $sc = new SyncCollections();
            $sc->LoadAllCollections();
            foreach ($sc as $folderid => $spa) {
                if ($spa->GetPingableFlag() == true) {
                    $spa->DelPingableFlag();
                    $sc->SaveCollection($spa);
                }
            }
            return true;
        }
        catch (ZPushException $e) {}
        return false;
    }

    /**
     * Constructor
     */
    public function __construct() {
    }

    /**
     * Sets the StateManager for this object
     * If this is not done and a method needs it, the StateManager will be
     * requested from the DeviceManager
     *
     * @param StateManager  $statemanager
     *
     * @access public
     * @return
     */
    public function SetStateManager($statemanager) {
        $this->stateManager = $statemanager;
    }

    /**
     * Loads all collections known for the current device
     *
     * @param boolean $overwriteLoaded          (opt) overwrites Collection with saved state if set to true
     * @param boolean $loadState                (opt) indicates if the collection sync state should be loaded, default false
     * @param boolean $checkPermissions         (opt) if set to true each folder will pass
     *                                          through a backend->Setup() to check permissions.
     *                                          If this fails a StatusException will be thrown.
     * @param boolean $loadHierarchy            (opt) if the hierarchy sync states should be loaded, default false
     * @param boolean $confirmedOnly            (opt) indicates if only confirmed states should be loaded, default: false
     *
     * @access public
     * @throws StatusException                  with SyncCollections::ERROR_WRONG_HIERARCHY if permission check fails
     * @throws StateInvalidException            if the sync state can not be found or relation between states is invalid ($loadState = true)
     * @return boolean
     */
    public function LoadAllCollections($overwriteLoaded = false, $loadState = false, $checkPermissions = false, $loadHierarchy = false, $confirmedOnly = false) {
        $this->loadStateManager();

        // this operation should not remove old state counters
        $this->stateManager->DoNotDeleteOldStates();

        $invalidStates = false;
        foreach($this->stateManager->GetSynchedFolders() as $folderid) {
            if ($overwriteLoaded === false && isset($this->collections[$folderid]))
                continue;

            // Load Collection!
            if (! $this->LoadCollection($folderid, $loadState, $checkPermissions, $confirmedOnly))
                $invalidStates = true;
        }

        // load the hierarchy data - there are no permissions to verify so we just set it to false
        if ($loadHierarchy && !$this->LoadCollection(false, $loadState, false, false))
            throw new StatusException("Invalid states found while loading hierarchy data. Forcing hierarchy sync");

        if ($invalidStates)
            throw new StateInvalidException("Invalid states found while loading collections. Forcing sync");

        return true;
    }

    /**
     * Loads all collections known for the current device
     *
     * @param string $folderid                  folder id to be loaded
     * @param boolean $loadState                (opt) indicates if the collection sync state should be loaded, default true
     * @param boolean $checkPermissions         (opt) if set to true each folder will pass
     *                                          through a backend->Setup() to check permissions.
     *                                          If this fails a StatusException will be thrown.
     * @param boolean $confirmedOnly            (opt) indicates if only confirmed states should be loaded, default: false
     *
     * @access public
     * @throws StatusException                  with SyncCollections::ERROR_WRONG_HIERARCHY if permission check fails
     * @throws StateInvalidException            if the sync state can not be found or relation between states is invalid ($loadState = true)
     * @return boolean
     */
    public function LoadCollection($folderid, $loadState = false, $checkPermissions = false, $confirmedOnly = false) {
        $this->loadStateManager();

        try {
            // Get SyncParameters for the folder from the state
            $spa = $this->stateManager->GetSynchedFolderState($folderid, !$loadState);

            // TODO remove resync of folders for < Z-Push 2 beta4 users
            // this forces a resync of all states previous to Z-Push 2 beta4
            if (! $spa instanceof SyncParameters) {
                throw new StateInvalidException("Saved state are not of type SyncParameters");
            }

            if ($spa->GetUuidCounter() == 0) {
                ZLog::Write(LOGLEVEL_DEBUG, "SyncCollections->LoadCollection(): Found collection with move state only, ignoring.");
                return true;
            }
        }
        catch (StateInvalidException $sive) {
            // in case there is something wrong with the state, just stop here
            // later when trying to retrieve the SyncParameters nothing will be found

            if ($folderid === false) {
                throw new StatusException(sprintf("SyncCollections->LoadCollection(): could not get FOLDERDATA state of the hierarchy uuid: %s", $spa->GetUuid()), self::ERROR_WRONG_HIERARCHY);
            }

            // we also generate a fake change, so a sync on this folder is triggered
            $this->changes[$folderid] = 1;

            return false;
        }

        // if this is an additional folder the backend has to be setup correctly
        if ($checkPermissions === true && ! ZPush::GetBackend()->Setup(ZPush::GetAdditionalSyncFolderStore($spa->GetBackendFolderId())))
            throw new StatusException(sprintf("SyncCollections->LoadCollection(): could not Setup() the backend for folder id %s/%s", $spa->GetFolderId(), $spa->GetBackendFolderId()), self::ERROR_WRONG_HIERARCHY);

        // add collection to object
        $addStatus = $this->AddCollection($spa);

        // load the latest known syncstate if requested
        if ($addStatus && $loadState === true) {
            try {
                // make sure the hierarchy cache is loaded when we are loading hierarchy states
                $this->addparms[$folderid]["state"] = $this->stateManager->GetSyncState($spa->GetLatestSyncKey($confirmedOnly), ($folderid === false));
            }
            catch (StateNotFoundException $snfe) {
                // if we can't find the state, first we should try a sync of that folder, so
                // we generate a fake change, so a sync on this folder is triggered
                $this->changes[$folderid] = 1;

                // make sure this folder is fully synched on next Sync request
                $this->invalidateFolderStat($spa);

                return false;
            }
        }

        return $addStatus;
    }

    /**
     * Saves a SyncParameters Object
     *
     * @param SyncParamerts $spa
     *
     * @access public
     * @return boolean
     */
    public function SaveCollection($spa) {
        if (! $this->saveData || !$spa->HasFolderId())
            return false;

        if ($spa->IsDataChanged()) {
            $this->loadStateManager();
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("SyncCollections->SaveCollection(): Data of folder '%s' changed", $spa->GetFolderId()));

            // save new windowsize
            if (isset($this->globalWindowSize))
                $spa->SetWindowSize($this->globalWindowSize);

            // update latest lifetime
            if (isset($this->refLifetime))
                $spa->SetReferenceLifetime($this->refLifetime);

            return $this->stateManager->SetSynchedFolderState($spa);
        }
        return false;
    }

    /**
     * Adds a SyncParameters object to the current list of collections
     *
     * @param SyncParameters $spa
     *
     * @access public
     * @return boolean
     */
    public function AddCollection($spa) {
        if (! $spa->HasFolderId())
            return false;

        if ($spa->GetKoeGabFolder() === true) {
            // put KOE GAB at the beginning of the sync
            $this->collections = [$spa->GetFolderId() => $spa] + $this->collections;
            ZLog::Write(LOGLEVEL_DEBUG, "SyncCollections->AddCollection(): Prioritizing KOE GAB folder for synchronization");
        }
        else {
            $this->collections[$spa->GetFolderId()] = $spa;
        }

        ZLog::Write(LOGLEVEL_DEBUG, sprintf("SyncCollections->AddCollection(): Folder id '%s' : ref. PolicyKey '%s', ref. Lifetime '%s', last sync at '%s'", $spa->GetFolderId(), $spa->GetReferencePolicyKey(), $spa->GetReferenceLifetime(), $spa->GetLastSyncTime()));
        if ($spa->HasLastSyncTime() && $spa->GetLastSyncTime() > $this->lastSyncTime) {
            $this->lastSyncTime = $spa->GetLastSyncTime();

            // use SyncParameters PolicyKey as reference if available
            if ($spa->HasReferencePolicyKey())
                $this->refPolicyKey = $spa->GetReferencePolicyKey();

            // use SyncParameters LifeTime as reference if available
            if ($spa->HasReferenceLifetime())
                $this->refLifetime = $spa->GetReferenceLifetime();

            ZLog::Write(LOGLEVEL_DEBUG, sprintf("SyncCollections->AddCollection(): Updated reference PolicyKey '%s', reference Lifetime '%s', Last sync at '%s'", $this->refPolicyKey, $this->refLifetime, $this->lastSyncTime));
        }

        return true;
    }

    /**
     * Returns a previousily added or loaded SyncParameters object for a folderid
     *
     * @param SyncParameters $spa
     *
     * @access public
     * @return SyncParameters / boolean      false if no SyncParameters object is found for folderid
     */
    public function GetCollection($folderid) {
        if (isset($this->collections[$folderid]))
            return $this->collections[$folderid];
        else
            return false;
    }

    /**
     * Indicates if there are any loaded CPOs
     *
     * @access public
     * @return boolean
     */
    public function HasCollections() {
        return ! empty($this->collections);
    }

    /**
     * Indicates the amount of collections loaded.
     *
     * @access public
     * @return int
     */
    public function GetCollectionCount() {
        return count($this->collections);
    }

    /**
     * Add a non-permanent key/value pair for a SyncParameters object
     *
     * @param SyncParameters    $spa    target SyncParameters
     * @param string            $key
     * @param mixed             $value
     *
     * @access public
     * @return boolean
     */
    public function AddParameter($spa, $key, $value) {
        if (!$spa->HasFolderId())
            return false;

        $folderid = $spa->GetFolderId();
        if (!isset($this->addparms[$folderid]))
            $this->addparms[$folderid] = array();

        $this->addparms[$folderid][$key] = $value;
        return true;
    }

    /**
     * Returns a previousily set non-permanent value for a SyncParameters object
     *
     * @param SyncParameters    $spa    target SyncParameters
     * @param string            $key
     *
     * @access public
     * @return mixed            returns 'null' if nothing set
     */
    public function GetParameter($spa, $key) {
        if (!$spa->HasFolderId())
            return null;

        if (isset($this->addparms[$spa->GetFolderId()]) && isset($this->addparms[$spa->GetFolderId()][$key]))
            return $this->addparms[$spa->GetFolderId()][$key];
        else
            return null;
    }

    /**
     * Returns the latest known PolicyKey to be used as reference
     *
     * @access public
     * @return int/boolen       returns false if nothing found in collections
     */
    public function GetReferencePolicyKey() {
        return $this->refPolicyKey;
    }

    /**
     * Sets a global window size which should be used for all collections
     * in a case of a heartbeat and/or partial sync
     *
     * @param int   $windowsize
     *
     * @access public
     * @return boolean
     */
    public function SetGlobalWindowSize($windowsize) {
        $this->globalWindowSize = $windowsize;
        return true;
    }

    /**
     * Returns the global window size of items to be exported in total over all
     * requested collections.
     *
     * @access public
     * @return int/boolean          returns requested windows size, 512 (max) or the
     *                              value of config SYNC_MAX_ITEMS if it is lower
     */
    public function GetGlobalWindowSize() {
        // take the requested global windowsize or the max 512 if not defined
        if (isset($this->globalWindowSize)) {
            $globalWindowSize = $this->globalWindowSize;
        }
        else {
            $globalWindowSize = WINDOW_SIZE_MAX; // 512 by default
        }

        if (defined("SYNC_MAX_ITEMS") && SYNC_MAX_ITEMS < $globalWindowSize) {
            if (!$this->loggedGlobalWindowSizeOverwrite) {
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("SyncCollections->GetGlobalWindowSize() overwriting requested global window size of %d by %d forced in configuration.", $globalWindowSize, SYNC_MAX_ITEMS));
                $this->loggedGlobalWindowSizeOverwrite = true;
            }
            $globalWindowSize = SYNC_MAX_ITEMS;
        }

        return $globalWindowSize;
    }

    /**
     * Sets the lifetime for heartbeat or ping connections
     *
     * @param int   $lifetime       time in seconds
     *
     * @access public
     * @return boolean
     */
    public function SetLifetime($lifetime) {
        $this->refLifetime = $lifetime;
        return true;
    }

    /**
     * Sets the lifetime for heartbeat or ping connections
     * previousily set or saved in a collection
     *
     * @access public
     * @return int                  returns PING_HIGHER_BOUND_LIFETIME as default if nothing set or not available.
     *                              If PING_HIGHER_BOUND_LIFETIME is not set, returns 600.
     */
    public function GetLifetime() {
        if (!isset($this->refLifetime) || $this->refLifetime === false) {
            if (PING_HIGHER_BOUND_LIFETIME !== false) {
                return PING_HIGHER_BOUND_LIFETIME;
            }
            return 600;
        }

        return $this->refLifetime;
    }

    /**
     * Returns the timestamp of the last synchronization for all
     * loaded collections
     *
     * @access public
     * @return int                  timestamp
     */
    public function GetLastSyncTime() {
        return $this->lastSyncTime;
    }

    /**
     * Checks if the currently known collections for changes for $lifetime seconds.
     * If the backend provides a ChangesSink the sink will be used.
     * If not every $interval seconds an exporter will be configured for each
     * folder to perform GetChangeCount().
     *
     * @param int       $lifetime       (opt) total lifetime to wait for changes / default 600s
     * @param int       $interval       (opt) time between blocking operations of sink or polling / default 30s
     * @param boolean   $onlyPingable   (opt) only check for folders which have the PingableFlag
     *
     * @access public
     * @return boolean              indicating if changes were found
     * @throws StatusException      with code SyncCollections::ERROR_NO_COLLECTIONS if no collections available
     *                              with code SyncCollections::ERROR_WRONG_HIERARCHY if there were errors getting changes
     */
    public function CheckForChanges($lifetime = 600, $interval = 30, $onlyPingable = false) {
        $classes = array();
        foreach ($this->collections as $folderid => $spa){
            if ($onlyPingable && $spa->GetPingableFlag() !== true || ! $folderid)
                continue;

            // the class name will be overwritten for KOE-GAB
            $class = $this->getPingClass($spa);

            if (!isset($classes[$class]))
                $classes[$class] = 0;
            $classes[$class] += 1;
        }
        if (empty($classes))
            $checkClasses = "policies only";
        else if (array_sum($classes) > 4) {
            $checkClasses = "";
            foreach($classes as $class=>$count) {
                if ($count == 1)
                    $checkClasses .= sprintf("%s ", $class);
                else
                    $checkClasses .= sprintf("%s(%d) ", $class, $count);
            }
        }
        else
            $checkClasses = implode(" ", array_keys($classes));

        $pingTracking = new PingTracking();
        $this->changes = array();

        ZPush::GetDeviceManager()->AnnounceProcessAsPush();
        ZPush::GetTopCollector()->AnnounceInformation(sprintf("lifetime %ds", $lifetime), true);
        ZLog::Write(LOGLEVEL_INFO, sprintf("SyncCollections->CheckForChanges(): Waiting for %s changes... (lifetime %d seconds)", (empty($classes))?'policy':'store', $lifetime));

        // use changes sink where available
        $changesSink = ZPush::GetBackend()->HasChangesSink();

        // create changessink and check folder stats if there are folders to Ping
        if (!empty($classes)) {
            // initialize all possible folders
            foreach ($this->collections as $folderid => $spa) {
                if (($onlyPingable && $spa->GetPingableFlag() !== true) || ! $folderid)
                    continue;

                $backendFolderId = $spa->GetBackendFolderId();

                // get the user store if this is a additional folder
                $store = ZPush::GetAdditionalSyncFolderStore($backendFolderId);

                // initialize sink if no immediate changes were found so far
                if ($changesSink && empty($this->changes)) {
                    ZPush::GetBackend()->Setup($store);
                    if (! ZPush::GetBackend()->ChangesSinkInitialize($backendFolderId))
                        throw new StatusException(sprintf("Error initializing ChangesSink for folder id %s/%s", $folderid, $backendFolderId), self::ERROR_WRONG_HIERARCHY);
                }

                // check if the folder stat changed since the last sync, if so generate a change for it (only on first run)
                $currentFolderStat = ZPush::GetBackend()->GetFolderStat($store, $backendFolderId);
                if ($this->waitingTime == 0 && ZPush::GetBackend()->HasFolderStats() && $currentFolderStat !== false && $spa->IsExporterRunRequired($currentFolderStat, true)) {
                    $this->changes[$spa->GetFolderId()] = 1;
                }
            }
        }

        if (!empty($this->changes)) {
            ZLog::Write(LOGLEVEL_DEBUG, "SyncCollections->CheckForChanges(): Using ChangesSink but found changes verifying the folder stats");
            return true;
        }

        // wait for changes
        $started = time();
        $endat = time() + $lifetime;

        // always use policy key from the request if it was sent
        $policyKey = $this->GetReferencePolicyKey();
        if (Request::WasPolicyKeySent() && Request::GetPolicyKey() != 0) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("refpolkey:'%s', sent polkey:'%s'", $policyKey, Request::GetPolicyKey()));
            $policyKey = Request::GetPolicyKey();
        }
        while(($now = time()) < $endat) {
            // how long are we waiting for changes
            $this->waitingTime = $now-$started;

            $nextInterval = $interval;
            // we should not block longer than the lifetime
            if ($endat - $now < $nextInterval)
                $nextInterval = $endat - $now;

            // Check if provisioning is necessary
            // if a PolicyKey was sent use it. If not, compare with the ReferencePolicyKey
            if (PROVISIONING === true && $policyKey !== false && ZPush::GetDeviceManager()->ProvisioningRequired($policyKey, true, false))
                // the hierarchysync forces provisioning
                throw new StatusException("SyncCollections->CheckForChanges(): Policies or PolicyKey changed. Provisioning required.", self::ERROR_WRONG_HIERARCHY);

            // Check if a hierarchy sync is necessary
            if ($this->countHierarchyChange())
                throw new StatusException("SyncCollections->CheckForChanges(): HierarchySync required.", self::HIERARCHY_CHANGED);

            // Check if there are newer requests
            // If so, this process should be terminated if more than 60 secs to go
            if ($pingTracking->DoForcePingTimeout()) {
                // do not update CPOs because another process has already read them!
                $this->saveData = false;

                // more than 60 secs to go?
                if (($now + 60) < $endat) {
                    ZPush::GetTopCollector()->AnnounceInformation(sprintf("Forced timeout after %ds", ($now-$started)), true);
                    throw new StatusException(sprintf("SyncCollections->CheckForChanges(): Timeout forced after %ss from %ss due to other process", ($now-$started), $lifetime), self::OBSOLETE_CONNECTION);
                }
            }

            // Use changes sink if available
            if ($changesSink) {
                ZPush::GetTopCollector()->AnnounceInformation(sprintf("Sink %d/%ds on %s", ($now-$started), $lifetime, $checkClasses));
                $notifications = ZPush::GetBackend()->ChangesSink($nextInterval);

                // how long are we waiting for changes
                $this->waitingTime = time()-$started;

                $validNotifications = false;
                foreach ($notifications as $backendFolderId) {
                    // Check hierarchy notifications
                    if ($backendFolderId === IBackend::HIERARCHYNOTIFICATION) {
                        // wait two seconds before validating this notification, because it could potentially be made by the mobile and we need some time to update the states.
                        sleep(2);
                        // check received hierarchy notifications by exporting
                        if ($this->countHierarchyChange(true)) {
                            throw new StatusException("SyncCollections->CheckForChanges(): HierarchySync required.", self::HIERARCHY_CHANGED);
                        }
                    }
                    else {
                        // the backend will notify on the backend folderid
                        $folderid = ZPush::GetDeviceManager()->GetFolderIdForBackendId($backendFolderId);

                        // check if the notification on the folder is within our filter
                        if ($this->CountChange($folderid)) {
                            ZLog::Write(LOGLEVEL_DEBUG, sprintf("SyncCollections->CheckForChanges(): Notification received on folder '%s'", $folderid));
                            $validNotifications = true;
                            $this->waitingTime = time()-$started;
                        }
                        else {
                            ZLog::Write(LOGLEVEL_DEBUG, sprintf("SyncCollections->CheckForChanges(): Notification received on folder '%s', but it is not relevant", $folderid));
                        }
                    }
                }
                if ($validNotifications)
                    return true;
            }
            // use polling mechanism
            else {
                ZPush::GetTopCollector()->AnnounceInformation(sprintf("Polling %d/%ds on %s", ($now-$started), $lifetime, $checkClasses));
                if ($this->CountChanges($onlyPingable)) {
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("SyncCollections->CheckForChanges(): Found changes polling"));
                    return true;
                }
                else {
                    sleep($nextInterval);
                }
            } // end polling
        } // end wait for changes
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("SyncCollections->CheckForChanges(): no changes found after %ds", time() - $started));

        return false;
    }

    /**
     * Checks if the currently known collections for
     * changes performing Exporter->GetChangeCount()
     *
     * @param boolean   $onlyPingable   (opt) only check for folders which have the PingableFlag
     *
     * @access public
     * @return boolean      indicating if changes were found or not
     */
    public function CountChanges($onlyPingable = false) {
        $changesAvailable = false;
        foreach ($this->collections as $folderid => $spa) {
            if ($onlyPingable && $spa->GetPingableFlag() !== true)
                continue;

            if (isset($this->addparms[$spa->GetFolderId()]["status"]) && $this->addparms[$spa->GetFolderId()]["status"] != SYNC_STATUS_SUCCESS)
                continue;

            if ($this->CountChange($folderid))
                $changesAvailable = true;
        }

        return $changesAvailable;
    }

    /**
     * Checks a folder for changes performing Exporter->GetChangeCount()
     *
     * @param string    $folderid   counts changes for a folder
     *
     * @access private
     * @return boolean      indicating if changes were found or not
     */
     private function CountChange($folderid) {
        $spa = $this->GetCollection($folderid);

        if (!$spa) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("SyncCollections->CountChange(): Could not get SyncParameters object from cache for folderid '%s' to verify notification. Ignoring.", $folderid));
            return false;
        }

        // Prevent ZP-623 by checking if the states have been used before, if so force a sync on this folder.
        // ZCP/KC 7.2.3 and newer support SYNC_STATE_READONLY so this behaviour is not required (see ZP-968).
        if (!Utils::CheckMapiExtVersion('7.2.3')) {
            if (ZPush::GetDeviceManager()->CheckHearbeatStateIntegrity($spa->GetFolderId(), $spa->GetUuid(), $spa->GetUuidCounter())) {
                ZLog::Write(LOGLEVEL_DEBUG, "SyncCollections->CountChange(): Cannot verify changes for state as it was already used. Forcing sync of folder.");
                $this->changes[$folderid] = 1;
                return true;
            }
        }

        $backendFolderId = ZPush::GetDeviceManager()->GetBackendIdForFolderId($folderid);
        // switch user store if this is a additional folder (additional true -> do not debug)
        ZPush::GetBackend()->Setup(ZPush::GetAdditionalSyncFolderStore($backendFolderId, true));
        $changecount = false;

        try {
            $exporter = ZPush::GetBackend()->GetExporter($backendFolderId);
            if ($exporter !== false && isset($this->addparms[$folderid]["state"])) {
                $importer = false;

                $exporter->SetMoveStates($spa->GetMoveState());
                $exporter->Config($this->addparms[$folderid]["state"], BACKEND_DISCARD_DATA);
                $exporter->ConfigContentParameters($spa->GetCPO());
                $ret = $exporter->InitializeExporter($importer);

                if ($ret !== false)
                    $changecount = $exporter->GetChangeCount();
            }
        }
        catch (StatusException $ste) {
            if ($ste->getCode() == SYNC_STATUS_FOLDERHIERARCHYCHANGED) {
                ZLog::Write(LOGLEVEL_WARN, "SyncCollections->CountChange(): exporter can not be re-configured due to state error, emulating change in folder to force Sync.");
                $this->changes[$folderid] = 1;
                // make sure this folder is fully synched on next Sync request
                $this->invalidateFolderStat($spa);

                return true;
            }
            throw new StatusException("SyncCollections->CountChange(): exporter can not be re-configured.", self::ERROR_WRONG_HIERARCHY, null, LOGLEVEL_WARN);
        }

        // start over if exporter can not be configured atm
        if ($changecount === false)
            ZLog::Write(LOGLEVEL_WARN, "SyncCollections->CountChange(): no changes received from Exporter.");

        $this->changes[$folderid] = $changecount;

        return ($changecount > 0);
     }

     /**
      * Checks the hierarchy for changes.
      *
      * @param boolean       export changes, default: false
      *
      * @access private
      * @return boolean      indicating if changes were found or not
      */
     private function countHierarchyChange($exportChanges = false) {
         $folderid = false;

         // Check with device manager if the hierarchy should be reloaded.
         // New additional folders are loaded here.
         if (ZPush::GetDeviceManager()->IsHierarchySyncRequired()) {
             ZLog::Write(LOGLEVEL_DEBUG, "SyncCollections->countHierarchyChange(): DeviceManager says HierarchySync is required.");
             return true;
         }

         $changecount = false;
         if ($exportChanges || $this->hierarchyExporterChecked === false) {
             try {
                 // if this is a validation (not first run), make sure to load the hierarchy data again
                 if ($this->hierarchyExporterChecked === true && !$this->LoadCollection(false, true, false))
                     throw new StatusException("Invalid states found while re-loading hierarchy data.");


                 $changesMem = ZPush::GetDeviceManager()->GetHierarchyChangesWrapper();
                 // the hierarchyCache should now fully be initialized - check for changes in the additional folders
                 $changesMem->Config(ZPush::GetAdditionalSyncFolders(false));

                 // reset backend to the main store
                 ZPush::GetBackend()->Setup(false);
                 $exporter = ZPush::GetBackend()->GetExporter();
                 if ($exporter !== false && isset($this->addparms[$folderid]["state"])) {
                     $exporter->Config($this->addparms[$folderid]["state"]);
                     $ret = $exporter->InitializeExporter($changesMem);
                     while(is_array($exporter->Synchronize()));

                     if ($ret !== false)
                         $changecount = $changesMem->GetChangeCount();

                     $this->hierarchyExporterChecked = true;
                 }
             }
             catch (StatusException $ste) {
                 throw new StatusException("SyncCollections->countHierarchyChange(): exporter can not be re-configured.", self::ERROR_WRONG_HIERARCHY, null, LOGLEVEL_WARN);
             }

             // start over if exporter can not be configured atm
             if ($changecount === false )
                 ZLog::Write(LOGLEVEL_WARN, "SyncCollections->countHierarchyChange(): no changes received from Exporter.");
         }
         return ($changecount > 0);
     }

    /**
     * Returns an array with all folderid and the amount of changes found
     *
     * @access public
     * @return array
     */
    public function GetChangedFolderIds() {
        return $this->changes;
    }

    /**
     * Indicates if there are folders which are pingable
     *
     * @access public
     * @return boolean
     */
    public function PingableFolders() {
        foreach ($this->collections as $folderid => $spa) {
            if ($spa->GetPingableFlag() == true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Indicates if the process did wait in a sink, polling or before running a
     * regular export to find changes
     *
     * @access public
     * @return boolean
     */
    public function WaitedForChanges() {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("SyncCollections->WaitedForChanges: waited for %d seconds", $this->waitingTime));
        return ($this->waitingTime > 0);
    }

    /**
     * Indicates how many seconds the process did wait in a sink, polling or before running a
     * regular export to find changes.
     *
     * @access public
     * @return int
     */
    public function GetWaitedSeconds() {
        return $this->waitingTime;
    }

    /**
     * Returns how the current folder should be called in the PING comment.
     *
     * @param SyncParameters $spa
     *
     * @access public
     * @return string
     */
    private function getPingClass($spa) {
        $class = $spa->GetContentClass();
        if ($class == "Calendar" && strpos($spa->GetFolderId(), DeviceManager::FLD_ORIGIN_GAB) === 0) {
            $class = "GAB";
        }
        return $class;
    }

    /**
     * Simple Iterator Interface implementation to traverse through collections
     */

    /**
     * Rewind the Iterator to the first element
     *
     * @access public
     * @return
     */
    public function rewind() {
        return reset($this->collections);
    }

    /**
     * Returns the current element
     *
     * @access public
     * @return mixed
     */
    public function current() {
        return current($this->collections);
    }

    /**
     * Return the key of the current element
     *
     * @access public
     * @return scalar on success, or NULL on failure.
     */
    public function key() {
        return key($this->collections);
    }

    /**
     * Move forward to next element
     *
     * @access public
     * @return
     */
    public function next() {
        return next($this->collections);
    }

    /**
     * Checks if current position is valid
     *
     * @access public
     * @return boolean
     */
    public function valid() {
        return (key($this->collections) != null && key($this->collections) != false);
    }

    /**
     * Gets the StateManager from the DeviceManager
     * if it's not available
     *
     * @access private
     * @return
     */
     private function loadStateManager() {
         if (!isset($this->stateManager))
            $this->stateManager = ZPush::GetDeviceManager()->GetStateManager();
     }

     /**
      * Remove folder statistics from a SyncParameter object.
      *
      * @param SyncParameters $spa
      *
      * @access public
      * @return
      */
     private function invalidateFolderStat($spa) {
         if($spa->HasFolderStat()) {
             ZLog::Write(LOGLEVEL_DEBUG, sprintf("SyncCollections->invalidateFolderStat(): removing folder stat '%s' for folderid '%s'", $spa->GetFolderStat(), $spa->GetFolderId()));
             $spa->DelFolderStat();
             $this->SaveCollection($spa);
             return true;
         }
         return false;
     }
}
