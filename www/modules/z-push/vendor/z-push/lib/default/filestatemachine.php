<?php
/***********************************************
* File      :   filestatemachine.php
* Project   :   Z-Push
* Descr     :   This class handles state requests;
*               Each Import/Export mechanism can
*               store its own state information,
*               which is stored through the
*               state machine.
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

class FileStateMachine implements IStateMachine {
    const SUPPORTED_STATE_VERSION = IStateMachine::STATEVERSION_02;
    const VERSION = "version";

    private $userfilename;
    private $settingsfilename;
    private $statefiles; // List of the state files. Used by z-push-admin and scripts.
    private $devicedatafiles; // List of the device data files. Used by z-push-admin and scripts.
    private $pattern; // State pattern for glob()

    /**
     * Constructor
     *
     * Performs some basic checks and initilizes the state directory
     *
     * @access public
     * @throws FatalMisconfigurationException
     */
    public function __construct() {
        if (!defined('STATE_DIR'))
            throw new FatalMisconfigurationException("No configuration for the state directory available.");

        if (substr(STATE_DIR, -1,1) != "/")
            throw new FatalMisconfigurationException("The configured state directory should terminate with a '/'");

        if (!file_exists(STATE_DIR))
            throw new FatalMisconfigurationException("The configured state directory does not exist or can not be accessed: ". STATE_DIR);
        // checks if the directory exists and tries to create the necessary subfolders if they do not exist
        $this->getDirectoryForDevice(Request::GetDeviceID());
        $this->userfilename = STATE_DIR . 'users';
        $this->settingsfilename = STATE_DIR . 'settings';

        if ((!file_exists($this->userfilename) && !touch($this->userfilename)) || !is_writable($this->userfilename))
            throw new FatalMisconfigurationException("Not possible to write to the configured state directory.");
        Utils::FixFileOwner($this->userfilename);

        $this->statefiles = array();
        $this->devicedatafiles = array();
        $this->pattern = STATE_DIR.'*/*/*';
    }

    /**
     * Gets a hash value indicating the latest dataset of the named
     * state with a specified key and counter.
     * If the state is changed between two calls of this method
     * the returned hash should be different
     *
     * @param string    $devid              the device id
     * @param string    $type               the state type
     * @param string    $key                (opt)
     * @param string    $counter            (opt)
     *
     * @access public
     * @return string
     * @throws StateNotFoundException, StateInvalidException, UnavailableException
     */
    public function GetStateHash($devid, $type, $key = false, $counter = false) {
        $filename = $this->getFullFilePath($devid, $type, $key, $counter);

        // the filemodification time is enough to track changes
        if(file_exists($filename))
            return filemtime($filename);
        else
            throw new StateNotFoundException(sprintf("FileStateMachine->GetStateHash(): Could not locate state '%s'",$filename));
    }

    /**
     * Gets a state for a specified key and counter.
     * This method sould call IStateMachine->CleanStates()
     * to remove older states (same key, previous counters)
     *
     * @param string    $devid              the device id
     * @param string    $type               the state type
     * @param string    $key                (opt)
     * @param string    $counter            (opt)
     * @param string    $cleanstates        (opt)
     *
     * @access public
     * @return mixed
     * @throws StateNotFoundException, StateInvalidException, UnavailableException
     */
    public function GetState($devid, $type, $key = false, $counter = false, $cleanstates = true) {
        if ($counter && $cleanstates)
            $this->CleanStates($devid, $type, $key, $counter);

        // Read current sync state
        $filename = $this->getFullFilePath($devid, $type, $key, $counter);

        if(file_exists($filename)) {
            $contents = Utils::SafeGetContents($filename, __FUNCTION__, false);
            $bytes = strlen($contents);
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("FileStateMachine->GetState() read '%d' bytes from file: '%s'", $bytes, $filename ));
            return unserialize($contents);
        }
        // throw an exception on all other states, but not FAILSAVE as it's most of the times not there by default
        else if ($type !== IStateMachine::FAILSAVE)
            throw new StateNotFoundException(sprintf("FileStateMachine->GetState(): Could not locate state '%s'",$filename));
    }

    /**
     * Writes ta state to for a key and counter
     *
     * @param mixed     $state
     * @param string    $devid              the device id
     * @param string    $type               the state type
     * @param string    $key                (opt)
     * @param int       $counter            (opt)
     *
     * @access public
     * @return boolean
     * @throws StateInvalidException, UnavailableException
     */
    public function SetState($state, $devid, $type, $key = false, $counter = false) {
        $state = serialize($state);

        $filename = $this->getFullFilePath($devid, $type, $key, $counter);
        if (($bytes = Utils::SafePutContents($filename, $state)) === false)
            throw new FatalMisconfigurationException(sprintf("FileStateMachine->SetState(): Could not write state '%s'",$filename));

        ZLog::Write(LOGLEVEL_DEBUG, sprintf("FileStateMachine->SetState() written %d bytes on file: '%s'", $bytes, $filename));
        return $bytes;
    }

    /**
     * Cleans up all older states.
     * If called with a $counter, all states previous state counter can be removed.
     * If additionally the $thisCounterOnly flag is true, only that specific counter will be removed.
     * If called without $counter, all keys (independently from the counter) can be removed.
     *
     * @param string    $devid              the device id
     * @param string    $type               the state type
     * @param string    $key
     * @param string    $counter            (opt)
     * @param string    $thisCounterOnly    (opt) if provided, the exact counter only will be removed
     *
     * @access public
     * @return
     * @throws StateInvalidException
     */
    public function CleanStates($devid, $type, $key, $counter = false, $thisCounterOnly = false) {
        $matching_files = $this->getStateFiles($this->getFullFilePath($devid, $type, $key). "*");
        if (is_array($matching_files)) {
            foreach($matching_files as $state) {
                $file = false;
                if($counter !== false && preg_match('/([0-9]+)$/', $state, $matches)) {
                    if(($thisCounterOnly === false && $matches[1] < $counter) || ($thisCounterOnly === true && $matches[1] == $counter)) {
                        $candidate = $this->getFullFilePath($devid, $type, $key, (int)$matches[1]);

                        if ($candidate == $state)
                            $file = $candidate;
                    }
                }
                else if ($counter === false)
                    $file =  $this->getFullFilePath($devid, $type, $key);

                if ($file !== false) {
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("FileStateMachine->CleanStates(): Deleting file: '%s'", $file));
                    unlink ($file);
                }
            }
        }
    }

    /**
     * Links a user to a device
     *
     * @param string    $username
     * @param string    $devid
     *
     * @access public
     * @return boolean     indicating if the user was added or not (existed already)
     */
    public function LinkUserDevice($username, $devid) {
        $mutex = new SimpleMutex();
        $changed = false;

        // exclusive block
        if ($mutex->Block()) {
            $filecontents = Utils::SafeGetContents($this->userfilename, __FUNCTION__, true);

            if ($filecontents)
                $users = unserialize($filecontents);
            else
                $users = array();

            // add user/device to the list
            if (!isset($users[$username])) {
                $users[$username] = array();
                $changed = true;
            }
            if (!isset($users[$username][$devid])) {
                $users[$username][$devid] = 1;
                $changed = true;
            }

            if ($changed) {
                $bytes = Utils::SafePutContents($this->userfilename, serialize($users), true);
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("FileStateMachine->LinkUserDevice(): wrote %d bytes to users file", $bytes));
            }
            else
                ZLog::Write(LOGLEVEL_DEBUG, "FileStateMachine->LinkUserDevice(): nothing changed");

            $mutex->Release();
        }
        return $changed;
    }

   /**
     * Unlinks a device from a user
     *
     * @param string    $username
     * @param string    $devid
     *
     * @access public
     * @return boolean
     */
    public function UnLinkUserDevice($username, $devid) {
        $mutex = new SimpleMutex();
        $changed = false;

        // exclusive block
        if ($mutex->Block()) {
            $filecontents = Utils::SafeGetContents($this->userfilename, __FUNCTION__, true);

            if ($filecontents)
                $users = unserialize($filecontents);
            else
                $users = array();

            // is this user listed at all?
            if (isset($users[$username])) {
                if (isset($users[$username][$devid])) {
                    unset($users[$username][$devid]);
                    $changed = true;
                }

                // if there is no device left, remove the user
                if (empty($users[$username])) {
                    unset($users[$username]);
                    $changed = true;
                }
            }

            if ($changed) {
                $bytes = Utils::SafePutContents($this->userfilename, serialize($users), true);
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("FileStateMachine->UnLinkUserDevice(): wrote %d bytes to users file", $bytes));
            }
            else
                ZLog::Write(LOGLEVEL_DEBUG, "FileStateMachine->UnLinkUserDevice(): nothing changed");

            $mutex->Release();
        }
        return $changed;
    }

    /**
     * Returns an array with all device ids for a user.
     * If no user is set, all device ids should be returned
     *
     * @param string    $username   (opt)
     *
     * @access public
     * @return array
     */
    public function GetAllDevices($username = false) {
        $out = array();
        if ($username === false) {
            foreach ($this->getDeviceDataFiles() as $devdata)
                // TODO do we still need this check here?
                if (preg_match('/\/([A-Za-z0-9]+)-'. IStateMachine::DEVICEDATA. '$/', $devdata, $matches))
                    $out[] = $matches[1];
            return $out;
        }
        else {
            $filecontents = Utils::SafeGetContents($this->userfilename, __FUNCTION__, false);
            if ($filecontents)
                $users = unserialize($filecontents);
            else
                $users = array();

            // get device list for the user
            if (isset($users[$username]))
                return array_keys($users[$username]);
            else
                return array();
        }
    }

    /**
     * Returns the current version of the state files
     *
     * @access public
     * @return int
     */
    public function GetStateVersion() {
        if (file_exists($this->settingsfilename)) {
            $filecontents = Utils::SafeGetContents($this->settingsfilename, __FUNCTION__, false);
            $settings = unserialize($filecontents);
            if (strtolower(gettype($settings) == "string") && strtolower($settings) == '2:1:{s:7:"version";s:1:"2";}') {
                ZLog::Write(LOGLEVEL_INFO, "Broken state version file found. Attempt to autofix it. See https://jira.zarafa.com/browse/ZP-493 for more information.");
                unlink($this->settingsfilename);
                $this->SetStateVersion(IStateMachine::STATEVERSION_02);
                $settings = array(self::VERSION => IStateMachine::STATEVERSION_02);
            }
        }
        else {
            $filecontents = Utils::SafeGetContents($this->userfilename, __FUNCTION__, true);
            if ($filecontents)
                $settings = array(self::VERSION => IStateMachine::STATEVERSION_01);
            else {
                $settings = array(self::VERSION => self::SUPPORTED_STATE_VERSION);
                $this->SetStateVersion(self::SUPPORTED_STATE_VERSION);
            }
        }

        return $settings[self::VERSION];
    }

    /**
     * Sets the current version of the state files
     *
     * @param int       $version            the new supported version
     *
     * @access public
     * @return boolean
     */
    public function SetStateVersion($version) {
        if (file_exists($this->settingsfilename)){
            $filecontents = Utils::SafeGetContents($this->settingsfilename, __FUNCTION__, false);
            $settings = unserialize($filecontents);
        }
        else
            $settings = array(self::VERSION => IStateMachine::STATEVERSION_01);

        $settings[self::VERSION] = $version;
        ZLog::Write(LOGLEVEL_INFO, sprintf("FileStateMachine->SetStateVersion() saving supported state version, value '%d'", $version));
        $status = Utils::SafePutContents($this->settingsfilename, serialize($settings));
        return $status;
    }

    /**
     * Returns all available states for a device id
     *
     * @param string    $devid              the device id
     *
     * @access public
     * @return array(mixed)
     */
    public function GetAllStatesForDevice($devid) {
        $types = array(IStateMachine::DEVICEDATA, IStateMachine::FOLDERDATA, IStateMachine::FAILSAVE, IStateMachine::HIERARCHY, IStateMachine::BACKENDSTORAGE);
        $out = array();
        $devdir = $this->getDirectoryForDevice($devid) . "/$devid-";
        $deviceFiles = array_filter($this->getStateFiles(), function($var) use ($devdir) {return strpos($var, $devdir) !== false;});
        foreach ($deviceFiles as $devdata) {
            // cut the device dir away and split into parts
            $parts = explode("-", substr($devdata, strlen($devdir)));

            $state = array('type' => false, 'counter' => false, 'uuid' => false);

            // part 0 could be "devicedata" or another type in broken states
            if (isset($parts[0]) && in_array($parts[0], $types))
                $state['type'] = $parts[0];

            if (isset($parts[0], $parts[1], $parts[2], $parts[3], $parts[4]) &&
                    strlen($parts[0]) == 8 && strlen($parts[1]) == 4 &&
                    strlen($parts[2]) == 4 && strlen($parts[3]) == 4 &&
                    strlen($parts[4]) == 12) {

                $state['uuid'] = $parts[0]."-".$parts[1]."-".$parts[2]."-".$parts[3]."-".$parts[4];
            }

            if (isset($parts[5]) && is_numeric($parts[5])) {
                $state['counter'] = $parts[5];
                $state['type'] = ""; // default
            }

            if (isset($parts[5])) {
                if (is_int($parts[5])) {
                    $state['counter'] = $parts[5];
                }
                else if (in_array($parts[5], $types)) {
                    $state['type'] = $parts[5];
                }
            }
            if (isset($parts[6]) && is_numeric($parts[6])) {
                $state['counter'] = $parts[6];
            }
            // Permanent BS are recognized here
            if($state['counter'] == false && $state['uuid'] == false && isset($parts[1]) && is_numeric($parts[1])) {
                $state['counter'] = $parts[1];
            }

            $out[] = $state;
        }
        return $out;
    }


    /**----------------------------------------------------------------------------------------------------------
     * Private FileStateMachine stuff
     */

    /**
     * Returns the full path incl. filename for a key (generally uuid) and a counter
     *
     * @param string    $devid              the device id
     * @param string    $type               the state type
     * @param string    $key                (opt)
     * @param string    $counter            (opt) default false
     * @param boolean   $doNotCreateDirs    (opt) indicates if missing subdirectories should be created, default false
     *
     * @access private
     * @return string
     * @throws StateInvalidException
     */
    private function getFullFilePath($devid, $type, $key = false, $counter = false, $doNotCreateDirs = false) {
        $testkey = $devid . (($key !== false)? "-". $key : "") . (($type !== "")? "-". $type : "");
        if (preg_match('/^[a-zA-Z0-9-]+$/', $testkey, $matches) || ($type == "" && $key === false))
            $internkey = $testkey . (($counter && is_int($counter))?"-".$counter:"");
        else
            throw new StateInvalidException("FileStateMachine->getFullFilePath(): Invalid state deviceid, type, key or in any combination");

        return $this->getDirectoryForDevice($devid, $doNotCreateDirs) ."/". $internkey;
    }

    /**
     * Checks if the configured path exists and if a subfolder structure is available
     *  A two level deep subdirectory structure is build to save the states.
     *  The subdirectories where to save, are determined with device id
     *
     * @param string    $devid                  the device id
     * @param boolen    $doNotCreateDirs        (opt) by default false - indicates if the subdirs should be created
     *
     * @access private
     * @return string/boolean                   returns the full directory of false if the dirs can not be created
     * @throws FatalMisconfigurationException   when configured directory is not writeable
     */
    private function getDirectoryForDevice($devid, $doNotCreateDirs = false) {
        $firstLevel = substr(strtolower($devid), -1, 1);
        $secondLevel = substr(strtolower($devid), -2, 1);

        $dir = STATE_DIR . $firstLevel . "/" . $secondLevel;
        if (is_dir($dir))
            return $dir;

        if ($doNotCreateDirs === false) {
            // try to create the subdirectory structure necessary
            $fldir = STATE_DIR . $firstLevel;
            if (!is_dir($fldir)) {
                $dirOK = mkdir($fldir);
                if (!$dirOK)
                    throw new FatalMisconfigurationException("FileStateMachine->getDirectoryForDevice(): Not possible to create state sub-directory: ". $fldir);
            }

            if (!is_dir($dir)) {
                $dirOK = mkdir($dir);
                if (!$dirOK)
                    throw new FatalMisconfigurationException("FileStateMachine->getDirectoryForDevice(): Not possible to create state sub-directory: ". $dir);
            }
            else
                return $dir;
        }
        return false;
    }

    /**
     * Returns the list of the state files.
     *
     * @param string    $pattern state pattern for glob()
     *
     * @access public
     * @return array
     */
    protected function getStateFiles($pattern = null) {
        if ($pattern === null || $pattern === $this->pattern) {
            if (empty($this->statefiles)) {
                $this->statefiles = glob($this->pattern, GLOB_NOSORT);
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("FileStateMachine->getStateFiles() read all state files '%d'", sizeof($this->statefiles)));
            }
            return $this->statefiles;
        }
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("FileStateMachine->getStateFiles() reading state files of '%s'", $pattern));
        return glob($pattern, GLOB_NOSORT);
    }

    /**
     * Filters the list of the state files and returns the device data files only.
     *
     * @access public
     * @return array
     */
    protected function getDeviceDataFiles() {
        if (empty($this->devicedatafiles)) {
            $this->devicedatafiles = array_filter($this->GetStateFiles(), function($var) {return strpos($var, IStateMachine::DEVICEDATA) !== false;} );
        }
        return $this->devicedatafiles;
    }
}
