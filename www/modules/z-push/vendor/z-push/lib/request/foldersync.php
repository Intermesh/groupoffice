<?php
/***********************************************
* File      :   foldersync.php
* Project   :   Z-Push
* Descr     :   Provides the FOLDERSYNC command
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

class FolderSync extends RequestProcessor {

    /**
     * Handles the FolderSync command
     *
     * @param int       $commandCode
     *
     * @access public
     * @return boolean
     */
    public function Handle ($commandCode) {
        // Parse input
        if(!self::$decoder->getElementStartTag(SYNC_FOLDERHIERARCHY_FOLDERSYNC))
            return false;

        if(!self::$decoder->getElementStartTag(SYNC_FOLDERHIERARCHY_SYNCKEY))
            return false;

        $synckey = self::$decoder->getElementContent();

        if(!self::$decoder->getElementEndTag())
            return false;

        // every FolderSync with SyncKey 0 should return the supported AS version & command headers
        if($synckey == "0") {
            self::$specialHeaders = array();
            self::$specialHeaders[] = ZPush::GetSupportedProtocolVersions();
            self::$specialHeaders[] = ZPush::GetSupportedCommands();
        }

        $status = SYNC_FSSTATUS_SUCCESS;
        $newsynckey = $synckey;
        try {
            $syncstate = self::$deviceManager->GetStateManager()->GetSyncState($synckey);

            // We will be saving the sync state under 'newsynckey'
            $newsynckey = self::$deviceManager->GetStateManager()->GetNewSyncKey($synckey);

            // there are no SyncParameters for the hierarchy, but we use it to save the latest synckeys
            $spa = self::$deviceManager->GetStateManager()->GetSynchedFolderState(false);
        }
        catch (StateNotFoundException $snfex) {
                $status = SYNC_FSSTATUS_SYNCKEYERROR;
        }
        catch (StateInvalidException $sive) {
                $status = SYNC_FSSTATUS_SYNCKEYERROR;
        }

        // The ChangesWrapper caches all imports in-memory, so we can send a change count
        // before sending the actual data.
        // the HierarchyCache is notified and the changes from the PIM are transmitted to the actual backend
        $changesMem = self::$deviceManager->GetHierarchyChangesWrapper();

        // the hierarchyCache should now fully be initialized - check for changes in the additional folders
        $changesMem->Config(ZPush::GetAdditionalSyncFolders(false), ChangesMemoryWrapper::SYNCHRONIZING);

         // reset to default store in backend
        self::$backend->Setup(false);

        // process incoming changes
        if(self::$decoder->getElementStartTag(SYNC_FOLDERHIERARCHY_CHANGES)) {
            // Ignore <Count> if present
            if(self::$decoder->getElementStartTag(SYNC_FOLDERHIERARCHY_COUNT)) {
                self::$decoder->getElementContent();
                if(!self::$decoder->getElementEndTag())
                    return false;
            }

            // Process the changes (either <Add>, <Modify>, or <Remove>)
            $element = self::$decoder->getElement();

            if($element[EN_TYPE] != EN_TYPE_STARTTAG)
                return false;

            $importer = false;
            WBXMLDecoder::ResetInWhile("folderSyncIncomingChange");
            while(WBXMLDecoder::InWhile("folderSyncIncomingChange")) {
                $folder = new SyncFolder();
                if(!$folder->Decode(self::$decoder))
                    break;

                // add the backendId to the SyncFolder object
                $folder->BackendId = self::$deviceManager->GetBackendIdForFolderId($folder->serverid);

                try {
                    if ($status == SYNC_FSSTATUS_SUCCESS && !$importer) {
                        // Configure the backends importer with last state
                        $importer = self::$backend->GetImporter();
                        $importer->Config($syncstate);
                        // the messages from the PIM will be forwarded to the backend
                        $changesMem->forwardImporter($importer);
                    }

                    if ($status == SYNC_FSSTATUS_SUCCESS) {
                        switch($element[EN_TAG]) {
                            case SYNC_ADD:
                            case SYNC_MODIFY:
                                $serverid = $changesMem->ImportFolderChange($folder);
                                break;
                            case SYNC_REMOVE:
                                $serverid = $changesMem->ImportFolderDeletion($folder);
                                break;
                        }
                    }
                    else {
                        ZLog::Write(LOGLEVEL_WARN, sprintf("Request->HandleFolderSync(): ignoring incoming folderchange for folder '%s' as status indicates problem.", $folder->displayname));
                        self::$topCollector->AnnounceInformation("Incoming change ignored", true);
                    }
                }
                catch (StatusException $stex) {
                   $status = $stex->getCode();
                }
            }

            if(!self::$decoder->getElementEndTag())
                return false;
        }
        // no incoming changes
        else {
            // check for a potential process loop like described in Issue ZP-5
            if ($synckey != "0" && self::$deviceManager->IsHierarchyFullResyncRequired())
                $status = SYNC_FSSTATUS_SYNCKEYERROR;
                self::$deviceManager->AnnounceProcessStatus(false, $status);
        }

        if(!self::$decoder->getElementEndTag())
            return false;

        // We have processed incoming foldersync requests, now send the PIM
        // our changes

        // Output our WBXML reply now
        self::$encoder->StartWBXML();

        self::$encoder->startTag(SYNC_FOLDERHIERARCHY_FOLDERSYNC);
        {
            if ($status == SYNC_FSSTATUS_SUCCESS) {
                try {
                    // do nothing if this is an invalid device id (like the 'validate' Androids internal client sends)
                    if (!Request::IsValidDeviceID())
                        throw new StatusException(sprintf("Request::IsValidDeviceID() indicated that '%s' is not a valid device id", Request::GetDeviceID()), SYNC_FSSTATUS_SERVERERROR);

                    // Changes from backend are sent to the MemImporter and processed for the HierarchyCache.
                    // The state which is saved is from the backend, as the MemImporter is only a proxy.
                    $exporter = self::$backend->GetExporter();

                    $exporter->Config($syncstate);
                    $exporter->InitializeExporter($changesMem);

                    // Stream all changes to the ImportExportChangesMem
                    $totalChanges = $exporter->GetChangeCount();
                    $exported = 0;
                    $partial = false;
                    while(is_array($exporter->Synchronize())) {
                        $exported++;

                        if (time() % 4 ) {
                            self::$topCollector->AnnounceInformation(sprintf("Exported %d from %d folders", $exported, $totalChanges));
                        }

                        // if partial sync is allowed, stop if this takes too long
                        if (USE_PARTIAL_FOLDERSYNC && Request::IsRequestTimeoutReached()) {
                            ZLog::Write(LOGLEVEL_WARN, sprintf("Request->HandleFolderSync(): Exporting folders is too slow. In %d seconds only %d from %d changes were processed.",(time() - $_SERVER["REQUEST_TIME"]), $exported, $totalChanges));
                            self::$topCollector->AnnounceInformation(sprintf("Partial export of %d out of %d folders", $exported, $totalChanges), true);
                            self::$deviceManager->SetFolderSyncComplete(false);
                            $partial = true;
                            break;
                        }
                    }

                    // update the foldersync complete flag
                    if (USE_PARTIAL_FOLDERSYNC && $partial == false && self::$deviceManager->GetFolderSyncComplete() === false) {
                        // say that we are done with partial synching
                        self::$deviceManager->SetFolderSyncComplete(true);
                        // reset the loop data to prevent any loop detection to kick in now
                        self::$deviceManager->ClearLoopDetectionData(Request::GetAuthUserString(), Request::GetDeviceID());
                        ZLog::Write(LOGLEVEL_INFO, "Request->HandleFolderSync(): Chunked exporting of folders completed successfully");
                    }

                    // get the new state from the backend
                    $newsyncstate = (isset($exporter))?$exporter->GetState():"";
                }
                catch (StatusException $stex) {
                    if ($stex->getCode() == SYNC_FSSTATUS_CODEUNKNOWN)
                        $status = SYNC_FSSTATUS_SYNCKEYERROR;
                    else
                        $status = $stex->getCode();
                }
            }

            self::$encoder->startTag(SYNC_FOLDERHIERARCHY_STATUS);
            self::$encoder->content($status);
            self::$encoder->endTag();

            if ($status == SYNC_FSSTATUS_SUCCESS) {
                self::$encoder->startTag(SYNC_FOLDERHIERARCHY_SYNCKEY);
                $synckey = ($changesMem->IsStateChanged()) ? $newsynckey : $synckey;
                self::$encoder->content($synckey);
                self::$encoder->endTag();

                // Stream folders directly to the PDA
                $streamimporter = new ImportChangesStream(self::$encoder, false);
                $changesMem->InitializeExporter($streamimporter);
                $changeCount = $changesMem->GetChangeCount();

                self::$encoder->startTag(SYNC_FOLDERHIERARCHY_CHANGES);
                {
                    self::$encoder->startTag(SYNC_FOLDERHIERARCHY_COUNT);
                    self::$encoder->content($changeCount);
                    self::$encoder->endTag();
                    while($changesMem->Synchronize());
                }
                self::$encoder->endTag();
                self::$topCollector->AnnounceInformation(sprintf("Outgoing %d folders",$changeCount), true);

                if ($changeCount == 0) {
                    self::$deviceManager->CheckFolderData();
                }
                // everything fine, save the sync state for the next time
                if ($synckey == $newsynckey) {
                    self::$deviceManager->GetStateManager()->SetSyncState($newsynckey, $newsyncstate);

                    // update SPA & save it
                    $spa->SetSyncKey($newsynckey);
                    $spa->SetFolderId(false);

                    // invalidate all pingable flags
                    SyncCollections::InvalidatePingableFlags();
                }
                // save the SyncParameters if it changed or the reference policy key is not set or different
                if ($spa->IsDataChanged() || !$spa->HasReferencePolicyKey() || self::$deviceManager->ProvisioningRequired($spa->GetReferencePolicyKey(), true, false)) {
                    // saves the SPA (while updating the reference policy key)
                    $spa->SetLastSynctime(time());
                    self::$deviceManager->GetStateManager()->SetSynchedFolderState($spa);
                }

            }
        }
        self::$encoder->endTag();

        return true;
    }
}
