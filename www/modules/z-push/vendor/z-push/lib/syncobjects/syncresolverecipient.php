<?php
/***********************************************
* File      :   syncresolverecipent.php
* Project   :   Z-Push
* Descr     :   WBXML appointment entities that can be
*               parsed directly (as a stream) from WBXML.
*               It is automatically decoded
*               according to $mapping,
*               and the Sync WBXML mappings
*
* Created   :   28.10.2012
*
* Copyright 2007 - 2013, 2015 - 2016 Zarafa Deutschland GmbH
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

class SyncResolveRecipient extends SyncObject {
    public $type;
    public $displayname;
    public $emailaddress;
    public $availability;
    public $certificates;
    public $picture;
    public $id;

    public function __construct() {
        $mapping = array (
            SYNC_RESOLVERECIPIENTS_TYPE                     => array (  self::STREAMER_VAR      => "type"),
            SYNC_RESOLVERECIPIENTS_DISPLAYNAME              => array (  self::STREAMER_VAR      => "displayname"),
            SYNC_RESOLVERECIPIENTS_EMAILADDRESS             => array (  self::STREAMER_VAR      => "emailaddress"),

            SYNC_RESOLVERECIPIENTS_CERTIFICATES             => array (  self::STREAMER_VAR      => "certificates",
                                                                        self::STREAMER_TYPE     => "SyncResolveRecipientsCertificates")
        );

        if (Request::GetProtocolVersion() >= 14.0) {
            $mapping[SYNC_RESOLVERECIPIENTS_AVAILABILITY]   = array (  self::STREAMER_VAR      => "availability",
                                                                       self::STREAMER_TYPE     => "SyncResolveRecipientsAvailability");
        }

        if (Request::GetProtocolVersion() >= 14.1) {
            $mapping[SYNC_RESOLVERECIPIENTS_PICTURE]        = array (  self::STREAMER_VAR      => "picture",
                                                                       self::STREAMER_TYPE     => "SyncResolveRecipientsPicture");
        }

        parent::__construct($mapping);
    }

}
