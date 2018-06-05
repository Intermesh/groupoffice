<?php
/***********************************************
* File      :   streamimporter.php
* Project   :   Z-Push
* Descr     :   sends changes directly to the wbxml stream
*
* Created   :   01.10.2007
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

class ImportChangesStream implements IImportChanges {
    private $encoder;
    private $objclass;
    private $seenObjects;
    private $importedMsgs;
    private $checkForIgnoredMessages;

    /**
     * Constructor of the StreamImporter
     *
     * @param WBXMLEncoder  $encoder        Objects are streamed to this encoder
     * @param SyncObject    $class          SyncObject class (only these are accepted when streaming content messages)
     *
     * @access public
     */
    public function __construct(&$encoder, $class) {
        $this->encoder = &$encoder;
        $this->objclass = $class;
        $this->classAsString = (is_object($class))?get_class($class):'';
        $this->seenObjects = array();
        $this->importedMsgs = 0;
        $this->checkForIgnoredMessages = true;
    }

    /**
     * Implement interface - never used
     */
    public function Config($state, $flags = 0) { return true; }
    public function ConfigContentParameters($contentparameters) { return true; }
    public function GetState() { return false;}
    public function SetMoveStates($srcState, $dstState = null) { return true; }
    public function GetMoveStates() { return array(false, false); }
    public function LoadConflicts($contentparameters, $state) { return true; }

    /**
     * Imports a single message
     *
     * @param string        $id
     * @param SyncObject    $message
     *
     * @access public
     * @return boolean
     */
    public function ImportMessageChange($id, $message) {
        // ignore other SyncObjects
        if(!($message instanceof $this->classAsString)) {
            return false;
        }

        // KOE ZO-42: to sync Notes to Outlook we sync them as Appointments
        if ($this->classAsString == "SyncNote") {
            if (KOE_CAPABILITY_NOTES && ZPush::GetDeviceManager()->IsKoe()) {
                // update category from SyncNote->Color
                $message->SetCategoryFromColor();

                $appointment = new SyncAppointment();
                $appointment->busystatus = 0;
                $appointment->sensitivity = 0;
                $appointment->alldayevent = 0;
                $appointment->reminder = 0;
                $appointment->meetingstatus = 0;
                $appointment->responserequested = 0;

                $appointment->flags = $message->flags;
                if (isset($message->asbody))
                    $appointment->asbody = $message->asbody;
                if (isset($message->categories))
                    $appointment->categories = $message->categories;
                if (isset($message->subject))
                    $appointment->subject = $message->subject;
                if (isset($message->lastmodified))
                    $appointment->dtstamp = $message->lastmodified;

                $appointment->starttime = time();
                $appointment->endtime = $appointment->starttime + 1;

                $message = $appointment;
            }
            else if (Request::IsOutlook()) {
                ZLog::Write(LOGLEVEL_WARN, "MS Outlook is synchronizing Notes folder without active KOE settings or extension. Not streaming SyncNote change!");
                return false;
            }
        }

        // prevent sending the same object twice in one request
        if (in_array($id, $this->seenObjects)) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("Object '%s' discarded! Object already sent in this request.", $id));
            return true;
        }

        $this->importedMsgs++;
        $this->seenObjects[] = $id;

        // checks if the next message may cause a loop or is broken
        if (ZPush::GetDeviceManager()->DoNotStreamMessage($id, $message)) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("ImportChangesStream->ImportMessageChange('%s'): message ignored and requested to be removed from mobile", $id));

            // this is an internal operation & should not trigger an update in the device manager
            $this->checkForIgnoredMessages = false;
            $stat = $this->ImportMessageDeletion($id);
            $this->checkForIgnoredMessages = true;

            return $stat;
        }

        // KOE ZO-3: Stream reply/forward flag and time as additional category to KOE
        if (ZPush::GetDeviceManager()->IsKoe() && KOE_CAPABILITY_RECEIVEFLAGS && isset($message->lastverbexectime) && isset($message->lastverbexecuted) && $message->lastverbexecuted > 0) {
            ZLog::Write(LOGLEVEL_DEBUG, "ImportChangesStream->ImportMessageChange('%s'): KOE detected. Adding LastVerb information as category.");
            if (!isset($message->categories)){
                $message->categories = array();
            }

            $s = "Push: Email ";
            if     ($message->lastverbexecuted == 1) $s .= "replied";
            elseif ($message->lastverbexecuted == 2) $s .= "replied-to-all";
            elseif ($message->lastverbexecuted == 3) $s .= "forwarded";
            $s .= " on " . gmdate("d-m-Y H:i:s", $message->lastverbexectime) . " GMT";

            $message->categories[] = $s;
        }

        if ($message->flags === false || $message->flags === SYNC_NEWMESSAGE)
            $this->encoder->startTag(SYNC_ADD);
        else {
            // on update of an SyncEmail we only export the flags and categories
            if($message instanceof SyncMail && ((isset($message->flag) && $message->flag instanceof SyncMailFlags) || isset($message->categories))) {
                $newmessage = new SyncMail();
                $newmessage->read = $message->read;
                if (isset($message->flag))              $newmessage->flag = $message->flag;
                if (isset($message->lastverbexectime))  $newmessage->lastverbexectime = $message->lastverbexectime;
                if (isset($message->lastverbexecuted))  $newmessage->lastverbexecuted = $message->lastverbexecuted;
                if (isset($message->categories))        $newmessage->categories = $message->categories;
                $message = $newmessage;
                unset($newmessage);
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("ImportChangesStream->ImportMessageChange('%s'): SyncMail message updated. Message content is striped, only flags/categories are streamed.", $id));
            }

            $this->encoder->startTag(SYNC_MODIFY);
        }

        // TAG: SYNC_ADD / SYNC_MODIFY
            $this->encoder->startTag(SYNC_SERVERENTRYID);
                $this->encoder->content($id);
            $this->encoder->endTag();
            $this->encoder->startTag(SYNC_DATA);
                $message->Encode($this->encoder);
            $this->encoder->endTag();
        $this->encoder->endTag();

        return true;
    }

    /**
     * Imports a deletion.
     *
     * @param string        $id
     * @param boolean       $asSoftDelete   (opt) if true, the deletion is exported as "SoftDelete", else as "Remove" - default: false
     *
     * @access public
     * @return boolean
     */
    public function ImportMessageDeletion($id, $asSoftDelete = false) {
        if ($this->checkForIgnoredMessages) {
           ZPush::GetDeviceManager()->RemoveBrokenMessage($id);
        }

        $this->importedMsgs++;
        if ($asSoftDelete) {
            $this->encoder->startTag(SYNC_SOFTDELETE);
        }
        else {
            $this->encoder->startTag(SYNC_REMOVE);
        }
            $this->encoder->startTag(SYNC_SERVERENTRYID);
                $this->encoder->content($id);
            $this->encoder->endTag();
        $this->encoder->endTag();

        return true;
    }

    /**
     * Imports a change in 'read' flag
     * Can only be applied to SyncMail (Email) requests
     *
     * @param string        $id
     * @param int           $flags - read/unread
     *
     * @access public
     * @return boolean
     */
    public function ImportMessageReadFlag($id, $flags) {
        if(!($this->objclass instanceof SyncMail))
            return false;

        $this->importedMsgs++;

        $this->encoder->startTag(SYNC_MODIFY);
            $this->encoder->startTag(SYNC_SERVERENTRYID);
                $this->encoder->content($id);
            $this->encoder->endTag();
            $this->encoder->startTag(SYNC_DATA);
                $this->encoder->startTag(SYNC_POOMMAIL_READ);
                    $this->encoder->content($flags);
                $this->encoder->endTag();
            $this->encoder->endTag();
        $this->encoder->endTag();

        return true;
    }

    /**
     * ImportMessageMove is not implemented, as this operation can not be streamed to a WBXMLEncoder
     *
     * @param string        $id
     * @param int           $flags      read/unread
     *
     * @access public
     * @return boolean
     */
    public function ImportMessageMove($id, $newfolder) {
        return true;
    }

    /**
     * Imports a change on a folder
     *
     * @param object        $folder     SyncFolder
     *
     * @access public
     * @return boolean/SyncObject           status/object with the ath least the serverid of the folder set
     */
    public function ImportFolderChange($folder) {
        // checks if the next message may cause a loop or is broken
        if (ZPush::GetDeviceManager(false) && ZPush::GetDeviceManager()->DoNotStreamMessage($folder->serverid, $folder)) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("ImportChangesStream->ImportFolderChange('%s'): folder ignored as requested by DeviceManager.", $folder->serverid));
            return true;
        }

        // send a modify flag if the folder is already known on the device
        if (isset($folder->flags) && $folder->flags === SYNC_NEWMESSAGE)
            $this->encoder->startTag(SYNC_FOLDERHIERARCHY_ADD);
        else
            $this->encoder->startTag(SYNC_FOLDERHIERARCHY_UPDATE);

        $folder->Encode($this->encoder);
        $this->encoder->endTag();

        return true;
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
        $this->encoder->startTag(SYNC_FOLDERHIERARCHY_REMOVE);
            $this->encoder->startTag(SYNC_FOLDERHIERARCHY_SERVERENTRYID);
                $this->encoder->content($folder->serverid);
            $this->encoder->endTag();
        $this->encoder->endTag();

        return true;
    }

    /**
     * Returns the number of messages which were changed, deleted and had changed read status
     *
     * @access public
     * @return int
     */
    public function GetImportedMessages() {
        return $this->importedMsgs;
    }
}
