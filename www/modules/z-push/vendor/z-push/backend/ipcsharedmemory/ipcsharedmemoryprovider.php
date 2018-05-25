<?php
/***********************************************
* File      :   ipcsharedmemoryprovider.php
* Project   :   Z-Push
* Descr     :   IPC Provider for PHP shared memory extension
*
* Created   :   20.10.2011
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

class IpcSharedMemoryProvider implements IIpcProvider {
    private $mutexid;
    private $memid;
    protected $type;
    protected $allocate;

    /**
     * Constructor
     *
     * @param int $type
     * @param int $allocate
     * @param string $class
     */
    public function __construct($type, $allocate, $class) {
        $this->type = $type;
        $this->allocate = $allocate;

        if ($this->initSharedMem())
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("%s(): Initialized mutexid %s and memid %s.", $class, $this->mutexid, $this->memid));
    }

    /**
     * Allocates shared memory.
     *
     * @access private
     * @return boolean
     */
    private function initSharedMem() {
        if (!function_exists('sem_get') || !function_exists('shm_attach') || !function_exists('sem_acquire')|| !function_exists('shm_get_var')) {
            ZLog::Write(LOGLEVEL_INFO, "IpcSharedMemoryProvider->initSharedMem(): PHP libraries for the use shared memory are not available. Check the isntalled php packages or use e.g. the memcache IPC provider.");
            return false;
        }

        // Create mutex
        $this->mutexid = @sem_get($this->type, 1);
        if ($this->mutexid === false) {
            ZLog::Write(LOGLEVEL_ERROR, "IpcSharedMemoryProvider->initSharedMem(): could not aquire semaphore");
            return false;
        }

        // Attach shared memory
        $this->memid = shm_attach($this->type+10, $this->allocate);
        if ($this->memid === false) {
            ZLog::Write(LOGLEVEL_ERROR, "IpcSharedMemoryProvider->initSharedMem(): could not attach shared memory");
            @sem_remove($this->mutexid);
            $this->mutexid = false;
            return false;
        }

        // TODO mem cleanup has to be implemented
        //$this->setInitialCleanTime();

        return true;
    }

    /**
     * Removes and detaches shared memory.
     *
     * @access private
     * @return boolean
     */
    private function removeSharedMem() {
        if ((isset($this->mutexid) && $this->mutexid !== false) && (isset($this->memid) && $this->memid !== false)) {
            @sem_acquire($this->mutexid);
            $memid = $this->memid;
            $this->memid = false;
            @sem_release($this->mutexid);

            @sem_remove($this->mutexid);
            @shm_remove($memid);
            @shm_detach($memid);

            $this->mutexid = false;

            return true;
        }
        return false;
    }

    /**
     * Reinitializes the IPC data by removing, detaching and re-allocating it.
     *
     * @access public
     * @return boolean
     */
    public function ReInitIPC() {
        return ($this->removeSharedMem() && $this->initSharedMem());
    }

    /**
     * Cleans up the IPC data block.
     *
     * @access public
     * @return boolean
     */
    public function Clean() {
        $stat = false;

        // exclusive block
        if ($this->BlockMutex()) {
            $cleanuptime = ($this->HasData(1)) ? $this->GetData(1) : false;

            // TODO implement Shared Memory cleanup

            $this->ReleaseMutex();
        }
        // end exclusive block

        return $stat;
    }

    /**
     * Indicates if the IPC is active.
     *
     * @access public
     * @return boolean
     */
    public function IsActive() {
        return ((isset($this->mutexid) && $this->mutexid !== false) && (isset($this->memid) && $this->memid !== false));
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
        if ((isset($this->mutexid) && $this->mutexid !== false) && (isset($this->memid) && $this->memid !== false))
            return @sem_acquire($this->mutexid);

        return false;
    }

    /**
     * Releases the class mutex.
     * After the release other processes are able to block the mutex themselves.
     *
     * @access public
     * @return boolean
     */
    public function ReleaseMutex() {
        if ((isset($this->mutexid) && $this->mutexid !== false) && (isset($this->memid) && $this->memid !== false))
            return @sem_release($this->mutexid);

        return false;
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
        if ((isset($this->mutexid) && $this->mutexid !== false) && (isset($this->memid) && $this->memid !== false)) {
            if (function_exists("shm_has_var"))
                return @shm_has_var($this->memid, $id);
            else {
                $some = $this->GetData($id);
                return isset($some);
            }
        }
        return false;
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
        if ((isset($this->mutexid) && $this->mutexid !== false) && (isset($this->memid) && $this->memid !== false))
            return @shm_get_var($this->memid, $id);

        return ;
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
        if ((isset($this->mutexid) && $this->mutexid !== false) && (isset($this->memid) && $this->memid !== false))
            return @shm_put_var($this->memid, $id, $data);

        return false;
    }

    /**
     * Sets the time when the shared memory block was created.
     *
     * @access private
     * @return boolean
     */
    private function setInitialCleanTime() {
        $stat = false;

        // exclusive block
        if ($this->BlockMutex()) {

            if ($this->HasData(1) == false)
                $stat = $this->SetData(time(), 1);

            $this->ReleaseMutex();
        }
        // end exclusive block

        return $stat;
    }

}
