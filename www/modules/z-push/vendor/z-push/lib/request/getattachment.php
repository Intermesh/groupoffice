<?php
/***********************************************
* File      :   getattachment.php
* Project   :   Z-Push
* Descr     :   Provides the GETATTACHMENT command
*
* Created   :   16.02.2012
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

class GetAttachment extends RequestProcessor {

    /**
     * Handles the GetAttachment command
     *
     * @param int       $commandCode
     *
     * @access public
     * @return boolean
     */
    public function Handle($commandCode) {
        $attname = Request::GetGETAttachmentName();
        if(!$attname)
            return false;

        try {
            $attachment = self::$backend->GetAttachmentData($attname);
            $stream = $attachment->data;
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("HandleGetAttachment(): attachment stream from backend: %s", $stream));

            // need to check for a resource here, as eg. feof('Error') === false and causing infinit loop in while!
            if (!is_resource($stream))
                throw new StatusException(sprintf("HandleGetAttachment(): No stream resource returned by backend for attachment: %s", $attname), SYNC_ITEMOPERATIONSSTATUS_INVALIDATT);

            header("Content-Type: application/octet-stream");
            self::$topCollector->AnnounceInformation("Starting attachment streaming", true);
            $l = fpassthru($stream);
            fclose($stream);
            if ($l === false)
                throw new FatalException("HandleGetAttachment(): fpassthru === false !!!");
            self::$topCollector->AnnounceInformation(sprintf("Streamed %d KB attachment", round($l/1024)), true);
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("HandleGetAttachment(): attachment with %d KB sent to mobile", round($l/1024)));
        }
        catch (StatusException $s) {
            // StatusException already logged so we just need to pass it upwards to send a HTTP error
            throw new HTTPReturnCodeException($s->getMessage(), HTTP_CODE_500, null, LOGLEVEL_DEBUG);
        }

        return true;
    }
}
