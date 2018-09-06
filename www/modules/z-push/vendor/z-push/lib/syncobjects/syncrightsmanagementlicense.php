<?php
/***********************************************
* File      :   syncrightsmanagementlicense.php
* Project   :   Z-Push
* Descr     :   WBXML rights management license entities
*               that can be parsed directly (as a stream)
*               from WBXML.
*               It is automatically decoded
*               according to $mapping,
*               and the Sync WBXML mappings
*
* Created   :   26.06.2017
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

class SyncRightsManagementLicense extends SyncObject {

    public $contentExpiryDate;
    public $contentOwner;
    public $editAllowed;
    public $exportAllowed;
    public $extractAllowed;
    public $forwardAllowed;
    public $modifyRecipientsAllowed;
    public $owner;
    public $printAllowed;
    public $programmaticAccessAllowed;
    public $replyAllAllowed;
    public $replyAllowed;
    public $description;
    public $id;
    public $name;

    public function __construct() {
        $mapping = array (
            SYNC_RIGHTSMANAGEMENT_CONTENTEXPIRYDATE         => array (  self::STREAMER_VAR      => "contentExpiryDate",
                                                                        self::STREAMER_TYPE     => self::STREAMER_TYPE_DATE),
            SYNC_RIGHTSMANAGEMENT_CONTENTOWNER              => array (  self::STREAMER_VAR      => "contentOwner",
                                                                        self::STREAMER_CHECKS   => array( self::STREAMER_CHECK_LENGTHMAX      => 320 )),
            SYNC_RIGHTSMANAGEMENT_EDITALLOWED               => array (  self::STREAMER_VAR      => "editAllowed",
                                                                        self::STREAMER_CHECKS   => array( self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),
            SYNC_RIGHTSMANAGEMENT_EXPORTALLOWED             => array (  self::STREAMER_VAR      => "exportAllowed",
                                                                        self::STREAMER_CHECKS   => array( self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),
            SYNC_RIGHTSMANAGEMENT_EXTRACTALLOWED            => array (  self::STREAMER_VAR      => "extractAllowed",
                                                                        self::STREAMER_CHECKS   => array( self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),
            SYNC_RIGHTSMANAGEMENT_FORWARDALLOWED            => array (  self::STREAMER_VAR      => "forwardAllowed",
                                                                        self::STREAMER_CHECKS   => array( self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),
            SYNC_RIGHTSMANAGEMENT_MODIFYRECIPIENTSALLOWED   => array (  self::STREAMER_VAR      => "modifyRecipientsAllowed",
                                                                        self::STREAMER_CHECKS   => array( self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),
            SYNC_RIGHTSMANAGEMENT_OWNER                     => array (  self::STREAMER_VAR      => "owner",
                                                                        self::STREAMER_CHECKS   => array( self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),
            SYNC_RIGHTSMANAGEMENT_PRINTALLOWED              => array (  self::STREAMER_VAR      => "printAllowed",
                                                                        self::STREAMER_CHECKS   => array( self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),
            SYNC_RIGHTSMANAGEMENT_PROGRAMMATICACCESSALLOWED => array (  self::STREAMER_VAR      => "programmaticAccessAllowed",
                                                                        self::STREAMER_CHECKS   => array( self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),
            SYNC_RIGHTSMANAGEMENT_REPLYALLALLOWED           => array (  self::STREAMER_VAR      => "replyAllAllowed",
                                                                        self::STREAMER_CHECKS   => array( self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),
            SYNC_RIGHTSMANAGEMENT_REPLYALLOWED              => array (  self::STREAMER_VAR      => "replyAllowed",
                                                                        self::STREAMER_CHECKS   => array( self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),
            SYNC_RIGHTSMANAGEMENT_TEMPLATEDESCRIPTION       => array (  self::STREAMER_VAR      => "description",
                                                                        self::STREAMER_CHECKS   => array( self::STREAMER_CHECK_LENGTHMAX      => 10240 )),
            SYNC_RIGHTSMANAGEMENT_TEMPLATEID                => array (  self::STREAMER_VAR      => "id"),
            SYNC_RIGHTSMANAGEMENT_TEMPLATENAME              => array (  self::STREAMER_VAR      => "name",
                                                                        self::STREAMER_CHECKS   => array( self::STREAMER_CHECK_LENGTHMAX      => 256 )),
        );

        parent::__construct($mapping);
    }
}