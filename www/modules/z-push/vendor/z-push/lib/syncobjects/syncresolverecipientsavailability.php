<?php
/***********************************************
* File      :   syncresolverecipentsavailability.php
* Project   :   Z-Push
* Descr     :   WBXML appointment entities that can be
*               parsed directly (as a stream) from WBXML.
*               It is automatically decoded
*               according to $mapping,
*               and the Sync WBXML mappings
*
* Created   :   28.12.2012
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

class SyncResolveRecipientsAvailability extends SyncObject {
    public $starttime;
    public $endtime;
    public $status;
    public $mergedfreebusy;

    public function __construct() {
        $mapping = array ();

        if (Request::GetProtocolVersion() >= 14.0) {
            $mapping[SYNC_RESOLVERECIPIENTS_STARTTIME]      = array (  self::STREAMER_VAR      => "starttime");
            $mapping[SYNC_RESOLVERECIPIENTS_ENDTIME]        = array (  self::STREAMER_VAR      => "endtime");
            $mapping[SYNC_RESOLVERECIPIENTS_STATUS]         = array (  self::STREAMER_VAR      => "status");
            $mapping[SYNC_RESOLVERECIPIENTS_MERGEDFREEBUSY] = array (  self::STREAMER_VAR      => "mergedfreebusy");
        }

        parent::__construct($mapping);
    }

}
