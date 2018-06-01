<?php
/***********************************************
* File      :   sendmail.php
* Project   :   Z-Push
* Descr     :   Provides the SENDMAIL, SMARTREPLY and SMARTFORWARD command
*
* Created   :   16.02.2012
*
* Copyright 2007 - 2013, 2016 Zarafa Deutschland GmbH
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

class SendMail extends RequestProcessor {

    /**
     * Handles the SendMail, SmartReply and SmartForward command
     *
     * @param int       $commandCode
     *
     * @access public
     * @return boolean
     */
    public function Handle($commandCode) {
        $sm = new SyncSendMail();

        $reply = $forward = $parent = $sendmail = $smartreply = $smartforward = false;
        if (Request::GetGETCollectionId())
            $parent = Request::GetGETCollectionId();
        if ($commandCode == ZPush::COMMAND_SMARTFORWARD)
            $forward = Request::GetGETItemId();
        else if ($commandCode == ZPush::COMMAND_SMARTREPLY)
            $reply = Request::GetGETItemId();

        if (self::$decoder->IsWBXML()) {
            $el = self::$decoder->getElement();

            if($el[EN_TYPE] != EN_TYPE_STARTTAG)
                return false;


            if($el[EN_TAG] == SYNC_COMPOSEMAIL_SENDMAIL)
                $sendmail = true;
            else if($el[EN_TAG] == SYNC_COMPOSEMAIL_SMARTREPLY)
                $smartreply = true;
            else if($el[EN_TAG] == SYNC_COMPOSEMAIL_SMARTFORWARD)
                $smartforward = true;

            if(!$sendmail && !$smartreply && !$smartforward)
                return false;

            $sm->Decode(self::$decoder);
        }
        else {
            $sm->mime = self::$decoder->GetPlainInputStream();
            // no wbxml output is provided, only a http OK
            $sm->saveinsent = Request::GetGETSaveInSent();
        }

        // KOE ZO-6: grep for the KOE header and set flags accordingly.
        // The header has the values verb/message-source-key/folder-source-key
        if (KOE_CAPABILITY_SENDFLAGS && preg_match("/X-Push-Flags: (\d{3})\/([a-z0-9:]+)\/([a-z0-9]+)/i", $sm->mime, $ol_flags)) {
            // "reply" and "reply-all" are handled as "reply"
            if ($ol_flags[1] == 102 || $ol_flags[1] == 103) {
                $reply = true;
            }
            else if ($ol_flags[1] == 104) {
                $forward = true;
            }
            // set source folder+item and replacemime
            if (!isset($sm->source)) {
                $sm->source = new SyncSendMailSource();
            }
            $sm->source->itemid = $ol_flags[2];
            $sm->source->folderid = $ol_flags[3];
            $sm->replacemime = true;

            ZLog::Write(LOGLEVEL_DEBUG, "SendMail(): KOE support: overwrite reply/forward flag, set parent-id and item-id, replacemime - original message should not be attached.");
        }

        // Check if it is a reply or forward. Two cases are possible:
        // 1. Either $smartreply or $smartforward are set after reading WBXML
        // 2. Either $reply or $forward are set after geting the request parameters
        if ($reply || $smartreply || $forward || $smartforward) {
            // If the mobile sends an email in WBXML data the variables below
            // should be set. If it is a RFC822 message, get the reply/forward message id
            // from the request as they are always available there
            if (!isset($sm->source)) $sm->source = new SyncSendMailSource();
            if (!isset($sm->source->itemid)) $sm->source->itemid = Request::GetGETItemId();
            if (!isset($sm->source->folderid)) $sm->source->folderid = Request::GetGETCollectionId();

            // Rewrite the AS folderid into a backend folderid
            if (isset($sm->source->folderid)) {
                $sm->source->folderid = self::$deviceManager->GetBackendIdForFolderId($sm->source->folderid);
            }
            if (isset($sm->source->itemid)) {
                list(, $sk) = Utils::SplitMessageId($sm->source->itemid);
                $sm->source->itemid = $sk;
            }
            // replyflag and forward flags are actually only for the correct icon.
            // Even if they are a part of SyncSendMail object, they won't be streamed.
            if ($smartreply || $reply)
                $sm->replyflag = true;
            else
                $sm->forwardflag = true;

            if (!isset($sm->source->folderid))
                ZLog::Write(LOGLEVEL_ERROR, sprintf("SendMail(): No parent folder id while replying or forwarding message:'%s'", (($reply) ? $reply : $forward)));
        }

        self::$topCollector->AnnounceInformation(sprintf("SendMail(): Sending email with %d bytes", strlen($sm->mime)), true);

        $statusMessage = '';
        try {
            $status = self::$backend->SendMail($sm);
        }
        catch (StatusException $se) {
            $status = $se->getCode();
            $statusMessage = $se->getMessage();
        }

        if ($status != SYNC_COMMONSTATUS_SUCCESS) {
            if (self::$decoder->IsWBXML()) {
                // TODO check no WBXML on SmartReply and SmartForward
                self::$encoder->StartWBXML();
                self::$encoder->startTag(SYNC_COMPOSEMAIL_SENDMAIL);
                self::$encoder->startTag(SYNC_COMPOSEMAIL_STATUS);
                self::$encoder->content($status); //TODO return the correct status
                self::$encoder->endTag();
                self::$encoder->endTag();
            }
            else
                throw new HTTPReturnCodeException($statusMessage, HTTP_CODE_500, null, LOGLEVEL_WARN);
        }

        return $status;
    }
}
