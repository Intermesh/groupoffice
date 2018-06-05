<?php
/***********************************************
* File      :   syncrecurrence.php
* Project   :   Z-Push
* Descr     :   WBXML appointment reccurence entities
*               that can be parsed directly (as a stream)
*               from WBXML.
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

class SyncRecurrence extends SyncObject {
    public $type;
    public $until;
    public $occurrences;
    public $interval;
    public $dayofweek;
    public $dayofmonth;
    public $weekofmonth;
    public $monthofyear;
    public $calendartype;
    public $firstdayofweek;

    function __construct() {
        $mapping = array (
                    // Recurrence type
                    // 0 = Recurs daily
                    // 1 = Recurs weekly
                    // 2 = Recurs monthly
                    // 3 = Recurs monthly on the nth day
                    // 5 = Recurs yearly
                    // 6 = Recurs yearly on the nth day
                    SYNC_POOMCAL_TYPE                                   => array (  self::STREAMER_VAR      => "type",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_REQUIRED   => self::STREAMER_CHECK_SETZERO,
                                                                                                                        self::STREAMER_CHECK_ONEVALUEOF => array(0,1,2,3,5,6) ),
                                                                                    self::STREAMER_RONOTIFY => true),

                    SYNC_POOMCAL_UNTIL                                  => array (  self::STREAMER_VAR      => "until",
                                                                                    self::STREAMER_TYPE     => self::STREAMER_TYPE_DATE,
                                                                                    self::STREAMER_RONOTIFY => true),

                    SYNC_POOMCAL_OCCURRENCES                            => array (  self::STREAMER_VAR      => "occurrences",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_CMPHIGHER  => 0,
                                                                                                                        self::STREAMER_CHECK_CMPLOWER   => 1000 ),
                                                                                    self::STREAMER_RONOTIFY => true),

                    SYNC_POOMCAL_INTERVAL                               => array (  self::STREAMER_VAR      => "interval",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_CMPHIGHER  => 0,
                                                                                                                        self::STREAMER_CHECK_CMPLOWER   => 1000 ),
                                                                                    self::STREAMER_RONOTIFY => true),

                    // DayOfWeek values
                    //   1 = Sunday
                    //   2 = Monday
                    //   4 = Tuesday
                    //   8 = Wednesday
                    //  16 = Thursday
                    //  32 = Friday
                    //  62 = Weekdays  // not in spec: daily weekday recurrence
                    //  64 = Saturday
                    // 127 = The last day of the month. Value valid only in monthly or yearly recurrences.
                    // As this is a bitmask, actually all values 0 > x < 128 are allowed
                    SYNC_POOMCAL_DAYOFWEEK                              => array (  self::STREAMER_VAR      => "dayofweek",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_CMPHIGHER  => 0,
                                                                                                                        self::STREAMER_CHECK_CMPLOWER   => 128 ),
                                                                                    self::STREAMER_RONOTIFY => true),

                    // DayOfMonth values
                    // 1-31 representing the day
                    SYNC_POOMCAL_DAYOFMONTH                             => array (  self::STREAMER_VAR      => "dayofmonth",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_CMPHIGHER  => 0,
                                                                                                                        self::STREAMER_CHECK_CMPLOWER   => 32 ),
                                                                                    self::STREAMER_RONOTIFY => true),

                    // WeekOfMonth
                    // 1-4 = Y st/nd/rd/th week of month
                    // 5 = last week of month
                    SYNC_POOMCAL_WEEKOFMONTH                            => array (  self::STREAMER_VAR      => "weekofmonth",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(1,2,3,4,5) ),
                                                                                    self::STREAMER_RONOTIFY => true),

                    // MonthOfYear
                    // 1-12 representing the month
                    SYNC_POOMCAL_MONTHOFYEAR                            => array (  self::STREAMER_VAR      => "monthofyear",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(1,2,3,4,5,6,7,8,9,10,11,12) ),
                                                                                    self::STREAMER_RONOTIFY => true),
                );

        if(Request::GetProtocolVersion() >= 14.0) {
            $mapping[SYNC_POOMCAL_CALENDARTYPE]                         = array (   self::STREAMER_VAR      => "calendartype",
                                                                                    self::STREAMER_RONOTIFY => true);
        }

        if(Request::GetProtocolVersion() >= 14.1) {
            // First day of the calendar week for recurrence.
            // FirstDayOfWeek values:
            //   0 = Sunday
            //   1 = Monday
            //   2 = Tuesday
            //   3 = Wednesday
            //   4 = Thursday
            //   5 = Friday
            //   6 = Saturday
            $mapping[SYNC_POOMCAL_FIRSTDAYOFWEEK]                       = array (   self::STREAMER_VAR      => "firstdayofweek",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(0,1,2,3,4,5,6) ),
                                                                                    self::STREAMER_RONOTIFY => true);
        }

        parent::__construct($mapping);

        // Indicates that this SyncObject supports the private flag and stripping of private data.
        // There is nothing concrete to be stripped here, but as it's part of an appointment it supports it.
        $this->supportsPrivateStripping = true;
    }
}
