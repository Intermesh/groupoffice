<?php

class goTask extends GoBaseBackendDiff {

//	public function ChangeFolder($folderid, $oldid, $displayname, $type) {
//
//		ZLog::Write(LOGLEVEL_DEBUG, 'goTask->ChangeFolder(' . $folderid . ',' . $oldid . ',' . $displayname . ', ' . $type . ')');
//
//		//returns stat of the main tasks folder.
//		//this will cause all tasks to be in all created lists on the iphone,
//		//not elegant, but it's the only way to prevent ios7 from crashing.
//		return $this->StatFolder($folderid);
//		//throw new HTTPReturnCodeException("Task folders not supported", 501);
//	}

	public function DeleteMessage($folderid, $id, $contentparameters) {
		ZLog::Write(LOGLEVEL_DEBUG, 'goTask->DeleteMessage(' . $folderid . ',' . $id . ')');

		$task = \GO\Tasks\Model\Task::model()->findByPk($id);

		if ($task && $task->checkPermissionLevel(\GO\Base\Model\Acl::DELETE_PERMISSION)) {
			return $task->delete();
		} else {
			return true;
		}
	}

	/**
	 * Get the item object that needs to be synced to the phone.
	 * This information will be send to the phone.
	 *
	 * Direction: SERVER -> PHONE
	 *
	 * @param StringHelper $folderid
	 * @param int $id
	 * @param array $contentparameters
	 * @return \SyncTask
	 */
	public function GetMessage($folderid, $id, $contentparameters) {

		$task = \GO\Tasks\Model\Task::model()->findByPk($id);

		$message = new SyncTask();

		if ($task) {

			$message->startdate = $task->start_time;
			$message->duedate = $task->due_time;
			$message->complete = ($task->status == "COMPLETED") ? 1 : 0;
			$message->reminderset = empty($task->reminder) ? 0 : 1;

			$startTimeEls = getdate($task->start_time);
			$endTimeFullEls = getdate($task->due_time);

			$message->utcstartdate = gmmktime($startTimeEls['hours'], $startTimeEls['minutes'], $startTimeEls['seconds'], $startTimeEls['mon'], $startTimeEls['mday'], $startTimeEls['year']);
			$message->utcduedate = gmmktime($endTimeFullEls['hours'], $endTimeFullEls['minutes'], $endTimeFullEls['seconds'], $endTimeFullEls['mon'], $endTimeFullEls['mday'], $endTimeFullEls['year']);

			$message->subject = $task->name;
			$message->importance = $task->priority;
			//$message->completion = $task->percentage_complete;

			$bpReturnType = GoSyncUtils::getBodyPreferenceMatch($contentparameters->GetBodyPreference());

			if (Request::GetProtocolVersion() >= 12.0) {
				$message->asbody = GoSyncUtils::createASBodyForMessage($task, 'description', $bpReturnType);
			} else {
				$message->body = \GO\Base\Util\StringHelper::normalizeCrlf($task->description);
				$message->bodysize = strlen($message->body);
				$message->bodytruncated = 0;
			}

			if (!empty($task->completion_time))
				$message->datecompleted = $task->completion_time;

			if (!empty($task->rrule))
				$message->recurrence = GoSyncUtils::exportRecurrence($task);

			if (!empty($task->reminder))
				$message->remindertime = $task->reminder;

			if (!isset($message->body) || strlen($message->body) == 0)
				$message->body = " ";
			// when set the task to complete using the WebAccess, the dateComplete property is not set correctly
			if ($message->complete == 1 && !isset($message->datecompleted))
				$message->datecompleted = time();

//			$message->rtf;
//			$message->categories;
//			$message->sensitivity;
//			$message->regenerate;
//			$message->deadoccur;
		}

		return $message;
	}

	/**
	 * Save the information from the phone to Group-Office.
	 *
	 * Direction: PHONE -> SERVER
	 *
	 * @param StringHelper $folderid
	 * @param int $id
	 * @param \SyncTask $message
	 * @return array
	 */
	public function ChangeMessage($folderid, $id, $message, $contentParameters) {


		try {

			$task = \GO\Tasks\Model\Task::model()->findByPk($id);

			if (!$task) {
//				$tasklist = GoSyncUtils::getUserSettings()->getDefaultTasklist();
//
//				if (!$tasklist)
//					throw new \Exception("FATAL: No default tasklist configured");

				$task = new \GO\Tasks\Model\Task();
				$task->tasklist_id = $folderid;
			}

			if (isset($message->startdate))
				$task->start_time = $message->startdate;

			if (isset($message->duedate))
				$task->due_time = $message->duedate;

			if (isset($message->datecompleted))
				$task->completion_time = $message->datecompleted;

			if (isset($message->subject))
				$task->name = $message->subject;

			if (isset($message->importance))
				$task->priority = $message->importance;

			$task->description = GoSyncUtils::getBodyFromMessage($message);

			if (isset($message->complete) && $message->complete)
				$task->status = \GO\Tasks\Model\Task::STATUS_COMPLETED;

			$task->reminder = isset($message->remindertime) ? $message->remindertime : 0;

			if (isset($message->recurrence)) {
				if (!empty($message->recurrence->until))
					$task->repeat_end_time = $message->recurrence->until;
				$task->start_time = $message->recurrence->start;
				$task->rrule = GoSyncUtils::importRecurrence($message->recurrence, $task->due_time);
			}

			//		$message->utcduedate;
			//    $message->regenerate;
			//    $message->deadoccur;
			//    $message->reminderset;
			//    $message->sensitivity;
			//    $message->utcstartdate;
			//    $message->rtf;
			//    $message->categories;

			$task->cutAttributeLengths();

			// When a task is created on today, then the start time needs to be fixed.
			if ($task->start_time > $task->due_time) {
				$task->start_time = $task->due_time;
			}

			if (!$task->save()) {
				ZLog::Write(LOGLEVEL_WARN, 'ZPUSH2TASK::Could not save ' . $task->id);
				ZLog::Write(LOGLEVEL_WARN, var_export($task->getAttributes('raw'), true));
				ZLog::Write(LOGLEVEL_WARN, var_export($task->getValidationErrors(), true));
				return false;
			}
			$id = $task->id;
		} catch (\Exception $e) {
			ZLog::Write(LOGLEVEL_FATAL, 'ZPUSH2TASK::EXCEPTION ~~ ' . (string) $e);
		}

		return $this->StatMessage($folderid, $id);
	}

	/**
	 * Get the status of an item
	 *
	 * @param StringHelper $folderid
	 * @param int $id
	 * @return array
	 */
	public function StatMessage($folderid, $id) {

		$task = \GO\Tasks\Model\Task::model()->findByPk($id);

		$stat = false;

		if ($task) {
			$stat = array();
			$stat["id"] = $task->id;
			$stat["flags"] = 1;
			$stat["mod"] = $task->mtime;
		}

		return $stat;
	}

	/**
	 * Get the list of the items that need to be synced
	 *
	 * @param StringHelper $folderid
	 * @param type $cutoffdate
	 * @return array
	 */
	public function GetMessageList($folderid, $cutoffdate) {

		$messages = array();
		if (\GO::modules()->tasks) {

			$params = \GO\Base\Db\FindParams::newInstance()
					  ->ignoreAcl()
					  ->select('t.id,t.mtime')
					  ->criteria(
					  	\GO\Base\Db\FindCriteria::newInstance()
									->addCondition('tasklist_id', $folderid)
							    ->addCondition('completion_time', 0)
					  );

//					  ->join(\GO\Sync\Model\UserTasklist::model()->tableName(), \GO\Base\Db\FindCriteria::newInstance()
//					  ->addCondition('tasklist_id', 's.tasklist_id', '=', 't', true, true)
//					  ->addCondition('user_id', \GO::user()->id, '=', 's')
//					  , 's');

			if (!empty($cutoffdate)) {
				ZLog::Write(LOGLEVEL_DEBUG, 'Client sent cutoff date for tasks: ' . \GO\Base\Util\Date::get_timestamp($cutoffdate));

				$params->getCriteria()->mergeWith(\GO\Base\Db\FindCriteria::newInstance()
					->addCondition('due_time', $cutoffdate, '>=')
				);
			}

			$stmt = \GO\Tasks\Model\Task::model()->find($params);

			while ($task = $stmt->fetch()) {
				$message = array();
				$message['id'] = $task->id;
				$message['flags'] = 1;
				$message['mod'] = $task->mtime;
				$messages[] = $message;
			}
		}

		return $messages;
	}

	 public function ChangeFolder($folderid, $oldid, $displayname, $type)
	 {
		if(!empty($oldid)) {

		  //remove t/ from the folder ? Shouldn't this already have been done by the combined backend wrapper?
		  $oldid = substr($oldid, 2);

		  $tasklist = \GO\Tasks\Model\Tasklist::model()->findByPk($oldid);
		  if(!$tasklist) {
			  ZLog::Write(LOGLEVEL_DEBUG, "Tasklist with $oldid not found");
			  return false;
		  }
		} else{
		  $tasklist = new \GO\Tasks\Model\Tasklist();
		}

		$tasklist->name = $displayname;
		if(!$tasklist->save()) {
		 ZLog::Write(LOGLEVEL_DEBUG, "Tasklist with $displayname could not be created");
		 return false;
		}

		if(empty($oldid)) {
		 $ut = new \GO\Sync\Model\UserTasklist();
		 $ut->user_id = GO::user()->id;
		 $ut->tasklist_id = $tasklist->id;

		 if(!$ut->save()) {
			 ZLog::Write(LOGLEVEL_DEBUG, "Tasklist with $displayname could not be added to sync profile");
			 return false;
		 }
		}

		return $this->StatFolder($tasklist->id);
	 }

	/**
	 * Get the syncFolder that is attached to the given id
	 *
	 * @param StringHelper $id
	 * @return \SyncFolder
	 */
	public function GetFolder($id) {

		$tasklist = \GO\Tasks\Model\Tasklist::model()->findByPk($id);
		if(!$tasklist) {
			return false;
		}

		$folder = new SyncFolder();
		$folder->serverid = $id;
		$folder->parentid = "0";
		$folder->displayname = $tasklist->name;
		$folder->type = SYNC_FOLDER_TYPE_TASK;

		return $folder;
	}

	/**
	 * Get a list of folders that are located in the current folder
	 *
	 * @return array
	 */
	public function GetFolderList() {
		$folders = array();

		$params = \GO\Base\Db\FindParams::newInstance()
			->ignoreAcl()
			->join(\GO\Sync\Model\UserTasklist::model()->tableName(), \GO\Base\Db\FindCriteria::newInstance()
				->addCondition('id', 's.tasklist_id', '=', 't', true, true)
				->addCondition('user_id', \GO::user()->id, '=', 's')
				, 's');

		$tasklists = \GO\Tasks\Model\Tasklist::model()->find($params);
		foreach($tasklists as $tasklist) {
			$folder = $this->StatFolder($tasklist->id);

			$folders[] = $folder;
		}

		return $folders;
	}

	public function getNotification($folder = null) {

		$params = \GO\Base\Db\FindParams::newInstance()
				  ->ignoreAcl()
				  ->single(true, true)
				  ->select('count(*) AS count, max(mtime) AS lastmtime')
					->criteria(\GO\Base\Db\FindCriteria::newInstance()->addCondition('tasklist_id', $folder));
//				  ->join(\GO\Sync\Model\UserTasklist::model()->tableName(), \GO\Base\Db\FindCriteria::newInstance()
//				  ->addCondition('tasklist_id', 's.tasklist_id', '=', 't', true, true)
//				  ->addCondition('user_id', \GO::user()->id, '=', 's')
//				  , 's');

		$record = \GO\Tasks\Model\Task::model()->find($params);

		$lastmtime = isset($record->lastmtime) ? $record->lastmtime : 0;
		$newstate = 'M' . $lastmtime . ':C' . $record->count;

		ZLog::Write(LOGLEVEL_DEBUG, 'goTask->getNotification() State: ' . $newstate);

		return $newstate;
	}

}
