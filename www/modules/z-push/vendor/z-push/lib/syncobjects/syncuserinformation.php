<?php
/***********************************************
* File      :   syncuserinformation.php
* Project   :   Z-Push
* Descr     :   WBXML user information entities that can be
*               parsed directly (as a stream) from WBXML.
*               It is automatically decoded
*               according to $mapping,
*               and the Sync WBXML mappings
*
* Created   :   08.11.2011
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

class SyncUserInformation extends SyncObject {
    public $emailaddresses;
    public $accounts;
    public $Status;

    public function __construct() {
        $mapping = array(SYNC_SETTINGS_PROP_STATUS      => array (  self::STREAMER_VAR      => "Status",
                                                                    self::STREAMER_TYPE     => self::STREAMER_TYPE_IGNORE));

        // In AS protocoll versions 12.0, 12.1 and 14.0 EmailAddresses element is child of Get in UserSettings
        // Since AS protocoll version 14.1 EmailAddresses element is child of Account element of Get in UserSettings
        if (Request::GetProtocolVersion() >= 12.0) {
            $mapping[SYNC_SETTINGS_EMAILADDRESSES]      = array (   self::STREAMER_VAR      => "emailaddresses",
                                                                    self::STREAMER_ARRAY    => SYNC_SETTINGS_SMPTADDRESS);
        }

        if (Request::GetProtocolVersion() >= 14.1) {
            unset($mapping[SYNC_SETTINGS_EMAILADDRESSES]);
            $mapping[SYNC_SETTINGS_ACCOUNTS]            = array (   self::STREAMER_VAR      => "accounts",
                                                                    self::STREAMER_TYPE     => "SyncAccount",
                                                                    self::STREAMER_ARRAY    => SYNC_SETTINGS_ACCOUNT);
        }

        parent::__construct($mapping);
    }
}
