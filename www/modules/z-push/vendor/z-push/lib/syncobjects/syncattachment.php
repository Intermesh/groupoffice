<?php
/***********************************************
* File      :   syncattachment.php
* Project   :   Z-Push
* Descr     :   WBXML mail attachment entities that can be parsed
*               directly (as a stream) from WBXML.
*               It is automatically decoded
*               according to $mapping,
*               and the Sync WBXML mappings.
*
* Created   :   05.09.2011
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

class SyncAttachment extends SyncObject {
    public $attmethod;
    public $attsize;
    public $displayname;
    public $attname;
    public $attoid;
    public $attremoved;

    function __construct() {
        $mapping = array(
                    SYNC_POOMMAIL_ATTMETHOD                             => array (  self::STREAMER_VAR      => "attmethod",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMMAIL_ATTSIZE                               => array (  self::STREAMER_VAR      => "attsize",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_REQUIRED   => self::STREAMER_CHECK_SETZERO,
                                                                                                                        self::STREAMER_CHECK_CMPHIGHER  => -1 )),

                    SYNC_POOMMAIL_DISPLAYNAME                           => array (  self::STREAMER_VAR      => "displayname",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_REQUIRED   => self::STREAMER_CHECK_SETEMPTY)),

                    SYNC_POOMMAIL_ATTNAME                               => array (  self::STREAMER_VAR      => "attname",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_REQUIRED   => self::STREAMER_CHECK_SETEMPTY)),

                    SYNC_POOMMAIL_ATTOID                                => array (  self::STREAMER_VAR      => "attoid"),
                    SYNC_POOMMAIL_ATTREMOVED                            => array (  self::STREAMER_VAR      => "attremoved",
                                                                                    self::STREAMER_RONOTIFY => true),
                );

        parent::__construct($mapping);
    }
}
