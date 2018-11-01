<?php
/*
 * Copyright 2005 - 2016  Zarafa B.V. and its licensors
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
 */

class FreeBusyPublish {

    var $session;
    var $calendar;
    var $entryid;
    var $starttime;
    var $length;
    var $store;
    var $proptags;

    /**
     * Constuctor
     *
     * @param mapi_session $session MAPI Session
     * @param mapi_folder $calendar Calendar to publish
     * @param string $entryid AddressBook Entry ID for the user we're publishing for
     */


    function __construct($session, $store, $calendar, $entryid)
    {
        $properties["entryid"] = PR_ENTRYID;
        $properties["parent_entryid"] = PR_PARENT_ENTRYID;
        $properties["message_class"] = PR_MESSAGE_CLASS;
        $properties["icon_index"] = PR_ICON_INDEX;
        $properties["subject"] = PR_SUBJECT;
        $properties["display_to"] = PR_DISPLAY_TO;
        $properties["importance"] = PR_IMPORTANCE;
        $properties["sensitivity"] = PR_SENSITIVITY;
        $properties["startdate"] = "PT_SYSTIME:PSETID_Appointment:0x820d";
        $properties["duedate"] = "PT_SYSTIME:PSETID_Appointment:0x820e";
        $properties["recurring"] = "PT_BOOLEAN:PSETID_Appointment:0x8223";
        $properties["recurring_data"] = "PT_BINARY:PSETID_Appointment:0x8216";
        $properties["busystatus"] = "PT_LONG:PSETID_Appointment:0x8205";
        $properties["label"] = "PT_LONG:PSETID_Appointment:0x8214";
        $properties["alldayevent"] = "PT_BOOLEAN:PSETID_Appointment:0x8215";
        $properties["private"] = "PT_BOOLEAN:PSETID_Common:0x8506";
        $properties["meeting"] = "PT_LONG:PSETID_Appointment:0x8217";
        $properties["startdate_recurring"] = "PT_SYSTIME:PSETID_Appointment:0x8235";
        $properties["enddate_recurring"] = "PT_SYSTIME:PSETID_Appointment:0x8236";
        $properties["location"] = "PT_STRING8:PSETID_Appointment:0x8208";
        $properties["duration"] = "PT_LONG:PSETID_Appointment:0x8213";
        $properties["responsestatus"] = "PT_LONG:PSETID_Appointment:0x8218";
        $properties["reminder"] = "PT_BOOLEAN:PSETID_Common:0x8503";
        $properties["reminder_minutes"] = "PT_LONG:PSETID_Common:0x8501";
        $properties["contacts"] = "PT_MV_STRING8:PSETID_Common:0x853a";
        $properties["contacts_string"] = "PT_STRING8:PSETID_Common:0x8586";
        $properties["categories"] = "PT_MV_STRING8:PS_PUBLIC_STRINGS:Keywords";
        $properties["reminder_time"] = "PT_SYSTIME:PSETID_Common:0x8502";
        $properties["commonstart"] = "PT_SYSTIME:PSETID_Common:0x8516";
        $properties["commonend"] = "PT_SYSTIME:PSETID_Common:0x8517";
        $properties["basedate"] = "PT_SYSTIME:PSETID_Appointment:0x8228";
        $properties["timezone_data"] = "PT_BINARY:PSETID_Appointment:0x8233";
        $this->proptags = getPropIdsFromStrings($store, $properties);

        $this->session = $session;
        $this->calendar = $calendar;
        $this->entryid = $entryid;
        $this->store = $store;
    }

    /**
     * Function is used to get the calender data based on give date range.
     *
     * @param timestamp $starttime Time from which to get the calender data.
     * @param timestamp $length Time up till now get the calender data.
     * @return Array return the calender data array.
     */
    function getCalendarData($starttime, $length)
    {
        $start = $starttime;
        $end = $length;

        // Get all the items in the calendar that we need

        $calendaritems = Array();

        $restrict = Array(RES_OR,
                             Array(
                                   // OR
                                   // (item[start] >= start && item[start] <= end)
                                   Array(RES_AND,
                                         Array(
                                               Array(RES_PROPERTY,
                                                     Array(RELOP => RELOP_GE,
                                                           ULPROPTAG => $this->proptags["startdate"],
                                                           VALUE => $start
                                                           )
                                                     ),
                                               Array(RES_PROPERTY,
                                                     Array(RELOP => RELOP_LE,
                                                           ULPROPTAG => $this->proptags["startdate"],
                                                           VALUE => $end
                                                           )
                                                     )
                                               )
                                         ),
                                   // OR
                                   // (item[end]   >= start && item[end]   <= end)
                                   Array(RES_AND,
                                         Array(
                                               Array(RES_PROPERTY,
                                                     Array(RELOP => RELOP_GE,
                                                           ULPROPTAG => $this->proptags["duedate"],
                                                           VALUE => $start
                                                           )
                                                     ),
                                               Array(RES_PROPERTY,
                                                     Array(RELOP => RELOP_LE,
                                                           ULPROPTAG => $this->proptags["duedate"],
                                                           VALUE => $end
                                                           )
                                                     )
                                               )
                                         ),
                                   // OR
                                   // (item[start] <  start && item[end]   >  end)
                                   Array(RES_AND,
                                         Array(
                                               Array(RES_PROPERTY,
                                                     Array(RELOP => RELOP_LT,
                                                           ULPROPTAG => $this->proptags["startdate"],
                                                           VALUE => $start
                                                           )
                                                     ),
                                               Array(RES_PROPERTY,
                                                     Array(RELOP => RELOP_GT,
                                                           ULPROPTAG => $this->proptags["duedate"],
                                                           VALUE => $end
                                                           )
                                                     )
                                               )
                                         ),
                                   // OR
                                   Array(RES_OR,
                                         Array(
                                               // OR
                                               // (EXIST(ecurrence_enddate_property) && item[isRecurring] == true && item[end] >= start)
                                               Array(RES_AND,
                                                     Array(
                                                           Array(RES_EXIST,
                                                                 Array(ULPROPTAG => $this->proptags["enddate_recurring"],
                                                                       )
                                                                 ),
                                                           Array(RES_PROPERTY,
                                                                 Array(RELOP => RELOP_EQ,
                                                                       ULPROPTAG => $this->proptags["recurring"],
                                                                       VALUE => true
                                                                       )
                                                                 ),
                                                           Array(RES_PROPERTY,
                                                                 Array(RELOP => RELOP_GE,
                                                                       ULPROPTAG => $this->proptags["enddate_recurring"],
                                                                       VALUE => $start
                                                                       )
                                                                 )
                                                           )
                                                     ),
                                               // OR
                                               // (!EXIST(ecurrence_enddate_property) && item[isRecurring] == true && item[start] <= end)
                                               Array(RES_AND,
                                                     Array(
                                                           Array(RES_NOT,
                                                                 Array(
                                                                       Array(RES_EXIST,
                                                                             Array(ULPROPTAG => $this->proptags["enddate_recurring"]
                                                                                   )
                                                                             )
                                                                       )
                                                                 ),
                                                           Array(RES_PROPERTY,
                                                                 Array(RELOP => RELOP_LE,
                                                                       ULPROPTAG => $this->proptags["startdate"],
                                                                       VALUE => $end
                                                                       )
                                                                 ),
                                                           Array(RES_PROPERTY,
                                                                 Array(RELOP => RELOP_EQ,
                                                                       ULPROPTAG => $this->proptags["recurring"],
                                                                       VALUE => true
                                                                       )
                                                                 )
                                                           )
                                                     )
                                               )
                                         ) // EXISTS OR
                                   )
                             );        // global OR

        $contents = mapi_folder_getcontentstable($this->calendar);
        mapi_table_restrict($contents, $restrict);

        while(1) {
            $rows = mapi_table_queryrows($contents, array_values($this->proptags), 0, 50);

            if(!is_array($rows))
                break;

            if(empty($rows))
                break;

            foreach ($rows as $row) {
                $occurrences = Array();
                if(isset($row[$this->proptags['recurring']]) && $row[$this->proptags['recurring']]) {
                    $recur = new Recurrence($this->store, $row);

                    $occurrences = $recur->getItems($starttime, $length);
                } else {
                    $occurrences[] = $row;
                }

                $calendaritems = array_merge($calendaritems, $occurrences);
            }
        }

        // $calendaritems now contains all the calendar items in the specified time
        // frame. We now need to merge these into a flat array of begin/end/status
        // objects. This also filters out all the 'free' items (status 0)
        $freebusy = $this->mergeItemsFB($calendaritems);

        return $freebusy;
    }

    /**
     * Publishes Free/Busy infomation of user.
     * @param timestamp $starttime Time from which to publish data  (usually now)
     * @param timestamp $length Time of seconds from $starttime we should publish
     */
    function publishFB($start, $end)
    {
        $freebusy = $this->getCalendarData($start, $end);

        // Get the FB interface
        try {
            $fbsupport = mapi_freebusysupport_open($this->session, $this->store);
        } catch (MAPIException $e) {
            if($e->getCode() == MAPI_E_NOT_FOUND) {
                $e->setHandled();
                ZLog::Write(LOGLEVEL_WARN, "Error in opening freebusysupport object.");
            }
        }

        // Open updater for this user
        if(isset($fbsupport) && $fbsupport) {
            $updaters = mapi_freebusysupport_loadupdate($fbsupport, Array($this->entryid));

            $updater = $updaters[0];

            // Send the data
            mapi_freebusyupdate_reset($updater);
            mapi_freebusyupdate_publish($updater, $freebusy);
            mapi_freebusyupdate_savechanges($updater, $start-24*60*60, $end);

            // We're finished
            mapi_freebusysupport_close($fbsupport);
        }
        else
            ZLog::Write(LOGLEVEL_WARN, "FreeBusyPublish is not available");
    }

    /**
    * Sorts by timestamp, if equal, then end before start
    */
    function cmp($a, $b)
    {
        if ($a["time"] == $b["time"]) {
            if($a["type"] < $b["type"])
                return 1;
            if($a["type"] > $b["type"])
                return -1;
            return 0;
        }
        return ($a["time"] > $b["time"] ? 1 : -1);
    }

    /**
    * Function mergeItems
    * @author Steve Hardy
    */
    function mergeItemsFB($items)
    {
        $merged = Array();
        $timestamps = Array();
        $csubj = Array();
        $cbusy = Array();
        $level = 0;
        $laststart = null;

        foreach($items as $item)
        {
            $ts["type"] = 0;
            $ts["time"] = $item[$this->proptags["startdate"]];
            $ts["subject"] = $item[PR_SUBJECT];
            $ts["status"] = (isset($item[$this->proptags["busystatus"]])) ? $item[$this->proptags["busystatus"]] : fbFree; //ZP-197
            $timestamps[] = $ts;

            $ts["type"] = 1;
            $ts["time"] = $item[$this->proptags["duedate"]];
            $ts["subject"] = $item[PR_SUBJECT];
            $ts["status"] = (isset($item[$this->proptags["busystatus"]])) ? $item[$this->proptags["busystatus"]] : fbFree; //ZP-197
            $timestamps[] = $ts;
        }

        usort($timestamps, Array($this, "cmp"));
        $laststart = 0; // seb added

        foreach($timestamps as $ts)
        {
            switch ($ts["type"])
            {
                case 0: // Start
                    if ($level != 0 && $laststart != $ts["time"])
                    {
                        $newitem["start"] = $laststart;
                        $newitem["end"] = $ts["time"];
                        $newitem["subject"] = join(",", $csubj);
                        $newitem["status"] = !empty($cbusy) ? max($cbusy) : 0;
                        if($newitem["status"] > 0)
                            $merged[] = $newitem;
                    }

                    $level++;

                    $csubj[] = $ts["subject"];
                    $cbusy[] = $ts["status"];

                    $laststart = $ts["time"];
                    break;
                case 1: // End
                    if ($laststart != $ts["time"])
                    {
                        $newitem["start"] = $laststart;
                        $newitem["end"] = $ts["time"];
                        $newitem["subject"] = join(",", $csubj);
                        $newitem["status"] = !empty($cbusy) ? max($cbusy) : 0;
                        if($newitem["status"] > 0)
                            $merged[] = $newitem;
                    }

                    $level--;

                    array_splice($csubj, array_search($ts["subject"], $csubj, 1), 1);
                    array_splice($cbusy, array_search($ts["status"], $cbusy, 1), 1);

                    $laststart = $ts["time"];
                    break;
            }
        }

        return $merged;
    }

}
