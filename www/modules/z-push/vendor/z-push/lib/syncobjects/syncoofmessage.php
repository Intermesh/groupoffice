<?php
/***********************************************
* File      :   syncoofmessage.php
* Project   :   Z-Push
* Descr     :   WBXML appointment entities that can be
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

class SyncOOFMessage extends SyncObject {
    public $appliesToInternal;
    public $appliesToExternal;
    public $appliesToExternalUnknown;
    public $enabled;
    public $replymessage;
    public $bodytype;

    public function __construct() {
        $mapping = array (
            //only one of the following 3 apply types will be available
            SYNC_SETTINGS_APPLIESTOINTERVAL             => array (  self::STREAMER_VAR      => "appliesToInternal",
                                                                    self::STREAMER_PROP     => self::STREAMER_TYPE_SEND_EMPTY),

            SYNC_SETTINGS_APPLIESTOEXTERNALKNOWN        => array (  self::STREAMER_VAR      => "appliesToExternal",
                                                                    self::STREAMER_PROP     => self::STREAMER_TYPE_SEND_EMPTY),

            SYNC_SETTINGS_APPLIESTOEXTERNALUNKNOWN      => array (  self::STREAMER_VAR      => "appliesToExternalUnknown",
                                                                    self::STREAMER_PROP     => self::STREAMER_TYPE_SEND_EMPTY),

            SYNC_SETTINGS_ENABLED                       => array (  self::STREAMER_VAR      => "enabled"),

            SYNC_SETTINGS_REPLYMESSAGE                  => array (  self::STREAMER_VAR      => "replymessage"),

            SYNC_SETTINGS_BODYTYPE                      => array (  self::STREAMER_VAR      => "bodytype",
                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(SYNC_SETTINGSOOF_BODYTYPE_HTML, ucfirst(strtolower(SYNC_SETTINGSOOF_BODYTYPE_TEXT))) )),

        );

        parent::__construct($mapping);
    }

}
