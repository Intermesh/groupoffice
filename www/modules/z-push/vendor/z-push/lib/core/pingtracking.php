<?php
/***********************************************
* File      :   pingtracking.php
* Project   :   Z-Push
* Descr     :
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

class PingTracking extends InterProcessData {

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct() {
        // initialize super parameters
        $this->allocate = 512000; // 500 KB
        $this->type = 2;
        parent::__construct();

        $this->initPing();
    }

    /**
     * Destructor
     * Used to remove the current ping data from shared memory
     *
     * @access public
     */
    public function __destruct() {
        // exclusive block
        if ($this->blockMutex()) {
            $pings = $this->getData();

            // check if our ping is still in the list
            if (isset($pings[self::$devid][self::$user][self::$pid])) {
                unset($pings[self::$devid][self::$user][self::$pid]);
                $stat = $this->setData($pings);
            }

            $this->releaseMutex();
        }
        // end exclusive block
    }

    /**
     * Initialized the current request
     *
     * @access public
     * @return boolean
     */
    protected function initPing() {
        $stat = false;

        // initialize params
        $this->initializeParams();

        // exclusive block
        if ($this->blockMutex()) {
            $pings = ($this->hasData()) ? $this->getData() : array();

            // set the start time for the current process
            $this->checkArrayStructure($pings);
            $pings[self::$devid][self::$user][self::$pid] = self::$start;
            $stat = $this->setData($pings);
            $this->releaseMutex();
        }
        // end exclusive block

        return $stat;
    }

    /**
     * Checks if there are newer ping requests for the same device & user so
     * the current process could be terminated
     *
     * @access public
     * @return boolean      true if the current process is obsolete
     */
    public function DoForcePingTimeout() {
        $pings = false;
        // exclusive block
        if ($this->blockMutex()) {
            $pings = $this->getData();
            $this->releaseMutex();
        }
        // end exclusive block

        // check if there is another (and newer) active ping connection
        if (is_array($pings) && isset($pings[self::$devid][self::$user]) && count($pings[self::$devid][self::$user]) > 1) {
            foreach ($pings[self::$devid][self::$user] as $pid=>$starttime)
                if ($starttime > self::$start)
                    return true;
        }

        return false;
    }

    /**
     * Builds an array structure for the concurrent ping connection detection
     *
     * @param array $array      reference to the ping data array
     *
     * @access private
     * @return
     */
    private function checkArrayStructure(&$array) {
        if (!is_array($array))
            $array = array();

        if (!isset($array[self::$devid]))
            $array[self::$devid] = array();

        if (!isset($array[self::$devid][self::$user]))
            $array[self::$devid][self::$user] = array();

    }
}
