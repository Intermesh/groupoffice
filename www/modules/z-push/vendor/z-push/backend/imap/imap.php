<?php
/***********************************************
* File      :   imap.php
* Project   :   Z-Push
* Descr     :   This backend is based on
*               'BackendDiff' and implements an
*               IMAP interface
*
* Created   :   10.10.2007
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

// config file
require_once("backend/imap/config.php");

require_once("backend/imap/mime_calendar.php");
require_once("backend/imap/mime_encode.php");
require_once("backend/imap/user_identity.php");

// Add the path for Andrew's Web Libraries to include_path
// because it is required for the emails with ics attachments
// @see https://jira.z-hub.io/browse/ZP-1149
set_include_path(get_include_path() . PATH_SEPARATOR . '/usr/share/awl/inc' . PATH_SEPARATOR . dirname(__FILE__) . '/');

class BackendIMAP extends BackendDiff implements ISearchProvider {
    private $wasteID;
    private $sentID;
    private $server;
    private $mbox;
    private $mboxFolder;
    private $username;
    private $password;
    private $domain;
    private $sinkfolders = array();
    private $sinkstates = array();
    private $changessinkinit = false;
    private $folderhierarchy;
    private $excludedFolders;
    private static $mimeTypes = false;
    private $imapParams = array();

    private $dontStat = array();            //keys in this array represent mailboxes which can't be stat'd (ie, /NoSELECT status)
    
    //define constants for imap mailbox attributes
    const LATT_NOINFERIORS = 1;
    const LATT_NOSELECT = 2;
    const LATT_MARKED = 4;
    const LATT_UNMARKED = 8;
    const LATT_REFERRAL = 16;
    const LATT_HASCHILDREN = 32;
    const LATT_HASNOCHILDREN = 64;

    public function __construct() {
        if (BackendIMAP::$mimeTypes === false) {
            BackendIMAP::$mimeTypes = $this->SystemExtensionMimeTypes();
        }
        $this->wasteID = false;
        $this->sentID = false;
        $this->mboxFolder = "";

        if (!function_exists("imap_open"))
            throw new FatalException("BackendIMAP(): php-imap module is not installed", 0, null, LOGLEVEL_FATAL);

        if (!function_exists("mb_detect_order")) {
            ZLog::Write(LOGLEVEL_WARN, sprintf("BackendIMAP(): php-mbstring module is not installed, you should install it for better encoding conversions"));
        }
        if (defined("IMAP_DISABLE_AUTHENTICATOR")) {
            ZLog::Write(LOGLEVEL_INFO, sprintf("BackendIMAP(): The following authentication methods are disabled: %s", IMAP_DISABLE_AUTHENTICATOR));
            $this->imapParams = array("DISABLE_AUTHENTICATOR" => array_map('trim' ,explode(',', IMAP_DISABLE_AUTHENTICATOR)));
        }
    }

    /**----------------------------------------------------------------------------------------------------------
     * default backend methods
     */

    /**
     * Authenticates the user
     *
     * @param string        $username
     * @param string        $domain
     * @param string        $password
     *
     * @access public
     * @return boolean
     * @throws FatalException   if php-imap module can not be found
     */
    public function Logon($username, $domain, $password) {
        $this->wasteID = false;
        $this->sentID = false;
        $this->server = "{" . IMAP_SERVER . ":" . IMAP_PORT . "/imap" . IMAP_OPTIONS . "}";

        if (!function_exists("imap_open"))
            throw new FatalException("BackendIMAP(): php-imap module is not installed", 0, null, LOGLEVEL_FATAL);

        if (defined('IMAP_FOLDER_CONFIGURED') && IMAP_FOLDER_CONFIGURED == false)
            throw new FatalException("BackendIMAP(): You didn't configure your IMAP folder names. Do it before!", 0, null, LOGLEVEL_FATAL);

        /* BEGIN fmbiete's contribution r1527, ZP-319 */
        $this->excludedFolders = array();
        if (defined('IMAP_EXCLUDED_FOLDERS') && strlen(IMAP_EXCLUDED_FOLDERS) > 0) {
            $this->excludedFolders = explode("|", IMAP_EXCLUDED_FOLDERS);
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->Logon(): Excluding Folders (%s)", IMAP_EXCLUDED_FOLDERS));
        }
        /* END fmbiete's contribution r1527, ZP-319 */

        // open the IMAP-mailbox
        $this->mbox = @imap_open($this->server , $username, $password, OP_HALFOPEN, 0, $this->imapParams);
        $this->mboxFolder = "";

        if ($this->mbox) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->Logon(): User '%s' is authenticated on '%s'", $username, $this->server));
            $this->username = $username;
            $this->password = $password;
            $this->domain = $domain;
            return true;
        }
        else {
            ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendIMAP->Logon(): can't connect as user '%s' on '%s': %s", $username, $this->server, imap_last_error()));
            return false;
        }
    }

    /**
     * Logs off
     * Called before shutting down the request to close the IMAP connection
     * writes errors to the log
     *
     * @access public
     * @return boolean
     */
    public function Logoff() {
        $this->close_connection();
        $this->SaveStorages();
    }

    /**
     * Sends an e-mail
     * This messages needs to be saved into the 'sent items' folder
     *
     * @param SyncSendMail  $sm     SyncSendMail object
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
    public function SendMail($sm) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->SendMail(): RFC822: %d bytes  forward-id: '%s' reply-id: '%s' parent-id: '%s' SaveInSent: '%s' ReplaceMIME: '%s'",
                                            strlen($sm->mime),
                                            Utils::PrintAsString($sm->forwardflag ? (isset($sm->source->itemid) ? $sm->source->itemid : "error no itemid") : false),
                                            Utils::PrintAsString($sm->replyflag ? (isset($sm->source->itemid) ? $sm->source->itemid : "error no itemid") : false),
                                            Utils::PrintAsString((isset($sm->source->folderid) ? $sm->source->folderid : false)),
                                            Utils::PrintAsString(($sm->saveinsent)), Utils::PrintAsString(isset($sm->replacemime))));

        // by splitting the message in several lines we can easily grep later
        foreach(preg_split("/((\r)?\n)/", $sm->mime) as $rfc822line)
            ZLog::Write(LOGLEVEL_WBXML, "RFC822: ". $rfc822line);

        $sourceMessage = $sourceMail = false;
        // If we have a reference to a source message and we are not replacing mime (since we wouldn't use it)
        if (isset($sm->source->folderid) && isset($sm->source->itemid) && (!isset($sm->replacemime) || $sm->replacemime === false)) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->SendMail(): We have a source message and we try to fetch it"));
            $parent = $this->getImapIdFromFolderId($sm->source->folderid);
            if ($parent === false) {
                throw new StatusException(sprintf("BackendIMAP->SendMail(): Could not get imapid from source folderid '%'", $sm->source->folderid), SYNC_COMMONSTATUS_ITEMNOTFOUND);
            }
            else {
                $this->imap_reopen_folder($parent);
                $sourceMail = @imap_fetchheader($this->mbox, $sm->source->itemid, FT_UID) . @imap_body($this->mbox, $sm->source->itemid, FT_PEEK | FT_UID);
                $mobj = new Mail_mimeDecode($sourceMail);
                $sourceMessage = $mobj->decode(array('decode_headers' => false, 'decode_bodies' => true, 'include_bodies' => true, 'rfc_822bodies' => true, 'charset' => 'utf-8'));
                unset($mobj);
                //We will need $sourceMail if the message is forwarded and not inlined

                // If it's a reply, we mark the original message as answered
                if ($sm->replyflag) {
                    if (!@imap_setflag_full($this->mbox, $sm->source->itemid, "\\Answered", ST_UID)) {
                        ZLog::Write(LOGLEVEL_WARN, sprintf("BackendIMAP->SendMail(): Unable to mark the message as Answered"));
                    }
                }

                // If it's a forward, we mark the original message as forwarded
                if ($sm->forwardflag) {
                    if (!@imap_setflag_full($this->mbox, $sm->source->itemid, "\\Forwarded", ST_UID)) {
                        ZLog::Write(LOGLEVEL_WARN, sprintf("BackendIMAP->SendMail(): Unable to mark the message as Forwarded"));
                    }
                }
            }
        }

        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->SendMail(): We get the new message"));
        $mobj = new Mail_mimeDecode($sm->mime);
        $message = $mobj->decode(array('decode_headers' => 'utf-8', 'decode_bodies' => true, 'include_bodies' => true, 'rfc_822bodies' => true, 'charset' => 'utf-8'));
        unset($mobj);

        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->SendMail(): We get the From and To"));
        $Mail_RFC822 = new Mail_RFC822();

        $toaddr = "";
        $this->setFromHeaderValue($message->headers);
        $fromaddr = $this->parseAddr($Mail_RFC822->parseAddressList($message->headers["from"]));

        if (isset($message->headers["to"])) {
            $toaddr = $this->parseAddr($Mail_RFC822->parseAddressList($message->headers["to"]));
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->SendMail(): To defined: %s", $toaddr));
        }

        if (isset($message->headers["to"])) {
            $message->headers["to"] = Utils::CheckAndFixEncodingInHeadersOfSentMail($Mail_RFC822->parseAddressList($message->headers["to"]));
        }
        if (isset($message->headers["cc"])) {
            $message->headers["cc"] = Utils::CheckAndFixEncodingInHeadersOfSentMail($Mail_RFC822->parseAddressList($message->headers["cc"]));
        }

        unset($Mail_RFC822);

        if (isset($message->headers["subject"]) && mb_detect_encoding($message->headers["subject"], "UTF-8") != false && preg_match('/[^\x00-\x7F]/', $message->headers["subject"]) == 1) {
            mb_internal_encoding("UTF-8");
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->SendMail(): Subject in raw UTF-8: %s", $message->headers["subject"]));
            $message->headers["subject"] = mb_encode_mimeheader($message->headers["subject"]);
        }

        $this->setReturnPathValue($message->headers, $fromaddr);

        $finalBody = "";
        $finalHeaders = array();

        // if it's a S/MIME message or has VCALENDAR objects I don't do anything with it
        if (is_smime($message) || has_calendar_object($message)) {
            $mobj = new Mail_mimeDecode($sm->mime);
            $parts =  $mobj->getSendArray();
            unset($mobj);
            if ($parts === false) {
                throw new StatusException(sprintf("BackendIMAP->SendMail(): Could not getSendArray for SMIME messages"), SYNC_COMMONSTATUS_MAILSUBMISSIONFAILED);
            }
            else {
                list($recipients, $finalHeaders, $finalBody) = $parts;

                $this->setFromHeaderValue($finalHeaders);
                $this->setReturnPathValue($finalHeaders, $fromaddr);
            }
        }
        else {
            //http://pear.php.net/manual/en/package.mail.mail-mime.example.php
            //http://pear.php.net/manual/en/package.mail.mail-mimedecode.decode.php
            //http://pear.php.net/manual/en/package.mail.mail-mimepart.addsubpart.php

            // I don't mind if the new message is multipart or not, I always will create a multipart. It's simpler
            $finalEmail = new Mail_mimePart('', array('content_type' => 'multipart/mixed'));

            if ($sm->replyflag && (!isset($sm->replacemime) || $sm->replacemime === false)) {
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->SendMail(): Replying message"));
                $this->addTextParts($finalEmail, $message, $sourceMessage, true);

                if (isset($message->parts)) {
                    // We add extra parts from the replying message
                    add_extra_sub_parts($finalEmail, $message->parts);
                }
                // A replied message doesn't include the original attachments
            }
            else if ($sm->forwardflag && (!isset($sm->replacemime) || $sm->replacemime === false)) {
                if (!defined('IMAP_INLINE_FORWARD') || IMAP_INLINE_FORWARD === false) {
                    ZLog::Write(LOGLEVEL_DEBUG, "BackendIMAP->SendMail(): Forwarding message as attached file - eml");
                    $finalEmail->addSubPart($sourceMail, array('content_type' => 'message/rfc822', 'encoding' => 'base64', 'disposition' => 'attachment', 'dfilename' => 'forwarded_message.eml'));
                }
                else {
                    ZLog::Write(LOGLEVEL_DEBUG, "BackendIMAP->SendMail(): Forwarding inlined message");
                    $this->addTextParts($finalEmail, $message, $sourceMessage, false);

                    if (isset($message->parts)) {
                        // We add extra parts from the forwarding message
                        add_extra_sub_parts($finalEmail, $message->parts);
                    }
                    if (isset($sourceMessage->parts)) {
                        // We add extra parts from the forwarded message
                        add_extra_sub_parts($finalEmail, $sourceMessage->parts);
                    }
                }
            }
            else {
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->SendMail(): is a new message or we are replacing mime"));
                $this->addTextPartsMessage($finalEmail, $message);
                if (isset($message->parts)) {
                    // We add extra parts from the new message
                    add_extra_sub_parts($finalEmail, $message->parts);
                }
            }

            // We encode the final message
            $boundary = '=_' . md5(rand() . microtime());
            $finalEmail = $finalEmail->encode($boundary);

            $finalHeaders = array('MIME-Version' => '1.0');
            // We copy all the non-existent headers, minus content_type
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->SendMail(): Copying new headers"));
            foreach ($message->headers as $k => $v) {
                if (strcasecmp($k, 'content-type') != 0 && strcasecmp($k, 'content-transfer-encoding') != 0 && strcasecmp($k, 'mime-version') != 0) {
                    if (!isset($finalHeaders[$k]))
                        $finalHeaders[ucwords($k)] = $v;
                }
            }
            foreach ($finalEmail['headers'] as $k => $v) {
                if (!isset($finalHeaders[$k]))
                    $finalHeaders[$k] = $v;
            }

            $finalBody = "This is a multi-part message in MIME format.\n" . $finalEmail['body'];

            unset($finalEmail);
        }

        unset($sourceMail);
        unset($message);
        unset($sourceMessage);

        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->SendMail(): Final mail to send:"));
        foreach ($finalHeaders as $k => $v)
            ZLog::Write(LOGLEVEL_WBXML, sprintf("%s: %s", $k, $v));
        foreach (preg_split("/((\r)?\n)/", $finalBody) as $bodyline)
            ZLog::Write(LOGLEVEL_WBXML, sprintf("Body: %s", $bodyline));

        $send = $this->sendMessage($fromaddr, $toaddr, $finalHeaders, $finalBody);

        if ($send) {
            if (isset($sm->saveinsent)) {
                $this->saveSentMessage($finalHeaders, $finalBody);
            }
            else {
                ZLog::Write(LOGLEVEL_DEBUG, "BackendIMAP->SendMail(): Not saving in SentFolder");
            }
        }

        unset($finalHeaders);
        unset($finalBody);

        return $send;
    }

    /**
     * Add text parts to a mimepart object, with reply or forward tags
     *
     * @param Mail_mimePart $email reference to the object
     * @param Mail_mimeDecode $message reference to the message
     * @param Mail_mimeDecode $sourceMessage reference to the original message
     * @param boolean $isReply true if it's a reply, false if it's a forward
     *
     * @access private
     * @return void
     */
    private function addTextParts(&$email, &$message, &$sourceMessage, $isReply = true) {
        $htmlBody = $plainBody = '';
        Mail_mimeDecode::getBodyRecursive($message, "html", $htmlBody);
        Mail_mimeDecode::getBodyRecursive($message, "plain", $plainBody);
        $htmlSource = $plainSource = '';
        Mail_mimeDecode::getBodyRecursive($sourceMessage, "html", $htmlSource);
        Mail_mimeDecode::getBodyRecursive($sourceMessage, "plain", $plainSource);

        $separator = '';
        if ($isReply) {
            $separator = ">\r\n";
            $separatorHtml = "<blockquote>";
            $separatorHtmlEnd = "</blockquote></body></html>";
        }
        else {
            $separator = "";
            $separatorHtml = "<div>";
            $separatorHtmlEnd = "</div>";
        }

        $altEmail = new Mail_mimePart('', array('content_type' => 'multipart/alternative'));

        if (strlen($htmlBody) > 0) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->addTextParts(): The message has HTML body"));
            if (strlen($htmlSource) > 0) {
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->addTextParts(): The original message had HTML body"));
                $altEmail->addSubPart($htmlBody . $separatorHtml . $htmlSource . $separatorHtmlEnd, array('content_type' => 'text/html; charset=utf-8', 'encoding' => 'base64'));
            }
            else {
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->addTextParts(): The original message had not HTML body, we use original PLAIN body to create HTML"));
                $altEmail->addSubPart($htmlBody . $separatorHtml . "<p>" . $plainSource . "</p>" . $separatorHtmlEnd, array('content_type' => 'text/html; charset=utf-8', 'encoding' => 'base64'));
            }
        }
        if (strlen($plainBody) > 0) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->addTextParts(): The message has PLAIN body"));
            if (strlen($plainSource) > 0) {
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->addTextParts(): The original message had PLAIN body"));
                $altEmail->addSubPart($plainBody . $separator . str_replace("\n", "\n> ", "> " . $plainSource), array('content_type' => 'text/plain; charset=utf-8', 'encoding' => 'base64'));
            }
            else {
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->addTextParts(): The original message had not PLAIN body, we use original HTML body to create PLAIN"));
                $altEmail->addSubPart($plainBody . $separator . str_replace("\n", "\n> ", "> " . Utils::ConvertHtmlToText($htmlSource)), array('content_type' => 'text/plain; charset=utf-8', 'encoding' => 'base64'));
            }
        }

        $boundary = '=_' . md5(rand() . microtime());
        $altEmail = $altEmail->encode($boundary);

        $email->addSubPart($altEmail['body'], array('content_type' => 'multipart/alternative;'."\n".' boundary="'.$boundary.'"'));

        unset($altEmail);

        unset($htmlBody);
        unset($htmlSource);
        unset($plainBody);
        unset($plainSource);
    }

    /**
     * Add text parts to a mimepart object
     *
     * @param Mail_mimePart $email reference to the object
     * @param Mail_mimeDecode $message reference to the message
     *
     * @access private
     * @return void
     */
    private function addTextPartsMessage(&$email, &$message) {
        $altEmail = new Mail_mimePart('', array('content_type' => 'multipart/alternative'));

        foreach (array("plain", "html", "calendar") as $type) {
            $body = '';
            Mail_mimeDecode::getBodyRecursive($message, $type, $body);
            if (strlen($body) > 0) {
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->addTextPartsMessage(): The message has %s body", $type));
                $altEmail->addSubPart($body, array('content_type' => sprintf("text/%s; charset=utf-8", $type), 'encoding' => 'base64'));
            }
        }
        unset($body);

        $boundary = '=_' . md5(rand() . microtime());
        $altEmail = $altEmail->encode($boundary);

        $email->addSubPart($altEmail['body'], array('content_type' => 'multipart/alternative;'."\n".' boundary="'.$boundary.'"'));

        unset($altEmail);
    }

    /**
     * Returns the waste basket
     *
     * @access public
     * @return string
     */
    public function GetWasteBasket() {
        // TODO this could be retrieved from the DeviceFolderCache
        if ($this->wasteID == false) {
            //try to get the waste basket without doing complete hierarchy sync
            $folder_name = IMAP_FOLDER_TRASH;
            if (defined('IMAP_FOLDER_PREFIX') && strlen(IMAP_FOLDER_PREFIX) > 0)
                $folder_name = IMAP_FOLDER_PREFIX . $this->getServerDelimiter() . $folder_name;
            $wastebaskt = @imap_getmailboxes($this->mbox, $this->server, $folder_name);
            if (isset($wastebaskt[0])) {
                $this->wasteID = $this->convertImapId(substr($wastebaskt[0]->name, strlen($this->server)));
                return $this->wasteID;
            }
            //try get waste id from hierarchy if it wasn't possible with above for some reason
            $this->GetHierarchy();
        }
        return $this->wasteID;
    }

    /**
     * Returns the content of the named attachment as stream. The passed attachment identifier is
     * the exact string that is returned in the 'AttName' property of an SyncAttachment.
     * Any information necessary to find the attachment must be encoded in that 'attname' property.
     *
     * @param string        $attname
     *
     * @access public
     * @return SyncItemOperationsAttachment
     * @throws StatusException
     */
    public function GetAttachmentData($attname) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->GetAttachmentData('%s')", $attname));

        list($folderid, $id, $part) = explode(":", $attname);

        if (!isset($folderid) || !isset($id) || !isset($part))
            throw new StatusException(sprintf("BackendIMAP->GetAttachmentData('%s'): Error, attachment name key can not be parsed", $attname), SYNC_ITEMOPERATIONSSTATUS_INVALIDATT);

        // convert back to work on an imap-id
        $folderImapid = $this->getImapIdFromFolderId($folderid);

        $this->imap_reopen_folder($folderImapid);
        $mail = @imap_fetchheader($this->mbox, $id, FT_UID) . @imap_body($this->mbox, $id, FT_PEEK | FT_UID);

        if (empty($mail)) {
            throw new StatusException(sprintf("BackendIMAP->GetAttachmentData('%s'): Error, message not found, maybe was moved", $attname), SYNC_ITEMOPERATIONSSTATUS_INVALIDATT);
        }

        $mobj = new Mail_mimeDecode($mail);
        $message = $mobj->decode(array('decode_headers' => 'utf-8', 'decode_bodies' => true, 'include_bodies' => true, 'rfc_822bodies' => true, 'charset' => 'utf-8'));

        if (!isset($message->parts)) {
            throw new StatusException(sprintf("BackendIMAP->GetAttachmentData('%s'): Error, message without parts. Requesting part key: '%d'", $attname, $part), SYNC_ITEMOPERATIONSSTATUS_INVALIDATT);
        }

        /* BEGIN fmbiete's contribution r1528, ZP-320 */
        //trying parts
        $mparts = $message->parts;
        for ($i = 0; $i < count($mparts); $i++) {
            $auxpart = $mparts[$i];
            //recursively add parts
            if($auxpart->ctype_primary == "multipart" && ($auxpart->ctype_secondary == "mixed" || $auxpart->ctype_secondary == "alternative"  || $auxpart->ctype_secondary == "related")) {
                foreach($auxpart->parts as $spart)
                    $mparts[] = $spart;
            }
        }
        /* END fmbiete's contribution r1528, ZP-320 */

        if (!isset($mparts[$part]->body))
            throw new StatusException(sprintf("BackendIMAP->GetAttachmentData('%s'): Error, requested part key can not be found: '%d'", $attname, $part), SYNC_ITEMOPERATIONSSTATUS_INVALIDATT);

        // unset mimedecoder & mail
        unset($mobj);
        unset($mail);

        $attachment = new SyncItemOperationsAttachment();
        /* BEGIN fmbiete's contribution r1528, ZP-320 */
        $attachment->data = StringStreamWrapper::Open($mparts[$part]->body);
        if (isset($mparts[$part]->ctype_primary) && isset($mparts[$part]->ctype_secondary))
            $attachment->contenttype = $mparts[$part]->ctype_primary .'/'.$mparts[$part]->ctype_secondary;

        unset($mparts);
        unset($message);

        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->GetAttachmentData contenttype %s", $attachment->contenttype));
        /* END fmbiete's contribution r1528, ZP-320 */

        return $attachment;
    }


    /**
     * Deletes all contents of the specified folder.
     * This is generally used to empty the trash (wastebasked), but could also be used on any
     * other folder.
     *
     * @param string        $folderid
     * @param boolean       $includeSubfolders      (opt) also delete sub folders, default true
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
    public function EmptyFolder($folderid, $includeSubfolders = true) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->EmptyFolder('%s', '%s')", $folderid, Utils::PrintAsString($includeSubfolders)));

        $folderImapid = $this->getImapIdFromFolderId($folderid);
        if ($folderImapid === false) {
            throw new StatusException(sprintf("BackendIMAP->EmptyFolder('%s','%s'): Error, unable to open folder (no entry id)", $folderid, Utils::PrintAsString($includeSubfolders)), SYNC_ITEMOPERATIONSSTATUS_SERVERERROR);
        }

        if (!$this->imap_reopen_folder($folderImapid)) {
            throw new StatusException(sprintf("BackendIMAP->EmptyFolder('%s','%s'): Error, unable to open parent folder (open entry)", $folderid, Utils::PrintAsString($includeSubfolders)), SYNC_ITEMOPERATIONSSTATUS_SERVERERROR);
        }

        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->EmptyFolder('%s','%s'): emptying folder", $folderid, Utils::PrintAsString($includeSubfolders)));

        // TODO: make transactional all these deletes: see comment bellow
        if (@imap_delete($this->mbox, "1:*")) {
            @imap_expunge($this->mbox);


            // An error erasing any subfolder won't return an error to the device, because we should undelete the already expunged messages, and we cannot undelete a folder
            if ($includeSubfolders) {
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->EmptyFolder('%s','%s'): deleting subfolders", $folderid, Utils::PrintAsString($includeSubfolders)));

                // Find subfolders
                $subfolders = @imap_getmailboxes($this->mbox, $this->server . $folderImapid, "*");
                if (is_array($subfolders)) {

                    // delete mailbox and its content
                    foreach ($subfolders as $val) {
                        $subname = substr($val->name, strlen($this->server));
                        if (!@imap_deletemailbox($this->mbox, $val->name)) {
                            ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendIMAP->EmptyFolder('%s','%s'): Error deleting subfolder %s", $folderid, Utils::PrintAsString($includeSubfolders), $subname));
                        }
                    }
                }
                else {
                    ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendIMAP->EmptyFolder('%s','%s'): Error getting subfolder list", $folderid, Utils::PrintAsString($includeSubfolders)));
                }
            }
        }
        else {
            throw new StatusException(sprintf("BackendIMAP->EmptyFolder('%s','%s'): Error, imap_delete() failed; '%s'", $folderid, Utils::PrintAsString($includeSubfolders), @imap_last_error()), SYNC_ITEMOPERATIONSSTATUS_SERVERERROR);
        }

        return true;
    }


    /**
     * Indicates if the backend has a ChangesSink.
     * A sink is an active notification mechanism which does not need polling.
     * The IMAP backend simulates a sink by polling status information of the folder
     *
     * @access public
     * @return boolean
     */
    public function HasChangesSink() {
        return true;
    }

    /**
     * The folder should be considered by the sink.
     * Folders which were not initialized should not result in a notification
     * of IBacken->ChangesSink().
     *
     * @param string        $folderid
     *
     * @access public
     * @return boolean      false if found can not be found
     */
    public function ChangesSinkInitialize($folderid) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->ChangesSinkInitialize(): folderid '%s'", $folderid));

        $imapid = $this->getImapIdFromFolderId($folderid);

        if (!$this->changessinkinit) {
            // First folder, store the actual folder structure
            $list= $this->get_attributes_list();
            foreach ($list as $l) {
                //ZLog::Write(LOGLEVEL_INFO, sprintf("BackendIMAP->ChangesSinkInitialize(): adding '%s' with attributes: '%s'", $l['name'], print_r($l,true)));
                $this->folderhierarchy[] = $l['name'];
                if (isset($l['noSelect']) && $l['noSelect'] != false) {
                    $dontStatFolder = str_replace( $this->server, '', $l['name']);
                    //ZLog::Write(LOGLEVEL_INFO, sprintf("BackendIMAP->ChangesSinkInitialize(): adding '%s' to dontStatFolders()", $dontStatFolder));
                    $this->dontStatFolders[$dontStatFolder] = true;
                }
            }
        }

        if (($imapid !== false) && !(isset($this->dontStatFolders[$imapid]) )) {
            $this->sinkfolders[] = $imapid;
            $this->changessinkinit = true;
        }

        return $this->changessinkinit;
    }

    /**
     * The actual ChangesSink.
     * For max. the $timeout value this method should block and if no changes
     * are available return an empty array.
     * If changes are available a list of folderids is expected.
     *
     * @param int           $timeout        max. amount of seconds to block
     *
     * @access public
     * @return array
     */
    public function ChangesSink($timeout = 30) {
        $notifications = array();
        $stopat = time() + $timeout - 1;

        //We can get here and the ChangesSink not be initialized yet
        if (!$this->changessinkinit) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP>ChangesSink - Not initialized ChangesSink, sleep and exit"));
            // We sleep and do nothing else
            sleep($timeout);
            return $notifications;
        }

        // Reconnect IMAP server
        $this->imap_reconnect();

        // Check folder hierarchy and create change
        if (count(array_diff($this->folderhierarchy, $this->get_folder_list())) > 0) {
            ZLog::Write(LOGLEVEL_DEBUG, "BackendIMAP->ChangesSink(): Changes in folder hierarchy detected!!");
             throw new StatusException("BackendIMAP->ChangesSink(): HierarchySync required.", SyncCollections::HIERARCHY_CHANGED);
        }

        // only check once to reduce pressure in the IMAP server
        foreach ($this->sinkfolders as $i => $imapid) {
            $this->imap_reopen_folder($imapid);

            // courier-imap only clears the status cache after checking
            @imap_check($this->mbox);

            $status = @imap_status($this->mbox, $this->server . $imapid, SA_ALL);
            if (!$status) {
                ZLog::Write(LOGLEVEL_WARN, sprintf("ChangesSink: could not stat folder '%s': %s ", $this->getFolderIdFromImapId($imapid), imap_last_error()));
            }
            else {
                $newstate = "M:". $status->messages ."-R:". $status->recent ."-U:". $status->unseen;

                if (! isset($this->sinkstates[$imapid]) ) {
                    $this->sinkstates[$imapid] = $newstate;
                }

                if ($this->sinkstates[$imapid] != $newstate) {
                    $notifications[] = $this->getFolderIdFromImapId($imapid);
                    $this->sinkstates[$imapid] = $newstate;
                    ZLog::Write(LOGLEVEL_DEBUG, "BackendIMAP->ChangesSink(): ChangesSink detected!!");
                }
            }
        }
        // Close IMAP connection, we will reconnect in the next execution. This will reduce IMAP pressure
        $this->close_connection();

        // Wait to timeout
        if (empty($notifications)) {
            while ($stopat > time()) {
                sleep(1);
            }
        }

        return $notifications;
    }


    /**----------------------------------------------------------------------------------------------------------
     * implemented DiffBackend methods
     */


    /**
     * Returns a list (array) of folders.
     *
     * @access public
     * @return array/boolean        false if the list could not be retrieved
     */
    public function GetFolderList() {
        $folders = array();

        $list = $this->get_folder_list();
        foreach ($list as $val) {
            // don't return the excluded folders
            $notExcluded = true;
            for ($i = 0, $cnt = count($this->excludedFolders); $notExcluded && $i < $cnt; $i++) { // expr1, expr2 modified by mku ZP-329
                // fix exclude folders with special chars by mku ZP-329
                if (strpos(strtolower($val), strtolower(Utils::Utf8_to_utf7imap($this->excludedFolders[$i]))) !== false) {
                    $notExcluded = false;
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("Pattern: <%s> found, excluding folder: '%s'", $this->excludedFolders[$i], $val)); // sprintf added by mku ZP-329
                }
            }

            if ($notExcluded) {
                $box = array();
                // cut off serverstring
                $imapid = substr($val, strlen($this->server));
                $box["id"] = $this->convertImapId($imapid);

                $folders[] = $box;
            }
        }

        return $folders;
    }

    /**
     * Returns an actual SyncFolder object
     *
     * @param string        $id           id of the folder
     *
     * @access public
     * @return object       SyncFolder with information
     */
    public function GetFolder($id) {
        $folder = new SyncFolder();
        $folder->serverid = $id;

        // convert back to work on an imap-id
        $imapid = $this->getImapIdFromFolderId($id);

        // explode hierarchy
        $fhir = explode($this->getServerDelimiter(), $imapid);

        // TODO WasteID or SentID could be saved for later ussage
        if (strcasecmp($imapid, $this->create_name_folder(IMAP_FOLDER_INBOX)) == 0) {
            $folder->parentid = "0";
            $folder->displayname = "Inbox";
            $folder->type = SYNC_FOLDER_TYPE_INBOX;
        }
        else if (strcasecmp($imapid, $this->create_name_folder(IMAP_FOLDER_DRAFT)) == 0) {
            $folder->parentid = "0";
            $folder->displayname = "Drafts";
            $folder->type = SYNC_FOLDER_TYPE_DRAFTS;
        }
        else if (strcasecmp($imapid, $this->create_name_folder(IMAP_FOLDER_SENT)) == 0) {
            $folder->parentid = "0";
            $folder->displayname = "Sent";
            $folder->type = SYNC_FOLDER_TYPE_SENTMAIL;
            $this->sentID = $id;
        }
        else if (strcasecmp($imapid, $this->create_name_folder(IMAP_FOLDER_TRASH)) == 0) {
            $folder->parentid = "0";
            $folder->displayname = "Trash";
            $folder->type = SYNC_FOLDER_TYPE_WASTEBASKET;
            $this->wasteID = $id;
        }
        else if (strcasecmp($imapid, $this->create_name_folder(IMAP_FOLDER_SPAM)) == 0) {
            $folder->parentid = "0";
            $folder->displayname = "Junk";
            $folder->type = SYNC_FOLDER_TYPE_USER_MAIL;
        }
        else if (strcasecmp($imapid, $this->create_name_folder(IMAP_FOLDER_ARCHIVE)) == 0) {
            $folder->parentid = "0";
            $folder->displayname = "Archive";
            $folder->type = SYNC_FOLDER_TYPE_USER_MAIL;
        }
        else {
            if (defined('IMAP_FOLDER_PREFIX') && strlen(IMAP_FOLDER_PREFIX) > 0) {
                if (strcasecmp($fhir[0], IMAP_FOLDER_PREFIX) == 0) {
                    // Discard prefix
                    array_shift($fhir);
                }
                else {
                    ZLog::Write(LOGLEVEL_WARN, sprintf("BackendIMAP->GetFolder('%s'): '%s'; using server delimiter '%s', first part '%s' is not equal to the prefix defined '%s'. Something is wrong with your config.", $id, $imapid, $this->getServerDelimiter(), $fhir[0], IMAP_FOLDER_PREFIX));
                }
            }

            if (count($fhir) == 1) {
                $folder->displayname = Utils::Utf7imap_to_utf8($fhir[0]);
                $folder->parentid = "0";
            }
            else {
                $this->getModAndParentNames($fhir, $folder->displayname, $imapparent);
                $folder->displayname = Utils::Utf7imap_to_utf8($folder->displayname);
                if ($imapparent === null) {
                    ZLog::Write(LOGLEVEL_WARN, sprintf("BackendIMAP->GetFolder('%s'): '%s'; we didn't found a valid parent name for the folder, but we should... contact the developers for further info", $id, $imapid));
                    $folder->parentid = "0"; // We put the folder as root folder, so we see it
                }
                else {
                    $folder->parentid = $this->convertImapId($imapparent);
                }
            }
            $folder->type = SYNC_FOLDER_TYPE_USER_MAIL;
        }

        //advanced debugging
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->GetFolder('%s'): '%s'", $id, $folder));

        return $folder;
    }

    /**
     * Returns folder stats. An associative array with properties is expected.
     *
     * @param string        $id             id of the folder
     *
     * @access public
     * @return array
     */
    public function StatFolder($id) {
        $folder = $this->GetFolder($id);

        $stat = array();
        $stat["id"] = $id;
        $stat["parent"] = $folder->parentid;
        $stat["mod"] = $folder->displayname;

        return $stat;
    }

    /**
     * Creates or modifies a folder
     * The folder type is ignored in IMAP, as all folders are Email folders
     *
     * @param string        $folderid       id of the parent folder
     * @param string        $oldid          if empty -> new folder created, else folder is to be renamed
     * @param string        $displayname    new folder name (to be created, or to be renamed to)
     * @param int           $type           folder type
     *
     * @access public
     * @return boolean                      status
     * @throws StatusException              could throw specific SYNC_FSSTATUS_* exceptions
     *
     */
    public function ChangeFolder($folderid, $oldid, $displayname, $type){
        ZLog::Write(LOGLEVEL_INFO, sprintf("BackendIMAP->ChangeFolder('%s','%s','%s','%s')", $folderid, $oldid, $displayname, $type));

        // if $id is set => rename mailbox, otherwise create
        if ($oldid) {
            // rename doesn't work properly with IMAP
            // the activesync client doesn't support a 'changing ID'
            // TODO this would be solved by implementing hex ids (Mantis #459)
            //$csts = imap_renamemailbox($this->mbox, $this->server . imap_utf7_encode(str_replace(".", $this->getServerDelimiter(), $oldid)), $newname);
            ZLog::Write(LOGLEVEL_ERROR, "BackendIMAP->ChangeFolder() : we do not support rename for now");
            return false;
        }
        else {

            // build name for new mailboxBackendMaildir
            $displayname = Utils::Utf8_to_utf7imap($displayname);

            if ($folderid == "0") {
                $newimapid = $displayname;
            }
            else {
                $imapid = $this->getImapIdFromFolderId($folderid);
                $newimapid = $imapid . $this->getServerDelimiter() . $displayname;
            }

            $csts = imap_createmailbox($this->mbox, $this->server . $newimapid);
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->ChangeFolder() createmailbox: '%s'", $newimapid));
            if ($csts) {
                imap_subscribe($this->mbox, $this->server . $newimapid);
                $newid = $this->convertImapId($newimapid);
                return $this->StatFolder($newid);
            }
            else {
                ZLog::Write(LOGLEVEL_WARN, "BackendIMAP->ChangeFolder() : mailbox creation failed");
                return false;
            }
        }
    }

    /**
     * Deletes a folder
     *
     * @param string        $id
     * @param string        $parent         is normally false
     *
     * @access public
     * @return boolean                      status - false if e.g. does not exist
     * @throws StatusException              could throw specific SYNC_FSSTATUS_* exceptions
     *
     */
    public function DeleteFolder($id, $parentid){
        $imapid = $this->getImapIdFromFolderId($id);
        if ($imapid) {
            return imap_deletemailbox($this->mbox, $this->server.$imapid);
        }

        return false;
    }

    /**
     * Returns a list (array) of messages
     *
     * @param string        $folderid       id of the parent folder
     * @param long          $cutoffdate     timestamp in the past from which on messages should be returned
     *
     * @access public
     * @return array/false  array with messages or false if folder is not available
     */
    public function GetMessageList($folderid, $cutoffdate) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->GetMessageList('%s','%s')", $folderid, $cutoffdate));

        $folderid = $this->getImapIdFromFolderId($folderid);

        if ($folderid == false)
            throw new StatusException("Folderid not found in cache", SYNC_STATUS_FOLDERHIERARCHYCHANGED);

        $messages = array();
        $this->imap_reopen_folder($folderid, true);

        $sequence = "1:*";
        if ($cutoffdate > 0) {
            $search = @imap_search($this->mbox, "SINCE ". date("d-M-Y", $cutoffdate));
            if ($search === false) {
                ZLog::Write(LOGLEVEL_INFO, sprintf("BackendIMAP->GetMessageList('%s','%s'): 0 result for the search or error: %s", $folderid, $cutoffdate, imap_last_error()));
                return $messages;
            }

            $sequence = implode(",", $search);
        }
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->GetMessageList(): searching with sequence '%s'", $sequence));
        $overviews = @imap_fetch_overview($this->mbox, $sequence);

        if (!is_array($overviews) || count($overviews) == 0) {
            $error = imap_last_error();
            if (strlen($error) > 0 && imap_num_msg($this->mbox) > 0) {
                ZLog::Write(LOGLEVEL_WARN, sprintf("BackendIMAP->GetMessageList('%s','%s'): Failed to retrieve overview: %s", $folderid, $cutoffdate, imap_last_error()));
            }
            return $messages;
        }

        foreach ($overviews as $overview) {
            // Determine the message's date and apply the cutoff; if the overview's ->udate property is
            // not available, fall back to the "Date:" header as it appears in the email.
            $date = 0;
            if (isset($overview->udate)) {
                $date = $overview->udate;
            } else if (isset($overview->date)) {
                $date = $this->cleanupDate($overview->date);
            }
            if ($date < $cutoffdate) {
                // Message is out of range; ignore it
                continue;
            }

            // cut of deleted messages
            if (isset($overview->deleted) && $overview->deleted)
                continue;

            if (isset($overview->uid)) {
                $message = array();
                $message["mod"] = $date;
                $message["id"] = $overview->uid;

                // 'seen' aka 'read'
                if (isset($overview->seen) && $overview->seen) {
                    $message["flags"] = 1;
                }
                else {
                    $message["flags"] = 0;
                }

                // 'flagged' aka 'FollowUp' aka 'starred'
                if (isset($overview->flagged) && $overview->flagged) {
                    $message["star"] = 1;
                }
                else {
                    $message["star"] = 0;
                }

                $messages[] = $message;
            }
        }
        return $messages;
    }

    /**
     * Returns the actual SyncXXX object type.
     *
     * @param string            $folderid           id of the parent folder
     * @param string            $id                 id of the message
     * @param ContentParameters $contentparameters  parameters of the requested message (truncation, mimesupport etc)
     *
     * @access public
     * @return object/false     false if the message could not be retrieved
     */
    public function GetMessage($folderid, $id, $contentparameters) {
        $truncsize = Utils::GetTruncSize($contentparameters->GetTruncation());
        $mimesupport = $contentparameters->GetMimeSupport();
        $bodypreference = $contentparameters->GetBodyPreference(); /* fmbiete's contribution r1528, ZP-320 */
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->GetMessage('%s', '%s', '%s')", $folderid,  $id, implode(",", $bodypreference)));

        $folderImapid = $this->getImapIdFromFolderId($folderid);

        $is_sent_folder = strcasecmp($folderImapid, $this->create_name_folder(IMAP_FOLDER_SENT)) == 0;

        // Get flags, etc
        $stat = $this->StatMessage($folderid, $id);

        if ($stat) {
            $this->imap_reopen_folder($folderImapid);
            $mail_headers = @imap_fetchheader($this->mbox, $id, FT_UID);
            $mail =  $mail_headers . @imap_body($this->mbox, $id, FT_PEEK | FT_UID);

            if (empty($mail)) {
                throw new StatusException(sprintf("BackendIMAP->GetMessage(): Error, message not found, maybe was moved"), SYNC_ITEMOPERATIONSSTATUS_INVALIDATT);
            }

            $mobj = new Mail_mimeDecode($mail);
            $message = $mobj->decode(array('decode_headers' => 'utf-8', 'decode_bodies' => true, 'include_bodies' => true, 'rfc_822bodies' => true, 'charset' => 'utf-8'));

            Utils::CheckAndFixEncodingInHeaders($mail, $message);

            $is_multipart = is_multipart($message);
            $is_smime = is_smime($message);
            $is_encrypted = $is_smime ? is_encrypted($message) : false;
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->GetMessage(): Message is multipart: %d, smime: %d, smime encrypted: %d", $is_multipart, $is_smime, $is_encrypted));

            //Select body type preference
            $bpReturnType = SYNC_BODYPREFERENCE_PLAIN;
            if ($bodypreference !== false) {
                $bpReturnType = Utils::GetBodyPreferenceBestMatch($bodypreference); // changed by mku ZP-330
            }
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->GetMessage(): getBodyPreferenceBestMatch: %d", $bpReturnType));

            // Prefered format is MIME -OR- message is SMIME -OR- the device supports MIME (iPhone) and doesn't really understand HTML
            if ($bpReturnType == SYNC_BODYPREFERENCE_MIME || $is_smime || in_array(SYNC_BODYPREFERENCE_MIME, $bodypreference)) {
                $bpReturnType = SYNC_BODYPREFERENCE_MIME;
            }

            // We need the text body even though MIME is used, for the preview
            $textBody = "";
            Mail_mimeDecode::getBodyRecursive($message, "html", $textBody, true);
            if (strlen($textBody) > 0) {
                if ($bpReturnType != SYNC_BODYPREFERENCE_MIME) {
                    $bpReturnType = SYNC_BODYPREFERENCE_HTML;
                }
            }
            else {
                Mail_mimeDecode::getBodyRecursive($message, "plain", $textBody, true);
                if ($bpReturnType != SYNC_BODYPREFERENCE_MIME) {
                    $bpReturnType = SYNC_BODYPREFERENCE_PLAIN;
                }
            }

            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->GetMessage(): after thinking a bit we will use: %d", $bpReturnType));


            $output = new SyncMail();

            if (Request::GetProtocolVersion() >= 12.0) {
                $output->asbody = new SyncBaseBody();

                Utils::CheckAndFixEncoding($textBody);

                $data = "";
                switch($bpReturnType) {
                    case SYNC_BODYPREFERENCE_PLAIN:
                        $data = $textBody;
                        break;
                    case SYNC_BODYPREFERENCE_HTML:
                        $data = $textBody;
                        break;
                    case SYNC_BODYPREFERENCE_MIME:
                        if ($is_smime) {
                            if ($is_encrypted) {
                                // #190, KD 2015-06-04 - If message body is encrypted only send the headers, as data should only be in the attachment
                                $data = $mail_headers;
                            }
                            else {
                                $data = $mail;
                            }
                        }
                        else {
                            $data = build_mime_message($message);
                        }
                        break;
                    case SYNC_BODYPREFERENCE_RTF:
                        ZLog::Write(LOGLEVEL_DEBUG, "BackendIMAP->GetMessage RTF Format NOT SUPPORTED");
                        // TODO: this is broken. This is no RTF.
                        $data = base64_encode($textBody);
                        break;
                }

                // truncate body, if requested.
                // MIME should not be truncated, but encrypted messages are truncated always to the headers size
                if ($bpReturnType == SYNC_BODYPREFERENCE_MIME) {
                    if ($is_encrypted) {
                        $output->asbody->truncated = 1;
                    }
                    else {
                        $output->asbody->truncated = 0;
                    }
                }
                else {
                    if (strlen($data) > $truncsize) {
                        $data = Utils::Utf8_truncate($data, $truncsize);
                        $output->asbody->truncated = 1;
                    }
                    else {
                        $output->asbody->truncated = 0;
                    }
                }

                // indicate in open that the data is HTML so it can be truncated correctly if required
                $output->asbody->data = StringStreamWrapper::Open($data, ($bpReturnType == SYNC_BODYPREFERENCE_HTML));
                $output->asbody->estimatedDataSize = strlen($data);
                unset($data);
                $output->asbody->type = $bpReturnType;
                if ($bpReturnType == SYNC_BODYPREFERENCE_MIME) {
                    // NativeBodyType can be only (1 => PLAIN, 2 => HTML, 3 => RTF). MIME uses 1
                    $output->nativebodytype = SYNC_BODYPREFERENCE_PLAIN;
                }
                else {
                    $output->nativebodytype = $bpReturnType;
                }

                $bpo = $contentparameters->BodyPreference($output->asbody->type);
                if (Request::GetProtocolVersion() >= 14.0 && $bpo->GetPreview()) {
                    // Preview must be always plaintext
                    $previewText = "";
                    Mail_mimeDecode::getBodyRecursive($message, "plain", $previewText, true);
                    if (strlen($previewText) == 0) {
                        Mail_mimeDecode::getBodyRecursive($message, "html", $previewText, true);
                        $previewText = Utils::ConvertHtmlToText($previewText);
                    }
                    $output->asbody->preview = Utils::Utf8_truncate($previewText, $bpo->GetPreview());
                }
            }
            /* END fmbiete's contribution r1528, ZP-320 */
            else { // ASV_2.5
                //DEPRECATED : very old devices, and incomplete code

                $output->bodytruncated = 0;
                $data = "";
                if ($bpReturnType == SYNC_BODYPREFERENCE_MIME) {
                   $data = $mail;
                }
                else {
                   $data = $textBody;
                }

                $output->mimesize = strlen($data);
                if (strlen($data) > $truncsize && $bpReturnType != SYNC_BODYPREFERENCE_MIME) {
                    $output->mimedata = StringStreamWrapper::Open(Utils::Utf8_truncate($data, $truncsize));
                    $output->mimetruncated = 1;
                }
                else {
                    $output->mimetruncated = 0;
                    $output->mimedata = StringStreamWrapper::Open($data);
                }
                unset($data);
            }

            unset($textBody);
            unset($mail_headers);

            $output->datereceived = isset($message->headers["date"]) ? $this->cleanupDate($message->headers["date"]) : null;

            if ($is_smime) {
                // #190, KD 2015-06-04 - Add Encrypted (and possibly signed) to the classifications emitted
                if ($is_encrypted) {
                    $output->messageclass = "IPM.Note.SMIME";
                }
                else {
                    $output->messageclass = "IPM.Note.SMIME.MultipartSigned";
                }
            }
            else {
                $output->messageclass = "IPM.Note";
            }
            $output->subject = isset($message->headers["subject"]) ? $message->headers["subject"] : "";
            $output->read = $stat["flags"];
            $output->from = isset($message->headers["from"]) ? $message->headers["from"] : null;

            if (isset($message->headers["thread-topic"])) {
                $output->threadtopic = $message->headers["thread-topic"];
            }
            else {
                $output->threadtopic = $output->subject;
            }

            // Language Code Page ID: http://msdn.microsoft.com/en-us/library/windows/desktop/dd317756%28v=vs.85%29.aspx
            $output->internetcpid = INTERNET_CPID_UTF8;
            if (Request::GetProtocolVersion() >= 12.0) {
                $output->contentclass = "urn:content-classes:message";

                $output->flag = new SyncMailFlags();
                if (isset($stat["star"]) && $stat["star"]) {
                    //flagstatus 0: clear, 1: complete, 2: active
                    $output->flag->flagstatus = SYNC_FLAGSTATUS_ACTIVE;
                    //flagtype: for follow up
                    $output->flag->flagtype = "FollowUp";
                }
                else {
                    $output->flag->flagstatus = SYNC_FLAGSTATUS_CLEAR;
                }
            }

            $Mail_RFC822 = new Mail_RFC822();
            $toaddr = $ccaddr = $replytoaddr = array();
            if(isset($message->headers["to"]))
                $toaddr = $Mail_RFC822->parseAddressList($message->headers["to"]);
            if(isset($message->headers["cc"]))
                $ccaddr = $Mail_RFC822->parseAddressList($message->headers["cc"]);
            if(isset($message->headers["reply-to"]))
                $replytoaddr = $Mail_RFC822->parseAddressList($message->headers["reply-to"]);

            $output->to = array();
            $output->cc = array();
            $output->reply_to = array();
            foreach(array("to" => $toaddr, "cc" => $ccaddr, "reply_to" => $replytoaddr) as $type => $addrlist) {
                if ($addrlist === false) {
                    //If we couldn't parse the addresslist we put the raw header (decoded)
                    if ($type == "reply_to") {
                        array_push($output->$type, $message->headers["reply-to"]);
                    }
                    else {
                        array_push($output->$type, $message->headers[$type]);
                    }
                }
                else {
                    foreach($addrlist as $addr) {
                        // If the address was a group we have "groupname" and "addresses" atributes
                        if (isset($addr->addresses)) {
                            if (count($addr->addresses) == 0) {
                                // readd the empty group delimiter
                                array_push($output->$type, sprintf("%s:;", $addr->groupname));
                                if (!isset($output->displayto) && strlen($addr->groupname) > 0) {
                                    $output->displayto = $addr->groupname;
                                }
                            }
                            else {
                                foreach($addr->addresses as $addr_group) {
                                    $name = $this->add_address_to_list($output->$type, $addr_group);
                                    if (!isset($output->displayto) && strlen($name) > 0) {
                                        $output->displayto = $name;
                                    }
                                }
                            }
                        }
                        else {
                            // Not a group
                            $name = $this->add_address_to_list($output->$type, $addr);
                            if (!isset($output->displayto) && strlen($name) > 0) {
                                $output->displayto = $name;
                            }
                        }
                    }
                }
            }

            // convert mime-importance to AS-importance
            if (isset($message->headers["x-priority"])) {
                $mimeImportance =  preg_replace("/\D+/", "", $message->headers["x-priority"]);
                //MAIL 1 - most important, 3 - normal, 5 - lowest
                //AS 0 - low, 1 - normal, 2 - important
                if ($mimeImportance > 3)
                    $output->importance = 0;
                elseif ($mimeImportance == 3)
                    $output->importance = 1;
                elseif ($mimeImportance < 3)
                    $output->importance = 2;
            }
            else { /* fmbiete's contribution r1528, ZP-320 */
                $output->importance = 1;
            }

            // Attachments are also needed for MIME messages
            if(isset($message->parts)) {
                $mparts = $message->parts;
                for ($i=0; $i < count($mparts); $i++) {
                    $part = $mparts[$i];

                    //recursively add subparts to later processing
                    if ((isset($part->ctype_primary) && $part->ctype_primary == "multipart") && (isset($part->ctype_secondary) && ($part->ctype_secondary == "mixed" || $part->ctype_secondary == "alternative"  || $part->ctype_secondary == "related"))) {
                        if (isset($part->parts)) {
                            foreach($part->parts as $spart)
                                $mparts[] = $spart;
                        }
                        // Go to the for again
                        continue;
                    }

                    if (is_calendar($part)) {
                        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->GetMessage(): text/calendar part found, trying to convert"));
                        $output->meetingrequest = new SyncMeetingRequest();
                        parse_meeting_calendar($part, $output, $is_sent_folder);
                    }
                    else {
                        //add part as attachment if it's disposition indicates so or if it is not a text part
                        if ((isset($part->disposition) && ($part->disposition == "attachment" || $part->disposition == "inline")) ||
                            (isset($part->ctype_primary) && $part->ctype_primary != "text")) {

                            if (isset($part->d_parameters['filename']))
                                $attname = $part->d_parameters['filename'];
                            else if (isset($part->ctype_parameters['name']))
                                $attname = $part->ctype_parameters['name'];
                            else if (isset($part->headers['content-description']))
                                $attname = $part->headers['content-description'];
                            else $attname = "unknown attachment";

                            /* BEGIN fmbiete's contribution r1528, ZP-320 */
                            if (Request::GetProtocolVersion() >= 12.0) {
                                if (!isset($output->asattachments) || !is_array($output->asattachments))
                                    $output->asattachments = array();

                                $attachment = new SyncBaseAttachment();

                                $attachment->estimatedDataSize = isset($part->d_parameters['size']) ? $part->d_parameters['size'] : isset($part->body) ? strlen($part->body) : 0;

                                $attachment->displayname = $attname;
                                $attachment->filereference = $folderid . ":" . $id . ":" . $i;
                                $attachment->method = 1; //Normal attachment
                                $attachment->contentid = isset($part->headers['content-id']) ? str_replace("<", "", str_replace(">", "", $part->headers['content-id'])) : "";
                                if (isset($part->disposition) && $part->disposition == "inline") {
                                    $attachment->isinline = 1;
                                    // #209 - KD 2015-06-16 If we got a filename use it, otherwise guess
                                    if (!isset($part->filename)) {
                                        // We try to fix the name for the inline file.
                                        // FIXME: This is a dirty hack as the used in the Zarafa backend, if you have a better method let me know!
                                        if (isset($part->ctype_primary) && isset($part->ctype_secondary)) {
                                            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->GetMessage(): Guessing extension for inline attachment [primary_type %s secondary_type %s]", $part->ctype_primary, $part->ctype_secondary));
                                            if (isset(BackendIMAP::$mimeTypes[$part->ctype_primary.'/'.$part->ctype_secondary])) {
                                                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->GetMessage(): primary_type %s secondary_type %s", $part->ctype_primary, $part->ctype_secondary));
                                                $attachment->displayname = "inline_".$i.".".BackendIMAP::$mimeTypes[$part->ctype_primary.'/'.$part->ctype_secondary];
                                            }
                                            else {
                                                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->GetMessage(): no extension found in '%s'!!", SYSTEM_MIME_TYPES_MAPPING));
                                            }
                                        }
                                        else {
                                            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->GetMessage(): no primary_type or secondary_type"));
                                        }
                                    }
                                }
                                else {
                                    $attachment->isinline = 0;
                                }

                                array_push($output->asattachments, $attachment);
                            }
                            else { //ASV_2.5
                                if (!isset($output->attachments) || !is_array($output->attachments))
                                    $output->attachments = array();

                                $attachment = new SyncAttachment();

                                $attachment->attsize = isset($part->d_parameters['size']) ? $part->d_parameters['size'] : isset($part->body) ? strlen($part->body) : 0;

                                $attachment->displayname = $attname;
                                $attachment->attname = $folderid . ":" . $id . ":" . $i;
                                $attachment->attmethod = 1;
                                $attachment->attoid = isset($part->headers['content-id']) ? str_replace("<", "", str_replace(">", "", $part->headers['content-id'])) : "";

                                array_push($output->attachments, $attachment);
                            }
                            /* END fmbiete's contribution r1528, ZP-320 */
                        }
                    }
                }
            }

            unset($message);
            unset($mobj);
            unset($mail);

            return $output;
        }

        return false;
    }

    /**
     * Returns message stats, analogous to the folder stats from StatFolder().
     *
     * @param string        $folderid       id of the folder
     * @param string        $id             id of the message
     *
     * @access public
     * @return array/boolean
     */
    public function StatMessage($folderid, $id) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->StatMessage('%s','%s')", $folderid, $id));
        $folderImapid = $this->getImapIdFromFolderId($folderid);

        $this->imap_reopen_folder($folderImapid);
        $overview = @imap_fetch_overview($this->mbox, $id, FT_UID);

        if (!$overview) {
            ZLog::Write(LOGLEVEL_WARN, sprintf("BackendIMAP->StatMessage('%s','%s'): Failed to retrieve overview: %s", $folderid, $id, imap_last_error()));
            return false;
        }

        // without uid it's not a valid message
        if (empty($overview[0]->uid)) return false;

        $entry = array();
        if (isset($overview[0]->udate)) {
            $entry["mod"] = $overview[0]->udate;
        } else if (isset($overview[0]->date)) {
            $entry["mod"] = $this->cleanupDate($overview[0]->date);
        } else {
            $entry["mod"] = 0;
        }
        $entry["id"] = $overview[0]->uid;

        // 'seen' aka 'read'
        if (isset($overview[0]->seen) && $overview[0]->seen) {
            $entry["flags"] = 1;
        }
        else {
            $entry["flags"] = 0;
        }

        // 'flagged' aka 'FollowUp' aka 'starred'
        if (isset($overview[0]->flagged) && $overview[0]->flagged) {
            $entry["star"] = 1;
        }
        else {
            $entry["star"] = 0;
        }

        return $entry;
    }

    /**
     * Called when a message has been changed on the mobile.
     * Added support for FollowUp flag
     *
     * @param string              $folderid            id of the folder
     * @param string              $id                  id of the message
     * @param SyncXXX             $message             the SyncObject containing a message
     * @param ContentParameters   $contentparameters
     *
     * @access public
     * @return array                        same return value as StatMessage()
     * @throws StatusException              could throw specific SYNC_STATUS_* exceptions
     */
    public function ChangeMessage($folderid, $id, $message, $contentparameters) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->ChangeMessage('%s','%s','%s')", $folderid, $id, get_class($message)));
        // TODO this could throw several StatusExceptions like e.g. SYNC_STATUS_OBJECTNOTFOUND, SYNC_STATUS_SYNCCANNOTBECOMPLETED

        if (isset($message->flag)) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->ChangeMessage('Setting flag')"));

            $folderImapid = $this->getImapIdFromFolderId($folderid);
            $this->imap_reopen_folder($folderImapid);

            if ($this->imap_inside_cutoffdate(Utils::GetCutOffDate($contentparameters->GetFilterType()), $id)) {
                if (isset($message->flag->flagstatus) && $message->flag->flagstatus == 2) {
                    ZLog::Write(LOGLEVEL_DEBUG, "Set On FollowUp -> IMAP Flagged");
                    $status = @imap_setflag_full($this->mbox, $id, "\\Flagged", ST_UID);
                }
                else {
                    ZLog::Write(LOGLEVEL_DEBUG, "Clearing Flagged");
                    $status = @imap_clearflag_full($this->mbox, $id, "\\Flagged", ST_UID);
                }

                if ($status) {
                    ZLog::Write(LOGLEVEL_DEBUG, "Flagged changed");
                }
                else {
                    ZLog::Write(LOGLEVEL_DEBUG, "Flagged failed");
                }
            }
            else {
                throw new StatusException(sprintf("BackendIMAP->ChangeMessage(): Message is outside the sync range"), SYNC_STATUS_SYNCCANNOTBECOMPLETED);
            }
        }

        return $this->StatMessage($folderid, $id);
    }

    /**
     * Changes the 'read' flag of a message on disk
     *
     * @param string              $folderid            id of the folder
     * @param string              $id                  id of the message
     * @param int                 $flags               read flag of the message
     * @param ContentParameters   $contentparameters
     *
     * @access public
     * @return boolean                      status of the operation
     * @throws StatusException              could throw specific SYNC_STATUS_* exceptions
     */
    public function SetReadFlag($folderid, $id, $flags, $contentparameters) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->SetReadFlag('%s','%s','%s')", $folderid, $id, $flags));

        $folderImapid = $this->getImapIdFromFolderId($folderid);
        $this->imap_reopen_folder($folderImapid);

        if ($this->imap_inside_cutoffdate(Utils::GetCutOffDate($contentparameters->GetFilterType()), $id)) {
            if ($flags == 0) {
                // set as "Unseen" (unread)
                $status = @imap_clearflag_full($this->mbox, $id, "\\Seen", ST_UID);
            } else {
                // set as "Seen" (read)
                $status = @imap_setflag_full($this->mbox, $id, "\\Seen", ST_UID);
            }
        }
        else {
            throw new StatusException(sprintf("BackendIMAP->SetReadFlag(): Message is outside the sync range"), SYNC_STATUS_OBJECTNOTFOUND);
        }

        return $status;
    }

    /**
     * Called when the user has requested to delete (really delete) a message
     *
     * @param string              $folderid             id of the folder
     * @param string              $id                   id of the message
     * @param ContentParameters   $contentparameters
     *
     * @access public
     * @return boolean                      status of the operation
     * @throws StatusException              could throw specific SYNC_STATUS_* exceptions
     */
    public function DeleteMessage($folderid, $id, $contentparameters) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->DeleteMessage('%s','%s')", $folderid, $id));

        $folderImapid = $this->getImapIdFromFolderId($folderid);
        if (strcasecmp($folderImapid, $this->create_name_folder(IMAP_FOLDER_TRASH)) != 0) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->DeleteMessage('%s','%s') move message to trash folder", $folderid, $id));
            return $this->MoveMessage($folderid, $id, $this->create_name_folder(IMAP_FOLDER_TRASH), $contentparameters);
        }
        $this->imap_reopen_folder($folderImapid);

        if ($this->imap_inside_cutoffdate(Utils::GetCutOffDate($contentparameters->GetFilterType()), $id)) {
            $s1 = @imap_delete ($this->mbox, $id, FT_UID);
            $s11 = @imap_setflag_full($this->mbox, $id, "\\Deleted", FT_UID);
            $s2 = @imap_expunge($this->mbox);
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->DeleteMessage('%s','%s'): result: s-delete: '%s' s-expunge: '%s' setflag: '%s'", $folderid, $id, $s1, $s2, $s11));
        }
        else {
            throw new StatusException(sprintf("BackendIMAP->DeleteMessage(): Message is outside the sync range"), SYNC_STATUS_OBJECTNOTFOUND);
        }

        return ($s1 && $s2 && $s11);
    }

    /**
     * Called when the user moves an item on the PDA from one folder to another
     *
     * @param string              $folderid            id of the source folder
     * @param string              $id                  id of the message
     * @param string              $newfolderid         id of the destination folder
     * @param ContentParameters   $contentparameters
     *
     * @access public
     * @return boolean                      status of the operation
     * @throws StatusException              could throw specific SYNC_MOVEITEMSSTATUS_* exceptions
     */
    public function MoveMessage($folderid, $id, $newfolderid, $contentparameters) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->MoveMessage('%s','%s','%s')", $folderid, $id, $newfolderid));
        $folderImapid = $this->getImapIdFromFolderId($folderid);
        $newfolderImapid = $this->getImapIdFromFolderId($newfolderid);

        if ($folderImapid == $newfolderImapid) {
            throw new StatusException(sprintf("BackendIMAP->MoveMessage('%s','%s','%s'): Error, destination folder is source folder. Canceling the move.", $folderid, $id, $newfolderid), SYNC_MOVEITEMSSTATUS_SAMESOURCEANDDEST);
        }

        $this->imap_reopen_folder($folderImapid);

        if ($this->imap_inside_cutoffdate(Utils::GetCutOffDate($contentparameters->GetFilterType()), $id)) {
            // read message flags
            $overview = @imap_fetch_overview($this->mbox, $id, FT_UID);

            if (!is_array($overview) || count($overview) == 0) {
                throw new StatusException(sprintf("BackendIMAP->MoveMessage('%s','%s','%s'): Error, unable to retrieve overview of source message: %s", $folderid, $id, $newfolderid, imap_last_error()), SYNC_MOVEITEMSSTATUS_INVALIDSOURCEID);
            }
            else {
                // get next UID for destination folder
                // when moving a message we have to announce through ActiveSync the new messageID in the
                // destination folder. This is a "guessing" mechanism as IMAP does not inform that value.
                // when lots of simultaneous operations happen in the destination folder this could fail.
                // in the worst case the moved message is displayed twice on the mobile.
                $destStatus = imap_status($this->mbox, $this->server . $newfolderImapid, SA_ALL);
                if (!$destStatus)
                    throw new StatusException(sprintf("BackendIMAP->MoveMessage('%s','%s','%s'): Error, unable to open destination folder: %s", $folderid, $id, $newfolderid, imap_last_error()), SYNC_MOVEITEMSSTATUS_INVALIDDESTID);

                $newid = $destStatus->uidnext;

                // move message
                $s1 = imap_mail_move($this->mbox, $id, $newfolderImapid, CP_UID);
                if (!$s1)
                    throw new StatusException(sprintf("BackendIMAP->MoveMessage('%s','%s','%s'): Error, copy to destination folder failed: %s", $folderid, $id, $newfolderid, imap_last_error()), SYNC_MOVEITEMSSTATUS_CANNOTMOVE);


                // delete message in from-folder
                $s2 = imap_expunge($this->mbox);

                // open new folder
                $stat = $this->imap_reopen_folder($newfolderImapid);
                if (!$stat)
                    throw new StatusException(sprintf("BackendIMAP->MoveMessage('%s','%s','%s'): Error, opening the destination folder: %s", $folderid, $id, $newfolderid, imap_last_error()), SYNC_MOVEITEMSSTATUS_CANNOTMOVE);


                // remove all flags
                $s3 = @imap_clearflag_full($this->mbox, $newid, "\\Seen \\Answered \\Flagged \\Deleted \\Draft", FT_UID);
                $newflags = "";
                $move_to_trash = strcasecmp($newfolderImapid, $this->create_name_folder(IMAP_FOLDER_TRASH)) == 0;

                if ($overview[0]->seen || ($move_to_trash && defined('IMAP_AUTOSEEN_ON_DELETE') && IMAP_AUTOSEEN_ON_DELETE == true))
                    $newflags .= "\\Seen";
                if ($overview[0]->flagged)
                    $newflags .= " \\Flagged";
                if ($overview[0]->answered)
                    $newflags .= " \\Answered";
                $s4 = @imap_setflag_full ($this->mbox, $newid, $newflags, FT_UID);

                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->MoveMessage('%s','%s','%s'): result s-move: '%s' s-expunge: '%s' unset-Flags: '%s' set-Flags: '%s'", $folderid, $id, $newfolderid, Utils::PrintAsString($s1), Utils::PrintAsString($s2), Utils::PrintAsString($s3), Utils::PrintAsString($s4)));

                // return the new id "as string"
                return $newid . "";
            }
        }
        else {
            throw new StatusException(sprintf("BackendIMAP->MoveMessage(): Message is outside the sync range"), SYNC_MOVEITEMSSTATUS_INVALIDSOURCEID);
        }
    }


    /**
     * Processes a response to a meeting request.
     *
     * @param string        $requestid      id of the object containing the request
     * @param string        $folderid       id of the parent folder of $requestid
     * @param string        $response
     *
     * @access public
     * @return string       id of the created/updated calendar obj
     * @throws StatusException
     */
    public function MeetingResponse($requestid, $folderid, $response) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->MeetingResponse('%s','%s','%s')", $requestid, $folderid, $response));

        $folderImapid = $this->getImapIdFromFolderId($folderid);
        $this->imap_reopen_folder($folderImapid);
        $mail = @imap_fetchheader($this->mbox, $requestid, FT_UID) . @imap_body($this->mbox, $requestid, FT_PEEK | FT_UID);

        if (empty($mail)) {
            throw new StatusException("BackendIMAP->MeetingResponse(): Error, message not found, maybe was moved", SYNC_ITEMOPERATIONSSTATUS_INVALIDATT);
        }

        // Get the original calendar request, so we don't need to create it from scratch
        $mobj = new Mail_mimeDecode($mail);
        unset($mail);
        $message = $mobj->decode(array('decode_headers' => 'utf-8', 'decode_bodies' => true, 'include_bodies' => true, 'rfc_822bodies' => true, 'charset' => 'utf-8'));
        unset($mobj);

        $body_part = null;
        if(isset($message->parts)) {
            $mparts = $message->parts;
            for ($i=0; $i < count($mparts); $i++) {
                $part = $mparts[$i];
                //recursively add parts
                if ((isset($part->ctype_primary) && $part->ctype_primary == "multipart")
                        && (isset($part->ctype_secondary) && ($part->ctype_secondary == "mixed" || $part->ctype_secondary == "alternative"  || $part->ctype_secondary == "related"))) {
                    foreach($part->parts as $spart)
                        $mparts[] = $spart;
                    continue;
                }

                if (is_calendar($part)) {
                    ZLog::Write(LOGLEVEL_DEBUG, "BackendIMAP->MeetingResponse - text/calendar part found, trying to reply");
                    $body_part = reply_meeting_calendar($part, $response, $this->GetUserDetails($this->username)['emailaddress']);
                }
            }
            unset($mparts);
        }
        unset($message);

        if ($body_part === null) {
            throw new StatusException("BackendIMAP->MeetingResponse(): Error, no calendar part modified", SYNC_ITEMOPERATIONSSTATUS_INVALIDATT);
        }

        $uuid_calendar = "";
        switch($response) {
            case 1: // ACCEPTED
            case 2: // TENTATIVE
                $uuid_calendar = create_calendar_dav($body_part);
                break;
            case 3: // DECLINED
                // Do nothing
                break;
        }

        // We don't need to send a reply, because the client will do it

        // Remove message: answered invitation
            // Roundcube client doesn't remove the original message, but Zarafa backend does
        $s1 = @imap_delete ($this->mbox, $requestid, FT_UID);
        $s11 = @imap_setflag_full($this->mbox, $requestid, "\\Deleted", FT_UID);
        $s2 = @imap_expunge($this->mbox);
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->MeetingResponse('%s','%s'): removing message result: s-delete: '%s' s-expunge: '%s' setflag: '%s'", $folderid, $requestid, $s1, $s2, $s11));

        return $uuid_calendar;
    }


    /**
     * Resolves recipients
     *
     * @param SyncObject        $resolveRecipients
     *
     * @access public
     * @return SyncObject       $resolveRecipients
     */
    public function ResolveRecipients($resolveRecipients) {
        // TODO:
        return false;
    }


    /**
     * Returns the email address and the display name of the user. Used by autodiscover.
     *
     * @param string        $username           The username
     *
     * @access public
     * @return Array
     */
    public function GetUserDetails($username) {
        $email = $username;
        if (!USE_FULLEMAIL_FOR_LOGIN) {
            $email = getDefaultEmailValue($username, $this->domain);
        }
        return array('emailaddress' => $email, 'fullname' => getDefaultFullNameValue($username, $this->domain));
    }


    /**
     * Applies settings to and gets informations from the device
     *
     * @param SyncObject    $settings (SyncOOF, SyncUserInformation, SyncRightsManagementTemplates possible)
     *
     * @access public
     * @return SyncObject       $settings
     */
    public function Settings($settings) {
        if ($settings instanceof SyncOOF) {
            $this->settingsOOF($settings);
        }
        elseif ($settings instanceof SyncUserInformation) {
            $this->settingsUserInformation($settings);
        }
        elseif ($settings instanceof SyncRightsManagementTemplates) {
            $settings->Status = SYNC_COMMONSTATUS_IRMFEATUREDISABLED;
        }

        return $settings;
    }


    /**
     * Indicates which AS version is supported by the backend.
     *
     * @access public
     * @return string       AS version constant
     */
    public function GetSupportedASVersion() {
        return ZPush::ASV_14;
    }


    /**
     * Returns the BackendIMAP as it implements the ISearchProvider interface
     * This could be overwritten by the global configuration
     *
     * @access public
     * @return object       Implementation of ISearchProvider
     */
    public function GetSearchProvider() {
        return $this;
    }


    /**----------------------------------------------------------------------------------------------------------
     * public ISearchProvider methods
     */

    /**
     * Indicates if a search type is supported by this SearchProvider
     *
     * @param string        $searchtype
     *
     * @access public
     * @return boolean
     */
    public function SupportsType($searchtype) {
        return ($searchtype == ISearchProvider::SEARCH_MAILBOX);
    }


    /**
     * Queries the IMAP backend
     *
     * @param string                        $searchquery        string to be searched for
     * @param string                        $searchrange        specified searchrange
     * @param SyncResolveRecipientsPicture  $searchpicture      limitations for picture
     *
     * @access public
     * @return array        search results
     * @throws StatusException
     */
    public function GetGALSearchResults($searchquery, $searchrange, $searchpicture) {
        return false;
    }

    /**
     * Searches for the emails on the server
     *
     * @param ContentParameter $cpo
     * @param string $prefix If used with the combined backend here will come the backend id and delimiter
     *
     * @return array
     */
    public function GetMailboxSearchResults($cpo, $prefix = '') {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->GetMailboxSearchResults()"));

        $items = false;
        $searchFolderId = $cpo->GetSearchFolderid();
        $searchRange = explode('-', $cpo->GetSearchRange());
        $filter = $this->getSearchRestriction($cpo);

        // Open the folder to search
        $search = true;

        if (empty($searchFolderId)) {
            $searchFolderId = $this->getFolderIdFromImapId($this->create_name_folder(IMAP_FOLDER_INBOX), false);
        }

        // Convert searchFolderId to IMAP id
        $imapId = $this->getImapIdFromFolderId($searchFolderId);

        $listMessages = array();
        $numMessages = 0;
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->GetMailboxSearchResults: Filter <%s>", $filter));

        if ($cpo->GetSearchDeepTraversal()) { // Recursive search
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->GetMailboxSearchResults: Recursive search %s", $imapId));
            $listFolders = @imap_list($this->mbox, $this->server, "*");
            if ($listFolders === false) {
                ZLog::Write(LOGLEVEL_WARN, sprintf("BackendIMAP->GetMailboxSearchResults: Error recursive list %s", imap_last_error()));
            }
            else {
                foreach ($listFolders as $subFolder) {
                    if (@imap_reopen($this->mbox, $subFolder)) {
                        $imapSubFolder = str_replace($this->server, "", $subFolder);
                        $subFolderId = $this->getFolderIdFromImapId($imapSubFolder);
                        if ($subFolderId !== false) { // only search found folders
                            $subList = @imap_search($this->mbox, $filter, SE_UID, "UTF-8");
                            if ($subList !== false) {
                                $numMessages += count($subList);
                                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->GetMailboxSearchResults: SubSearch in %s : %s ocurrences", $imapSubFolder, count($subList)));
                                $listMessages[] = array($subFolderId => $subList);
                            }
                        }
                    }
                }
            }
        }
        else { // Search in folder
            if (@imap_reopen($this->mbox, $this->server . $imapId)) {
                $subList = @imap_search($this->mbox, $filter, SE_UID, "UTF-8");
                if ($subList !== false) {
                    $numMessages += count($subList);
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->GetMailboxSearchResults: Search in %s : %s ocurrences", $imapId, count($subList)));
                    $listMessages[] = array($searchFolderId => $subList);
                }
            }
        }


        if ($numMessages > 0) {
            // range for the search results
            $rangestart = 0;
            $rangeend = SEARCH_MAXRESULTS - 1;

            if (is_array($searchRange) && isset($searchRange[0]) && isset($searchRange[1])) {
                $rangestart = $searchRange[0];
                $rangeend = $searchRange[1];
            }

            $querycnt = $numMessages;
            $items = array();
            $querylimit = (($rangeend + 1) < $querycnt) ? ($rangeend + 1) : $querycnt + 1;
            $items['range'] = $rangestart.'-'.($querylimit - 1);
            $items['searchtotal'] = $querycnt;

            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->GetMailboxSearchResults: %s entries found, returning %s", $items['searchtotal'], $items['range']));

            $p = 0;
            $pc = 0;
            for ($i = $rangestart, $j = 0; $i <= $rangeend && $i < $querycnt; $i++, $j++) {
                $keys = array_keys($listMessages[$p]);
                $cntFolder = count($listMessages[$p][$keys[0]]);
                if ($pc >= $cntFolder) {
                    $p++;
                    $pc = 0;
                    $keys = array_keys($listMessages[$p]);
                }
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->GetMailboxSearchResults: %s %s %s %s", $p, $pc, $keys[0], $listMessages[$p][$keys[0]][$pc]));
                $foundFolderId = $keys[0];
                $items[$j]['class'] = 'Email';
                $items[$j]['longid'] = $prefix . $foundFolderId . ":" . $listMessages[$p][$foundFolderId][$pc];
                $items[$j]['folderid'] = $prefix . $foundFolderId;
                $pc++;
            }
        }
        else {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->GetMailboxSearchResults: No messages found!"));
        }

        return $items;
    }

    /**
    * Terminates a search for a given PID
    *
    * @param int $pid
    *
    * @return boolean
    */
    public function TerminateSearch($pid) {
        return true;
    }

    /**
     * Disconnects from IMAP
     *
     * @access public
     * @return boolean
     */
    public function Disconnect() {
        // Don't close the mailbox, we will need it open in the Backend methods
        return true;
    }


    /**
     * Creates a search restriction
     *
     * @param ContentParameter $cpo
     * @return string
     */
    private function getSearchRestriction($cpo) {
        $searchText = $cpo->GetSearchFreeText();
        $searchGreater = $cpo->GetSearchValueGreater() ? strftime("%Y-%m-%d", strtotime($cpo->GetSearchValueGreater())) : '';
        $searchLess = $cpo->GetSearchValueLess() ? strftime("%Y-%m-%d", strtotime($cpo->GetSearchValueLess())) : '';

        $filter = '';
        if ($searchGreater != '') {
            $filter .= ' SINCE "' . $searchGreater . '"';
        } else {
            // Only search in sync messages
            $limitdate = new DateTime();
            switch (SYNC_FILTERTIME_MAX) {
                case SYNC_FILTERTYPE_1DAY:
                    $limitdate = $limitdate->sub(new DateInterval("P1D"));
                    break;
                case SYNC_FILTERTYPE_3DAYS:
                    $limitdate = $limitdate->sub(new DateInterval("P3D"));
                    break;
                case SYNC_FILTERTYPE_1WEEK:
                    $limitdate = $limitdate->sub(new DateInterval("P1W"));
                    break;
                case SYNC_FILTERTYPE_2WEEKS:
                    $limitdate = $limitdate->sub(new DateInterval("P2W"));
                    break;
                case SYNC_FILTERTYPE_1MONTH:
                    $limitdate = $limitdate->sub(new DateInterval("P1M"));
                    break;
                case SYNC_FILTERTYPE_3MONTHS:
                    $limitdate = $limitdate->sub(new DateInterval("P3M"));
                    break;
                case SYNC_FILTERTYPE_6MONTHS:
                    $limitdate = $limitdate->sub(new DateInterval("P6M"));
                    break;
                default:
                    $limitdate = false;
                    break;
            }

            if ($limitdate !== false) {
                // date format : 7 Jan 2012
                $filter .= ' SINCE "' . ($limitdate->format("d M Y")) . '"';
            }
        }
        if ($searchLess != '') {
            $filter .= ' BEFORE "' . $searchLess . '"';
        }

        $filter .= ' TEXT "' . $searchText . '"';

        return $filter;
    }


    /**----------------------------------------------------------------------------------------------------------
     * protected IMAP methods
     */

    /**
     * Unmasks a hex folderid and returns the imap folder id
     *
     * @param string        $folderid       hex folderid generated by convertImapId()
     *
     * @access protected
     * @return string       imap folder id
     */
    protected function getImapIdFromFolderId($folderid) {
        $this->InitializePermanentStorage();

        if (isset($this->permanentStorage->fmFidFimap)) {
            if (isset($this->permanentStorage->fmFidFimap[$folderid])) {
                $imapId = $this->permanentStorage->fmFidFimap[$folderid];
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->getImapIdFromFolderId('%s') = %s", $folderid, $imapId));
                return $imapId;
            }
            else {
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->getImapIdFromFolderId('%s') = %s", $folderid, 'not found'));
                return false;
            }
        }
        ZLog::Write(LOGLEVEL_WARN, sprintf("BackendIMAP->getImapIdFromFolderId('%s') = %s", $folderid, 'not initialized!'));
        return false;
    }

    /**
     * Retrieves a hex folderid previousily masked imap
     *
     * @param string        $imapid         Imap folder id
     *
     * @access protected
     * @return string       hex folder id
     */
    protected function getFolderIdFromImapId($imapid, $case_sensitive = true) {
        $this->InitializePermanentStorage();

        if (!isset($this->permanentStorage->fmFimapFid)) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->getFolderIdFromImapId('%s') IMAP cache folder not found, creating one", $imapid));
            // folderId to folderImap mapping
            $this->permanentStorage->fmFidFimap = array();
            // folderImap to folderId mapping
            $this->permanentStorage->fmFimapFid = array();
            // folderImap to folderId mapping - lowercase
            $this->permanentStorage->fmFimapFidLowercase = array();

            $this->GetFolderList();
        }

        if ($case_sensitive && isset($this->permanentStorage->fmFimapFid[$imapid])) {
                $folderid = $this->permanentStorage->fmFimapFid[$imapid];
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->getFolderIdFromImapId('%s') = %s", $imapid, $folderid));
                return $folderid;
        }

        if (!$case_sensitive && isset($this->permanentStorage->fmFimapFidLowercase[strtolower($imapid)])) {
                $folderid = $this->permanentStorage->fmFimapFidLowercase[strtolower($imapid)];
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->getFolderIdFromImapId('%s', false) = %s", $imapid, $folderid));
                return $folderid;
        }
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->getFolderIdFromImapId('%s', '%s') = %s", $imapid, Utils::PrintAsString($case_sensitive), 'not found'));
        return false;
    }

    /**
     * Masks a imap folder id into a generated hex folderid
     * The method getFolderIdFromImapId() is consulted so that an
     * imapid always returns the same hex folder id
     *
     * @param string        $imapid         Imap folder id
     *
     * @access protected
     * @return string       hex folder id
     */
    protected function convertImapId($imapid) {
        $this->InitializePermanentStorage();

        // check if this imap id was converted before
        $folderid = $this->getFolderIdFromImapId($imapid);

        // nothing found, so generate a new id and put it in the cache
        if (!$folderid) {
            // generate folderid and add it to the mapping
            $folderid = sprintf('%04x%04x', mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ));

//             // folderId to folderImap mapping
//             if (!isset($this->permanentStorage->fmFidFimap))
//                 $this->permanentStorage->fmFidFimap = array();

            $a = $this->permanentStorage->fmFidFimap;
            $a[$folderid] = $imapid;
            $this->permanentStorage->fmFidFimap = $a;

//             // folderImap to folderid mapping
//             if (!isset($this->permanentStorage->fmFimapFid))
//                 $this->permanentStorage->fmFimapFid = array();

            $b = $this->permanentStorage->fmFimapFid;
            $b[$imapid] = $folderid;
            $this->permanentStorage->fmFimapFid = $b;

//             if (!isset($this->permanentStorage->fmFimapFidLowercase))
//                 $this->permanentStorage->fmFimapFidLowercase = array();

            $c = $this->permanentStorage->fmFimapFidLowercase;
            $c[strtolower($imapid)] = $folderid;
            $this->permanentStorage->fmFimapFidLowercase = $c;
        }

        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->convertImapId('%s') = %s", $imapid, $folderid));

        return $folderid;
    }

    /**
     * Returns the serverdelimiter for folder parsing
     *
     * @access protected
     * @return string       delimiter
     */
    protected function getServerDelimiter() {
        $this->InitializePermanentStorage();
        if (isset($this->permanentStorage->serverdelimiter)) {
            return $this->permanentStorage->serverdelimiter;
        }

        $list = @imap_getmailboxes($this->mbox, $this->server, "*");
        if (is_array($list) && count($list) > 0) {
            // get the delimiter from the first folder
            $delimiter = $list[0]->delimiter;
            $this->permanentStorage->serverdelimiter = $delimiter;
        } else {
            // default
            $delimiter = ".";
        }
        return $delimiter;
    }

    /**
     * Helper to re-initialize the folder to speed things up
     * Remember what folder is currently open and only change if necessary
     *
     * @param string        $folderid       id of the folder
     * @param boolean       $force          re-open the folder even if currently opened
     *
     * @access protected
     * @return boolean      if folder is opened
     */
    protected function imap_reopen_folder($folderid, $force = false) {
        // Reconnect
        $this->imap_reconnect();

        // to see changes, the folder has to be reopened!
        if ($this->mboxFolder != $folderid || $force) {
            $s = @imap_reopen($this->mbox, $this->server . $folderid);
            // TODO throw status exception
            if (!$s) {
                ZLog::Write(LOGLEVEL_WARN, sprintf("BackendIMAP->imap_reopen_folder('%s'): failed to change folder: %s", $folderid, implode(", ", imap_errors())));
                return false;
            }
            $this->mboxFolder = $folderid;
        }

        return true;
    }

    /**
     * Reconnect IMAP connection if needed
     *
     * @access private
     */
    private function imap_reconnect() {
        if ($this->mbox) {
            imap_ping($this->mbox);
        }
        else {
            $this->mbox = @imap_open($this->server, $this->username, $this->password, OP_HALFOPEN, 0, $this->imapParams);
            $this->mboxFolder = "";
        }
    }

    /**
     * Creates a new IMAP folder.
     *
     * @param string        $foldername     full folder name
     *
     * @access private
     * @return boolean      success
     */
    private function imap_create_folder($foldername) {
        $name = Utils::Utf8_to_utf7imap($foldername);

        $res = @imap_createmailbox($this->mbox, $name);
        if ($res) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->imap_create_folder('%s'): new folder created", $foldername));
        }
        else {
            ZLog::Write(LOGLEVEL_WARN, sprintf("BackendIMAP->imap_create_folder('%s'): failed to create folder: %s", $foldername, implode(", ", imap_errors())));
        }

        return $res;
    }

    /**
     * Check if the message was sent before the cutoffdate.
     *
     * @access private
     * @param integer   $cutoffdate     EPOCH of the bottom sync range. 0 if no range is defined
     * @param integer   $id             Message id
     * @return boolean
     */
    private function imap_inside_cutoffdate($cutoffdate, $id) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->imap_inside_cutoffdate(): Checking if the messages is withing the cutoffdate %d, %s", $cutoffdate, $id));
        $is_inside = false;

        if ($cutoffdate == 0) {
            // No cutoffdate, all the messages are in range
            $is_inside = true;
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->imap_inside_cutoffdate(): No cutoffdate, all the messages are in range"));
        }
        else {
            $overview = imap_fetch_overview($this->mbox, $id, FT_UID);
            if (is_array($overview)) {
                if (isset($overview[0]->date)) {
                    $epoch_sent = strtotime($overview[0]->date);
                    if ( $epoch_sent === false ) {
                        $pattern1 = '/^(Sun|Mon|Tue|Wed|Thu|Fri|Sat), [0-9]+ (Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) [0-9]+ [0-9]+:[0-9]+(:[0-9]+)* [+-]+[0-9]+/';
                        $pattern2 = '/^(Sun|Mon|Tue|Wed|Thu|Fri|Sat), [0-9]+ (Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) [0-9]+ [0-9]+:[0-9]+(:[0-9]+)* /';
                        if (preg_match($pattern1, $overview[0]->date, $matches) == 1) {
                            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->imap_inside_cutoffdate(): date: %s, match: %s", $overview[0]->date, $matches[0]));
                            $epoch_sent = strtotime($matches[0]);
                        } else if (preg_match($pattern2, $overview[0]->date, $matches) == 1) {
                            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->imap_inside_cutoffdate(): date: %s, match: %s", $overview[0]->date, $matches[0]));
                            $epoch_sent = strtotime($matches[0].' UTC');
                        }
                    }
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->imap_inside_cutoffdate(): cutoffdate: %s, epoch_sent: %s", $cutoffdate, $epoch_sent));
                    $is_inside = ($cutoffdate <= $epoch_sent);
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->imap_inside_cutoffdate(): Message is %s cutoffdate range", ($is_inside ? "INSIDE" : "OUTSIDE")));
                }
                else {
                    // No sent date defined, that's a buggy message but we will think that the message is in range
                    $is_inside = true;
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->imap_inside_cutoffdate(): No sent date defined, that's a buggy message but we will think that the message is in range"));
                }
            }
            else {
                // No overview, maybe the message is no longer there
                $is_inside = false;
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->imap_inside_cutoffdate(): No overview, maybe the message is no longer there"));
            }
        }

        return $is_inside;
    }

    /**
     * Adds a message with seen flag to a specified folder (used for saving sent items)
     *
     * @param string        $folderid       id of the folder
     * @param string        $header         header of the message
     * @param long          $body           body of the message
     *
     * @access protected
     * @return boolean      status
     */
    protected function addSentMessage($folderid, $header, $body) {
        $header_body = str_replace("\n", "\r\n", str_replace("\r", "", $header . "\n\n" . $body));

        return @imap_append($this->mbox, $this->server . $folderid, $header_body, "\\Seen");
    }

    /**
     * Parses an mimedecode address array back to a simple "," separated string
     *
     * @param array         $ad             addresses array
     *
     * @access protected
     * @return string       mail address(es) string
     */
    protected function parseAddr($ad) {
        $addr_string = "";
        if (isset($ad) && is_array($ad)) {
            foreach($ad as $addr) {
                if ($addr_string) $addr_string .= ",";
                    $addr_string .= $addr->mailbox . "@" . $addr->host;
            }
        }
        return $addr_string;
    }

    /**
     * Recursive way to get mod and parent - repeat until only one part is left
     * or the folder is identified as an IMAP folder
     *
     * @param string        $fhir           folder hierarchy string
     * @param string        &$displayname   reference of the displayname
     * @param long          &$parent        reference of the parent folder
     *
     * @access protected
     * @return
     */
    protected function getModAndParentNames($fhir, &$displayname, &$parent) {
        // if mod is already set add the previous part to it as it might be a folder which has delimiter in its name
        $displayname = (isset($displayname) && strlen($displayname) > 0) ? $displayname = array_pop($fhir) . $this->getServerDelimiter() . $displayname : array_pop($fhir);
        $parent = implode($this->getServerDelimiter(), $fhir);

        if (count($fhir) == 1 || $this->checkIfIMAPFolder($parent)) {
            return;
        }
        //recursion magic
        $this->getModAndParentNames($fhir, $displayname, $parent);
    }

    /**
     * Prepare the folder name to get the type and parent.
     *
     * @param string $folder_name
     * @return string
     * @access private
     */
    private function create_name_folder($folder_name) {
        $foldername = $folder_name;
        // If we have defined a folder prefix, and it's not empty
        if (defined('IMAP_FOLDER_PREFIX') && IMAP_FOLDER_PREFIX != "") {
            // If inbox uses prefix or we are not evaluating inbox
            if (IMAP_FOLDER_PREFIX_IN_INBOX == true || strcasecmp($foldername, IMAP_FOLDER_INBOX) != 0) {
                $foldername = IMAP_FOLDER_PREFIX . $this->getServerDelimiter() . $foldername;
            }
        }

        return $foldername;
    }

    /**
     * Checks if a specified name is a folder in the IMAP store
     *
     * @param string        $foldername     a foldername
     *
     * @access protected
     * @return boolean
     */
    protected function checkIfIMAPFolder($folderName) {
        $folder_name = $folderName;
        if (defined('IMAP_FOLDER_PREFIX') && strlen(IMAP_FOLDER_PREFIX) > 0) {
            // TODO: We don't care about the inbox exception with the prefix, because we won't check inbox
            $folder_name = IMAP_FOLDER_PREFIX . $this->getServerDelimiter() . $folder_name;
        }
        $list_subfolders = @imap_list($this->mbox, $this->server, $folder_name);
        return is_array($list_subfolders);
    }

    /**
     * Removes parenthesis (comments) from the date string because
     * strtotime returns false if received date has them
     *
     * @param string        $receiveddate   a date as a string
     *
     * @access protected
     * @return integer
     */
    protected function cleanupDate($receiveddate) {
        if (is_array($receiveddate)) {
            // Header Date could be repeated in the message, we only check the first
            $receiveddate = $receiveddate[0];
        }
        $receivedtime = strtotime(preg_replace('/\(.*\)/', "", $receiveddate));
        if ($receivedtime === false || $receivedtime == -1) {
            ZLog::Write(LOGLEVEL_WARN, sprintf("cleanupDate('%s'): strtotime() failed - message might be broken.", $receiveddate));
            return null;
        }

        return $receivedtime;
    }


    /**
     * Returns a list of mime-types with extension files
     *
     * @access private
     * @return array[mime-type => extension]
     */
    private function SystemExtensionMimeTypes() {
        $out = array();
        if (file_exists(SYSTEM_MIME_TYPES_MAPPING)) {
            $file = fopen(SYSTEM_MIME_TYPES_MAPPING, 'r');
            while(($line = fgets($file)) !== false) {
                $line = trim(preg_replace('/#.*/', '', $line));
                if(!$line)
                    continue;
                $parts = preg_split('/\s+/', $line);
                if(count($parts) == 1)
                    continue;
                $type = array_shift($parts);
                foreach($parts as $part) {
                    if (!isset($out[$type])) {
                        $out[$type] = $part;
                    }
                }
            }
            fclose($file);
        }

        return $out;
    }


    /**
     * Sends a message
     *
     * @access private
     * @param $fromaddr     From address
     * @param $toaddr       To address
     * @param $headers      Headers array
     * @param $body         Body array
     * @return boolean      True if sent
     * @throws StatusException
     */
    private function sendMessage($fromaddr, $toaddr, $headers, $body) {
        global $imap_smtp_params;

        $sendingMethod = 'mail';
        if (defined('IMAP_SMTP_METHOD')) {
            $sendingMethod = IMAP_SMTP_METHOD;
            if ($sendingMethod == 'smtp') {
                if (isset($imap_smtp_params['username']) && $imap_smtp_params['username'] == 'imap_username') {
                    $imap_smtp_params['username'] = $this->username;
                }
                if (isset($imap_smtp_params['password']) && $imap_smtp_params['password'] == 'imap_password') {
                    $imap_smtp_params['password'] = $this->password;
                }
            }
        }

        if (is_array($toaddr)) {
            $recipients = $toaddr;
        }
        else {
            $recipients = array($toaddr);
        }

        // Cc and Bcc headers are sent, but we need to make sure that the recipient list contains them
        foreach (array("CC", "cc", "Cc", "BCC", "Bcc", "bcc") as $key) {
            if (!empty($headers[$key])) {
                if (is_array($headers[$key])) {
                    $recipients = array_merge($recipients, $headers[$key]);
                }
                else {
                    $recipients[] = $headers[$key];
                }
            }
        }

        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->sendMessage(): SendingMail with %s", $sendingMethod));
        $mail = Mail::factory($sendingMethod, $sendingMethod == "mail" ? "-f " . $fromaddr : $imap_smtp_params);
        $send = $mail->send($recipients, $headers, $body);
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->sendMessage(): send return value %s", $send));

        if ($send !== true) {
            throw new StatusException(sprintf("BackendIMAP->sendMessage(): The email could not be sent"), SYNC_COMMONSTATUS_MAILSUBMISSIONFAILED);
        }

        return $send;
    }


    /**
     * Saves a copy of a message in the Sent folder
     *
     * @access public
     * @param $finalHeaders     Array of headers
     * @param $finalBody        Body part
     * @return boolean          If the message is saved
     */
    private function saveSentMessage($finalHeaders, $finalBody) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->saveSentMessage(): saving message in Sent Items folder"));

        $headers = "";
        foreach ($finalHeaders as $k => $v) {
            if (strlen($headers) > 0) {
                $headers .= "\n";
            }
            $headers .= "$k: $v";
        }

        if ($this->sentID === false) {
            $this->sentID = $this->getFolderIdFromImapId($this->create_name_folder(IMAP_FOLDER_SENT), false);
        }

        $saved = false;
        if ($this->sentID) {
            $imapid = $this->getImapIdFromFolderId($this->sentID);
            $saved = $this->addSentMessage($imapid, $headers, $finalBody);
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->saveSentMessage(): Outgoing mail saved in 'Sent' folder '%s' ['%s']", $imapid, $this->sentID));
        }
        else {
            ZLog::Write(LOGLEVEL_ERROR, "BackendIMAP->saveSentMessage(): The email could not be saved to Sent Items folder. Check your configuration.");
        }
        unset($headers);

        return $saved;
    }


    /**
     * Set the from header value if not set or we are overwriting by configuration.
     *
     * @param array &$headers
     * @return void
     * @access private
     */
    private function setFromHeaderValue(&$headers) {
        $from = getDefaultFromValue($this->username, $this->domain);

        if (isset($headers["from"])) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->getFromHeaderValue(): from defined: %s", $headers["from"]));
            if (strlen(IMAP_DEFAULTFROM) > 0) {
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->getFromHeaderValue(): Overwriting From: %s", $from));
                $headers["from"] = $from;
            }
        }
        elseif (isset($headers["From"])) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->getFromHeaderValue(): From defined: %s", $headers["From"]));
            if (strlen(IMAP_DEFAULTFROM) > 0) {
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->getFromHeaderValue(): Overwriting From: %s", $from));
                $headers["From"] = $from;
            }
        }
        else {
            // not From header found
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->getFromHeaderValue(): No From address defined, we try for a default one"));
            $headers["from"] = $from;
        }
    }


    /**
     * Set the Return-Path header value if not set
     *
     * @param array &$headers
     * @param string $fromaddr
     * @return void
     * @access private
     */
    private function setReturnPathValue(&$headers, $fromaddr) {
        if (!(isset($headers["return-path"]) || isset($headers["Return-Path"]))) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->setReturnPathValue(): No Return-Path address defined, we use From"));
            $headers["return-path"] = $fromaddr;
        }
    }


    /**
     * The meta function for out of office settings.
     *
     * @param SyncObject $oof
     *
     * @access private
     * @return void
     */
    private function settingsOOF(&$oof) {
        //if oof state is set it must be set of oof and get otherwise
        if (!isset($oof->oofstate)) {
            $oof->oofstate = SYNC_SETTINGSOOF_DISABLED;
            $oof->Status = SYNC_SETTINGSSTATUS_SUCCESS;

            //unset body type for oof in order not to stream it
            unset($oof->bodytype);
            return true;
        }
        else {
            return false;
        }
    }


    /**
     * Gets the user's email address from server
     *
     * @param SyncObject $userinformation
     *
     * @access private
     * @return void
     */
    private function settingsUserInformation(&$userinformation) {
        $userinformation->Status = SYNC_SETTINGSSTATUS_USERINFO_SUCCESS;
        if (Request::GetProtocolVersion() >= 14.1) {
            $account = new SyncAccount();
            $emailaddresses = new SyncEmailAddresses();
            $emailaddresses->smtpaddress[] = $this->username;
            $emailaddresses->primarysmtpaddress = $this->username;
            $account->emailaddresses = $emailaddresses;
            $userinformation->accounts[] = $account;
        }
        else {
            $userinformation->emailaddresses[] = $this->username;
        }
        return true;
    }


    /**
     * Gets the folder list
     *
     * @access private
     * @return array
     */
    private function get_folder_list() {
        $folders = array();
        $list = @imap_getmailboxes($this->mbox, $this->server, "*");
        if (is_array($list)) {
            $list = array_reverse($list);
            foreach ($list as $l) {
                $folders[] = $l->name;
            }
        }

        return $folders;
    }

    /**
     * Add one address to the list
     *
     * @access private
     * @param array $addresses
     * @param RFC822 address object $addr
     * @return string
     */
    private function add_address_to_list(&$addresses, $addr) {
        $name = "";

        if (isset($addr->mailbox) && isset($addr->host) && isset($addr->personal)) {
            $address = sprintf("%s@%s", $addr->mailbox, $addr->host);
            $name = $addr->personal;

            if(strlen($name) == 0 || $name == $address) {
                $fulladdr = $address;
            }
            else {
                if (preg_match('/^\".*\"$/', $name)) {
                    $fulladdr = sprintf("%s <%s>", $name, $address);
                }
                else {
                    $fulladdr = sprintf("\"%s\" <%s>", $name, $address);
                }
            }

            array_push($addresses, $fulladdr);
        }

        return $name;
    }

    /**
     * Close the IMAP connection.
     *
     * @access private
     */
    private function close_connection() {
        if ($this->mbox) {
            // list all errors
            $errors = imap_errors();
            if (is_array($errors)) {
                foreach ($errors as $e) {
                    if (stripos($e, "fail") !== false) {
                        $level = LOGLEVEL_WARN;
                    }
                    else {
                        $level = LOGLEVEL_DEBUG;
                    }
                    ZLog::Write($level, "BackendIMAP->close_connection(): IMAP said: " . $e);
                }
            }
            @imap_close($this->mbox);
            ZLog::Write(LOGLEVEL_DEBUG, "BackendIMAP->close_connection(): disconnected from IMAP server");
            $this->mbox = false;
        }
    }

    /**
     * Gets the folder list attributes
     *
     * @access private
     * @return array of ( ['name'], ['noInferiors'], ['noSelect'], ['marked'], ['referral'], ['children'] )
     */
    private function get_attributes_list() {
        $attributes = array();
        $list = @imap_getmailboxes($this->mbox, $this->server, "*");
        if (is_array($list)) {
            $list = array_reverse($list);
            foreach ($list as $l) {
                $attr = array(
                    'name' => $l->name,
                    'noInferiors' => (($l->attributes & LATT_NOINFERIORS) != false) ,
                    'noSelect' => (($l->attributes & LATT_NOSELECT) != false) ,
                    'referral' => (($l->attributes & LATT_REFERRAL) != false)
                );
                if ($l->attributes & LATT_MARKED) {
                    $attr['marked'] = true;
                } elseif ($l->attributes & LATT_UNMARKED) {
                    $attr['marked'] = false;    
                }
                if ($l->attributes & LATT_HASCHILDREN) {
                    $attr['children'] = true;
                } elseif ($l->attributes & LATT_HASNOCHILDREN) {
                    $attr['children'] = false;
                }
                $attributes[] = $attr;
                $attr = array();
            }
        }
        return $attributes;
    }
};
