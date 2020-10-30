<?php
/***********************************************
 * File      :   log.php
 * Project   :   Z-Push
 * Descr     :   Logging functionalities
 *
 * Created   :   13.11.2015
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

abstract class Log {

    /**
     * @var string
     */
    protected $user = '';

    /**
     * @var string
     */
    protected $authUser = '';

    /**
     * @var string
     */
    protected $devid = '';

    /**
     * @var string
     */
    protected $pid = '';

    /**
     * @var array
     */
    protected $specialLogUsers = array();

    /**
     * Only used as a cache value for IsUserInSpecialLogUsers.
     * @var array
     */
    private $isUserInSpecialLogUsers = array();

    /**
     * Only used as a cache value for IsAuthUserInSpecialLogUsers function
     * @var bool
     */
    private $isAuthUserInSpecialLogUsers = false;

    /**
     * @var array
     */
    private $unauthMessageCache = array();

    /**
     * Constructor
     */
    public function __construct() {
    }

    /**
     * Returns the current user.
     *
     * @access public
     * @return string
     */
    public function GetUser() {
        return $this->user;
    }

    /**
     * Sets the current user.
     *
     * @param string $value
     *
     * @access public
     * @return void
     */
    public function SetUser($value) {
        $this->user = $value;
    }

    /**
     * Returns the current authenticated user.
     *
     * @access public
     * @return string
     */
    public function GetAuthUser() {
        return $this->authUser;
    }

    /**
     * Sets the current authenticated user.
     *
     * @param string $value
     *
     * @access public
     * @return void
     */
    public function SetAuthUser($value) {
        $this->isAuthUserInSpecialLogUsers = false;
        $this->authUser = $value;
    }

    /**
     * Check that the current authUser ($this->GetAuthUser) is in the special log user array.
     * This call is equivalent to `$this->IsUserInSpecialLogUsers($this->GetAuthUser())` at the exception that this
     * call uses cache so there won't be more than one check to the specialLogUser for the AuthUser.
     *
     * @access public
     * @return bool
     */
    public function IsAuthUserInSpecialLogUsers() {
        if ($this->isAuthUserInSpecialLogUsers) {
            return true;
        }
        if($this->IsUserInSpecialLogUsers($this->GetAuthUser())){
            $this->isAuthUserInSpecialLogUsers = true;
            return true;
        }
        return false;
    }

    /**
     * Returns the current device id.
     *
     * @access public
     * @return string
     */
    public function GetDevid() {
        return $this->devid;
    }

    /**
     * Sets the current device id.
     *
     * @param string $value
     *
     * @access public
     * @return void
     */
    public function SetDevid($value) {
        $this->devid = $value;
    }

    /**
     * Returns the current PID (as string).
     *
     * @access public
     * @return string
     */
    public function GetPid() {
        return $this->pid;
    }

    /**
     * Sets the current PID.
     *
     * @param string $value
     *
     * @access public
     * @return void
     */
    public function SetPid($value) {
        $this->pid = $value;
    }

    /**
     * Indicates if special log users are known.
     *
     * @access public
     * @return bool True if we do have to log some specific user. False otherwise.
     */
    public function HasSpecialLogUsers() {
        return !empty($this->specialLogUsers) || $this->isAuthUserInSpecialLogUsers;
    }

    /**
     * Indicates if the user is in the special log users.
     *
     * @param string $user
     *
     * @access public
     * @return bool
     */
    public function IsUserInSpecialLogUsers($user) {
        if (isset($this->isUserInSpecialLogUsers[$user])) {
            return true;
        }
        if ($this->HasSpecialLogUsers() && in_array($user, $this->GetSpecialLogUsers())) {
            $this->isUserInSpecialLogUsers[$user] = true;
            return true;
        }
        return false;
    }

    /**
     * Returns the current special log users array.
     *
     * @access public
     * @return array
     */
    public function GetSpecialLogUsers() {
        return $this->specialLogUsers;
    }

    /**
     * Sets the current special log users array.
     *
     * @param array $value
     *
     * @access public
     * @return void
     */
    public function SetSpecialLogUsers(array $value) {
        $this->isUserInSpecialLogUsers = array(); // reset cache
        $this->specialLogUsers = $value;
    }

    /**
     * If called, the current user should get an extra log-file.
     *
     * If called until the user is authenticated (e.g. at the end of IBackend->Logon()) all
     * messages logged until then will also be logged in the user file.
     *
     * @access public
     * @return void
     */
    public function SpecialLogUser() {
        $this->isAuthUserInSpecialLogUsers = true;
    }

    /**
     * Logs a message with a given log level.
     *
     *
     * @param int $loglevel
     * @param string $message
     *
     * @access public
     * @return void
     */
    public function Log($loglevel, $message) {
        if ($loglevel <= LOGLEVEL) {
            $this->Write($loglevel, $message);
        }
        if ($loglevel <= LOGUSERLEVEL) {
            // cache log messages for unauthenticated users
            if (!RequestProcessor::isUserAuthenticated()) {
                $this->unauthMessageCache[] = array($loglevel, $message);
            }
            // user is authenticated now
            elseif ($this->IsAuthUserInSpecialLogUsers()) {
                // something was logged before the user was authenticated and cached write it to the log
                if (!empty($this->unauthMessageCache)) {
                    foreach ($this->unauthMessageCache as $authcache) {
                        $this->WriteForUser($authcache[0], $authcache[1]);
                    }
                    $this->unauthMessageCache = array();
                }
                $this->WriteForUser($loglevel, $message);
            }
            else {
                $this->unauthMessageCache[] = array($loglevel, $message);
            }
        }

        $this->afterLog($loglevel, $message);
    }

    /**
     * This function is used as an event for log implementer.
     * It happens when the ZLog static class is finished with the initialization of this instance.
     *
     * @access public
     * @return void
     */
    public function AfterInitialize() {
    }

    /**
     * This function is used as an event for log implementer.
     * It happens when the a call to the Log function is finished.
     *
     * @access protected
     * @return void
     */
    protected function afterLog($loglevel, $message) {
    }

    /**
     * Returns the string representation of the given $loglevel.
     * String can be padded
     *
     * @param int $loglevel one of the LOGLEVELs
     * @param boolean $pad
     *
     * @access protected
     * @return string
     */
    protected function GetLogLevelString($loglevel, $pad = false) {
        if ($pad)
            $s = " ";
        else
            $s = "";
        switch($loglevel) {
            case LOGLEVEL_OFF:          return ""; break;
            case LOGLEVEL_FATAL:        return "[FATAL]"; break;
            case LOGLEVEL_ERROR:        return "[ERROR]"; break;
            case LOGLEVEL_WARN:         return "[".$s."WARN]"; break;
            case LOGLEVEL_INFO:         return "[".$s."INFO]"; break;
            case LOGLEVEL_DEBUG:        return "[DEBUG]"; break;
            case LOGLEVEL_WBXML:        return "[WBXML]"; break;
            case LOGLEVEL_DEVICEID:     return "[DEVICEID]"; break;
            case LOGLEVEL_WBXMLSTACK:   return "[WBXMLSTACK]"; break;
        }
    }

    /**
     * Writes a log message to the general log.
     *
     * @param int $loglevel
     * @param string $message
     *
     * @access protected
     * @return void
     */
    abstract protected function Write($loglevel, $message);

    /**
     * Writes a log message to the user specific log.
     * @param int $loglevel
     * @param string $message
     *
     * @access public
     * @return void
     */
    abstract public function WriteForUser($loglevel, $message);
}