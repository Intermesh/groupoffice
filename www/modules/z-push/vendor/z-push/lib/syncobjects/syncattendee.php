<?php
/***********************************************
* File      :   syncattendee.php
* Project   :   Z-Push
* Descr     :   WBXML attendee entities that can be parsed
*               directly (as a stream) from WBXML.
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

class SyncAttendee extends SyncObject {
    public $email;
    public $name;
    public $attendeestatus;
    public $attendeetype;

    function __construct() {
        $mapping = array(
                    SYNC_POOMCAL_EMAIL                                  => array (  self::STREAMER_VAR      => "email",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_REQUIRED => self::STREAMER_CHECK_SETEMPTY),
                                                                                    self::STREAMER_RONOTIFY => true,
                                                                                    self::STREAMER_PRIVATE  => 'attendee@localhost'),

                    SYNC_POOMCAL_NAME                                   => array (  self::STREAMER_VAR      => "name",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_REQUIRED => self::STREAMER_CHECK_SETEMPTY),
                                                                                    self::STREAMER_RONOTIFY => true,
                                                                                    self::STREAMER_PRIVATE  => 'Undisclosed Attendee')
                );

        if (Request::GetProtocolVersion() >= 12.0) {
            $mapping[SYNC_POOMCAL_ATTENDEESTATUS]                       =  array (  self::STREAMER_VAR      => "attendeestatus",
                                                                                    self::STREAMER_RONOTIFY => true,
                                                                                    self::STREAMER_PRIVATE  => true
            );
            $mapping[SYNC_POOMCAL_ATTENDEETYPE]                         =  array (  self::STREAMER_VAR      => "attendeetype",
                                                                                    self::STREAMER_RONOTIFY => true,
                                                                                    self::STREAMER_PRIVATE  => true
            );
        }

        parent::__construct($mapping);

        // Indicates that this SyncObject supports the private flag and stripping of private data.
        $this->supportsPrivateStripping = true;
    }
}
