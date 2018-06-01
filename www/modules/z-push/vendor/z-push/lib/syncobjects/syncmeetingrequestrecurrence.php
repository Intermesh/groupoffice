<?php
/***********************************************
* File      :   syncmeetingrequestrecurrence.php
* Project   :   Z-Push
* Descr     :   WBXML meeting request recurrence entities
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

class SyncMeetingRequestRecurrence extends SyncObject {
    public $type;
    public $until;
    public $occurrences;
    public $interval;
    public $dayofweek;
    public $dayofmonth;
    public $weekofmonth;
    public $monthofyear;
    public $calendartype;

    function __construct() {
        $mapping = array (
                    // Recurrence type
                    // 0 = Recurs daily
                    // 1 = Recurs weekly
                    // 2 = Recurs monthly
                    // 3 = Recurs monthly on the nth day
                    // 5 = Recurs yearly
                    // 6 = Recurs yearly on the nth day
                    SYNC_POOMMAIL_TYPE                                  => array (  self::STREAMER_VAR      => "type",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_REQUIRED   => self::STREAMER_CHECK_SETZERO,
                                                                                                                        self::STREAMER_CHECK_ONEVALUEOF => array(0,1,2,3,5,6) )),

                    SYNC_POOMMAIL_UNTIL                                 => array (  self::STREAMER_VAR      => "until",
                                                                                    self::STREAMER_TYPE     => self::STREAMER_TYPE_DATE),

                    SYNC_POOMMAIL_OCCURRENCES                           => array (  self::STREAMER_VAR      => "occurrences",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_CMPHIGHER  => 0,
                                                                                                                        self::STREAMER_CHECK_CMPLOWER   => 1000 )),

                    SYNC_POOMMAIL_INTERVAL                              => array (  self::STREAMER_VAR      => "interval",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_CMPHIGHER  => 0,
                                                                                                                        self::STREAMER_CHECK_CMPLOWER   => 1000 )),

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
                    SYNC_POOMMAIL_DAYOFWEEK                             => array (  self::STREAMER_VAR      => "dayofweek",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_CMPHIGHER  => 0,
                                                                                                                        self::STREAMER_CHECK_CMPLOWER   => 128 )),

                    // DayOfMonth values
                    // 1-31 representing the day
                    SYNC_POOMMAIL_DAYOFMONTH                            => array (  self::STREAMER_VAR      => "dayofmonth",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_CMPHIGHER  => 0,
                                                                                                                        self::STREAMER_CHECK_CMPLOWER   => 32 )),

                    // WeekOfMonth
                    // 1-4 = Y st/nd/rd/th week of month
                    // 5 = last week of month
                    SYNC_POOMMAIL_WEEKOFMONTH                           => array (  self::STREAMER_VAR      => "weekofmonth",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(1,2,3,4,5) )),

                    // MonthOfYear
                    // 1-12 representing the month
                    SYNC_POOMMAIL_MONTHOFYEAR                           => array (  self::STREAMER_VAR      => "monthofyear",
                                                                                    self::STREAMER_CHECKS   => array(   self::STREAMER_CHECK_ONEVALUEOF => array(1,2,3,4,5,6,7,8,9,10,11,12) )),
                );

        if(Request::GetProtocolVersion() >= 14.0) {
            $mapping[SYNC_POOMMAIL2_CALENDARTYPE]                       = array (   self::STREAMER_VAR      => "calendartype");
        }

        parent::__construct($mapping);
    }
}
