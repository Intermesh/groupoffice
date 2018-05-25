<?php
/***********************************************
* File      :   syncappointment.php
* Project   :   Z-Push
* Descr     :   WBXML appointment entities that can be
*               parsed directly (as a stream) from WBXML.
*               It is automatically decoded
*               according to $mapping,
*               and the Sync WBXML mappings.
*
* Created   :   05.09.2011
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

class SyncAppointment extends SyncObject {
    public $timezone;
    public $dtstamp;
    public $starttime;
    public $subject;
    public $uid;
    public $organizername;
    public $organizeremail;
    public $location;
    public $endtime;
    public $recurrence;
    public $sensitivity;
    public $busystatus;
    public $alldayevent;
    public $reminder;
    public $rtf;
    public $meetingstatus;
    public $attendees;
    public $body;
    public $bodytruncated;
    public $exceptions;
    public $deleted;
    public $exceptionstarttime;
    public $categories;

    // AS 12.0 props
    public $asbody;
    public $nativebodytype;

    // AS 14.0 props
    public $disallownewtimeprop;
    public $responsetype;
    public $responserequested;

    // AS 14.1 props
    public $onlineMeetingConfLink;
    public $onlineMeetingExternalLink;

    function __construct() {
        $mapping = array(
                    SYNC_POOMCAL_TIMEZONE                               => array (  self::STREAMER_VAR      => "timezone",
                                                                                    self::STREAMER_RONOTIFY => true),

                    SYNC_POOMCAL_DTSTAMP                                => array (  self::STREAMER_VAR      => "dtstamp",
                                                                                    self::STREAMER_TYPE     => self::STREAMER_TYPE_DATE,
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_REQUIRED       => self::STREAMER_CHECK_SETZERO)),

                    SYNC_POOMCAL_STARTTIME                              => array (  self::STREAMER_VAR      => "starttime",
                                                                                    self::STREAMER_TYPE     => self::STREAMER_TYPE_DATE,
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_CMPLOWER       => SYNC_POOMCAL_ENDTIME ),
                                                                                    self::STREAMER_RONOTIFY => true ),


                    SYNC_POOMCAL_SUBJECT                                => array (  self::STREAMER_VAR      => "subject",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_REQUIRED       => self::STREAMER_CHECK_SETEMPTY),
                                                                                    self::STREAMER_RONOTIFY => true,
                                                                                    self::STREAMER_PRIVATE  => self::STRIP_PRIVATE_SUBSTITUTE ),

                    SYNC_POOMCAL_UID                                    => array (  self::STREAMER_VAR      => "uid"),
                    SYNC_POOMCAL_ORGANIZERNAME                          => array (  self::STREAMER_VAR      => "organizername", // verified below
                                                                                    self::STREAMER_PRIVATE  => 'Undisclosed Organizer' ),
                    SYNC_POOMCAL_ORGANIZEREMAIL                         => array (  self::STREAMER_VAR      => "organizeremail", // verified below
                                                                                    self::STREAMER_PRIVATE  => 'undisclosed@localhost' ),
                    SYNC_POOMCAL_LOCATION                               => array (  self::STREAMER_VAR      => "location",
                                                                                    self::STREAMER_RONOTIFY => true,
                                                                                    self::STREAMER_PRIVATE  => true),
                    SYNC_POOMCAL_ENDTIME                                => array (  self::STREAMER_VAR      => "endtime",
                                                                                    self::STREAMER_TYPE     => self::STREAMER_TYPE_DATE,
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_CMPHIGHER      => SYNC_POOMCAL_STARTTIME ),
                                                                                    self::STREAMER_RONOTIFY => true ),

                    SYNC_POOMCAL_RECURRENCE                             => array (  self::STREAMER_VAR      => "recurrence",
                                                                                    self::STREAMER_TYPE     => "SyncRecurrence",
                                                                                    self::STREAMER_RONOTIFY => true),

                    // Sensitivity values
                    // 0 = Normal
                    // 1 = Personal
                    // 2 = Private
                    // 3 = Confident
                    SYNC_POOMCAL_SENSITIVITY                            => array (  self::STREAMER_VAR      => "sensitivity",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(0,1,2,3) ),
                                                                                    self::STREAMER_RONOTIFY => true,
                                                                                    self::STREAMER_VALUEMAP => array(   0 => "Normal",
                                                                                                                        1 => "Personal",
                                                                                                                        2 => "Private",
                                                                                                                        3 => "Confident")),

                    // Busystatus values
                    // 0 = Free
                    // 1 = Tentative
                    // 2 = Busy
                    // 3 = Out of office
                    // 4 = Working Elsewhere
                    SYNC_POOMCAL_BUSYSTATUS                             => array (  self::STREAMER_VAR      => "busystatus",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_REQUIRED   => self::STREAMER_CHECK_SETTWO,
                                                                                                                        self::STREAMER_CHECK_ONEVALUEOF => array(0,1,2,3,4) ),
                                                                                    self::STREAMER_RONOTIFY => true,
                                                                                    self::STREAMER_PRIVATE  => 2,                   // if private is stripped, value will be set to 2 (busy)
                                                                                    self::STREAMER_VALUEMAP => array(   0 => "Free",
                                                                                                                        1 => "Tentative",
                                                                                                                        2 => "Busy",
                                                                                                                        3 => "Out of office",
                                                                                                                        4 => "Working Elsewhere")),

                    SYNC_POOMCAL_ALLDAYEVENT                            => array (  self::STREAMER_VAR      => "alldayevent",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ZEROORONE      => self::STREAMER_CHECK_SETZERO),
                                                                                    self::STREAMER_RONOTIFY => true,
                                                                                    self::STREAMER_VALUEMAP => array(   0 => "No",
                                                                                                                        1 => "Yes")),

                    SYNC_POOMCAL_REMINDER                               => array (  self::STREAMER_VAR      => "reminder",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_CMPHIGHER      => -1),
                                                                                    self::STREAMER_RONOTIFY => true,
                                                                                    self::STREAMER_PRIVATE  => true ),              // if private is stripped, value will be unset (no reminder)

                    SYNC_POOMCAL_RTF                                    => array (  self::STREAMER_VAR      => "rtf",
                                                                                    self::STREAMER_PRIVATE  => true),

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
                    SYNC_POOMCAL_MEETINGSTATUS                          => array (  self::STREAMER_VAR      => "meetingstatus",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(0,1,3,5,7,9,11,13,15) ),
                                                                                    self::STREAMER_RONOTIFY => true,
                                                                                    self::STREAMER_VALUEMAP => array(   0 => "Not a meeting",
                                                                                                                        1 => "Meeting",
                                                                                                                        3 => "Meeting received",
                                                                                                                        5 => "Meeting canceled",
                                                                                                                        7 => "Meeting canceled and received",
                                                                                                                        9 => "Meeting",
                                                                                                                       11 => "Meeting received",
                                                                                                                       13 => "Meeting canceled",
                                                                                                                       15 => "Meeting canceled and received",)),

                    SYNC_POOMCAL_ATTENDEES                              => array (  self::STREAMER_VAR      => "attendees",
                                                                                    self::STREAMER_TYPE     => "SyncAttendee",
                                                                                    self::STREAMER_ARRAY    => SYNC_POOMCAL_ATTENDEE,
                                                                                    self::STREAMER_RONOTIFY => true,
                                                                                    self::STREAMER_PRIVATE  => true),

                    SYNC_POOMCAL_BODY                                   => array (  self::STREAMER_VAR      => "body",
                                                                                    self::STREAMER_RONOTIFY => true,
                                                                                    self::STREAMER_PRIVATE  => true),
                    SYNC_POOMCAL_BODYTRUNCATED                          => array (  self::STREAMER_VAR      => "bodytruncated",
                                                                                    self::STREAMER_PRIVATE  => true),
                    SYNC_POOMCAL_EXCEPTIONS                             => array (  self::STREAMER_VAR      => "exceptions",
                                                                                    self::STREAMER_TYPE     => "SyncAppointmentException",
                                                                                    self::STREAMER_ARRAY    => SYNC_POOMCAL_EXCEPTION,
                                                                                    self::STREAMER_RONOTIFY => true),

                    SYNC_POOMCAL_CATEGORIES                             => array (  self::STREAMER_VAR      => "categories",
                                                                                    self::STREAMER_ARRAY    => SYNC_POOMCAL_CATEGORY,
                                                                                    self::STREAMER_RONOTIFY => true,
                                                                                    self::STREAMER_PRIVATE  => true),
                );

        if (Request::GetProtocolVersion() >= 12.0) {
            $mapping[SYNC_AIRSYNCBASE_BODY]                             = array (   self::STREAMER_VAR      => "asbody",
                                                                                    self::STREAMER_TYPE     => "SyncBaseBody",
                                                                                    self::STREAMER_RONOTIFY => true,
                                                                                    self::STREAMER_PRIVATE  => true
                                                                          );

            $mapping[SYNC_AIRSYNCBASE_NATIVEBODYTYPE]                   = array (   self::STREAMER_VAR      => "nativebodytype");

            //unset these properties because airsyncbase body and attachments will be used instead
            unset($mapping[SYNC_POOMCAL_BODY], $mapping[SYNC_POOMCAL_BODYTRUNCATED]);
        }

        if(Request::GetProtocolVersion() >= 14.0) {
            $mapping[SYNC_POOMCAL_DISALLOWNEWTIMEPROPOSAL]              = array (   self::STREAMER_VAR      => "disallownewtimeprop",
                                                                                    self::STREAMER_RONOTIFY => true,
                                                                                    self::STREAMER_PRIVATE  => 1); // don't permit new time proposal
            $mapping[SYNC_POOMCAL_RESPONSEREQUESTED]                    = array (   self::STREAMER_VAR      => "responserequested",
                                                                                    self::STREAMER_RONOTIFY => true);
            $mapping[SYNC_POOMCAL_RESPONSETYPE]                         = array (   self::STREAMER_VAR      => "responsetype",
                                                                                    self::STREAMER_RONOTIFY => true);
        }

        if(Request::GetProtocolVersion() >= 14.1) {
            $mapping[SYNC_POOMCAL_ONLINEMEETINGCONFLINK]                = array (   self::STREAMER_VAR      => "onlineMeetingConfLink",
                                                                                    self::STREAMER_RONOTIFY => true);
            $mapping[SYNC_POOMCAL_ONLINEMEETINGEXTERNALLINK]            = array (   self::STREAMER_VAR      => "onlineMeetingExternalLink",
                                                                                    self::STREAMER_RONOTIFY => true);
        }

        parent::__construct($mapping);

        // Indicates that this SyncObject supports the private flag and stripping of private data.
        $this->supportsPrivateStripping = true;
    }

    /**
     * Method checks if the object has the minimum of required parameters
     * and fullfills semantic dependencies
     *
     * This overloads the general check() with special checks to be executed
     * Checks if SYNC_POOMCAL_ORGANIZERNAME and SYNC_POOMCAL_ORGANIZEREMAIL are correctly set
     *
     * @param boolean   $logAsDebug     (opt) default is false, so messages are logged in WARN log level
     *
     * @access public
     * @return boolean
     */
    public function Check($logAsDebug = false) {
        // Fix starttime and endtime if they are not set on NEW appointments - see https://jira.z-hub.io/browse/ZP-983
        if ($this->flags === SYNC_NEWMESSAGE) {
            $time = time();
            $calcstart = $time + 1800 - ($time % 1800); // round up to the next half hour

            // Check error cases first
            // Case 2: starttime not set, endtime in the past
            if (!isset($this->starttime) && isset($this->endtime) && $this->endtime < $time) {
                ZLog::Write(LOGLEVEL_WARN, "SyncAppointment->Check(): Parameter 'starttime' not set while 'endtime' is in the past (case 2). Aborting.");
                return false;
            }
            // Case 3b: starttime not set, endtime in the future (3) but before the calculated starttime (3b)
            elseif (!isset($this->starttime) && isset($this->endtime) && $this->endtime > $time && $this->endtime < $calcstart) {
                ZLog::Write(LOGLEVEL_WARN, "SyncAppointment->Check(): Parameter 'starttime' not set while 'endtime' is in the future but before the calculated starttime (case 3b). Aborting.");
                return false;
            }
            // Case 5: starttime in the future but no endtime set
            elseif (isset($this->starttime) && $this->starttime > $time && !isset($this->endtime)) {
                ZLog::Write(LOGLEVEL_WARN, "SyncAppointment->Check(): Parameter 'starttime' is in the future but 'endtime' is not set (case 5). Aborting.");
                return false;
            }

            // Set starttime to the rounded up next half hour
            // Case 1, 3a (endtime won't be changed as it's set)
            if (!isset($this->starttime)) {
                $this->starttime = $calcstart;
                ZLog::Write(LOGLEVEL_WBXML, sprintf("SyncAppointment->Check(): Parameter 'starttime' was not set, setting it to %d (%s).", $this->starttime, gmstrftime("%Y%m%dT%H%M%SZ", $this->starttime)));
            }
            // Case 1, 4
            if (!isset($this->endtime)) {
                $this->endtime = $calcstart + 1800; // 30 min after calcstart
                ZLog::Write(LOGLEVEL_WBXML, sprintf("SyncAppointment->Check(): Parameter 'endtime' was not set, setting it to %d (%s).", $this->endtime, gmstrftime("%Y%m%dT%H%M%SZ", $this->endtime)));
            }
        }

        $ret = parent::Check($logAsDebug);

        // semantic checks general "turn off switch"
        if (defined("DO_SEMANTIC_CHECKS") && DO_SEMANTIC_CHECKS === false)
            return $ret;

        if (!$ret)
            return false;

        if ($this->meetingstatus > 0) {
            if (!isset($this->organizername) || !isset($this->organizeremail)) {
                ZLog::Write(LOGLEVEL_WARN, "SyncAppointment->Check(): Parameter 'organizername' and 'organizeremail' should be set for a meeting request");
            }
        }

        // do not sync a recurrent appointment without a timezone (except all day events)
        if (isset($this->recurrence) && !isset($this->timezone) && empty($this->alldayevent)) {
            ZLog::Write(LOGLEVEL_ERROR, "SyncAppointment->Check(): timezone for a recurring appointment is not set.");
            return false;
        }

        return true;
    }
}
