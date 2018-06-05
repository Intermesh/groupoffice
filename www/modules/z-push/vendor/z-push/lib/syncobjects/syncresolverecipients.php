<?php
/***********************************************
* File      :   syncresolverecipents.php
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

class SyncResolveRecipients extends SyncObject {
    public $to = array();
    public $options;
    public $status;
    public $response;

    public function __construct() {
        $mapping = array (
            SYNC_RESOLVERECIPIENTS_TO                       => array (  self::STREAMER_VAR      => "to",
                                                                        self::STREAMER_ARRAY    => SYNC_RESOLVERECIPIENTS_TO,
                                                                        self::STREAMER_PROP     => self::STREAMER_TYPE_NO_CONTAINER),

            SYNC_RESOLVERECIPIENTS_OPTIONS                  => array (  self::STREAMER_VAR      => "options",
                                                                        self::STREAMER_TYPE     => "SyncResolveRecipientsOptions"),

            SYNC_RESOLVERECIPIENTS_STATUS                   => array (  self::STREAMER_VAR      => "status"),

            SYNC_RESOLVERECIPIENTS_RESPONSE                 => array (  self::STREAMER_VAR      => "response",
                                                                        self::STREAMER_TYPE     => "SyncResolveRecipientsResponse",
                                                                        self::STREAMER_ARRAY    => SYNC_RESOLVERECIPIENTS_RESPONSE),
        );

        parent::__construct($mapping);
    }

}
