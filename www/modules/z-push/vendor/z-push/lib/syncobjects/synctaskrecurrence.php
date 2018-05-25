<?php
/***********************************************
* File      :   synctaskrecurrence.php
* Project   :   Z-Push
* Descr     :   WBXML tasl recurrence entities that can
*               be parsed directly (as a stream) from WBXML.
*               It is automatically decoded
*               according to $mapping,
*               and the Sync WBXML mappings.
*
* Created   :   05.09.2011
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

// Exactly the same as SyncRecurrence, but then with SYNC_POOMTASKS_*
class SyncTaskRecurrence extends SyncObject {
    public $start;
    public $type;
    public $until;
    public $occurrences;
    public $interval;
    public $dayofweek;
    public $dayofmonth;
    public $weekofmonth;
    public $monthofyear;
    public $regenerate;
    public $deadoccur;
    public $calendartype;

    function __construct() {
        $mapping = array (
                    SYNC_POOMTASKS_START                                => array (  self::STREAMER_VAR      => "start",
                                                                                    self::STREAMER_TYPE     => self::STREAMER_TYPE_DATE,
                                                                                    self::STREAMER_RONOTIFY => true),

                    // Recurrence type
                    // 0 = Recurs daily
                    // 1 = Recurs weekly
                    // 2 = Recurs monthly
                    // 3 = Recurs monthly on the nth day
                    // 5 = Recurs yearly
                    // 6 = Recurs yearly on the nth day
                    SYNC_POOMTASKS_TYPE                                 => array (  self::STREAMER_VAR      => "type",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_REQUIRED   => self::STREAMER_CHECK_SETZERO,
                                                                                                                        self::STREAMER_CHECK_ONEVALUEOF => array(0,1,2,3,5,6) ),
                                                                                    self::STREAMER_RONOTIFY => true),

                    SYNC_POOMTASKS_UNTIL                                => array (  self::STREAMER_VAR      => "until",
                                                                                    self::STREAMER_TYPE     => self::STREAMER_TYPE_DATE,
                                                                                    self::STREAMER_RONOTIFY => true),

                    SYNC_POOMTASKS_OCCURRENCES                          => array (  self::STREAMER_VAR      => "occurrences",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_CMPHIGHER  => 0,
                                                                                                                        self::STREAMER_CHECK_CMPLOWER   => 1000 ),
                                                                                    self::STREAMER_RONOTIFY => true),

                    SYNC_POOMTASKS_INTERVAL                             => array (  self::STREAMER_VAR      => "interval",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_CMPHIGHER  => 0,
                                                                                                                        self::STREAMER_CHECK_CMPLOWER   => 1000 ),
                                                                                    self::STREAMER_RONOTIFY => true),

                    //TODO: check iOS5 sends deadoccur inside of the recurrence
                    SYNC_POOMTASKS_DEADOCCUR                            => array (  self::STREAMER_VAR      => "deadoccur",
                                                                                    self::STREAMER_RONOTIFY => true),
                    SYNC_POOMTASKS_REGENERATE                           => array (  self::STREAMER_VAR      => "regenerate",
                                                                                    self::STREAMER_RONOTIFY => true),

                    // DayOfWeek values
                    //   1 = Sunday
                    //   2 = Monday
                    //   4 = Tuesday
                    //   8 = Wednesday
                    //  16 = Thursday
                    //  32 = Friday
                    //  62 = Weekdays  // TODO check: value set by WA with daily weekday recurrence
                    //  64 = Saturday
                    // 127 = The last day of the month. Value valid only in monthly or yearly recurrences.
                    SYNC_POOMTASKS_DAYOFWEEK                            => array (  self::STREAMER_VAR      => "dayofweek",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_CMPHIGHER  => 0,
                                                                                                                        self::STREAMER_CHECK_CMPLOWER   => 128 ),
                                                                                    self::STREAMER_RONOTIFY => true),

                    // DayOfMonth values
                    // 1-31 representing the day
                    SYNC_POOMTASKS_DAYOFMONTH                           => array (  self::STREAMER_VAR      => "dayofmonth",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_CMPHIGHER  => 0,
                                                                                                                        self::STREAMER_CHECK_CMPLOWER   => 32 ),
                                                                                    self::STREAMER_RONOTIFY => true),

                    // WeekOfMonth
                    // 1-4 = Y st/nd/rd/th week of month
                    // 5 = last week of month
                    SYNC_POOMTASKS_WEEKOFMONTH                          => array (  self::STREAMER_VAR      => "weekofmonth",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(1,2,3,4,5) ),
                                                                                    self::STREAMER_RONOTIFY => true),

                    // MonthOfYear
                    // 1-12 representing the month
                    SYNC_POOMTASKS_MONTHOFYEAR                          => array (  self::STREAMER_VAR      => "monthofyear",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(1,2,3,4,5,6,7,8,9,10,11,12) ),
                                                                                    self::STREAMER_RONOTIFY => true),
                );

        if(Request::GetProtocolVersion() >= 14.0) {
            $mapping[SYNC_POOMTASKS_CALENDARTYPE]                       = array (   self::STREAMER_VAR      => "calendartype",
                                                                                    self::STREAMER_RONOTIFY => true);
        }

        parent::__construct($mapping);
    }

    /**
     * Method checks if the object has the minimum of required parameters
     * and fullfills semantic dependencies
     *
     * This overloads the general check() with special checks to be executed
     *
     * @param boolean   $logAsDebug     (opt) default is false, so messages are logged in WARN log level
     *
     * @access public
     * @return boolean
     */
    public function Check($logAsDebug = false) {
        $ret = parent::Check($logAsDebug);

        // semantic checks general "turn off switch"
        if (defined("DO_SEMANTIC_CHECKS") && DO_SEMANTIC_CHECKS === false)
            return $ret;

        if (!$ret)
            return false;

        if (isset($this->start) && isset($this->until) && $this->until < $this->start) {
            ZLog::Write(LOGLEVEL_WARN, sprintf("SyncObject->Check(): Unmet condition in object from type %s: parameter 'start' is HIGHER than 'until'. Check failed!", get_class($this) ));
            return false;
        }

        return true;
    }
}
