<?php
/***********************************************
* File      :   syncrightsmanagementtemplate.php
* Project   :   Z-Push
* Descr     :   WBXML rights management template entities
*               that can be parsed directly (as a stream)
*               from WBXML.
*               It is automatically decoded
*               according to $mapping,
*               and the Sync WBXML mappings
*
* Created   :   16.06.2017
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

class SyncRightsManagementTemplate extends SyncObject {

    public $description;
    public $id;
    public $name;

    public function __construct() {
        $mapping = array (
            SYNC_RIGHTSMANAGEMENT_TEMPLATEDESCRIPTION   => array (  self::STREAMER_VAR      => "description",
                                                                    self::STREAMER_CHECKS   => array( self::STREAMER_CHECK_LENGTHMAX      => 10240 )),
            SYNC_RIGHTSMANAGEMENT_TEMPLATEID            => array (  self::STREAMER_VAR      => "id"),
            SYNC_RIGHTSMANAGEMENT_TEMPLATENAME          => array (  self::STREAMER_VAR      => "name",
                                                                    self::STREAMER_CHECKS   => array( self::STREAMER_CHECK_LENGTHMAX      => 256 )),
        );

        parent::__construct($mapping);
    }
}