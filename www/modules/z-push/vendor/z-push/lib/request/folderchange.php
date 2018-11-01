<?php
/***********************************************
* File      :   folderchange.php
* Project   :   Z-Push
* Descr     :   Provides the FOLDERCREATE, FOLDERDELETE, FOLDERUPDATE command
*
* Created   :   16.02.2012
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

class FolderChange extends RequestProcessor {

    /**
     * Handles creates, updates or deletes of a folder
     * issued by the commands FolderCreate, FolderUpdate and FolderDelete
     *
     * @param int       $commandCode
     *
     * @access public
     * @return boolean
     */
    public function Handle ($commandCode) {
        $el = self::$decoder->getElement();

        if($el[EN_TYPE] != EN_TYPE_STARTTAG)
            return false;

        $create = $update = $delete = false;
        if($el[EN_TAG] == SYNC_FOLDERHIERARCHY_FOLDERCREATE)
            $create = true;
        else if($el[EN_TAG] == SYNC_FOLDERHIERARCHY_FOLDERUPDATE)
            $update = true;
        else if($el[EN_TAG] == SYNC_FOLDERHIERARCHY_FOLDERDELETE)
            $delete = true;

        if(!$create && !$update && !$delete)
            return false;

        // SyncKey
        if(!self::$decoder->getElementStartTag(SYNC_FOLDERHIERARCHY_SYNCKEY))
            return false;
        $synckey = self::$decoder->getElementContent();
        if(!self::$decoder->getElementEndTag())
            return false;

        // ServerID
        $serverid = false;
        $backendid = false;
        if(self::$decoder->getElementStartTag(SYNC_FOLDERHIERARCHY_SERVERENTRYID)) {
            $serverid = self::$decoder->getElementContent();
            $backendid = self::$deviceManager->GetBackendIdForFolderId($serverid);
            if(!self::$decoder->getElementEndTag())
                return false;
        }

        // Parent
        $parentid = false;
        $parentBackendId = false;

        // when creating or updating more information is necessary
        if (!$delete) {
            if(self::$decoder->getElementStartTag(SYNC_FOLDERHIERARCHY_PARENTID)) {
                $parentid = self::$decoder->getElementContent();
                $parentBackendId = self::$deviceManager->GetBackendIdForFolderId($parentid);
                if(!self::$decoder->getElementEndTag())
                    return false;
            }

            // Displayname
            if(!self::$decoder->getElementStartTag(SYNC_FOLDERHIERARCHY_DISPLAYNAME))
                return false;
            $displayname = self::$decoder->getElementContent();
            if(!self::$decoder->getElementEndTag())
                return false;

            // Type
            $type = false;
            if(self::$decoder->getElementStartTag(SYNC_FOLDERHIERARCHY_TYPE)) {
                $type = self::$decoder->getElementContent();
                if(!self::$decoder->getElementEndTag())
                    return false;
            }
        }

        // endtag foldercreate, folderupdate, folderdelete
        if(!self::$decoder->getElementEndTag())
            return false;

        $status = SYNC_FSSTATUS_SUCCESS;
        // Get state of hierarchy
        try {
            $syncstate = self::$deviceManager->GetStateManager()->GetSyncState($synckey);
            $newsynckey = self::$deviceManager->GetStateManager()->GetNewSyncKey($synckey);

            // there are no SyncParameters for the hierarchy, but we use it to save the latest synckeys
            $spa = self::$deviceManager->GetStateManager()->GetSynchedFolderState(false);

            // Over the ChangesWrapper the HierarchyCache is notified about all changes
            $changesMem = self::$deviceManager->GetHierarchyChangesWrapper();

            // the hierarchyCache should now fully be initialized - check for changes in the additional folders
            $changesMem->Config(ZPush::GetAdditionalSyncFolders(false));

            // reset to default store in backend
            self::$backend->Setup(false);

            // there are unprocessed changes in the hierarchy, trigger resync
            if ($changesMem->GetChangeCount() > 0)
                throw new StatusException("HandleFolderChange() can not proceed as there are unprocessed hierarchy changes", SYNC_FSSTATUS_SERVERERROR);

            // any additional folders can not be modified - with exception if they are of type SYNC_FOLDER_TYPE_UNKNOWN (ZP-907)
            if (self::$deviceManager->GetFolderTypeFromCacheById($serverid) != SYNC_FOLDER_TYPE_UNKNOWN && $serverid !== false && ZPush::GetAdditionalSyncFolderStore($backendid))
                throw new StatusException("HandleFolderChange() can not change additional folders which are configured", SYNC_FSSTATUS_SYSTEMFOLDER);

            // switch user store if this this happens inside an additional folder
            // if this is an additional folder the backend has to be setup correctly
            // backend should also not be switched when type is SYNC_FOLDER_TYPE_UNKNOWN (ZP-1220)
            if (self::$deviceManager->GetFolderTypeFromCacheById($serverid) != SYNC_FOLDER_TYPE_UNKNOWN && !self::$backend->Setup(ZPush::GetAdditionalSyncFolderStore((($parentBackendId != false) ? $parentBackendId : $backendid))))
                throw new StatusException(sprintf("HandleFolderChange() could not Setup() the backend for folder id '%s'", (($parentBackendId != false) ? $parentBackendId : $backendid)), SYNC_FSSTATUS_SERVERERROR);
        }
        catch (StateNotFoundException $snfex) {
            $status = SYNC_FSSTATUS_SYNCKEYERROR;
        }
        catch (StatusException $stex) {
           $status = $stex->getCode();
        }

        // set $newsynckey in case of an error
        if (!isset($newsynckey))
            $newsynckey = $synckey;

        if ($status == SYNC_FSSTATUS_SUCCESS) {
            try {
                // Configure importer with last state
                $importer = self::$backend->GetImporter();
                $importer->Config($syncstate);

                // the messages from the PIM will be forwarded to the real importer
                $changesMem->SetDestinationImporter($importer);

                // Create SyncFolder object
                $folder = new SyncFolder();
                $folder->serverid = $serverid;
                $folder->parentid = $parentBackendId;
                if (isset($displayname)) {
                    $folder->displayname = $displayname;
                }
                if (isset($type)) {
                    $folder->type = $type;
                }
                // add the backendId to the SyncFolder object
                $folder->BackendId = $backendid;

                // process incoming change
                if (!$delete) {
                    // when creating, $folder->serverid is false, and the returned id is already mapped by the backend
                    $folder = $changesMem->ImportFolderChange($folder);
                }
                else {
                    // delete folder
                    $changesMem->ImportFolderDeletion($folder);
                }
            }
            catch (StatusException $stex) {
                $status = $stex->getCode();
            }
        }

        self::$encoder->startWBXML();
        if ($create) {

            self::$encoder->startTag(SYNC_FOLDERHIERARCHY_FOLDERCREATE);
            {
                {
                    self::$encoder->startTag(SYNC_FOLDERHIERARCHY_STATUS);
                    self::$encoder->content($status);
                    self::$encoder->endTag();

                    self::$encoder->startTag(SYNC_FOLDERHIERARCHY_SYNCKEY);
                    self::$encoder->content($newsynckey);
                    self::$encoder->endTag();

                    self::$encoder->startTag(SYNC_FOLDERHIERARCHY_SERVERENTRYID);
                    self::$encoder->content($folder->serverid);
                    self::$encoder->endTag();
                }
            }
            self::$encoder->endTag();
        }

        elseif ($update) {
            self::$encoder->startTag(SYNC_FOLDERHIERARCHY_FOLDERUPDATE);
            {
                {
                    self::$encoder->startTag(SYNC_FOLDERHIERARCHY_STATUS);
                    self::$encoder->content($status);
                    self::$encoder->endTag();

                    self::$encoder->startTag(SYNC_FOLDERHIERARCHY_SYNCKEY);
                    self::$encoder->content($newsynckey);
                    self::$encoder->endTag();
                }
            }
            self::$encoder->endTag();
        }

        elseif ($delete) {
            self::$encoder->startTag(SYNC_FOLDERHIERARCHY_FOLDERDELETE);
            {
                {
                    self::$encoder->startTag(SYNC_FOLDERHIERARCHY_STATUS);
                    self::$encoder->content($status);
                    self::$encoder->endTag();

                    self::$encoder->startTag(SYNC_FOLDERHIERARCHY_SYNCKEY);
                    self::$encoder->content($newsynckey);
                    self::$encoder->endTag();
                }
            }
            self::$encoder->endTag();
        }

        self::$topCollector->AnnounceInformation(sprintf("Operation status %d", $status), true);

        // Save the sync state for the next time
        if (isset($importer)) {
            self::$deviceManager->GetStateManager()->SetSyncState($newsynckey, $importer->GetState());

            // update SPA & save it
            $spa->SetSyncKey($newsynckey);
            $spa->SetFolderId(false);
            self::$deviceManager->GetStateManager()->SetSynchedFolderState($spa);

            // invalidate all pingable flags
            SyncCollections::InvalidatePingableFlags();
        }

        return true;
    }
}
