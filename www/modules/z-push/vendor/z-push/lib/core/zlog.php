<?php
/***********************************************
* File      :   zlog.php
* Project   :   Z-Push
* Descr     :   Debug and logging
*
* Created   :   01.10.2007
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

class ZLog {
    static private $wbxmlDebug = '';
    static private $lastLogs = array();

    /**
     * @var Log $logger
     */
    static private $logger = null;

    /**
     * Initializes the logging.
     *
     * @access public
     * @return boolean
     */
    static public function Initialize() {
        // define some constants for the logging
        if (!defined('LOGUSERLEVEL'))
            define('LOGUSERLEVEL', LOGLEVEL_OFF);

        if (!defined('LOGLEVEL'))
            define('LOGLEVEL', LOGLEVEL_OFF);

        $logger = self::getLogger();

        return true;
    }

    /**
     * Check if WBXML logging is enabled in current LOG(USER)LEVEL.
     *
     * @access public
     * @return boolean
     */
    static public function IsWbxmlDebugEnabled() {
        return LOGLEVEL >= LOGLEVEL_WBXML || (LOGUSERLEVEL >= LOGLEVEL_WBXML && self::getLogger()->HasSpecialLogUsers());
    }

    /**
     * Writes a log line.
     *
     * @param int       $loglevel           one of the defined LOGLEVELS
     * @param string    $message
     * @param boolean   $truncate           indicate if the message should be truncated, default true
     *
     * @access public
     * @return void
     */
    static public function Write($loglevel, $message, $truncate = true) {
        // truncate messages longer than 10 KB
        $messagesize = strlen($message);
        if ($truncate && $messagesize > 10240)
            $message = substr($message, 0, 10240) . sprintf(" <log message with %d bytes truncated>", $messagesize);

        self::$lastLogs[$loglevel] = $message;

        try {
            self::getLogger()->Log($loglevel, $message);
        }
        catch (\Exception $e) {
            //@TODO How should we handle logging error ?
            // Ignore any error.
        }

        if ($loglevel & LOGLEVEL_WBXMLSTACK) {
            self::$wbxmlDebug .= $message . PHP_EOL;
        }
    }

    /**
     * Returns logged information about the WBXML stack.
     *
     * @access public
     * @return string
     */
    static public function GetWBXMLDebugInfo() {
        return trim(self::$wbxmlDebug);
    }

    /**
     * Returns the last message logged for a log level.
     *
     * @param int       $loglevel           one of the defined LOGLEVELS
     *
     * @access public
     * @return string/false     returns false if there was no message logged in that level
     */
    static public function GetLastMessage($loglevel) {
        return (isset(self::$lastLogs[$loglevel]))?self::$lastLogs[$loglevel]:false;
    }

    /**
     * If called, the authenticated current user gets an extra log-file.
     *
     * If called until the user is authenticated (e.g. at the end of IBackend->Logon()) all log
     * messages that happened until this point will also be logged.
     *
     * @access public
     * @return void
     */
    static public function SpecialLogUser() {
        self::getLogger()->SpecialLogUser();
    }

    /**
     * Returns the logger object. If no logger has been initialized, FileLog will be initialized and returned.
     *
     * @access private
     * @return Log
     * @throws Exception thrown if the logger class cannot be instantiated.
     */
    static private function getLogger() {
        if (!self::$logger) {
            global $specialLogUsers; // This variable comes from the configuration file (config.php)

            $logger = LOGBACKEND_CLASS;
            if (!class_exists($logger)) {
                $errmsg = 'The configured logging class `'.$logger.'` does not exist. Check your configuration.';
                error_log($errmsg);
                throw new \Exception($errmsg);
            }

            // if there is an impersonated user it's used instead of the GET user
            if (Request::GetImpersonatedUser()) {
                $user = Request::GetImpersonatedUser();
            }
            else {
                list($user) = Utils::SplitDomainUser(strtolower(Request::GetGETUser()));
            }

            self::$logger = new $logger();
            self::$logger->SetUser($user);
            self::$logger->SetAuthUser(Request::GetAuthUser());
            self::$logger->SetSpecialLogUsers($specialLogUsers);
            self::$logger->SetDevid(Request::GetDeviceID());
            self::$logger->SetPid(@getmypid());
            self::$logger->AfterInitialize();
        }
        return self::$logger;
    }

    /**----------------------------------------------------------------------------------------------------------
     * private log stuff
     */
}

/**----------------------------------------------------------------------------------------------------------
 * Legacy debug stuff
 */

// TODO review error handler
function zpush_error_handler($errno, $errstr, $errfile, $errline) {
    if (defined('LOG_ERROR_MASK')) $errno &= LOG_ERROR_MASK;

    switch ($errno) {
        case 0:
            // logging disabled by LOG_ERROR_MASK
            break;

        case E_DEPRECATED:
            // do not handle this message
            break;

        case E_NOTICE:
        case E_WARNING:
//            // TODO check if there is a better way to avoid these messages
//            if (stripos($errfile,'interprocessdata') !== false && stripos($errstr,'shm_get_var()') !== false)
//                break;
//            ZLog::Write(LOGLEVEL_WARN, "$errfile:$errline $errstr ($errno)");
//            break;

        default:
            $bt = debug_backtrace();
            ZLog::Write(LOGLEVEL_ERROR, "trace error: $errfile:$errline $errstr ($errno) - backtrace: ". (count($bt)-1) . " steps");
            for($i = 1, $bt_length = count($bt); $i < $bt_length; $i++) {
                $file = $line = "unknown";
                if (isset($bt[$i]['file'])) $file = $bt[$i]['file'];
                if (isset($bt[$i]['line'])) $line = $bt[$i]['line'];
                ZLog::Write(LOGLEVEL_ERROR, "trace: $i:". $file . ":" . $line. " - " . ((isset($bt[$i]['class']))? $bt[$i]['class'] . $bt[$i]['type']:""). $bt[$i]['function']. "()");
            }
            //throw new Exception("An error occured.");
            break;
    }
}

error_reporting(E_ALL);
set_error_handler("zpush_error_handler");


function zpush_fatal_handler() {
    $errfile = "unknown file";
    $errstr  = "shutdown";
    $errno   = E_CORE_ERROR;
    $errline = 0;

    $error = error_get_last();

    if( $error !== null) {
        $errno   = $error["type"];
        $errfile = $error["file"];
        $errline = $error["line"];
        $errstr  = $error["message"];

        // do NOT log PHP Notice, Warning, Deprecated or Strict as FATAL
        if ($errno & ~(E_NOTICE|E_WARNING|E_DEPRECATED|E_STRICT)) {
            ZLog::Write(LOGLEVEL_FATAL, sprintf("Fatal error: %s:%d - %s (%s)", $errfile, $errline, $errstr, $errno));
        }
    }
}
register_shutdown_function("zpush_fatal_handler");
