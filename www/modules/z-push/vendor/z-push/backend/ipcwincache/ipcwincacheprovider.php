<?php
/***********************************************
* File      :   ipcwincacheprovider.php
* Project   :   Z-Push
* Descr     :   IPC Provider for PHP wincache extension
*
* Created   :   04.12.2017
*
* Copyright 2017 messageconcept GmbH
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

class IpcWincacheProvider implements IIpcProvider {
    protected $type;
    protected $allocate;

    /**
     * Constructor
     *
     * @param int $type
     * @param int $allocate
     * @param string $class
     * @param string $serverKey
     */
    public function __construct($type, $allocate, $class, $serverKey) {

        if (!function_exists('wincache_lock')) {
            throw new FatalMisconfigurationException("IpcWincacheProvider failure: PHP libraries for wincache not found. Please make sure the wincache extension is installed and enabled.");
        }

        $this->type = $type;
    }

    /**
     * Reinitializes the IPC data by removing, detaching and re-allocating it.
     *
     * @access public
     * @return boolean
     */
    public function ReInitIPC() {
        // We simply clear the whole wincache ucache
        return wincache_ucache_clear();
    }

    /**
     * Cleans up the IPC data block.
     *
     * @access public
     * @return boolean
     */
    public function Clean() {

        return false;
    }

    /**
     * Indicates if the IPC is active.
     *
     * @access public
     * @return boolean
     */
    public function IsActive() {

        return true;
    }

    /**
     * Blocks the class mutex.
     * Method blocks until mutex is available!
     * ATTENTION: make sure that you *always* release a blocked mutex!
     *
     * @access public
     * @return boolean
     */
    public function BlockMutex() {
        if (!wincache_lock($this->type)) {
            ZLog::Write(LOGLEVEL_INFO, sprintf("%s->BlockMutex(): could not acquire lock for '%s'", get_class($this), $this->type));
            return false;
        }
        return true;
    }

    /**
     * Releases the class mutex.
     * After the release other processes are able to block the mutex themselves.
     *
     * @access public
     * @return boolean
     */
    public function ReleaseMutex() {
        if (!wincache_unlock($this->type)) {
            ZLog::Write(LOGLEVEL_INFO, sprintf("%s->ReleaseMutex(): could not release lock for '%s'", get_class($this), $this->type));
            return false;
        }

        return true;
    }

    /**
     * Indicates if the requested variable is available in IPC data.
     *
     * @param int   $id     int indicating the variable
     *
     * @access public
     * @return boolean
     */
    public function HasData($id = 2) {
        $key = $this->type.':'.$id;

        if (!wincache_ucache_exists($key)) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("%s->HasData(): no data found for key '%s'", get_class($this), $key));
            return false;
        }

        return true;
    }

    /**
     * Returns the requested variable from IPC data.
     *
     * @param int   $id     int indicating the variable
     *
     * @access public
     * @return mixed
     */
    public function GetData($id = 2) {
        $key = $this->type.':'.$id;
        $found = false;

        $result = wincache_ucache_get($key, $found);

        if (!$found) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("%s->GetData(): no data found for key '%s'", get_class($this), $key));
            return;
        }

        return $result;
    }

    /**
     * Writes the transmitted variable to IPC data.
     * Subclasses may never use an id < 2!
     *
     * @param mixed $data   data which should be saved into IPC data
     * @param int   $id     int indicating the variable (bigger than 2!)
     *
     * @access public
     * @return boolean
     */
    public function SetData($data, $id = 2) {
        $key = $this->type.':'.$id;

        if (!wincache_ucache_set($key, $data)) {
            ZLog::Write(LOGLEVEL_INFO, sprintf("%s->SetData(): failed to store data for key '%s': '%s'", get_class($this), $key, print_r($data, false)));
            return false;
        }

        return true;
    }

}
