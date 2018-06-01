<?php
/***********************************************
* File      :   synccontact.php
* Project   :   Z-Push
* Descr     :   WBXML contact entities that can be parsed
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

class SyncContact extends SyncObject {
    public $anniversary;
    public $assistantname;
    public $assistnamephonenumber;
    public $birthday;
    public $body;
    public $bodysize;
    public $bodytruncated;
    public $business2phonenumber;
    public $businesscity;
    public $businesscountry;
    public $businesspostalcode;
    public $businessstate;
    public $businessstreet;
    public $businessfaxnumber;
    public $businessphonenumber;
    public $carphonenumber;
    public $children;
    public $companyname;
    public $department;
    public $email1address;
    public $email2address;
    public $email3address;
    public $fileas;
    public $firstname;
    public $home2phonenumber;
    public $homecity;
    public $homecountry;
    public $homepostalcode;
    public $homestate;
    public $homestreet;
    public $homefaxnumber;
    public $homephonenumber;
    public $jobtitle;
    public $lastname;
    public $middlename;
    public $mobilephonenumber;
    public $officelocation;
    public $othercity;
    public $othercountry;
    public $otherpostalcode;
    public $otherstate;
    public $otherstreet;
    public $pagernumber;
    public $radiophonenumber;
    public $spouse;
    public $suffix;
    public $title;
    public $webpage;
    public $yomicompanyname;
    public $yomifirstname;
    public $yomilastname;
    public $rtf;
    public $picture;
    public $categories;

    // AS 2.5 props
    public $customerid;
    public $governmentid;
    public $imaddress;
    public $imaddress2;
    public $imaddress3;
    public $managername;
    public $companymainphone;
    public $accountname;
    public $nickname;
    public $mms;

    // AS 12.0 props
    public $asbody;

    function __construct() {
        $mapping = array (
                    SYNC_POOMCONTACTS_ANNIVERSARY                       => array (  self::STREAMER_VAR      => "anniversary",
                                                                                    self::STREAMER_TYPE     => self::STREAMER_TYPE_DATE_DASHES,
                                                                                    self::STREAMER_RONOTIFY => true),

                    SYNC_POOMCONTACTS_ASSISTANTNAME                     => array (  self::STREAMER_VAR      => "assistantname",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_ASSISTNAMEPHONENUMBER             => array (  self::STREAMER_VAR      => "assistnamephonenumber",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_BIRTHDAY                          => array (  self::STREAMER_VAR      => "birthday",
                                                                                    self::STREAMER_TYPE     => self::STREAMER_TYPE_DATE_DASHES,
                                                                                    self::STREAMER_RONOTIFY => true),

                    SYNC_POOMCONTACTS_BODY                              => array (  self::STREAMER_VAR      => "body",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_BODYSIZE                          => array (  self::STREAMER_VAR      => "bodysize"),
                    SYNC_POOMCONTACTS_BODYTRUNCATED                     => array (  self::STREAMER_VAR      => "bodytruncated"),
                    SYNC_POOMCONTACTS_BUSINESS2PHONENUMBER              => array (  self::STREAMER_VAR      => "business2phonenumber",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_BUSINESSCITY                      => array (  self::STREAMER_VAR      => "businesscity",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_BUSINESSCOUNTRY                   => array (  self::STREAMER_VAR      => "businesscountry",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_BUSINESSPOSTALCODE                => array (  self::STREAMER_VAR      => "businesspostalcode",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_BUSINESSSTATE                     => array (  self::STREAMER_VAR      => "businessstate",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_BUSINESSSTREET                    => array (  self::STREAMER_VAR      => "businessstreet",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_BUSINESSFAXNUMBER                 => array (  self::STREAMER_VAR      => "businessfaxnumber",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_BUSINESSPHONENUMBER               => array (  self::STREAMER_VAR      => "businessphonenumber",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_CARPHONENUMBER                    => array (  self::STREAMER_VAR      => "carphonenumber",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_CHILDREN                          => array (  self::STREAMER_VAR      => "children",
                                                                                    self::STREAMER_ARRAY    => SYNC_POOMCONTACTS_CHILD,
                                                                                    self::STREAMER_RONOTIFY => true),

                    SYNC_POOMCONTACTS_COMPANYNAME                       => array (  self::STREAMER_VAR      => "companyname",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_DEPARTMENT                        => array (  self::STREAMER_VAR      => "department",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_EMAIL1ADDRESS                     => array (  self::STREAMER_VAR      => "email1address",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_EMAIL2ADDRESS                     => array (  self::STREAMER_VAR      => "email2address",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_EMAIL3ADDRESS                     => array (  self::STREAMER_VAR      => "email3address",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_FILEAS                            => array (  self::STREAMER_VAR      => "fileas",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_FIRSTNAME                         => array (  self::STREAMER_VAR      => "firstname",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_HOME2PHONENUMBER                  => array (  self::STREAMER_VAR      => "home2phonenumber",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_HOMECITY                          => array (  self::STREAMER_VAR      => "homecity",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_HOMECOUNTRY                       => array (  self::STREAMER_VAR      => "homecountry",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_HOMEPOSTALCODE                    => array (  self::STREAMER_VAR      => "homepostalcode",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_HOMESTATE                         => array (  self::STREAMER_VAR      => "homestate",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_HOMESTREET                        => array (  self::STREAMER_VAR      => "homestreet",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_HOMEFAXNUMBER                     => array (  self::STREAMER_VAR      => "homefaxnumber",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_HOMEPHONENUMBER                   => array (  self::STREAMER_VAR      => "homephonenumber",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_JOBTITLE                          => array (  self::STREAMER_VAR      => "jobtitle",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_LASTNAME                          => array (  self::STREAMER_VAR      => "lastname",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_MIDDLENAME                        => array (  self::STREAMER_VAR      => "middlename",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_MOBILEPHONENUMBER                 => array (  self::STREAMER_VAR      => "mobilephonenumber",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_OFFICELOCATION                    => array (  self::STREAMER_VAR      => "officelocation",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_OTHERCITY                         => array (  self::STREAMER_VAR      => "othercity",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_OTHERCOUNTRY                      => array (  self::STREAMER_VAR      => "othercountry",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_OTHERPOSTALCODE                   => array (  self::STREAMER_VAR      => "otherpostalcode",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_OTHERSTATE                        => array (  self::STREAMER_VAR      => "otherstate",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_OTHERSTREET                       => array (  self::STREAMER_VAR      => "otherstreet",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_PAGERNUMBER                       => array (  self::STREAMER_VAR      => "pagernumber",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_RADIOPHONENUMBER                  => array (  self::STREAMER_VAR      => "radiophonenumber",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_SPOUSE                            => array (  self::STREAMER_VAR      => "spouse",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_SUFFIX                            => array (  self::STREAMER_VAR      => "suffix",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_TITLE                             => array (  self::STREAMER_VAR      => "title",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_WEBPAGE                           => array (  self::STREAMER_VAR      => "webpage",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_YOMICOMPANYNAME                   => array (  self::STREAMER_VAR      => "yomicompanyname",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_YOMIFIRSTNAME                     => array (  self::STREAMER_VAR      => "yomifirstname",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_YOMILASTNAME                      => array (  self::STREAMER_VAR      => "yomilastname",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMCONTACTS_RTF                               => array (  self::STREAMER_VAR      => "rtf"),
                    SYNC_POOMCONTACTS_PICTURE                           => array (  self::STREAMER_VAR      => "picture",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_LENGTHMAX      => SYNC_CONTACTS_MAXPICTURESIZE ),
                                                                                    self::STREAMER_RONOTIFY => true),

                    SYNC_POOMCONTACTS_CATEGORIES                        => array (  self::STREAMER_VAR      => "categories",
                                                                                    self::STREAMER_ARRAY    => SYNC_POOMCONTACTS_CATEGORY ,
                                                                                    self::STREAMER_RONOTIFY => true),
                );

        if (Request::GetProtocolVersion() >= 2.5) {
            $mapping[SYNC_POOMCONTACTS2_CUSTOMERID]                     = array (   self::STREAMER_VAR      => "customerid",        self::STREAMER_RONOTIFY => true);
            $mapping[SYNC_POOMCONTACTS2_GOVERNMENTID]                   = array (   self::STREAMER_VAR      => "governmentid",      self::STREAMER_RONOTIFY => true);
            $mapping[SYNC_POOMCONTACTS2_IMADDRESS]                      = array (   self::STREAMER_VAR      => "imaddress",         self::STREAMER_RONOTIFY => true);
            $mapping[SYNC_POOMCONTACTS2_IMADDRESS2]                     = array (   self::STREAMER_VAR      => "imaddress2",        self::STREAMER_RONOTIFY => true);
            $mapping[SYNC_POOMCONTACTS2_IMADDRESS3]                     = array (   self::STREAMER_VAR      => "imaddress3",        self::STREAMER_RONOTIFY => true);
            $mapping[SYNC_POOMCONTACTS2_MANAGERNAME]                    = array (   self::STREAMER_VAR      => "managername",       self::STREAMER_RONOTIFY => true);
            $mapping[SYNC_POOMCONTACTS2_COMPANYMAINPHONE]               = array (   self::STREAMER_VAR      => "companymainphone",  self::STREAMER_RONOTIFY => true);
            $mapping[SYNC_POOMCONTACTS2_ACCOUNTNAME]                    = array (   self::STREAMER_VAR      => "accountname",       self::STREAMER_RONOTIFY => true);
            $mapping[SYNC_POOMCONTACTS2_NICKNAME]                       = array (   self::STREAMER_VAR      => "nickname",          self::STREAMER_RONOTIFY => true);
            $mapping[SYNC_POOMCONTACTS2_MMS]                            = array (   self::STREAMER_VAR      => "mms",               self::STREAMER_RONOTIFY => true);
        }

        if (Request::GetProtocolVersion() >= 12.0) {
            $mapping[SYNC_AIRSYNCBASE_BODY]                             = array (   self::STREAMER_VAR      => "asbody",
                                                                                    self::STREAMER_TYPE     => "SyncBaseBody",
                                                                                    self::STREAMER_RONOTIFY => true);

            //unset these properties because airsyncbase body and attachments will be used instead
            unset($mapping[SYNC_POOMCONTACTS_BODY], $mapping[SYNC_POOMCONTACTS_BODYTRUNCATED]);
        }

        parent::__construct($mapping);
    }
}
