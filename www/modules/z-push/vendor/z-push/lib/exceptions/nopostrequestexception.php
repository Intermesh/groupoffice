<?php
/***********************************************
* File      :   nopostrequestexception.php
* Project   :   Z-Push
* Descr     :   Exception thrown if the request is not a POST request
*               The code indicates if the request identified was a OPTIONS or GET request
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

class NoPostRequestException extends FatalException {
    const OPTIONS_REQUEST = 1;
    const GET_REQUEST = 2;
    protected $defaultLogLevel = LOGLEVEL_DEBUG;
}
