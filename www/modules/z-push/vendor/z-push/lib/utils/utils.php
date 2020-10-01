<?php
/***********************************************
* File      :   utils.php
* Project   :   Z-Push
* Descr     :   Several utility functions
*
* Created   :   03.04.2008
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

class Utils {
    /**
     * Prints a variable as string
     * If a boolean is sent, 'true' or 'false' is displayed
     *
     * @param string $var
     * @access public
     * @return string
     */
    static public function PrintAsString($var) {
      return ($var)?(($var===true)?'true':$var):(($var===false)?'false':(($var==='')?'empty':(($var == null) ? 'null':$var)));
//return ($var)?(($var===true)?'true':$var):'false';
    }

    /**
     * Splits a "domain\user" string into two values
     * If the string cotains only the user, domain is returned empty
     *
     * @param string    $domainuser
     *
     * @access public
     * @return array    index 0: user  1: domain
     */
    static public function SplitDomainUser($domainuser) {
        $pos = strrpos($domainuser, '\\');
        if($pos === false){
            $user = $domainuser;
            $domain = '';
        }
        else{
            $domain = substr($domainuser,0,$pos);
            $user = substr($domainuser,$pos+1);
        }
        return array($user, $domain);
    }

    /**
     * Build an address string from the components
     *
     * @param string    $street     the street
     * @param string    $zip        the zip code
     * @param string    $city       the city
     * @param string    $state      the state
     * @param string    $country    the country
     *
     * @access public
     * @return string   the address string or null
     */
    static public function BuildAddressString($street, $zip, $city, $state, $country) {
        $out = "";

        if (isset($country) && $street != "") $out = $country;

        $zcs = "";
        if (isset($zip) && $zip != "") $zcs = $zip;
        if (isset($city) && $city != "") $zcs .= (($zcs)?" ":"") . $city;
        if (isset($state) && $state != "") $zcs .= (($zcs)?" ":"") . $state;
        if ($zcs) $out = $zcs . "\r\n" . $out;

        if (isset($street) && $street != "") $out = $street . (($out)?"\r\n\r\n". $out: "") ;

        return ($out)?$out:null;
    }

    /**
     * Build the fileas string from the components according to the configuration.
     *
     * @param string $lastname
     * @param string $firstname
     * @param string $middlename
     * @param string $company
     *
     * @access public
     * @return string fileas
     */
    static public function BuildFileAs($lastname = "", $firstname = "", $middlename = "", $company = "") {
        if (defined('FILEAS_ORDER')) {
            $fileas = $lastfirst = $firstlast = "";
            $names = trim ($firstname . " " . $middlename);
            $lastname = trim($lastname);
            $company = trim($company);

            // lastfirst is "lastname, firstname middlename"
            // firstlast is "firstname middlename lastname"
            if (strlen($lastname) > 0) {
                $lastfirst = $lastname;
                if (strlen($names) > 0){
                    $lastfirst .= ", $names";
                    $firstlast = "$names $lastname";
                }
                else {
                    $firstlast = $lastname;
                }
            }
            elseif (strlen($names) > 0) {
                $lastfirst = $firstlast = $names;
            }

            // if fileas with a company is selected
            // but company is emtpy then it will
            // fallback to firstlast or lastfirst
            // (depending on which is selected for company)
            switch (FILEAS_ORDER) {
                case SYNC_FILEAS_COMPANYONLY:
                    if (strlen($company) > 0) {
                        $fileas = $company;
                    }
                    elseif (strlen($firstlast) > 0)
                        $fileas = $lastfirst;
                    break;
                case SYNC_FILEAS_COMPANYLAST:
                    if (strlen($company) > 0) {
                        $fileas = $company;
                        if (strlen($lastfirst) > 0)
                            $fileas .= "($lastfirst)";
                    }
                    elseif (strlen($lastfirst) > 0)
                        $fileas = $lastfirst;
                    break;
                case SYNC_FILEAS_COMPANYFIRST:
                    if (strlen($company) > 0) {
                        $fileas = $company;
                        if (strlen($firstlast) > 0) {
                            $fileas .= " ($firstlast)";
                        }
                    }
                    elseif (strlen($firstlast) > 0) {
                        $fileas = $firstlast;
                    }
                    break;
                case SYNC_FILEAS_FIRSTCOMPANY:
                    if (strlen($firstlast) > 0) {
                        $fileas = $firstlast;
                        if (strlen($company) > 0) {
                            $fileas .= " ($company)";
                        }
                    }
                    elseif (strlen($company) > 0) {
                        $fileas = $company;
                    }
                    break;
                case SYNC_FILEAS_LASTCOMPANY:
                    if (strlen($lastfirst) > 0) {
                        $fileas = $lastfirst;
                        if (strlen($company) > 0) {
                            $fileas .= " ($company)";
                        }
                    }
                    elseif (strlen($company) > 0) {
                        $fileas = $company;
                    }
                    break;
                case SYNC_FILEAS_LASTFIRST:
                    if (strlen($lastfirst) > 0) {
                        $fileas = $lastfirst;
                    }
                    break;
                default:
                    $fileas = $firstlast;
                    break;
            }
            if (strlen($fileas) == 0)
                ZLog::Write(LOGLEVEL_DEBUG, "Fileas is empty.");
            return $fileas;
        }
        ZLog::Write(LOGLEVEL_DEBUG, "FILEAS_ORDER not defined. Add it to your config.php.");
        return null;
    }

    /**
     * Checks if the PHP-MAPI extension is available and in a requested version.
     *
     * @param string    $version    the version to be checked ("6.30.10-18495", parts or build number)
     *
     * @access public
     * @return boolean installed version is superior to the checked string
     */
    static public function CheckMapiExtVersion($version = "") {
        if (!extension_loaded("mapi")) {
            return false;
        }
        // compare build number if requested
        if (preg_match('/^\d+$/', $version) && strlen($version) > 3) {
            $vs = preg_split('/-/', phpversion("mapi"));
            return ($version <= $vs[1]);
        }
        if (version_compare(phpversion("mapi"), $version) == -1){
            return false;
        }

        return true;
    }

    /**
     * Parses and returns an ecoded vCal-Uid from an OL compatible GlobalObjectID.
     *
     * @param string    $olUid      an OL compatible GlobalObjectID
     *
     * @access public
     * @return string   the vCal-Uid if available in the olUid, else the original olUid as HEX
     */
    static public function GetICalUidFromOLUid($olUid){
        //check if "vCal-Uid" is somewhere in outlookid case-insensitive
        $icalUid = stristr($olUid, "vCal-Uid");
        if ($icalUid !== false) {
            //get the length of the ical id - go back 4 position from where "vCal-Uid" was found
            $begin = unpack("V", substr($olUid, strlen($icalUid) * (-1) - 4, 4));
            //remove "vCal-Uid" and packed "1" and use the ical id length
            return substr($icalUid, 12, ($begin[1] - 13));
        }
        return strtoupper(bin2hex($olUid));
    }

    /**
     * Checks the given UID if it is an OL compatible GlobalObjectID
     * If not, the given UID is encoded inside the GlobalObjectID
     *
     * @param string    $icalUid    an appointment uid as HEX
     *
     * @access public
     * @return string   an OL compatible GlobalObjectID
     *
     */
    static public function GetOLUidFromICalUid($icalUid) {
        if (strlen($icalUid) <= 64) {
            $len = 13 + strlen($icalUid);
            $OLUid = pack("V", $len);
            $OLUid .= "vCal-Uid";
            $OLUid .= pack("V", 1);
            $OLUid .= $icalUid;
            return hex2bin("040000008200E00074C5B7101A82E0080000000000000000000000000000000000000000". bin2hex($OLUid). "00");
        }
        else
           return hex2bin($icalUid);
    }

    /**
     * Extracts the basedate of the GlobalObjectID and the RecurStartTime
     *
     * @param string    $goid           OL compatible GlobalObjectID
     * @param long      $recurStartTime
     *
     * @access public
     * @return long     basedate
     */
    static public function ExtractBaseDate($goid, $recurStartTime) {
        $hexbase = substr(bin2hex($goid), 32, 8);
        $day = hexdec(substr($hexbase, 6, 2));
        $month = hexdec(substr($hexbase, 4, 2));
        $year = hexdec(substr($hexbase, 0, 4));

        if ($day && $month && $year) {
            $h = $recurStartTime >> 12;
            $m = ($recurStartTime - $h * 4096) >> 6;
            $s = $recurStartTime - $h * 4096 - $m * 64;

            return gmmktime($h, $m, $s, $month, $day, $year);
        }
        else
            return false;
    }

    /**
     * Converts SYNC_FILTERTYPE into a timestamp
     *
     * @param int $filtertype      Filtertype
     *
     * @access public
     * @return long
     */
    static public function GetCutOffDate($filtertype) {
        $back = Utils::GetFiltertypeInterval($filtertype);

        if ($back === false) {
            return 0; // unlimited
        }

        return time() - $back;
    }

    /**
     * Returns the interval indicated by the filtertype.
     *
     * @param int $filtertype
     *
     * @access public
     * @return long|boolean     returns false on invalid filtertype
     */
    static public function GetFiltertypeInterval($filtertype) {
        $back = false;
        switch($filtertype) {
            case SYNC_FILTERTYPE_1DAY:
                $back = 60 * 60 * 24;
                break;
            case SYNC_FILTERTYPE_3DAYS:
                $back = 60 * 60 * 24 * 3;
                break;
            case SYNC_FILTERTYPE_1WEEK:
                $back = 60 * 60 * 24 * 7;
                break;
            case SYNC_FILTERTYPE_2WEEKS:
                $back = 60 * 60 * 24 * 14;
                break;
            case SYNC_FILTERTYPE_1MONTH:
                $back = 60 * 60 * 24 * 31;
                break;
            case SYNC_FILTERTYPE_3MONTHS:
                $back = 60 * 60 * 24 * 31 * 3;
                break;
            case SYNC_FILTERTYPE_6MONTHS:
                $back = 60 * 60 * 24 * 31 * 6;
                break;
            default:
                $back = false;
        }
        return $back;
    }

    /**
     * Converts SYNC_TRUNCATION into bytes
     *
     * @param int       SYNC_TRUNCATION
     *
     * @return long
     */
    static public function GetTruncSize($truncation) {
        switch($truncation) {
            case SYNC_TRUNCATION_HEADERS:
                return 0;
            case SYNC_TRUNCATION_512B:
                return 512;
            case SYNC_TRUNCATION_1K:
                return 1024;
            case SYNC_TRUNCATION_2K:
                return 2*1024;
            case SYNC_TRUNCATION_5K:
                return 5*1024;
            case SYNC_TRUNCATION_10K:
                return 10*1024;
            case SYNC_TRUNCATION_20K:
                return 20*1024;
            case SYNC_TRUNCATION_50K:
                return 50*1024;
            case SYNC_TRUNCATION_100K:
                return 100*1024;
            case SYNC_TRUNCATION_ALL:
                return 1024*1024; // We'll limit to 1MB anyway
            default:
                return 1024; // Default to 1Kb
        }
    }

    /**
     * Truncate an UTF-8 encoded sting correctly
     *
     * If it's not possible to truncate properly, an empty string is returned
     *
     * @param string    $string     the string
     * @param string    $length     position where string should be cut
     * @param boolean   $htmlsafe   doesn't cut html tags in half, doesn't ensure correct html - default: false
     *
     * @return string truncated string
     */
    static public function Utf8_truncate($string, $length, $htmlsafe = false) {
        // make sure length is always an interger
        $length = (int)$length;

        // if the input string is shorter then the trunction, make sure it's valid UTF-8!
        if (strlen($string) <= $length) {
            $length = strlen($string) - 1;
        }

        // The intent is not to cut HTML tags in half which causes displaying issues (see ZP-1240).
        // The used method just tries to cut outside of tags, without checking tag validity and closing tags.
        if ($htmlsafe) {
            $offset = 0 - strlen($string) + $length;
            $validPos = strrpos($string, "<", $offset);
            if ($validPos > strrpos($string, ">", $offset)) {
                $length = $validPos;
            }
        }

        while($length >= 0) {
            if ((ord($string[$length]) < 0x80) || (ord($string[$length]) >= 0xC0)) {
                return substr($string, 0, $length);
            }
            $length--;
        }
        return "";
    }

    /**
     * Indicates if the specified folder type is a system folder
     *
     * @param int            $foldertype
     *
     * @access public
     * @return boolean
     */
    static public function IsSystemFolder($foldertype) {
        return ($foldertype == SYNC_FOLDER_TYPE_INBOX || $foldertype == SYNC_FOLDER_TYPE_DRAFTS || $foldertype == SYNC_FOLDER_TYPE_WASTEBASKET || $foldertype == SYNC_FOLDER_TYPE_SENTMAIL ||
                $foldertype == SYNC_FOLDER_TYPE_OUTBOX || $foldertype == SYNC_FOLDER_TYPE_TASK || $foldertype == SYNC_FOLDER_TYPE_APPOINTMENT || $foldertype == SYNC_FOLDER_TYPE_CONTACT ||
                $foldertype == SYNC_FOLDER_TYPE_NOTE || $foldertype == SYNC_FOLDER_TYPE_JOURNAL) ? true:false;
    }

    /**
     * Our own utf7_decode function because imap_utf7_decode converts a string
     * into ISO-8859-1 encoding which doesn't have euro sign (it will be converted
     * into two chars: [space](ascii 32) and "¬" ("not sign", ascii 172)). Also
     * php iconv function expects '+' as delimiter instead of '&' like in IMAP.
     *
     * @param string $string IMAP folder name
     *
     * @access public
     * @return string
    */
    static public function Utf7_iconv_decode($string) {
        //do not alter string if there aren't any '&' or '+' chars because
        //it won't have any utf7-encoded chars and nothing has to be escaped.
        if (strpos($string, '&') === false && strpos($string, '+') === false ) return $string;

        //Get the string length and go back through it making the replacements
        //necessary
        $len = strlen($string) - 1;
        while ($len > 0) {
            //look for '&-' sequence and replace it with '&'
            if ($len > 0 && $string[$len-1] == '&' && $string[$len] == '-') {
                $string = substr_replace($string, '&', $len - 1, 2);
                $len--; //decrease $len as this char has alreasy been processed
            }
            //search for '&' which weren't found in if clause above and
            //replace them with '+' as they mark an utf7-encoded char
            if ($len > 0 && $string[($len-1)] == '&') {
                $string = substr_replace($string, '+', $len - 1, 1);
                $len--; //decrease $len as this char has alreasy been processed
            }
            //finally "escape" all remaining '+' chars
            if ($len > 0 && $string[$len-1] == '+') {
                $string = substr_replace($string, '+-', $len - 1, 1);
            }
            $len--;
        }
        return $string;
    }

    /**
     * Our own utf7_encode function because the string has to be converted from
     * standard UTF7 into modified UTF7 (aka UTF7-IMAP).
     *
     * @param string $str IMAP folder name
     *
     * @access public
     * @return string
    */
    static public function Utf7_iconv_encode($string) {
        //do not alter string if there aren't any '&' or '+' chars because
        //it won't have any utf7-encoded chars and nothing has to be escaped.
        if (strpos($string, '&') === false && strpos($string, '+') === false ) return $string;

        //Get the string length and go back through it making the replacements
        //necessary
        $len = strlen($string) - 1;
        while ($len > 0) {
            //look for '&-' sequence and replace it with '&'
            if ($len > 0 && $string[$len-1] == '+' && $string[$len] == '-') {
                $string = substr_replace($string, '+', $len - 1, 2);
                $len--; //decrease $len as this char has alreasy been processed
            }
            //search for '&' which weren't found in if clause above and
            //replace them with '+' as they mark an utf7-encoded char
            if ($len > 0 && $string[$len-1] == '+') {
                $string = substr_replace($string, '&', $len - 1, 1);
                $len--; //decrease $len as this char has alreasy been processed
            }
            //finally "escape" all remaining '+' chars
            if ($len > 0 && $string[$len-1] == '&') {
                $string = substr_replace($string, '&-', $len - 1, 1);
            }
            $len--;
        }
        return $string;
    }

    /**
     * Converts an UTF-7 encoded string into an UTF-8 string.
     *
     * @param string $string to convert
     *
     * @access public
     * @return string
     */
    static public function Utf7_to_utf8($string) {
        if (function_exists("iconv")){
            return @iconv("UTF-7", "UTF-8", $string);
        }
        else
            ZLog::Write(LOGLEVEL_WARN, "Utils::Utf7_to_utf8() 'iconv' is not available. Charset conversion skipped.");

        return $string;
    }

    /**
     * Converts an UTF7-IMAP encoded string into an UTF-8 string.
     *
     * @param string $string to convert
     *
     * @access public
     * @return string
     */
    static public function Utf7imap_to_utf8($string) {
        if (function_exists("mb_convert_encoding")){
            return @mb_convert_encoding($string, "UTF-8", "UTF7-IMAP");
        }
        return $string;
    }

    /**
     * Converts an UTF-8 encoded string into an UTF-7 string.
     *
     * @param string $string to convert
     *
     * @access public
     * @return string
     */
    static public function Utf8_to_utf7($string) {
        if (function_exists("iconv")){
            return @iconv("UTF-8", "UTF-7", $string);
        }
        else
            ZLog::Write(LOGLEVEL_WARN, "Utils::Utf8_to_utf7() 'iconv' is not available. Charset conversion skipped.");

        return $string;
    }

    /**
     * Converts an UTF-8 encoded string into an UTF7-IMAP string.
     *
     * @param string $string to convert
     *
     * @access public
     * @return string
     */
    static public function Utf8_to_utf7imap($string) {
        if (function_exists("mb_convert_encoding")){
            return @mb_convert_encoding($string, "UTF7-IMAP", "UTF-8");
        }
        return $string;
    }

    /**
     * Checks for valid email addresses
     * The used regex actually only checks if a valid email address is part of the submitted string
     * it also returns true for the mailbox format, but this is not checked explicitly
     *
     * @param string $email     address to be checked
     *
     * @access public
     * @return boolean
     */
    static public function CheckEmail($email) {
        return strpos($email, '@') !== false ? true : false;
    }

    /**
     * Checks if a string is base64 encoded
     *
     * @param string $string    the string to be checked
     *
     * @access public
     * @return boolean
     */
    static public function IsBase64String($string) {
        return (bool) preg_match("#^([A-Za-z0-9+/]{4})*([A-Za-z0-9+/]{2}==|[A-Za-z0-9+\/]{3}=|[A-Za-z0-9+/]{4})?$#", $string);
    }

    /**
     * Returns a command string for a given command code.
     *
     * @param int $code
     *
     * @access public
     * @return string or false if code is unknown
     */
    public static function GetCommandFromCode($code) {
        switch ($code) {
            case ZPush::COMMAND_SYNC:                 return 'Sync';
            case ZPush::COMMAND_SENDMAIL:             return 'SendMail';
            case ZPush::COMMAND_SMARTFORWARD:         return 'SmartForward';
            case ZPush::COMMAND_SMARTREPLY:           return 'SmartReply';
            case ZPush::COMMAND_GETATTACHMENT:        return 'GetAttachment';
            case ZPush::COMMAND_FOLDERSYNC:           return 'FolderSync';
            case ZPush::COMMAND_FOLDERCREATE:         return 'FolderCreate';
            case ZPush::COMMAND_FOLDERDELETE:         return 'FolderDelete';
            case ZPush::COMMAND_FOLDERUPDATE:         return 'FolderUpdate';
            case ZPush::COMMAND_MOVEITEMS:            return 'MoveItems';
            case ZPush::COMMAND_GETITEMESTIMATE:      return 'GetItemEstimate';
            case ZPush::COMMAND_MEETINGRESPONSE:      return 'MeetingResponse';
            case ZPush::COMMAND_SEARCH:               return 'Search';
            case ZPush::COMMAND_SETTINGS:             return 'Settings';
            case ZPush::COMMAND_PING:                 return 'Ping';
            case ZPush::COMMAND_ITEMOPERATIONS:       return 'ItemOperations';
            case ZPush::COMMAND_PROVISION:            return 'Provision';
            case ZPush::COMMAND_RESOLVERECIPIENTS:    return 'ResolveRecipients';
            case ZPush::COMMAND_VALIDATECERT:         return 'ValidateCert';

            // Deprecated commands
            case ZPush::COMMAND_GETHIERARCHY:         return 'GetHierarchy';
            case ZPush::COMMAND_CREATECOLLECTION:     return 'CreateCollection';
            case ZPush::COMMAND_DELETECOLLECTION:     return 'DeleteCollection';
            case ZPush::COMMAND_MOVECOLLECTION:       return 'MoveCollection';
            case ZPush::COMMAND_NOTIFY:               return 'Notify';

            // Webservice commands
            case ZPush::COMMAND_WEBSERVICE_DEVICE:    return 'WebserviceDevice';
            case ZPush::COMMAND_WEBSERVICE_USERS:     return 'WebserviceUsers';
            case ZPush::COMMAND_WEBSERVICE_INFO:      return 'WebserviceInfo';
        }
        return false;
    }

    /**
     * Returns a command code for a given command.
     *
     * @param string $command
     *
     * @access public
     * @return int or false if command is unknown
     */
    public static function GetCodeFromCommand($command) {
        switch ($command) {
            case 'Sync':                 return ZPush::COMMAND_SYNC;
            case 'SendMail':             return ZPush::COMMAND_SENDMAIL;
            case 'SmartForward':         return ZPush::COMMAND_SMARTFORWARD;
            case 'SmartReply':           return ZPush::COMMAND_SMARTREPLY;
            case 'GetAttachment':        return ZPush::COMMAND_GETATTACHMENT;
            case 'FolderSync':           return ZPush::COMMAND_FOLDERSYNC;
            case 'FolderCreate':         return ZPush::COMMAND_FOLDERCREATE;
            case 'FolderDelete':         return ZPush::COMMAND_FOLDERDELETE;
            case 'FolderUpdate':         return ZPush::COMMAND_FOLDERUPDATE;
            case 'MoveItems':            return ZPush::COMMAND_MOVEITEMS;
            case 'GetItemEstimate':      return ZPush::COMMAND_GETITEMESTIMATE;
            case 'MeetingResponse':      return ZPush::COMMAND_MEETINGRESPONSE;
            case 'Search':               return ZPush::COMMAND_SEARCH;
            case 'Settings':             return ZPush::COMMAND_SETTINGS;
            case 'Ping':                 return ZPush::COMMAND_PING;
            case 'ItemOperations':       return ZPush::COMMAND_ITEMOPERATIONS;
            case 'Provision':            return ZPush::COMMAND_PROVISION;
            case 'ResolveRecipients':    return ZPush::COMMAND_RESOLVERECIPIENTS;
            case 'ValidateCert':         return ZPush::COMMAND_VALIDATECERT;

            // Deprecated commands
            case 'GetHierarchy':         return ZPush::COMMAND_GETHIERARCHY;
            case 'CreateCollection':     return ZPush::COMMAND_CREATECOLLECTION;
            case 'DeleteCollection':     return ZPush::COMMAND_DELETECOLLECTION;
            case 'MoveCollection':       return ZPush::COMMAND_MOVECOLLECTION;
            case 'Notify':               return ZPush::COMMAND_NOTIFY;

            // Webservice commands
            case 'WebserviceDevice':     return ZPush::COMMAND_WEBSERVICE_DEVICE;
            case 'WebserviceUsers':      return ZPush::COMMAND_WEBSERVICE_USERS;
            case 'WebserviceInfo':       return ZPush::COMMAND_WEBSERVICE_INFO;
        }
        return false;
    }

    /**
     * Normalize the given timestamp to the start of the day
     *
     * @param long      $timestamp
     *
     * @access private
     * @return long
     */
    public static function getDayStartOfTimestamp($timestamp) {
        return $timestamp - ($timestamp % (60 * 60 * 24));
    }

    /**
     * Returns a formatted string output from an optional timestamp.
     * If no timestamp is sent, NOW is used.
     *
     * @param long  $timestamp
     *
     * @access public
     * @return string
     */
    public static function GetFormattedTime($timestamp = false) {
        if (!$timestamp)
            return @strftime("%d/%m/%Y %H:%M:%S");
        else
            return @strftime("%d/%m/%Y %H:%M:%S", $timestamp);
    }


   /**
    * Get charset name from a codepage
    *
    * @see http://msdn.microsoft.com/en-us/library/dd317756(VS.85).aspx
    *
    * Table taken from common/codepage.cpp
    *
    * @param integer codepage Codepage
    *
    * @access public
    * @return string iconv-compatible charset name
    */
    public static function GetCodepageCharset($codepage) {
        $codepages = array(
            20106 => "DIN_66003",
            20108 => "NS_4551-1",
            20107 => "SEN_850200_B",
            950 => "big5",
            50221 => "csISO2022JP",
            51932 => "euc-jp",
            51936 => "euc-cn",
            51949 => "euc-kr",
            949 => "euc-kr",
            936 => "gb18030",
            52936 => "csgb2312",
            852 => "ibm852",
            866 => "ibm866",
            50220 => "iso-2022-jp",
            50222 => "iso-2022-jp",
            50225 => "iso-2022-kr",
            1252 => "windows-1252",
            28591 => "iso-8859-1",
            28592 => "iso-8859-2",
            28593 => "iso-8859-3",
            28594 => "iso-8859-4",
            28595 => "iso-8859-5",
            28596 => "iso-8859-6",
            28597 => "iso-8859-7",
            28598 => "iso-8859-8",
            28599 => "iso-8859-9",
            28603 => "iso-8859-13",
            28605 => "iso-8859-15",
            20866 => "koi8-r",
            21866 => "koi8-u",
            932 => "shift-jis",
            1200 => "unicode",
            1201 => "unicodebig",
            65000 => "utf-7",
            65001 => "utf-8",
            1250 => "windows-1250",
            1251 => "windows-1251",
            1253 => "windows-1253",
            1254 => "windows-1254",
            1255 => "windows-1255",
            1256 => "windows-1256",
            1257 => "windows-1257",
            1258 => "windows-1258",
            874 => "windows-874",
            20127 => "us-ascii"
        );

        if(isset($codepages[$codepage])) {
            return $codepages[$codepage];
        } else {
            // Defaulting to iso-8859-15 since it is more likely for someone to make a mistake in the codepage
            // when using west-european charsets then when using other charsets since utf-8 is binary compatible
            // with the bottom 7 bits of west-european
            return "iso-8859-15";
        }
    }

    /**
     * Converts a string encoded with codepage into an UTF-8 string
     *
     * @param int $codepage
     * @param string $string
     *
     * @access public
     * @return string
     */
    public static function ConvertCodepageStringToUtf8($codepage, $string) {
        if (function_exists("iconv")) {
            $charset = self::GetCodepageCharset($codepage);
            return iconv($charset, "utf-8", $string);
        }
        else
            ZLog::Write(LOGLEVEL_WARN, "Utils::ConvertCodepageStringToUtf8() 'iconv' is not available. Charset conversion skipped.");

        return $string;
    }

    /**
     * Converts a string to another charset.
     *
     * @param int $in
     * @param int $out
     * @param string $string
     *
     * @access public
     * @return string
     */
    public static function ConvertCodepage($in, $out, $string) {
        // do nothing if both charsets are the same
        if ($in == $out)
            return $string;

        if (function_exists("iconv")) {
            $inCharset = self::GetCodepageCharset($in);
            $outCharset = self::GetCodepageCharset($out);
            return iconv($inCharset, $outCharset, $string);
        }
        else
            ZLog::Write(LOGLEVEL_WARN, "Utils::ConvertCodepage() 'iconv' is not available. Charset conversion skipped.");

        return $string;
    }

    /**
     * Returns the best match of preferred body preference types.
     *
     * @param array             $bpTypes
     *
     * @access public
     * @return int
     */
    public static function GetBodyPreferenceBestMatch($bpTypes) {
        if ($bpTypes === false) {
            return SYNC_BODYPREFERENCE_PLAIN;
        }
        // The best choice is RTF, then HTML and then MIME in order to save bandwidth
        // because MIME is a complete message including the headers and attachments
        if (in_array(SYNC_BODYPREFERENCE_RTF, $bpTypes))  return SYNC_BODYPREFERENCE_RTF;
        if (in_array(SYNC_BODYPREFERENCE_HTML, $bpTypes)) return SYNC_BODYPREFERENCE_HTML;
        if (in_array(SYNC_BODYPREFERENCE_MIME, $bpTypes)) return SYNC_BODYPREFERENCE_MIME;
        return SYNC_BODYPREFERENCE_PLAIN;
    }

    /* BEGIN fmbiete's contribution r1516, ZP-318 */
    /**
     * Converts a html string into a plain text string
     *
     * @param string $html
     *
     * @access public
     * @return string
     */
    public static function ConvertHtmlToText($html) {
        // remove css-style tags
        $plaintext = preg_replace("/<style.*?<\/style>/is", "", $html);
        // remove all other html
        $plaintext = strip_tags($plaintext);

        return $plaintext;
    }
    /* END fmbiete's contribution r1516, ZP-318 */

    /**
     * Checks if a file has the same owner and group as the parent directory.
     * If not, owner and group are fixed (being updated to the owner/group of the directory).
     * If the given file is a special file (i.g., /dev/null, fifo), nothing is changed.
     * Function code contributed by Robert Scheck aka rsc.
     *
     * @param string $file
     *
     * @access public
     * @return boolean
     */
    public static function FixFileOwner($file) {
        if (!function_exists('posix_getuid')) {
           ZLog::Write(LOGLEVEL_DEBUG, "Utils::FixeFileOwner(): Posix subsystem not available, skipping.");
           return false;
        }
        if (posix_getuid() == 0 && is_file($file)) {
            $dir = dirname($file);
            $perm_dir = stat($dir);
            $perm_file = stat($file);

            if ($perm_file['uid'] == 0 && $perm_dir['uid'] == 0 && $perm_dir['gid'] == 0) {
                unlink($file);
                throw new FatalException("FixFileOwner: $dir must be owned by the nginx/apache/php user instead of root for debian based systems and by root:z-push for RHEL-based systems");
            }

            if($perm_dir['uid'] !== $perm_file['uid'] || $perm_dir['gid'] !== $perm_file['gid']) {
                chown($file, $perm_dir['uid']);
                chgrp($file, $perm_dir['gid']);
                chmod($file, 0664);
            }
        }
        return true;
    }

    /**
     * Returns AS-style LastVerbExecuted value from the server value.
     *
     * @param int $verb
     *
     * @access public
     * @return int
     */
    public static function GetLastVerbExecuted($verb) {
        switch ($verb) {
            case NOTEIVERB_REPLYTOSENDER:   return AS_REPLYTOSENDER;
            case NOTEIVERB_REPLYTOALL:      return AS_REPLYTOALL;
            case NOTEIVERB_FORWARD:         return AS_FORWARD;
        }

        return 0;
    }

    /**
     * Returns the local part from email address.
     *
     * @param string $email
     *
     * @access public
     * @return string
     */
    public static function GetLocalPartFromEmail($email) {
        $pos = strpos($email, '@');
        if ($pos === false) {
            return $email;
        }
        return substr($email, 0, $pos);
    }

    /**
     * Safely write data to disk, using an unique tmp file (concurrent write),
     * and using rename for atomicity. It also calls FixFileOwner to prevent
     * ownership/rights problems when running as root
     *
     * If you use SafePutContents, you can safely use file_get_contents
     * (you will always read a fully written file)
     *
     * @param string $filename
     * @param string $data
     * @return boolean|int
     */
    public static function SafePutContents($filename, $data) {
        //put the 'tmp' as a prefix (and not suffix) so all glob call will not see temp files
        $tmp = dirname($filename).DIRECTORY_SEPARATOR.'tmp-'.getmypid().'-'.basename($filename);

        //number of attempts
        $attempts = (defined('FILE_STATE_ATTEMPTS') ? FILE_STATE_ATTEMPTS : 3);
        //ms to sleep between attempts
        $sleep_time = (defined('FILE_STATE_SLEEP') ? FILE_STATE_SLEEP : 100);
        $i = 1;
        while (($i <= $attempts) && (($bytes = file_put_contents($tmp, $data)) === false)) {
            ZLog::Write(LOGLEVEL_WARN, sprintf("Utils->SafePutContents: Failed on writing data in tmp - attempt: %d - filename: %s", $i, $tmp));
            $i++;
            usleep($sleep_time * 1000);
        }
        if ($bytes !== false){
            self::FixFileOwner($tmp);
            $i = 1;
            while (($i <= $attempts) && (($res = rename($tmp, $filename)) !== true)) {
                ZLog::Write(LOGLEVEL_WARN, sprintf("Utils->SafePutContents: Failed on rename - attempt: %d - filename: %s", $i, $tmp));
                $i++;
                usleep($sleep_time * 1000);
            }
            if ($res !== true) $bytes = false;
        }
        return $bytes;
    }

    /**
     * Format bytes to a more human readable value.
     * @param int $bytes
     * @param int $precision
     *
     * @access public
     * @return void|string
     */
    public static function FormatBytes($bytes, $precision = 2) {
        if ($bytes <= 0) return '0 B';

        $units = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB');
        $base = log ($bytes, 1024);
        $fBase = floor($base);
        $pow = pow(1024, $base - $fBase);
        return sprintf ("%.{$precision}f %s", $pow, $units[$fBase]);
    }

    public static function GetAvailableCharacterEncodings() {
        return array(
                'UCS-4',
                'UCS-4BE',
                'UCS-4LE',
                'UCS-2',
                'UCS-2BE',
                'UCS-2LE',
                'UTF-32',
                'UTF-32BE',
                'UTF-32LE',
                'UTF-16',
                'UTF-16BE',
                'UTF-16LE',
                'UTF-7',
                'UTF7-IMAP',
                'UTF-8',
                'ASCII',
                'EUC-JP',
                'SJIS',
                'eucJP-win',
                'SJIS-win',
                'ISO-2022-JP',
                'ISO-2022-JP-MS',
                'CP932',
                'CP51932',
                'SJIS-mac',
                'MacJapanese',
                'SJIS-Mobile#DOCOMO',
                'SJIS-DOCOMO',
                'SJIS-Mobile#KDDI',
                'SJIS-KDDI',
                'SJIS-Mobile#SOFTBANK',
                'SJIS-SOFTBANK',
                'UTF-8-Mobile#DOCOMO',
                'UTF-8-DOCOMO',
                'UTF-8-Mobile#KDDI-A',
                'UTF-8-Mobile#KDDI-B',
                'UTF-8-KDDI',
                'UTF-8-Mobile#SOFTBANK',
                'UTF-8-SOFTBANK',
                'ISO-2022-JP-MOBILE#KDDI',
                'ISO-2022-JP-KDDI',
                'JIS',
                'JIS-ms',
                'CP50220',
                'CP50220raw',
                'CP50221',
                'CP50222',
                'ISO-8859-1',
                'ISO-8859-2',
                'ISO-8859-3',
                'ISO-8859-4',
                'ISO-8859-5',
                'ISO-8859-6',
                'ISO-8859-7',
                'ISO-8859-8',
                'ISO-8859-9',
                'ISO-8859-10',
                'ISO-8859-13',
                'ISO-8859-14',
                'ISO-8859-15',
                'byte2be',
                'byte2le',
                'byte4be',
                'byte4le',
                'BASE64',
                'HTML-ENTITIES',
                '7bit',
                '8bit',
                'EUC-CN',
                'CP936',
                'GB18030',
                'HZ',
                'EUC-TW',
                'CP950',
                'BIG-5',
                'EUC-KR',
                'UHC (CP949)',
                'ISO-2022-KR',
                'Windows-1251',
                'CP1251',
                'Windows-1252',
                'CP1252',
                'CP866 (IBM866)',
                'KOI8-R',
                'ArmSCII-8',
                'ArmSCII8',
        );
    }

    /**
     * Returns folder origin identifier from its id.
     *
     * @param string $folderid
     *
     * @access public
     * @return string|boolean  matches values of DeviceManager::FLD_ORIGIN_*
     */
    public static function GetFolderOriginFromId($folderid) {
        $origin = substr($folderid, 0, 1);
        switch ($origin) {
            case DeviceManager::FLD_ORIGIN_CONFIG:
            case DeviceManager::FLD_ORIGIN_GAB:
            case DeviceManager::FLD_ORIGIN_SHARED:
            case DeviceManager::FLD_ORIGIN_USER:
            case DeviceManager::FLD_ORIGIN_IMPERSONATED:
                return $origin;
        }
        ZLog::Write(LOGLEVEL_WARN, sprintf("Utils->GetFolderOriginFromId(): Unknown folder origin for folder with id '%s'", $folderid));
        return false;
    }

    /**
     * Returns folder origin as string from its id.
     *
     * @param string $folderid
     *
     * @access public
     * @return string
     */
    public static function GetFolderOriginStringFromId($folderid) {
        $origin = substr($folderid, 0, 1);
        switch ($origin) {
            case DeviceManager::FLD_ORIGIN_CONFIG:
                return 'configured';
            case DeviceManager::FLD_ORIGIN_GAB:
                return 'GAB';
            case DeviceManager::FLD_ORIGIN_SHARED:
                return 'shared';
            case DeviceManager::FLD_ORIGIN_USER:
                return 'user';
            case DeviceManager::FLD_ORIGIN_IMPERSONATED:
                return 'impersonated';
        }
        ZLog::Write(LOGLEVEL_WARN, sprintf("Utils->GetFolderOriginStringFromId(): Unknown folder origin for folder with id '%s'", $folderid));
        return 'unknown';
    }

    /**
     * Splits the id into folder id and message id parts. A colon in the $id indicates
     * that the id has folderid:messageid format.
     *
     * @param string            $id
     *
     * @access public
     * @return array
     */
    public static function SplitMessageId($id) {
        if (strpos($id, ':') !== false) {
            return explode(':', $id);
        }
        return array(null, $id);
    }

    /**
     * Detects encoding of the input and converts it to UTF-8.
     * This is currently only used for authorization header conversion.
     *
     * @param string      $data     input data
     *
     * @access public
     * @return string               utf-8 encoded data
     */
    public static function ConvertAuthorizationToUTF8($data) {
        $encoding = mb_detect_encoding($data, "UTF-8, ISO-8859-1");

        if (!$encoding) {
            $encoding = mb_detect_encoding($data, Utils::GetAvailableCharacterEncodings());
            if ($encoding) {
                ZLog::Write(LOGLEVEL_WARN,
                        sprintf("Utils::ConvertAuthorizationToUTF8(): mb_detect_encoding detected '%s' charset. This charset is not in the default detect list. Please report it to Z-Push developers.",
                                $encoding));
            }
            else {
                ZLog::Write(LOGLEVEL_ERROR, "Utils::ConvertAuthorizationToUTF8(): mb_detect_encoding failed to detect the Authorization header charset. It's possible that user won't be able to login.");
            }
        }

        if ($encoding && strtolower($encoding) != "utf-8") {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("Utils::ConvertAuthorizationToUTF8(): mb_detect_encoding detected '%s' charset. Authorization header will be converted to UTF-8 from it.", $encoding));
            return mb_convert_encoding($data, "UTF-8", $encoding);
        }

        return $data;
    }

    /**
     * Modifies a SyncFolder object, changing the type to SYNC_FOLDER_TYPE_UNKNOWN but saving the original type.
     * It also appends a zero-width UTF-8 (U+200B) character to the name, which serves as marker.
     *
     * @access public
     * @param SyncFolder $folder
     * @return SyncFolder
     */
    public static function ChangeFolderToTypeUnknownForKoe($folder) {
        // append a zero width UTF-8 space to the name
        $folder->displayname .= hex2bin("e2808b");
        $folder->TypeReal = $folder->type;
        $folder->type = SYNC_FOLDER_TYPE_UNKNOWN;

        return $folder;
    }

    /**
     * Checks if the displayname of the folder contains the zero-width UTF-8 (U+200B) character marker.
     *
     * @access public
     * @param SyncFolder $folder
     * @return boolean
     */
    public static function IsFolderToBeProcessedByKoe($folder) {
        return isset($folder->displayname) && substr($folder->displayname, -3) == hex2bin("e2808b");
    }

    /**
     * If string is ISO-2022-JP, convert this into utf-8.
     *
     * @param string $nonencstr
     * @param string $utf8str
     *
     * @access private
     * @return string
     */
    private static function convertRawHeader2Utf8($nonencstr, $utf8str) {
        if (!isset($nonencstr)) {
            return $utf8str;
        }
        // if php-imap option is not installed, there is no noconversion
        if (!function_exists("imap_mime_header_decode")) {
            return $utf8str;
        }
        $isiso2022jp = false;
        $issamecharset = true;
        $charset = NULL;
        $str = "";
        $striso2022jp = "";
        foreach (@imap_mime_header_decode($nonencstr) as $val) {
            if (is_null($charset)) {
                $charset = strtolower($val->charset);
            }
            if ($charset != strtolower($val->charset)) {
                $issamecharset = false;
            }
            if (strtolower($val->charset) == "iso-2022-jp") {
                $isiso2022jp = true;
                $striso2022jp .= $val->text;
                $str .= @mb_convert_encoding($val->text, "utf-8", "ISO-2022-JP-MS");
            }
            elseif (strtolower($val->charset) == "default") {
                $str .= $val->text;
            }
            else {
                $str .= @mb_convert_encoding($val->text, "utf-8", $val->charset);
            }
        }
        if (!$isiso2022jp) {
            return $utf8str;
        }
        if ($charset == 'iso-2022-jp' && $issamecharset) {
            $str = @mb_convert_encoding($striso2022jp, "utf-8", "ISO-2022-JP-MS");
        }
        return $str;
    }

    /**
     * Get raw mail headers as key-value pair array.
     *
     * @param &$mail: this is reference of the caller's $mail,
     *                not copy. So the call to
     *                Utils::getRawMailHeaders() will not require
     *                memory for $mail.
     *
     * @access private
     * @return string array
     */
    private static function getRawMailHeaders(&$mail) {
        // if no headers, return FALSE
        if (!preg_match("/^(.*?)\r?\n\r?\n/s", $mail, $match)) {
            ZLog::Write(LOGLEVEL_DEBUG, "Utils::getRawMailHeaders(): no header");
            return false;
        }
        $input = $match[1];
        // if no headers, return FALSE
        if ($input == "") {
            ZLog::Write(LOGLEVEL_DEBUG, "Utils::getRawMailHeaders(): no header");
            return false;
        }
        // parse headers
        $input = preg_replace("/\r?\n/", "\r\n", $input);
        $input = preg_replace("/=\r\n(\t| )+/", '=', $input);
        $input = preg_replace("/\r\n(\t| )+/", ' ', $input);
        $headersonly = explode("\r\n", trim($input));
        unset($input);
        $headers = array("subject" => NULL, "from" => NULL);
        foreach ($headersonly as $value) {
            if (!preg_match("/^(.+):[ \t]*(.+)$/", $value, $match)) {
                continue;
            }
            $headers[strtolower($match[1])] = $match[2];
        }
        unset($headersonly);
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("Utils::getRawMailHeaders(): subject = %s", $headers["subject"]));
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("Utils::getRawMailHeaders(): from = %s", $headers["from"]));
        return $headers;
    }

    /**
     * Check if the UTF-8 string has ISO-2022-JP esc seq
     * if so, it is ISO-2022-JP, not UTF-8 and convert it into UTF-8
     * string
     *
     * @access public
     * @param $string
     * @return $string
     */
    public static function CheckAndFixEncoding(&$string) {
        if ( isset($string) && strpos($string, chr(0x1b).'$B') !== false ) {
            $string = mb_convert_encoding($string, "utf-8", "ISO-2022-JP-MS");
        }
    }

    /**
     * Get to or cc header in mime-header-encoded UTF-8 text.
     *
     * @access public
     * @param $addrstruncs
     *        $addrstruncts is a return value of
     *        Mail_RFC822->parseAddressList(). Convert this into
     *        plain text. If the phrase part is in plain UTF-8,
     *        convert this into mime-header encoded UTF-8
     */
    public static function CheckAndFixEncodingInHeadersOfSentMail($addrstructs) {
        mb_internal_encoding("UTF-8");
        $addrarray = array();
        // process each address
        foreach ( $addrstructs as $struc ) {
            $addrphrase = $struc->personal;
            if (isset($addrphrase) && strlen($addrphrase) > 0 && mb_detect_encoding($addrphrase, "UTF-8") != false && preg_match('/[^\x00-\x7F]/', $addrphrase) == 1) {
                // phrase part is plain utf-8 text including non ascii characters
                // convert ths into mime-header-encoded text
                $addrphrase = mb_encode_mimeheader($addrphrase);
            }
            if ( strlen($addrphrase) > 0 ) {
                // there is a phrase part in the address
                $addrarray[] = $addrphrase . " " . " <" . $struc->mailbox . "@" . $struc->host . ">";
            } else {
                // there is no phrase part in the address
                $addrarray[] = $struc->mailbox . "@" . $struc->host;
            }
        }
        // combine each address into a string
        $addresses = implode(",", $addrarray);
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("Utils::CheckAndFixEncodingInHeadersOfSentMail(): addresses %s", $addresses));
        return $addresses;
    }

    /**
     * Set expected subject and from in utf-8 even if in wrong
     * decoded.
     *
     * @param &$mail
     *        &$mail is reference of the caller's, not copy. So the
     *        call to Utils::CheckAndFixEncodingInHeaders() will not
     *        require memory for $mail.
     * @param $message
     *        $message is an instance of a class. So the call to
     *        Utils::CheckAndFixEncodingInHeaders() will not
     *        require memory for $message
     *
     * @access public
     * @return void
     */
    public static function CheckAndFixEncodingInHeaders(&$mail, $message) {
        $rawheaders = Utils::getRawMailHeaders($mail);
        if (!$rawheaders) {
            return;
        }
        $message->headers["subject"] = isset($message->headers["subject"]) ? Utils::convertRawHeader2Utf8($rawheaders["subject"], $message->headers["subject"]) : "";
        $message->headers["from"] = Utils::convertRawHeader2Utf8($rawheaders["from"], $message->headers["from"]);
    }

    /**
     * Tries to load the content of a file from disk with retries in case of file system returns an empty file.
     *
     * @param $filename
     *        $filename is the name of the file to be opened
     *
     * @param $functName
     *        $functName is the name of the caller function. Usefull to be printed into the log file
     *
     * @param $suppressWarnings
     *        $suppressWarnings boolean. True if file_get_contents function has to be called with suppress warnings enabled, False otherwise
     *
     * @access private
     * @return string
     */
    public static function SafeGetContents($filename, $functName, $suppressWarnings) {
        $attempts = (defined('FILE_STATE_ATTEMPTS') ? FILE_STATE_ATTEMPTS : 3);
        $sleep_time = (defined('FILE_STATE_SLEEP') ? FILE_STATE_SLEEP : 100);
        $i = 1;
        while (($i <= $attempts) && (($filecontents = ($suppressWarnings ? @file_get_contents($filename) : file_get_contents($filename))) === '')) {
            ZLog::Write(LOGLEVEL_WARN, sprintf("FileStateMachine->%s(): Failed on reading filename '%s' - attempt: %d", $functName, $filename, $i));
            $i++;
            usleep($sleep_time * 1000);
        }
        if ($i > $attempts)
            ZLog::Write(LOGLEVEL_FATAL, sprintf("FileStateMachine->%s(): Unable to read filename '%s' after %s retries",$functName, $filename, --$i));

        return $filecontents;
    }
}



// TODO Win1252/UTF8 functions are deprecated and will be removed sometime
//if the ICS backend is loaded in CombinedBackend and Zarafa > 7
//STORE_SUPPORTS_UNICODE is true and the convertion will not be done
//for other backends.
function utf8_to_windows1252($string, $option = "", $force_convert = false) {
    //if the store supports unicode return the string without converting it
    if (!$force_convert && defined('STORE_SUPPORTS_UNICODE') && STORE_SUPPORTS_UNICODE == true) return $string;

    if (function_exists("iconv")){
        return @iconv("UTF-8", "Windows-1252" . $option, $string);
    }else{
        return utf8_decode($string); // no euro support here
    }
}

function windows1252_to_utf8($string, $option = "", $force_convert = false) {
    //if the store supports unicode return the string without converting it
    if (!$force_convert && defined('STORE_SUPPORTS_UNICODE') && STORE_SUPPORTS_UNICODE == true) return $string;

    if (function_exists("iconv")){
        return @iconv("Windows-1252", "UTF-8" . $option, $string);
    }else{
        return utf8_encode($string); // no euro support here
    }
}

function w2u($string) { return windows1252_to_utf8($string); }
function u2w($string) { return utf8_to_windows1252($string); }

function w2ui($string) { return windows1252_to_utf8($string, "//TRANSLIT"); }
function u2wi($string) { return utf8_to_windows1252($string, "//TRANSLIT"); }
