<?php
/***********************************************
* File      :   syncitemoperationsattachment.php
* Project   :   Z-Push
* Descr     :   WBXML ItemOperations attachment entities that can be parsed
*               directly (as a stream) from WBXML.
*               It is automatically decoded according to $mapping,
*               and the Sync WBXML mappings.
*
* Created   :   24.11.2011
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

class SyncItemOperationsAttachment extends SyncObject {
    public $contenttype;
    public $data;

    function __construct() {
        $mapping = array(
            SYNC_AIRSYNCBASE_CONTENTTYPE                        => array (  self::STREAMER_VAR      => "contenttype"),
            SYNC_ITEMOPERATIONS_DATA                            => array (  self::STREAMER_VAR      => "data",
                                                                            self::STREAMER_TYPE     => self::STREAMER_TYPE_STREAM_ASBASE64,
                                                                            self::STREAMER_PROP     => self::STREAMER_TYPE_MULTIPART),
        );

        parent::__construct($mapping);
    }
}
