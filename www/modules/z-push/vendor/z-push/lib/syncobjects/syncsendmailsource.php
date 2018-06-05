<?php
/***********************************************
* File      :   syncsendmailsource.php
* Project   :   Z-Push
* Descr     :   WBXML send mail source entities
*               that can be parsed directly
*               (as a stream) from WBXML.
*               It is automatically decoded
*               according to $mapping,
*               and the Sync WBXML mappings.
*
* Created   :   30.01.2012
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

class SyncSendMailSource extends SyncObject {
    public $folderid;
    public $itemid;
    public $longid;
    public $instanceid;

    function __construct() {
        $mapping = array (
                    SYNC_COMPOSEMAIL_FOLDERID                             => array (  self::STREAMER_VAR      => "folderid"),
                    SYNC_COMPOSEMAIL_ITEMID                               => array (  self::STREAMER_VAR      => "itemid"),
                    SYNC_COMPOSEMAIL_LONGID                               => array (  self::STREAMER_VAR      => "longid"),
                    SYNC_COMPOSEMAIL_INSTANCEID                           => array (  self::STREAMER_VAR      => "instanceid"),
        );

        parent::__construct($mapping);
    }

}
