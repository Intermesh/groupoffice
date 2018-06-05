<?php
/***********************************************
* File      :   topcollector.php
* Project   :   Z-Push
* Descr     :   available everywhere to collect
*               data which could be displayed in z-push-top
*               the 'persistent' flag should be used with care, so
*               there is not too much information
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

class TopCollector extends InterProcessData {
    const ENABLEDAT = 2;
    const TOPDATA = 3;

    protected $preserved;
    protected $latest;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct() {
        // initialize super parameters
        $this->allocate = 2097152; // 2 MB
        $this->type = 20;
        parent::__construct();

        // initialize params
        $this->initializeParams();

        $this->preserved = array();
        // static vars come from the parent class
        $this->latest = array(  "pid"       => self::$pid,
                                "ip"        => Request::GetRemoteAddr(),
                                "user"      => self::$user,
                                "start"     => self::$start,
                                "devtype"   => Request::GetDeviceType(),
                                "devid"     => self::$devid,
                                "devagent"  => Request::GetUserAgent(),
                                "command"   => Request::GetCommandCode(),
                                "ended"     => 0,
                                "push"      => false,
                        );

        $this->AnnounceInformation("initializing");
    }

    /**
     * Destructor
     * indicates that the process is shutting down
     *
     * @access public
     */
    public function __destruct() {
        $this->AnnounceInformation("OK", false, true);
    }

    /**
     * Advices all other processes that they should start/stop
     * collecting data. The data saved is a timestamp. It has to be
     * reactivated every couple of seconds
     *
     * @param boolean   $stop       (opt) default false (do collect)
     *
     * @access public
     * @return boolean  indicating if it was set to collect before
     */
    public function CollectData($stop = false) {
        $wasEnabled = false;

        // exclusive block
        if ($this->blockMutex()) {
            $wasEnabled = ($this->hasData(self::ENABLEDAT)) ? $this->getData(self::ENABLEDAT) : false;

            $time = time();
            if ($stop === true) $time = 0;

            if (! $this->setData($time, self::ENABLEDAT))
                return false;
            $this->releaseMutex();
        }
        // end exclusive block

        return $wasEnabled;
    }

    /**
     * Announces a string to the TopCollector
     *
     * @param string    $info
     * @param boolean   $preserve       info should be displayed when process terminates
     * @param boolean   $terminating    indicates if the process is terminating
     *
     * @access public
     * @return boolean
     */
    public function AnnounceInformation($addinfo, $preserve = false, $terminating = false) {
        if (defined('TOPCOLLECTOR_DISABLED') && constant('TOPCOLLECTOR_DISABLED') === true) {
            return true;
        }

        $this->latest["addinfo"] = $addinfo;
        $this->latest["update"] = time();

        if ($terminating) {
            $this->latest["ended"] = time();
            foreach ($this->preserved as $p)
                $this->latest["addinfo"] .= " : ".$p;
        }

        if ($preserve)
            $this->preserved[] = $addinfo;

        if ($this->isEnabled()) {
            $ok = false;
            // exclusive block
            if ($this->blockMutex()) {
                $topdata = ($this->hasData(self::TOPDATA)) ? $this->getData(self::TOPDATA): array();

                $this->checkArrayStructure($topdata);

                // update
                $topdata[self::$devid][self::$user][self::$pid] = $this->latest;
                $ok = $this->setData($topdata, self::TOPDATA);
                $this->releaseMutex();
            }
            // end exclusive block
            if (!$ok) {
                ZLog::Write(LOGLEVEL_WARN, "TopCollector::AnnounceInformation(): could not write to shared memory. Z-Push top will not display this data.");
                return false;
            }
        }
        return true;
    }

    /**
     * Returns all available top data
     *
     * @access public
     * @return array
     */
    public function ReadLatest() {
        $topdata = array();

        // exclusive block
        if ($this->blockMutex()) {
            $topdata = ($this->hasData(self::TOPDATA)) ? $this->getData(self::TOPDATA) : array();
            $this->releaseMutex();
        }
        // end exclusive block

        return $topdata;
    }

    /**
     * Cleans up data collected so far
     *
     * @param boolean   $all        (optional) if set all data independently from the age is removed
     *
     * @access public
     * @return boolean  status
     */
    public function ClearLatest($all = false) {
        // it's ok when doing this every 10 sec
        if ($all == false && time() % 10 != 0 )
            return true;

        $stat = false;

        // exclusive block
        if ($this->blockMutex()) {
            if ($all == true) {
                $topdata = array();
            }
            else {
                $topdata = ($this->hasData(self::TOPDATA)) ? $this->getData(self::TOPDATA) : array();

                $toClear = array();
                foreach ($topdata as $devid=>$users) {
                    foreach ($users as $user=>$pids) {
                        foreach ($pids as $pid=>$line) {
                            // remove everything which terminated for 20 secs or is not updated for more than 120 secs
                            if (($line["ended"] != 0 && time() - $line["ended"] > 20) ||
                                time() - $line["update"] > 120) {
                                $toClear[] = array($devid, $user, $pid);
                            }
                        }
                    }
                }
                foreach ($toClear as $tc) {
                    unset($topdata[$tc[0]][$tc[1]][$tc[2]]);
                }
            }

            $stat = $this->setData($topdata, self::TOPDATA);
            $this->releaseMutex();
        }
        // end exclusive block

        return $stat;
    }

    /**
     * Sets a different UserAgent for this connection
     *
     * @param string    $agent
     *
     * @access public
     * @return boolean
     */
    public function SetUserAgent($agent) {
        $this->latest["devagent"] = $agent;
        return true;
    }

    /**
     * Marks this process as push connection
     *
     * @param string    $agent
     *
     * @access public
     * @return boolean
     */
    public function SetAsPushConnection() {
        $this->latest["push"] = true;
        return true;
    }

    /**
     * Reinitializes the IPC data.
     *
     * @access public
     * @return boolean
     */
    public function ReInitIPC() {
        $status = parent::ReInitIPC();
        if (!status) {
            $this->SetData(array(), self::TOPDATA);
        }
        return $status;
    }

    /**
     * Indicates if top data should be saved or not
     * Returns true for 10 seconds after the latest CollectData()
     * SHOULD only be called with locked mutex!
     *
     * @access private
     * @return boolean
     */
    private function isEnabled() {
        $isEnabled = ($this->hasData(self::ENABLEDAT)) ? $this->getData(self::ENABLEDAT) : false;
        return ($isEnabled !== false && ($isEnabled +300) > time());
    }

    /**
     * Builds an array structure for the top data
     *
     * @param array $topdata    reference to the topdata array
     *
     * @access private
     * @return
     */
    private function checkArrayStructure(&$topdata) {
        if (!isset($topdata) || !is_array($topdata))
            $topdata = array();

        if (!isset($topdata[self::$devid]))
            $topdata[self::$devid] = array();

        if (!isset($topdata[self::$devid][self::$user]))
            $topdata[self::$devid][self::$user] = array();

        if (!isset($topdata[self::$devid][self::$user][self::$pid]))
            $topdata[self::$devid][self::$user][self::$pid] = array();
    }
}
