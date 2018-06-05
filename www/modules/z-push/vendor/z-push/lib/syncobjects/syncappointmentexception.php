<?php
/***********************************************
* File      :   syncappointmentexception.php
* Project   :   Z-Push
* Descr     :   WBXML appointment exception entities that
*               can be parsed directly (as a stream) from WBXML.
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

class SyncAppointmentException extends SyncAppointment {
    public $deleted;
    public $exceptionstarttime;

    function __construct() {
        parent::__construct();

        $this->mapping += array(
                    SYNC_POOMCAL_DELETED                                => array (  self::STREAMER_VAR      => "deleted",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ZEROORONE      => self::STREAMER_CHECK_SETZERO),
                                                                                    self::STREAMER_RONOTIFY => true),

                    SYNC_POOMCAL_EXCEPTIONSTARTTIME                     => array (  self::STREAMER_VAR      => "exceptionstarttime",
                                                                                    self::STREAMER_TYPE     => self::STREAMER_TYPE_DATE,
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_REQUIRED       => self::STREAMER_CHECK_SETONE),
                                                                                    self::STREAMER_RONOTIFY => true),
                );

        // some parameters are not required in an exception, others are not allowed to be set in SyncAppointmentExceptions
        $this->mapping[SYNC_POOMCAL_TIMEZONE][self::STREAMER_CHECKS]        = array();
        $this->mapping[SYNC_POOMCAL_TIMEZONE][self::STREAMER_RONOTIFY]      = true;
        $this->mapping[SYNC_POOMCAL_DTSTAMP][self::STREAMER_CHECKS]         = array();
        $this->mapping[SYNC_POOMCAL_STARTTIME][self::STREAMER_CHECKS]       = array(self::STREAMER_CHECK_CMPLOWER   => SYNC_POOMCAL_ENDTIME);
        $this->mapping[SYNC_POOMCAL_STARTTIME][self::STREAMER_RONOTIFY]     = true;
        $this->mapping[SYNC_POOMCAL_SUBJECT][self::STREAMER_CHECKS]         = array();
        $this->mapping[SYNC_POOMCAL_SUBJECT][self::STREAMER_RONOTIFY]       = true;
        $this->mapping[SYNC_POOMCAL_ENDTIME][self::STREAMER_CHECKS]         = array(self::STREAMER_CHECK_CMPHIGHER  => SYNC_POOMCAL_STARTTIME);
        $this->mapping[SYNC_POOMCAL_ENDTIME][self::STREAMER_RONOTIFY]       = true;
        $this->mapping[SYNC_POOMCAL_BUSYSTATUS][self::STREAMER_CHECKS]      = array(self::STREAMER_CHECK_ONEVALUEOF => array(0,1,2,3,4) );
        $this->mapping[SYNC_POOMCAL_BUSYSTATUS][self::STREAMER_RONOTIFY]    = true;
        $this->mapping[SYNC_POOMCAL_REMINDER][self::STREAMER_CHECKS]        = array(self::STREAMER_CHECK_CMPHIGHER  => -1);
        $this->mapping[SYNC_POOMCAL_REMINDER][self::STREAMER_RONOTIFY]      = true;
        $this->mapping[SYNC_POOMCAL_EXCEPTIONS][self::STREAMER_CHECKS]      = array(self::STREAMER_CHECK_NOTALLOWED => true);

        // Indicates that this SyncObject supports the private flag and stripping of private data.
        // It behaves as a SyncAppointment.
        $this->supportsPrivateStripping = true;
    }
}
