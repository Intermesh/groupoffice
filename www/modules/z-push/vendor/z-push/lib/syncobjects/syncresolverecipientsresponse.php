<?php
/**********************************************************
* File      :   syncresolverecipientsresponse.php
* Project   :   Z-Push
* Descr     :   WBXML appointment entities that can be
*               parsed directly (as a stream) from WBXML.
*               It is automatically decoded
*               according to $mapping,
*               and the Sync WBXML mappings
*
* Created   :   07.09.2015
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

class SyncResolveRecipientsResponse extends SyncObject {
    public $to;
    public $status;
    public $recipientcount;
    public $recipient;

    public function __construct() {
        $mapping = array (
            SYNC_RESOLVERECIPIENTS_TO                       => array (  self::STREAMER_VAR      => "to"),

            SYNC_RESOLVERECIPIENTS_STATUS                   => array (  self::STREAMER_VAR      => "status"),

            SYNC_RESOLVERECIPIENTS_RECIPIENTCOUNT           => array (  self::STREAMER_VAR      => "recipientcount"),

            SYNC_RESOLVERECIPIENTS_RECIPIENT                => array (  self::STREAMER_VAR      => "recipient",
                                                                        self::STREAMER_TYPE     => "SyncResolveRecipient",
                                                                        self::STREAMER_ARRAY    => SYNC_RESOLVERECIPIENTS_RECIPIENT),
        );

        parent::__construct($mapping);
    }
}