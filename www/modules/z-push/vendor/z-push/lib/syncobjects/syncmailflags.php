<?php
/***********************************************
* File      :   syncmailflags.php
* Project   :   Z-Push
* Descr     :   WBXML AirSyncBase body entities that can be parsed
*               directly (as a stream) from WBXML.
*               It is automatically decoded according to $mapping,
*               and the Sync WBXML mappings.
*
* Created   :   09.09.2011
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

class SyncMailFlags extends SyncObject {
    public $subject;
    public $flagstatus;
    public $flagtype; //Possible types are clear, complete, active
    public $datecompleted;
    public $completetime;
    public $startdate;
    public $duedate;
    public $utcstartdate;
    public $utcduedate;
    public $reminderset;
    public $remindertime;
    public $ordinaldate;
    public $subordinaldate;


    function __construct() {
        $mapping = array(
                    SYNC_POOMTASKS_SUBJECT                              => array (  self::STREAMER_VAR      => "subject",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMMAIL_FLAGSTATUS                            => array (  self::STREAMER_VAR      => "flagstatus",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMMAIL_FLAGTYPE                              => array (  self::STREAMER_VAR      => "flagtype",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMTASKS_DATECOMPLETED                        => array (  self::STREAMER_VAR      => "datecompleted",
                                                                                    self::STREAMER_TYPE     => self::STREAMER_TYPE_DATE_DASHES,
                                                                                    self::STREAMER_RONOTIFY => true),

                    SYNC_POOMMAIL_COMPLETETIME                          => array (  self::STREAMER_VAR      => "completetime",
                                                                                    self::STREAMER_TYPE     => self::STREAMER_TYPE_DATE_DASHES,
                                                                                    self::STREAMER_RONOTIFY => true),

                    SYNC_POOMTASKS_STARTDATE                            => array (  self::STREAMER_VAR      => "startdate",
                                                                                    self::STREAMER_TYPE     => self::STREAMER_TYPE_DATE_DASHES,
                                                                                    self::STREAMER_RONOTIFY => true),

                    SYNC_POOMTASKS_DUEDATE                              => array (  self::STREAMER_VAR      => "duedate",
                                                                                    self::STREAMER_TYPE     => self::STREAMER_TYPE_DATE_DASHES,
                                                                                    self::STREAMER_RONOTIFY => true),

                    SYNC_POOMTASKS_UTCSTARTDATE                         => array (  self::STREAMER_VAR      => "utcstartdate",
                                                                                    self::STREAMER_TYPE     => self::STREAMER_TYPE_DATE_DASHES,
                                                                                    self::STREAMER_RONOTIFY => true),

                    SYNC_POOMTASKS_UTCDUEDATE                           => array (  self::STREAMER_VAR      => "utcduedate",
                                                                                    self::STREAMER_TYPE     => self::STREAMER_TYPE_DATE_DASHES,
                                                                                    self::STREAMER_RONOTIFY => true),

                    SYNC_POOMTASKS_REMINDERSET                          => array (  self::STREAMER_VAR      => "reminderset",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMTASKS_REMINDERTIME                         => array (  self::STREAMER_VAR      => "remindertime",
                                                                                    self::STREAMER_TYPE     => self::STREAMER_TYPE_DATE_DASHES,
                                                                                    self::STREAMER_RONOTIFY => true),

                    SYNC_POOMTASKS_ORDINALDATE                          => array (  self::STREAMER_VAR      => "ordinaldate",
                                                                                    self::STREAMER_TYPE     => self::STREAMER_TYPE_DATE_DASHES,
                                                                                    self::STREAMER_RONOTIFY => true),

                    SYNC_POOMTASKS_SUBORDINALDATE                       => array (  self::STREAMER_VAR      => "subordinaldate",
                                                                                    self::STREAMER_RONOTIFY => true),
        );

        parent::__construct($mapping);
    }
}
