<?php
/***********************************************
* File      :   ping.php
* Project   :   Z-Push
* Descr     :   Provides the PING command
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

class Ping extends RequestProcessor {

    /**
     * Handles the Ping command
     *
     * @param int       $commandCode
     *
     * @access public
     * @return boolean
     */
    public function Handle($commandCode) {
        $interval = (defined('PING_INTERVAL') && PING_INTERVAL > 0) ? PING_INTERVAL : 30;
        $pingstatus = false;
        $fakechanges = array();
        $foundchanges = false;

        // Contains all requested folders (containers)
        $sc = new SyncCollections();

        // read from stream to see if the symc params are being sent
        $params_present = self::$decoder->getElementStartTag(SYNC_PING_PING);

        // Load all collections - do load states, check permissions and allow unconfirmed states
        try {
            $sc->LoadAllCollections(true, true, true, true, false);
        }
        catch (StateInvalidException $siex) {
            // if no params are present, indicate to send params, else do hierarchy sync
            if (!$params_present) {
                $pingstatus = SYNC_PINGSTATUS_FAILINGPARAMS;
                self::$topCollector->AnnounceInformation("StateInvalidException: require PingParameters", true);
            }
            elseif (self::$deviceManager->IsHierarchySyncRequired()) {
                // we could be in a looping  - see LoopDetection->ProcessLoopDetectionIsHierarchySyncAdvised()
                $pingstatus = SYNC_PINGSTATUS_FOLDERHIERSYNCREQUIRED;
                self::$topCollector->AnnounceInformation("Potential loop detection: require HierarchySync", true);
            }
            else {
                // we do not have a ping status for this, but SyncCollections should have generated fake changes for the folders which are broken
                $fakechanges = $sc->GetChangedFolderIds();
                $foundchanges = true;

                self::$topCollector->AnnounceInformation("StateInvalidException: force sync", true);
            }
        }
        catch (StatusException $stex) {
            $pingstatus = SYNC_PINGSTATUS_FOLDERHIERSYNCREQUIRED;
            self::$topCollector->AnnounceInformation("StatusException: require HierarchySync", true);
        }

        ZLog::Write(LOGLEVEL_DEBUG, sprintf("HandlePing(): reference PolicyKey for PING: %s", $sc->GetReferencePolicyKey()));

        // receive PING initialization data
        if($params_present) {
            self::$topCollector->AnnounceInformation("Processing PING data");
            ZLog::Write(LOGLEVEL_DEBUG, "HandlePing(): initialization data received");

            if(self::$decoder->getElementStartTag(SYNC_PING_LIFETIME)) {
                $sc->SetLifetime(self::$decoder->getElementContent());
                self::$decoder->getElementEndTag();
            }

            if(($el = self::$decoder->getElementStartTag(SYNC_PING_FOLDERS)) && $el[EN_FLAGS] & EN_FLAGS_CONTENT) {
                // cache requested (pingable) folderids
                $pingable = array();

                while(self::$decoder->getElementStartTag(SYNC_PING_FOLDER)) {
                    WBXMLDecoder::ResetInWhile("pingFolder");
                    while(WBXMLDecoder::InWhile("pingFolder")) {
                        if(self::$decoder->getElementStartTag(SYNC_PING_SERVERENTRYID)) {
                            $folderid = self::$decoder->getElementContent();
                            self::$decoder->getElementEndTag();
                        }
                        if(self::$decoder->getElementStartTag(SYNC_PING_FOLDERTYPE)) {
                            $class = self::$decoder->getElementContent();
                            self::$decoder->getElementEndTag();
                        }

                        $e = self::$decoder->peek();
                        if($e[EN_TYPE] == EN_TYPE_ENDTAG) {
                            self::$decoder->getElementEndTag();
                            break;
                        }
                    }

                    $spa = $sc->GetCollection($folderid);
                    if (! $spa) {
                        // The requested collection is not synchronized.
                        // check if the HierarchyCache is available, if not, trigger a HierarchySync
                        try {
                            self::$deviceManager->GetFolderClassFromCacheByID($folderid);
                            // ZP-907: ignore all folders with SYNC_FOLDER_TYPE_UNKNOWN
                            if (self::$deviceManager->GetFolderTypeFromCacheById($folderid) == SYNC_FOLDER_TYPE_UNKNOWN) {
                                ZLog::Write(LOGLEVEL_DEBUG, sprintf("HandlePing(): ignoring folder id '%s' as it's of type UNKNOWN ", $folderid));
                                continue;
                            }
                        }
                        catch (NoHierarchyCacheAvailableException $nhca) {
                            ZLog::Write(LOGLEVEL_INFO, sprintf("HandlePing(): unknown collection '%s', triggering HierarchySync", $folderid));
                            $pingstatus = SYNC_PINGSTATUS_FOLDERHIERSYNCREQUIRED;
                        }

                        // Trigger a Sync request because then the device will be forced to resync this folder.
                        $fakechanges[$folderid] = 1;
                        $foundchanges = true;
                    }
                    else if ($this->isClassValid($class, $spa)) {
                        $pingable[] = $folderid;
                        ZLog::Write(LOGLEVEL_DEBUG, sprintf("HandlePing(): using saved sync state for '%s' id '%s'", $spa->GetContentClass(), $folderid));
                    }
                }
                if(!self::$decoder->getElementEndTag())
                    return false;

                // update pingable flags
                foreach ($sc as $folderid => $spa) {
                    // if the folderid is in $pingable, we should ping it, else remove the flag
                    if (in_array($folderid, $pingable)) {
                        $spa->SetPingableFlag(true);
                    }
                    else  {
                        $spa->DelPingableFlag();
                    }
                }
            }
            if(!self::$decoder->getElementEndTag())
                return false;

            if(!$this->lifetimeBetweenBound($sc->GetLifetime())){
                $pingstatus = SYNC_PINGSTATUS_HBOUTOFRANGE;
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("HandlePing(): ping lifetime not between bound (higher bound:'%d' lower bound:'%d' current lifetime:'%d'. Returning SYNC_PINGSTATUS_HBOUTOFRANGE.", PING_HIGHER_BOUND_LIFETIME, PING_LOWER_BOUND_LIFETIME, $sc->GetLifetime()));
            }
            // save changed data
            foreach ($sc as $folderid => $spa)
                $sc->SaveCollection($spa);
        } // END SYNC_PING_PING
        else {
            // if no ping initialization data was sent, we check if we have pingable folders
            // if not, we indicate that there is nothing to do.
            if (! $sc->PingableFolders()) {
                $pingstatus = SYNC_PINGSTATUS_FAILINGPARAMS;
                ZLog::Write(LOGLEVEL_DEBUG, "HandlePing(): no pingable folders found and no initialization data sent. Returning SYNC_PINGSTATUS_FAILINGPARAMS.");
            }
            elseif(!$this->lifetimeBetweenBound($sc->GetLifetime())){
                $pingstatus = SYNC_PINGSTATUS_FAILINGPARAMS;
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("HandlePing(): ping lifetime not between bound (higher bound:'%d' lower bound:'%d' current lifetime:'%d'. Returning SYNC_PINGSTATUS_FAILINGPARAMS.", PING_HIGHER_BOUND_LIFETIME, PING_LOWER_BOUND_LIFETIME, $sc->GetLifetime()));
            }
        }


        // Check for changes on the default LifeTime, set interval and ONLY on pingable collections
        try {
            if (!$pingstatus && empty($fakechanges)) {
                self::$deviceManager->DoAutomaticASDeviceSaving(false);
                $foundchanges = $sc->CheckForChanges($sc->GetLifetime(), $interval, true);
            }
        }
        catch (StatusException $ste) {
            switch($ste->getCode()) {
                case SyncCollections::ERROR_NO_COLLECTIONS:
                    $pingstatus = SYNC_PINGSTATUS_FAILINGPARAMS;
                    break;
                case SyncCollections::ERROR_WRONG_HIERARCHY:
                    $pingstatus = SYNC_PINGSTATUS_FOLDERHIERSYNCREQUIRED;
                    self::$deviceManager->AnnounceProcessStatus(false, $pingstatus);
                    break;
                case SyncCollections::OBSOLETE_CONNECTION:
                    $foundchanges = false;
                    break;
                case SyncCollections::HIERARCHY_CHANGED:
                    $pingstatus = SYNC_PINGSTATUS_FOLDERHIERSYNCREQUIRED;
                    break;
            }
        }

        self::$encoder->StartWBXML();
        self::$encoder->startTag(SYNC_PING_PING);
        {
            self::$encoder->startTag(SYNC_PING_STATUS);
            if (isset($pingstatus) && $pingstatus)
                self::$encoder->content($pingstatus);
            else
                self::$encoder->content($foundchanges ? SYNC_PINGSTATUS_CHANGES : SYNC_PINGSTATUS_HBEXPIRED);
            self::$encoder->endTag();

            if (! $pingstatus) {
                self::$encoder->startTag(SYNC_PING_FOLDERS);

                if (empty($fakechanges))
                    $changes = $sc->GetChangedFolderIds();
                else
                    $changes = $fakechanges;

                $announceAggregated = false;
                if (count($changes) > 1) {
                    $announceAggregated = 0;
                }
                foreach ($changes as $folderid => $changecount) {
                    if ($changecount > 0) {
                        self::$encoder->startTag(SYNC_PING_FOLDER);
                        self::$encoder->content($folderid);
                        self::$encoder->endTag();
                        if ($announceAggregated === false) {
                            if (empty($fakechanges)) {
                                self::$topCollector->AnnounceInformation(sprintf("Found change in %s", $sc->GetCollection($folderid)->GetContentClass()), true);
                            }
                        }
                        else {
                            $announceAggregated += $changecount;
                        }
                        self::$deviceManager->AnnounceProcessStatus($folderid, SYNC_PINGSTATUS_CHANGES);
                    }
                }
                if ($announceAggregated !== false) {
                    self::$topCollector->AnnounceInformation(sprintf("Found %d changes in %d folders", $announceAggregated, count($changes)), true);
                }
                self::$encoder->endTag();
            }
            elseif($pingstatus == SYNC_PINGSTATUS_HBOUTOFRANGE){
                self::$encoder->startTag(SYNC_PING_LIFETIME);
                if($sc->GetLifetime() > PING_HIGHER_BOUND_LIFETIME){
                    self::$encoder->content(PING_HIGHER_BOUND_LIFETIME);
                }
                else{
                    self::$encoder->content(PING_LOWER_BOUND_LIFETIME);
                }
                self::$encoder->endTag();
            }
        }

        self::$encoder->endTag();

        // update the waittime waited
        self::$waitTime = $sc->GetWaitedSeconds();

        return true;
    }

    /**
     * Return true if the ping lifetime is between the specified bound (PING_HIGHER_BOUND_LIFETIME and PING_LOWER_BOUND_LIFETIME). If no bound are specified, it returns true.
     *
     * @param int       $lifetime
     *
     * @access private
     * @return boolean
     */
    private function lifetimeBetweenBound($lifetime){
        if(PING_HIGHER_BOUND_LIFETIME !== false && PING_LOWER_BOUND_LIFETIME !== false){
            return ($lifetime <= PING_HIGHER_BOUND_LIFETIME && $lifetime >= PING_LOWER_BOUND_LIFETIME);
        }
        if(PING_HIGHER_BOUND_LIFETIME !== false){
            return $lifetime <= PING_HIGHER_BOUND_LIFETIME;
        }
        if(PING_LOWER_BOUND_LIFETIME !== false){
            return $lifetime >= PING_LOWER_BOUND_LIFETIME;
        }
        return true;
    }

    /**
     * Checks if a sent folder class is valid for that SyncParameters object.
     *
     * @param string $class
     * @param SycnParameters $spa
     *
     * @access public
     * @return boolean
     */
    private function isClassValid($class, $spa) {
        // ZP-907: Outlook might request a ping for such a folder, but we shouldn't answer it in any way
        if (Request::IsOutlook() && self::$deviceManager->GetFolderTypeFromCacheById($spa->GetFolderId()) == SYNC_FOLDER_TYPE_UNKNOWN) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("HandlePing()->isClassValid(): ignoring folder id '%s' as it's of type UNKNOWN ", $spa->GetFolderId()));
            return false;
        }
        if ($class == $spa->GetContentClass() ||
                // KOE ZO-42: Notes are synched as Appointments
                (self::$deviceManager->IsKoe() && KOE_CAPABILITY_NOTES && $class == "Calendar" && $spa->GetContentClass() == "Notes")
            ) {
            return true;
        }
        return false;
    }
}
