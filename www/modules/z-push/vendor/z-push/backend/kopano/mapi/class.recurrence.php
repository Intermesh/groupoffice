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

    /**
     * Recurrence
     */
    class Recurrence extends BaseRecurrence
    {
        /*
         * ABOUT TIMEZONES
         *
         * Timezones are rather complicated here so here are some rules to think about:
         *
         * - Timestamps in mapi-like properties (so in PT_SYSTIME properties) are always in GMT (including
         *   the 'basedate' property in exceptions !!)
         * - Timestamps for recurrence (so start/end of recurrence, and basedates for exceptions (everything
         *   outside the 'basedate' property in the exception !!), and start/endtimes for exceptions) are
         *   always in LOCAL time.
         */

        // All properties for a recipient that are interesting
        var $recipprops = Array(
            PR_ENTRYID,
            PR_SEARCH_KEY,
            PR_DISPLAY_NAME,
            PR_EMAIL_ADDRESS,
            PR_RECIPIENT_ENTRYID,
            PR_RECIPIENT_TYPE,
            PR_SEND_INTERNET_ENCODING,
            PR_SEND_RICH_INFO,
            PR_RECIPIENT_DISPLAY_NAME,
            PR_ADDRTYPE,
            PR_DISPLAY_TYPE,
            PR_DISPLAY_TYPE_EX,
            PR_RECIPIENT_TRACKSTATUS,
            PR_RECIPIENT_TRACKSTATUS_TIME,
            PR_RECIPIENT_FLAGS,
            PR_ROWID
        );

        /**
         * Constructor
         * @param resource $store MAPI Message Store Object
         * @param resource $message the MAPI (appointment) message
         * @param array    $proptags an associative array of protags and their values.
         */
        function __construct($store, $message, $proptags = [])
        {

            if ($proptags) {
                $this->proptags = $proptags;
            } else {
                $properties = array();
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
                $properties["recurring_pattern"] = "PT_STRING8:PSETID_Appointment:0x8232";
                $properties["location"] = "PT_STRING8:PSETID_Appointment:0x8208";
                $properties["duration"] = "PT_LONG:PSETID_Appointment:0x8213";
                $properties["responsestatus"] = "PT_LONG:PSETID_Appointment:0x8218";
                $properties["reminder"] = "PT_BOOLEAN:PSETID_Common:0x8503";
                $properties["reminder_minutes"] = "PT_LONG:PSETID_Common:0x8501";
                $properties["recurrencetype"] = "PT_LONG:PSETID_Appointment:0x8231";
                $properties["contacts"] = "PT_MV_STRING8:PSETID_Common:0x853a";
                $properties["contacts_string"] = "PT_STRING8:PSETID_Common:0x8586";
                $properties["categories"] = "PT_MV_STRING8:PS_PUBLIC_STRINGS:Keywords";
                $properties["reminder_time"] = "PT_SYSTIME:PSETID_Common:0x8502";
                $properties["commonstart"] = "PT_SYSTIME:PSETID_Common:0x8516";
                $properties["commonend"] = "PT_SYSTIME:PSETID_Common:0x8517";
                $properties["basedate"] = "PT_SYSTIME:PSETID_Appointment:0x8228";
                $properties["timezone_data"] = "PT_BINARY:PSETID_Appointment:0x8233";
                $properties["timezone"] = "PT_STRING8:PSETID_Appointment:0x8234";
                $properties["flagdueby"] = "PT_SYSTIME:PSETID_Common:0x8560";
                $properties["side_effects"] = "PT_LONG:PSETID_Common:0x8510";
                $properties["hideattachments"] = "PT_BOOLEAN:PSETID_Common:0x8514";

                $this->proptags = getPropIdsFromStrings($store, $properties);
            }

            parent::__construct($store, $message);
        }

        /**
         * Create an exception
         * @param array $exception_props the exception properties (same properties as normal recurring items)
         * @param date $base_date the base date of the exception (LOCAL time of non-exception occurrence)
         * @param boolean $delete true - delete occurrence, false - create new exception or modify existing
         * @param array $exception_recips true - delete occurrence, false - create new exception or modify existing
         * @param mapi_message $copy_attach_from mapi message from which attachments should be copied
         */
        function createException($exception_props, $base_date, $delete = false, $exception_recips = array(), $copy_attach_from = false)
        {
            $baseday = $this->dayStartOf($base_date);
            $basetime = $baseday + $this->recur["startocc"] * 60;

            // Remove any pre-existing exception on this base date
            if($this->isException($baseday)) {
                $this->deleteException($baseday); // note that deleting an exception is different from creating a deleted exception (deleting an occurrence).
            }

            if(!$delete) {
                if(isset($exception_props[$this->proptags["startdate"]]) && !$this->isValidExceptionDate($base_date, $this->fromGMT($this->tz, $exception_props[$this->proptags["startdate"]]))) {
                    return false;
                }
                // Properties in the attachment are the properties of the base object, plus $exception_props plus the base date
                foreach (array("subject", "location", "label", "reminder", "reminder_minutes", "alldayevent", "busystatus") as $propname) {
                    if(isset($this->messageprops[$this->proptags[$propname]]))
                        $props[$this->proptags[$propname]] = $this->messageprops[$this->proptags[$propname]];
                }

                $props[PR_MESSAGE_CLASS] = "IPM.OLE.CLASS.{00061055-0000-0000-C000-000000000046}";
                unset($exception_props[PR_MESSAGE_CLASS]);
                unset($exception_props[PR_ICON_INDEX]);
                $props = $exception_props + $props;

                // Basedate in the exception attachment is the GMT time at which the original occurrence would have been
                $props[$this->proptags["basedate"]] = $this->toGMT($this->tz, $basetime);

                if (!isset($exception_props[$this->proptags["startdate"]])) {
                    $props[$this->proptags["startdate"]] = $this->getOccurrenceStart($base_date);
                }

                if (!isset($exception_props[$this->proptags["duedate"]])) {
                    $props[$this->proptags["duedate"]] = $this->getOccurrenceEnd($base_date);
                }

                // synchronize commonstart/commonend with startdate/duedate
                if(isset($props[$this->proptags["startdate"]])) {
                    $props[$this->proptags["commonstart"]] = $props[$this->proptags["startdate"]];
                }

                if(isset($props[$this->proptags["duedate"]])) {
                    $props[$this->proptags["commonend"]] = $props[$this->proptags["duedate"]];
                }

                // Save the data into an attachment
                $this->createExceptionAttachment($props, $exception_recips, $copy_attach_from);

                $changed_item = array();

                $changed_item["basedate"] = $baseday;
                $changed_item["start"] = $this->fromGMT($this->tz, $props[$this->proptags["startdate"]]);
                $changed_item["end"] = $this->fromGMT($this->tz, $props[$this->proptags["duedate"]]);

                if(array_key_exists($this->proptags["subject"], $exception_props)) {
                    $changed_item["subject"] = $exception_props[$this->proptags["subject"]];
                }

                if(array_key_exists($this->proptags["location"], $exception_props)) {
                    $changed_item["location"] = $exception_props[$this->proptags["location"]];
                }

                if(array_key_exists($this->proptags["label"], $exception_props)) {
                    $changed_item["label"] = $exception_props[$this->proptags["label"]];
                }

                if(array_key_exists($this->proptags["reminder"], $exception_props)) {
                    $changed_item["reminder_set"] = $exception_props[$this->proptags["reminder"]];
                }

                if(array_key_exists($this->proptags["reminder_minutes"], $exception_props)) {
                    $changed_item["remind_before"] = $exception_props[$this->proptags["reminder_minutes"]];
                }

                if(array_key_exists($this->proptags["alldayevent"], $exception_props)) {
                    $changed_item["alldayevent"] = $exception_props[$this->proptags["alldayevent"]];
                }

                if(array_key_exists($this->proptags["busystatus"], $exception_props)) {
                    $changed_item["busystatus"] = $exception_props[$this->proptags["busystatus"]];
                }

                // Add the changed occurrence to the list
                array_push($this->recur["changed_occurences"], $changed_item);
            } else {
                // Delete the occurrence by placing it in the deleted occurrences list
                array_push($this->recur["deleted_occurences"], $baseday);
            }

            // Turn on hideattachments, because the attachments in this item are the exceptions
            mapi_setprops($this->message, array ( $this->proptags["hideattachments"] => true ));

            // Save recurrence data to message
            $this->saveRecurrence();

            return true;
        }

        /**
         * Modifies an existing exception, but only updates the given properties
         * NOTE: You can't remove properites from an exception, only add new ones
         */
        function modifyException($exception_props, $base_date, $exception_recips = array(), $copy_attach_from = false)
        {
            if(isset($exception_props[$this->proptags["startdate"]]) && !$this->isValidExceptionDate($base_date, $this->fromGMT($this->tz, $exception_props[$this->proptags["startdate"]]))) {
                return false;
            }

            $baseday = $this->dayStartOf($base_date);
            $extomodify = false;

            for($i = 0, $len = count($this->recur["changed_occurences"]); $i < $len; $i++) {
                if($this->isSameDay($this->recur["changed_occurences"][$i]["basedate"], $baseday))
                    $extomodify = &$this->recur["changed_occurences"][$i];
            }

            if(!$extomodify)
                return false;

            // remove basedate property as we want to preserve the old value
            // client will send basedate with time part as zero, so discard that value
            unset($exception_props[$this->proptags["basedate"]]);

            if(array_key_exists($this->proptags["startdate"], $exception_props)) {
                $extomodify["start"] = $this->fromGMT($this->tz, $exception_props[$this->proptags["startdate"]]);
            }

            if(array_key_exists($this->proptags["duedate"], $exception_props)) {
                $extomodify["end"] =   $this->fromGMT($this->tz, $exception_props[$this->proptags["duedate"]]);
            }

            if(array_key_exists($this->proptags["subject"], $exception_props)) {
                $extomodify["subject"] = $exception_props[$this->proptags["subject"]];
            }

            if(array_key_exists($this->proptags["location"], $exception_props)) {
                $extomodify["location"] = $exception_props[$this->proptags["location"]];
            }

            if(array_key_exists($this->proptags["label"], $exception_props)) {
                $extomodify["label"] = $exception_props[$this->proptags["label"]];
            }

            if(array_key_exists($this->proptags["reminder"], $exception_props)) {
                $extomodify["reminder_set"] = $exception_props[$this->proptags["reminder"]];
            }

            if(array_key_exists($this->proptags["reminder_minutes"], $exception_props)) {
                $extomodify["remind_before"] = $exception_props[$this->proptags["reminder_minutes"]];
            }

            if(array_key_exists($this->proptags["alldayevent"], $exception_props)) {
                $extomodify["alldayevent"] = $exception_props[$this->proptags["alldayevent"]];
            }

            if(array_key_exists($this->proptags["busystatus"], $exception_props)) {
                $extomodify["busystatus"] = $exception_props[$this->proptags["busystatus"]];
            }

            $exception_props[PR_MESSAGE_CLASS] = "IPM.OLE.CLASS.{00061055-0000-0000-C000-000000000046}";

            // synchronize commonstart/commonend with startdate/duedate
            if(isset($exception_props[$this->proptags["startdate"]])) {
                $exception_props[$this->proptags["commonstart"]] = $exception_props[$this->proptags["startdate"]];
            }

            if(isset($exception_props[$this->proptags["duedate"]])) {
                $exception_props[$this->proptags["commonend"]] = $exception_props[$this->proptags["duedate"]];
            }

            $attach = $this->getExceptionAttachment($baseday);
            if(!$attach) {
                if ($copy_attach_from) {
                    $this->deleteExceptionAttachment($base_date);
                    $this->createException($exception_props, $base_date, false, $exception_recips, $copy_attach_from);
                } else {
                    $this->createExceptionAttachment($exception_props, $exception_recips, $copy_attach_from);
                }
            } else {
                $message = mapi_attach_openobj($attach, MAPI_MODIFY);

                // Set exception properties on embedded message and save
                mapi_setprops($message, $exception_props);
                $this->setExceptionRecipients($message, $exception_recips, false);
                mapi_savechanges($message);

                // If a new start or duedate is provided, we update the properties 'PR_EXCEPTION_STARTTIME' and 'PR_EXCEPTION_ENDTIME'
                // on the attachment which holds the embedded msg and save everything.
                $props = array();
                if (isset($exception_props[$this->proptags["startdate"]])) {
                    $props[PR_EXCEPTION_STARTTIME] = $this->fromGMT($this->tz, $exception_props[$this->proptags["startdate"]]);
                }
                if (isset($exception_props[$this->proptags["duedate"]])) {
                    $props[PR_EXCEPTION_ENDTIME] = $this->fromGMT($this->tz, $exception_props[$this->proptags["duedate"]]);
                }
                if (!empty($props)) {
                    mapi_setprops($attach, $props);
                }

                mapi_savechanges($attach);
            }

            // Save recurrence data to message
            $this->saveRecurrence();

            return true;
        }

        // Checks to see if the following is true:
        // 1) The exception to be created doesn't create two exceptions starting on one day (however, they can END on the same day by modifying duration)
        // 2) The exception to be created doesn't 'jump' over another occurrence (which may be an exception itself!)
        //
        // Both $basedate and $start are in LOCAL time
        function isValidExceptionDate($basedate, $start)
        {
            // The way we do this is to look at the days that we're 'moving' the item in the exception. Each
            // of these days may only contain the item that we're modifying. Any other item violates the rules.

            if($this->isException($basedate)) {
                // If we're modifying an exception, we want to look at the days that we're 'moving' compared to where
                // the exception used to be.
                $oldexception = $this->getChangeException($basedate);
                $prevday = $this->dayStartOf($oldexception["start"]);
            } else {
                // If its a new exception, we want to look at the original placement of this item.
                $prevday = $basedate;
            }

            $startday = $this->dayStartOf($start);

            // Get all the occurrences on the days between the basedate (may be reversed)
            if($prevday < $startday)
                $items = $this->getItems($this->toGMT($this->tz, $prevday), $this->toGMT($this->tz, $startday + 24 * 60 * 60));
            else
                $items = $this->getItems($this->toGMT($this->tz, $startday), $this->toGMT($this->tz, $prevday + 24 * 60 * 60));

            // There should now be exactly one item, namely the item that we are modifying. If there are any other items in the range,
            // then we abort the change, since one of the rules has been violated.
            return count($items) == 1;
        }

        /**
         * Check to see if the exception proposed at a certain basedate is allowed concerning reminder times:
         *
         * Both must be true:
         * - reminder time of this item is not before the starttime of the previous recurring item
         * - reminder time of the next item is not before the starttime of this item
         *
         * @param date $basedate the base date of the exception (LOCAL time of non-exception occurrence)
         * @param string $reminderminutes reminder minutes which is set of the item
         * @param date $startdate the startdate of the selected item
         * @returns boolean if the reminder minutes value valid (FALSE if either of the rules above are FALSE)
         */
        function isValidReminderTime($basedate, $reminderminutes, $startdate)
        {
            // get all occurence items before the seleceted items occurence starttime
            $occitems = $this->getItems($this->messageprops[$this->proptags["startdate"]], $this->toGMT($this->tz, $basedate));

            if(!empty($occitems)) {
                // as occitems array is sorted in ascending order of startdate, to get the previous occurence we take the last items in occitems .
                $previousitem_startdate = $occitems[count($occitems) - 1][$this->proptags["startdate"]];

                // if our reminder is set before or equal to the beginning of the previous occurrence, then that's not allowed
                if($startdate - ($reminderminutes*60) <= $previousitem_startdate)
                    return false;
            }

            // Get the endtime of the current occurrence and find the next two occurrences (including the current occurrence)
            $currentOcc = $this->getItems($this->toGMT($this->tz, $basedate), 0x7ff00000, 2, true);

            // If there are another two occurrences, then the first is the current occurrence, and the one after that
            // is the next occurrence.
            if(count($currentOcc) > 1) {
                $next = $currentOcc[1];
                // Get reminder time of the next occurrence.
                $nextOccReminderTime = $next[$this->proptags["startdate"]] - ($next[$this->proptags["reminder_minutes"]] * 60);
                // If the reminder time of the next item is before the start of this item, then that's not allowed
                if($nextOccReminderTime <= $startdate)
                    return false;
            }

            // All was ok
            return true;
        }

        function setRecurrence($tz, $recur)
        {
            // only reset timezone if specified
            if($tz)
                $this->tz = $tz;

            $this->recur = $recur;

            if(!isset($this->recur["changed_occurences"]))
                $this->recur["changed_occurences"] = Array();

            if(!isset($this->recur["deleted_occurences"]))
                $this->recur["deleted_occurences"] = Array();

            $this->deleteAttachments();
            $this->saveRecurrence();

            // if client has not set the recurring_pattern then we should generate it and save it
            $messageProps = mapi_getprops($this->message, Array($this->proptags["recurring_pattern"]));
            if(empty($messageProps[$this->proptags["recurring_pattern"]])) {
                $this->saveRecurrencePattern();
            }
        }

        // Returns the start or end time of the occurrence on the given base date.
        // This assumes that the basedate you supply is in LOCAL time
        function getOccurrenceStart($basedate)  {
            $daystart = $this->dayStartOf($basedate);
            return $this->toGMT($this->tz, $daystart + $this->recur["startocc"] * 60);
        }

        function getOccurrenceEnd($basedate)  {
            $daystart = $this->dayStartOf($basedate);
            return $this->toGMT($this->tz, $daystart + $this->recur["endocc"] * 60);
        }


        // Backwards compatible code
        function getOccurenceStart($basedate)  {
            return $this->getOccurrenceStart($basedate);
        }
        function getOccurenceEnd($basedate)  {
            return $this->getOccurrenceEnd($basedate);
        }

        /**
        * This function returns the next remindertime starting from $timestamp
        * When no next reminder exists, false is returned.
        *
        * Note: Before saving this new reminder time (when snoozing), you must check for
        *       yourself if this reminder time is earlier than your snooze time, else
        *       use your snooze time and not this reminder time.
        */
        function getNextReminderTime($timestamp)
        {
            /**
             * Get next item from now until forever, but max 1 item with reminder set
             * Note 0x7ff00000 instead of 0x7fffffff because of possible overflow failures when converting to GMT....
             * Here for getting next 10 occurences assuming that next here we will be able to find
             * nextreminder occurence in 10 occureneces
             */
            $items = $this->getItems($timestamp, 0x7ff00000, 10, true);

            // Initially setting nextreminder to false so when no next reminder exists, false is returned.
            $nextreminder = false;
            /**
             * Loop through all reminder which we get in items variable
             * and check whether the remindertime is greater than timestamp.
             * On the first occurence of greater nextreminder break the loop
             * and return the value to calling function.
             */
            for($i = 0, $len = count($items); $i < $len; $i++)
            {
                $item = $items[$i];
                $tempnextreminder = $item[$this->proptags["startdate"]] - ( $item[$this->proptags["reminder_minutes"]] * 60 );

                // If tempnextreminder is greater than timestamp then save it in nextreminder and break from the loop.
                if($tempnextreminder > $timestamp)
                {
                    $nextreminder = $tempnextreminder;
                    break;
                }
            }
            return $nextreminder;
        }

        /**
         * Note: Static function, more like a utility function.
         *
         * Gets all the items (including recurring items) in the specified calendar in the given timeframe. Items are
         * included as a whole if they overlap the interval <$start, $end> (non-inclusive). This means that if the interval
         * is <08:00 - 14:00>, the item [6:00 - 8:00> is NOT included, nor is the item [14:00 - 16:00>. However, the item
         * [7:00 - 9:00> is included as a whole, and is NOT capped to [8:00 - 9:00>.
         *
         * @param $store resource The store in which the calendar resides
         * @param $calendar resource The calendar to get the items from
         * @param $viewstart int Timestamp of beginning of view window
         * @param $viewend int Timestamp of end of view window
         * @param $propsrequested array Array of properties to return
         * @param $rows array Array of rowdata as if they were returned directly from mapi_table_queryrows. Each recurring item is
         *                    expanded so that it seems that there are only many single appointments in the table.
         */
        static function getCalendarItems($store, $calendar, $viewstart, $viewend, $propsrequested)
        {
            return getCalendarItems($store, $calendar, $viewstart, $viewend, $propsrequested);
        }


        /*****************************************************************************************************************
         * CODE BELOW THIS LINE IS FOR INTERNAL USE ONLY
         *****************************************************************************************************************
         */

        /**
         * Generates and stores recurrence pattern string to recurring_pattern property.
         */
        function saveRecurrencePattern()
        {
            // Start formatting the properties in such a way we can apply
            // them directly into the recurrence pattern.
            $type = $this->recur['type'];
            $everyn = $this->recur['everyn'];
            $start = $this->recur['start'];
            $end = $this->recur['end'];
            $term = $this->recur['term'];
            $numocc = isset($this->recur['numoccur']) ? $this->recur['numoccur'] : false;
            $startocc = $this->recur['startocc'];
            $endocc = $this->recur['endocc'];
            $pattern = '';
            $occSingleDayRank = false;
            $occTimeRange = ($startocc != 0 && $endocc != 0);

            switch ($type) {
                // Daily
                case 0x0A:
                    if ($everyn == 1) {
                        $type = _('workday');
                        $occSingleDayRank = true;
                    } else if ($everyn == (24 * 60)) {
                        $type = _('day');
                        $occSingleDayRank = true;
                    } else {
                        $everyn /= (24 * 60);
                        $type = _('days');
                        $occSingleDayRank = false;
                    }
                    break;
                // Weekly
                case 0x0B:
                    if ($everyn == 1) {
                        $type = _('week');
                        $occSingleDayRank = true;
                    } else {
                        $type = _('weeks');
                        $occSingleDayRank = false;
                    }
                    break;
                // Monthly
                case 0x0C:
                    if ($everyn == 1) {
                        $type = _('month');
                        $occSingleDayRank = true;
                    } else {
                        $type = _('months');
                        $occSingleDayRank = false;
                    }
                    break;
                // Yearly
                case 0x0D:
                    if ($everyn <= 12) {
                        $everyn = 1;
                        $type = _('year');
                        $occSingleDayRank = true;
                    } else {
                        $everyn = $everyn / 12;
                        $type = _('years');
                        $occSingleDayRank = false;
                    }
                    break;
            }

            // get timings of the first occurence
            $firstoccstartdate = isset($startocc) ? $start + (((int) $startocc) * 60) : $start;
            $firstoccenddate = isset($endocc) ? $end + (((int) $endocc) * 60) : $end;

            $start = gmdate(_('d-m-Y'), $firstoccstartdate);
            $end = gmdate(_('d-m-Y'), $firstoccenddate);
            $startocc = gmdate(_('G:i'), $firstoccstartdate);
            $endocc = gmdate(_('G:i'), $firstoccenddate);

            // Based on the properties, we need to generate the recurrence pattern string.
            // This is obviously very easy since we can simply concatenate a bunch of strings,
            // however this messes up translations for languages which order their words
            // differently.
            // To improve translation quality we create a series of default strings, in which
            // we only have to fill in the correct variables. The base string is thus selected
            // based on the available properties.
            if ($term == 0x23) {
                // Never ends
                if ($occTimeRange) {
                    if ($occSingleDayRank) {
                        $pattern = sprintf(_('Occurs every %s effective %s from %s to %s.'), $type, $start, $startocc, $endocc);
                    } else {
                        $pattern = sprintf(_('Occurs every %s %s effective %s from %s to %s.'), $everyn, $type, $start, $startocc, $endocc);
                    }
                } else {
                    if ($occSingleDayRank) {
                        $pattern = sprintf(_('Occurs every %s effective %s.'), $type, $start);
                    } else {
                        $pattern = sprintf(_('Occurs every %s %s effective %s.'), $everyn, $type, $start);
                    }
                }
            } else if ($term == 0x22) {
                // After a number of times
                if ($occTimeRange) {
                    if ($occSingleDayRank) {
                        $pattern = sprintf(ngettext('Occurs every %s effective %s for %s occurence from %s to %s.',
                                                'Occurs every %s effective %s for %s occurences from %s to %s.', $numocc), $type, $start, $numocc, $startocc, $endocc);
                    } else {
                        $pattern = sprintf(ngettext('Occurs every %s %s effective %s for %s occurence from %s to %s.',
                                                'Occurs every %s %s effective %s for %s occurences %s to %s.', $numocc), $everyn, $type, $start, $numocc, $startocc, $endocc);
                    }
                } else {
                    if ($occSingleDayRank) {
                        $pattern = sprintf(ngettext('Occurs every %s effective %s for %s occurence.',
                                                     'Occurs every %s effective %s for %s occurences.', $numocc), $type, $start, $numocc);
                    } else {
                        $pattern = sprintf(ngettext('Occurs every %s %s effective %s for %s occurence.',
                                                         'Occurs every %s %s effective %s for %s occurences.', $numocc), $everyn, $type, $start, $numocc);
                    }
                }
            } else if ($term == 0x21) {
                // After the given enddate
                if ($occTimeRange) {
                    if ($occSingleDayRank) {
                        $pattern = sprintf(_('Occurs every %s effective %s until %s from %s to %s.'), $type, $start, $end, $startocc, $endocc);
                    } else {
                        $pattern = sprintf(_('Occurs every %s %s effective %s until %s from %s to %s.'), $everyn, $type, $start, $end, $startocc, $endocc);
                    }
                } else {
                    if ($occSingleDayRank) {
                        $pattern = sprintf(_('Occurs every %s effective %s until %s.'), $type, $start, $end);
                    } else {
                        $pattern = sprintf(_('Occurs every %s %s effective %s until %s.'), $everyn, $type, $start, $end);
                    }
                }
            }

            if(!empty($pattern)) {
                mapi_setprops($this->message, Array($this->proptags["recurring_pattern"] => $pattern ));
            }
        }

        /*
         * Remove an exception by base_date. This is the base date in local daystart time
         */
        function deleteException($base_date)
        {
            // Remove all exceptions on $base_date from the deleted and changed occurrences lists

            // Remove all items in $todelete from deleted_occurences
            $new = Array();

            foreach($this->recur["deleted_occurences"] as $entry) {
                if($entry != $base_date)
                    $new[] = $entry;
            }
            $this->recur["deleted_occurences"] = $new;

            $new = Array();

            foreach($this->recur["changed_occurences"] as $entry) {
                if(!$this->isSameDay($entry["basedate"], $base_date))
                    $new[] = $entry;
                else
                    $this->deleteExceptionAttachment($this->toGMT($this->tz, $base_date + $this->recur["startocc"] * 60));
            }

            $this->recur["changed_occurences"] = $new;
        }

        /**
         * Function which saves the exception data in an attachment.
         * @param array $exception_props the exception data (like any other MAPI appointment)
         * @param array $exception_recips list of recipients
         * @param mapi_message $copy_attach_from mapi message from which attachments should be copied
         * @return array properties of the exception
         */
        function createExceptionAttachment($exception_props, $exception_recips = array(), $copy_attach_from = false)
        {
              // Create new attachment.
              $attachment = mapi_message_createattach($this->message);
              $props = array();
              $props[PR_ATTACHMENT_FLAGS] = 2;
              $props[PR_ATTACHMENT_HIDDEN] = true;
              $props[PR_ATTACHMENT_LINKID] = 0;
              $props[PR_ATTACH_FLAGS] = 0;
              $props[PR_ATTACH_METHOD] = 5;
              $props[PR_DISPLAY_NAME] = "Exception";
              $props[PR_EXCEPTION_STARTTIME] = $this->fromGMT($this->tz, $exception_props[$this->proptags["startdate"]]);
              $props[PR_EXCEPTION_ENDTIME] = $this->fromGMT($this->tz, $exception_props[$this->proptags["duedate"]]);
              mapi_setprops($attachment, $props);

            $imessage = mapi_attach_openobj($attachment, MAPI_CREATE | MAPI_MODIFY);

            if ($copy_attach_from) {
                $attachmentTable = mapi_message_getattachmenttable($copy_attach_from);
                if($attachmentTable) {
                    $attachments = mapi_table_queryallrows($attachmentTable, array(PR_ATTACH_NUM, PR_ATTACH_SIZE, PR_ATTACH_LONG_FILENAME, PR_ATTACHMENT_HIDDEN, PR_DISPLAY_NAME, PR_ATTACH_METHOD));

                    foreach($attachments as $attach_props){
                        $attach_old = mapi_message_openattach($copy_attach_from, (int) $attach_props[PR_ATTACH_NUM]);
                        $attach_newResourceMsg = mapi_message_createattach($imessage);
                        mapi_copyto($attach_old, array(), array(), $attach_newResourceMsg, 0);
                        mapi_savechanges($attach_newResourceMsg);
                    }
                }
            }

            $props = $props + $exception_props;

            // FIXME: the following piece of code is written to fix the creation
            // of an exception. This is only a quickfix as it is not yet possible
            // to change an existing exception.
            // remove mv properties when needed
            foreach($props as $propTag=>$propVal){
                if ((mapi_prop_type($propTag) & MV_FLAG) == MV_FLAG && is_null($propVal)){
                    unset($props[$propTag]);
                }
            }

            mapi_setprops($imessage, $props);

            $this->setExceptionRecipients($imessage, $exception_recips, true);

            mapi_savechanges($imessage);
            mapi_savechanges($attachment);
        }

        /**
         * Function which deletes the attachment of an exception.
         * @param date $base_date base date of the attachment. Should be in GMT. The attachment
         *                        actually saves the real time of the original date, so we have
         *                          to check whether it's on the same day.
         */
        function deleteExceptionAttachment($base_date)
        {
            $attachments = mapi_message_getattachmenttable($this->message);
            $attachTable = mapi_table_queryallrows($attachments, Array(PR_ATTACH_NUM));

            foreach($attachTable as $attachRow)
            {
                $tempattach = mapi_message_openattach($this->message, $attachRow[PR_ATTACH_NUM]);
                $exception = mapi_attach_openobj($tempattach);

                  $data = mapi_message_getprops($exception, array($this->proptags["basedate"]));

                  if($this->dayStartOf($this->fromGMT($this->tz, $data[$this->proptags["basedate"]])) == $this->dayStartOf($base_date)) {
                      mapi_message_deleteattach($this->message, $attachRow[PR_ATTACH_NUM]);
                  }
            }
        }

        /**
         * Function which deletes all attachments of a message.
         */
        function deleteAttachments()
        {
            $attachments = mapi_message_getattachmenttable($this->message);
            $attachTable = mapi_table_queryallrows($attachments, Array(PR_ATTACH_NUM, PR_ATTACHMENT_HIDDEN));

            foreach($attachTable as $attachRow)
            {
                if(isset($attachRow[PR_ATTACHMENT_HIDDEN]) && $attachRow[PR_ATTACHMENT_HIDDEN]) {
                    mapi_message_deleteattach($this->message, $attachRow[PR_ATTACH_NUM]);
                }
            }
        }

        /**
         * Get an exception attachment based on its basedate
         */
        function getExceptionAttachment($base_date)
        {
            // Retrieve only exceptions which are stored as embedded messages
            $attach_res = Array(RES_AND,
                            Array(
                                Array(RES_PROPERTY,
                                    Array(
                                        RELOP => RELOP_EQ,
                                        ULPROPTAG => PR_ATTACH_METHOD,
                                        VALUE => array(PR_ATTACH_METHOD => ATTACH_EMBEDDED_MSG)
                                    )
                                )
                            )
            );
            $attachments = mapi_message_getattachmenttable($this->message);
            $attachRows = mapi_table_queryallrows($attachments, Array(PR_ATTACH_NUM), $attach_res);

            if(is_array($attachRows)) {
                foreach($attachRows as $attachRow)
                {
                    $tempattach = mapi_message_openattach($this->message, $attachRow[PR_ATTACH_NUM]);
                    $exception = mapi_attach_openobj($tempattach);

                    $data = mapi_message_getprops($exception, array($this->proptags["basedate"]));

                    if(!isset($data[$this->proptags["basedate"]])) {
                        // if no basedate found then it could be embedded message so ignore it
                        // we need proper restriction to exclude embedded messages aswell
                        continue;
                    }

                    if($this->isSameDay($this->fromGMT($this->tz, $data[$this->proptags["basedate"]]), $base_date)) {
                        return $tempattach;
                    }
                }
            }

            return false;
        }

        /**
         * processOccurrenceItem, adds an item to a list of occurrences, but only if the following criteria are met:
         * - The resulting occurrence (or exception) starts or ends in the interval <$start, $end>
         * - The ocurrence isn't specified as a deleted occurrence
         * @param array $items reference to the array to be added to
         * @param date $start start of timeframe in GMT TIME
         * @param date $end end of timeframe in GMT TIME
         * @param date $basedate (hour/sec/min assumed to be 00:00:00) in LOCAL TIME OF THE OCCURRENCE
         * @param int $startocc start of occurrence since beginning of day in minutes
         * @param int $endocc end of occurrence since beginning of day in minutes
         * @param int $tz the timezone info for this occurrence ( applied to $basedate / $startocc / $endocc )
         * @param bool $reminderonly If TRUE, only add the item if the reminder is set
         */
        function processOccurrenceItem(&$items, $start, $end, $basedate, $startocc, $endocc, $tz, $reminderonly)
        {
            $exception = $this->isException($basedate);
            if($exception){
                return false;
            }else{
                $occstart = $basedate + $startocc * 60;
                $occend = $basedate + $endocc * 60;

                // Convert to GMT
                $occstart = $this->toGMT($tz, $occstart);
                $occend = $this->toGMT($tz, $occend);

                /**
                 * FIRST PART : Check range criterium. Exact matches (eg when $occstart == $end), do NOT match since you cannot
                 * see any part of the appointment. Partial overlaps DO match.
                 *
                 * SECOND PART : check if occurence is not a zero duration occurrence which
                 * starts at 00:00 and ends on 00:00. if it is so, then process
                 * the occurrence and send it in response.
                 */
                if(($occstart  >= $end || $occend <=  $start) && !($occstart == $occend && $occstart == $start))
                    return;

                // Properties for this occurrence are the same as the main object,
                // With these properties overridden
                $newitem = $this->messageprops;
                $newitem[$this->proptags["startdate"]] = $occstart;
                $newitem[$this->proptags["duedate"]] = $occend;
                $newitem[$this->proptags["commonstart"]] = $occstart;
                $newitem[$this->proptags["commonend"]] = $occend;
                $newitem["basedate"] = $basedate;
            }

            // If reminderonly is set, only add reminders
            if($reminderonly && (!isset($newitem[$this->proptags["reminder"]]) || $newitem[$this->proptags["reminder"]] == false))
                return;

            $items[] = $newitem;
        }

        /**
         * processExceptionItem, adds an all exception item to a list of occurrences, without any constraint on timeframe
         * @param array $items reference to the array to be added to
         * @param date $start start of timeframe in GMT TIME
         * @param date $end end of timeframe in GMT TIME
         */
        function processExceptionItems(&$items, $start, $end)
        {
            $limit = 0;
            foreach($this->recur["changed_occurences"] as $exception) {

                // Convert to GMT
                $occstart = $this->toGMT($this->tz, $exception["start"]);
                $occend = $this->toGMT($this->tz, $exception["end"]);

                // Check range criterium. Exact matches (eg when $occstart == $end), do NOT match since you cannot
                // see any part of the appointment. Partial overlaps DO match.
                if($occstart >= $end || $occend <= $start)
                    continue;

                array_push($items, $this->getExceptionProperties($exception));
                if((count($items) == $limit))
                    break;
                }
        }

        /**
         * Function which verifies if on the given date an exception, delete or change, occurs.
         * @param date $date the date
         * @return array the exception, true - if an occurrence is deleted on the given date, false - no exception occurs on the given date
         */
        function isException($basedate)
        {
            if($this->isDeleteException($basedate))
                return true;

            if($this->getChangeException($basedate) != false)
                return true;

            return false;
        }

        /**
         * Returns TRUE if there is a DELETE exception on the given base date
         */
        function isDeleteException($basedate)
        {
            // Check if the occurrence is deleted on the specified date
            foreach($this->recur["deleted_occurences"] as $deleted)
            {
                if($this->isSameDay($deleted, $basedate))
                    return true;
            }

            return false;
        }

        /**
         * Returns the exception if there is a CHANGE exception on the given base date, or FALSE otherwise
         */
        function getChangeException($basedate)
        {
            // Check if the occurrence is modified on the specified date
            foreach($this->recur["changed_occurences"] as $changed)
            {
                if($this->isSameDay($changed["basedate"], $basedate))
                    return $changed;
            }

            return false;
        }

        /**
         * Function to see if two dates are on the same day
         * @param date $time1 date 1
         * @param date $time2 date 2
         * @return boolean Returns TRUE when both dates are on the same day
         */
        function isSameDay($date1, $date2)
        {
            $time1 = $this->gmtime($date1);
            $time2 = $this->gmtime($date2);

            return $time1["tm_mon"] == $time2["tm_mon"] && $time1["tm_year"] == $time2["tm_year"] && $time1["tm_mday"] == $time2["tm_mday"];
        }

        /**
         * Function to get all properties of a single changed exception.
         * @param date $date base date of exception
         * @return array associative array of properties for the exception, compatible with
         */
        function getExceptionProperties($exception)
        {
            // Exception has same properties as main object, with some properties overridden:
            $item = $this->messageprops;

            // Special properties
            $item["exception"] = true;
            $item["basedate"] = $exception["basedate"]; // note that the basedate is always in local time !

            // MAPI-compatible properties (you can handle an exception as a normal calendar item like this)
            $item[$this->proptags["startdate"]] = $this->toGMT($this->tz, $exception["start"]);
            $item[$this->proptags["duedate"]] = $this->toGMT($this->tz, $exception["end"]);
            $item[$this->proptags["commonstart"]] = $item[$this->proptags["startdate"]];
            $item[$this->proptags["commonend"]] = $item[$this->proptags["duedate"]];

            if(isset($exception["subject"])) {
                $item[$this->proptags["subject"]] = $exception["subject"];
            }

            if(isset($exception["label"])) {
                $item[$this->proptags["label"]] = $exception["label"];
            }

            if(isset($exception["alldayevent"])) {
                $item[$this->proptags["alldayevent"]] = $exception["alldayevent"];
            }

            if(isset($exception["location"])) {
                $item[$this->proptags["location"]] = $exception["location"];
            }

            if(isset($exception["remind_before"])) {
                $item[$this->proptags["reminder_minutes"]] = $exception["remind_before"];
            }

            if(isset($exception["reminder_set"])) {
                $item[$this->proptags["reminder"]] = $exception["reminder_set"];
            }

            if(isset($exception["busystatus"])) {
                $item[$this->proptags["busystatus"]] = $exception["busystatus"];
            }

            return $item;
        }

        /**
         * Function which sets recipients for an exception.
         *
         * The $exception_recips can be provided in 2 ways:
         *  - A delta which indicates which recipients must be added, removed or deleted.
         *  - A complete array of the recipients which should be applied to the message.
         *
         * The first option is preferred as it will require less work to be executed.
         *
         * @param resource $message exception attachment of recurring item
         * @param array $exception_recips list of recipients
         * @param boolean $copy_orig_recips True to copy all recipients which are on the original
         * message to the attachment by default. False if only the $exception_recips changes should
         * be applied.
         */
        function setExceptionRecipients($message, $exception_recips, $copy_orig_recips = true)
        {
            if (isset($exception_recips['add']) || isset($exception_recips['remove']) || isset($exception_recips['modify'])) {
                $this->setDeltaExceptionRecipients($message, $exception_recips, $copy_orig_recips);
            } else {
                $this->setAllExceptionRecipients($message, $exception_recips);
            }
        }

        /**
         * Function which applies the provided delta for recipients changes to the exception.
         *
         * The $exception_recips should be an array containing the following keys:
         *  - "add": this contains an array of recipients which must be added
         *  - "remove": This contains an array of recipients which must be removed
         *  - "modify": This contains an array of recipients which must be modified
         *
         * @param resource $message exception attachment of recurring item
         * @param array $exception_recips list of recipients
         * @param boolean $copy_orig_recips True to copy all recipients which are on the original
         * message to the attachment by default. False if only the $exception_recips changes should
         * be applied.
         */
        function setDeltaExceptionRecipients($exception, $exception_recips, $copy_orig_recips)
        {
            // Check if the recipients from the original message should be copied,
            // if so, open the recipient table of the parent message and apply all
            // rows on the target recipient.
            if ($copy_orig_recips === true) {
                $origTable = mapi_message_getrecipienttable($this->message);
                $recipientRows = mapi_table_queryallrows($origTable, $this->recipprops);
                mapi_message_modifyrecipients($exception, MODRECIP_ADD, $recipientRows);
            }

            // Add organizer to meeting only if it is not organized.
            $msgprops = mapi_getprops($exception, array(PR_SENT_REPRESENTING_ENTRYID, PR_SENT_REPRESENTING_EMAIL_ADDRESS, PR_SENT_REPRESENTING_NAME, PR_SENT_REPRESENTING_ADDRTYPE, PR_SENT_REPRESENTING_SEARCH_KEY, $this->proptags['responsestatus']));
            if (isset($msgprops[$this->proptags['responsestatus']]) && $msgprops[$this->proptags['responsestatus']] != olResponseOrganized){
                $this->addOrganizer($msgprops, $exception_recips['add']);
            }

            // Remove all deleted recipients
            if (isset($exception_recips['remove'])) {
                foreach ($exception_recips['remove'] as &$recip) {
                    if (!isset($recip[PR_RECIPIENT_FLAGS]) || $recip[PR_RECIPIENT_FLAGS] != (recipReserved | recipExceptionalDeleted | recipSendable)) {
                        $recip[PR_RECIPIENT_FLAGS] = recipSendable | recipExceptionalDeleted;
                    } else {
                        $recip[PR_RECIPIENT_FLAGS] = recipReserved | recipExceptionalDeleted | recipSendable;
                    }
                    $recip[PR_RECIPIENT_TRACKSTATUS] = olResponseNone;        // No Response required
                }
                unset($recip);
                mapi_message_modifyrecipients($exception, MODRECIP_MODIFY, $exception_recips['remove']);
            }

            // Add all new recipients
            if (isset($exception_recips['add'])) {
                mapi_message_modifyrecipients($exception, MODRECIP_ADD, $exception_recips['add']);
            }

            // Modify the existing recipients
            if (isset($exception_recips['modify'])) {
                mapi_message_modifyrecipients($exception, MODRECIP_MODIFY, $exception_recips['modify']);
            }
        }

        /**
         * Function which applies the provided recipients to the exception, also checks for deleted recipients.
         *
         * The $exception_recips should be an array containing all recipients which must be applied
         * to the exception. This will copy all recipients from the original message and then start filter
         * out all recipients which are not provided by the $exception_recips list.
         *
         * @param resource $message exception attachment of recurring item
         * @param array $exception_recips list of recipients
         */
        function setAllExceptionRecipients($message, $exception_recips)
        {
            $deletedRecipients = array();
            $useMessageRecipients = false;

            $recipientTable = mapi_message_getrecipienttable($message);
            $recipientRows = mapi_table_queryallrows($recipientTable, $this->recipprops);

            if (empty($recipientRows)) {
                $useMessageRecipients = true;
                $recipientTable = mapi_message_getrecipienttable($this->message);
                $recipientRows = mapi_table_queryallrows($recipientTable, $this->recipprops);
            }

            // Add organizer to meeting only if it is not organized.
            $msgprops = mapi_getprops($message, array(PR_SENT_REPRESENTING_ENTRYID, PR_SENT_REPRESENTING_EMAIL_ADDRESS, PR_SENT_REPRESENTING_NAME, PR_SENT_REPRESENTING_ADDRTYPE, PR_SENT_REPRESENTING_SEARCH_KEY, $this->proptags['responsestatus']));
            if (isset($msgprops[$this->proptags['responsestatus']]) && $msgprops[$this->proptags['responsestatus']] != olResponseOrganized){
                $this->addOrganizer($msgprops, $exception_recips);
            }

            if (!empty($exception_recips)) {
                foreach($recipientRows as $key => $recipient) {
                    $found = false;
                    foreach($exception_recips as $excep_recip) {
                        if (isset($recipient[PR_SEARCH_KEY], $excep_recip[PR_SEARCH_KEY]) && $recipient[PR_SEARCH_KEY] == $excep_recip[PR_SEARCH_KEY])
                            $found = true;
                    }

                    if (!$found) {
                       $foundInDeletedRecipients = false;
                       // Look if the $recipient is in the list of deleted recipients
                       if (!empty($deletedRecipients)) {
                               foreach($deletedRecipients as $recip) {
                                   if ($recip[PR_SEARCH_KEY] == $recipient[PR_SEARCH_KEY]){
                                       $foundInDeletedRecipients = true;
                                       break;
                                   }
                               }
                       }

                       // If recipient is not in list of deleted recipient, add him
                       if (!$foundInDeletedRecipients) {
                            if (!isset($recipient[PR_RECIPIENT_FLAGS]) || $recipient[PR_RECIPIENT_FLAGS] != (recipReserved | recipExceptionalDeleted | recipSendable)) {
                                $recipient[PR_RECIPIENT_FLAGS] = recipSendable | recipExceptionalDeleted;
                            } else {
                                $recipient[PR_RECIPIENT_FLAGS] = recipReserved | recipExceptionalDeleted | recipSendable;
                            }
                            $recipient[PR_RECIPIENT_TRACKSTATUS] = olRecipientTrackStatusNone;    // No Response required
                            $deletedRecipients[] = $recipient;
                        }
                    }

                    // When $message contains a non-empty recipienttable, we must delete the recipients
                    // before re-adding them. However, when $message is doesn't contain any recipients,
                    // we are using the recipient table of the original message ($this->message)
                    // rather then $message. In that case, we don't need to remove the recipients
                    // from the $message, as the recipient table is already empty, and
                    // mapi_message_modifyrecipients() will throw an error.
                    if ($useMessageRecipients === false) {
                        mapi_message_modifyrecipients($message, MODRECIP_REMOVE, array($recipient));
                    }
                }
                $exception_recips = array_merge($exception_recips, $deletedRecipients);
            } else {
                $exception_recips = $recipientRows;
            }

            if (!empty($exception_recips)) {
                // Set the new list of recipients on the exception message, this also removes the existing recipients
                mapi_message_modifyrecipients($message, 0, $exception_recips);
            }
        }

        /**
         * Function returns basedates of all changed occurrences
         *@return array array(
                            0 => 123459321
                        )
         */
        function getAllExceptions()
        {
            $result = false;
            if (!empty($this->recur["changed_occurences"])) {
                $result = array();
                foreach($this->recur["changed_occurences"] as $exception) {
                    $result[] = $exception["basedate"];
                }
                return $result;
            }
            return $result;
        }

        /**
         *  Function which adds organizer to recipient list which is passed.
         *  This function also checks if it has organizer.
         *
         * @param array $messageProps message properties
         * @param array $recipients    recipients list of message.
         * @param boolean $isException true if we are processing recipient of exception
         */
        function addOrganizer($messageProps, &$recipients, $isException = false)
        {
            $hasOrganizer = false;
            // Check if meeting already has an organizer.
            foreach ($recipients as $key => $recipient){
                if (isset($recipient[PR_RECIPIENT_FLAGS]) && $recipient[PR_RECIPIENT_FLAGS] == (recipSendable | recipOrganizer)) {
                    $hasOrganizer = true;
                } else if ($isException && !isset($recipient[PR_RECIPIENT_FLAGS])){
                    // Recipients for an occurrence
                    $recipients[$key][PR_RECIPIENT_FLAGS] = recipSendable | recipExceptionalResponse;
                }
            }

            if (!$hasOrganizer){
                // Create organizer.
                $organizer = array();
                $organizer[PR_ENTRYID] = $messageProps[PR_SENT_REPRESENTING_ENTRYID];
                $organizer[PR_DISPLAY_NAME] = $messageProps[PR_SENT_REPRESENTING_NAME];
                $organizer[PR_EMAIL_ADDRESS] = $messageProps[PR_SENT_REPRESENTING_EMAIL_ADDRESS];
                $organizer[PR_RECIPIENT_TYPE] = MAPI_TO;
                $organizer[PR_RECIPIENT_DISPLAY_NAME] = $messageProps[PR_SENT_REPRESENTING_NAME];
                $organizer[PR_ADDRTYPE] = empty($messageProps[PR_SENT_REPRESENTING_ADDRTYPE])?'SMTP':$messageProps[PR_SENT_REPRESENTING_ADDRTYPE];
                $organizer[PR_RECIPIENT_TRACKSTATUS] = olRecipientTrackStatusNone;
                $organizer[PR_RECIPIENT_FLAGS] = recipSendable | recipOrganizer;
                $organizer[PR_SEARCH_KEY] = $messageProps[PR_SENT_REPRESENTING_SEARCH_KEY];

                // Add organizer to recipients list.
                array_unshift($recipients, $organizer);
            }
        }
    }

    /*

    From http://www.ohelp-one.com/new-6765483-3268.html:

    Recurrence Data Structure Offset Type Value

    0 ULONG (?) Constant : { 0x04, 0x30, 0x04, 0x30}

    4 UCHAR 0x0A + recurrence type: 0x0A for daily, 0x0B for weekly, 0x0C for
    monthly, 0x0D for yearly

    5 UCHAR Constant: { 0x20}

    6 ULONG Seems to be a variant of the recurrence type: 1 for daily every n
    days, 2 for daily every weekday and weekly, 3 for monthly or yearly. The
    special exception is regenerating tasks that regenerate on a weekly basis: 0
    is used in that case (I have no idea why).

    Here's the recurrence-type-specific data. Because the daily every N days
    data are 4 bytes shorter than the data for the other types, the offsets for
    the rest of the data will be 4 bytes off depending on the recurrence type.

    Daily every N days:

    10 ULONG ( N - 1) * ( 24 * 60). I'm not sure what this is used for, but it's consistent.

    14 ULONG N * 24 * 60: minutes between recurrences

    18 ULONG 0 for all events and non-regenerating recurring tasks. 1 for
    regenerating tasks.

    Daily every weekday (this is essentially a subtype of weekly recurrence):

    10 ULONG 6 * 24 * 60: minutes between recurrences ( a week... sort of)

    14 ULONG 1: recur every week (corresponds to the second parameter for weekly
    recurrence)

    18 ULONG 0 for all events and non-regenerating recurring tasks. 1 for
    regenerating tasks.

    22 ULONG 0x3E: bitmask for recurring every weekday (corresponds to fourth
    parameter for weekly recurrence)

    Weekly every N weeks for all events and non-regenerating tasks:

    10 ULONG 6 * 24 * 60: minutes between recurrences (a week... sort of)

    14 ULONG N: recurrence interval

    18 ULONG Constant: 0

    22 ULONG Bitmask for determining which days of the week the event recurs on
    ( 1 << dayOfWeek, where Sunday is 0).

    Weekly every N weeks for regenerating tasks: 10 ULONG Constant: 0

    14 ULONG N * 7 * 24 * 60: recurrence interval in minutes between occurrences

    18 ULONG Constant: 1

    Monthly every N months on day D:

    10 ULONG This is the most complicated value
    in the entire mess. It's basically a very complicated way of stating the
    recurrence interval. I tweaked fbs' basic algorithm. DateTime::MonthInDays
    simply returns the number of days in a given month, e.g. 31 for July for 28
    for February (the algorithm doesn't take into account leap years, but it
    doesn't seem to matter). My DateTime object, like Microsoft's COleDateTime,
    uses 1-based months (i.e. January is 1, not 0). With that in mind, this
    works:

    long monthIndex = ( ( ( ( 12 % schedule-=GetInterval()) *

    ( ( schedule-=GetStartDate().GetYear() - 1601) %

    schedule-=GetInterval())) % schedule-=GetInterval()) +

    ( schedule-=GetStartDate().GetMonth() - 1)) % schedule-=GetInterval();

    for( int i = 0; i < monthIndex; i++)

    {

    value += DateTime::GetDaysInMonth( ( i % 12) + 1) * 24 * 60;

    }

    This should work for any recurrence interval, including those greater than
    12.

    14 ULONG N: recurrence interval

    18 ULONG 0 for all events and non-regenerating recurring tasks. 1 for
    regenerating tasks.

    22 ULONG D: day of month the event recurs on (if this value is greater than
    the number of days in a given month [e.g. 31 for and recurs in June], then
    the event will recur on the last day of the month)

    Monthly every N months on the Xth Y (e.g. "2nd Tuesday"):

    10 ULONG See above: same as for monthly every N months on day D

    14 ULONG N: recurrence interval

    18 ULONG 0 for all events and non-regenerating recurring tasks. 1 for
    regenerating tasks.

    22 ULONG Y: bitmask for determining which day of the week the event recurs
    on (see weekly every N weeks). Some useful values are 0x7F for any day, 0x3E
    for a weekday, or 0x41 for a weekend day.

    26 ULONG X: 1 for first occurrence, 2 for second, etc. 5 for last
    occurrence. E.g. for "2nd Tuesday", you should have values of 0x04 for the
    prior value and 2 for this one.

    Yearly on day D of month M:

    10 ULONG M (sort of): This is another messy
    value. It's the number of minute since the startning of the year to the
    given month. For an explanation of GetDaysInMonth, see monthly every N
    months. This will work:

    ULONG monthOfYearInMinutes = 0;

    for( int i = DateTime::cJanuary; i < schedule-=GetMonth(); i++)

    {

    monthOfYearInMinutes += DateTime::GetDaysInMonth( i) * 24 * 60;

    }



    14 ULONG 12: recurrence interval in months. Naturally, 12.

    18 ULONG 0 for all events and non-regenerating recurring tasks. 1 for
    regenerating tasks.

    22 ULONG D: day of month the event recurs on. See monthly every N months on
    day D.

    Yearly on the Xth Y of month M: 10 ULONG M (sort of): See yearly on day D of
    month M.

    14 ULONG 12: recurrence interval in months. Naturally, 12.

    18 ULONG Constant: 0

    22 ULONG Y: see monthly every N months on the Xth Y.

    26 ULONG X: see monthly every N months on the Xth Y.

    After these recurrence-type-specific values, the offsets will change
    depending on the type. For every type except daily every N days, the offsets
    will grow by at least 4. For those types using the Xth Y, the offsets will
    grow by an additional 4, for a total of 8. The offsets for the rest of these
    values will be given for the most basic case, daily every N days, i.e.
    without any growth. Adjust as necessary. Also, the presence of exceptions
    will change the offsets following the exception data by a variable number of
    bytes, so the offsets given in the table are accurate only for those
    recurrence patterns without any exceptions.


    22 UCHAR Type of pattern termination: 0x21 for terminating on a given date, 0x22 for terminating
    after a given number of recurrences, or 0x23 for never terminating
    (recurring infinitely)

    23 UCHARx3 Constant: { 0x20, 0x00, 0x00}

    26 ULONG Number of occurrences in pattern: 0 for infinite recurrence,
    otherwise supply the value, even if it terminates on a given date, not after
    a given number

    30 ULONG Constant: 0

    34 ULONG Number of exceptions to pattern (i.e. deleted or changed
    occurrences)

    .... ULONGxN Base date of each exception, given in hundreds of nanoseconds
    since 1601, so see below to turn them into a comprehensible format. The base
    date of an exception is the date (and only the date-- not the time) the
    exception would have occurred on in the pattern. They must occur in
    ascending order.

    38 ULONG Number of changed exceptions (i.e. total number of exceptions -
    number of deleted exceptions): if there are changed exceptions, again, more
    data will be needed, but that will wait

    .... ULONGxN Start date (and only the date-- not the time) of each changed
    exception, i.e. the exceptions which aren't deleted. These must also occur
    in ascending order. If all of the exceptions are deleted, this data will be
    absent. If present, they will be in the format above. Any dates that are in
    the first list but not in the second are exceptions that have been deleted
    (i.e. the difference between the two sets). Note that this is the start date
    (including time), not the base date. Given that the values are unordered and
    that they can't be matched up against the previous list in this iteration of
    the recurrence data (they could in previous ones), it is very difficult to
    tell which exceptions are deleted and which are changed. Fortunately, for
    this new format, the base dates are given on the attachment representing the
    changed exception (described below), so you can simply ignore this list of
    changed exceptions. Just create a list of exceptions from the previous list
    and assume they're all deleted unless you encounter an attachment with a
    matching base date later on.

    42 ULONG Start date of pattern given in hundreds of nanoseconds since 1601;
    see below for an explanation.

    46 ULONG End date of pattern: see start date of pattern

    50 ULONG Constant: { 0x06, 0x30, 0x00, 0x00}

    NOTE: I find the following 8-byte sequence of bytes to be very useful for
    orienting myself when looking at the raw data. If you can find { 0x06, 0x30,
    0x00, 0x00, 0x08, 0x30, 0x00, 0x00}, you can use these tables to work either
    forwards or backwards to find the data you need. The sequence sort of
    delineates certain critical exception-related data and delineates the
    exceptions themselves from the rest of the data and is relatively easy to
    find. If you're going to be meddling in here a lot, I suggest making a
    friend of ol' 0x00003006.

    54 UCHAR This number is some kind of version indicator. Use 0x08 for Outlook
    2003. I believe 0x06 is Outlook 2000 and possibly 98, while 0x07 is Outlook
    XP. This number must be consistent with the features of the data structure
    generated by the version of Outlook indicated thereby-- there are subtle
    differences between the structures, and, if the version doesn't match the
    data, Outlook will sometimes failto read the structure.

    55 UCHARx3 Constant: { 0x30, 0x00, 0x00}

    58 ULONG Start time of occurrence in minutes: e.g. 0 for midnight or 720 for
    12 PM

    62 ULONG End time of occurrence in minutes: i.e. start time + duration, e.g.
    900 for an event that starts at 12 PM and ends at 3PM

    Exception Data 66 USHORT Number of changed exceptions: essentially a check
    on the prior occurrence of this value; should be equivalent.

    NOTE: The following structure will occur N many times (where N = number of
    changed exceptions), and each structure can be of variable length.

    .... ULONG Start date of changed exception given in hundreds of nanoseconds
    since 1601

    .... ULONG End date of changed exception given in hundreds of nanoseconds
    since 1601

    .... ULONG This is a value I don't clearly understand. It seems to be some
    kind of archival value that matches the start time most of the time, but
    will lag behind when the start time is changed and then match up again under
    certain conditions later. In any case, setting to the same value as the
    start time seems to work just fine (more information on this value would be
    appreciated).

    .... USHORT Bitmask of changes to the exception (see below). This will be 0
    if the only changes to the exception were to its start or end time.

    .... ULONGxN Numeric values (e.g. label or minutes to remind before the
    event) changed in the exception. These will occur in the order of their
    corresponding bits (see below). If no numeric values were changed, then
    these values will be absent.

    NOTE: The following three values constitute a single sub-structure that will
    occur N many times, where N is the number of strings that are changed in the
    exception. Since there are at most 2 string values that can be excepted
    (i.e. subject [or description], and location), there can at most be two of
    these, but there may be none.

    .... USHORT Length of changed string value with NULL character

    .... USHORT Length of changed string value without NULL character (i.e.
    previous value - 1)

    .... CHARxN Changed string value (without NULL terminator)

    Unicode Data NOTE: If a string value was changed on an exception, those
    changed string values will reappear here in Unicode format after 8 bytes of
    NULL padding (possibly a Unicode terminator?). For each exception with a
    changed string value, there will be an identifier, followed by the changed
    strings in Unicode. The strings will occur in the order of their
    corresponding bits (see below). E.g., if both subject and location were
    changed in the exception, there would be the 3-ULONG identifier, then the
    length of the subject, then the subject, then the length of the location,
    then the location.

    70 ULONGx2 Constant: { 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00}. This
    padding serves as a barrier between the older data structure and the
    appended Unicode data. This is the same sequence as the Unicode terminator,
    but I'm not sure whether that's its identity or not.

    .... ULONGx3 These are the three times used to identify the exception above:
    start date, end date, and repeated start date. These should be the same as
    they were above.

    .... USHORT Length of changed string value without NULL character. This is
    given as count of WCHARs, so it should be identical to the value above.

    .... WCHARxN Changed string value in Unicode (without NULL terminator)

    Terminator ... ULONGxN Constant: { 0x00, 0x00, 0x00, 0x00}. 4 bytes of NULL
    padding per changed exception. If there were no changed exceptions, all
    you'll need is the final terminator below.

    .... ULONGx2 Constant: { 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00}.

    */
