<?php
/***********************************************
* File      :   getitemestimate.php
* Project   :   Z-Push
* Descr     :   Provides the GETITEMESTIMATE command
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

class GetItemEstimate extends RequestProcessor {

    /**
     * Handles the GetItemEstimate command
     * Returns an estimation of how many items will be synchronized at the next sync
     * This is mostly used to show something in the progress bar
     *
     * @param int       $commandCode
     *
     * @access public
     * @return boolean
     */
    public function Handle($commandCode) {
        $sc = new SyncCollections();

        if(!self::$decoder->getElementStartTag(SYNC_GETITEMESTIMATE_GETITEMESTIMATE))
            return false;

        if(!self::$decoder->getElementStartTag(SYNC_GETITEMESTIMATE_FOLDERS))
            return false;

        while(self::$decoder->getElementStartTag(SYNC_GETITEMESTIMATE_FOLDER)) {
            $spa = new SyncParameters();
            $spastatus = false;

            // read the folder properties
            WBXMLDecoder::ResetInWhile("getItemEstimateFolders");
            while(WBXMLDecoder::InWhile("getItemEstimateFolders")) {
                if(self::$decoder->getElementStartTag(SYNC_SYNCKEY)) {
                    try {
                        $spa->SetSyncKey(self::$decoder->getElementContent());
                    }
                    catch (StateInvalidException $siex) {
                        $spastatus = SYNC_GETITEMESTSTATUS_SYNCSTATENOTPRIMED;
                    }

                    if(!self::$decoder->getElementEndTag())
                        return false;
                }

                elseif(self::$decoder->getElementStartTag(SYNC_GETITEMESTIMATE_FOLDERID)) {
                    $fid = self::$decoder->getElementContent();
                    $spa->SetFolderId($fid);
                    $spa->SetBackendFolderId(self::$deviceManager->GetBackendIdForFolderId($fid));

                    if(!self::$decoder->getElementEndTag())
                        return false;
                }

                // conversation mode requested
                elseif(self::$decoder->getElementStartTag(SYNC_CONVERSATIONMODE)) {
                    $spa->SetConversationMode(true);
                    if(($conversationmode = self::$decoder->getElementContent()) !== false) {
                        $spa->SetConversationMode((bool)$conversationmode);
                        if(!self::$decoder->getElementEndTag())
                            return false;
                    }
                }

                // get items estimate does not necessarily send the folder type
                elseif(self::$decoder->getElementStartTag(SYNC_GETITEMESTIMATE_FOLDERTYPE)) {
                    $spa->SetContentClass(self::$decoder->getElementContent());

                    if(!self::$decoder->getElementEndTag())
                        return false;
                }

                //TODO AS 2.5 and filtertype not set
                elseif(self::$decoder->getElementStartTag(SYNC_FILTERTYPE)) {
                    $spa->SetFilterType(self::$decoder->getElementContent());

                    if(!self::$decoder->getElementEndTag())
                        return false;
                }

                while(self::$decoder->getElementStartTag(SYNC_OPTIONS)) {
                    WBXMLDecoder::ResetInWhile("getItemEstimateOptions");
                    while(WBXMLDecoder::InWhile("getItemEstimateOptions")) {
                        $firstOption = true;
                        // foldertype definition
                        if(self::$decoder->getElementStartTag(SYNC_FOLDERTYPE)) {
                            $foldertype = self::$decoder->getElementContent();
                            ZLog::Write(LOGLEVEL_DEBUG, sprintf("HandleGetItemEstimate(): specified options block with foldertype '%s'", $foldertype));

                            // switch the foldertype for the next options
                            $spa->UseCPO($foldertype);

                            // set to synchronize all changes. The mobile could overwrite this value
                            $spa->SetFilterType(SYNC_FILTERTYPE_ALL);

                            if(!self::$decoder->getElementEndTag())
                                return false;
                        }
                        // if no foldertype is defined, use default cpo
                        else if ($firstOption){
                            $spa->UseCPO();
                            // set to synchronize all changes. The mobile could overwrite this value
                            $spa->SetFilterType(SYNC_FILTERTYPE_ALL);
                        }
                        $firstOption = false;

                        if(self::$decoder->getElementStartTag(SYNC_FILTERTYPE)) {
                            $spa->SetFilterType(self::$decoder->getElementContent());
                            if(!self::$decoder->getElementEndTag())
                                return false;
                        }

                        if(self::$decoder->getElementStartTag(SYNC_MAXITEMS)) {
                            $spa->SetWindowSize($maxitems = self::$decoder->getElementContent());
                            if(!self::$decoder->getElementEndTag())
                                return false;
                        }

                        $e = self::$decoder->peek();
                        if($e[EN_TYPE] == EN_TYPE_ENDTAG) {
                            self::$decoder->getElementEndTag();
                            break;
                        }
                    }
                }

                $e = self::$decoder->peek();
                if($e[EN_TYPE] == EN_TYPE_ENDTAG) {
                    self::$decoder->getElementEndTag(); //SYNC_GETITEMESTIMATE_FOLDER
                    break;
                }
            }
            // Process folder data

            //In AS 14 request only collectionid is sent, without class
            if (! $spa->HasContentClass() && $spa->HasFolderId()) {
               try {
                    $spa->SetContentClass(self::$deviceManager->GetFolderClassFromCacheByID($spa->GetFolderId()));
                }
                catch (NoHierarchyCacheAvailableException $nhca) {
                    $spastatus = SYNC_GETITEMESTSTATUS_COLLECTIONINVALID;
                }
            }

            // compatibility mode AS 1.0 - get folderid which was sent during GetHierarchy()
            if (! $spa->HasFolderId() && $spa->HasContentClass()) {
                $spa->SetFolderId(self::$deviceManager->GetFolderIdFromCacheByClass($spa->GetContentClass()));
            }

            // Add collection to SC and load state
            $sc->AddCollection($spa);
            if ($spastatus) {
                // the CPO has a folder id now, so we can set the status
                $sc->AddParameter($spa, "status", $spastatus);
            }
            else {
                try {
                    $sc->AddParameter($spa, "state", self::$deviceManager->GetStateManager()->GetSyncState($spa->GetSyncKey()));

                    // if this is an additional folder the backend has to be setup correctly
                    if (!self::$backend->Setup(ZPush::GetAdditionalSyncFolderStore($spa->GetBackendFolderId())))
                        throw new StatusException(sprintf("HandleGetItemEstimate() could not Setup() the backend for folder id %s/%s", $spa->GetFolderId(), $spa->GetBackendFolderId()), SYNC_GETITEMESTSTATUS_COLLECTIONINVALID);
                }
                catch (StateNotFoundException $snfex) {
                    // ok, the key is invalid. Question is, if the hierarchycache is still ok
                    //if not, we have to issue SYNC_GETITEMESTSTATUS_COLLECTIONINVALID which triggers a FolderSync
                    try {
                        self::$deviceManager->GetFolderClassFromCacheByID($spa->GetFolderId());
                        // we got here, so the HierarchyCache is ok
                        $sc->AddParameter($spa, "status", SYNC_GETITEMESTSTATUS_SYNCKKEYINVALID);
                    }
                    catch (NoHierarchyCacheAvailableException $nhca) {
                        $sc->AddParameter($spa, "status", SYNC_GETITEMESTSTATUS_COLLECTIONINVALID);
                    }

                    self::$topCollector->AnnounceInformation("StateNotFoundException ". $sc->GetParameter($spa, "status"), true);
                }
                catch (StatusException $stex) {
                    if ($stex->getCode() == SYNC_GETITEMESTSTATUS_COLLECTIONINVALID)
                        $sc->AddParameter($spa, "status", SYNC_GETITEMESTSTATUS_COLLECTIONINVALID);
                    else
                        $sc->AddParameter($spa, "status", SYNC_GETITEMESTSTATUS_SYNCSTATENOTPRIMED);
                    self::$topCollector->AnnounceInformation("StatusException ". $sc->GetParameter($spa, "status"), true);
                }
            }

        }
        if(!self::$decoder->getElementEndTag())
            return false; //SYNC_GETITEMESTIMATE_FOLDERS

        if(!self::$decoder->getElementEndTag())
            return false; //SYNC_GETITEMESTIMATE_GETITEMESTIMATE

        self::$encoder->startWBXML();
        self::$encoder->startTag(SYNC_GETITEMESTIMATE_GETITEMESTIMATE);
        {
            $status = SYNC_GETITEMESTSTATUS_SUCCESS;
            // look for changes in all collections

            try {
                $sc->CountChanges();
            }
            catch (StatusException $ste) {
                $status = SYNC_GETITEMESTSTATUS_COLLECTIONINVALID;
            }
            $changes = $sc->GetChangedFolderIds();

            foreach($sc as $folderid => $spa) {
                self::$encoder->startTag(SYNC_GETITEMESTIMATE_RESPONSE);
                {
                    if ($sc->GetParameter($spa, "status"))
                        $status = $sc->GetParameter($spa, "status");

                    self::$encoder->startTag(SYNC_GETITEMESTIMATE_STATUS);
                    self::$encoder->content($status);
                    self::$encoder->endTag();

                    self::$encoder->startTag(SYNC_GETITEMESTIMATE_FOLDER);
                    {
                        self::$encoder->startTag(SYNC_GETITEMESTIMATE_FOLDERTYPE);
                        self::$encoder->content($spa->GetContentClass());
                        self::$encoder->endTag();

                        self::$encoder->startTag(SYNC_GETITEMESTIMATE_FOLDERID);
                        self::$encoder->content($spa->GetFolderId());
                        self::$encoder->endTag();

                        if (isset($changes[$folderid]) && $changes[$folderid] !== false) {
                            self::$encoder->startTag(SYNC_GETITEMESTIMATE_ESTIMATE);
                            self::$encoder->content($changes[$folderid]);
                            self::$encoder->endTag();

                            if ($changes[$folderid] > 0)
                                self::$topCollector->AnnounceInformation(sprintf("%s %d changes", $spa->GetContentClass(), $changes[$folderid]), true);

                            // update the device data to mark folders as complete when synching with WM
                            if ($changes[$folderid] == 0)
                                self::$deviceManager->SetFolderSyncStatus($folderid, DeviceManager::FLD_SYNC_COMPLETED);
                        }
                    }
                    self::$encoder->endTag();
                }
                self::$encoder->endTag();
            }
            if (array_sum($changes) == 0)
                self::$topCollector->AnnounceInformation("No changes found", true);
        }
        self::$encoder->endTag();

        return true;
    }
}
