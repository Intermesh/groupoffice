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

    class TaskRecurrence extends BaseRecurrence
    {
        /**
         * Timezone info which is always false for task
         */
        var $tz = false;

        function __construct($store, $message)
        {
            $this->store = $store;
            $this->message = $message;

            $properties = array();
            $properties["entryid"] = PR_ENTRYID;
            $properties["parent_entryid"] = PR_PARENT_ENTRYID;
            $properties["icon_index"] = PR_ICON_INDEX;
            $properties["message_class"] = PR_MESSAGE_CLASS;
            $properties["message_flags"] = PR_MESSAGE_FLAGS;
            $properties["subject"] = PR_SUBJECT;
            $properties["importance"] = PR_IMPORTANCE;
            $properties["sensitivity"] = PR_SENSITIVITY;
            $properties["last_modification_time"] = PR_LAST_MODIFICATION_TIME;
            $properties["status"] = "PT_LONG:PSETID_Task:0x8101";
            $properties["percent_complete"] = "PT_DOUBLE:PSETID_Task:0x8102";
            $properties["startdate"] = "PT_SYSTIME:PSETID_Task:0x8104";
            $properties["duedate"] = "PT_SYSTIME:PSETID_Task:0x8105";
            $properties["reset_reminder"] = "PT_BOOLEAN:PSETID_Task:0x8107";
            $properties["dead_occurrence"] = "PT_BOOLEAN:PSETID_Task:0x8109";
            $properties["datecompleted"] = "PT_SYSTIME:PSETID_Task:0x810f";
            $properties["recurring_data"] = "PT_BINARY:PSETID_Task:0x8116";
            $properties["actualwork"] = "PT_LONG:PSETID_Task:0x8110";
            $properties["totalwork"] = "PT_LONG:PSETID_Task:0x8111";
            $properties["complete"] = "PT_BOOLEAN:PSETID_Task:0x811c";
            $properties["task_f_creator"] = "PT_BOOLEAN:PSETID_Task:0x811e";
            $properties["owner"] = "PT_STRING8:PSETID_Task:0x811f";
            $properties["recurring"] = "PT_BOOLEAN:PSETID_Task:0x8126";

            $properties["reminder_minutes"] = "PT_LONG:PSETID_Common:0x8501";
            $properties["reminder_time"] = "PT_SYSTIME:PSETID_Common:0x8502";
            $properties["reminder"] = "PT_BOOLEAN:PSETID_Common:0x8503";

            $properties["private"] = "PT_BOOLEAN:PSETID_Common:0x8506";
            $properties["contacts"] = "PT_MV_STRING8:PSETID_Common:0x853a";
            $properties["contacts_string"] = "PT_STRING8:PSETID_Common:0x8586";
            $properties["categories"] = "PT_MV_STRING8:PS_PUBLIC_STRINGS:Keywords";

            $properties["commonstart"] = "PT_SYSTIME:PSETID_Common:0x8516";
            $properties["commonend"] = "PT_SYSTIME:PSETID_Common:0x8517";
            $properties["commonassign"] = "PT_LONG:PSETID_Common:0x8518";
            $properties["flagdueby"] = "PT_SYSTIME:PSETID_Common:0x8560";
            $properties["side_effects"] = "PT_LONG:PSETID_Common:0x8510";
            $properties["reminder"] = "PT_BOOLEAN:PSETID_Common:0x8503";
            $properties["reminder_minutes"] = "PT_LONG:PSETID_Common:0x8501";

            $this->proptags = getPropIdsFromStrings($store, $properties);

            parent::__construct($store, $message, $properties);
        }

        /**
         * Function which saves recurrence and also regenerates task if necessary.
         *@param array $recur new recurrence properties
         *@return array of properties of regenerated task else false
         */
        function setRecurrence(&$recur)
        {
            $this->recur = $recur;
            $this->action =& $recur;

            if(!isset($this->recur["changed_occurences"]))
                $this->recur["changed_occurences"] = Array();

            if(!isset($this->recur["deleted_occurences"]))
                $this->recur["deleted_occurences"] = Array();

            if (!isset($this->recur['startocc'])) $this->recur['startocc'] = 0;
            if (!isset($this->recur['endocc'])) $this->recur['endocc'] = 0;

            // Save recurrence because we need proper startrecurrdate and endrecurrdate
            $this->saveRecurrence();

            // Update $this->recur with proper startrecurrdate and endrecurrdate updated after saveing recurrence
            $msgProps = mapi_getprops($this->message, array($this->proptags['recurring_data']));
            $recurring_data = $this->parseRecurrence($msgProps[$this->proptags['recurring_data']]);
            foreach($recurring_data as $key => $value) {
                $this->recur[$key] = $value;
            }

            $this->setFirstOccurrence();

            // Let's see if next occurrence has to be generated
            return $this->moveToNextOccurrence();
        }

        /**
         * Sets task object to first occurrence if startdate/duedate of task object is different from first occurrence
         */
        function setFirstOccurrence()
        {
            // Check if it is already the first occurrence
            if($this->action['start'] == $this->recur["start"]){
                return;
            }else{
                $items = $this->getNextOccurrence();

                $props = array();
                $props[$this->proptags['startdate']] = $items[$this->proptags['startdate']];
                $props[$this->proptags['commonstart']] = $items[$this->proptags['startdate']];

                $props[$this->proptags['duedate']] = $items[$this->proptags['duedate']];
                $props[$this->proptags['commonend']] = $items[$this->proptags['duedate']];

                mapi_setprops($this->message, $props);
            }
        }

        /**
         * Function which creates new task as current occurrence and moves the
         * existing task to next occurrence.
         *
         *@param array $recur $action from client
         *@return boolean if moving to next occurrence succeed then it returns
         *        properties of either newly created task or existing task ELSE
         *        false because that was last occurrence
         */
        function moveToNextOccurrence()
        {
            $result = false;
            /**
             * Every recurring task should have a 'duedate'. If a recurring task is created with no start/end date
             * then we create first two occurrence separately and for first occurrence recurrence has ended.
             */
            if ((empty($this->action['startdate']) && empty($this->action['duedate']))
                || ($this->action['complete'] == 1) || (isset($this->action['deleteOccurrence']) && $this->action['deleteOccurrence'])){

                $nextOccurrence = $this->getNextOccurrence();
                $result = mapi_getprops($this->message, array(PR_ENTRYID, PR_PARENT_ENTRYID, PR_STORE_ENTRYID));

                $props = array();
                if ($nextOccurrence) {
                    if (!isset($this->action['deleteOccurrence'])) {
                        // Create current occurrence as separate task
                        $result = $this->regenerateTask($this->action['complete']);
                    }

                    // Set reminder for next occurrence
                    $this->setReminder($nextOccurrence);

                    // Update properties for next occurrence
                    $this->action['duedate'] = $props[$this->proptags['duedate']] = $nextOccurrence[$this->proptags['duedate']];
                    $this->action['commonend'] = $props[$this->proptags['commonend']] = $nextOccurrence[$this->proptags['duedate']];

                    $this->action['startdate'] = $props[$this->proptags['startdate']] = $nextOccurrence[$this->proptags['startdate']];
                    $this->action['commonstart'] = $props[$this->proptags['commonstart']] = $nextOccurrence[$this->proptags['startdate']];

                    // If current task as been mark as 'Complete' then next occurrence should be uncomplete.
                    if (isset($this->action['complete']) && $this->action['complete'] == 1) {
                        $this->action['status'] = $props[$this->proptags["status"]] = olTaskNotStarted;
                        $this->action['complete'] = $props[$this->proptags["complete"]] = false;
                        $this->action['percent_complete'] = $props[$this->proptags["percent_complete"]] = 0;
                    }

                    $props[$this->proptags["dead_occurrence"]] = false;
                } else {
                    if (isset($this->action['deleteOccurrence']) && $this->action['deleteOccurrence'])
                        return false;

                    // Didn't get next occurrence, probably this is the last one, so recurrence ends here
                    $props[$this->proptags["dead_occurrence"]] = true;
                    $props[$this->proptags["datecompleted"]] = $this->action['datecompleted'];
                    $props[$this->proptags["task_f_creator"]] = true;

                    //OL props
                    $props[$this->proptags["side_effects"]] = 1296;
                    $props[$this->proptags["icon_index"]] = 1280;
                }

                mapi_setprops($this->message, $props);
            }

            return $result;
        }

        /**
         * Function which return properties of next occurrence
         *@return array startdate/enddate of next occurrence
         */
        function getNextOccurrence()
        {
            if ($this->recur) {
                $items = array();

                //@TODO: fix start of range
                $start = isset($this->messageprops[$this->proptags["duedate"]]) ? $this->messageprops[$this->proptags["duedate"]] : $this->action['start'];
                $dayend = ($this->recur['term'] == 0x23) ? 0x7fffffff : $this->dayStartOf($this->recur["end"]);

                // Fix recur object
                $this->recur['startocc'] = 0;
                $this->recur['endocc'] = 0;

                // Retrieve next occurrence
                $items = $this->getItems($start, $dayend, 1);

                return !empty($items) ? $items[0] : false;
            }
        }

        /**
         * Function which clones current occurrence and sets appropriate properties.
         * The original recurring item is moved to next occurrence.
         *@param boolean $markComplete true if existing occurrence has to be mark complete else false.
         */
        function regenerateTask($markComplete)
        {
            // Get all properties
            $taskItemProps = mapi_getprops($this->message);

            if (isset($this->action["subject"])) $taskItemProps[$this->proptags["subject"]] = $this->action["subject"];
            if (isset($this->action["importance"])) $taskItemProps[$this->proptags["importance"]] = $this->action["importance"];
            if (isset($this->action["startdate"])) {
                $taskItemProps[$this->proptags["startdate"]] = $this->action["startdate"];
                $taskItemProps[$this->proptags["commonstart"]] = $this->action["startdate"];
            }
            if (isset($this->action["duedate"])) {
                $taskItemProps[$this->proptags["duedate"]] = $this->action["duedate"];
                $taskItemProps[$this->proptags["commonend"]] = $this->action["duedate"];
            }

            $folder = mapi_msgstore_openentry($this->store, $taskItemProps[PR_PARENT_ENTRYID]);
            $newMessage = mapi_folder_createmessage($folder);

            $taskItemProps[$this->proptags["status"]] = $markComplete ? olTaskComplete : olTaskNotStarted;
            $taskItemProps[$this->proptags["complete"]] = $markComplete;
            $taskItemProps[$this->proptags["percent_complete"]] = $markComplete ? 1 : 0;

            // This occurrence has been marked as 'Complete' so disable reminder
            if ($markComplete) {
                $taskItemProps[$this->proptags["reset_reminder"]] = false;
                $taskItemProps[$this->proptags["reminder"]] = false;
                $taskItemProps[$this->proptags["datecompleted"]] = $this->action["datecompleted"];

                unset($this->action[$this->proptags['datecompleted']]);
            }

            // Recurrence ends for this item
            $taskItemProps[$this->proptags["dead_occurrence"]] = true;
            $taskItemProps[$this->proptags["task_f_creator"]] = true;

            //OL props
            $taskItemProps[$this->proptags["side_effects"]] = 1296;
            $taskItemProps[$this->proptags["icon_index"]] = 1280;

            // Copy recipients
            $recipienttable = mapi_message_getrecipienttable($this->message);
            $recipients = mapi_table_queryallrows($recipienttable, array(PR_ENTRYID, PR_DISPLAY_NAME, PR_EMAIL_ADDRESS, PR_RECIPIENT_ENTRYID, PR_RECIPIENT_TYPE, PR_SEND_INTERNET_ENCODING, PR_SEND_RICH_INFO, PR_RECIPIENT_DISPLAY_NAME, PR_ADDRTYPE, PR_DISPLAY_TYPE, PR_RECIPIENT_TRACKSTATUS, PR_RECIPIENT_TRACKSTATUS_TIME, PR_RECIPIENT_FLAGS, PR_ROWID));

            $copy_to_recipientTable = mapi_message_getrecipienttable($newMessage);
            $copy_to_recipientRows = mapi_table_queryallrows($copy_to_recipientTable, array(PR_ROWID));
            foreach($copy_to_recipientRows as $recipient) {
                mapi_message_modifyrecipients($newMessage, MODRECIP_REMOVE, array($recipient));
            }
            mapi_message_modifyrecipients($newMessage, MODRECIP_ADD, $recipients);

            // Copy attachments
            $attachmentTable = mapi_message_getattachmenttable($this->message);
            if($attachmentTable) {
                $attachments = mapi_table_queryallrows($attachmentTable, array(PR_ATTACH_NUM, PR_ATTACH_SIZE, PR_ATTACH_LONG_FILENAME, PR_ATTACHMENT_HIDDEN, PR_DISPLAY_NAME, PR_ATTACH_METHOD));

                foreach($attachments as $attach_props){
                    $attach_old = mapi_message_openattach($this->message, (int) $attach_props[PR_ATTACH_NUM]);
                    $attach_newResourceMsg = mapi_message_createattach($newMessage);

                    mapi_copyto($attach_old, array(), array(), $attach_newResourceMsg, 0);
                    mapi_savechanges($attach_newResourceMsg);
                }
            }

            mapi_setprops($newMessage, $taskItemProps);
            mapi_savechanges($newMessage);

            // Update body of original message
            $msgbody = mapi_openproperty($this->message, PR_BODY);
            $msgbody = trim($msgbody, "\0");
            $separator = "------------\r\n";

            if (!empty($msgbody) && strrpos($msgbody, $separator) === false) {
                $msgbody = $separator . $msgbody;
                $stream = mapi_openproperty($this->message, PR_BODY, IID_IStream, STGM_TRANSACTED, 0);
                mapi_stream_setsize($stream, strlen($msgbody));
                mapi_stream_write($stream, $msgbody);
                mapi_stream_commit($stream);
            }

            // We need these properties to notify client
            return mapi_getprops($newMessage, array(PR_ENTRYID, PR_PARENT_ENTRYID, PR_STORE_ENTRYID));
        }

        /**
         * processOccurrenceItem, adds an item to a list of occurrences, but only if the
         * resulting occurrence starts or ends in the interval <$start, $end>
         * @param array $items reference to the array to be added to
         * @param date $start start of timeframe in GMT TIME
         * @param date $end end of timeframe in GMT TIME
         * @param date $basedate (hour/sec/min assumed to be 00:00:00) in LOCAL TIME OF THE OCCURRENCE
         */
        function processOccurrenceItem(&$items, $start, $end, $now)
        {
            if ($now > $start) {
                $newItem = array();
                $newItem[$this->proptags['startdate']] = $now;

                // If startdate and enddate are set on task, then slide enddate according to duration
                if (isset($this->messageprops[$this->proptags["startdate"]], $this->messageprops[$this->proptags["duedate"]])) {
                    $newItem[$this->proptags['duedate']] = $newItem[$this->proptags['startdate']] + ($this->messageprops[$this->proptags["duedate"]] - $this->messageprops[$this->proptags["startdate"]]);
                } else {
                    $newItem[$this->proptags['duedate']] = $newItem[$this->proptags['startdate']];
                }

                $items[] = $newItem;
            }
        }

        /**
         * Function which marks existing occurrence to 'Complete'
         *@param array $recur array action from client
         *@return array of properties of regenerated task else false
         */
        function markOccurrenceComplete(&$recur)
        {
            // Fix timezone object
            $this->tz = false;
            $this->action =& $recur;
            $dead_occurrence = isset($this->messageprops[$this->proptags['dead_occurrence']]) ? $this->messageprops[$this->proptags['dead_occurrence']] : false;

            if (!$dead_occurrence) {
                return $this->moveToNextOccurrence();
            }

            return false;
        }

        /**
         * Function which sets reminder on recurring task after existing occurrence has been deleted or marked complete.
         *@param array $nextOccurrence properties of next occurrence
         */
        function setReminder($nextOccurrence)
        {
            $props = array();
            if ($nextOccurrence) {
                // Check if reminder is reset. Default is 'false'
                $reset_reminder = isset($this->messageprops[$this->proptags['reset_reminder']]) ? $this->messageprops[$this->proptags['reset_reminder']] : false;
                $reminder = $this->messageprops[$this->proptags['reminder']];

                // Either reminder was already set OR reminder was set but was dismissed bty user
                if ($reminder || $reset_reminder) {
                    // Reminder can be set at any time either before or after the duedate, so get duration between the reminder time and duedate
                    $reminder_time = isset($this->messageprops[$this->proptags['reminder_time']]) ? $this->messageprops[$this->proptags['reminder_time']] : 0;
                    $reminder_difference = isset($this->messageprops[$this->proptags['duedate']]) ? $this->messageprops[$this->proptags['duedate']] : 0;
                    $reminder_difference = $reminder_difference - $reminder_time;

                    // Apply duration to next calculated duedate
                    $next_reminder_time = $nextOccurrence[$this->proptags['duedate']] - $reminder_difference;

                    $props[$this->proptags['reminder_time']] = $next_reminder_time;
                    $props[$this->proptags['flagdueby']] = $next_reminder_time;
                    $this->action['reminder'] = $props[$this->proptags['reminder']] = true;
                }
            } else {
                // Didn't get next occurrence, probably this is the last occurrence
                $props[$this->proptags['reminder']] = false;
                $props[$this->proptags['reset_reminder']] = false;
            }

            if (!empty($props))
                mapi_setprops($this->message, $props);
        }

        /**
         * Function which recurring task to next occurrence.
         * It simply doesn't regenerate task
         @param array $action
         */
        function deleteOccurrence($action)
        {
            $this->tz = false;
            $this->action = $action;
            $result = $this->moveToNextOccurrence();

            mapi_savechanges($this->message);

            return $result;
        }
    }
