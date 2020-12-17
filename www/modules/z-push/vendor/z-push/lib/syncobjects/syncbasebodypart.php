<?php
/***********************************************
* File      :   syncbasebodypart.php
* Project   :   Z-Push
* Descr     :   WBXML AirSyncBase body part entities that can be parsed
*               directly (as a stream) from WBXML.
*               It is automatically decoded according to $mapping,
*               and the Sync WBXML mappings.
*
* Created   :   11.07.2017
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

class SyncBaseBodyPart extends SyncObject {
    public $status;
    public $type; // Should be html (2)
    public $estimatedDataSize;
    public $truncated;
    public $data;
    public $preview;

    function __construct() {
        $mapping = array(
                    SYNC_AIRSYNCBASE_STATUS                             => array (  self::STREAMER_VAR        => "status"),
                    SYNC_AIRSYNCBASE_TYPE                               => array (  self::STREAMER_VAR        => "type"),
                    SYNC_AIRSYNCBASE_ESTIMATEDDATASIZE                  => array (  self::STREAMER_VAR        => "estimatedDataSize",
                                                                                    self::STREAMER_PRIVATE    => strlen(self::STRIP_PRIVATE_SUBSTITUTE)),          // when stripping private we set the body to self::STRIP_PRIVATE_SUBSTITUTE, so the size needs to be its length
                    SYNC_AIRSYNCBASE_TRUNCATED                          => array (  self::STREAMER_VAR        => "truncated"),
                    SYNC_AIRSYNCBASE_DATA                               => array (  self::STREAMER_VAR        => "data",
                                                                                    self::STREAMER_TYPE       => self::STREAMER_TYPE_STREAM_ASPLAIN,
                                                                                    self::STREAMER_PROP       => self::STREAMER_TYPE_MULTIPART,
                                                                                    self::STREAMER_RONOTIFY   => true,
                                                                                    self::STREAMER_PRIVATE    => StringStreamWrapper::Open(self::STRIP_PRIVATE_SUBSTITUTE)),       // replace the body with self::STRIP_PRIVATE_SUBSTITUTE when stripping private
                    SYNC_AIRSYNCBASE_PREVIEW                            => array (  self::STREAMER_VAR        => "preview",
                                                                                    self::STREAMER_PRIVATE    => self::STRIP_PRIVATE_SUBSTITUTE)
        );

        parent::__construct($mapping);

        // Indicates that this SyncObject supports the private flag and stripping of private data.
        $this->supportsPrivateStripping = true;
    }
}
