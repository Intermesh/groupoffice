<?php
/***********************************************
* File      :   syncprovisioning.php
* Project   :   Z-Push
* Descr     :   WBXML AS12+ provisionign entities that
*               can be parsed directly (as a stream) from WBXML.
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

class SyncProvisioning extends SyncObject {
    //AS 12.0, 12.1 and 14.0 props
    public $devpwenabled;
    public $alphanumpwreq;
    public $devencenabled;
    public $pwrecoveryenabled;
    public $docbrowseenabled;
    public $attenabled;
    public $mindevpwlenngth;
    public $maxinacttimedevlock;
    public $maxdevpwfailedattempts;
    public $maxattsize;
    public $allowsimpledevpw;
    public $devpwexpiration;
    public $devpwhistory;

    //AS 12.1 and 14.0 props
    public $allowstoragecard;
    public $allowcam;
    public $reqdevenc;
    public $allowunsignedapps;
    public $allowunsigninstallpacks;
    public $mindevcomplexchars;
    public $allowwifi;
    public $allowtextmessaging;
    public $allowpopimapemail;
    public $allowbluetooth;
    public $allowirda;
    public $reqmansyncroam;
    public $allowdesktopsync;
    public $maxcalagefilter;
    public $allowhtmlemail;
    public $maxemailagefilter;
    public $maxemailbodytruncsize;
    public $maxemailhtmlbodytruncsize;
    public $reqsignedsmimemessages;
    public $reqencsmimemessages;
    public $reqsignedsmimealgorithm;
    public $reqencsmimealgorithm;
    public $allowsmimeencalgneg;
    public $allowsmimesoftcerts;
    public $allowbrowser;
    public $allowconsumeremail;
    public $allowremotedesk;
    public $allowinternetsharing;
    public $unapprovedinromapplist;
    public $approvedapplist;

    // policy name used with the policies; not part of ActiveSync
    public $PolicyName;

    function __construct() {
        $mapping = array (
                    SYNC_PROVISION_DEVPWENABLED                         => array (  self::STREAMER_VAR      => "devpwenabled",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),

                    SYNC_PROVISION_ALPHANUMPWREQ                        => array (  self::STREAMER_VAR      => "alphanumpwreq",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),

                    SYNC_PROVISION_PWRECOVERYENABLED                    => array (  self::STREAMER_VAR      => "pwrecoveryenabled",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),

                    SYNC_PROVISION_DEVENCENABLED                        => array (  self::STREAMER_VAR      => "devencenabled",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),

                    SYNC_PROVISION_DOCBROWSEENABLED                     => array (  self::STREAMER_VAR      => "docbrowseenabled"), // depricated
                    SYNC_PROVISION_ATTENABLED                           => array (  self::STREAMER_VAR      => "attenabled",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),

                    SYNC_PROVISION_MINDEVPWLENGTH                       => array (  self::STREAMER_VAR      => "mindevpwlenngth",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_CMPHIGHER  => 0,
                                                                                                                        self::STREAMER_CHECK_CMPLOWER   => 17  )),

                    SYNC_PROVISION_MAXINACTTIMEDEVLOCK                  => array (  self::STREAMER_VAR      => "maxinacttimedevlock"),
                    SYNC_PROVISION_MAXDEVPWFAILEDATTEMPTS               => array (  self::STREAMER_VAR      => "maxdevpwfailedattempts",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_CMPHIGHER  => 3,
                                                                                                                        self::STREAMER_CHECK_CMPLOWER   => 17  )),

                    SYNC_PROVISION_MAXATTSIZE                           => array (  self::STREAMER_VAR      => "maxattsize",
                                                                                    self::STREAMER_PROP     => self::STREAMER_TYPE_SEND_EMPTY,
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_CMPHIGHER  => -1 )),

                    SYNC_PROVISION_ALLOWSIMPLEDEVPW                     => array (  self::STREAMER_VAR      => "allowsimpledevpw",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),

                    SYNC_PROVISION_DEVPWEXPIRATION                      => array (  self::STREAMER_VAR      => "devpwexpiration",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_CMPHIGHER  => -1 )),

                    SYNC_PROVISION_DEVPWHISTORY                         => array (  self::STREAMER_VAR      => "devpwhistory",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_CMPHIGHER  => -1 )),


                    SYNC_PROVISION_POLICYNAME                           => array (  self::STREAMER_VAR      => "PolicyName",
                                                                                    self::STREAMER_TYPE     => self::STREAMER_TYPE_IGNORE),
                );

        if(Request::GetProtocolVersion() >= 12.1) {
            $mapping += array (
                    SYNC_PROVISION_ALLOWSTORAGECARD                     => array (  self::STREAMER_VAR      => "allowstoragecard",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),

                    SYNC_PROVISION_ALLOWCAM                             => array (  self::STREAMER_VAR      => "allowcam",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),

                    SYNC_PROVISION_REQDEVENC                            => array (  self::STREAMER_VAR      => "reqdevenc",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),

                    SYNC_PROVISION_ALLOWUNSIGNEDAPPS                    => array (  self::STREAMER_VAR      => "allowunsignedapps",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),

                    SYNC_PROVISION_ALLOWUNSIGNEDINSTALLATIONPACKAGES    => array (  self::STREAMER_VAR      => "allowunsigninstallpacks",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),

                    SYNC_PROVISION_MINDEVPWCOMPLEXCHARS                 => array (  self::STREAMER_VAR      => "mindevcomplexchars",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(1,2,3,4) )),

                    SYNC_PROVISION_ALLOWWIFI                            => array (  self::STREAMER_VAR      => "allowwifi",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),

                    SYNC_PROVISION_ALLOWTEXTMESSAGING                   => array (  self::STREAMER_VAR      => "allowtextmessaging",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),

                    SYNC_PROVISION_ALLOWPOPIMAPEMAIL                    => array (  self::STREAMER_VAR      => "allowpopimapemail",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),

                    SYNC_PROVISION_ALLOWBLUETOOTH                       => array (  self::STREAMER_VAR      => "allowbluetooth",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(0,1,2) )),

                    SYNC_PROVISION_ALLOWIRDA                            => array (  self::STREAMER_VAR      => "allowirda",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),

                    SYNC_PROVISION_REQMANUALSYNCWHENROAM                => array (  self::STREAMER_VAR      => "reqmansyncroam",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),

                    SYNC_PROVISION_ALLOWDESKTOPSYNC                     => array (  self::STREAMER_VAR      => "allowdesktopsync",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),

                    SYNC_PROVISION_MAXCALAGEFILTER                      => array (  self::STREAMER_VAR      => "maxcalagefilter",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(0,4,5,6,7) )),

                    SYNC_PROVISION_ALLOWHTMLEMAIL                       => array (  self::STREAMER_VAR      => "allowhtmlemail",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),

                    SYNC_PROVISION_MAXEMAILAGEFILTER                    => array (  self::STREAMER_VAR      => "maxemailagefilter",
                                                                                     self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_CMPHIGHER  => -1,
                                                                                                                         self::STREAMER_CHECK_CMPLOWER   => 6  )),

                    SYNC_PROVISION_MAXEMAILBODYTRUNCSIZE                => array (  self::STREAMER_VAR      => "maxemailbodytruncsize",
                                                                                     self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_CMPHIGHER  => -2 )),

                    SYNC_PROVISION_MAXEMAILHTMLBODYTRUNCSIZE            => array (  self::STREAMER_VAR      => "maxemailhtmlbodytruncsize",
                                                                                     self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_CMPHIGHER  => -2 )),

                    SYNC_PROVISION_REQSIGNEDSMIMEMESSAGES               => array (  self::STREAMER_VAR      => "reqsignedsmimemessages",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),

                    SYNC_PROVISION_REQENCSMIMEMESSAGES                  => array (  self::STREAMER_VAR      => "reqencsmimemessages",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),

                    SYNC_PROVISION_REQSIGNEDSMIMEALGORITHM              => array (  self::STREAMER_VAR      => "reqsignedsmimealgorithm",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),

                    SYNC_PROVISION_REQENCSMIMEALGORITHM                 => array (  self::STREAMER_VAR      => "reqencsmimealgorithm",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(0,1,2,3,4) )),

                    SYNC_PROVISION_ALLOWSMIMEENCALGORITHNEG             => array (  self::STREAMER_VAR      => "allowsmimeencalgneg",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(0,1,2) )),

                    SYNC_PROVISION_ALLOWSMIMESOFTCERTS                  => array (  self::STREAMER_VAR      => "allowsmimesoftcerts",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),

                    SYNC_PROVISION_ALLOWBROWSER                         => array (  self::STREAMER_VAR      => "allowbrowser",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),

                    SYNC_PROVISION_ALLOWCONSUMEREMAIL                   => array (  self::STREAMER_VAR      => "allowconsumeremail",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),

                    SYNC_PROVISION_ALLOWREMOTEDESKTOP                   => array (  self::STREAMER_VAR      => "allowremotedesk",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),

                    SYNC_PROVISION_ALLOWINTERNETSHARING                 => array (  self::STREAMER_VAR      => "allowinternetsharing",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(0,1) )),

                    SYNC_PROVISION_UNAPPROVEDINROMAPPLIST               => array (  self::STREAMER_VAR      => "unapprovedinromapplist",
                                                                                    self::STREAMER_PROP     => self::STREAMER_TYPE_SEND_EMPTY,
                                                                                    self::STREAMER_ARRAY    => SYNC_PROVISION_APPNAME),  //TODO check

                    SYNC_PROVISION_APPROVEDAPPLIST                      => array (  self::STREAMER_VAR      => "approvedapplist",
                                                                                    self::STREAMER_PROP     => self::STREAMER_TYPE_SEND_EMPTY,
                                                                                    self::STREAMER_ARRAY    => SYNC_PROVISION_HASH), //TODO check
            );
        }

        parent::__construct($mapping);
    }

    /**
     * Loads provisioning policies into a SyncProvisioning object.
     *
     * @param array     $policies     array with policies' names and values
     * @param boolean   $logPolicies  optional, determines if the policies and values should be logged. Default: false
     *
     * @access public
     * @return void
     */
    public function Load($policies = array(), $logPolicies = false) {
        $this->LoadDefaultPolicies();

        $streamerVars = $this->GetStreamerVars();
        foreach ($policies as $p=>$v) {
            if (!in_array($p, $streamerVars)) {
                ZLog::Write(LOGLEVEL_INFO, sprintf("Policy '%s' not supported by the device, ignoring", $p));
                continue;
            }
            if ($logPolicies) {
                ZLog::Write(LOGLEVEL_WBXML, sprintf("Policy '%s' enforced with: %s (%s)", $p, (is_array($v)) ? Utils::PrintAsString(implode(',', $v)) : Utils::PrintAsString($v), gettype($v)));
            }
            $this->$p = (is_array($v) && empty($v)) ? array() : $v;
        }
    }

    /**
     * Loads default policies' values into a SyncProvisioning object.
     *
     * @access public
     * @return void
     */
    public function LoadDefaultPolicies() {
        //AS 12.0, 12.1 and 14.0 props
        $this->devpwenabled = 0;
        $this->alphanumpwreq = 0;
        $this->devencenabled = 0;
        $this->pwrecoveryenabled = 0;
        $this->docbrowseenabled;
        $this->attenabled = 1;
        $this->mindevpwlenngth = 4;
        $this->maxinacttimedevlock = 900;
        $this->maxdevpwfailedattempts = 8;
        $this->maxattsize = '';
        $this->allowsimpledevpw = 1;
        $this->devpwexpiration = 0;
        $this->devpwhistory = 0;

        //AS 12.1 and 14.0 props
        $this->allowstoragecard = 1;
        $this->allowcam = 1;
        $this->reqdevenc = 0;
        $this->allowunsignedapps = 1;
        $this->allowunsigninstallpacks = 1;
        $this->mindevcomplexchars = 3;
        $this->allowwifi = 1;
        $this->allowtextmessaging = 1;
        $this->allowpopimapemail = 1;
        $this->allowbluetooth = 2;
        $this->allowirda = 1;
        $this->reqmansyncroam = 0;
        $this->allowdesktopsync = 1;
        $this->maxcalagefilter = 0;
        $this->allowhtmlemail = 1;
        $this->maxemailagefilter = 0;
        $this->maxemailbodytruncsize = -1;
        $this->maxemailhtmlbodytruncsize = -1;
        $this->reqsignedsmimemessages = 0;
        $this->reqencsmimemessages = 0;
        $this->reqsignedsmimealgorithm = 0;
        $this->reqencsmimealgorithm = 0;
        $this->allowsmimeencalgneg = 2;
        $this->allowsmimesoftcerts = 1;
        $this->allowbrowser = 1;
        $this->allowconsumeremail = 1;
        $this->allowremotedesk = 1;
        $this->allowinternetsharing = 1;
        $this->unapprovedinromapplist = array();
        $this->approvedapplist = array();
    }

    /**
     * Returns the policy hash.
     *
     * @access public
     * @return string
     */
    public function GetPolicyHash() {
        return md5(serialize($this));
    }

    /**
     * Returns the SyncProvisioning instance.
     *
     * @param array     $policies     array with policies' names and values
     * @param boolean   $logPolicies  optional, determines if the policies and values should be logged. Default: false
     *
     * @access public
     * @return SyncProvisioning
     */
    public static function GetObjectWithPolicies($policies = array(), $logPolicies = false) {
        $p = new SyncProvisioning();
        $p->Load($policies, $logPolicies);
        return $p;
    }
}
