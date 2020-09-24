<?php
/***********************************************
* File      :   caldav.php
* Project   :   PHP-Push
* Descr     :   This backend is based on 'BackendDiff' and implements a CalDAV interface
*
* Created   :   29.03.2012
*
* Copyright 2012 - 2014 Jean-Louis Dupond
*
* Jean-Louis Dupond released this code as AGPLv3 here: https://github.com/dupondje/PHP-Push-2/issues/93
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

// config file
require_once("backend/caldav/config.php");

class BackendCalDAV extends BackendDiff {
    /**
     * @var CalDAVClient
     */
    private $_caldav;
    private $_caldav_path;
    private $_collection = array();

    private $changessinkinit;
    private $sinkdata;
    private $sinkmax;

    /**
     * Constructor
     */
    public function __construct() {
        if (!function_exists("curl_init")) {
            throw new FatalException("BackendCalDAV(): php-curl is not found", 0, null, LOGLEVEL_FATAL);
        }

        $this->changessinkinit = false;
        $this->sinkdata = array();
        $this->sinkmax = array();
    }

    /**
     * Login to the CalDAV backend
     * @see IBackend::Logon()
     */
    public function Logon($username, $domain, $password) {
        $this->_caldav_path = str_replace('%u', $username, CALDAV_PATH);
        $url = sprintf("%s://%s:%d%s", CALDAV_PROTOCOL, CALDAV_SERVER, CALDAV_PORT, $this->_caldav_path);
        $this->_caldav = new CalDAVClient($url, $username, $password);
        if ($connected = $this->_caldav->CheckConnection()) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->Logon(): User '%s' is authenticated on CalDAV '%s'", $username, $url));
            if ($domain == "") {
                $this->originalUsername = $username;
            }
            else {
                $this->originalUsername = $username . '@' . $domain;
            }
        }
        else {
            ZLog::Write(LOGLEVEL_WARN, sprintf("BackendCalDAV->Logon(): User '%s' is not authenticated on CalDAV '%s'", $username, $url));
        }

        return $connected;
    }

    /**
     * The connections to CalDAV are always directly closed. So nothing special needs to happen here.
     * @see IBackend::Logoff()
     */
    public function Logoff() {
        if ($this->_caldav != null) {
            $this->_caldav->Disconnect();
            unset($this->_caldav);
        }

        $this->SaveStorages();

        unset($this->sinkdata);
        unset($this->sinkmax);

        ZLog::Write(LOGLEVEL_DEBUG, "BackendCalDAV->Logoff(): disconnected from CALDAV server");

        return true;
    }

    /**
     * CalDAV doesn't need to handle SendMail
     * @see IBackend::SendMail()
     */
    public function SendMail($sm) {
        return false;
    }

    /**
     * No attachments in CalDAV
     * @see IBackend::GetAttachmentData()
     */
    public function GetAttachmentData($attname) {
        return false;
    }

    /**
     * Deletes are always permanent deletes. Messages doesn't get moved.
     * @see IBackend::GetWasteBasket()
     */
    public function GetWasteBasket() {
        return false;
    }

    /**
     * Get a list of all the folders we are going to sync.
     * Each caldav calendar can contain tasks (prefix T) and events (prefix C), so duplicate each calendar found.
     * @see BackendDiff::GetFolderList()
     */
    public function GetFolderList() {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->GetFolderList(): Getting all folders."));
        $folders = array();
        $calendars = $this->_caldav->FindCalendars();
        foreach ($calendars as $val) {
            $folder = array();
            $fpath = explode("/", $val->url, -1);
            if (is_array($fpath)) {
                $folderid = array_pop($fpath);
                $id = "C" . $folderid;
                $folders[] = $this->StatFolder($id);
                $id = "T" . $folderid;
                $folders[] = $this->StatFolder($id);
            }
        }
        return $folders;
    }

    /**
     * Returning a SyncFolder
     * @see BackendDiff::GetFolder()
     */
    public function GetFolder($id) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->GetFolder('%s')", $id));
        $val = $this->_caldav->GetCalendarDetails($this->_caldav_path . substr($id, 1) .  "/");
        $folder = new SyncFolder();
        $folder->parentid = "0";
        $folder->displayname = $val->displayname;
        $folder->serverid = $id;
        if ($id[0] == "C") {
            if (defined('CALDAV_PERSONAL') && strcasecmp(substr($id, 1), CALDAV_PERSONAL) == 0) {
                $folder->type = SYNC_FOLDER_TYPE_APPOINTMENT;
            }
            else {
                $folder->type = SYNC_FOLDER_TYPE_USER_APPOINTMENT;
            }
        }
        else {
            if (defined('CALDAV_PERSONAL') && strcasecmp(substr($id, 1), CALDAV_PERSONAL) == 0) {
                $folder->type = SYNC_FOLDER_TYPE_TASK;
            }
            else {
                $folder->type = SYNC_FOLDER_TYPE_USER_TASK;
            }
        }
        return $folder;
    }

    /**
     * Returns information on the folder.
     * @see BackendDiff::StatFolder()
     */
    public function StatFolder($id) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->StatFolder('%s')", $id));
        $val = $this->GetFolder($id);
        $folder = array();
        $folder["id"] = $id;
        $folder["parent"] = $val->parentid;
        $folder["mod"] = $val->serverid;
        return $folder;
    }

    /**
     * ChangeFolder is not supported under CalDAV
     * @see BackendDiff::ChangeFolder()
     */
    public function ChangeFolder($folderid, $oldid, $displayname, $type) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->ChangeFolder('%s','%s','%s','%s')", $folderid, $oldid, $displayname, $type));
        return false;
    }

    /**
     * DeleteFolder is not supported under CalDAV
     * @see BackendDiff::DeleteFolder()
     */
    public function DeleteFolder($id, $parentid) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->DeleteFolder('%s','%s')", $id, $parentid));
        return false;
    }

    /**
     * Get a list of all the messages.
     * @see BackendDiff::GetMessageList()
     */
    public function GetMessageList($folderid, $cutoffdate) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->GetMessageList('%s','%s')", $folderid, $cutoffdate));

        /* Calculating the range of events we want to sync */
        $begin = gmdate("Ymd\THis\Z", $cutoffdate);
        $finish = gmdate("Ymd\THis\Z", CALDAV_MAX_SYNC_PERIOD);

        $path = $this->_caldav_path . substr($folderid, 1) . "/";
        if ($folderid[0] == "C") {
            $msgs = $this->_caldav->GetEvents($begin, $finish, $path);
        }
        else {
            $msgs = $this->_caldav->GetTodos($begin, $finish, false, false, $path);
        }

        $messages = array();
        foreach ($msgs as $e) {
            $id = $e['href'];
            $this->_collection[$id] = $e;
            $messages[] = $this->StatMessage($folderid, $id);
        }
        return $messages;
    }

    /**
     * Get a SyncObject by its ID
     * @see BackendDiff::GetMessage()
     */
    public function GetMessage($folderid, $id, $contentparameters) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->GetMessage('%s','%s')", $folderid,  $id));
        $data = $this->_collection[$id]['data'];

        if ($folderid[0] == "C") {
            return $this->_ParseVEventToAS($data, $contentparameters);
        }
        if ($folderid[0] == "T") {
            return $this->_ParseVTodoToAS($data, $contentparameters);
        }
        return false;
    }

    /**
     * Return id, flags and mod of a messageid
     * @see BackendDiff::StatMessage()
     */
    public function StatMessage($folderid, $id) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->StatMessage('%s','%s')", $folderid,  $id));
        $type = "VEVENT";
        if ($folderid[0] == "T") {
            $type = "VTODO";
        }
        $data = null;
        if (array_key_exists($id, $this->_collection)) {
            $data = $this->_collection[$id];
        }
        else {
            $path = $this->_caldav_path . substr($folderid, 1) . "/";
            $e = $this->_caldav->GetEntryByUid(substr($id, 0, strlen($id)-4), $path, $type);
            if ($e == null && count($e) <= 0)
                return;
            $data = $e[0];
        }
        $message = array();
        $message['id'] = $data['href'];
        $message['flags'] = "1";
        $message['mod'] = $data['etag'];
        return $message;
    }

    /**
     * Change/Add a message with contents received from ActiveSync
     * @see BackendDiff::ChangeMessage()
     */
    public function ChangeMessage($folderid, $id, $message, $contentParameters) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->ChangeMessage('%s','%s')", $folderid,  $id));

        if ($id) {
            $mod = $this->StatMessage($folderid, $id);
            $etag = $mod['mod'];
        }
        else {
            $etag = "*";
            $id = sprintf("%s-%s.ics", gmdate("Ymd\THis\Z"), hash("md5", microtime()));
        }

        $url = $this->_caldav_path . substr($folderid, 1) . "/" . $id;

        $data = $this->_ParseASToVCalendar($message, $folderid, substr($id, 0, strlen($id) - 4));

        $etag_new = $this->CreateUpdateCalendar($data, $url, $etag);

        $item = array();
        $item['href'] = $id;
        $item['etag'] = $etag_new;
        $item['data'] = $data;
        $this->_collection[$id] = $item;

        return $this->StatMessage($folderid, $id);
    }

    /**
     * Change the read flag is not supported.
     * @see BackendDiff::SetReadFlag()
     */
    public function SetReadFlag($folderid, $id, $flags, $contentParameters) {
        return false;
    }

    /**
     * Delete a message from the CalDAV server.
     * @see BackendDiff::DeleteMessage()
     */
    public function DeleteMessage($folderid, $id, $contentParameters) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->DeleteMessage('%s','%s')", $folderid,  $id));
        $url = $this->_caldav_path . substr($folderid, 1) . "/" . $id;
        $http_status_code = $this->_caldav->DoDELETERequest($url);
        return $http_status_code == "204";
    }

    /**
     * Move a message is not supported by CalDAV.
     * @see BackendDiff::MoveMessage()
     */
    public function MoveMessage($folderid, $id, $newfolderid, $contentParameters) {
        return false;
    }

    /**
     * Create or Update one event
     *
     * @access public
     * @param $data     string      VCALENDAR text
     * @param $url      string      URL for the calendar, if false a new calendar object is created
     * @param $etag     string      ETAG for the calendar, if '*' is a new object
     * @return array
     */
    public function CreateUpdateCalendar($data, $url = false, $etag = "*") {
        if ($url === false) {
            $url = sprintf("%s%s/%s-%s.ics", $this->_caldav_path, CALDAV_PERSONAL, gmdate("Ymd\THis\Z"), hash("md5", microtime()));
            $etag = "*";
        }

        return $this->_caldav->DoPUTRequest($url, $data, $etag);
    }

    /**
     * Deletes one VCALENDAR
     *
     * @access public
     * @param $id       string      ID of the VCALENDAR
     * @return boolean
     */
    public function DeleteCalendar($id) {
        $http_status_code = $this->_caldav->DoDELETERequest(sprintf("%s%s/%s", $this->_caldav_path, CALDAV_PERSONAL, $id));
        return $http_status_code == "204";
    }

    /**
     * Finds one VCALENDAR
     *
     * @access public
     * @param $uid      string      UID attribute
     * @return array
     */
    public function FindCalendar($uid) {
        $filter = sprintf("<C:filter><C:comp-filter name=\"VCALENDAR\"><C:comp-filter name=\"VEVENT\"><C:prop-filter name=\"UID\"><C:text-match>%s</C:text-match></C:prop-filter></C:comp-filter></C:comp-filter></C:filter>", $uid);

        $events = $this->_caldav->DoCalendarQuery($filter, sprintf("%s%s", $this->_caldav_path, CALDAV_PERSONAL));

        return $events;
    }

    /**
     * Resolves recipients
     *
     * @param SyncObject        $resolveRecipients
     *
     * @access public
     * @return SyncObject       $resolveRecipients
     */
    public function ResolveRecipients($resolveRecipients) {
        // TODO:
        return false;
    }

    /**
     * Indicates which AS version is supported by the backend.
     *
     * @access public
     * @return string       AS version constant
     */
    public function GetSupportedASVersion() {
        return ZPush::ASV_14;
    }

    /**
     * Indicates if the backend has a ChangesSink.
     * A sink is an active notification mechanism which does not need polling.
     * The CalDAV backend simulates a sink by polling revision dates from the events or use the native sync-collection.
     *
     * @access public
     * @return boolean
     */
    public function HasChangesSink() {
        return true;
    }

    /**
     * The folder should be considered by the sink.
     * Folders which were not initialized should not result in a notification
     * of IBackend->ChangesSink().
     *
     * @param string        $folderid
     *
     * @access public
     * @return boolean      false if found can not be found
     */
    public function ChangesSinkInitialize($folderid) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->ChangesSinkInitialize(): folderid '%s'", $folderid));

        // We don't need the actual events, we only need to get the changes since this moment
        $init_ok = true;
        $url = $this->_caldav_path . substr($folderid, 1) . "/";
        $this->sinkdata[$folderid] = $this->_caldav->GetSync($url, true, CALDAV_SUPPORTS_SYNC);
        if (CALDAV_SUPPORTS_SYNC) {
            // we don't need to store the sinkdata if the caldav server supports native sync
            unset($this->sinkdata[$url]);
            $this->sinkdata[$folderid] = array();
        }

        $this->changessinkinit = $init_ok;
        $this->sinkmax = array();

        return $this->changessinkinit;
    }

    /**
     * The actual ChangesSink.
     * For max. the $timeout value this method should block and if no changes
     * are available return an empty array.
     * If changes are available a list of folderids is expected.
     *
     * @param int           $timeout        max. amount of seconds to block
     *
     * @access public
     * @return array
     */
    public function ChangesSink($timeout = 30) {
        $notifications = array();
        $stopat = time() + $timeout - 1;

        //We can get here and the ChangesSink not be initialized yet
        if (!$this->changessinkinit) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->ChangesSink - Not initialized ChangesSink, sleep and exit"));
            // We sleep and do nothing else
            sleep($timeout);
            return $notifications;
        }

        // only check once to reduce pressure in the DAV server
        foreach ($this->sinkdata as $k => $v) {
            $changed = false;

            $url = $this->_caldav_path . substr($k, 1) . "/";
            $response = $this->_caldav->GetSync($url, false, CALDAV_SUPPORTS_SYNC);

            if (CALDAV_SUPPORTS_SYNC) {
                if (count($response) > 0) {
                    $changed = true;
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->ChangesSink - Changes detected"));
                }
            }
            else {
                // If the numbers of events are different, we know for sure, there are changes
                if (count($response) != count($v)) {
                    $changed = true;
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->ChangesSink - Changes detected"));
                }
                else {
                    // If the numbers of events are equals, we compare the biggest date
                    // FIXME: we are comparing strings no dates
                    if (!isset($this->sinkmax[$k])) {
                        $this->sinkmax[$k] = '';
                        for ($i = 0; $i < count($v); $i++) {
                            if ($v[$i]['getlastmodified'] > $this->sinkmax[$k]) {
                                $this->sinkmax[$k] = $v[$i]['getlastmodified'];
                            }
                        }
                    }

                    for ($i = 0; $i < count($response); $i++) {
                        if ($response[$i]['getlastmodified'] > $this->sinkmax[$k]) {
                            $changed = true;
                        }
                    }

                    if ($changed) {
                        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->ChangesSink - Changes detected"));
                    }
                }
            }

            if ($changed) {
                $notifications[] = $k;
            }
        }

        // Wait to timeout
        if (empty($notifications)) {
            while ($stopat > time()) {
                sleep(1);
            }
        }

        return $notifications;
    }


    /**
     * Convert a iCAL VEvent to ActiveSync format
     * @param ical_vevent $data
     * @param ContentParameters $contentparameters
     * @return SyncAppointment
     */
    private function _ParseVEventToAS($data, $contentparameters) {
        ZLog::Write(LOGLEVEL_DEBUG, "BackendCalDAV->_ParseVEventToAS(): Parsing VEvent");

        $truncsize = Utils::GetTruncSize($contentparameters->GetTruncation());
        $message = new SyncAppointment();

        $ical = new iCalComponent($data);

        $vevents = $ical->GetComponents("VEVENT");
        // First check the main vevent to determine time zone and all-day
        $message->alldayevent = "0";
        $message->timezone = date_default_timezone_get();
        foreach ($vevents as $event) {
            if (count($event->GetProperties("RECURRENCE-ID")) > 0) {
                continue;
            }
            if (strlen($event->GetPValue("DTSTART")) == 8
                || strlen($event->GetPValue("DTEND")) == 8
                || $event->GetPValue("X-MICROSOFT-CDO-ALLDAYEVENT") == "TRUE"
            ) {
                $message->alldayevent = "1";
            }
            $message->timezone = TimezoneUtil::GetPhpSupportedTimezone($event->GetPParamValue("DTSTART", "TZID"));
            break;
        }
        // Now process all vevents
        foreach ($vevents as $event) {
            $rec = $event->GetProperties("RECURRENCE-ID");
            if (count($rec) > 0) {
                $recurrence_id = reset($rec);
                $exception = new SyncAppointmentException();
                $exception = $this->_ParseVEventToSyncObject($event, $exception, $truncsize);

                if ($recurrence_id->GetParameterValue("TZID")){
                    $rec_tzid = $recurrence_id->GetParameterValue("TZID");
                }
                else {
                    $rec_tzid = $message->timezone;
                }

                $exception->exceptionstarttime = TimezoneUtil::MakeUTCDate($recurrence_id->Value(), $rec_tzid);
                $exception->deleted = "0";
                if (!isset($message->exceptions)) {
                    $message->exceptions = array();
                }
                $message->exceptions[] = $exception;
            }
            else {
                $message = $this->_ParseVEventToSyncObject($event, $message, $truncsize);
            }
        }
        $message->timezone = $this->_GetTimezoneString($message->timezone);
        return $message;
    }

    /**
     * Parse 1 VEvent
     * @param ical_vevent $event
     * @param SyncAppointment(Exception) $message
     * @param int $truncsize
     */
    private function _ParseVEventToSyncObject($event, $message, $truncsize) {
        //Defaults
        $message->busystatus = "2";

        $properties = $event->GetProperties();
        foreach ($properties as $property) {
            switch ($property->Name()) {
                case "LAST-MODIFIED":
                    $message->dtstamp = TimezoneUtil::MakeUTCDate($property->Value());
                    break;

                case "DTSTART":
                    $message->starttime = TimezoneUtil::MakeUTCDate($property->Value(), TimezoneUtil::GetPhpSupportedTimezone($property->GetParameterValue("TZID")));
                    break;

                case "SUMMARY":
                    $message->subject = $property->Value();
                    break;

                case "UID":
                    $message->uid = $property->Value();
                    break;

                case "ORGANIZER":
                    $org_mail = str_ireplace("MAILTO:", "", $property->Value());
                    $message->organizeremail = $org_mail;
                    $org_cn = $property->GetParameterValue("CN");
                    if ($org_cn) {
                        $message->organizername = $org_cn;
                    }
                    break;

                case "LOCATION":
                    $message->location = $property->Value();
                    break;

                case "DTEND":
                    $message->endtime = TimezoneUtil::MakeUTCDate($property->Value(), TimezoneUtil::GetPhpSupportedTimezone($property->GetParameterValue("TZID")));
                    break;

                case "DURATION":
                    if (!isset($message->endtime)) {
                        $start = date_create("@" . $message->starttime);
                        $val = str_replace("+", "", $property->Value());
                        $interval = new DateInterval($val);
                        $message->endtime = date_timestamp_get(date_add($start, $interval));
                    }
                break;

                case "RRULE":
                    $message->recurrence = $this->_ParseRecurrence($property->Value(), "vevent");
                    break;

                case "CLASS":
                    switch ($property->Value()) {
                        case "PUBLIC":
                            $message->sensitivity = "0";
                            break;
                        case "PRIVATE":
                            $message->sensitivity = "2";
                            break;
                        case "CONFIDENTIAL":
                            $message->sensitivity = "3";
                            break;
                    }
                    break;

                case "TRANSP":
                    if(!isset($message->busystatus)){
                        switch ($property->Value()) {
                            case "TRANSPARENT":
                                $message->busystatus = "0";
                                break;
                            case "OPAQUE":
                                $message->busystatus = "2";
                                break;
                        }
                    }
                    break;

                case "X-MICROSOFT-CDO-INTENDEDSTATUS":
                    switch ($property->Value()) {
                        case "FREE":
                            $message->busystatus = "0";
                            break;
                        case "TENTATIVE":
                            $message->busystatus = "1";
                            break;
                        case "BUSY":
                            $message->busystatus = "2";
                            break;
                        case "OOF":
                            $message->busystatus = "3";
                            break;
                        case "WORKINGELSEWHERE":
                            $message->busystatus = "4";
                            break;
                    }
                    break;

                // SYNC_POOMCAL_MEETINGSTATUS
                // Meetingstatus values
                //  0 = is not a meeting
                //  1 = is a meeting
                //  3 = Meeting received
                //  5 = Meeting is canceled
                //  7 = Meeting is canceled and received
                //  9 = as 1
                // 11 = as 3
                // 13 = as 5
                // 15 = as 7
                case "STATUS":
                    switch ($property->Value()) {
                        case "TENTATIVE":
                            $message->meetingstatus = "3"; // was 1
                            break;
                        case "CONFIRMED":
                            $message->meetingstatus = "1"; // was 3
                            break;
                        case "CANCELLED":
                            $message->meetingstatus = "5"; // could also be 7
                            break;
                    }
                    break;

                case "ATTENDEE":
                    $attendee = new SyncAttendee();
                    $att_email = str_ireplace("MAILTO:", "", $property->Value());
                    $attendee->email = $att_email;
                    $att_cn = $property->GetParameterValue("CN");
                    if ($att_cn) {
                        $attendee->name = $att_cn;
                    }
                    if (isset($message->attendees) && is_array($message->attendees)) {
                        $message->attendees[] = $attendee;
                    }
                    else {
                        $message->attendees = array($attendee);
                    }
                    break;

                case "DESCRIPTION":
                    if (Request::GetProtocolVersion() >= 12.0) {
                        $message->asbody = new SyncBaseBody();
                        $data = str_replace("\n","\r\n", str_replace("\r","",Utils::ConvertHtmlToText($property->Value())));
                        // truncate body, if requested
                        if (strlen($data) > $truncsize) {
                            $message->asbody->truncated = 1;
                            $data = Utils::Utf8_truncate($data, $truncsize);
                        }
                        else {
                            $message->asbody->truncated = 0;
                        }
                        $message->asbody->data = StringStreamWrapper::Open($data);
                        $message->asbody->estimatedDataSize = strlen($data);
                        unset($data);
                        $message->nativebodytype = SYNC_BODYPREFERENCE_PLAIN;
                    }
                    else {
                        $body = $property->Value();
                        // truncate body, if requested
                        if(strlen($body) > $truncsize) {
                            $body = Utils::Utf8_truncate($body, $truncsize);
                            $message->bodytruncated = 1;
                        } else {
                            $message->bodytruncated = 0;
                        }
                        $body = str_replace("\n","\r\n", str_replace("\r","",$body));
                        $message->body = $body;
                    }
                    break;

                case "CATEGORIES":
                    $categories = explode(",", $property->Value());
                    $message->categories = $categories;
                    break;

                case "EXDATE":
                    $exception = new SyncAppointmentException();
                    $exception->deleted = "1";
                    $exception->exceptionstarttime = TimezoneUtil::MakeUTCDate($property->Value());
                    if (!isset($message->exceptions)) {
                        $message->exceptions = array();
                    }
                    $message->exceptions[] = $exception;
                    break;

                //We can ignore the following
                case "PRIORITY":
                case "SEQUENCE":
                case "CREATED":
                case "DTSTAMP":
                case "X-MOZ-GENERATION":
                case "X-MOZ-LASTACK":
                case "X-LIC-ERROR":
                case "RECURRENCE-ID":
                    break;

                default:
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->_ParseVEventToSyncObject(): '%s' is not yet supported.", $property->Name()));
            }
        }

        if ($message->meetingstatus > 0) {
            // No organizer was set for the meeting, assume it is the user
            if (!isset($message->organizeremail)) {
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->_ParseVEventToSyncObject(): No organizeremail defined, using user details"));
                $userDetails = ZPush::GetBackend()->GetCurrentUsername();
                $message->organizeremail = $userDetails['emailaddress'];
                $message->organizername = $userDetails['fullname'];
            }
            // Ensure the organizer name is set
            if (!isset($message->organizername)) {
                $message->organizername = Utils::GetLocalPartFromEmail($message->organizeremail);
            }
        }

        $valarm = current($event->GetComponents("VALARM"));
        if ($valarm) {
            $properties = $valarm->GetProperties();
            foreach ($properties as $property) {
                if ($property->Name() == "TRIGGER") {
                    $parameters = $property->Parameters();
                    if (array_key_exists("VALUE", $parameters) && $parameters["VALUE"] == "DATE-TIME") {
                        $trigger = date_create("@" . TimezoneUtil::MakeUTCDate($property->Value()));
                        $begin = date_create("@" . $message->starttime);
                        $interval = date_diff($begin, $trigger);
                        $message->reminder = $interval->format("%i") + $interval->format("%h") * 60 + $interval->format("%a") * 60 * 24;
                    }
                    elseif (!array_key_exists("VALUE", $parameters) || $parameters["VALUE"] == "DURATION") {
                        $val = str_replace("-", "", $property->Value());
                        $interval = new DateInterval($val);
                        $message->reminder = $interval->format("%i") + $interval->format("%h") * 60 + $interval->format("%a") * 60 * 24;
                    }
                }
            }
        }

        return $message;
    }

    /**
     * Parse a RRULE
     * @param string $rrulestr
     */
    private function _ParseRecurrence($rrulestr, $type) {
        $recurrence = new SyncRecurrence();
        if ($type == "vtodo") {
            $recurrence = new SyncTaskRecurrence();
        }
        $rrules = explode(";", $rrulestr);
        foreach ($rrules as $rrule) {
            $rule = explode("=", $rrule);
            switch ($rule[0]) {
                case "FREQ":
                    switch ($rule[1]) {
                        case "DAILY":
                            $recurrence->type = "0";
                            break;
                        case "WEEKLY":
                            $recurrence->type = "1";
                            break;
                        case "MONTHLY":
                            $recurrence->type = "2";
                            break;
                        case "YEARLY":
                            $recurrence->type = "5";
                    }
                    break;

                case "UNTIL":
                    $recurrence->until = TimezoneUtil::MakeUTCDate($rule[1]);
                    break;

                case "COUNT":
                    $recurrence->occurrences = $rule[1];
                    break;

                case "INTERVAL":
                    $recurrence->interval = $rule[1];
                    break;

                case "BYDAY":
                    $dval = 0;
                    $days = explode(",", $rule[1]);
                    foreach ($days as $day) {
                        if ($recurrence->type == "2") {
                            if (strlen($day) > 2) {
                                $recurrence->weekofmonth = intval($day);
                                $day = substr($day,-2);
                            }
                            else {
                                $recurrence->weekofmonth = 1;
                            }
                            $recurrence->type = "3";
                        }
                        switch ($day) {
                            //   1 = Sunday
                            //   2 = Monday
                            //   4 = Tuesday
                            //   8 = Wednesday
                            //  16 = Thursday
                            //  32 = Friday
                            //  62 = Weekdays  // not in spec: daily weekday recurrence
                            //  64 = Saturday
                            case "SU":
                                $dval += 1;
                                break;
                            case "MO":
                                $dval += 2;
                                break;
                            case "TU":
                                $dval += 4;
                                break;
                            case "WE":
                                $dval += 8;
                                break;
                            case "TH":
                                $dval += 16;
                                break;
                            case "FR":
                                $dval += 32;
                                break;
                            case "SA":
                                $dval += 64;
                                break;
                        }
                    }
                    $recurrence->dayofweek = $dval;
                    break;

                    //Only 1 BYMONTHDAY is supported, so BYMONTHDAY=2,3 will only include 2
                case "BYMONTHDAY":
                    $days = explode(",", $rule[1]);
                    $recurrence->dayofmonth = $days[0];
                    break;

                case "BYMONTH":
                    $recurrence->monthofyear = $rule[1];
                    break;

                default:
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->_ParseRecurrence(): '%s' is not yet supported.", $rule[0]));
            }
        }
        return $recurrence;
    }

    /**
     * Generate a iCAL VCalendar from ActiveSync object.
     * @param string $data
     * @param string $folderid
     * @param string $id
     */
    private function _ParseASToVCalendar($data, $folderid, $id) {
        $ical = new iCalComponent();
        $ical->SetType("VCALENDAR");
        $ical->AddProperty("VERSION", "2.0");
        $ical->AddProperty("PRODID", "-//z-push-contrib//NONSGML Z-Push-contrib Calendar//EN");
        $ical->AddProperty("CALSCALE", "GREGORIAN");

        if (isset($data->timezone) && isset($data->starttime) && ($tzid = $this->tzidFromMSTZ($data->timezone, $data->starttime))) {
            $tzpar = array("TZID" => $tzid);

            // Add timezone
            $vtimezone = new iCalComponent();
            $vtimezone->SetType("VTIMEZONE");
            $vtimezone->AddProperty("TZID", $tzid);
            $timezone = new DateTimeZone($tzid);
            $year = date("Y", $data->starttime);
            $transitions = $timezone->getTransitions(date("U", strtotime($year."0101T000000Z")), date("U", strtotime($year."1231T235959Z")));

            $offset_from = self::phpOffsetToIcalOffset($transitions[0]['offset']);
            for ($i=0; $i<count($transitions); $i++) {
                $offset_to = self::phpOffsetToIcalOffset($transitions[$i]['offset']);
                if ($i == 0) {
                    $offset_from = $offset_to;
                    if (count($transitions) > 1) {
                        continue;
                    }
                }
                $vtransition = new iCalComponent();
                $vtransition->AddProperty("TZOFFSETFROM", $offset_from);
                $vtransition->AddProperty("TZOFFSETTO", $offset_to);
                $offset_from = $offset_to;

                $type = $transitions[$i]['isdst'] == 1 ? "DAYLIGHT" : "STANDARD";
                $vtransition->SetType($type);
                $vtransition->AddProperty("TZNAME", $transitions[$i]['abbr']);
                $vtransition->AddProperty("DTSTART", date("Ymd\THis", $transitions[$i]['ts']));
                $vtimezone->AddComponent($vtransition);
            }
            $ical->AddComponent($vtimezone);
        }
        else {
            $tzid = 'Z';
            $tzpar = null;
        }

        if ($folderid[0] == "C") {
            $vevent = $this->_ParseASEventToVEvent($data, $id, $tzid, $tzpar);
            $vevent->AddProperty("UID", $id);
            $ical->AddComponent($vevent);
            if (isset($data->exceptions) && is_array($data->exceptions)) {
                foreach ($data->exceptions as $ex) {
                    if (isset($ex->deleted) && $ex->deleted == "1") {
                        if ($exdate = $vevent->GetPValue("EXDATE")) {
                            if ($data->alldayevent == 1) {
                                $vevent->SetPValue("EXDATE", $exdate.",".$this->_GetDateFromUTC("Ymd", $ex->exceptionstarttime, $data->timezone), array("VALUE" => "DATE"));
                            }
                            else {
                                $vevent->SetPValue("EXDATE", $exdate.",".$this->DAVDateTimeInTimezone($ex->exceptionstarttime, $tzid), $tzpar);
                            }
                        }
                        else {
                            if ($data->alldayevent == 1) {
                                $vevent->AddProperty("EXDATE", $this->_GetDateFromUTC("Ymd", $ex->exceptionstarttime, $data->timezone), array("VALUE" => "DATE"));
                            }
                            else {
                                $vevent->AddProperty("EXDATE", $this->DAVDateTimeInTimezone($ex->exceptionstarttime, $tzid), $tzpar);
                            }
                        }
                        continue;
                    }

                    $exception = $this->_ParseASEventToVEvent($ex, $id, $tzid, $tzpar);
                    if ($data->alldayevent == 1) {
                        $exception->AddProperty("RECURRENCE-ID", $this->_GetDateFromUTC("Ymd", $ex->exceptionstarttime, $data->timezone), array("VALUE" => "DATE"));
                    }
                    else {
                        $exception->AddProperty("RECURRENCE-ID", $this->DAVDateTimeInTimezone($ex->exceptionstarttime, $tzid), $tzpar);
                    }
                    $exception->AddProperty("UID", $id);
                    $ical->AddComponent($exception);
                }
            }
        }
        if ($folderid[0] == "T") {
            $vtodo = $this->_ParseASTaskToVTodo($data, $id);
            $vtodo->AddProperty("UID", $id);
            $vtodo->AddProperty("DTSTAMP", gmdate("Ymd\THis\Z"));
            $ical->AddComponent($vtodo);
        }

        return $ical->Render();
    }

    /**
     * Generate a VEVENT from a SyncAppointment(Exception).
     * @param string $data
     * @param string $id
     * @param string $tzid
     * @param array $tzpar
     * @return iCalComponent
     */
    private function _ParseASEventToVEvent($data, $id, $tzid, $tzpar) {
        $vevent = new iCalComponent();
        $vevent->SetType("VEVENT");

        if (isset($data->dtstamp)) {
            $vevent->AddProperty("DTSTAMP", gmdate("Ymd\THis\Z", $data->dtstamp));
            $vevent->AddProperty("LAST-MODIFIED", gmdate("Ymd\THis\Z", $data->dtstamp));
        }
        if (isset($data->starttime)) {
            if ($data->alldayevent == 1) {
                $vevent->AddProperty("DTSTART", $this->_GetDateFromUTC("Ymd", $data->starttime, $data->timezone), array("VALUE" => "DATE"));
            }
            else {
                $vevent->AddProperty("DTSTART", $this->DAVDateTimeInTimezone($data->starttime, $tzid), $tzpar);
            }
        }
        if (isset($data->subject)) {
            $vevent->AddProperty("SUMMARY", $data->subject);
        }
        if (isset($data->location)) {
            $vevent->AddProperty("LOCATION", $data->location);
        }
        if (isset($data->endtime)) {
            if ($data->alldayevent == 1) {
                $vevent->AddProperty("DTEND", $this->_GetDateFromUTC("Ymd", $data->endtime, $data->timezone), array("VALUE" => "DATE"));
                $vevent->AddProperty("X-MICROSOFT-CDO-ALLDAYEVENT", "TRUE");
            }
            else {
                $vevent->AddProperty("DTEND", $this->DAVDateTimeInTimezone($data->endtime, $tzid), $tzpar);
                $vevent->AddProperty("X-MICROSOFT-CDO-ALLDAYEVENT", "FALSE");
            }
        }
        else {
            $vevent->AddProperty("X-MICROSOFT-CDO-ALLDAYEVENT", "TRUE");
        }
        if (isset($data->recurrence)) {
            $vevent->AddProperty("RRULE", $this->_GenerateRecurrence($data->recurrence, $data->alldayevent == 1));
        }
        if (isset($data->sensitivity)) {
            switch ($data->sensitivity) {
                case "0":
                    $vevent->AddProperty("CLASS", "PUBLIC");
                    break;
                case "2":
                    $vevent->AddProperty("CLASS", "PRIVATE");
                    break;
                case "3":
                    $vevent->AddProperty("CLASS", "CONFIDENTIAL");
                    break;
            }
        }
        if (isset($data->busystatus)) {
            switch ($data->busystatus) {
                case "0": //Free
                    $vevent->AddProperty("TRANSP", "TRANSPARENT");
                    $vevent->AddProperty("X-MICROSOFT-CDO-INTENDEDSTATUS", "FREE");
                    break;
                case "1": //Tentative
                    $vevent->AddProperty("TRANSP", "OPAQUE");
                    $vevent->AddProperty("X-MICROSOFT-CDO-INTENDEDSTATUS", "TENTATIVE");
                    break;
                case "2": //Busy
                    $vevent->AddProperty("TRANSP", "OPAQUE");
                    $vevent->AddProperty("X-MICROSOFT-CDO-INTENDEDSTATUS", "BUSY");
                    break;
                case "3": //Out of office
                    $vevent->AddProperty("TRANSP", "TRANSPARENT");
                    $vevent->AddProperty("X-MICROSOFT-CDO-INTENDEDSTATUS", "OOF");
                    break;
                case "4": //Working elsewhere (not yet in Android)
                    $vevent->AddProperty("TRANSP", "TRANSPARENT");
                    $vevent->AddProperty("X-MICROSOFT-CDO-INTENDEDSTATUS", "WORKINGELSEWHERE");
                    break;
            }
        }
        if (isset($data->reminder)) {
            $valarm = new iCalComponent();
            $valarm->SetType("VALARM");
            $valarm->AddProperty("ACTION", "DISPLAY");
            $trigger = "-PT" . $data->reminder . "M";
            $valarm->AddProperty("TRIGGER", $trigger);
            $vevent->AddComponent($valarm);
        }
        if (isset($data->rtf)) {
            $rtfparser = new z_RTF();
            $rtfparser->loadrtf(base64_decode($data->rtf));
            $rtfparser->output("ascii");
            $rtfparser->parse();
            $vevent->AddProperty("DESCRIPTION", $rtfparser->out);
        }
        if (isset($data->meetingstatus) && $data->meetingstatus > 0) {
            switch ($data->meetingstatus) {
                case "1":
                    $vevent->AddProperty("STATUS", "TENTATIVE");
                    $vevent->AddProperty("X-MICROSOFT-CDO-BUSYSTATUS", "TENTATIVE");
                    $vevent->AddProperty("X-MICROSOFT-DISALLOW-COUNTER", "FALSE");
                    break;
                case "3":
                    $vevent->AddProperty("STATUS", "CONFIRMED");
                    $vevent->AddProperty("X-MICROSOFT-CDO-BUSYSTATUS", "CONFIRMED");
                    $vevent->AddProperty("X-MICROSOFT-DISALLOW-COUNTER", "FALSE");
                    break;
                case "5":
                case "7":
                    $vevent->AddProperty("STATUS", "CANCELLED");
                    $vevent->AddProperty("X-MICROSOFT-CDO-BUSYSTATUS", "CANCELLED");
                    $vevent->AddProperty("X-MICROSOFT-DISALLOW-COUNTER", "TRUE");
                    break;
            }
            if (isset($data->organizeremail) && isset($data->organizername)) {
                $vevent->AddProperty("ORGANIZER", sprintf("MAILTO:%s", $data->organizeremail), array("CN" => $data->organizername));
            }
            elseif (isset($data->organizeremail)) {
                $vevent->AddProperty("ORGANIZER", sprintf("MAILTO:%s", $data->organizeremail));
            }
            else {
                //Some phones doesn't send the organizeremail, so we gotto get it somewhere else.
                $userDetails = ZPush::GetBackend()->GetCurrentUsername();
                $vevent->AddProperty("ORGANIZER", sprintf("MAILTO:%s", $userDetails['emailaddress']), array("CN" => $userDetails['fullname']));
            }
            if (isset($data->attendees) && is_array($data->attendees)) {
                foreach ($data->attendees as $att) {
                    if (isset($att->name)) {
                        $vevent->AddProperty("ATTENDEE", sprintf("MAILTO:%s", $att->email), array("CN" => $att->name));
                    }
                    else {
                        $vevent->AddProperty("ATTENDEE", sprintf("MAILTO:%s", $att->email));
                    }
                }
            }
        }
        if (isset($data->body) && strlen($data->body) > 0) {
            $vevent->AddProperty("DESCRIPTION", $data->body);
        }
        if (isset($data->asbody->data)) {
            $asbody = stream_get_contents($data->asbody->data);
            if (strlen($asbody) > 0) {
                $vevent->AddProperty("DESCRIPTION", $asbody);
            }
        }
        if (isset($data->categories) && is_array($data->categories)) {
            $vevent->AddProperty("CATEGORIES", implode(",", $data->categories));
        }

// X-MICROSOFT-CDO-APPT-SEQUENCE:0
// X-MICROSOFT-CDO-OWNERAPPTID:2113393086
// X-MICROSOFT-CDO-IMPORTANCE:1
// X-MICROSOFT-CDO-INSTTYPE:0


        return $vevent;
    }

    /**
     * Generate Recurrence
     * @param string $rec
     * @param boolean $allday
     */
    private function _GenerateRecurrence($rec, $allday) {
        $rrule = array();
        if (isset($rec->type)) {
            $freq = "";
            switch ($rec->type) {
                case "0":
                    $freq = "DAILY";
                    break;
                case "1":
                    $freq = "WEEKLY";
                    break;
                case "2":
                case "3":
                    $freq = "MONTHLY";
                    break;
                case "5":
                    $freq = "YEARLY";
                    break;
            }
            $rrule[] = "FREQ=" . $freq;
        }
        if (isset($rec->until)) {
            if ($allday) {
                $rrule[] = "UNTIL=" . gmdate("Ymd", $rec->until);
            }
            else {
                $rrule[] = "UNTIL=" . gmdate("Ymd\THis\Z", $rec->until);
            }
        }
        if (isset($rec->occurrences)) {
            $rrule[] = "COUNT=" . $rec->occurrences;
        }
        if (isset($rec->interval)) {
            $rrule[] = "INTERVAL=" . $rec->interval;
        }
        if (isset($rec->dayofweek)) {
            $week = '';
            if (isset($rec->weekofmonth)) {
                $week = $rec->weekofmonth;
            }
            $days = array();
            if (($rec->dayofweek & 1) == 1) {
                if (empty($week)) {
                    $days[] = "SU";
                }
                else {
                    $days[] = $week . "SU";
                }
            }
            if (($rec->dayofweek & 2) == 2) {
                if (empty($week)) {
                    $days[] = "MO";
                }
                else {
                    $days[] = $week . "MO";
                }
            }
            if (($rec->dayofweek & 4) == 4) {
                if (empty($week)) {
                    $days[] = "TU";
                }
                else {
                    $days[] = $week . "TU";
                }
            }
            if (($rec->dayofweek & 8) == 8) {
                if (empty($week)) {
                    $days[] = "WE";
                }
                else {
                    $days[] = $week . "WE";
                }
            }
            if (($rec->dayofweek & 16) == 16) {
                if (empty($week)) {
                    $days[] = "TH";
                }
                else {
                    $days[] = $week . "TH";
                }
            }
            if (($rec->dayofweek & 32) == 32) {
                if (empty($week)) {
                    $days[] = "FR";
                }
                else {
                    $days[] = $week . "FR";
                }
            }
            if (($rec->dayofweek & 64) == 64) {
                if (empty($week)) {
                    $days[] = "SA";
                }
                else {
                    $days[] = $week . "SA";
                }
            }
            $rrule[] = "BYDAY=" . implode(",", $days);
        }
        if (isset($rec->dayofmonth)) {
            $rrule[] = "BYMONTHDAY=" . $rec->dayofmonth;
        }
        if (isset($rec->monthofyear)) {
            $rrule[] = "BYMONTH=" . $rec->monthofyear;
        }
        return implode(";", $rrule);
    }

    /**
     * Convert a iCAL VTodo to ActiveSync format
     * @param string $data
     * @param ContentParameters $contentparameters
     */
    private function _ParseVTodoToAS($data, $contentparameters) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->_ParseVTodoToAS(): Parsing VTodo"));
        $truncsize = Utils::GetTruncSize($contentparameters->GetTruncation());

        $message = new SyncTask();
        $ical = new iCalComponent($data);

        $vtodos = $ical->GetComponents("VTODO");
        //Should only loop once
        foreach ($vtodos as $vtodo) {
            $message = $this->_ParseVTodoToSyncObject($vtodo, $message, $truncsize);
        }
        return $message;
    }

    /**
     * Parse 1 VEvent
     * @param ical_vtodo $vtodo
     * @param SyncAppointment(Exception) $message
     * @param int $truncsize
     */
    private function _ParseVTodoToSyncObject($vtodo, $message, $truncsize) {
        //Default
        $message->reminderset = "0";
        $message->importance = "1";
        $message->complete = "0";

        $properties = $vtodo->GetProperties();
        foreach ($properties as $property) {
            switch ($property->Name()) {
                case "SUMMARY":
                    $message->subject = $property->Value();
                    break;

                case "STATUS":
                    switch ($property->Value()) {
                        case "NEEDS-ACTION":
                        case "IN-PROCESS":
                            $message->complete = "0";
                            break;
                        case "COMPLETED":
                        case "CANCELLED":
                            $message->complete = "1";
                            break;
                    }
                    break;

                case "COMPLETED":
                    $message->datecompleted = TimezoneUtil::MakeUTCDate($property->Value());
                    break;

                case "DUE":
                    $message->utcduedate = TimezoneUtil::MakeUTCDate($property->Value());
                    break;

                case "PRIORITY":
                    $priority = $property->Value();
                    if ($priority <= 3)
                        $message->importance = "0";
                    if ($priority <= 6)
                        $message->importance = "1";
                    if ($priority > 6)
                        $message->importance = "2";
                    break;

                case "RRULE":
                    $message->recurrence = $this->_ParseRecurrence($property->Value(), "vtodo");
                    break;

                case "CLASS":
                    switch ($property->Value()) {
                        case "PUBLIC":
                            $message->sensitivity = "0";
                            break;
                        case "PRIVATE":
                            $message->sensitivity = "2";
                            break;
                        case "CONFIDENTIAL":
                            $message->sensitivity = "3";
                            break;
                    }
                    break;

                case "DTSTART":
                    $message->utcstartdate = TimezoneUtil::MakeUTCDate($property->Value());
                    break;

                case "SUMMARY":
                    $message->subject = $property->Value();
                    break;

                case "CATEGORIES":
                    $categories = explode(",", $property->Value());
                    $message->categories = $categories;
                    break;
            }
        }

        if (isset($message->recurrence)) {
            $message->recurrence->start = $message->utcstartdate;
        }

        $valarm = current($vtodo->GetComponents("VALARM"));
        if ($valarm) {
            $properties = $valarm->GetProperties();
            foreach ($properties as $property) {
                if ($property->Name() == "TRIGGER") {
                    $parameters = $property->Parameters();
                    if (array_key_exists("VALUE", $parameters) && $parameters["VALUE"] == "DATE-TIME") {
                        $message->remindertime = TimezoneUtil::MakeUTCDate($property->Value());
                        $message->reminderset = "1";
                    }
                    elseif (!array_key_exists("VALUE", $parameters) || $parameters["VALUE"] == "DURATION") {
                        $val = str_replace("-", "", $property->Value());
                        $interval = new DateInterval($val);
                        $start = date_create("@" . $message->utcstartdate);
                        $message->remindertime = date_timestamp_get(date_sub($start, $interval));
                        $message->reminderset = "1";
                    }
                }
            }
        }
        return $message;
    }

    /**
     * Generate a VTODO from a SyncAppointment(Exception)
     * @param string $data
     * @param string $id
     * @return iCalComponent
     */
    private function _ParseASTaskToVTodo($data, $id) {
        $vtodo = new iCalComponent();
        $vtodo->SetType("VTODO");

        if (isset($data->body)) {
            $vtodo->AddProperty("DESCRIPTION", $data->body);
        }
        if (isset($data->asbody->data)) {
            if (isset($data->nativebodytype) && $data->nativebodytype == SYNC_BODYPREFERENCE_RTF) {
                $rtfparser = new z_RTF();
                $rtfparser->loadrtf(base64_decode(stream_get_contents($data->asbody->data)));
                $rtfparser->output("ascii");
                $rtfparser->parse();
                $vtodo->AddProperty("DESCRIPTION", $rtfparser->out);
            }
            else {
                $vtodo->AddProperty("DESCRIPTION", stream_get_contents($data->asbody->data));
            }
        }
        if (isset($data->complete)) {
            if ($data->complete == "0") {
                $vtodo->AddProperty("STATUS", "NEEDS-ACTION");
            }
            else {
                $vtodo->AddProperty("STATUS", "COMPLETED");
            }
        }
        if (isset($data->datecompleted)) {
            $vtodo->AddProperty("COMPLETED", gmdate("Ymd\THis\Z", $data->datecompleted));
        }
        if ($data->utcduedate) {
            $vtodo->AddProperty("DUE", gmdate("Ymd\THis\Z", $data->utcduedate));
        }
        if (isset($data->importance)) {
            if ($data->importance == "1") {
                $vtodo->AddProperty("PRIORITY", 6);
            }
            elseif ($data->importance == "2") {
                $vtodo->AddProperty("PRIORITY", 9);
            }
            else {
                $vtodo->AddProperty("PRIORITY", 1);
            }
        }
        if (isset($data->recurrence)) {
            $vtodo->AddProperty("RRULE", $this->_GenerateRecurrence($data->recurrence, false));
        }
        if ($data->reminderset && $data->remindertime) {
            $valarm = new iCalComponent();
            $valarm->SetType("VALARM");
            $valarm->AddProperty("ACTION", "DISPLAY");
            $valarm->AddProperty("TRIGGER;VALUE=DATE-TIME", gmdate("Ymd\THis\Z", $data->remindertime));
            $vtodo->AddComponent($valarm);
        }
        if (isset($data->sensitivity)) {
            switch ($data->sensitivity) {
                case "0":
                    $vtodo->AddProperty("CLASS", "PUBLIC");
                    break;

                case "2":
                    $vtodo->AddProperty("CLASS", "PRIVATE");
                    break;

                case "3":
                    $vtodo->AddProperty("CLASS", "CONFIDENTIAL");
                    break;
            }
        }
        if (isset($data->utcstartdate)) {
            $vtodo->AddProperty("DTSTART", gmdate("Ymd\THis\Z", $data->utcstartdate));
        }
        if (isset($data->subject)) {
            $vtodo->AddProperty("SUMMARY", $data->subject);
        }
        if (isset($data->rtf)) {
            $rtfparser = new z_RTF();
            $rtfparser->loadrtf(base64_decode($data->rtf));
            $rtfparser->output("ascii");
            $rtfparser->parse();
            $vtodo->AddProperty("DESCRIPTION", $rtfparser->out);
        }
        if (isset($data->categories) && is_array($data->categories)) {
            $vtodo->AddProperty("CATEGORIES", implode(",", $data->categories));
        }

        return $vtodo;
    }

    private function _GetDateFromUTC($format, $date, $tz_str) {
        $timezone = $this->_GetTimezoneFromString($tz_str);
        $dt = date_create('@' . $date);
        date_timezone_set($dt, timezone_open($timezone));
        return date_format($dt, $format);
    }

    //This returns a timezone that matches the timezonestring.
    //We can't be sure this is the one you chose, as multiple timezones have same timezonestring
    private function _GetTimezoneFromString($tz_string) {
        //Get a list of all timezones
        $identifiers = DateTimeZone::listIdentifiers();
        //Try the default timezone first
        array_unshift($identifiers, date_default_timezone_get());
        foreach ($identifiers as $tz) {
            $str = $this->_GetTimezoneString($tz, false);
            if ($str == $tz_string) {
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->_GetTimezoneFromString(): Found timezone: '%s'.", $tz));
                return $tz;
            }
        }
        return date_default_timezone_get();
    }

    /**
     * Generate ActiveSync Timezone Packed String.
     * @param string $timezone
     * @param string $with_names
     * @throws Exception
     */
    private function _GetTimezoneString($timezone, $with_names = true) {
        // UTC needs special handling
        if ($timezone == "UTC")
            return base64_encode(pack('la64vvvvvvvvla64vvvvvvvvl', 0, '', 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, 0, 0, 0));
        try {
            //Generate a timezone string (PHP 5.3 needed for this)
            $timezone = new DateTimeZone($timezone);
            $trans = $timezone->getTransitions(date('U',strtotime(date('Y-01-01'))), date('U',strtotime(date('Y-12-31'))));
            $stdTime = null;
            $dstTime = null;
            if (count($trans) < 1) {
                throw new Exception();
            }
            if (count($trans) == 1) {
                $stdBias = $trans[0]['offset'] / -60;
                $stdName = $trans[0]['abbr'];
                $stdMonth = 0;
                $stdDay = 0;
                $stdWeek = 0;
                $stdHour = 0;
                $stdMinute = 0;
                $dstName = "";
                $dstMonth = 0;
                $dstDay = 0;
                $dstWeek = 0;
                $dstHour = 0;
                $dstMinute = 0;
                $dstBias = -60;
            }
            else {
                if ($trans[1]['isdst'] == 1) {
                    $dstTime = $trans[1];
                    $stdTime = $trans[2];
                }
                else {
                    $dstTime = $trans[2];
                    $stdTime = $trans[1];
                }
                $stdTimeO = new DateTime($stdTime['time']);
                $stdFirst = new DateTime(sprintf("first sun of %s %s", $stdTimeO->format('F'), $stdTimeO->format('Y')), timezone_open("UTC"));
                $stdBias = $stdTime['offset'] / -60;
                $stdName = $stdTime['abbr'];
                $stdYear = 0;
                $stdMonth = $stdTimeO->format('n');
                $stdWeek = floor(($stdTimeO->format("j")-$stdFirst->format("j"))/7)+1;
                $stdDay = $stdTimeO->format('w');
                $stdHour = $stdTimeO->format('H');
                $stdMinute = $stdTimeO->format('i');
                $stdTimeO->add(new DateInterval('P7D'));
                if ($stdTimeO->format('n') != $stdMonth) {
                    $stdWeek = 5;
                }
                $dstTimeO = new DateTime($dstTime['time']);
                $dstFirst = new DateTime(sprintf("first sun of %s %s", $dstTimeO->format('F'), $dstTimeO->format('Y')), timezone_open("UTC"));
                $dstName = $dstTime['abbr'];
                $dstYear = 0;
                $dstMonth = $dstTimeO->format('n');
                $dstWeek = floor(($dstTimeO->format("j")-$dstFirst->format("j"))/7)+1;
                $dstDay = $dstTimeO->format('w');
                $dstHour = $dstTimeO->format('H');
                $dstMinute = $dstTimeO->format('i');
                $dstTimeO->add(new DateInterval('P7D'));
                if ($dstTimeO->format('n') != $dstMonth) {
                    $dstWeek = 5;
                }
                $dstBias = ($dstTime['offset'] - $stdTime['offset']) / -60;
            }
            if ($with_names) {
                return base64_encode(pack('la64vvvvvvvvla64vvvvvvvvl', $stdBias, $stdName, 0, $stdMonth, $stdDay, $stdWeek, $stdHour, $stdMinute, 0, 0, 0, $dstName, 0, $dstMonth, $dstDay, $dstWeek, $dstHour, $dstMinute, 0, 0, $dstBias));
            }
            else {
                return base64_encode(pack('la64vvvvvvvvla64vvvvvvvvl', $stdBias, '', 0, $stdMonth, $stdDay, $stdWeek, $stdHour, $stdMinute, 0, 0, 0, '', 0, $dstMonth, $dstDay, $dstWeek, $dstHour, $dstMinute, 0, 0, $dstBias));
            }
        }
        catch (Exception $e) {
            // If invalid timezone is given, we return UTC
            return base64_encode(pack('la64vvvvvvvvla64vvvvvvvvl', 0, '', 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, 0, 0, 0));
        }
        return base64_encode(pack('la64vvvvvvvvla64vvvvvvvvl', 0, '', 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, 0, 0, 0));
    }

    private static $knownMSTZS = array(
        "-780/-60/0/0/0/0/0/0/0/0"=>"Pacific/Enderbury"
        ,"-720/-60/4/1/0/3/9/5/0/2"=>"Pacific/Auckland"
        ,"-660/-60/0/0/0/0/0/0/0/0"=>"Antarctica/Casey"
        ,"-600/-60/4/1/0/3/10/1/0/2"=>"Australia/Melbourne"
        ,"-600/-60/0/0/0/0/0/0/0/0"=>"Australia/Brisbane"
        ,"-570/-60/4/1/0/3/10/1/0/2"=>"Australia/Adelaide"
        ,"-570/-60/0/0/0/0/0/0/0/0"=>"Australia/Darwin"
        ,"-540/-60/0/0/0/0/0/0/0/0"=>"Asia/Chita"
        ,"-480/-60/0/0/0/0/0/0/0/0"=>"Asia/Brunei"
        ,"-420/-60/0/0/0/0/0/0/0/0"=>"Antarctica/Davis"
        ,"-390/-60/0/0/0/0/0/0/0/0"=>"Asia/Yangon"
        ,"-360/-60/0/0/0/0/0/0/0/0"=>"Antarctica/Vostok"
        ,"-345/-60/0/0/0/0/0/0/0/0"=>"Asia/Kathmandu"
        ,"-330/-60/0/0/0/0/0/0/0/0"=>"Asia/Colombo"
        ,"-300/-60/0/0/0/0/0/0/0/0"=>"Antarctica/Mawson"
        ,"-270/-60/0/0/0/0/0/0/0/0"=>"Asia/Kabul"
        ,"-210/-60/9/3/4/22/3/3/3/22"=>"Asia/Tehran"
        ,"-180/-60/0/0/0/0/0/0/0/0"=>"Africa/Addis_Ababa"
        ,"-120/-60/10/5/0/4/3/5/0/3"=>"Europe/Helsinki"
        ,"-120/-60/0/0/0/0/0/0/0/0"=>"Africa/Blantyre"
        ,"-60/-60/10/5/0/3/3/5/0/2"=>"Europe/Berlin"
        ,"-60/-60/10/4/0/3/3/5/0/2"=>"Europe/Berlin"
        ,"-60/-60/0/0/0/0/0/0/0/0"=>"Africa/Algiers"
        ,"0/-60/10/5/0/2/3/5/0/1"=>"Europe/Dublin"
        ,"0/-60/10/4/0/2/3/5/0/1"=>"Europe/Dublin"
        ,"0/-60/0/0/0/0/0/0/0/0"=>"Africa/Abidjan"
        ,"60/-60/0/0/0/0/0/0/0/0"=>"Atlantic/Cape_Verde"
        ,"180/-60/2/4/6/23/10/3/6/23"=>"America/Sao_Paulo"
        ,"180/-60/10/5/6/23/3/4/6/22"=>"America/Godthab"
        ,"180/-60/0/0/0/0/0/0/0/0"=>"America/Araguaina"
        ,"240/-60/11/1/0/2/3/2/0/2"=>"America/Barbados"
        ,"240/-60/0/0/0/0/0/0/0/0"=>"America/Anguilla"
        ,"270/-60/0/0/0/0/0/0/0/0"=>"America/Caracas"
        ,"300/-60/11/1/0/2/3/2/0/2"=>"America/New_York"
        ,"300/-60/0/0/0/0/0/0/0/0"=>"America/Atikokan"
        ,"360/-60/11/1/0/2/3/2/0/2"=>"America/Chicago"
        ,"360/-60/0/0/0/0/0/0/0/0"=>"America/Belize"
        ,"420/-60/10/5/0/2/4/1/0/2"=>"America/Chihuahua"
        ,"420/-60/11/1/0/2/3/2/0/2"=>"America/Denver"
        ,"420/-60/0/0/0/0/0/0/0/0"=>"America/Creston"
        ,"480/-60/11/1/0/2/3/2/0/2"=>"America/Los_Angeles"
        ,"540/-60/11/1/0/2/3/2/0/2"=>"America/Anchorage"
        ,"600/-60/0/0/0/0/0/0/0/0"=>"Pacific/Honolulu"
    );

    /**
     * Given the MS timezone find a matching tzid, for the year the event starts in.
     * @param string $mstz
     * @param string $eventstart
     * @return string
     */
    public static function tzidFromMSTZ($mstz, $eventstart){
        // 1. Check known MS time zones
        $mstz_parts = unpack("lbias/Z64tzname/vdstendyear/vdstendmonth/vdstendday/vdstendweek/vdstendhour/"
                                    ."vdstendminute/vdstendsecond/vdstendmillis/lstdbias/Z64tznamedst/vdststartyear/"
                                    ."vdststartmonth/vdststartday/vdststartweek/vdststarthour/vdststartminute/"
                                    ."vdststartsecond/vdststartmillis/ldstbias", base64_decode($mstz));
        $mstz = $mstz_parts['bias']
                    ."/".$mstz_parts['dstbias']
                    ."/".$mstz_parts['dstendmonth']
                    ."/".$mstz_parts['dstendweek']
                    ."/".$mstz_parts['dstendday']
                    ."/".$mstz_parts['dstendhour']
                    ."/".$mstz_parts['dststartmonth']
                    ."/".$mstz_parts['dststartweek']
                    ."/".$mstz_parts['dststartday']
                    ."/".$mstz_parts['dststarthour'];
        if (isset(self::$knownMSTZS[$mstz])) {
            $tzid = self::$knownMSTZS[$mstz];
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->tzidFromMSTZ(): Found tzid in known list: '%s'.", $tzid));
            return $tzid;
        }

        // 2. Loop all time zones to find a match on offset and transition date
        $year = date("Y", $eventstart);
        $offset_std = -($mstz_parts["bias"] * 60);
        $offset_dst = -(($mstz_parts["bias"] + $mstz_parts["dstbias"]) * 60);
        $dststart_timestamp = self::timestampFromMSTZ($mstz_parts, "dststart", $mstz_parts["bias"], $year);
        $dstend_timestamp = self::timestampFromMSTZ($mstz_parts, "dstend", $mstz_parts["bias"] + $mstz_parts["dstbias"], $year);

        $tzids = DateTimeZone::listIdentifiers();
        foreach ($tzids as $tzid) {
            $timezone = new DateTimeZone($tzid);
            $transitions = $timezone->getTransitions(date("U", strtotime($year."0101T000000Z")), date("U", strtotime($year."1231T235959Z")));

            $tno = count($transitions);
            if ($tno == 1 && $dststart_timestamp == 0) {
                if ($transitions[0]['offset'] == $offset_std) {
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->tzidFromMSTZ(): Found tzid: '%s'.", $tzid));
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->tzidFromMSTZ(): Add tzid to knownMSTZS array for better performance: '%s'.", ',"'.$mstz.'"=>"'.$tzid.'"'));
                    return $tzid;
                }
            }
            else if (($tno == 3 || $tno == 5) && $dststart_timestamp != 0) {
                if ($dststart_timestamp < $dstend_timestamp) {
                    if(
                        $transitions[1]['isdst'] == 1 &&
                        $transitions[1]['ts'] == $dststart_timestamp &&
                        $transitions[1]['offset'] == $offset_dst &&
                        $transitions[2]['isdst'] == 0 &&
                        $transitions[2]['ts'] == $dstend_timestamp &&
                        $transitions[2]['offset'] == $offset_std)
                    {
                        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->tzidFromMSTZ(): Found tzid: '%s'.", $tzid));
                        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->tzidFromMSTZ(): Add tzid to knownMSTZS array for better performance: '%s'.", ',"'.$mstz.'"=>"'.$tzid.'"'));
                        return $tzid;
                    }
                }
                else {
                    if (
                        $transitions[1]['isdst'] == 0 &&
                        $transitions[1]['ts'] == $dstend_timestamp &&
                        $transitions[1]['offset'] == $offset_std &&
                        $transitions[2]['isdst'] == 1 &&
                        $transitions[2]['ts'] == $dststart_timestamp &&
                        $transitions[2]['offset'] == $offset_dst)
                    {
                        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->tzidFromMSTZ(): Found tzid: '%s'.", $tzid));
                        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->tzidFromMSTZ(): Add tzid to knownMSTZS array for better performance: '%s'.", ',"'.$mstz.'"=>"'.$tzid.'"'));
                        return $tzid;
                    }
                }
            }
        }

        // 3. Give up, use Zulu
        ZLog::Write(LOGLEVEL_WARN, sprintf("BackendCalDAV->tzidFromMSTZ(): Failed to find tzid, defaulting to UTC. MS time zone: '%s'.", join('/', $mstz_parts)));
        return null;
    }

    /*
     * Calculate a unix timestamp for DST or STD start times in the MS time zone.
     * @param array $mstz_parts
     * @param string $prefix
     * @param integer $bias
     * @param string $year
     * @return integer
     */
    private static function timestampFromMSTZ($mstz_parts, $prefix, $bias, $year){
        if($mstz_parts[$prefix."month"] == 0){return 0;} // If month is empty, there is no transition

        $month = $mstz_parts[$prefix."month"];
        $weeks = array('', 'first', 'second', 'third', 'fourth', 'last');
        $week = $weeks[$mstz_parts[$prefix."week"]];
        $days = array('Sunday', 'Monday', 'Tuesday', 'Wednesday','Thursday','Friday', 'Saturday');
        $day = $days[$mstz_parts[$prefix."day"]];

        $second = $mstz_parts[$prefix."hour"] * 3600 + $mstz_parts[$prefix."minute"] * 60 + $mstz_parts[$prefix."second"] + $bias * 60;

        return date("U", strtotime("$week $day of $year-$month Z") + $second);
    }

    /*
     * Calculate a DAV datetime field with timezone
     * @param int $timestamp
     * @param string $tzid
     * @return string
     */
    private static function DAVDateTimeInTimezone($timestamp, $tzid) {
        $dt = new DateTime('@'.$timestamp);
        $dt->setTimeZone(new DateTimeZone($tzid));
        return $tzid == 'Z' ? $dt->format('Ymd\THis\Z') : $dt->format('Ymd\THis');
    }

    /**
     * Convert the offset provided by php to what ical uses in VTIMEZONE.
     * @param integer $phpoffset
     * @return string
     */
    private static function phpOffsetToIcalOffset($phpoffset) {
        $prefix = $phpoffset < 0 ? "-" : "+";
        $offset = abs($phpoffset);
        $hours = floor($offset / 3600);
        return sprintf("$prefix%'.02d%'.02d", $hours, ($offset - ($hours * 3600)) / 60);
    }

}