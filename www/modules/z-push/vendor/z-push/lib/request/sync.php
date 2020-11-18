<?php
/***********************************************
* File      :   sync.php
* Project   :   Z-Push
* Descr     :   Provides the SYNC command
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

class Sync extends RequestProcessor {
    // Ignored SMS identifier
    const ZPUSHIGNORESMS = "ZPISMS";
    private $importer;
    private $globallyExportedItems;
    private $singleFolder;
    private $multiFolderInfo;
    private $startTagsSent = false;
    private $startFolderTagSent = false;

    /**
     * Handles the Sync command
     * Performs the synchronization of messages
     *
     * @param int       $commandCode
     *
     * @access public
     * @return boolean
     */
    public function Handle($commandCode) {
        // Contains all requested folders (containers)
        $sc = new SyncCollections();
        $status = SYNC_STATUS_SUCCESS;
        $wbxmlproblem = false;
        $emptysync = false;
        $this->singleFolder = true;
        $this->multiFolderInfo = array();
        $this->globallyExportedItems = 0;


        // check if the hierarchySync was fully completed
        if (USE_PARTIAL_FOLDERSYNC) {
            if (self::$deviceManager->GetFolderSyncComplete() === false)  {
                ZLog::Write(LOGLEVEL_INFO, "Request->HandleSync(): Sync request aborted, as exporting of folders has not yet completed");
                self::$topCollector->AnnounceInformation("Aborted due incomplete folder sync", true);
                $status = SYNC_STATUS_FOLDERHIERARCHYCHANGED;
            }
            else
                ZLog::Write(LOGLEVEL_INFO, "Request->HandleSync(): FolderSync marked as complete");
        }

        // Start Synchronize
        if(self::$decoder->getElementStartTag(SYNC_SYNCHRONIZE)) {

            // AS 1.0 sends version information in WBXML
            if(self::$decoder->getElementStartTag(SYNC_VERSION)) {
                $sync_version = self::$decoder->getElementContent();
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("WBXML sync version: '%s'", $sync_version));
                if(!self::$decoder->getElementEndTag())
                    return false;
            }

            // Synching specified folders
            // Android still sends heartbeat sync even if all syncfolders are disabled.
            // Check if Folders tag is empty (<Folders/>) and only sync if there are
            // some folders in the request. See ZP-172
            $startTag = self::$decoder->getElementStartTag(SYNC_FOLDERS);
            if(isset($startTag[EN_FLAGS]) && $startTag[EN_FLAGS]) {
                while(self::$decoder->getElementStartTag(SYNC_FOLDER)) {
                    $actiondata = array();
                    $actiondata["requested"] = true;
                    $actiondata["clientids"] = array();
                    $actiondata["modifyids"] = array();
                    $actiondata["removeids"] = array();
                    $actiondata["fetchids"] = array();
                    $actiondata["statusids"] = array();

                    // read class, synckey and folderid without SyncParameters Object for now
                    $class = $synckey = $folderid = false;

                    // if there are already collections in SyncCollections, this is min. the second folder
                    if ($sc->HasCollections()) {
                        $this->singleFolder = false;
                    }

                    //for AS versions < 2.5
                    if(self::$decoder->getElementStartTag(SYNC_FOLDERTYPE)) {
                        $class = self::$decoder->getElementContent();
                        ZLog::Write(LOGLEVEL_DEBUG, sprintf("Sync folder: '%s'", $class));

                        if(!self::$decoder->getElementEndTag())
                            return false;
                    }

                    // SyncKey
                    if(self::$decoder->getElementStartTag(SYNC_SYNCKEY)) {
                        $synckey = "0";
                        if (($synckey = self::$decoder->getElementContent()) !== false) {
                            if(!self::$decoder->getElementEndTag()) {
                                return false;
                            }
                        }
                    }
                    else
                        return false;

                    // FolderId
                    if(self::$decoder->getElementStartTag(SYNC_FOLDERID)) {
                        $folderid = self::$decoder->getElementContent();

                        if(!self::$decoder->getElementEndTag())
                            return false;
                    }

                    // compatibility mode AS 1.0 - get folderid which was sent during GetHierarchy()
                    if (! $folderid && $class) {
                        $folderid = self::$deviceManager->GetFolderIdFromCacheByClass($class);
                    }

                    // folderid HAS TO BE known by now, so we retrieve the correct SyncParameters object for an update
                    try {
                        $spa = self::$deviceManager->GetStateManager()->GetSynchedFolderState($folderid);

                        // TODO remove resync of folders for < Z-Push 2 beta4 users
                        // this forces a resync of all states previous to Z-Push 2 beta4
                        if (! $spa instanceof SyncParameters)
                            throw new StateInvalidException("Saved state are not of type SyncParameters");

                        // new/resync requested
                        if ($synckey == "0") {
                            $spa->RemoveSyncKey();
                            $spa->DelFolderStat();
                            $spa->SetMoveState(false);
                        }
                        else if ($synckey !== false) {
                            if (($synckey !== $spa->GetSyncKey() && $synckey !== $spa->GetNewSyncKey()) || !!$spa->GetMoveState()) {
                                ZLog::Write(LOGLEVEL_DEBUG, "HandleSync(): Synckey does not match latest saved for this folder or there is a move state, removing folderstat to force Exporter setup");
                                $spa->DelFolderStat();
                            }
                            $spa->SetSyncKey($synckey);
                        }
                    }
                    catch (StateInvalidException $stie) {
                        $spa = new SyncParameters();
                        $status = SYNC_STATUS_INVALIDSYNCKEY;
                        self::$topCollector->AnnounceInformation("State invalid - Resync folder", $this->singleFolder);
                        self::$deviceManager->ForceFolderResync($folderid);
                        $this->saveMultiFolderInfo("exception", "StateInvalidException");
                    }

                    // update folderid.. this might be a new object
                    $spa->SetFolderId($folderid);
                    $spa->SetBackendFolderId(self::$deviceManager->GetBackendIdForFolderId($folderid));

                    if ($class !== false)
                        $spa->SetContentClass($class);

                    // Get class for as versions >= 12.0
                    if (! $spa->HasContentClass()) {
                        try {
                            $spa->SetContentClass(self::$deviceManager->GetFolderClassFromCacheByID($spa->GetFolderId()));
                            ZLog::Write(LOGLEVEL_DEBUG, sprintf("GetFolderClassFromCacheByID from Device Manager: '%s' for id:'%s'", $spa->GetContentClass(), $spa->GetFolderId()));
                        }
                        catch (NoHierarchyCacheAvailableException $nhca) {
                            $status = SYNC_STATUS_FOLDERHIERARCHYCHANGED;
                            self::$deviceManager->ForceFullResync();
                        }
                    }

                    // determine if this is the KOE GAB folder so it can be prioritized by SyncCollections
                    if (KOE_CAPABILITY_GAB && self::$deviceManager->IsKoe() && $spa->GetBackendFolderId() == self::$deviceManager->GetKoeGabBackendFolderId()) {
                        $spa->SetKoeGabFolder(true);
                    }

                    // done basic SPA initialization/loading -> add to SyncCollection
                    $sc->AddCollection($spa);
                    $sc->AddParameter($spa, "requested", true);

                    if ($spa->HasContentClass())
                        self::$topCollector->AnnounceInformation(sprintf("%s request", $spa->GetContentClass()), $this->singleFolder);
                    else
                        ZLog::Write(LOGLEVEL_WARN, "Not possible to determine class of request. Request did not contain class and apparently there is an issue with the HierarchyCache.");

                    // SUPPORTED properties
                    if(($se = self::$decoder->getElementStartTag(SYNC_SUPPORTED)) !== false) {
                        // ZP-481: LG phones send an empty supported tag, so only read the contents if available here
                        // if <Supported/> is received, it's as no supported fields would have been sent at all.
                        // unsure if this is the correct approach, or if in this case some default list should be used
                        if ($se[EN_FLAGS] & EN_FLAGS_CONTENT) {
                            $supfields = array();
                            WBXMLDecoder::ResetInWhile("syncSupported");
                            while(WBXMLDecoder::InWhile("syncSupported")) {
                                $el = self::$decoder->getElement();

                                if($el[EN_TYPE] == EN_TYPE_ENDTAG)
                                    break;
                                else
                                    $supfields[] = $el[EN_TAG];
                            }
                            self::$deviceManager->SetSupportedFields($spa->GetFolderId(), $supfields);
                        }
                    }

                    // Deletes as moves can be an empty tag as well as have value
                    if(self::$decoder->getElementStartTag(SYNC_DELETESASMOVES)) {
                        $spa->SetDeletesAsMoves(true);
                        if (($dam = self::$decoder->getElementContent()) !== false) {
                            $spa->SetDeletesAsMoves((bool)$dam);
                            if(!self::$decoder->getElementEndTag()) {
                                return false;
                            }
                        }
                    }

                    // Get changes can be an empty tag as well as have value
                    // code block partly contributed by dw2412
                    if($starttag = self::$decoder->getElementStartTag(SYNC_GETCHANGES)) {
                        $sc->AddParameter($spa, "getchanges", true);
                        if (($gc = self::$decoder->getElementContent()) !== false) {
                            $sc->AddParameter($spa, "getchanges", $gc);
                        }
                        // read the endtag if SYNC_GETCHANGES wasn't an empty tag
                        if ($starttag[EN_FLAGS] & EN_FLAGS_CONTENT) {
                            if (!self::$decoder->getElementEndTag()) {
                                return false;
                            }
                        }
                    }

                    if(self::$decoder->getElementStartTag(SYNC_WINDOWSIZE)) {
                        $ws = self::$decoder->getElementContent();
                        // normalize windowsize - see ZP-477
                        if ($ws == 0 || $ws > WINDOW_SIZE_MAX)
                            $ws = WINDOW_SIZE_MAX;

                        $spa->SetWindowSize($ws);

                        // also announce the currently requested window size to the DeviceManager
                        self::$deviceManager->SetWindowSize($spa->GetFolderId(), $spa->GetWindowSize());

                        if(!self::$decoder->getElementEndTag())
                            return false;
                    }

                    // conversation mode requested
                    if(self::$decoder->getElementStartTag(SYNC_CONVERSATIONMODE)) {
                        $spa->SetConversationMode(true);
                        if(($conversationmode = self::$decoder->getElementContent()) !== false) {
                            $spa->SetConversationMode((bool)$conversationmode);
                            if(!self::$decoder->getElementEndTag())
                            return false;
                        }
                    }

                    // Do not truncate by default
                    $spa->SetTruncation(SYNC_TRUNCATION_ALL);

                    // use default conflict handling if not specified by the mobile
                    $spa->SetConflict(SYNC_CONFLICT_DEFAULT);

                    // save the current filtertype because it might have been changed on the mobile
                    $currentFilterType = $spa->GetFilterType();

                    while(self::$decoder->getElementStartTag(SYNC_OPTIONS)) {
                        $firstOption = true;
                        WBXMLDecoder::ResetInWhile("syncOptions");
                        while(WBXMLDecoder::InWhile("syncOptions")) {
                            // foldertype definition
                            if(self::$decoder->getElementStartTag(SYNC_FOLDERTYPE)) {
                                $foldertype = self::$decoder->getElementContent();
                                ZLog::Write(LOGLEVEL_DEBUG, sprintf("HandleSync(): specified options block with foldertype '%s'", $foldertype));

                                // switch the foldertype for the next options
                                $spa->UseCPO($foldertype);

                                // save the current filtertype because it might have been changed on the mobile
                                $currentFilterType = $spa->GetFilterType();

                                // set to synchronize all changes. The mobile could overwrite this value
                                $spa->SetFilterType(SYNC_FILTERTYPE_ALL);

                                if(!self::$decoder->getElementEndTag())
                                    return false;
                            }
                            // if no foldertype is defined, use default cpo
                            else if ($firstOption){
                                $spa->UseCPO();
                                // save the current filtertype because it might have been changed on the mobile
                                $currentFilterType = $spa->GetFilterType();
                                // set to synchronize all changes. The mobile could overwrite this value
                                $spa->SetFilterType(SYNC_FILTERTYPE_ALL);
                            }
                            $firstOption = false;

                            if(self::$decoder->getElementStartTag(SYNC_FILTERTYPE)) {
                                $spa->SetFilterType(self::$decoder->getElementContent());
                                if(!self::$decoder->getElementEndTag())
                                    return false;
                            }
                            if(self::$decoder->getElementStartTag(SYNC_TRUNCATION)) {
                                $spa->SetTruncation(self::$decoder->getElementContent());
                                if(!self::$decoder->getElementEndTag())
                                    return false;
                            }
                            if(self::$decoder->getElementStartTag(SYNC_RTFTRUNCATION)) {
                                $spa->SetRTFTruncation(self::$decoder->getElementContent());
                                if(!self::$decoder->getElementEndTag())
                                    return false;
                            }

                            if(self::$decoder->getElementStartTag(SYNC_MIMESUPPORT)) {
                                $spa->SetMimeSupport(self::$decoder->getElementContent());
                                if(!self::$decoder->getElementEndTag())
                                    return false;
                            }

                            if(self::$decoder->getElementStartTag(SYNC_MIMETRUNCATION)) {
                                $spa->SetMimeTruncation(self::$decoder->getElementContent());
                                if(!self::$decoder->getElementEndTag())
                                    return false;
                            }

                            if(self::$decoder->getElementStartTag(SYNC_CONFLICT)) {
                                $spa->SetConflict(self::$decoder->getElementContent());
                                if(!self::$decoder->getElementEndTag())
                                    return false;
                            }

                            while (self::$decoder->getElementStartTag(SYNC_AIRSYNCBASE_BODYPREFERENCE)) {
                                if(self::$decoder->getElementStartTag(SYNC_AIRSYNCBASE_TYPE)) {
                                    $bptype = self::$decoder->getElementContent();
                                    $spa->BodyPreference($bptype);
                                    if(!self::$decoder->getElementEndTag()) {
                                        return false;
                                    }
                                }

                                if(self::$decoder->getElementStartTag(SYNC_AIRSYNCBASE_TRUNCATIONSIZE)) {
                                    $spa->BodyPreference($bptype)->SetTruncationSize(self::$decoder->getElementContent());
                                    if(!self::$decoder->getElementEndTag())
                                        return false;
                                }

                                if(self::$decoder->getElementStartTag(SYNC_AIRSYNCBASE_ALLORNONE)) {
                                    $spa->BodyPreference($bptype)->SetAllOrNone(self::$decoder->getElementContent());
                                    if(!self::$decoder->getElementEndTag())
                                        return false;
                                }

                                if(self::$decoder->getElementStartTag(SYNC_AIRSYNCBASE_PREVIEW)) {
                                    $spa->BodyPreference($bptype)->SetPreview(self::$decoder->getElementContent());
                                    if(!self::$decoder->getElementEndTag())
                                        return false;
                                }

                                if(!self::$decoder->getElementEndTag())
                                    return false;
                            }

                            if (self::$decoder->getElementStartTag(SYNC_AIRSYNCBASE_BODYPARTPREFERENCE)) {
                                if (self::$decoder->getElementStartTag(SYNC_AIRSYNCBASE_TYPE)) {
                                    $bpptype = self::$decoder->getElementContent();
                                    $spa->BodyPartPreference($bpptype);
                                    if (!self::$decoder->getElementEndTag()) {
                                        return false;
                                    }
                                }

                                if (self::$decoder->getElementStartTag(SYNC_AIRSYNCBASE_TRUNCATIONSIZE)) {
                                    $spa->BodyPartPreference($bpptype)->SetTruncationSize(self::$decoder->getElementContent());
                                    if(!self::$decoder->getElementEndTag())
                                        return false;
                                }

                                if (self::$decoder->getElementStartTag(SYNC_AIRSYNCBASE_ALLORNONE)) {
                                    $spa->BodyPartPreference($bpptype)->SetAllOrNone(self::$decoder->getElementContent());
                                    if(!self::$decoder->getElementEndTag())
                                        return false;
                                }

                                if (self::$decoder->getElementStartTag(SYNC_AIRSYNCBASE_PREVIEW)) {
                                    $spa->BodyPartPreference($bpptype)->SetPreview(self::$decoder->getElementContent());
                                    if(!self::$decoder->getElementEndTag())
                                        return false;
                                }

                                if (!self::$decoder->getElementEndTag())
                                    return false;
                            }

                            if (self::$decoder->getElementStartTag(SYNC_RIGHTSMANAGEMENT_SUPPORT)) {
                                $spa->SetRmSupport(self::$decoder->getElementContent());
                                if (!self::$decoder->getElementEndTag())
                                    return false;
                            }

                            $e = self::$decoder->peek();
                            if($e[EN_TYPE] == EN_TYPE_ENDTAG) {
                                self::$decoder->getElementEndTag();
                                break;
                            }
                        }
                    }

                    // limit items to be synchronized to the mobiles if configured
                    $maxAllowed = self::$deviceManager->GetFilterType($spa->GetFolderId(), $spa->GetBackendFolderId());
                    if ($maxAllowed > SYNC_FILTERTYPE_ALL &&
                        (!$spa->HasFilterType() || $spa->GetFilterType() == SYNC_FILTERTYPE_ALL || $spa->GetFilterType() > $maxAllowed)) {
                            ZLog::Write(LOGLEVEL_DEBUG, sprintf("HandleSync(): FilterType applied globally or specifically, using value: %s", $maxAllowed));
                            $spa->SetFilterType($maxAllowed);
                    }

                    // unset filtertype for KOE GAB folder
                    if ($spa->GetKoeGabFolder() === true) {
                        $spa->SetFilterType(SYNC_FILTERTYPE_ALL);
                        ZLog::Write(LOGLEVEL_DEBUG, "HandleSync(): KOE GAB folder - setting filter type to unlimited");
                    }

                    if ($currentFilterType != $spa->GetFilterType()) {
                        ZLog::Write(LOGLEVEL_DEBUG, sprintf("HandleSync(): FilterType has changed (old: '%s', new: '%s'), removing folderstat to force Exporter setup", $currentFilterType, $spa->GetFilterType()));
                        $spa->DelFolderStat();
                    }

                    // Check if the hierarchycache is available. If not, trigger a HierarchySync
                    if (self::$deviceManager->IsHierarchySyncRequired()) {
                        $status = SYNC_STATUS_FOLDERHIERARCHYCHANGED;
                        ZLog::Write(LOGLEVEL_DEBUG, "HierarchyCache is also not available. Triggering HierarchySync to device");
                    }

                    if(($el = self::$decoder->getElementStartTag(SYNC_PERFORM)) && ($el[EN_FLAGS] & EN_FLAGS_CONTENT)) {
                        // We can not proceed here as the content class is unknown
                        if ($status != SYNC_STATUS_SUCCESS) {
                            ZLog::Write(LOGLEVEL_WARN, "Ignoring all incoming actions as global status indicates problem.");
                            $wbxmlproblem = true;
                            break;
                        }

                        $performaction = true;

                        // unset the importer
                        $this->importer = false;

                        $nchanges = 0;
                        WBXMLDecoder::ResetInWhile("syncActions");
                        while(WBXMLDecoder::InWhile("syncActions")) {
                            // ADD, MODIFY, REMOVE or FETCH
                            $element = self::$decoder->getElement();

                            if($element[EN_TYPE] != EN_TYPE_STARTTAG) {
                                self::$decoder->ungetElement($element);
                                break;
                            }

                            if ($status == SYNC_STATUS_SUCCESS)
                                $nchanges++;

                            // Foldertype sent when synching SMS
                            if(self::$decoder->getElementStartTag(SYNC_FOLDERTYPE)) {
                                $foldertype = self::$decoder->getElementContent();
                                ZLog::Write(LOGLEVEL_DEBUG, sprintf("HandleSync(): incoming data with foldertype '%s'", $foldertype));

                                if(!self::$decoder->getElementEndTag())
                                return false;
                            }
                            else
                                $foldertype = false;

                            $serverid = false;
                            if(self::$decoder->getElementStartTag(SYNC_SERVERENTRYID)) {
                                if (($serverid = self::$decoder->getElementContent()) !== false) {
                                    if(!self::$decoder->getElementEndTag()) { // end serverid
                                        return false;
                                    }
                                }
                            }

                            if(self::$decoder->getElementStartTag(SYNC_CLIENTENTRYID)) {
                                $clientid = self::$decoder->getElementContent();

                                if(!self::$decoder->getElementEndTag()) // end clientid
                                    return false;
                            }
                            else
                                $clientid = false;

                            // Get the SyncMessage if sent
                            if(($el = self::$decoder->getElementStartTag(SYNC_DATA)) && ($el[EN_FLAGS] & EN_FLAGS_CONTENT)) {
                                $message = ZPush::getSyncObjectFromFolderClass($spa->GetContentClass());

                                // KOE ZO-42: OL sends Notes as Appointments
                                if ($spa->GetContentClass() == "Notes" && KOE_CAPABILITY_NOTES && self::$deviceManager->IsKoe()) {
                                    ZLog::Write(LOGLEVEL_DEBUG, "HandleSync(): KOE sends Notes as Appointments, read as SyncAppointment and convert it into a SyncNote object.");
                                    $message = new SyncAppointment();
                                    $message->Decode(self::$decoder);

                                    $note = new SyncNote();
                                    if (isset($message->asbody))
                                        $note->asbody = $message->asbody;
                                    if (isset($message->categories))
                                        $note->categories = $message->categories;
                                    if (isset($message->subject))
                                        $note->subject = $message->subject;
                                    if (isset($message->dtstamp))
                                        $note->lastmodified = $message->dtstamp;

                                    // set SyncNote->Color from a color category
                                    $note->SetColorFromCategory();

                                    $message = $note;
                                }
                                else {
                                    $message->Decode(self::$decoder);

                                    // set Ghosted fields
                                    $message->emptySupported(self::$deviceManager->GetSupportedFields($spa->GetFolderId()));
                                }

                                if(!self::$decoder->getElementEndTag()) // end applicationdata
                                    return false;
                            }
                            else
                                $message = false;

                            switch($element[EN_TAG]) {
                                case SYNC_FETCH:
                                    array_push($actiondata["fetchids"], $serverid);
                                    break;
                                default:
                                    // get the importer
                                    if ($this->importer == false)
                                        $status = $this->getImporter($sc, $spa, $actiondata);

                                    if ($status == SYNC_STATUS_SUCCESS)
                                        $this->importMessage($spa, $actiondata, $element[EN_TAG], $message, $clientid, $serverid, $foldertype, $nchanges);
                                    else
                                        ZLog::Write(LOGLEVEL_WARN, "Ignored incoming change, global status indicates problem.");

                                    break;
                            }

                            if ($actiondata["fetchids"])
                                self::$topCollector->AnnounceInformation(sprintf("Fetching %d", $nchanges));
                            else
                                self::$topCollector->AnnounceInformation(sprintf("Incoming %d", $nchanges));

                            if(!self::$decoder->getElementEndTag()) // end add/change/delete/move
                                return false;
                        }

                        if ($status == SYNC_STATUS_SUCCESS && $this->importer !== false) {
                            ZLog::Write(LOGLEVEL_INFO, sprintf("Processed '%d' incoming changes", $nchanges));
                            if (!$actiondata["fetchids"]) {
                                self::$topCollector->AnnounceInformation(sprintf("%d incoming", $nchanges), $this->singleFolder);
                                $this->saveMultiFolderInfo("incoming", $nchanges);
                            }

                            try {
                                // Save the updated state, which is used for the exporter later
                                $sc->AddParameter($spa, "state", $this->importer->GetState());
                            }
                            catch (StatusException $stex) {
                               $status = $stex->getCode();
                            }
                        }

                        if(!self::$decoder->getElementEndTag()) // end PERFORM
                            return false;
                    }

                    // save the failsave state
                    if (!empty($actiondata["statusids"])) {
                        unset($actiondata["failstate"]);
                        $actiondata["failedsyncstate"] = $sc->GetParameter($spa, "state");
                        self::$deviceManager->GetStateManager()->SetSyncFailState($actiondata);
                    }

                    // save actiondata
                    $sc->AddParameter($spa, "actiondata", $actiondata);

                    if(!self::$decoder->getElementEndTag()) // end collection
                        return false;

                    // AS14 does not send GetChanges anymore. We should do it if there were no incoming changes
                    if (!isset($performaction) && !$sc->GetParameter($spa, "getchanges") && $spa->HasSyncKey())
                        $sc->AddParameter($spa, "getchanges", true);
                } // END FOLDER

                if(!$wbxmlproblem && !self::$decoder->getElementEndTag()) // end collections
                    return false;
            } // end FOLDERS

            if (self::$decoder->getElementStartTag(SYNC_HEARTBEATINTERVAL)) {
                $hbinterval = self::$decoder->getElementContent();
                if(!self::$decoder->getElementEndTag()) // SYNC_HEARTBEATINTERVAL
                    return false;
            }

            if (self::$decoder->getElementStartTag(SYNC_WAIT)) {
                $wait = self::$decoder->getElementContent();
                if(!self::$decoder->getElementEndTag()) // SYNC_WAIT
                    return false;

                // internally the heartbeat interval and the wait time are the same
                // heartbeat is in seconds, wait in minutes
                $hbinterval = $wait * 60;
            }

            if (self::$decoder->getElementStartTag(SYNC_WINDOWSIZE)) {
                $sc->SetGlobalWindowSize(self::$decoder->getElementContent());
                ZLog::Write(LOGLEVEL_DEBUG, "Sync(): Global WindowSize requested: ". $sc->GetGlobalWindowSize());
                if(!self::$decoder->getElementEndTag()) // SYNC_WINDOWSIZE
                    return false;
            }

            if(self::$decoder->getElementStartTag(SYNC_PARTIAL))
                $partial = true;
            else
                $partial = false;

            if(!$wbxmlproblem && !self::$decoder->getElementEndTag()) // end sync
                return false;
        }
        // we did not receive a SYNCHRONIZE block - assume empty sync
        else {
            $emptysync = true;
        }
        // END SYNCHRONIZE

        // check heartbeat/wait time
        if (isset($hbinterval)) {
            if ($hbinterval < 60 || $hbinterval > 3540) {
                $status = SYNC_STATUS_INVALIDWAITORHBVALUE;
                ZLog::Write(LOGLEVEL_WARN, sprintf("HandleSync(): Invalid heartbeat or wait value '%s'", $hbinterval));
            }
        }

        // Partial & Empty Syncs need saved data to proceed with synchronization
        if ($status == SYNC_STATUS_SUCCESS && ($emptysync === true || $partial === true) ) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("HandleSync(): Partial or Empty sync requested. Retrieving data of synchronized folders."));

            // Load all collections - do not overwrite existing (received!), load states, check permissions and only load confirmed states!
            try {
                $sc->LoadAllCollections(false, true, true, true, true);
            }
            catch (StateInvalidException $siex) {
                $status = SYNC_STATUS_INVALIDSYNCKEY;
                self::$topCollector->AnnounceInformation("StateNotFoundException", $this->singleFolder);
                $this->saveMultiFolderInfo("exeption", "StateNotFoundException");
            }
            catch (StatusException $stex) {
               $status = SYNC_STATUS_FOLDERHIERARCHYCHANGED;
               self::$topCollector->AnnounceInformation(sprintf("StatusException code: %d", $status), $this->singleFolder);
               $this->saveMultiFolderInfo("exeption", "StatusException");
            }

            // update a few values
            foreach($sc as $folderid => $spa) {
                // manually set getchanges parameter for this collection if it is synchronized
                if ($spa->HasSyncKey()) {
                    $actiondata = $sc->GetParameter($spa, "actiondata");
                    // request changes if no other actions are executed
                    if (empty($actiondata["modifyids"]) && empty($actiondata["clientids"]) && empty($actiondata["removeids"])) {
                        $sc->AddParameter($spa, "getchanges", true);
                    }

                    // announce WindowSize to DeviceManager
                    self::$deviceManager->SetWindowSize($folderid, $spa->GetWindowSize());
                }
            }
            if (!$sc->HasCollections())
                $status = SYNC_STATUS_SYNCREQUESTINCOMPLETE;
        }
        else if (isset($hbinterval)) {
            // load the hierarchy data - there are no permissions to verify so we just set it to false
            if (!$sc->LoadCollection(false, true, false)) {
                $status = SYNC_STATUS_FOLDERHIERARCHYCHANGED;
                self::$topCollector->AnnounceInformation(sprintf("StatusException code: %d", $status), $this->singleFolder);
                $this->saveMultiFolderInfo("exeption", "StatusException");
            }
        }

        // HEARTBEAT
        if ($status == SYNC_STATUS_SUCCESS && isset($hbinterval)) {
            $interval = (defined('PING_INTERVAL') && PING_INTERVAL > 0) ? PING_INTERVAL : 30;

            if (isset($hbinterval))
                $sc->SetLifetime($hbinterval);

            // states are lazy loaded - we have to make sure that they are there!
            $loadstatus = SYNC_STATUS_SUCCESS;
            foreach($sc as $folderid => $spa) {
                // some androids do heartbeat on the OUTBOX folder, with weird results - ZP-362
                // we do not load the state so we will never get relevant changes on the OUTBOX folder
                if (self::$deviceManager->GetFolderTypeFromCacheById($folderid) == SYNC_FOLDER_TYPE_OUTBOX) {
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("HandleSync(): Heartbeat on Outbox folder not allowed"));
                    continue;
                }

                $fad = array();
                // if loading the states fails, we do not enter heartbeat, but we keep $status on SYNC_STATUS_SUCCESS
                // so when the changes are exported the correct folder gets an SYNC_STATUS_INVALIDSYNCKEY
                if ($loadstatus == SYNC_STATUS_SUCCESS)
                    $loadstatus = $this->loadStates($sc, $spa, $fad);
            }

            if ($loadstatus == SYNC_STATUS_SUCCESS) {
                $foundchanges = false;

                try {
                    // always check for changes
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("HandleSync(): Entering Heartbeat mode"));
                    $foundchanges = $sc->CheckForChanges($sc->GetLifetime(), $interval);
                }
                catch (StatusException $stex) {
                    if ($stex->getCode() == SyncCollections::OBSOLETE_CONNECTION) {
                        $status = SYNC_COMMONSTATUS_SYNCSTATEVERSIONINVALID;
                    }
                    else {
                        $status = SYNC_STATUS_FOLDERHIERARCHYCHANGED;
                        self::$topCollector->AnnounceInformation(sprintf("StatusException code: %d", $status), $this->singleFolder);
                        $this->saveMultiFolderInfo("exeption", "StatusException");
                    }
                }

                // update the waittime waited
                self::$waitTime = $sc->GetWaitedSeconds();

                // in case there are no changes and no other request has synchronized while we waited, we can reply with an empty response
                if (!$foundchanges && $status == SYNC_STATUS_SUCCESS) {
                    // if there were changes to the SPA or CPOs we need to save this before we terminate
                    // only save if the state was not modified by some other request, if so, return state invalid status
                    foreach($sc as $folderid => $spa) {
                        if (self::$deviceManager->CheckHearbeatStateIntegrity($spa->GetFolderId(), $spa->GetUuid(), $spa->GetUuidCounter())) {
                            $status = SYNC_COMMONSTATUS_SYNCSTATEVERSIONINVALID;
                        }
                        else {
                            $sc->SaveCollection($spa);
                        }
                    }

                    if ($status == SYNC_STATUS_SUCCESS) {
                        ZLog::Write(LOGLEVEL_DEBUG, "No changes found and no other process changed states. Replying with empty response and closing connection.");
                        self::$specialHeaders = array();
                        self::$specialHeaders[] = "Connection: close";
                        return true;
                    }
                }

                if ($foundchanges) {
                    foreach ($sc->GetChangedFolderIds() as $folderid => $changecount) {
                        // check if there were other sync requests for a folder during the heartbeat
                        $spa = $sc->GetCollection($folderid);
                        if ($changecount > 0 && $sc->WaitedForChanges() && self::$deviceManager->CheckHearbeatStateIntegrity($spa->GetFolderId(), $spa->GetUuid(), $spa->GetUuidCounter())) {
                            ZLog::Write(LOGLEVEL_DEBUG, sprintf("HandleSync(): heartbeat: found %d changes in '%s' which was already synchronized. Heartbeat aborted!", $changecount, $folderid));
                            $status = SYNC_COMMONSTATUS_SYNCSTATEVERSIONINVALID;
                        }
                        else
                            ZLog::Write(LOGLEVEL_DEBUG, sprintf("HandleSync(): heartbeat: found %d changes in '%s'", $changecount, $folderid));
                    }
                }
            }
        }

        // Start the output
        ZLog::Write(LOGLEVEL_DEBUG, "HandleSync(): Start Output");

        // global status
        // SYNC_COMMONSTATUS_* start with values from 101
        if ($status != SYNC_COMMONSTATUS_SUCCESS && ($status == SYNC_STATUS_FOLDERHIERARCHYCHANGED || $status > 100)) {
            self::$deviceManager->AnnounceProcessStatus($folderid, $status);
            $this->sendStartTags();
            self::$encoder->startTag(SYNC_STATUS);
                self::$encoder->content($status);
            self::$encoder->endTag();
            self::$encoder->endTag(); // SYNC_SYNCHRONIZE
            return true;
        }

        // Loop through requested folders
        foreach($sc as $folderid => $spa) {
            // get actiondata
            $actiondata = $sc->GetParameter($spa, "actiondata");

            if ($status == SYNC_STATUS_SUCCESS && (!$spa->GetContentClass() || !$spa->GetFolderId())) {
                ZLog::Write(LOGLEVEL_ERROR, sprintf("HandleSync(): no content class or folderid found for collection."));
                continue;
            }

            if (! $sc->GetParameter($spa, "requested")) {
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("HandleSync(): partial sync for folder class '%s' with id '%s'", $spa->GetContentClass(), $spa->GetFolderId()));
                // reload state and initialize StateMachine correctly
                $sc->AddParameter($spa, "state", null);
                $status = $this->loadStates($sc, $spa, $actiondata);
            }

            // initialize exporter to get changecount
            $changecount = false;
            $exporter = false;
            $streamimporter = false;
            $newFolderStat = false;
            $setupExporter = true;

            // TODO we could check against $sc->GetChangedFolderIds() on heartbeat so we do not need to configure all exporter again
            if($status == SYNC_STATUS_SUCCESS && ($sc->GetParameter($spa, "getchanges") || ! $spa->HasSyncKey())) {

                // no need to run the exporter if the globalwindowsize is already full - if collection already has a synckey (ZP-1215)
                if ($sc->GetGlobalWindowSize() == $this->globallyExportedItems && $spa->HasSyncKey()) {
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("Sync(): no exporter setup for '%s' as GlobalWindowSize is full.", $spa->GetFolderId()));
                    $setupExporter = false;
                }
                // if the maximum request timeout is reached, stop processing other collections
                if (Request::IsRequestTimeoutReached()) {
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("Sync(): no exporter setup for '%s' as request timeout reached, omitting output for collection.", $spa->GetFolderId()));
                    $setupExporter = false;
                }

                // if max memory allocation is reached, stop processing other collections
                if (Request::IsRequestMemoryLimitReached()) {
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("Sync(): no exporter setup for '%s' as max memory allocatation reached, omitting output for collection.", $spa->GetFolderId()));
                    $setupExporter = false;
                }

                // ZP-907: never send changes of UNKNOWN folders to an Outlook client
                if (Request::IsOutlook() && self::$deviceManager->GetFolderTypeFromCacheById($spa->GetFolderId()) == SYNC_FOLDER_TYPE_UNKNOWN && $spa->HasSyncKey()) {
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("Sync(): no exporter setup for '%s' as type is UNKNOWN.", $spa->GetFolderId()));
                    $setupExporter = false;
                }

                // force exporter run if there is a saved status
                if ($setupExporter && self::$deviceManager->HasFolderSyncStatus($spa->GetFolderId())) {
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("Sync(): forcing exporter setup for '%s' as a sync status is saved - ignoring backend folder stats", $spa->GetFolderId()));
                }
                // compare the folder statistics if the backend supports this
                elseif ($setupExporter && self::$backend->HasFolderStats()) {
                    // check if the folder stats changed -> if not, don't setup the exporter, there are no changes!
                    $newFolderStat = self::$backend->GetFolderStat(ZPush::GetAdditionalSyncFolderStore($spa->GetBackendFolderId()), $spa->GetBackendFolderId());
                    if ($newFolderStat !== false && ! $spa->IsExporterRunRequired($newFolderStat, true)) {
                        $changecount = 0;
                        $setupExporter = false;
                    }
                }

                // Do a full Exporter setup if we can't avoid it
                if ($setupExporter) {
                    //make sure the states are loaded
                    $status = $this->loadStates($sc, $spa, $actiondata);

                    if($status == SYNC_STATUS_SUCCESS) {
                        try {
                            // if this is an additional folder the backend has to be setup correctly
                            if (!self::$backend->Setup(ZPush::GetAdditionalSyncFolderStore($spa->GetBackendFolderId())))
                                throw new StatusException(sprintf("HandleSync() could not Setup() the backend for folder id %s/%s", $spa->GetFolderId(), $spa->GetBackendFolderId()), SYNC_STATUS_FOLDERHIERARCHYCHANGED);

                            // Use the state from the importer, as changes may have already happened
                            $exporter = self::$backend->GetExporter($spa->GetBackendFolderId());

                            if ($exporter === false)
                                throw new StatusException(sprintf("HandleSync() could not get an exporter for folder id %s/%s", $spa->GetFolderId(), $spa->GetBackendFolderId()), SYNC_STATUS_FOLDERHIERARCHYCHANGED);
                        }
                        catch (StatusException $stex) {
                           $status = $stex->getCode();
                        }
                        try {
                            // Stream the messages directly to the PDA
                            $streamimporter = new ImportChangesStream(self::$encoder, ZPush::getSyncObjectFromFolderClass($spa->GetContentClass()));

                            if ($exporter !== false) {
                                $exporter->SetMoveStates($spa->GetMoveState());
                                $exporter->Config($sc->GetParameter($spa, "state"));
                                $exporter->ConfigContentParameters($spa->GetCPO());
                                $exporter->InitializeExporter($streamimporter);

                                $changecount = $exporter->GetChangeCount();
                            }
                        }
                        catch (StatusException $stex) {
                            if ($stex->getCode() === SYNC_FSSTATUS_CODEUNKNOWN && $spa->HasSyncKey())
                                $status = SYNC_STATUS_INVALIDSYNCKEY;
                            else
                                $status = $stex->getCode();
                        }

                        if (! $spa->HasSyncKey()) {
                            self::$topCollector->AnnounceInformation(sprintf("Exporter registered. %d objects queued.", $changecount), $this->singleFolder);
                            $this->saveMultiFolderInfo("queued", $changecount);
                            // update folder status as initialized
                            $spa->SetFolderSyncTotal($changecount);
                            $spa->SetFolderSyncRemaining($changecount);
                            if ($changecount > 0) {
                                self::$deviceManager->SetFolderSyncStatus($folderid, DeviceManager::FLD_SYNC_INITIALIZED);
                            }
                        }
                        else if ($status != SYNC_STATUS_SUCCESS) {
                            self::$topCollector->AnnounceInformation(sprintf("StatusException code: %d", $status), $this->singleFolder);
                            $this->saveMultiFolderInfo("exception", "StatusException");
                        }
                        self::$deviceManager->AnnounceProcessStatus($spa->GetFolderId(), $status);
                    }
                }
            }

            // Get a new sync key to output to the client if any changes have been send by the mobile or a new synckey is to be sent
            if (!empty($actiondata["modifyids"]) ||
                !empty($actiondata["clientids"]) ||
                !empty($actiondata["removeids"]) ||
                (! $spa->HasSyncKey() && $status == SYNC_STATUS_SUCCESS)) {
                    $spa->SetNewSyncKey(self::$deviceManager->GetStateManager()->GetNewSyncKey($spa->GetSyncKey()));
            }
            // get a new synckey only if we did not reach the global limit yet
            else {
                // when reaching the global limit for changes of all collections, stop processing other collections (ZP-697)
                if ($sc->GetGlobalWindowSize() <= $this->globallyExportedItems) {
                    ZLog::Write(LOGLEVEL_DEBUG, "Global WindowSize for amount of exported changes reached, omitting output for collection.");
                    continue;
                }

                // get a new synckey if there are changes are we did not reach the limit yet
                if ($changecount > 0) {
                    $spa->SetNewSyncKey(self::$deviceManager->GetStateManager()->GetNewSyncKey($spa->GetSyncKey()));
                }
            }

            // Fir AS 14.0+ omit output for folder, if there were no incoming or outgoing changes and no Fetch
            if (Request::GetProtocolVersion() >= 14.0 && ! $spa->HasNewSyncKey() && $changecount == 0 && empty($actiondata["fetchids"]) && $status == SYNC_STATUS_SUCCESS &&
                    ! $spa->HasConfirmationChanged() && ($newFolderStat === false || ! $spa->IsExporterRunRequired($newFolderStat))) {
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("HandleSync: No changes found for %s folder id '%s'. Omitting output.", $spa->GetContentClass(), $spa->GetFolderId()));
                continue;
            }

            // if there are no other responses sent, we should end with a global status
            if ($status == SYNC_STATUS_FOLDERHIERARCHYCHANGED && $this->startTagsSent === false) {
                $this->sendStartTags();
                self::$encoder->startTag(SYNC_STATUS);
                self::$encoder->content($status);
                self::$encoder->endTag();
                self::$encoder->endTag(); // SYNC_SYNCHRONIZE
                return true;
            }

            // there is something to send here, sync folder to output
            $this->syncFolder($sc, $spa, $exporter, $changecount, $streamimporter, $status, $newFolderStat);

            // reset status for the next folder
            $status = SYNC_STATUS_SUCCESS;
        } // END foreach collection

        //SYNC_FOLDERS - only if the starttag was sent
        if ($this->startFolderTagSent)
            self::$encoder->endTag();

        // Check if there was any response - in case of an empty sync request, we shouldn't send an empty answer (ZP-1241)
        if (!$this->startTagsSent && $emptysync === true) {
            $this->sendStartTags();
            self::$encoder->startTag(SYNC_STATUS);
            self::$encoder->content(SYNC_STATUS_SYNCREQUESTINCOMPLETE);
            self::$encoder->endTag();
        }

        //SYNC_SYNCHRONIZE - only if the starttag was sent
        if ($this->startTagsSent)
            self::$encoder->endTag();

        // final top announcement for a multi-folder sync
        if ($sc->GetCollectionCount() > 1) {
            self::$topCollector->AnnounceInformation($this->getMultiFolderInfoLine($sc->GetCollectionCount()), true);
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("HandleSync: Processed %d folders", $sc->GetCollectionCount()));
        }

        // update the waittime waited
        self::$waitTime = $sc->GetWaitedSeconds();

        return true;
    }

    /**
     * Sends the SYNC_SYNCHRONIZE once per request.
     *
     * @access private
     * @return void
     */
    private function sendStartTags() {
        if ($this->startTagsSent === false) {
            self::$encoder->startWBXML();
            self::$encoder->startTag(SYNC_SYNCHRONIZE);
            $this->startTagsSent = true;
        }
    }

    /**
     * Sends the SYNC_FOLDERS once per request.
     *
     * @access private
     * @return void
     */
    private function sendFolderStartTag() {
        $this->sendStartTags();
        if ($this->startFolderTagSent === false) {
            self::$encoder->startTag(SYNC_FOLDERS);
            $this->startFolderTagSent = true;
        }
    }

    /**
     * Synchronizes a folder to the output stream. Changes for this folders are expected.
     *
     * @param SyncCollections       $sc
     * @param SyncParameters        $spa
     * @param IExportChanges        $exporter             Fully configured exporter for this folder
     * @param int                   $changecount          Amount of changes expected
     * @param ImportChangesStream   $streamimporter       Output stream
     * @param int                   $status               current status of the folder processing
     * @param string                $newFolderStat        the new folder stat to be set if everything was exported
     *
     * @throws StatusException
     * @return int  sync status code
     */
    private function syncFolder($sc, $spa, $exporter, $changecount, $streamimporter, $status, $newFolderStat) {
        $actiondata = $sc->GetParameter($spa, "actiondata");

        // send the WBXML start tags (if not happened already)
        $this->sendFolderStartTag();
        self::$encoder->startTag(SYNC_FOLDER);

        if($spa->HasContentClass()) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("Folder type: %s", $spa->GetContentClass()));
            // AS 12.0 devices require content class
            if (Request::GetProtocolVersion() < 12.1) {
                self::$encoder->startTag(SYNC_FOLDERTYPE);
                self::$encoder->content($spa->GetContentClass());
                self::$encoder->endTag();
            }
        }

        self::$encoder->startTag(SYNC_SYNCKEY);
        if($status == SYNC_STATUS_SUCCESS && $spa->HasNewSyncKey())
            self::$encoder->content($spa->GetNewSyncKey());
        else
            self::$encoder->content($spa->GetSyncKey());
        self::$encoder->endTag();

        self::$encoder->startTag(SYNC_FOLDERID);
        self::$encoder->content($spa->GetFolderId());
        self::$encoder->endTag();

        self::$encoder->startTag(SYNC_STATUS);
        self::$encoder->content($status);
        self::$encoder->endTag();

        // announce failing status to the process loop detection
        if ($status !== SYNC_STATUS_SUCCESS)
            self::$deviceManager->AnnounceProcessStatus($spa->GetFolderId(), $status);

        // Output IDs and status for incoming items & requests
        if($status == SYNC_STATUS_SUCCESS && (
                !empty($actiondata["clientids"]) ||
                !empty($actiondata["modifyids"]) ||
                !empty($actiondata["removeids"]) ||
                !empty($actiondata["fetchids"]) )) {

            self::$encoder->startTag(SYNC_REPLIES);
            // output result of all new incoming items
            foreach($actiondata["clientids"] as $clientid => $serverid) {
                self::$encoder->startTag(SYNC_ADD);
                self::$encoder->startTag(SYNC_CLIENTENTRYID);
                self::$encoder->content($clientid);
                self::$encoder->endTag();
                if ($serverid) {
                    self::$encoder->startTag(SYNC_SERVERENTRYID);
                    self::$encoder->content($serverid);
                    self::$encoder->endTag();
                }
                self::$encoder->startTag(SYNC_STATUS);
                self::$encoder->content((isset($actiondata["statusids"][$clientid])?$actiondata["statusids"][$clientid]:SYNC_STATUS_CLIENTSERVERCONVERSATIONERROR));
                self::$encoder->endTag();
                self::$encoder->endTag();
            }

            // loop through modify operations which were not a success, send status
            foreach($actiondata["modifyids"] as $serverid) {
                if (isset($actiondata["statusids"][$serverid]) && $actiondata["statusids"][$serverid] !== SYNC_STATUS_SUCCESS) {
                    self::$encoder->startTag(SYNC_MODIFY);
                    self::$encoder->startTag(SYNC_SERVERENTRYID);
                    self::$encoder->content($serverid);
                    self::$encoder->endTag();
                    self::$encoder->startTag(SYNC_STATUS);
                    self::$encoder->content($actiondata["statusids"][$serverid]);
                    self::$encoder->endTag();
                    self::$encoder->endTag();
                }
            }

            // loop through remove operations which were not a success, send status
            foreach($actiondata["removeids"] as $serverid) {
                if (isset($actiondata["statusids"][$serverid]) && $actiondata["statusids"][$serverid] !== SYNC_STATUS_SUCCESS) {
                    self::$encoder->startTag(SYNC_REMOVE);
                    self::$encoder->startTag(SYNC_SERVERENTRYID);
                    self::$encoder->content($serverid);
                    self::$encoder->endTag();
                    self::$encoder->startTag(SYNC_STATUS);
                    self::$encoder->content($actiondata["statusids"][$serverid]);
                    self::$encoder->endTag();
                    self::$encoder->endTag();
                }
            }

            if (!empty($actiondata["fetchids"])) {
                self::$topCollector->AnnounceInformation(sprintf("Fetching %d objects ", count($actiondata["fetchids"])), $this->singleFolder);
                $this->saveMultiFolderInfo("fetching", count($actiondata["fetchids"]));
            }

            foreach($actiondata["fetchids"] as $id) {
                $data = false;
                try {
                    $fetchstatus = SYNC_STATUS_SUCCESS;

                    // if this is an additional folder the backend has to be setup correctly
                    if (!self::$backend->Setup(ZPush::GetAdditionalSyncFolderStore($spa->GetBackendFolderId())))
                        throw new StatusException(sprintf("HandleSync(): could not Setup() the backend to fetch in folder id %s/%s", $spa->GetFolderId(), $spa->GetBackendFolderId()), SYNC_STATUS_OBJECTNOTFOUND);

                    $data = self::$backend->Fetch($spa->GetBackendFolderId(), $id, $spa->GetCPO());

                    // check if the message is broken
                    if (ZPush::GetDeviceManager(false) && ZPush::GetDeviceManager()->DoNotStreamMessage($id, $data)) {
                        ZLog::Write(LOGLEVEL_DEBUG, sprintf("HandleSync(): message not to be streamed as requested by DeviceManager, id = %s", $id));
                        $fetchstatus = SYNC_STATUS_CLIENTSERVERCONVERSATIONERROR;
                    }
                }
                catch (StatusException $stex) {
                    $fetchstatus = $stex->getCode();
                }

                self::$encoder->startTag(SYNC_FETCH);
                self::$encoder->startTag(SYNC_SERVERENTRYID);
                self::$encoder->content($id);
                self::$encoder->endTag();

                self::$encoder->startTag(SYNC_STATUS);
                self::$encoder->content($fetchstatus);
                self::$encoder->endTag();

                if($data !== false && $status == SYNC_STATUS_SUCCESS) {
                    self::$encoder->startTag(SYNC_DATA);
                    $data->Encode(self::$encoder);
                    self::$encoder->endTag();
                }
                else
                    ZLog::Write(LOGLEVEL_WARN, sprintf("Unable to Fetch '%s'", $id));
                self::$encoder->endTag();

            }
            self::$encoder->endTag();
        }

        if($sc->GetParameter($spa, "getchanges") && $spa->HasFolderId() && $spa->HasContentClass() && $spa->HasSyncKey()) {
            $moreAvailableSent = false;
            $windowSize = self::$deviceManager->GetWindowSize($spa->GetFolderId(), $spa->GetUuid(), $spa->GetUuidCounter(), $changecount);

            // limit windowSize to the max available limit of the global window size left
            $globallyAvailable = $sc->GetGlobalWindowSize() - $this->globallyExportedItems;
            if ($changecount > $globallyAvailable && $windowSize > $globallyAvailable) {
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("HandleSync(): Limit window size to %d as the global window size limit will be reached", $globallyAvailable));
                $windowSize = $globallyAvailable;
            }
            // send <MoreAvailable/> if there are more changes than fit in the folder windowsize
            // or there is a move state (another sync should be done afterwards)
            if($changecount > $windowSize || $spa->GetMoveState() !== false) {
                self::$encoder->startTag(SYNC_MOREAVAILABLE, false, true);
                $moreAvailableSent = true;
                $spa->DelFolderStat();
            }
        }

        // Stream outgoing changes
        if($status == SYNC_STATUS_SUCCESS && $sc->GetParameter($spa, "getchanges") == true && $windowSize > 0 && !!$exporter) {
            self::$topCollector->AnnounceInformation(sprintf("Streaming data of %d objects", (($changecount > $windowSize)?$windowSize:$changecount)));

            // Output message changes per folder
            self::$encoder->startTag(SYNC_PERFORM);

            $n = 0;
            WBXMLDecoder::ResetInWhile("syncSynchronize");
            while(WBXMLDecoder::InWhile("syncSynchronize")) {
                try {
                    $progress = $exporter->Synchronize();
                    if(!is_array($progress))
                        break;
                    $n++;
                    if ($n % 10 == 0)
                        self::$topCollector->AnnounceInformation(sprintf("Streamed data of %d objects out of %d", $n, (($changecount > $windowSize)?$windowSize:$changecount)));
                }
                catch (SyncObjectBrokenException $mbe) {
                    $brokenSO = $mbe->GetSyncObject();
                    if (!$brokenSO) {
                        ZLog::Write(LOGLEVEL_ERROR, sprintf("HandleSync(): Catched SyncObjectBrokenException but broken SyncObject not available. This should be fixed in the backend."));
                    }
                    else {
                        if (!isset($brokenSO->id)) {
                            $brokenSO->id = "Unknown ID";
                            ZLog::Write(LOGLEVEL_ERROR, sprintf("HandleSync(): Catched SyncObjectBrokenException but no ID of object set. This should be fixed in the backend."));
                        }
                        self::$deviceManager->AnnounceIgnoredMessage($spa->GetFolderId(), $brokenSO->id, $brokenSO);
                    }
                }
                // something really bad happened while exporting changes
                catch (StatusException $stex) {
                    $status = $stex->getCode();
                    // during export we found out that the states should be thrown away (ZP-623)
                    if ($status == SYNC_STATUS_INVALIDSYNCKEY) {
                        self::$deviceManager->ForceFolderResync($spa->GetFolderId());
                        break;
                    }
                }

                if($n >= $windowSize || Request::IsRequestTimeoutReached() || Request::IsRequestMemoryLimitReached()) {
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("HandleSync(): Exported maxItems of messages: %d / %d", $n, $changecount));
                    break;
                }
            }

            // $progress is not an array when exporting the last message
            // so we get the number to display from the streamimporter if it's available
            if (!!$streamimporter) {
                $n = $streamimporter->GetImportedMessages();
            }

            self::$encoder->endTag();

            // log the request timeout
            if (Request::IsRequestTimeoutReached() || Request::IsRequestMemoryLimitReached()) {
                ZLog::Write(LOGLEVEL_DEBUG, "HandleSync(): Stopping export as limits of request timeout or available memory are almost reached!");
                // Send a <MoreAvailable/> tag if we reached the request timeout or max memory, there are more changes and a moreavailable was not already send
                if (!$moreAvailableSent && ($n > $windowSize)) {
                    self::$encoder->startTag(SYNC_MOREAVAILABLE, false, true);
                    $spa->DelFolderStat();
                    $moreAvailableSent = true;
                }
            }

            self::$topCollector->AnnounceInformation(sprintf("Outgoing %d objects%s", $n, ($n >= $windowSize)?" of ".$changecount:""), $this->singleFolder);
            $this->saveMultiFolderInfo("outgoing", $n);
            $this->saveMultiFolderInfo("queued", $changecount);

            $this->globallyExportedItems += $n;

            // update folder status, if there is something set
            if ($spa->GetFolderSyncRemaining() && $changecount > 0) {
                $spa->SetFolderSyncRemaining($changecount);
            }
            // changecount is initialized with 'false', so 0 means no changes!
            if ($changecount === 0 || ($changecount !== false && $changecount <= $windowSize)) {
                self::$deviceManager->SetFolderSyncStatus($spa->GetFolderId(), DeviceManager::FLD_SYNC_COMPLETED);

                // we should update the folderstat, but we recheck to see if it changed. If so, it's not updated to force another sync
                if (self::$backend->HasFolderStats()) {
                    $newFolderStatAfterExport = self::$backend->GetFolderStat(ZPush::GetAdditionalSyncFolderStore($spa->GetBackendFolderId()), $spa->GetBackendFolderId());
                    if ($newFolderStat === $newFolderStatAfterExport) {
                        $this->setFolderStat($spa, $newFolderStat);
                    }
                    else {
                        ZLog::Write(LOGLEVEL_DEBUG, "Sync() Folderstat differs after export, force another exporter run.");
                    }
                }
            }
            else
                self::$deviceManager->SetFolderSyncStatus($spa->GetFolderId(), DeviceManager::FLD_SYNC_INPROGRESS);
        }

        self::$encoder->endTag();

        // Save the sync state for the next time
        if($spa->HasNewSyncKey()) {
            self::$topCollector->AnnounceInformation("Saving state");

            try {
                if (isset($exporter) && $exporter) {
                    $state = $exporter->GetState();

                    // update the move state (it should be gone now)
                    list($moveState,) = $exporter->GetMoveStates();
                    $spa->SetMoveState($moveState);
                }

                // nothing exported, but possibly imported - get the importer state
                else if ($sc->GetParameter($spa, "state") !== null)
                    $state = $sc->GetParameter($spa, "state");

                // if a new request without state information (hierarchy) save an empty state
                else if (! $spa->HasSyncKey())
                    $state = "";
            }
            catch (StatusException $stex) {
                $status = $stex->getCode();
            }


            if (isset($state) && $status == SYNC_STATUS_SUCCESS)
                self::$deviceManager->GetStateManager()->SetSyncState($spa->GetNewSyncKey(), $state, $spa->GetFolderId());
            else
                ZLog::Write(LOGLEVEL_ERROR, sprintf("HandleSync(): error saving '%s' - no state information available", $spa->GetNewSyncKey()));
        }

        // save SyncParameters
        if ($status == SYNC_STATUS_SUCCESS && empty($actiondata["fetchids"]))
            $sc->SaveCollection($spa);

        return $status;
    }

    /**
     * Loads the states and writes them into the SyncCollection Object and the actiondata failstate
     *
     * @param SyncCollection    $sc             SyncCollection object
     * @param SyncParameters    $spa            SyncParameters object
     * @param array             $actiondata     Actiondata array
     * @param boolean           $loadFailsave   (opt) default false - indicates if the failsave states should be loaded
     *
     * @access private
     * @return status           indicating if there were errors. If no errors, status is SYNC_STATUS_SUCCESS
     */
    private function loadStates($sc, $spa, &$actiondata, $loadFailsave = false) {
        $status = SYNC_STATUS_SUCCESS;

        if ($sc->GetParameter($spa, "state") == null) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("Sync->loadStates(): loading states for folder '%s'",$spa->GetFolderId()));

            try {
                $sc->AddParameter($spa, "state", self::$deviceManager->GetStateManager()->GetSyncState($spa->GetSyncKey()));

                if ($loadFailsave) {
                    // if this request was made before, there will be a failstate available
                    $actiondata["failstate"] = self::$deviceManager->GetStateManager()->GetSyncFailState();
                }

                // if this is an additional folder the backend has to be setup correctly
                if (!self::$backend->Setup(ZPush::GetAdditionalSyncFolderStore($spa->GetBackendFolderId())))
                    throw new StatusException(sprintf("HandleSync() could not Setup() the backend for folder id %s/%s", $spa->GetFolderId(), $spa->GetBackendFolderId()), SYNC_STATUS_FOLDERHIERARCHYCHANGED);
            }
            catch (StateNotFoundException $snfex) {
                $status = SYNC_STATUS_INVALIDSYNCKEY;
                self::$topCollector->AnnounceInformation("StateNotFoundException", $this->singleFolder);
                $this->saveMultiFolderInfo("exception", "StateNotFoundException");
            }
            catch (StatusException $stex) {
               $status = $stex->getCode();
               self::$topCollector->AnnounceInformation(sprintf("StatusException code: %d", $status), $this->singleFolder);
               $this->saveMultiFolderInfo("exception", "StateNotFoundException");

            }
        }

        return $status;
    }

    /**
     * Initializes the importer for the SyncParameters folder, loads necessary
     * states (incl. failsave states) and initializes the conflict detection
     *
     * @param SyncCollection    $sc             SyncCollection object
     * @param SyncParameters    $spa            SyncParameters object
     * @param array             $actiondata     Actiondata array
     *
     * @access private
     * @return status           indicating if there were errors. If no errors, status is SYNC_STATUS_SUCCESS
     */
    private function getImporter($sc, $spa, &$actiondata) {
        ZLog::Write(LOGLEVEL_DEBUG, "Sync->getImporter(): initialize importer");
        $status = SYNC_STATUS_SUCCESS;

        // load the states with failsave data
        $status = $this->loadStates($sc, $spa, $actiondata, true);

        try {
            if ($status == SYNC_STATUS_SUCCESS) {
                // Configure importer with last state
                $this->importer = self::$backend->GetImporter($spa->GetBackendFolderId());

                // if something goes wrong, ask the mobile to resync the hierarchy
                if ($this->importer === false)
                    throw new StatusException(sprintf("Sync->getImporter(): no importer for folder id %s/%s", $spa->GetFolderId(), $spa->GetBackendFolderId()), SYNC_STATUS_FOLDERHIERARCHYCHANGED);

                // set the move state so the importer is aware of previous made moves
                $this->importer->SetMoveStates($spa->GetMoveState());

                // if there is a valid state obtained after importing changes in a previous loop, we use that state
                if (isset($actiondata["failstate"]) && isset($actiondata["failstate"]["failedsyncstate"])) {
                    $this->importer->Config($actiondata["failstate"]["failedsyncstate"], $spa->GetConflict());
                }
                else
                    $this->importer->Config($sc->GetParameter($spa, "state"), $spa->GetConflict());

                // the CPO is also needed by the importer to check if imported changes are inside the sync window - see ZP-258
                $this->importer->ConfigContentParameters($spa->GetCPO());
                $this->importer->LoadConflicts($spa->GetCPO(), $sc->GetParameter($spa, "state"));
            }
        }
        catch (StatusException $stex) {
           $status = $stex->getCode();
        }

        return $status;
    }

    /**
     * Imports a message
     *
     * @param SyncParameters    $spa            SyncParameters object
     * @param array             $actiondata     Actiondata array
     * @param integer           $todo           WBXML flag indicating how message should be imported.
     *                                          Valid values: SYNC_ADD, SYNC_MODIFY, SYNC_REMOVE
     * @param SyncObject        $message        SyncObject message to be imported
     * @param string            $clientid       Client message identifier
     * @param string            $serverid       Server message identifier
     * @param string            $foldertype     On sms sync, this says "SMS", else false
     * @param integer           $messageCount   Counter of already imported messages
     *
     * @access private
     * @throws StatusException  in case the importer is not available
     * @return -                Message related status are returned in the actiondata.
     */
    private function importMessage($spa, &$actiondata, $todo, $message, $clientid, $serverid, $foldertype, $messageCount) {
        // the importer needs to be available!
        if ($this->importer == false)
            throw StatusException("Sync->importMessage(): importer not available", SYNC_STATUS_SERVERERROR);

        // mark this state as used, e.g. for HeartBeat
        self::$deviceManager->SetHeartbeatStateIntegrity($spa->GetFolderId(), $spa->GetUuid(), $spa->GetUuidCounter());

        // Detect incoming loop
        // messages which were created/removed before will not have the same action executed again
        // if a message is edited we perform this action "again", as the message could have been changed on the mobile in the meantime
        $ignoreMessage = false;
        if ($actiondata["failstate"]) {
            // message was ADDED before, do NOT add it again
            if ($todo == SYNC_ADD && isset($actiondata["failstate"]["clientids"][$clientid])) {
                $ignoreMessage = true;

                // make sure no messages are sent back
                self::$deviceManager->SetWindowSize($spa->GetFolderId(), 0);

                $actiondata["clientids"][$clientid] = $actiondata["failstate"]["clientids"][$clientid];
                $actiondata["statusids"][$clientid] = $actiondata["failstate"]["statusids"][$clientid];

                ZLog::Write(LOGLEVEL_WARN, sprintf("Mobile loop detected! Incoming new message '%s' was created on the server before. Replying with known new server id: %s", $clientid, $actiondata["clientids"][$clientid]));
            }

            // message was REMOVED before, do NOT attemp to remove it again
            if ($todo == SYNC_REMOVE && isset($actiondata["failstate"]["removeids"][$serverid])) {
                $ignoreMessage = true;

                // make sure no messages are sent back
                self::$deviceManager->SetWindowSize($spa->GetFolderId(), 0);

                $actiondata["removeids"][$serverid] = $actiondata["failstate"]["removeids"][$serverid];
                $actiondata["statusids"][$serverid] = $actiondata["failstate"]["statusids"][$serverid];

                ZLog::Write(LOGLEVEL_WARN, sprintf("Mobile loop detected! Message '%s' was deleted by the mobile before. Replying with known status: %s", $clientid, $actiondata["statusids"][$serverid]));
            }
        }

        if (!$ignoreMessage) {
            switch($todo) {
                case SYNC_MODIFY:
                    self::$topCollector->AnnounceInformation(sprintf("Saving modified message %d", $messageCount));
                    try {
                        $actiondata["modifyids"][] = $serverid;

                        // ignore sms messages
                        if ($foldertype == "SMS" || stripos($serverid, self::ZPUSHIGNORESMS) !== false) {
                            ZLog::Write(LOGLEVEL_DEBUG, "SMS sync are not supported. Ignoring message.");
                            // TODO we should update the SMS
                            $actiondata["statusids"][$serverid] = SYNC_STATUS_SUCCESS;
                        }
                        // check incoming message without logging WARN messages about errors
                        else if (!($message instanceof SyncObject) || !$message->Check(true)) {
                            $actiondata["statusids"][$serverid] = SYNC_STATUS_CLIENTSERVERCONVERSATIONERROR;
                        }
                        else {
                            if(isset($message->read)) {
                                // Currently, 'read' is only sent by the PDA when it is ONLY setting the read flag.
                                $this->importer->ImportMessageReadFlag($serverid, $message->read);
                            }
                            elseif (!isset($message->flag)) {
                                $this->importer->ImportMessageChange($serverid, $message);
                            }

                            // email todoflags - some devices send todos flags together with read flags,
                            // so they have to be handled separately
                            if (isset($message->flag)){
                                $this->importer->ImportMessageChange($serverid, $message);
                            }

                            $actiondata["statusids"][$serverid] = SYNC_STATUS_SUCCESS;
                        }
                    }
                    catch (StatusException $stex) {
                        $actiondata["statusids"][$serverid] = $stex->getCode();
                    }

                    break;
                case SYNC_ADD:
                    self::$topCollector->AnnounceInformation(sprintf("Creating new message from mobile %d", $messageCount));
                    try {
                        // mark the message as new message so SyncObject->Check() can differentiate
                        $message->flags = SYNC_NEWMESSAGE;

                        // ignore sms messages
                        if ($foldertype == "SMS") {
                            ZLog::Write(LOGLEVEL_DEBUG, "SMS sync are not supported. Ignoring message.");
                            // TODO we should create the SMS
                            // return a fake serverid which we can identify later
                            $actiondata["clientids"][$clientid] = self::ZPUSHIGNORESMS . $clientid;
                            $actiondata["statusids"][$clientid] = SYNC_STATUS_SUCCESS;
                        }
                        // check incoming message without logging WARN messages about errors
                        else if (!($message instanceof SyncObject) || !$message->Check(true)) {
                            $actiondata["clientids"][$clientid] = false;
                            $actiondata["statusids"][$clientid] = SYNC_STATUS_CLIENTSERVERCONVERSATIONERROR;
                        }
                        else {
                            $actiondata["clientids"][$clientid] = false;
                            $actiondata["clientids"][$clientid] = $this->importer->ImportMessageChange(false, $message);
                            $actiondata["statusids"][$clientid] = SYNC_STATUS_SUCCESS;
                        }
                    }
                    catch (StatusException $stex) {
                       $actiondata["statusids"][$clientid] = $stex->getCode();
                    }
                    break;
                case SYNC_REMOVE:
                    self::$topCollector->AnnounceInformation(sprintf("Deleting message removed on mobile %d", $messageCount));
                    try {
                        $actiondata["removeids"][] = $serverid;
                        // ignore sms messages
                        if ($foldertype == "SMS" || stripos($serverid, self::ZPUSHIGNORESMS) !== false) {
                            ZLog::Write(LOGLEVEL_DEBUG, "SMS sync are not supported. Ignoring message.");
                            // TODO we should delete the SMS
                            $actiondata["statusids"][$serverid] = SYNC_STATUS_SUCCESS;
                        }
                        else {
                            // if message deletions are to be moved, move them
                            if($spa->GetDeletesAsMoves()) {
                                $folderid = self::$backend->GetWasteBasket();

                                if($folderid) {
                                    $actiondata["statusids"][$serverid] = SYNC_STATUS_SUCCESS;
                                    $this->importer->ImportMessageMove($serverid, $folderid);
                                    break;
                                }
                                else
                                    ZLog::Write(LOGLEVEL_WARN, "Message should be moved to WasteBasket, but the Backend did not return a destination ID. Message is hard deleted now!");
                            }

                            $this->importer->ImportMessageDeletion($serverid);
                            $actiondata["statusids"][$serverid] = SYNC_STATUS_SUCCESS;
                        }
                    }
                    catch (StatusException $stex) {
                        if($stex->getCode() != SYNC_MOVEITEMSSTATUS_SUCCESS) {
                            $actiondata["statusids"][$serverid] = SYNC_STATUS_OBJECTNOTFOUND;
                        }
                    }
                    break;
            }
            ZLog::Write(LOGLEVEL_DEBUG, "Sync->importMessage(): message imported");
        }
    }

    /**
     * Keeps some interesting information about the sync process of several folders.
     *
     * @access private
     * @return
     */
    private function saveMultiFolderInfo($key, $value) {
        if ($key == "incoming" || $key == "outgoing" || $key == "queued" || $key == "fetching") {
            if (!isset($this->multiFolderInfo[$key])) {
                $this->multiFolderInfo[$key] = 0;
            }
            $this->multiFolderInfo[$key] += $value;
        }
        if ($key == "exception") {
            if (!isset($this->multiFolderInfo[$key])) {
                $this->multiFolderInfo[$key] = array();
            }
            $this->multiFolderInfo[$key][] = $value;
        }
    }

    /**
     * Returns a single string with information about the multi folder synchronization.
     *
     * @param int $amountOfFolders
     *
     * @access private
     * @return string
     */
    private function getMultiFolderInfoLine($amountOfFolders) {
        $s = $amountOfFolders . " folders";
        if (isset($this->multiFolderInfo["incoming"])) {
            $s .= ": ". $this->multiFolderInfo["incoming"] ." saved";
        }
        if (isset($this->multiFolderInfo["outgoing"]) && isset($this->multiFolderInfo["queued"]) && $this->multiFolderInfo["outgoing"] > 0) {
            $s .= sprintf(": Streamed %d out of %d", $this->multiFolderInfo["outgoing"], $this->multiFolderInfo["queued"]);
        }
        else if (!isset($this->multiFolderInfo["outgoing"]) && !isset($this->multiFolderInfo["queued"])) {
            $s .= ": no changes";
        }
        else {
            if (isset($this->multiFolderInfo["outgoing"])) {
                $s .= "/".$this->multiFolderInfo["outgoing"] ." streamed";
            }
            if (isset($this->multiFolderInfo["queued"])) {
                $s .= "/".$this->multiFolderInfo["queued"] ." queued";
            }
        }
        if (isset($this->multiFolderInfo["exception"])) {
            $exceptions = array_count_values($this->multiFolderInfo["exception"]);
            foreach ($exceptions as $name => $count) {
                $s .= sprintf("-%s(%d)", $name, $count);
            }
        }
        return $s;
    }

    /**
     * Sets the new folderstat and calculates & sets an expiration date for the folder stat.
     *
     * @param SyncParameters $spa
     * @param string $newFolderStat
     *
     * @access private
     * @return
     */
    private function setFolderStat($spa, $newFolderStat) {
        $spa->SetFolderStat($newFolderStat);
        $maxTimeout = 60 * 60 * 24 * 31; // one month

        $interval = Utils::GetFiltertypeInterval($spa->GetFilterType());
        $timeout = time() + (($interval && $interval < $maxTimeout) ? $interval : $maxTimeout);
        // randomize timout in 12h
        $timeout -= rand(0, 43200);
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("Sync()->setFolderStat() on %s: %s expiring %s", $spa->getFolderId(), $newFolderStat, date('Y-m-d H:i:s', $timeout)));
        $spa->SetFolderStatTimeout($timeout);
    }
}
