<?php
/***********************************************
* File      :   syncbaseattachment.php
* Project   :   Z-Push
* Descr     :   WBXML AirSyncBase attachment entities that can be parsed
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

class SyncBaseAttachment extends SyncObject {
    public $displayname;
    public $filereference;
    public $method;
    public $estimatedDataSize;
    public $contentid;
    public $contentlocation;
    public $isinline;

    function __construct() {
        $mapping = array(
                    SYNC_AIRSYNCBASE_DISPLAYNAME                        => array (self::STREAMER_VAR        => "displayname"),
                    SYNC_AIRSYNCBASE_FILEREFERENCE                      => array (self::STREAMER_VAR        => "filereference"),
                    SYNC_AIRSYNCBASE_METHOD                             => array (self::STREAMER_VAR        => "method"),
                    SYNC_AIRSYNCBASE_ESTIMATEDDATASIZE                  => array (self::STREAMER_VAR        => "estimatedDataSize"),
                    SYNC_AIRSYNCBASE_CONTENTID                          => array (self::STREAMER_VAR        => "contentid"),
                    SYNC_AIRSYNCBASE_CONTENTLOCATION                    => array (self::STREAMER_VAR        => "contentlocation"),
                    SYNC_AIRSYNCBASE_ISINLINE                           => array (self::STREAMER_VAR        => "isinline"),
        );

        parent::__construct($mapping);
    }
}
