<?php
/***********************************************
* File      :   replybackimexporter.php
* Project   :   Z-Push
* Descr     :   This class fullfills the IImportChanges
*               and IExportChanges interfaces.
*               Messages that are imported are silently
*               ignored and then exported again.
*
* Created   :   22.04.2016
*
* Copyright 2016 Zarafa Deutschland GmbH
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

class ReplyBackImExporter implements IImportChanges, IExportChanges {
    const REPLYBACKID = "ReplyBack";
    const EXPORT_DELETE_AFTER_MOVE_TIMES = 3;
    const CHANGE = 1;
    const DELETION = 2;
    const READFLAG = 3;
    const CREATION = 4;
    const MOVEDHERE = 5;

    private $session;
    private $store;
    private $folderid;
    private $changes;
    private $changesDest;
    private $changesNext;
    private $step;
    private $exportImporter;
    private $mapiprovider;
    private $contentparameters;
    private $moveSrcState;
    private $moveDstState;

    /**
     * Constructor
     *
     * @param mapisession       $session
     * @param mapistore         $store
     * @param string            $folderid
     *
     * @access public
     * @throws StatusException
     */
    public function __construct($session, $store, $folderid) {
        $this->session = $session;
        $this->store = $store;
        $this->folderid = $folderid;

        $this->changes = array();
        $this->step = 0;

        $this->changesDest = array();
        $this->changesNext = array();
        $this->mapiprovider = new MAPIProvider($this->session, $this->store);
        $this->moveSrcState = false;
        $this->moveDstState = false;
    }

    /**
     * Initializes the state and flags.
     *
     * @param string        $state
     * @param int           $flags
     *
     * @access public
     * @return boolean      status flag
     * @throws StatusException
     */
    public function Config($state, $flags = 0) {
        if (is_array($state)) {
            $this->changes = array_merge($this->changes, $state);
        }
        $this->step = 0;
        return true;
    }

    /**
     * Configures additional parameters used for content synchronization.
     *
     * @param ContentParameters         $contentparameters
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
    public function ConfigContentParameters($contentparameters) {
        $this->contentparameters = $contentparameters;
        return true;
    }

    /**
     * Reads and returns the current state.
     *
     * @access public
     * @return string
     */
    public function GetState() {
        // we can discard all entries in the $changes array up to $step
        $changes = array_slice($this->changes, $this->step);
        return array_merge($changes, $this->changesNext);
    }

    /**
     * Sets the states from move operations.
     * When src and dst state are set, a MOVE operation is being executed.
     *
     * @param mixed         $srcState
     * @param mixed         (opt) $dstState, default: null
     *
     * @access public
     * @return boolean
     */
    public function SetMoveStates($srcState, $dstState = null) {
        if (is_array($srcState)) {
            $this->changes = array_merge($this->changes, $srcState);
        }
        if (is_array($dstState)) {
            $this->changesDest = array_merge($this->changes, $dstState);
        }
        return true;
    }

    /**
     * Gets the states of special move operations.
     *
     * @access public
     * @return array(0 => $srcState, 1 => $dstState)
     */
    public function GetMoveStates() {
        // if a move was executed, there will be changes for the destination folder, so we have to return the
        // source changes as well. If not, they will be transported via GetState().
        $srcMoveState = false;
        $dstMoveState = $this->changesDest;
        if (!empty($this->changesDest)) {
            $srcMoveState = $this->changes;
        }
        else {
            $dstMoveState = false;
        }
        return array($srcMoveState, $dstMoveState);
    }


    /**
     * Implement interfaces which are never used.
     */

    /**
     * Loads objects which are expected to be exported with the state.
     * Before importing/saving the actual message from the mobile, a conflict detection should be done.
     *
     * @param ContentParameters         $contentparameters
     * @param string                    $state
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
    public function LoadConflicts($contentparameters, $state) {
        return true;
    }

    /**
     * Imports a move of a message. This occurs when a user moves an item to another folder.
     *
     * @param string        $id
     * @param string        $newfolder      destination folder
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
    public function ImportMessageMove($id, $newfolder) {
        if (strtolower($newfolder) == strtolower(bin2hex($this->folderid)) )
            throw new StatusException(sprintf("ReplyBackImExporter->ImportMessageMove('%s','%s'): Error, source and destination are equal", $id, $newfolder), SYNC_MOVEITEMSSTATUS_SAMESOURCEANDDEST);

        // At this point, we don't know which case of move is happening:
        // 1. ReadOnly -> Writeable     (should normally work, message is duplicated)
        // 2. ReadOnly -> ReadOnly
        // 3. Writeable -> ReadOnly
        // As we don't know which case happens, we do the same for all cases (no move, no duplication!):
        //   1. in the src folder, the message is added again (same case as a deletion in RO)
        //   2. generate a tmp-id for the destination message in the destination folder
        //   3. for the destination folder, the tmp-id message is deleted (same as creation in RO)

        // make sure the message is added again to the src folder
        $this->changes[] = array(self::DELETION, $id, null);

        // generate tmp-id and have it removed later via the dest changes (saved via DstMoveState)
        $tmpId = $this->getTmpId($newfolder);
        $this->changesDest[] = array(self::MOVEDHERE, $tmpId, 0);
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("ReplyBackImExporter->ImportMessageMove(): Move forbidden. Restoring message in source folder and added a delete request for the destination folder for the id: %s", $tmpId));

        return $tmpId;
    }

    /**
     * Imports a change on a folder.
     *
     * @param object        $folder         SyncFolder
     *
     * @access public
     * @return boolean/SyncObject           status/object with the ath least the serverid of the folder set
     * @throws StatusException
     */
    public function ImportFolderChange($folder) {
        return false;
    }

    /**
     * Imports a folder deletion.
     *
     * @param SyncFolder    $folder         at least "serverid" needs to be set
     *
     * @access public
     * @return boolean/int  success/SYNC_FOLDERHIERARCHY_STATUS
     * @throws StatusException
    */
    public function ImportFolderDeletion($folder) {
        return false;
    }


    /**----------------------------------------------------------------------------------------------------------
     * IImportChanges
     */

    /**
     * Imports a message change, which is imported into memory.
     *
     * @param string        $id         id of message which is changed
     * @param SyncObject    $message    message to be changed
     *
     * @access public
     * @return boolean
     */
    public function ImportMessageChange($id, $message) {
        if(ZPush::GetDeviceManager()->IsKoe()) {
            // Ignore incoming update events of KOE caused by PatchItem - ZP-1060
            if (KOE_CAPABILITY_NOTES && $id && $message instanceof SyncNote && !isset($message->asbody)) {
                ZLog::Write(LOGLEVEL_DEBUG, "ReplyBackImExporter->ImportMessageChange(): KOE patch item update. Ignoring incoming update.");
                return true;
            }
            // KOE ZP-990: OL updates the deleted category which causes a race condition if more than one KOE is connected to that user
            if (KOE_CAPABILITY_RECEIVEFLAGS && $message instanceof SyncMail && !isset($message->flag) && isset($message->categories)) {
                // check if the categories changed
                $serverMessage = $this->getMessage($id, false);
                if((empty($message->categories) && empty($serverMessage->categories)) ||
                    (is_array($mapiCategories) && count(array_diff($mapiCategories, $message->categories)) == 0 && count(array_diff($message->categories, $mapiCategories)) == 0)) {
                        ZLog::Write(LOGLEVEL_DEBUG, "ReplyBackImExporter->ImportMessageChange(): KOE update of flag categories. Ignoring incoming update.");
                        return true;
                }
            }
        }
        $hexFolderid = bin2hex($this->folderid);
        // data is going to be dropped, inform the user
        if (@constant('READ_ONLY_NOTIFY_LOST_DATA')) {
            $notifyUser = true;


            $userFolder = ZPush::GetDeviceManager()->GetAdditionalUserSyncFolder($hexFolderid);
            if ($userFolder['flags'] & DeviceManager::FLD_FLAGS_NOREADONLYNOTIFY) {
                ZLog::Write(LOGLEVEL_INFO, "ReplyBackImExporter->ImportMessageChange(): the folder has no notify flag. Data received from the mobile will be lost. User was *not* informed as configured (see FLD_FLAGS_NOREADONLYNOTIFY)");
                $notifyUser = false;
            }
            elseif (@constant('READ_ONLY_NONOTIFY')) {
                $noNotifyFolders = explode(',', READ_ONLY_NONOTIFY);
                foreach ($noNotifyFolders as $noNotifyFolder) {
                    if (strcasecmp(trim($noNotifyFolder), $hexFolderid) == 0) {
                        ZLog::Write(LOGLEVEL_INFO, "ReplyBackImExporter->ImportMessageChange(): the folder is in no notify list. Data received from the mobile will be lost. User was *not* informed as configured (see READ_ONLY_NONOTIFY)");
                        $notifyUser = false;
                        break;
                    }
                }
            }

            if ($notifyUser) {
                try {
                    // get the old message - if there is no old message, this is a "create" action
                    $oldmessage = $this->getMessage($id, false);
                    if (!$oldmessage instanceof SyncObject) {
                        $oldmessage = $message;
                    }

                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("ReplyBackImExporter->ImportMessageChange(): Data send from the mobile will be lost. Sending email to user notifying about this."));
                    $this->sendNotificationEmail($message, $oldmessage);
                }
                catch (ZPushException $zpe) {
                    // TODO should we still print the email to the log so the data is not lost at all?
                    ZLog::Write(LOGLEVEL_ERROR, "ReplyBackImExporter->ImportMessageChange(): exception sending notification email");
                }
            }
        }
        else {
            ZLog::Write(LOGLEVEL_INFO, sprintf("ReplyBackImExporter->ImportMessageChange(): Data received from the mobile will be lost. User was *not* informed as configured (see READ_ONLY_NOTIFY_LOST_DATA)."));
        }

        if ($id) {
            $this->changes[] = array(self::CHANGE, $id, $message);
            return true;
        }
        // if there is no $id it means it's a new object. We have to reply back that we accepted it and then delete it.
        $id = $this->getTmpId($hexFolderid);
        $this->changes[] = array(self::CREATION, $id, $message);
        return $id;
    }

    /**
     * Imports a deletion. This may conflict if the local object has been modified.
     *
     * @param string        $id
     * @param boolean       $asSoftDelete   (opt) if true, the deletion is exported as "SoftDelete", else as "Remove" - default: false
     *
     * @access public
     * @return boolean
     */
    public function ImportMessageDeletion($id, $asSoftDelete = false) {
        // TODO do something different due to $asSoftDelete?
        $this->changes[] = array(self::DELETION, $id, null);
        throw new StatusException(sprintf("ReplyBackImExporter->ImportMessageDeletion('%s'): Read only folder. Data from PIM will be dropped! Server will read data.", $id), SYNC_STATUS_CONFLICTCLIENTSERVEROBJECT, null, LOGLEVEL_INFO);
    }

    /**
     * Imports a change in 'read' flag.
     * This can never conflict.
     *
     * @param string        $id
     * @param int           $flags
     * @param array         $categories
     *
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
    public function ImportMessageReadFlag($id, $flags, $categories = array()) {
        $this->changes[] = array(self::READFLAG, $id, $flags);
        return true;
    }


    /**----------------------------------------------------------------------------------------------------------
     * IExportChanges & destination importer
     */

    /**
     * Initializes the Exporter where changes are synchronized to.
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
     * Returns the amount of changes to be exported.
     *
     * @access public
     * @return int
     */
    public function GetChangeCount() {
        return count($this->changes);
    }

    /**
     * Synchronizes a change. The previously imported messages are now retrieved from the backend
     * and sent back to the mobile.
     *
     * @access public
     * @return array
     */
    public function Synchronize() {
        if($this->step < count($this->changes) && isset($this->exportImporter)) {

            $change = $this->changes[$this->step];

            $this->step++;
            $status = array("steps" => count($this->changes), "progress" => $this->step);

            $id = $change[1];
            $oldmessage = $change[2];


            // MOVEDHERE is an OL hack: export the deletion of the destination folder
            // several times, because OL doesn't removes the item the first time
            // we generate the same change again for EXPORT_DELETE_AFTER_MOVE_TIMES.
            if ($change[0] == self::MOVEDHERE) {
                $this->exportImporter->ImportMessageDeletion($id);
                if (is_int($oldmessage) && $oldmessage < self::EXPORT_DELETE_AFTER_MOVE_TIMES) {
                    $change[2]++;
                    $this->changesNext[] = $change;
                }
            }
            else if ($change[0] === self::CREATION || $this->isTmpId($id)) {
                $this->exportImporter->ImportMessageDeletion($id);
            }
            else {
                // This block also handles the read flags,
                // so that the todo flags are exported properly as well.
                // get the server side message
                $message = $this->getMessage($id);
                if (! $message instanceOf SyncObject) {
                    return $message;
                }

                if ($change[0] === self::DELETION) {
                    $message->flags = SYNC_NEWMESSAGE;
                }
                else {
                    $message->flags = "";
                }
                // only reply back on modify
                if ($change[1] !== "") {
                    $this->exportImporter->ImportMessageChange($id, $message);
                }
            }

            // return progress array
            return $status;
        }
        else
            return false;
    }

    /**
     * Generates a temporary id.
     *
     * @param string    $backendfolderid
     *
     * @access private
     * @return string
     */
    private function getTmpId($backendfolderid) {
        return ZPush::GetDeviceManager()->GetFolderIdForBackendId($backendfolderid) .":". self::REPLYBACKID ."". substr(md5(microtime()), 0, 5);
    }

    /**
     * Checks if an id is a temporary id generated by the ReplyBackImExporter.
     *
     * @access public
     * @return boolean
     */
    private function isTmpId($id) {
        return !!stripos($id, self::REPLYBACKID);
    }

    private function getMessage($id, $announceErrors = true) {
        if (!$id) {
            return false;
        }
        $message = false;

        list($fsk, $sk) = Utils::SplitMessageId($id);

        $sourcekey = hex2bin($sk);
        $parentsourcekey = hex2bin(ZPush::GetDeviceManager()->GetBackendIdForFolderId($fsk));
        // Backwards compatibility for old style folder ids
        if (empty($parentsourcekey)) {
            $parentsourcekey = $this->folderid;
        }
        $entryid = mapi_msgstore_entryidfromsourcekey($this->store, $parentsourcekey, $sourcekey);

        if(!$entryid) {
            ZLog::Write(LOGLEVEL_INFO, sprintf("ReplyBackImExporter->getMessage(): Couldn't retrieve message from MAPIProvider, sourcekey: '%s', parentsourcekey: '%s'", bin2hex($sourcekey), bin2hex($parentsourcekey), bin2hex($entryid)));
            return false;
        }

        $mapimessage = mapi_msgstore_openentry($this->store, $entryid);
        try {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("ReplyBackImExporter->getMessage(): Getting message from MAPIProvider, sourcekey: '%s', parentsourcekey: '%s', entryid: '%s'", bin2hex($sourcekey), bin2hex($parentsourcekey), bin2hex($entryid)));
            $message = $this->mapiprovider->GetMessage($mapimessage, $this->contentparameters);

            // strip or do not send private messages from shared folders to the device
            if (MAPIUtils::IsMessageSharedAndPrivate($this->folderid, $mapimessage)) {
                if ($message->SupportsPrivateStripping()) {
                    ZLog::Write(LOGLEVEL_DEBUG, "ReplyBackImExporter->getMessage(): stripping data of private message from a shared folder");
                    $message->StripData(Streamer::STRIP_PRIVATE_DATA);
                }
                else {
                    ZLog::Write(LOGLEVEL_DEBUG, "ReplyBackImExporter->getMessage(): ignoring private message from a shared folder");
                    return SYNC_E_IGNORE;
                }
            }
        }
        catch (SyncObjectBrokenException $mbe) {
            if ($announceErrors) {

                $brokenSO = $mbe->GetSyncObject();
                if (!$brokenSO) {
                    ZLog::Write(LOGLEVEL_ERROR, sprintf("ReplyBackImExporter->getMessage(): Catched SyncObjectBrokenException but broken SyncObject available"));
                }
                else {
                    if (!isset($brokenSO->id)) {
                        $brokenSO->id = "Unknown ID";
                        ZLog::Write(LOGLEVEL_ERROR, sprintf("ReplyBackImExporter->getMessage(): Catched SyncObjectBrokenException but no ID of object set"));
                    }
                    ZPush::GetDeviceManager()->AnnounceIgnoredMessage(false, $brokenSO->id, $brokenSO);
                }
                return false;
            }
        }
        return $message;
    }

    /**
     * Sends an email notification to the user containing the data the user tried to save.
     *
     * @param SyncObject $message
     * @param SyncObject $oldmessage
     * @return void
     */
    private function sendNotificationEmail($message, $oldmessage) {
        // get email address and full name of the user that performed the operation (auth user in all cases)
        $userinfo = ZPush::GetBackend()->GetUserDetails(Request::GetAuthUser());

        // get the name of the folder
        $foldername = "unknown";
        $folderid = bin2hex($this->folderid);
        $folders = ZPush::GetAdditionalSyncFolders();
        if (isset($folders[$folderid]) && isset($folders[$folderid]->displayname)) {
            $foldername = $folders[$folderid]->displayname;
        }

        // get the foldername from MAPI when impersonating - ZP-1369
        if ($foldername == "unknown") {
            $entryid = mapi_msgstore_entryidfromsourcekey($this->store, $this->folderid);
            $mapifolder = mapi_msgstore_openentry($this->store, $entryid);
            $folderprops = mapi_getprops($mapifolder, array(PR_DISPLAY_NAME));
            if (isset($folderprops[PR_DISPLAY_NAME])) {
                $foldername = $folderprops[PR_DISPLAY_NAME];
            }
        }

        // get the differences between the two objects
        $data = substr(get_class($oldmessage), 4) . "\r\n";
        // get the suppported fields as we need them to determine the ghosted properties
        $supportedFields = ZPush::GetDeviceManager()->GetSupportedFields(ZPush::GetDeviceManager()->GetFolderIdForBackendId($folderid));
        $dataarray = $oldmessage->EvaluateAndCompare($message, @constant('READ_ONLY_NOTIFY_YOURDATA'), $supportedFields);

        foreach($dataarray as $key => $value) {
            $value = str_replace("\r", "", $value);
            $value = str_replace("\n", str_pad("\r\n",25), $value);
            $data .= str_pad(ucfirst($key).":", 25) . $value ."\r\n";
        }

        // build a simple mime message
        $toEmail = $userinfo['emailaddress'];
        $mail  = "From: Z-Push <no-reply>\r\n";
        $mail .= "To: $toEmail\r\n";
        $mail .= "Content-Type: text/plain; charset=utf-8\r\n";
        $mail .= "Subject: ". @constant('READ_ONLY_NOTIFY_SUBJECT'). "\r\n\r\n";
        $mail .= @constant('READ_ONLY_NOTIFY_BODY'). "\r\n";

        // replace values of template
        $mail = str_replace("**USERFULLNAME**", $userinfo['fullname'], $mail);
        $mail = str_replace("**DATE**", strftime(@constant('READ_ONLY_NOTIFY_DATE_FORMAT')), $mail);
        $mail = str_replace("**TIME**", strftime(@constant('READ_ONLY_NOTIFY_TIME_FORMAT')), $mail);
        $mail = str_replace("**FOLDERNAME**", $foldername, $mail);
        $mail = str_replace("**MOBILETYPE**", Request::GetDeviceType(), $mail);
        $mail = str_replace("**MOBILEDEVICEID**", Request::GetDeviceID(), $mail);
        $mail = str_replace("**DIFFERENCES**", $data, $mail);

        // user send email to himself
        $m = new SyncSendMail();
        $m->saveinsent = false;
        $m->replacemime = true;
        $m->mime = $mail;

        ZPush::GetBackend()->SendMail($m);
    }

}
