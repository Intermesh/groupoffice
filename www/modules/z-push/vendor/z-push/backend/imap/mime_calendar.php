<?php
/***********************************************
* File      :   mime_calendar.php
* Project   :   Z-Push
* Descr     :   Functions for using within the IMAP backend
*
* Created   :   2015
*
* Copyright 2015 - 2016 Zarafa Deutschland GmbH
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

function create_calendar_dav($data) {
    ZLog::Write(LOGLEVEL_DEBUG, "BackendIMAP->create_calendar_dav(): Creating calendar event");

    if (defined('IMAP_MEETING_USE_CALDAV') && IMAP_MEETING_USE_CALDAV) {
        $caldav = new BackendCalDAV();
        if ($caldav->Logon(Request::GetAuthUser(), Request::GetAuthDomain(), Request::GetAuthPassword())) {
            $etag = $caldav->CreateUpdateCalendar($data);
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->create_calendar_dav(): Calendar created with etag '%s' and data <%s>", $etag, $data));
            $caldav->Logoff();
        }
        else {
            ZLog::Write(LOGLEVEL_ERROR, "BackendIMAP->create_calendar_dav(): Error connecting with BackendCalDAV");
        }
    }
}

function delete_calendar_dav($uid) {
    ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->delete_calendar_dav('%s'): Deleting calendar event", $uid));

    if ($uid === false) {
        ZLog::Write(LOGLEVEL_WARN, "BackendIMAP->delete_calendar_dav(): UID not found; report the full calendar object to developers");
    }
    else {
        if (defined('IMAP_MEETING_USE_CALDAV') && IMAP_MEETING_USE_CALDAV) {
            $caldav = new BackendCalDAV();
            if ($caldav->Logon(Request::GetAuthUser(), Request::GetAuthDomain(), Request::GetAuthPassword())) {
                $events = $caldav->FindCalendar($uid);
                if (count($events) == 1) {
                    $href = $events[0]["href"];
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->delete_calendar_dav(): found event with href '%s', deleting", $href));
                    // Delete event
                    $res = $caldav->DeleteCalendar($href);
                    if ($res) {
                        ZLog::Write(LOGLEVEL_DEBUG, "BackendIMAP->delete_calendar_dav(): event deleted");
                    }
                    else {
                        ZLog::Write(LOGLEVEL_ERROR, "BackendIMAP->delete_calendar_dav(): error removing event, we will end with zombie events");
                    }
                    $caldav->Logoff();
                }
                else {
                    ZLog::Write(LOGLEVEL_ERROR, "BackendIMAP->delete_calendar_dav(): event not found, we will end with zombie events");
                }
            }
            else {
                ZLog::Write(LOGLEVEL_ERROR, "BackendIMAP->delete_calendar_dav(): Error connecting with BackendCalDAV");
            }
        }
    }
}


function update_calendar_attendee($uid, $mailto, $status) {
    ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->update_calendar_attendee('%s', '%s', '%s'): Updating calendar event attendee", $uid, $mailto, $status));
    $updated = false;

    if ($uid === false) {
        ZLog::Write(LOGLEVEL_WARN, "BackendIMAP->update_calendar_attendee(): UID not found; report the full calendar object to developers");
    }
    else {
        if (defined('IMAP_MEETING_USE_CALDAV') && IMAP_MEETING_USE_CALDAV) {
            $caldav = new BackendCalDAV();
            if ($caldav->Logon(Request::GetAuthUser(), Request::GetAuthDomain(), Request::GetAuthPassword())) {
                $events = $caldav->FindCalendar($uid);
                if (count($events) == 1) {
                    $href = $events[0]["href"];
                    $etag = $events[0]["etag"];
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->update_calendar_attendee(): found event with href '%s' etag '%s'; updating", $href, $etag));

                    // Get Attendee status
                    $old_status = "";

                    if (strcasecmp($old_status, $status) != 0) {
                        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->update_calendar_attendee(): Before <%s>", $events[0]["data"]));
                        $ical = new iCalComponent();
                        $ical->ParseFrom($events[0]["data"]);
                        $ical->SetCPParameterValue("VEVENT", "ATTENDEE", "PARTSTAT", strtoupper($status), $mailto);
                        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->update_calendar_attendee(): After <%s>", $ical->Render()));
                        $etag = $caldav->CreateUpdateCalendar($ical->Render(), $href, $etag);
                        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->update_calendar_attendee(): Calendar updated with etag '%s'", $etag));
                        // Update new status
                        $updated = true;
                    }

                    $caldav->Logoff();
                }
                else {
                    ZLog::Write(LOGLEVEL_ERROR, "BackendIMAP->update_calendar_attendee(): event not found or duplicated event");
                }
            }
            else {
                ZLog::Write(LOGLEVEL_ERROR, "BackendIMAP->update_calendar_attendee(): Error connecting with BackendCalDAV");
            }
        }
    }

    return $updated;
}

/**
 * Detect if one message has one VCALENDAR part
 *
 * @param Mail_mimeDecode $message
 * @return boolean
 */
function has_calendar_object($message) {
    if (is_calendar($message)) {
        return true;
    }
    else {
        if(isset($message->parts)) {
            for ($i = 0; $i < count($message->parts); $i++) {
                if (is_calendar($message->parts[$i])) {
                    return true;
                }
            }
        }
    }

    return false;
}


/**
 * Detect if the message-part is VCALENDAR
 * Content-Type: text/calendar;
 *
 * @param Mail_mimeDecode $message
 * @return boolean
 */
function is_calendar($message) {
    return isset($message->ctype_primary) && isset($message->ctype_secondary) && $message->ctype_primary == "text" && $message->ctype_secondary == "calendar";
}


/**
 * Converts a text/calendar part into SyncMeetingRequest
 * This is called on received messages, it's not called for events generated from the mobile
 *
 * @param $part             MIME part
 * @param $output           SyncMail object
 * @param $is_sent_folder   boolean
 */
function parse_meeting_calendar($part, &$output, $is_sent_folder) {
    $ical = new iCalComponent();
    $ical->ParseFrom($part->body);
    ZLog::Write(LOGLEVEL_WBXML, sprintf("BackendIMAP->parse_meeting_calendar(): %s", $part->body));

    // Get UID
    $uid = false;
    $props = $ical->GetPropertiesByPath("VEVENT/UID");
    if (count($props) > 0) {
        $uid = $props[0]->Value();
    }

    $method = false;
    $props = $ical->GetPropertiesByPath("VCALENDAR/METHOD");
    if (count($props) > 0) {
        $method = strtolower($props[0]->Value());
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->parse_meeting_calendar(): Using method from vcalendar object: %s", $method));
    }
    else {
        if (isset($part->ctype_parameters["method"])) {
            $method = strtolower($part->ctype_parameters["method"]);
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->parse_meeting_calendar(): Using method from mime part object: %s", $method));
        }
    }

    if ($method === false) {
        ZLog::Write(LOGLEVEL_WARN, sprintf("BackendIMAP->parse_meeting_calendar() - No method header, please report it to the developers"));
        $output->messageclass = "IPM.Appointment";
    }
    else {
        switch ($method) {
            case "cancel":
                $output->messageclass = "IPM.Schedule.Meeting.Canceled";
                ZLog::Write(LOGLEVEL_DEBUG, "BackendIMAP->parse_meeting_calendar(): Event canceled, removing calendar object");
                delete_calendar_dav($uid);
                break;
            case "counter":
                ZLog::Write(LOGLEVEL_DEBUG, "BackendIMAP->parse_meeting_calendar(): Counter received");
                $output->messageclass = "IPM.Schedule.Meeting.Resp.Tent";
                $output->meetingrequest->disallownewtimeproposal = 0;
                break;
            case "reply":
                ZLog::Write(LOGLEVEL_DEBUG, "BackendIMAP->parse_meeting_calendar(): Reply received");
                $props = $ical->GetPropertiesByPath('VEVENT/ATTENDEE');

                for ($i = 0; $i < count($props); $i++) {
                    $mailto = $props[$i]->Value();
                    $props_params = $props[$i]->Parameters();
                    $status = strtolower($props_params["PARTSTAT"]);
                    if (!$is_sent_folder) {
                        // Only evaluate received replies, not sent
                        $res = update_calendar_attendee($uid, $mailto, $status);
                    }
                    else {
                        $res = true;
                    }
                    if ($res) {
                        // Only set messageclass for replies changing my calendar object
                        switch ($status) {
                            case "accepted":
                                $output->messageclass = "IPM.Schedule.Meeting.Resp.Pos";
                                ZLog::Write(LOGLEVEL_DEBUG, "BackendIMAP->parse_meeting_calendar(): Update attendee -> accepted");
                                break;
                            case "needs-action":
                                $output->messageclass = "IPM.Schedule.Meeting.Resp.Tent";
                                ZLog::Write(LOGLEVEL_DEBUG, "BackendIMAP->parse_meeting_calendar(): Update attendee -> needs-action");
                                break;
                            case "tentative":
                                $output->messageclass = "IPM.Schedule.Meeting.Resp.Tent";
                                ZLog::Write(LOGLEVEL_DEBUG, "BackendIMAP->parse_meeting_calendar(): Update attendee -> tentative");
                                break;
                            case "declined":
                                $output->messageclass = "IPM.Schedule.Meeting.Resp.Neg";
                                ZLog::Write(LOGLEVEL_DEBUG, "BackendIMAP->parse_meeting_calendar(): Update attendee -> declined");
                                break;
                            default:
                                ZLog::Write(LOGLEVEL_WARN, sprintf("BackendIMAP->parse_meeting_calendar() - Unknown reply status <%s>, please report it to the developers", $status));
                                $output->messageclass = "IPM.Appointment";
                                break;
                        }
                    }
                }
                $output->meetingrequest->disallownewtimeproposal = 1;
                break;
            case "request":
                $output->messageclass = "IPM.Schedule.Meeting.Request";
                $output->meetingrequest->disallownewtimeproposal = 0;
                ZLog::Write(LOGLEVEL_DEBUG, "BackendIMAP->parse_meeting_calendar(): New request");
                // New meeting, we don't create it now, because we need to confirm it first, but if we don't create it we won't see it in the calendar
                break;
            default:
                ZLog::Write(LOGLEVEL_WARN, sprintf("BackendIMAP->parse_meeting_calendar() - Unknown method <%s>, please report it to the developers", strtolower($part->headers["method"])));
                $output->messageclass = "IPM.Appointment";
                $output->meetingrequest->disallownewtimeproposal = 0;
                break;
        }
    }

    $props = $ical->GetPropertiesByPath('VEVENT/DTSTAMP');
    if (count($props) == 1) {
        $output->meetingrequest->dtstamp = TimezoneUtil::MakeUTCDate($props[0]->Value());
    }
    $props = $ical->GetPropertiesByPath('VEVENT/UID');
    if (count($props) == 1) {
        $output->meetingrequest->globalobjid = $props[0]->Value();
    }
    $props = $ical->GetPropertiesByPath('VEVENT/DTSTART');
    if (count($props) == 1) {
        $output->meetingrequest->starttime = TimezoneUtil::MakeUTCDate($props[0]->Value());
        if (strlen($props[0]->Value()) == 8) {
            $output->meetingrequest->alldayevent = 1;
        }
    }
    $props = $ical->GetPropertiesByPath('VEVENT/DTEND');
    if (count($props) == 1) {
        $output->meetingrequest->endtime = TimezoneUtil::MakeUTCDate($props[0]->Value());
        if (strlen($props[0]->Value()) == 8) {
            $output->meetingrequest->alldayevent = 1;
        }
    }
    $props = $ical->GetPropertiesByPath('VEVENT/ORGANIZER');
    if (count($props) == 1) {
        $output->meetingrequest->organizer = str_ireplace("MAILTO:", "", $props[0]->Value());
    }
    $props = $ical->GetPropertiesByPath('VEVENT/LOCATION');
    if (count($props) == 1) {
        $output->meetingrequest->location = $props[0]->Value();
    }
    $props = $ical->GetPropertiesByPath('VEVENT/CLASS');
    if (count($props) == 1) {
        switch ($props[0]->Value()) {
            case "PUBLIC":
                $output->meetingrequest->sensitivity = "0";
                break;
            case "PRIVATE":
                $output->meetingrequest->sensitivity = "2";
                break;
            case "CONFIDENTIAL":
                $output->meetingrequest->sensitivity = "3";
                break;
            default:
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->parse_meeting_calendar() - Unknown VEVENT/CLASS '%s'. Using 0", $props[0]->Value()));
                $output->meetingrequest->sensitivity = "0";
                break;
        }
    }
    else {
        ZLog::Write(LOGLEVEL_DEBUG, "BackendIMAP->parse_meeting_calendar() - No sensitivity class. Using 0");
        $output->meetingrequest->sensitivity = "0";
    }

    // Get $tz from first timezone
    $props = $ical->GetPropertiesByPath("VTIMEZONE/TZID");
    if (count($props) > 0) {
        // TimeZones shouldn't have dots
        $tzname = str_replace(".", "", $props[0]->Value());
        $tz = TimezoneUtil::GetFullTZFromTZName($tzname);
    }
    else {
        $tz = TimezoneUtil::GetFullTZ();
    }
    $output->meetingrequest->timezone = base64_encode(TimezoneUtil::GetSyncBlobFromTZ($tz));

    // Fixed values
    $output->meetingrequest->instancetype = 0;
    $output->meetingrequest->responserequested = 1;
    $output->meetingrequest->busystatus = 2;

    // TODO: reminder
    $output->meetingrequest->reminder = "";
}



/**
 * Modify a text/calendar part to transform it in a reply
 *
 * @param $part             MIME part
 * @param $response         Response numeric value
 * @param $condition_value  string
 * @return string MIME text/calendar
 */
function reply_meeting_calendar($part, $response, $username) {
    $status_attendee = "ACCEPTED"; // 1 or default is ACCEPTED
    $status_event = "CONFIRMED";
    switch ($response) {
        case 1:
            $status_attendee = "ACCEPTED";
            $status_event = "CONFIRMED";
            break;
        case 2:
            $status_attendee = $status_event = "TENTATIVE";
            break;
        case 3:
            // We won't hit this case ever, because we won't create an event if we are rejecting it
            $status_attendee = "DECLINED";
            $status_event = "CANCELLED";
            break;
    }

    $ical = new iCalComponent();
    $ical->ParseFrom($part->body);

    $ical->SetPValue("METHOD", "REPLY");
    $ical->SetCPParameterValue("VEVENT", "STATUS", $status_event, null);
    // Update my information as attendee, but only mine
    $ical->SetCPParameterValue("VEVENT", "ATTENDEE", "PARTSTAT", $status_attendee, sprintf("MAILTO:%s", $username));
    $ical->SetCPParameterValue("VEVENT", "ATTENDEE", "RSVP", null, sprintf("MAILTO:%s", $username));

    return $ical->Render();
}
