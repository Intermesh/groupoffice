<?php
/***********************************************
* File      :   nopostrequestexception.php
* Project   :   Z-Push
* Descr     :   Exception thrown to return a an non 200 http return code
*               to the mobile
*
* Created   :   06.02.2012
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

class HTTPReturnCodeException extends FatalException {
    protected $defaultLogLevel = LOGLEVEL_ERROR;
    protected $showLegal = false;

    public function __construct($message = "", $code = 0, $previous = NULL, $logLevel = false) {
        if ($code)
            $this->httpReturnCode = $code;
        parent::__construct($message, (int) $code, $previous, $logLevel);
    }
}
