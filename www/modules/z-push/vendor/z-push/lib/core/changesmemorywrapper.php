<?php
/***********************************************
* File      :   changesmemorywrapper.php
* Project   :   Z-Push
* Descr     :   Class that collect changes in memory
*
* Created   :   18.08.2011
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

class ChangesMemoryWrapper extends HierarchyCache implements IImportChanges, IExportChanges {
    const CHANGE = 1;
    const DELETION = 2;
    const SOFTDELETION = 3;
    const SYNCHRONIZING = 4;

    private $changes;
    private $step;
    private $destinationImporter;
    private $exportImporter;
    private $impersonating;
    private $foldersWithoutPermissions;

    /**
     * Constructor
     *
     * @access public
     * @return
     */
    public function __construct() {
        $this->changes = array();
        $this->step = 0;
        $this->impersonating = null;
        $this->foldersWithoutPermissions = array();
        parent::__construct();
    }

    /**
     * Only used to load additional folder sync information for hierarchy changes
     *
     * @param array    $state               current state of additional hierarchy folders
     *
     * @access public
     * @return boolean
     */
    public function Config($state, $flags = 0) {
        if ($this->impersonating == null) {
            $this->impersonating = (Request::GetImpersonatedUser()) ? strtolower(Request::GetImpersonatedUser()) : false;
        }

        // we should never forward this changes to a backend
        if (!isset($this->destinationImporter)) {
            foreach($state as $addKey => $addFolder) {
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("ChangesMemoryWrapper->Config(AdditionalFolders) : process folder '%s'", $addFolder->displayname));
                if (isset($addFolder->NoBackendFolder) && $addFolder->NoBackendFolder == true) {
                    // check rights for readonly access only
                    $hasRights = ZPush::GetBackend()->Setup($addFolder->Store, true, $addFolder->BackendId, true);
                    // delete the folder on the device
                    if (! $hasRights) {
                        // delete the folder only if it was an additional folder before, else ignore it
                        $synchedfolder = $this->GetFolder($addFolder->serverid);
                        if (isset($synchedfolder->NoBackendFolder) && $synchedfolder->NoBackendFolder == true)
                            $this->ImportFolderDeletion($addFolder);
                        continue;
                    }
                }
                // make sure, if the folder is already in cache, to set the TypeReal flag (if available)
                $cacheFolder = $this->GetFolder($addFolder->serverid);
                if (isset($cacheFolder->TypeReal)) {
                    $addFolder->TypeReal = $cacheFolder->TypeReal;
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("ChangesMemoryWrapper->Config(): Set REAL foldertype for folder '%s' from cache: '%s'", $addFolder->displayname, $addFolder->TypeReal));
                }

                // add folder to the device - if folder is already on the device, nothing will happen
                $this->ImportFolderChange($addFolder);
            }

            // look for folders which are currently on the device if there are now not to be synched anymore
            $alreadyDeleted = $this->GetDeletedFolders();
            $folderIdsOnClient = array();
            foreach ($this->ExportFolders(true) as $sid => $folder) {
                // check if previously synchronized secondary contact folders were patched for KOE - if no RealType is set they weren't
                if ($flags == self::SYNCHRONIZING && ZPush::GetDeviceManager()->IsKoeSupportingSecondaryContacts() && $folder->type == SYNC_FOLDER_TYPE_USER_CONTACT && !isset($folder->TypeReal)) {
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("ChangesMemoryWrapper->Config(): Identifided secondary contact folder '%s' that was not patched for KOE before. Re-adding it now.", $folder->displayname));
                    // we need to delete it from the hierarchy cache so it's exported as NEW (way to convince OL to add it)
                    $this->DelFolder($folder->serverid);
                    $folder->flags = SYNC_NEWMESSAGE;
                    $this->ImportFolderChange($folder);
                }

                // we are only looking at additional folders
                if (isset($folder->NoBackendFolder)) {
                    // look if this folder is still in the list of additional folders and was not already deleted (e.g. missing permissions)
                    if (!array_key_exists($sid, $state) && !array_key_exists($sid, $alreadyDeleted)) {
                        ZLog::Write(LOGLEVEL_INFO, sprintf("ChangesMemoryWrapper->Config(AdditionalFolders) : previously synchronized folder '%s' is not to be synched anymore. Sending delete to mobile.", $folder->displayname));
                        $this->ImportFolderDeletion($folder);
                    }
                }
                else {
                    $folderIdsOnClient[] = $sid;
                }
            }

            // check permissions on impersonated folders
            if ($this->impersonating) {
                ZLog::Write(LOGLEVEL_DEBUG, "ChangesMemoryWrapper->Config(): check permissions of folders of impersonated account");
                $hierarchy = ZPush::GetBackend()->GetHierarchy();
                foreach ($hierarchy as $folder) {
                    // Check for at least read permissions of the impersonater on folders
                    $hasRights = ZPush::GetBackend()->Setup($this->impersonating, true, $folder->BackendId, true);

                    // the folder has no permissions
                    if (!$hasRights) {
                        $this->foldersWithoutPermissions[$folder->serverid] = $folder;
                        // if it's on the device, remove it
                        if (in_array($folder->serverid, $folderIdsOnClient)) {
                            ZLog::Write(LOGLEVEL_DEBUG, sprintf("ChangesMemoryWrapper->Config(AdditionalFolders) : previously synchronized folder '%s' has no permissions anymore. Sending delete to mobile.", $folder->displayname));
                            // delete folder into memory so it's then sent to the client
                            $this->ImportFolderDeletion($folder);
                        }
                    }
                    // has permissions but is not on the device, add it
                    elseif (!in_array($folder->serverid, $folderIdsOnClient)) {
                        $folder->flags = SYNC_NEWMESSAGE;
                        $this->ImportFolderChange($folder);
                    }
                }
            }
        }
        return true;
    }


    /**
     * Implement interfaces which are never used
     */
    public function GetState() { return false;}
    public function LoadConflicts($contentparameters, $state) { return true; }
    public function ConfigContentParameters($contentparameters) { return true; }
    public function SetMoveStates($srcState, $dstState = null) { return true; }
    public function GetMoveStates() { return array(false, false); }
    public function ImportMessageReadFlag($id, $flags, $categories = array()) { return true; }
    public function ImportMessageMove($id, $newfolder) { return true; }

    /**----------------------------------------------------------------------------------------------------------
     * IImportChanges & destination importer
     */

    /**
     * Sets an importer where incoming changes should be sent to
     *
     * @param IImportChanges    $importer   message to be changed
     *
     * @access public
     * @return boolean
     */
    public function SetDestinationImporter(&$importer) {
        $this->destinationImporter = $importer;
    }

    /**
     * Imports a message change, which is imported into memory
     *
     * @param string        $id         id of message which is changed
     * @param SyncObject    $message    message to be changed
     *
     * @access public
     * @return boolean
     */
    public function ImportMessageChange($id, $message) {
        $this->changes[] = array(self::CHANGE, $id);
        return true;
    }

    /**
     * Imports a message deletion, which is imported into memory
     *
     * @param string        $id             id of message which is deleted
     * @param boolean       $asSoftDelete   (opt) if true, the deletion is exported as "SoftDelete", else as "Remove" - default: false
     *
     * @access public
     * @return boolean
     */
    public function ImportMessageDeletion($id, $asSoftDelete = false) {
        if ($asSoftDelete === true) {
            $this->changes[] = array(self::SOFTDELETION, $id);
        }
        else {
            $this->changes[] = array(self::DELETION, $id);
        }
        return true;
    }

    /**
     * Checks if a message id is flagged as changed
     *
     * @param string        $id     message id
     *
     * @access public
     * @return boolean
     */
    public function IsChanged($id) {
        return (array_search(array(self::CHANGE, $id), $this->changes) === false) ? false:true;
    }

    /**
     * Checks if a message id is flagged as deleted
     *
     * @param string        $id     message id
     *
     * @access public
     * @return boolean
     */
    public function IsDeleted($id) {
       return !((array_search(array(self::DELETION, $id), $this->changes) === false) && (array_search(array(self::SOFTDELETION, $id), $this->changes) === false));
    }

    /**
     * Imports a folder change
     *
     * @param SyncFolder    $folder     folder to be changed
     *
     * @access public
     * @return boolean/SyncObject           status/object with the ath least the serverid of the folder set
     */
    public function ImportFolderChange($folder) {
        // if the destinationImporter is set, then this folder should be processed by another importer
        // instead of being loaded in memory.
        if (isset($this->destinationImporter)) {
            // normally the $folder->type is not set, but we need this value to check if the change operation is permitted
            // e.g. system folders can normally not be changed - set the type from cache and let the destinationImporter decide
            if (!isset($folder->type) || ! $folder->type) {
                $cacheFolder = $this->GetFolder($folder->serverid);
                $folder->type = $cacheFolder->type;
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("ChangesMemoryWrapper->ImportFolderChange(): Set foldertype for folder '%s' from cache as it was not sent: '%s'", $folder->displayname, $folder->type));
                if (isset($cacheFolder->TypeReal)) {
                    $folder->TypeReal = $cacheFolder->TypeReal;
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("ChangesMemoryWrapper->ImportFolderChange(): Set REAL foldertype for folder '%s' from cache: '%s'", $folder->displayname, $folder->TypeReal));
                }
            }

            // KOE ZO-42: When Notes folders are updated in Outlook, it tries to update the name (that fails by default, as it's a system folder)
            // catch this case here and ignore the change
            if (($folder->type == SYNC_FOLDER_TYPE_NOTE || $folder->type == SYNC_FOLDER_TYPE_USER_NOTE) && ZPush::GetDeviceManager()->IsKoe()) {
                $retFolder = false;
            }
            // KOE ZP-907: When a secondary contact folder is patched (update type & change name) don't import it through the backend
            // This is a bit more permissive than ZPush::GetDeviceManager()->IsKoeSupportingSecondaryContacts() so that updates are always catched
            // even if the feature was disabled in the meantime.
            elseif ($folder->type == SYNC_FOLDER_TYPE_UNKNOWN && ZPush::GetDeviceManager()->IsKoe() && !Utils::IsFolderToBeProcessedByKoe($folder)) {
                ZLog::Write(LOGLEVEL_DEBUG, "ChangesMemoryWrapper->ImportFolderChange(): Rewrote folder type to real type, as KOE patched the folder");
                // cacheFolder contains other properties that must be maintained
                // so we continue using the cacheFolder, but rewrite the type and use the incoming displayname
                $cacheFolder = $this->GetFolder($folder->serverid);
                $cacheFolder->type = $cacheFolder->TypeReal;
                $cacheFolder->displayname = $folder->displayname;
                $folder = $cacheFolder;
                $retFolder = $folder;
            }
            // do regular folder update
            else {
                $retFolder = $this->destinationImporter->ImportFolderChange($folder);
            }

            // if the operation was sucessfull, update the HierarchyCache
            if ($retFolder) {
                // if we get a folder back, we need to update some data in the cache
                if (isset($retFolder->serverid) && $retFolder->serverid) {
                    // for folder creation, the serverid & backendid are not set and have to be updated
                    if (!isset($folder->serverid) || $folder->serverid == "") {
                        $folder->serverid = $retFolder->serverid;
                        if (isset($retFolder->BackendId) && $retFolder->BackendId) {
                            $folder->BackendId = $retFolder->BackendId;
                        }
                    }

                    // if the parentid changed (folder was moved) this needs to be updated as well
                    if ($retFolder->parentid != $folder->parentid) {
                        $folder->parentid = $retFolder->parentid;
                    }
                }

                $this->AddFolder($folder);
            }
            return $retFolder;
        }
        // load into memory
        else {
            if (isset($folder->serverid)) {
                // The Zarafa/Kopano HierarchyExporter exports all kinds of changes for folders (e.g. update no. of unread messages in a folder).
                // These changes are not relevant for the mobiles, as something changes but the relevant displayname and parentid
                // stay the same. These changes will be dropped and are not sent!
                if ($folder->equals($this->GetFolder($folder->serverid), false, true)) {
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("ChangesMemoryWrapper->ImportFolderChange(): Change for folder '%s' will not be sent as modification is not relevant.", $folder->displayname));
                    return false;
                }

                // check if the parent ID is known on the device
                if (!isset($folder->parentid) || ($folder->parentid != "0" && !$this->GetFolder($folder->parentid))) {
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("ChangesMemoryWrapper->ImportFolderChange(): Change for folder '%s' will not be sent as parent folder is not set or not known on mobile.", $folder->displayname));
                    return false;
                }

                // ZP-907: if we are ADDING a secondary contact folder and a compatible Outlook is connected, rewrite the type to SYNC_FOLDER_TYPE_UNKNOWN and mark the foldername
                if (ZPush::GetDeviceManager()->IsKoeSupportingSecondaryContacts() && $folder->type == SYNC_FOLDER_TYPE_USER_CONTACT &&
                         (!$this->GetFolder($folder->serverid, true) || !$this->GetFolder($folder->serverid) || $this->GetFolder($folder->serverid)->type === SYNC_FOLDER_TYPE_UNKNOWN)
                        ) {
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("ChangesMemoryWrapper->ImportFolderChange(): Sending new folder '%s' as type SYNC_FOLDER_TYPE_UNKNOWN as Outlook is not able to handle secondary contact folders", $folder->displayname));
                    $folder = Utils::ChangeFolderToTypeUnknownForKoe($folder);
                }

                // folder changes are only sent if the user has permissions on that folder, if not, change is ignored
                if ($this->impersonating && array_key_exists($folder->serverid, $this->foldersWithoutPermissions)) {
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("ChangesMemoryWrapper->ImportFolderChange(): Change for folder '%s' will not be sent as impersonating user has no permissions on folder.", $folder->displayname));
                    return false;
                }

                // load this change into memory
                $this->changes[] = array(self::CHANGE, $folder);

                // HierarchyCache: already add/update the folder so changes are not sent twice (if exported twice)
                $this->AddFolder($folder);
                return true;
            }
            return false;
        }
    }

    /**
     * Imports a folder deletion
     *
     * @param SyncFolder    $folder         at least "serverid" needs to be set
     *
     * @access public
     * @return boolean
     */
    public function ImportFolderDeletion($folder) {
        $id = $folder->serverid;

        // if the forwarder is set, then this folder should be processed by another importer
        // instead of being loaded in mem.
        if (isset($this->destinationImporter)) {
            $ret = $this->destinationImporter->ImportFolderDeletion($folder);

            // if the operation was sucessfull, update the HierarchyCache
            if ($ret)
                $this->DelFolder($id);

            return $ret;
        }
        else {
            // if this folder is not in the cache, the change does not need to be streamed to the mobile
            if ($this->GetFolder($id)) {

                // load this change into memory
                $this->changes[] = array(self::DELETION, $folder);

                // HierarchyCache: delete the folder so changes are not sent twice (if exported twice)
                $this->DelFolder($id);
                return true;
            }
        }
    }


    /**----------------------------------------------------------------------------------------------------------
     * IExportChanges & destination importer
     */

    /**
     * Initializes the Exporter where changes are synchronized to
     *
     * @param IImportChanges    $importer
     *
     * @access public
     * @return boolean
     */
    public function InitializeExporter(&$importer) {
        $this->exportImporter = $importer;
        $this->step = 0;
        return true;
    }

    /**
     * Returns the amount of changes to be exported
     *
     * @access public
     * @return int
     */
    public function GetChangeCount() {
        return count($this->changes);
    }

    /**
     * Synchronizes a change. Only HierarchyChanges will be Synchronized()
     *
     * @access public
     * @return array
     */
    public function Synchronize() {
        if($this->step < count($this->changes) && isset($this->exportImporter)) {

            $change = $this->changes[$this->step];

            if ($change[0] == self::CHANGE) {
                if (! $this->GetFolder($change[1]->serverid, true))
                    $change[1]->flags = SYNC_NEWMESSAGE;

                $this->exportImporter->ImportFolderChange($change[1]);
            }
            // deletion
            else {
                $this->exportImporter->ImportFolderDeletion($change[1]);
            }
            $this->step++;

            // return progress array
            return array("steps" => count($this->changes), "progress" => $this->step);
        }
        else
            return false;
    }

    /**
     * Initializes a few instance variables
     * called after unserialization
     *
     * @access public
     * @return array
     */
    public function __wakeup() {
        $this->changes = array();
        $this->step = 0;
        $this->foldersWithoutPermissions = array();
    }

    /**
     * Removes internal data from the object, so this data can not be exposed.
     *
     * @access public
     * @return boolean
     */
    public function StripData() {
        unset($this->changes);
        unset($this->step);
        unset($this->destinationImporter);
        unset($this->exportImporter);

        return parent::StripData();
    }
}
