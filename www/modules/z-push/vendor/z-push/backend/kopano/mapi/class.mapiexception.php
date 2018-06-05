<?php
/*
 * Copyright 2005 - 2016  Zarafa B.V. and its licensors
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
 */

    /**
     * MAPIException
     * if enabled using mapi_enable_exceptions then php-ext can throw exceptions when
     * any error occurs in mapi calls. this exception will only be thrown when severity bit is set in
     * error code that means it will be thrown only for mapi errors not for mapi warnings.
     */
    // FatalException will trigger a HTTP return code 500 to the mobile
    class MAPIException extends FatalException
    {
        /**
         * Function will return display message of exception if its set by the calle.
         * if it is not set then we are generating some default display messages based
         * on mapi error code.
         * @return string returns error-message that should be sent to client to display.
         */
        public function getDisplayMessage()
        {
            if(!empty($this->displayMessage))
                return $this->displayMessage;

            switch($this->getCode())
            {
                case MAPI_E_NO_ACCESS:
                    return _("You have insufficient privileges to open this object.");
                case MAPI_E_LOGON_FAILED:
                case MAPI_E_UNCONFIGURED:
                    return _("Logon Failed. Please check your username/password.");
                case MAPI_E_NETWORK_ERROR:
                    return _("Can not connect to Kopano server.");
                case MAPI_E_UNKNOWN_ENTRYID:
                    return _("Can not open object with provided id.");
                case MAPI_E_NO_RECIPIENTS:
                    return _("There are no recipients in the message.");
                case MAPI_E_NOT_FOUND:
                    return _("Can not find object.");
                case MAPI_E_INTERFACE_NOT_SUPPORTED:
                case MAPI_E_INVALID_PARAMETER:
                case MAPI_E_INVALID_ENTRYID:
                case MAPI_E_INVALID_OBJECT:
                case MAPI_E_TOO_COMPLEX:
                case MAPI_E_CORRUPT_DATA:
                case MAPI_E_END_OF_SESSION:
                case MAPI_E_AMBIGUOUS_RECIP:
                case MAPI_E_COLLISION:
                case MAPI_E_UNCONFIGURED:
                default :
                    return sprintf(_("Unknown MAPI Error: %s"), get_mapi_error_name($this->getCode()));
            }
        }
    }

    // Tell the PHP extension which exception class to instantiate
    if (function_exists('mapi_enable_exceptions')) {
       //mapi_enable_exceptions("mapiexception");
    }
