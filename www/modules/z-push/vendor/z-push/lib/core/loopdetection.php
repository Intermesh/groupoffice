<?php
/***********************************************
* File      :   loopdetection.php
* Project   :   Z-Push
* Descr     :   detects an outgoing loop by looking
*               if subsequent requests do try to get changes
*               for the same sync key. If more than once a synckey
*               is requested, the amount of items to be sent to the mobile
*               is reduced to one. If then (again) the same synckey is
*               requested, we have most probably found the 'broken' item.
*
* Created   :   20.10.2011
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

class LoopDetection extends InterProcessData {
    const INTERPROCESSLD = "ipldkey";
    const BROKENMSGS = "bromsgs";
    static private $processident;
    static private $processentry;
    private $ignore_messageid;
    private $broken_message_uuid;
    private $broken_message_counter;


    /**
     * Constructor
     *
     * @access public
     */
    public function __construct() {
        // initialize super parameters
        $this->allocate = 1024000; // 1 MB
        $this->type = 1337;
        parent::__construct();

        $this->ignore_messageid = false;
    }

    /**
     * PROCESS LOOP DETECTION
     */

    /**
     * Adds the process entry to the process stack
     *
     * @access public
     * @return boolean
     */
    public function ProcessLoopDetectionInit() {
        return $this->updateProcessStack();
    }

    /**
     * Marks the process entry as termineted successfully on the process stack
     *
     * @access public
     * @return boolean
     */
    public function ProcessLoopDetectionTerminate() {
        // just to be sure that the entry is there
        self::GetProcessEntry();

        self::$processentry['end'] = time();
        ZLog::Write(LOGLEVEL_DEBUG, "LoopDetection->ProcessLoopDetectionTerminate()");
        return $this->updateProcessStack();
    }

    /**
     * Returns a unique identifier for the internal process tracking
     *
     * @access public
     * @return string
     */
    public static function GetProcessIdentifier() {
        if (!isset(self::$processident))
            self::$processident = sprintf('%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff));

        return self::$processident;
    }

    /**
     * Returns a unique entry with informations about the current process
     *
     * @access public
     * @return array
     */
    public static function GetProcessEntry() {
        if (!isset(self::$processentry)) {
            self::$processentry = array();
            self::$processentry['id'] = self::GetProcessIdentifier();
            self::$processentry['pid'] = self::$pid;
            self::$processentry['time'] = self::$start;
            self::$processentry['cc'] = Request::GetCommandCode();
        }

        return self::$processentry;
    }

    /**
     * Adds an Exceptions to the process tracking
     *
     * @param Exception     $exception
     *
     * @access public
     * @return boolean
     */
    public function ProcessLoopDetectionAddException($exception) {
        // generate entry if not already there
        self::GetProcessEntry();

        if (!isset(self::$processentry['stat']))
            self::$processentry['stat'] = array();

        self::$processentry['stat'][get_class($exception)] = $exception->getCode();

        $this->updateProcessStack();
        return true;
    }

    /**
     * Adds a folderid and connected status code to the process tracking
     *
     * @param string    $folderid
     * @param int       $status
     *
     * @access public
     * @return boolean
     */
    public function ProcessLoopDetectionAddStatus($folderid, $status) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("LoopDetection->ProcessLoopDetectionAddStatus: '%s' with status %d", $folderid?$folderid:'hierarchy', $status));
        // generate entry if not already there
        self::GetProcessEntry();

        if ($folderid === false)
            $folderid = "hierarchy";

        if (!isset(self::$processentry['stat']))
            self::$processentry['stat'] = array();

        self::$processentry['stat'][$folderid] = $status;

        $this->updateProcessStack();

        return true;
    }

    /**
     * Marks the current process as a PUSH connection
     *
     * @access public
     * @return boolean
     */
    public function ProcessLoopDetectionSetAsPush() {
        // generate entry if not already there
        self::GetProcessEntry();
        self::$processentry['push'] = true;

        return $this->updateProcessStack();
    }

    /**
     * Indicates if a simple Hierarchy sync should be done after Ping.
     *
     * When trying to sync a non existing folder, Sync will return Status 12.
     * This should trigger a hierarchy sync by the client, but this is not always done.
     * Clients continue trying to Ping, which fails as well and triggers a Sync again.
     * This goes on forever, like here: https://jira.z-hub.io/browse/ZP-1077
     *
     * Ping could indicate to perform a FolderSync as well after a few Sync/Ping cycles.
     *
     * @access public
     * @return boolean
     *
     */
    public function ProcessLoopDetectionIsHierarchySyncAdvised() {
        $me = self::GetProcessEntry();
        if ($me['cc'] !== ZPush::COMMAND_PING) {
            return false;
        }

        $loopingFolders = array();

        $lookback = self::$start - 600; // look at the last 5 min
        foreach ($this->getProcessStack() as $se) {
            if ($se['time'] > $lookback && $se['time'] < (self::$start-1)) {
                // look for sync command
                if (isset($se['stat']) && ($se['cc'] == ZPush::COMMAND_SYNC || $se['cc'] == ZPush::COMMAND_PING)) {
                    foreach($se['stat'] as $key => $value) {
                        // we only care about hierarchy errors of this folder
                        if ($se['cc'] == ZPush::COMMAND_SYNC) {
                            if ($value == SYNC_STATUS_FOLDERHIERARCHYCHANGED) {
                                ZLog::Write(LOGLEVEL_DEBUG, sprintf("LoopDetection->ProcessLoopDetectionIsHierarchySyncAdvised(): seen Sync command with Exception or folderid '%s' and code '%s'", $key, $value ));
                            }
                        }
                        if (!isset($loopingFolders[$key])) {
                            $loopingFolders[$key] = array(ZPush::COMMAND_SYNC => array(), ZPush::COMMAND_PING => array());
                        }
                        if (!isset($loopingFolders[$key][$se['cc']][$value])) {
                            $loopingFolders[$key][$se['cc']][$value] = 0;
                        }
                        $loopingFolders[$key][$se['cc']][$value]++;
                    }
                }
            }
        }

        $filtered = array();
        foreach ($loopingFolders as $fid => $data) {
            // Ping is constantly generating changes for this folder
            if (isset($data[ZPush::COMMAND_PING][SYNC_PINGSTATUS_CHANGES]) && $data[ZPush::COMMAND_PING][SYNC_PINGSTATUS_CHANGES] >= 3) {
                // but the Sync request is not treating it (not being requested)
                if (count($data[ZPush::COMMAND_SYNC]) == 0) {
                    ZLog::Write(LOGLEVEL_INFO, sprintf("LoopDetection->ProcessLoopDetectionIsHierarchySyncAdvised(): Ping loop of folderid '%s' detected that is not being synchronized.", $fid));
                    return true;
                }
                // Sync is executed, but a foldersync should be executed (hierarchy errors)
                if (isset($data[ZPush::COMMAND_SYNC][SYNC_STATUS_FOLDERHIERARCHYCHANGED]) &&
                        $data[ZPush::COMMAND_SYNC][SYNC_STATUS_FOLDERHIERARCHYCHANGED] > 3 ) {
                    ZLog::Write(LOGLEVEL_INFO, sprintf("LoopDetection->ProcessLoopDetectionIsHierarchySyncAdvised(): Sync(with error)/Ping loop of folderid '%s' detected.", $fid));
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Indicates if a full Hierarchy Resync is necessary
     *
     * In some occasions the mobile tries to sync a folder with an invalid/not-existing ID.
     * In these cases a status exception like SYNC_STATUS_FOLDERHIERARCHYCHANGED is returned
     * so the mobile executes a FolderSync expecting that some action is taken on that folder (e.g. remove).
     *
     * If the FolderSync is not doing anything relevant, then the Sync is attempted again
     * resulting in the same error and looping between these two processes.
     *
     * This method checks if in the last process stack a Sync and FolderSync were triggered to
     * catch the loop at the 2nd interaction (Sync->FolderSync->Sync->FolderSync => ReSync)
     * Ticket: https://jira.zarafa.com/browse/ZP-5
     *
     * @access public
     * @return boolean
     *
     */
    public function ProcessLoopDetectionIsHierarchyResyncRequired() {
        $seenFailed = array();
        $seenFolderSync = false;

        $lookback = self::$start - 600; // look at the last 5 min
        foreach ($this->getProcessStack() as $se) {
            if ($se['time'] > $lookback && $se['time'] < (self::$start-1)) {
                // look for sync command
                if (isset($se['stat']) && ($se['cc'] == ZPush::COMMAND_SYNC || $se['cc'] == ZPush::COMMAND_PING)) {
                    foreach($se['stat'] as $key => $value) {
                        // don't count PING with changes on a folder or sync with success
                        if (($se['cc'] == ZPush::COMMAND_PING && $value == SYNC_PINGSTATUS_CHANGES) ||
                            ($se['cc'] == ZPush::COMMAND_SYNC && $value == SYNC_STATUS_SUCCESS) ) {
                            continue;
                        }
                        if (!isset($seenFailed[$key]))
                            $seenFailed[$key] = 0;
                        $seenFailed[$key]++;
                        ZLog::Write(LOGLEVEL_DEBUG, sprintf("LoopDetection->ProcessLoopDetectionIsHierarchyResyncRequired(): seen command with Exception or folderid '%s' and code '%s'", $key, $value ));
                    }
                }
                // look for FolderSync command with previous failed commands
                if ($se['cc'] == ZPush::COMMAND_FOLDERSYNC && !empty($seenFailed) && $se['id'] != self::GetProcessIdentifier()) {
                    // a full folderresync was already triggered
                    if (isset($se['stat']) && isset($se['stat']['hierarchy']) && $se['stat']['hierarchy'] == SYNC_FSSTATUS_SYNCKEYERROR) {
                        ZLog::Write(LOGLEVEL_DEBUG, "LoopDetection->ProcessLoopDetectionIsHierarchyResyncRequired(): a full FolderReSync was already requested. Resetting fail counter.");
                        $seenFailed = array();
                    }
                    else {
                        $seenFolderSync = true;
                        if (!empty($seenFailed))
                            ZLog::Write(LOGLEVEL_DEBUG, "LoopDetection->ProcessLoopDetectionIsHierarchyResyncRequired(): seen FolderSync after other failing command");
                    }
                }
            }
        }

        $filtered = array();
        foreach ($seenFailed as $k => $count) {
            if ($count>1)
                $filtered[] = $k;
        }

        if ($seenFolderSync && !empty($filtered)) {
            ZLog::Write(LOGLEVEL_INFO, "LoopDetection->ProcessLoopDetectionIsHierarchyResyncRequired(): Potential loop detected. Full hierarchysync indicated.");
            return true;
        }

        return false;
    }

    /**
     * Indicates if a previous process could not be terminated
     *
     * Checks if there is an end time for the last entry on the stack
     *
     * @access public
     * @return boolean
     *
     */
    public function ProcessLoopDetectionPreviousConnectionFailed() {
        $stack = $this->getProcessStack();
        if (count($stack) > 1) {
            $se = $stack[0];
            if (!isset($se['end']) && $se['cc'] != ZPush::COMMAND_PING && !isset($se['push']) ) {
                // there is no end time
                ZLog::Write(LOGLEVEL_ERROR, sprintf("LoopDetection->ProcessLoopDetectionPreviousConnectionFailed(): Command '%s' at %s with pid '%d' terminated unexpectedly or is still running.", Utils::GetCommandFromCode($se['cc']), Utils::GetFormattedTime($se['time']), $se['pid']));
                ZLog::Write(LOGLEVEL_ERROR, "Please check your logs for this PID and errors like PHP-Fatals or Apache segmentation faults and report your results to the Z-Push dev team.");
            }
        }
    }

    /**
     * Gets the PID of an outdated search process
     *
     * Returns false if there isn't any process
     *
     * @access public
     * @return boolean
     *
     */
    public function ProcessLoopDetectionGetOutdatedSearchPID() {
        $stack = $this->getProcessStack();
        if (count($stack) > 1) {
            $se = $stack[0];
            if ($se['cc'] == ZPush::COMMAND_SEARCH) {
                return $se['pid'];
            }
        }
        return false;
    }

    /**
     * Inserts or updates the current process entry on the stack
     *
     * @access private
     * @return boolean
     */
    private function updateProcessStack() {
        // initialize params
        $this->initializeParams();
        if ($this->blockMutex()) {
            $loopdata = ($this->hasData()) ? $this->getData() : array();

            // check and initialize the array structure
            $this->checkArrayStructure($loopdata, self::INTERPROCESSLD);

            $stack = $loopdata[self::$devid][self::$user][self::INTERPROCESSLD];

            // insert/update current process entry
            $nstack = array();
            $updateentry = self::GetProcessEntry();
            $found = false;

            foreach ($stack as $entry) {
                if ($entry['id'] != $updateentry['id']) {
                    $nstack[] = $entry;
                }
                else {
                    $nstack[] = $updateentry;
                    $found = true;
                }
            }

            if (!$found)
                $nstack[] = $updateentry;

            if (count($nstack) > 10)
                $nstack = array_slice($nstack, -10, 10);

            // update loop data
            $loopdata[self::$devid][self::$user][self::INTERPROCESSLD] = $nstack;
            $ok = $this->setData($loopdata);

            $this->releaseMutex();
        }
        // end exclusive block

        return true;
    }

    /**
     * Returns the current process stack
     *
     * @access private
     * @return array
     */
    private function getProcessStack() {
        // initialize params
        $this->initializeParams();
        $stack = array();

        if ($this->blockMutex()) {
            $loopdata = ($this->hasData()) ? $this->getData() : array();

            // check and initialize the array structure
            $this->checkArrayStructure($loopdata, self::INTERPROCESSLD);

            $stack = $loopdata[self::$devid][self::$user][self::INTERPROCESSLD];

            $this->releaseMutex();
        }
        // end exclusive block

        return $stack;
    }

    /**
     * TRACKING OF BROKEN MESSAGES
     * if a previousily ignored message is streamed again to the device it's tracked here
     *
     * There are two outcomes:
     * - next uuid counter is higher than current -> message is fixed and successfully synchronized
     * - next uuid counter is the same or uuid changed -> message is still broken
     */

    /**
     * Adds a message to the tracking of broken messages
     * Being tracked means that a broken message was streamed to the device.
     * We save the latest uuid and counter so if on the next sync the counter is higher
     * the message was accepted by the device.
     *
     * @param string    $folderid   the parent folder of the message
     * @param string    $id         the id of the message
     *
     * @access public
     * @return boolean
     */
    public function SetBrokenMessage($folderid, $id) {
        if ($folderid == false || !isset($this->broken_message_uuid) || !isset($this->broken_message_counter) || $this->broken_message_uuid == false || $this->broken_message_counter == false)
            return false;

        $ok = false;
        $brokenkey = self::BROKENMSGS ."-". $folderid;

        // initialize params
        $this->initializeParams();
        if ($this->blockMutex()) {
            $loopdata = ($this->hasData()) ? $this->getData() : array();

            // check and initialize the array structure
            $this->checkArrayStructure($loopdata, $brokenkey);

            $brokenmsgs = $loopdata[self::$devid][self::$user][$brokenkey];

            $brokenmsgs[$id] = array('uuid' => $this->broken_message_uuid, 'counter' => $this->broken_message_counter);
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("LoopDetection->SetBrokenMessage('%s', '%s'): tracking broken message", $folderid, $id));

            // update data
            $loopdata[self::$devid][self::$user][$brokenkey] = $brokenmsgs;
            $ok = $this->setData($loopdata);

            $this->releaseMutex();
        }
        // end exclusive block

        return $ok;
    }

    /**
     * Gets a list of all ids of a folder which were tracked and which were
     * accepted by the device from the last sync.
     *
     * @param string    $folderid   the parent folder of the message
     * @param string    $id         the id of the message
     *
     * @access public
     * @return array
     */
    public function GetSyncedButBeforeIgnoredMessages($folderid) {
        if ($folderid == false || !isset($this->broken_message_uuid) || !isset($this->broken_message_counter) || $this->broken_message_uuid == false || $this->broken_message_counter == false)
            return array();

        $brokenkey = self::BROKENMSGS ."-". $folderid;
        $removeIds = array();
        $okIds = array();

        // initialize params
        $this->initializeParams();
        if ($this->blockMutex()) {
            $loopdata = ($this->hasData()) ? $this->getData() : array();

            // check and initialize the array structure
            $this->checkArrayStructure($loopdata, $brokenkey);

            $brokenmsgs = $loopdata[self::$devid][self::$user][$brokenkey];

            if (!empty($brokenmsgs)) {
                foreach ($brokenmsgs as $id => $data) {
                    // previously broken message was sucessfully synced!
                    if ($data['uuid'] == $this->broken_message_uuid && $data['counter'] < $this->broken_message_counter) {
                        ZLog::Write(LOGLEVEL_DEBUG, sprintf("LoopDetection->GetSyncedButBeforeIgnoredMessages('%s'): message '%s' was successfully synchronized", $folderid, $id));
                        $okIds[] = $id;
                    }

                    // if the uuid has changed this is old data which should also be removed
                    if ($data['uuid'] != $this->broken_message_uuid) {
                        ZLog::Write(LOGLEVEL_DEBUG, sprintf("LoopDetection->GetSyncedButBeforeIgnoredMessages('%s'): stored message id '%s' for uuid '%s' is obsolete", $folderid, $id, $data['uuid']));
                        $removeIds[] = $id;
                    }
                }

                // remove data
                foreach (array_merge($okIds,$removeIds) as $id) {
                    unset($brokenmsgs[$id]);
                }

                if (empty($brokenmsgs) && isset($loopdata[self::$devid][self::$user][$brokenkey])) {
                    unset($loopdata[self::$devid][self::$user][$brokenkey]);
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("LoopDetection->GetSyncedButBeforeIgnoredMessages('%s'): removed folder from tracking of ignored messages", $folderid));
                }
                else {
                    // update data
                    $loopdata[self::$devid][self::$user][$brokenkey] = $brokenmsgs;
                }
                $ok = $this->setData($loopdata);
            }

            $this->releaseMutex();
        }
        // end exclusive block

        return $okIds;
    }

    /**
     * Marks a SyncState as "already used", e.g. when an import process started.
     * This is most critical for DiffBackends, as an imported message would be exported again
     * in the heartbeat if the notification is triggered before the import is complete.
     *
     * @param string $folderid          folder id
     * @param string $uuid              synkkey
     * @param string $counter           synckey counter
     *
     * @access public
     * @return boolean
     */
    public function SetSyncStateUsage($folderid, $uuid, $counter) {
        // initialize params
        $this->initializeParams();

        ZLog::Write(LOGLEVEL_DEBUG, sprintf("LoopDetection->SetSyncStateUsage(): uuid: %s  counter: %d", $uuid, $counter));

        // exclusive block
        if ($this->blockMutex()) {
            $loopdata = ($this->hasData()) ? $this->getData() : array();
            // check and initialize the array structure
            $this->checkArrayStructure($loopdata, $folderid);
            $current = $loopdata[self::$devid][self::$user][$folderid];

            if (!isset($current["uuid"])) {
                $current["uuid"] = $uuid;
            }
            if (!isset($current["count"])) {
                $current["count"] = $counter;
            }
            if (!isset($current["queued"])) {
                $current["queued"] = 0;
            }

            // update the usage flag
            $current["usage"] = $counter;

            // update loop data
            $loopdata[self::$devid][self::$user][$folderid] = $current;
            $ok = $this->setData($loopdata);

            $this->releaseMutex();
        }
        // end exclusive block
    }

    /**
     * Checks if the given counter for a certain uuid+folderid was exported before.
     * Returns also true if the counter are the same but previously there were
     * changes to be exported.
     *
     * @param string $folderid          folder id
     * @param string $uuid              synkkey
     * @param string $counter           synckey counter
     *
     * @access public
     * @return boolean                  indicating if an uuid+counter were exported (with changes) before
     */
    public function IsSyncStateObsolete($folderid, $uuid, $counter) {
        // initialize params
        $this->initializeParams();

        $obsolete = false;

        // exclusive block
        if ($this->blockMutex()) {
            $loopdata = ($this->hasData()) ? $this->getData() : array();
            $this->releaseMutex();
            // end exclusive block

            // check and initialize the array structure
            $this->checkArrayStructure($loopdata, $folderid);

            $current = $loopdata[self::$devid][self::$user][$folderid];

            if (!empty($current)) {
                if (!isset($current["uuid"]) || $current["uuid"] != $uuid) {
                    ZLog::Write(LOGLEVEL_DEBUG, "LoopDetection->IsSyncStateObsolete(): yes, uuid changed or not set");
                    $obsolete = true;
                }
                else {
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("LoopDetection->IsSyncStateObsolete(): check folderid: '%s' uuid '%s' counter: %d - last counter: %d with %d queued",
                            $folderid, $uuid, $counter, $current["count"], $current["queued"]));

                    if ($current["uuid"] == $uuid && (
                            $current["count"] > $counter ||
                            ($current["count"] == $counter && $current["queued"] > 0) ||
                            (isset($current["usage"]) && $current["usage"] >= $counter)
                          )) {
                        $usage = isset($current["usage"]) ? sprintf(" - counter %d already expired",$current["usage"]) : "";
                        ZLog::Write(LOGLEVEL_DEBUG, "LoopDetection->IsSyncStateObsolete(): yes, counter already processed". $usage);
                        $obsolete = true;
                    }
                }
            }
            else {
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("LoopDetection->IsSyncStateObsolete(): check folderid: '%s' uuid '%s' counter: %d - no data found: not obsolete", $folderid, $uuid, $counter));
            }
        }

        return $obsolete;
    }

    /**
     * MESSAGE LOOP DETECTION
     */

    /**
     * Loop detection mechanism
     *
     *    1. request counter is higher than the previous counter (somehow default)
     *      1.1)   standard situation                                   -> do nothing
     *      1.2)   loop information exists
     *      1.2.1) request counter < maxCounter AND no ignored data     -> continue in loop mode
     *      1.2.2) request counter < maxCounter AND ignored data        -> we have already encountered issue, return to normal
     *
     *    2. request counter is the same as the previous, but no data was sent on the last request (standard situation)
     *
     *    3. request counter is the same as the previous and last time objects were sent (loop!)
     *      3.1)   no loop was detected before, entereing loop mode     -> save loop data, loopcount = 1
     *      3.2)   loop was detected before, but are gone               -> loop resolved
     *      3.3)   loop was detected before, continuing in loop mode    -> this is probably the broken element,loopcount++,
     *      3.3.1) item identified, loopcount >= 3                      -> ignore item, set ignoredata flag
     *
     * @param string $folderid          the current folder id to be worked on
     * @param string $uuid              the synkkey
     * @param string $counter           the synckey counter
     * @param string $maxItems          the current amount of items to be sent to the mobile
     * @param string $queuedMessages    the amount of messages which were found by the exporter
     *
     * @access public
     * @return boolean      when returning true if a loop has been identified
     */
    public function Detect($folderid, $uuid, $counter, $maxItems, $queuedMessages) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("LoopDetection->Detect(): folderid:'%s' uuid:'%s' counter:'%s' max:'%s' queued:'%s'", $folderid, $uuid, $counter, $maxItems, $queuedMessages));
        $this->broken_message_uuid = $uuid;
        $this->broken_message_counter = $counter;

        // if an incoming loop is already detected, do nothing
        if ($maxItems === 0 && $queuedMessages > 0) {
            ZPush::GetTopCollector()->AnnounceInformation("Incoming loop!", true);
            return true;
        }

        // initialize params
        $this->initializeParams();

        $loop = false;

        // exclusive block
        if ($this->blockMutex()) {
            $loopdata = ($this->hasData()) ? $this->getData() : array();

            // check and initialize the array structure
            $this->checkArrayStructure($loopdata, $folderid);

            $current = $loopdata[self::$devid][self::$user][$folderid];

            // completely new/unknown UUID
            if (empty($current))
                $current = array("uuid" => $uuid, "count" => $counter-1, "queued" => $queuedMessages);

            // old UUID in cache - the device requested a new state!!
            if (isset($current['uuid']) && $current['uuid'] != $uuid ) {
                ZLog::Write(LOGLEVEL_DEBUG, "LoopDetection->Detect(): UUID changed for folder");

                // some devices (iPhones) may request new UUIDs after broken items were sent several times
                if (isset($current['queued']) && $current['queued'] > 0 &&
                    (isset($current['maxCount']) && $current['count']+1 < $current['maxCount'] || $counter == 1)) {

                    ZLog::Write(LOGLEVEL_DEBUG, "LoopDetection->Detect(): UUID changed and while items where sent to device - forcing loop mode");
                    $loop = true; // force loop mode
                    $current['queued'] = $queuedMessages;
                }
                else {
                    $current['queued'] = 0;
                }

                // set new data, unset old loop information
                $current["uuid"] = $uuid;
                $current['count'] = $counter;
                unset($current['loopcount']);
                unset($current['ignored']);
                unset($current['maxCount']);
                unset($current['potential']);
            }

            // see if there are values
            if (isset($current['uuid']) && $current['uuid'] == $uuid &&
                isset($current['count'])) {

                // case 1 - standard, during loop-resolving & resolving
                if ($current['count'] < $counter) {

                    // case 1.1
                    $current['count'] = $counter;
                    $current['queued'] = $queuedMessages;
                    if (isset($current["usage"]) && $current["usage"] < $current['count'])
                        unset($current["usage"]);

                    // case 1.2
                    if (isset($current['maxCount'])) {
                        ZLog::Write(LOGLEVEL_DEBUG, "LoopDetection->Detect(): case 1.2 detected");

                        // case 1.2.1
                        // broken item not identified yet
                        if (!isset($current['ignored']) && $counter < $current['maxCount']) {
                            $current['loopcount'] = 1;
                            $loop = true; // continue in loop-resolving
                            ZLog::Write(LOGLEVEL_DEBUG, "LoopDetection->Detect(): case 1.2.1 detected");
                        }
                        // case 1.2.2 - if there were any broken items they should be gone, return to normal
                        else {
                            ZLog::Write(LOGLEVEL_DEBUG, "LoopDetection->Detect(): case 1.2.2 detected");
                            unset($current['loopcount']);
                            unset($current['ignored']);
                            unset($current['maxCount']);
                            unset($current['potential']);
                        }
                    }
                }

                // case 2 - same counter, but there were no changes before and are there now
                else if ($current['count'] == $counter && $current['queued'] == 0 && $queuedMessages > 0) {
                    $current['queued'] = $queuedMessages;
                    if (isset($current["usage"]) && $current["usage"] < $current['count'])
                        unset($current["usage"]);
                }

                // case 3 - same counter, changes sent before, hanging loop and ignoring
                else if ($current['count'] == $counter && $current['queued'] > 0) {

                    if (!isset($current['loopcount'])) {
                        // case 3.1) we have just encountered a loop!
                        ZLog::Write(LOGLEVEL_DEBUG, "LoopDetection->Detect(): case 3.1 detected - loop detected, init loop mode");
                        $current['loopcount'] = 1;
                        // the MaxCount is the max number of messages exported before
                        $current['maxCount'] = $counter + (($maxItems < $queuedMessages) ? $maxItems : $queuedMessages);
                        $loop = true;   // loop mode!!
                    }
                    else if ($queuedMessages == 0) {
                        // case 3.2) there was a loop before but now the changes are GONE
                        ZLog::Write(LOGLEVEL_DEBUG, "LoopDetection->Detect(): case 3.2 detected - changes gone - clearing loop data");
                        $current['queued'] = 0;
                        unset($current['loopcount']);
                        unset($current['ignored']);
                        unset($current['maxCount']);
                        unset($current['potential']);
                    }
                    else {
                        // case 3.3) still looping the same message! Increase counter
                        ZLog::Write(LOGLEVEL_DEBUG, "LoopDetection->Detect(): case 3.3 detected - in loop mode, increase loop counter");
                        $current['loopcount']++;

                        // case 3.3.1 - we got our broken item!
                        if ($current['loopcount'] >= 3 && isset($current['potential'])) {
                            ZLog::Write(LOGLEVEL_DEBUG, sprintf("LoopDetection->Detect(): case 3.3.1 detected - broken item should be next, attempt to ignore it - id '%s'", $current['potential']));
                            $this->ignore_messageid = $current['potential'];
                        }
                        $current['maxCount'] = $counter + (($maxItems < $queuedMessages) ? $maxItems : $queuedMessages);
                        $loop = true;   // loop mode!!
                    }
                }

            }
            if (isset($current['loopcount']))
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("LoopDetection->Detect(): loop data: loopcount(%d), maxCount(%d), queued(%d), ignored(%s)", $current['loopcount'], $current['maxCount'], $current['queued'], (isset($current['ignored'])?$current['ignored']:'false')));

            // update loop data
            $loopdata[self::$devid][self::$user][$folderid] = $current;
            $ok = $this->setData($loopdata);

            $this->releaseMutex();
        }
        // end exclusive block

        if ($loop == true && $this->ignore_messageid == false) {
            ZPush::GetTopCollector()->AnnounceInformation("Loop detection", true);
        }

        return $loop;
    }

    /**
     * Indicates if the next messages should be ignored (not be sent to the mobile!)
     *
     * @param string  $messageid        (opt) id of the message which is to be exported next
     * @param string  $folderid         (opt) parent id of the message
     * @param boolean $markAsIgnored    (opt) to peek without setting the next message to be
     *                                  ignored, set this value to false
     * @access public
     * @return boolean
     */
    public function IgnoreNextMessage($markAsIgnored = true, $messageid = false, $folderid = false) {
        // as the next message id is not available at all point this method is called, we use different indicators.
        // potentialbroken indicates that we know that the broken message should be exported next,
        // alltho we do not know for sure as it's export message orders can change
        // if the $messageid is available and matches then we are sure and only then really ignore it

        $potentialBroken = false;
        $realBroken = false;
        if (Request::GetCommandCode() == ZPush::COMMAND_SYNC && $this->ignore_messageid !== false)
            $potentialBroken = true;

        if ($messageid !== false && $this->ignore_messageid == $messageid)
            $realBroken = true;

        // this call is just to know what should be happening
        // no further actions necessary
        if ($markAsIgnored === false) {
            return $potentialBroken;
        }

        // we should really do something here

        // first we check if we are in the loop mode, if so,
        // we update the potential broken id message so we loop count the same message

        $changedData = false;
        // exclusive block
        if ($this->blockMutex()) {
            $loopdata = ($this->hasData()) ? $this->getData() : array();

            // check and initialize the array structure
            $this->checkArrayStructure($loopdata, $folderid);

            $current = $loopdata[self::$devid][self::$user][$folderid];

            // we found our broken message!
            if ($realBroken) {
                $this->ignore_messageid = false;
                $current['ignored'] = $messageid;
                $changedData = true;

                // check if this message was broken before - here we know that it still is and remove it from the tracking
                $brokenkey = self::BROKENMSGS ."-". $folderid;
                if (isset($loopdata[self::$devid][self::$user][$brokenkey]) && isset($loopdata[self::$devid][self::$user][$brokenkey][$messageid])) {
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("LoopDetection->IgnoreNextMessage(): previously broken message '%s' is still broken and will not be tracked anymore", $messageid));
                    unset($loopdata[self::$devid][self::$user][$brokenkey][$messageid]);
                }
            }
            // not the broken message yet
            else {
                // update potential id if looping on an item
                if (isset($current['loopcount'])) {
                    $current['potential'] = $messageid;

                    // this message should be the broken one, but is not!!
                    // we should reset the loop count because this is certainly not the broken one
                    if ($potentialBroken) {
                        $current['loopcount'] = 1;
                        ZLog::Write(LOGLEVEL_DEBUG, "LoopDetection->IgnoreNextMessage(): this should be the broken one, but is not! Resetting loop count.");
                    }

                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("LoopDetection->IgnoreNextMessage(): Loop mode, potential broken message id '%s'", $current['potential']));

                    $changedData = true;
                }
            }

            // update loop data
            if ($changedData == true) {
                $loopdata[self::$devid][self::$user][$folderid] = $current;
                $ok = $this->setData($loopdata);
            }

            $this->releaseMutex();
        }
        // end exclusive block

        if ($realBroken)
            ZPush::GetTopCollector()->AnnounceInformation("Broken message ignored", true);

        return $realBroken;
    }

    /**
     * Clears loop detection data
     *
     * @param string    $user           (opt) user which data should be removed - user can not be specified without
     * @param string    $devid          (opt) device id which data to be removed
     *
     * @return boolean
     * @access public
     */
    public function ClearData($user = false, $devid = false) {
        $stat = true;
        $ok = false;

        // exclusive block
        if ($this->blockMutex()) {
            $loopdata = ($this->hasData()) ? $this->getData() : array();

            if ($user == false && $devid == false)
                $loopdata = array();
            elseif ($user == false && $devid != false)
                $loopdata[$devid] = array();
            elseif ($user != false && $devid != false)
                $loopdata[$devid][$user] = array();
            elseif ($user != false && $devid == false) {
                ZLog::Write(LOGLEVEL_WARN, sprintf("Not possible to reset loop detection data for user '%s' without a specifying a device id", $user));
                $stat = false;
            }

            if ($stat)
                $ok = $this->setData($loopdata);

            $this->releaseMutex();
        }
        // end exclusive block

        return $stat && $ok;
    }

    /**
     * Returns loop detection data for a user and device
     *
     * @param string    $user
     * @param string    $devid
     *
     * @return array/boolean    returns false if data not available
     * @access public
     */
    public function GetCachedData($user, $devid) {
        // exclusive block
        if ($this->blockMutex()) {
            $loopdata = ($this->hasData()) ? $this->getData() : array();
            $this->releaseMutex();
        }
        // end exclusive block
        if (isset($loopdata) && isset($loopdata[$devid]) && isset($loopdata[$devid][$user]))
            return $loopdata[$devid][$user];

        return false;
    }

    /**
     * Builds an array structure for the loop detection data
     *
     * @param array $loopdata    reference to the topdata array
     *
     * @access private
     * @return
     */
    private function checkArrayStructure(&$loopdata, $folderid) {
        if (!isset($loopdata) || !is_array($loopdata))
            $loopdata = array();

        if (!isset($loopdata[self::$devid]))
            $loopdata[self::$devid] = array();

        if (!isset($loopdata[self::$devid][self::$user]))
            $loopdata[self::$devid][self::$user] = array();

        if (!isset($loopdata[self::$devid][self::$user][$folderid]))
            $loopdata[self::$devid][self::$user][$folderid] = array();
    }
}
