<?php
/***********************************************
* File      :   syncemailaddresses.php
* Project   :   Z-Push
* Descr     :   WBXML email adresses entities that can be
*               parsed directly (as a stream) from WBXML.
*               It is automatically decoded
*               according to $mapping,
*               and the Sync WBXML mappings
*
* Created   :   18.05.2017
*
* Copyright 2017 Zarafa Deutschland GmbH
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

class SyncEmailAddresses extends SyncObject {
    public $smtpaddress;
    public $primarysmtpaddress;

    public function __construct() {
        $mapping = array (
             SYNC_SETTINGS_SMPTADDRESS              => array (  self::STREAMER_VAR      => "smtpaddress",
                                                                self::STREAMER_PROP     => self::STREAMER_TYPE_NO_CONTAINER,
                                                                self::STREAMER_ARRAY     => SYNC_SETTINGS_SMPTADDRESS),
             SYNC_SETTINGS_PRIMARYSMTPADDRESS       => array (  self::STREAMER_VAR      => "primarysmtpaddress"));

        parent::__construct($mapping);
    }
}