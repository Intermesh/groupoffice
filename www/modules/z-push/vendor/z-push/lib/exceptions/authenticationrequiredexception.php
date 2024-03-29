<?php
/***********************************************
* File      :   authenticationrequiredexception.php
* Project   :   Z-Push
* Descr     :   Exception sending a '401 Unauthorized' to the mobile
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

class AuthenticationRequiredException extends HTTPReturnCodeException {
    protected $defaultLogLevel = LOGLEVEL_INFO;
    protected $httpReturnCode = HTTP_CODE_401;
    protected $httpReturnMessage = "Unauthorized";
    protected $httpHeaders = array('WWW-Authenticate: Basic realm="Group-Office ActiveSync"');
    protected $showLegal = true;
}
