<?php
/***********************************************
* File      :   mapimapping.php
* Project   :   Z-Push
* Descr     :
*
* Created   :   29.04.2011
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
/**
 *
 * MAPI to AS mapping class
 *
 *
 */
class MAPIMapping {
    /**
     * Returns the MAPI to AS mapping for contacts
     *
     * @return array
     */
    public static function GetContactMapping() {
        return array (
            "anniversary"           => PR_WEDDING_ANNIVERSARY,
            "assistantname"         => PR_ASSISTANT,
            "assistnamephonenumber" => PR_ASSISTANT_TELEPHONE_NUMBER,
            "birthday"              => PR_BIRTHDAY,
            "body"                  => PR_BODY,
            "business2phonenumber"  => PR_BUSINESS2_TELEPHONE_NUMBER,
            "businesscity"          => "PT_STRING8:PSETID_Address:0x8046",
            "businesscountry"       => "PT_STRING8:PSETID_Address:0x8049",
            "businesspostalcode"    => "PT_STRING8:PSETID_Address:0x8048",
            "businessstate"         => "PT_STRING8:PSETID_Address:0x8047",
            "businessstreet"        => "PT_STRING8:PSETID_Address:0x8045",
            "businessfaxnumber"     => PR_BUSINESS_FAX_NUMBER,
            "businessphonenumber"   => PR_OFFICE_TELEPHONE_NUMBER,
            "carphonenumber"        => PR_CAR_TELEPHONE_NUMBER,
            "categories"            => "PT_MV_STRING8:PS_PUBLIC_STRINGS:Keywords",
            "children"              => PR_CHILDRENS_NAMES,
            "companyname"           => PR_COMPANY_NAME,
            "department"            => PR_DEPARTMENT_NAME,
            "email1address"         => "PT_STRING8:PSETID_Address:0x8083",
            "email2address"         => "PT_STRING8:PSETID_Address:0x8093",
            "email3address"         => "PT_STRING8:PSETID_Address:0x80A3",
            "fileas"                => "PT_STRING8:PSETID_Address:0x8005",
            "firstname"             => PR_GIVEN_NAME,
            "home2phonenumber"      => PR_HOME2_TELEPHONE_NUMBER,
            "homecity"              => PR_HOME_ADDRESS_CITY,
            "homecountry"           => PR_HOME_ADDRESS_COUNTRY,
            "homepostalcode"        => PR_HOME_ADDRESS_POSTAL_CODE,
            "homestate"             => PR_HOME_ADDRESS_STATE_OR_PROVINCE,
            "homestreet"            => PR_HOME_ADDRESS_STREET,
            "homefaxnumber"         => PR_HOME_FAX_NUMBER,
            "homephonenumber"       => PR_HOME_TELEPHONE_NUMBER,
            "jobtitle"              => PR_TITLE,
            "lastname"              => PR_SURNAME,
            "middlename"            => PR_MIDDLE_NAME,
            "mobilephonenumber"     => PR_CELLULAR_TELEPHONE_NUMBER,
            "officelocation"        => PR_OFFICE_LOCATION,
            "othercity"             => PR_OTHER_ADDRESS_CITY,
            "othercountry"          => PR_OTHER_ADDRESS_COUNTRY,
            "otherpostalcode"       => PR_OTHER_ADDRESS_POSTAL_CODE,
            "otherstate"            => PR_OTHER_ADDRESS_STATE_OR_PROVINCE,
            "otherstreet"           => PR_OTHER_ADDRESS_STREET,
            "pagernumber"           => PR_PAGER_TELEPHONE_NUMBER,
            "radiophonenumber"      => PR_RADIO_TELEPHONE_NUMBER,
            "spouse"                => PR_SPOUSE_NAME,
            "suffix"                => PR_GENERATION,
            "title"                 => PR_DISPLAY_NAME_PREFIX,
            "webpage"               => "PT_STRING8:PSETID_Address:0x802b",
            "yomicompanyname"       => "PT_STRING8:PSETID_Address:0x802e",
            "yomifirstname"         => "PT_STRING8:PSETID_Address:0x802c",
            "yomilastname"          => "PT_STRING8:PSETID_Address:0x802d",
            "rtf"                   => PR_RTF_COMPRESSED,
            // picture
            "customerid"            => PR_CUSTOMER_ID,
            "governmentid"          => PR_GOVERNMENT_ID_NUMBER,
            "imaddress"             => "PT_STRING8:PSETID_Address:0x8062",
            "imaddress2"            => "PT_STRING8:PSETID_AirSync:IMAddress2",
            "imaddress3"            => "PT_STRING8:PSETID_AirSync:IMAddress3",
            "managername"           => PR_MANAGER_NAME,
            "companymainphone"      => PR_COMPANY_MAIN_PHONE_NUMBER,
            "accountname"           => PR_ACCOUNT,
            "nickname"              => PR_NICKNAME,
            // mms
            );
    }


    /**
     *
     * Returns contact specific MAPI properties
     *
     * @access public
     *
     * @return array
     */
    public static function GetContactProperties() {
        return array (
            "haspic"                => "PT_BOOLEAN:PSETID_Address:0x8015",
            "emailaddress1"         => "PT_STRING8:PSETID_Address:0x8083",
            "emailaddressdname1"    => "PT_STRING8:PSETID_Address:0x8080",
            "emailaddressdemail1"   => "PT_STRING8:PSETID_Address:0x8084",
            "emailaddresstype1"     => "PT_STRING8:PSETID_Address:0x8082",
            "emailaddressentryid1"  => "PT_BINARY:PSETID_Address:0x8085",
            "emailaddress2"         => "PT_STRING8:PSETID_Address:0x8093",
            "emailaddressdname2"    => "PT_STRING8:PSETID_Address:0x8090",
            "emailaddressdemail2"   => "PT_STRING8:PSETID_Address:0x8094",
            "emailaddresstype2"     => "PT_STRING8:PSETID_Address:0x8092",
            "emailaddressentryid2"  => "PT_BINARY:PSETID_Address:0x8095",
            "emailaddress3"         => "PT_STRING8:PSETID_Address:0x80a3",
            "emailaddressdname3"    => "PT_STRING8:PSETID_Address:0x80a0",
            "emailaddressdemail3"   => "PT_STRING8:PSETID_Address:0x80a4",
            "emailaddresstype3"     => "PT_STRING8:PSETID_Address:0x80a2",
            "emailaddressentryid3"  => "PT_BINARY:PSETID_Address:0x80a5",
            "addressbookmv"         => "PT_MV_LONG:PSETID_Address:0x8028",
            "addressbooklong"       => "PT_LONG:PSETID_Address:0x8029",
            "displayname"           => PR_DISPLAY_NAME,
            "subject"               => PR_SUBJECT,
            "country"               => PR_COUNTRY,
            "city"                  => PR_LOCALITY,
            "postaladdress"         => PR_POSTAL_ADDRESS,
            "postalcode"            => PR_POSTAL_CODE,
            "state"                 => PR_STATE_OR_PROVINCE,
            "street"                => PR_STREET_ADDRESS,
            "homeaddress"           => "PT_STRING8:PSETID_Address:0x801a",
            "businessaddress"       => "PT_STRING8:PSETID_Address:0x801b",
            "otheraddress"          => "PT_STRING8:PSETID_Address:0x801c",
            "mailingaddress"        => "PT_LONG:PSETID_Address:0x8022",
        );
    }


    /**
     * Returns the MAPI to AS mapping for emails
     *
     * @return array
     */
    public static function GetEmailMapping() {
        return array (
            // from
            "datereceived"          => PR_MESSAGE_DELIVERY_TIME,
            "displayname"           => PR_SUBJECT,
            "displayto"             => PR_DISPLAY_TO,
            "importance"            => PR_IMPORTANCE,
            "messageclass"          => PR_MESSAGE_CLASS,
            "subject"               => PR_SUBJECT,
            "read"                  => PR_MESSAGE_FLAGS,
            // "to" // need to be generated with SMTP addresses
            // "cc"
            // "threadtopic"        => PR_CONVERSATION_TOPIC,
            "internetcpid"          => PR_INTERNET_CPID,
            "nativebodytype"        => PR_NATIVE_BODY_INFO,
            "lastverbexecuted"      => PR_LAST_VERB_EXECUTED,
            "lastverbexectime"      => PR_LAST_VERB_EXECUTION_TIME,
            "categories"            => "PT_MV_STRING8:PS_PUBLIC_STRINGS:Keywords",
            );
    }


    /**
     *
     * Returns email specific MAPI properties
     *
     * @access public
     *
     * @return array
     */
    public static function GetEmailProperties() {
        return array (
            // Override 'From' to show "Full Name <user@domain.com>"
            "representingname"      => PR_SENT_REPRESENTING_NAME,
            "representingentryid"   => PR_SENT_REPRESENTING_ENTRYID,
            "representingsearchkey" => PR_SENT_REPRESENTING_SEARCH_KEY,
            "sourcekey"             => PR_SOURCE_KEY,
            "entryid"               => PR_ENTRYID,
            "parentsourcekey"       => PR_PARENT_SOURCE_KEY,
            "body"                  => PR_BODY,
            "rtfcompressed"         => PR_RTF_COMPRESSED,
            "html"                  => PR_HTML,
            "rtfinsync"             => PR_RTF_IN_SYNC,
            "processed"             => PR_PROCESSED,
        );
    }


    /**
     * Returns the MAPI to AS mapping for meeting requests
     *
     * @return array
     */
    public static function GetMeetingRequestMapping() {
        return array (
            "responserequested"     => PR_RESPONSE_REQUESTED,
            // timezone
            "alldayevent"           => "PT_BOOLEAN:PSETID_Appointment:0x8215",
            "busystatus"            => "PT_LONG:PSETID_Appointment:0x8224",
            "rtf"                   => PR_RTF_COMPRESSED,
            "dtstamp"               => PR_LAST_MODIFICATION_TIME,
            "endtime"               => "PT_SYSTIME:PSETID_Appointment:0x820e",
            "location"              => "PT_STRING8:PSETID_Appointment:0x8208",
            // recurrences
            "reminder"              => "PT_LONG:PSETID_Common:0x8501",
            "starttime"             => "PT_SYSTIME:PSETID_Appointment:0x820d",
            "sensitivity"           => PR_SENSITIVITY,
            );
    }


    public static function GetMeetingRequestProperties() {
        return array (
            "goidtag"               => "PT_BINARY:PSETID_Meeting:0x3",
            "timezonetag"           => "PT_BINARY:PSETID_Appointment:0x8233",
            "recReplTime"           => "PT_SYSTIME:PSETID_Appointment:0x8228",
            "isrecurringtag"        => "PT_BOOLEAN:PSETID_Appointment:0x8223",
            "recurringstate"        => "PT_BINARY:PSETID_Appointment:0x8216",
            "appSeqNr"              => "PT_LONG:PSETID_Appointment:0x8201",
            "lidIsException"        => "PT_BOOLEAN:PSETID_Appointment:0xA",
            "recurStartTime"        => "PT_LONG:PSETID_Meeting:0xE",
            "reminderset"           => "PT_BOOLEAN:PSETID_Common:0x8503",
            "remindertime"          => "PT_LONG:PSETID_Common:0x8501",
            "recurrenceend"         => "PT_SYSTIME:PSETID_Appointment:0x8236",
            "meetingType"           => "PT_LONG:PSETID_Meeting:0x26",
            );
    }


    public static function GetTnefAndIcalProperties() {
        return array(
            "starttime"             => "PT_SYSTIME:PSETID_Appointment:0x820d",
            "endtime"               => "PT_SYSTIME:PSETID_Appointment:0x820e",
            "commonstart"           => "PT_SYSTIME:PSETID_Common:0x8516",
            "commonend"             => "PT_SYSTIME:PSETID_Common:0x8517",
            "clipstart"             => "PT_SYSTIME:PSETID_Appointment:0x8235", //ical only
            "recurrenceend"         => "PT_SYSTIME:PSETID_Appointment:0x8236", //ical only
            "isrecurringtag"        => "PT_BOOLEAN:PSETID_Appointment:0x8223",
            "goidtag"               => "PT_BINARY:PSETID_Meeting:0x3",
            "goid2tag"              => "PT_BINARY:PSETID_Meeting:0x23",
            "usetnef"               => "PT_LONG:PSETID_Meeting:0x8582",
            "tneflocation"          => "PT_STRING8:PSETID_Meeting:0x2", //ical only
            "location"              => "PT_STRING8:PSETID_Appointment:0x8208",
            "tnefrecurr"            => "PT_BOOLEAN:PSETID_Meeting:0x5",
            "sideeffects"           => "PT_LONG:PSETID_Common:0x8510",
            "type"                  => "PT_STRING8:PSETID_Meeting:0x24",
            "busystatus"            => "PT_LONG:PSETID_Appointment:0x8205",
            "meetingstatus"         => "PT_LONG:PSETID_Appointment:0x8217",
            "responsestatus"        => "PT_LONG:PSETID_Meeting:0x8218",
            //the properties below are currently not used
            "dayinterval"           => "PT_I2:PSETID_Meeting:0x11",
            "weekinterval"          => "PT_I2:PSETID_Meeting:0x12",
            "monthinterval"         => "PT_I2:PSETID_Meeting:0x13",
            "yearinterval"          => "PT_I2:PSETID_Meeting:0x14",
        );
    }


    /**
     * Returns the MAPI to AS mapping for appointments
     *
     * @return array
     */
    public static function GetAppointmentMapping() {
        return array (
            "alldayevent"           => "PT_BOOLEAN:PSETID_Appointment:0x8215",
            "body"                  => PR_BODY,
            "busystatus"            => "PT_LONG:PSETID_Appointment:0x8205",
            "categories"            => "PT_MV_STRING8:PS_PUBLIC_STRINGS:Keywords",
            "rtf"                   => PR_RTF_COMPRESSED,
            "dtstamp"               => PR_LAST_MODIFICATION_TIME,
            "endtime"               => "PT_SYSTIME:PSETID_Appointment:0x820e",
            "location"              => "PT_STRING8:PSETID_Appointment:0x8208",
            "meetingstatus"         => "PT_LONG:PSETID_Appointment:0x8217",
            "sensitivity"           => PR_SENSITIVITY,
            "subject"               => PR_SUBJECT,
            "starttime"             => "PT_SYSTIME:PSETID_Appointment:0x820d",
            "uid"                   => "PT_BINARY:PSETID_Meeting:0x3",
            "nativebodytype"        => PR_NATIVE_BODY_INFO,
            );
    }


    /**
     *
     * Returns appointment specific MAPI properties
     *
     * @access public
     *
     * @return array
     */
    public static function GetAppointmentProperties() {
        return array(
            "sourcekey"             => PR_SOURCE_KEY,
            "representingentryid"   => PR_SENT_REPRESENTING_ENTRYID,
            "representingname"      => PR_SENT_REPRESENTING_NAME,
            "sentrepresentingemail" => PR_SENT_REPRESENTING_EMAIL_ADDRESS,
            "sentrepresentingaddt"  => PR_SENT_REPRESENTING_ADDRTYPE,
            "sentrepresentinsrchk"  => PR_SENT_REPRESENTING_SEARCH_KEY,
            "reminderset"           => "PT_BOOLEAN:PSETID_Common:0x8503",
            "remindertime"          => "PT_LONG:PSETID_Common:0x8501",
            "meetingstatus"         => "PT_LONG:PSETID_Appointment:0x8217",
            "isrecurring"           => "PT_BOOLEAN:PSETID_Appointment:0x8223",
            "recurringstate"        => "PT_BINARY:PSETID_Appointment:0x8216",
            "timezonetag"           => "PT_BINARY:PSETID_Appointment:0x8233",
            "timezonedesc"          => "PT_STRING8:PSETID_Appointment:0x8234",
            "recurrenceend"         => "PT_SYSTIME:PSETID_Appointment:0x8236",
            "responsestatus"        => "PT_LONG:PSETID_Appointment:0x8218",
            "commonstart"           => "PT_SYSTIME:PSETID_Common:0x8516",
            "commonend"             => "PT_SYSTIME:PSETID_Common:0x8517",
            "reminderstart"         => "PT_SYSTIME:PSETID_Common:0x8502",
            "duration"              => "PT_LONG:PSETID_Appointment:0x8213",
            "private"               => "PT_BOOLEAN:PSETID_Common:0x8506",
            "uid"                   => "PT_BINARY:PSETID_Meeting:0x23",
            "sideeffects"           => "PT_LONG:PSETID_Common:0x8510",
            "flagdueby"             => "PT_SYSTIME:PSETID_Common:0x8560",
            "icon"                  => PR_ICON_INDEX,
            "mrwassent"             => "PT_BOOLEAN:PSETID_Appointment:0x8229",
            "endtime"               => "PT_SYSTIME:PSETID_Appointment:0x820e",//this is here for calendar restriction, tnef and ical
            "starttime"             => "PT_SYSTIME:PSETID_Appointment:0x820d",//this is here for calendar restriction, tnef and ical
            "clipstart"             => "PT_SYSTIME:PSETID_Appointment:0x8235", //ical only
            "recurrencetype"        => "PT_LONG:PSETID_Appointment:0x8231",
            "body"                  => PR_BODY,
            "rtfcompressed"         => PR_RTF_COMPRESSED,
            "html"                  => PR_HTML,
            "rtfinsync"             => PR_RTF_IN_SYNC,
        );
    }


    /**
     * Returns the MAPI to AS mapping for tasks
     *
     * @return array
     */
    public static function GetTaskMapping() {
        return array (
            "body"                  => PR_BODY,
            "categories"            => "PT_MV_STRING8:PS_PUBLIC_STRINGS:Keywords",
            "complete"              => "PT_BOOLEAN:PSETID_Task:0x811C",
            "datecompleted"         => "PT_SYSTIME:PSETID_Task:0x810F",
            "duedate"               => "PT_SYSTIME:PSETID_Task:0x8105",
            "utcduedate"            => "PT_SYSTIME:PSETID_Common:0x8517",
            "utcstartdate"          => "PT_SYSTIME:PSETID_Common:0x8516",
            "importance"            => PR_IMPORTANCE,
            // recurrence
            // regenerate
            // deadoccur
            "reminderset"           => "PT_BOOLEAN:PSETID_Common:0x8503",
            "remindertime"          => "PT_SYSTIME:PSETID_Common:0x8502",
            "sensitivity"           => PR_SENSITIVITY,
            "startdate"             => "PT_SYSTIME:PSETID_Task:0x8104",
            "subject"               => PR_SUBJECT,
            "rtf"                   => PR_RTF_COMPRESSED,
            "html"                  => PR_HTML,
            );
    }


    /**
     * Returns task specific MAPI properties
     *
     * @access public
     *
     * @return array
     */
    public static function GetTaskProperties() {
        return array (
            "isrecurringtag"        => "PT_BOOLEAN:PSETID_Task:0x8126",
            "recurringstate"        => "PT_BINARY:PSETID_Task:0x8116",
            "deadoccur"             => "PT_BOOLEAN:PSETID_Task:0x8109",
            "completion"            => "PT_DOUBLE:PSETID_Task:0x8102",
            "status"                => "PT_LONG:PSETID_Task:0x8101",
            "icon"                  => PR_ICON_INDEX,
            "owner"                 => "PT_STRING8:PSETID_Task:0x811F",
            "private"               => "PT_BOOLEAN:PSETID_Common:0x8506",
        );
    }


    /**
    * Returns the MAPI to AS mapping for email todo flags
    *
    * @return array
    */
    public static function GetMailFlagsMapping() {
        return array (
            "flagstatus"            => PR_FLAG_STATUS,
            "flagtype"              => "PT_STRING8:PSETID_Common:0x8530",
            "datecompleted"         => "PT_SYSTIME:PSETID_Common:0x810F",
            "completetime"          => PR_FLAG_COMPLETE_TIME,
            "startdate"             => "PT_SYSTIME:PSETID_Task:0x8104",
            "duedate"               => "PT_SYSTIME:PSETID_Task:0x8105",
            "utcstartdate"          => "PT_SYSTIME:PSETID_Common:0x8516",
            "utcduedate"            => "PT_SYSTIME:PSETID_Common:0x8517",
            "reminderset"           => "PT_BOOLEAN:PSETID_Common:0x8503",
            "remindertime"          => "PT_SYSTIME:PSETID_Common:0x8502",
            "ordinaldate"           => "PT_SYSTIME:PSETID_Common:0x85A0",
            "subordinaldate"        => "PT_STRING8:PSETID_Common:0x85A1",

        );
    }


    /**
    * Returns email todo flags' specific MAPI properties
    *
    * @access public
    *
    * @return array
    */
    public static function GetMailFlagsProperties() {
        return array(
            "todoitemsflags"        => PR_TODO_ITEM_FLAGS,
            "todotitle"             => "PT_STRING8:PSETID_Common:0x85A4",
            "flagicon"              => PR_FLAG_ICON,
            "replyrequested"        => PR_REPLY_REQUESTED,
            "responserequested"     => PR_RESPONSE_REQUESTED,
            "status"                => "PT_LONG:PSETID_Task:0x8101",
            "completion"            => "PT_DOUBLE:PSETID_Task:0x8102",
            "complete"              => "PT_BOOLEAN:PSETID_Task:0x811C",
        );
    }


    /**
    * Returns the MAPI to AS mapping for notes
    *
    * @access public
    *
    * @return array
    */
    public static function GetNoteMapping() {
        return array(
            "categories"            => "PT_MV_STRING8:PS_PUBLIC_STRINGS:Keywords",
            "lastmodified"          => PR_LAST_MODIFICATION_TIME,
            "messageclass"          => PR_MESSAGE_CLASS,
            "subject"               => PR_SUBJECT,
            "Color"                 => "PT_LONG:PSETID_Note:0x8B00",
            "Iconindex"             => PR_ICON_INDEX,
        );
    }


    /**
    * Returns note specific MAPI properties
    *
    * @access public
    *
    * @return array
    */
    public static function GetNoteProperties() {
        return array(
            "body"                  => PR_BODY,
            "messageclass"          => PR_MESSAGE_CLASS,
            "html"                  => PR_HTML,
            "internetcpid"          => PR_INTERNET_CPID,

        );
    }


    /**
    * Returns properties for sending an email
    *
    * @access public
    *
    * @return array
    */
    public static function GetSendMailProperties() {
        return array(
                "outboxentryid"         => PR_IPM_OUTBOX_ENTRYID,
                "ipmsentmailentryid"    => PR_IPM_SENTMAIL_ENTRYID,
                "sentmailentryid"       => PR_SENTMAIL_ENTRYID,
                "subject"               => PR_SUBJECT,
                "messageclass"          => PR_MESSAGE_CLASS,
                "deliverytime"          => PR_MESSAGE_DELIVERY_TIME,
                "importance"            => PR_IMPORTANCE,
                "priority"              => PR_PRIORITY,
                "addrtype"              => PR_ADDRTYPE,
                "emailaddress"          => PR_EMAIL_ADDRESS,
                "displayname"           => PR_DISPLAY_NAME,
                "recipienttype"         => PR_RECIPIENT_TYPE,
                "entryid"               => PR_ENTRYID,
                "iconindex"             => PR_ICON_INDEX,
                "body"                  => PR_BODY,
                "html"                  => PR_HTML,
                "sentrepresentingname"  => PR_SENT_REPRESENTING_NAME,
                "sentrepresentingemail" => PR_SENT_REPRESENTING_EMAIL_ADDRESS,
                "representingentryid"   => PR_SENT_REPRESENTING_ENTRYID,
                "sentrepresentingaddt"  => PR_SENT_REPRESENTING_ADDRTYPE,
                "sentrepresentinsrchk"  => PR_SENT_REPRESENTING_SEARCH_KEY,
                "displayto"             => PR_DISPLAY_TO,
                "displaycc"             => PR_DISPLAY_CC,
                "clientsubmittime"      => PR_CLIENT_SUBMIT_TIME,
                "attachnum"             => PR_ATTACH_NUM,
                "attachdatabin"         => PR_ATTACH_DATA_BIN,
                "internetcpid"          => PR_INTERNET_CPID,
                "rtf"                   => PR_RTF_COMPRESSED,
                "rtfinsync"             => PR_RTF_IN_SYNC,
        );
    }
}
