<?php
/***********************************************
* File      :   resolverecipients.php
* Project   :   Z-Push
* Descr     :   Provides the ResolveRecipients command
*
* Created   :   15.10.2012
*
* Copyright 2007 - 2013, 2015 - 2016 Zarafa Deutschland GmbH
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

class ResolveRecipients extends RequestProcessor {

    /**
     * Handles the ResolveRecipients command
     *
     * @param int       $commandCode
     *
     * @access public
     * @return boolean
     */
    public function Handle($commandCode) {
        // Parse input
        if(!self::$decoder->getElementStartTag(SYNC_RESOLVERECIPIENTS_RESOLVERECIPIENTS))
            return false;

        $resolveRecipients = new SyncResolveRecipients();
        $resolveRecipients->Decode(self::$decoder);

        if(!self::$decoder->getElementEndTag())
            return false; // SYNC_RESOLVERECIPIENTS_RESOLVERECIPIENTS

        $resolveRecipients = self::$backend->ResolveRecipients($resolveRecipients);

        self::$encoder->startWBXML();
        self::$encoder->startTag(SYNC_RESOLVERECIPIENTS_RESOLVERECIPIENTS);

            self::$encoder->startTag(SYNC_RESOLVERECIPIENTS_STATUS);
            self::$encoder->content($resolveRecipients->status);
            self::$encoder->endTag(); // SYNC_RESOLVERECIPIENTS_STATUS

            if ($resolveRecipients->status == SYNC_COMMONSTATUS_SUCCESS && !empty($resolveRecipients->response)) {
                foreach ($resolveRecipients->response as $i => $response) {
                    self::$encoder->startTag(SYNC_RESOLVERECIPIENTS_RESPONSE);
                        self::$encoder->startTag(SYNC_RESOLVERECIPIENTS_TO);
                        self::$encoder->content($response->to);
                        self::$encoder->endTag(); // SYNC_RESOLVERECIPIENTS_TO

                        self::$encoder->startTag(SYNC_RESOLVERECIPIENTS_STATUS);
                        self::$encoder->content($response->status);
                        self::$encoder->endTag(); // SYNC_RESOLVERECIPIENTS_STATUS

                        // do only if recipient is resolved
                        if ($response->status != SYNC_RESOLVERECIPSSTATUS_RESPONSE_UNRESOLVEDRECIP && !empty($response->recipient)) {
                            self::$encoder->startTag(SYNC_RESOLVERECIPIENTS_RECIPIENTCOUNT);
                            self::$encoder->content(count($response->recipient));
                            self::$encoder->endTag(); // SYNC_RESOLVERECIPIENTS_RECIPIENTCOUNT

                            foreach ($response->recipient as $recipient) {
                                self::$encoder->startTag(SYNC_RESOLVERECIPIENTS_RECIPIENT);
                                $recipient->Encode(self::$encoder);
                                self::$encoder->endTag(); // SYNC_RESOLVERECIPIENTS_RECIPIENT
                            }
                        }
                    self::$encoder->endTag(); // SYNC_RESOLVERECIPIENTS_RESPONSE
                }
            }

        self::$encoder->endTag(); // SYNC_RESOLVERECIPIENTS_RESOLVERECIPIENTS
        return true;
    }
}