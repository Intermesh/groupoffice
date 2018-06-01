<?php
/***********************************************
* File      :   zpushexceptions.php
* Project   :   Z-Push
* Descr     :   Main Z-Push exception
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

class ZPushException extends Exception {
    protected $defaultLogLevel = LOGLEVEL_FATAL;
    protected $httpReturnCode = HTTP_CODE_500;
    protected $httpReturnMessage = "Internal Server Error";
    protected $httpHeaders = array();
    protected $showLegal = true;

    public function __construct($message = "", $code = 0, $previous = NULL, $logLevel = false) {
        if (! $message)
            $message = $this->httpReturnMessage;

        if (!$logLevel)
            $logLevel = $this->defaultLogLevel;

        parent::__construct($message, (int) $code);
        ZLog::Write($logLevel, get_class($this) .': '. $message . ' - code: '.$code. ' - file: '. $this->getFile().':'.$this->getLine(), false);
    }

    public function getHTTPCodeString() {
        return $this->httpReturnCode . " ". $this->httpReturnMessage;
    }

    public function getHTTPHeaders() {
        return $this->httpHeaders;
    }

    public function showLegalNotice() {
        return $this->showLegal;
    }
}
