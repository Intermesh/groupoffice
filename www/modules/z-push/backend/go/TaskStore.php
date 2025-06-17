<?php

use go\core\model\Acl;
use go\core\model\Module;
use go\core\util\Recurrence;
use go\modules\community\tasks\model\Alert;
use go\modules\community\tasks\model\Progress;
use go\modules\community\tasks\model\Task;
use go\modules\community\tasks\model\TaskList;

class TaskStore extends Store {

	public function DeleteMessage($folderid, $id, $contentparameters) {
		ZLog::Write(LOGLEVEL_DEBUG, 'goTask->DeleteMessage(' . $folderid . ',' . $id . ')');

		if(!go()->getAuthState()->getUser(['syncSettings'])->syncSettings->allowDeletes) {
			ZLog::Write(LOGLEVEL_DEBUG, 'Deleting by sync is disabled in user settings');
			throw new StatusException("Access denied", SYNC_ITEMOPERATIONSSTATUS_DL_ACCESSDENIED);
		}

		$task = Task::findById($id);

		if (!$task) {
			return true;
		} else if($task->getPermissionLevel() < Acl::LEVEL_DELETE){
			throw new StatusException("Access denied", SYNC_ITEMOPERATIONSSTATUS_DL_ACCESSDENIED);
		} else {
			try {
				return Task::delete($task->primaryKeyValues());
			} catch (Exception $e) {
				ZLog::Write(LOGLEVEL_FATAL, 'Task::EXCEPTION ~~ ' .  $e->getMessage());
				ZLog::Write(LOGLEVEL_DEBUG, $e->getTraceAsString());
				throw new StatusException("Access denied", SYNC_ITEMOPERATIONSSTATUS_DL_ACCESSDENIED);
			}
		}
	}


	/**
	 * Move a mailmessage to an other folder.
	 *
	 * @param string $folderid
	 * @param int $id
	 * @param string $newfolderid
	 * @return boolean
	 */
	public function MoveMessage($folderid, $id, $newfolderid, $contentparameters) {

		try {
			ZLog::Write(LOGLEVEL_DEBUG, "goTask::MoveMessage($folderid, $id, $newfolderid)");

			$task = Task::findById($id);

			if (!$task) {
				ZLog::Write(LOGLEVEL_WARN, "Task not found with id = " . $id . " in folder " . $folderid);

				return false;
			} else if ($task->getPermissionLevel() < Acl::LEVEL_DELETE) {
				throw new StatusException("Access denied", SYNC_ITEMOPERATIONSSTATUS_DL_ACCESSDENIED);
			}

			$task->tasklistId = $newfolderid;

			if (!$task->save()) {
				ZLog::Write(LOGLEVEL_WARN, "Task not saved with id = " . $id . " in folder " . $folderid);

				return false;
			}

			// required for not duplicating events on iphone!
			return $task->id . "";
		}catch (Exception $e) {
			ZLog::Write(LOGLEVEL_FATAL, 'Task::EXCEPTION ~~ ' .  $e->getMessage());
			ZLog::Write(LOGLEVEL_DEBUG, $e->getTraceAsString());
			return false;
		}

	}

	private function makeUTCDate($date) {
		return $date->format('U'); // todo in UTC
	}

	/**
	 * Get the item object that needs to be synced to the phone.
	 * This information will be send to the phone.
	 *
	 * Direction: SERVER -> PHONE
	 *
	 * @param string $folderid
	 * @param int $id
	 * @param SyncParameters $contentparameters
	 * @return \SyncTask
	 * @throws JsonException
	 */
	public function GetMessage($folderid, $id, $contentparameters) {
		$task = Task::findById($id);
		$message = new SyncTask();
		if ($task) {
			$message->uid = $task->getUid();

			if($task->start) {
				$message->startdate = $task->start->format("U");
				$message->utcstartdate = $this->makeUTCDate($task->start);
			}
			if($task->due) {
				$message->duedate = $task->due->format("U");
				$message->utcduedate = $this->makeUTCDate($task->due);
			}

			$message->complete = $task->getProgress() == Progress::$db[Progress::Completed] ? 1 : 0;

			if($message->complete) {
				$message->datecompleted = $task->progressUpdated ? $task->progressUpdated->format("U") : $task->modifiedAt->format("U");
			}

			$message->reminderset = empty($task->alerts) ? 0 : 1;
			if($message->reminderset) {
				$firstAlert = reset($task->alerts);
				$message->remindertime = $firstAlert->at()->format("U");
			}

			$message->subject = $task->title;
			// GO = [1-9] AS = [0-2]
			$message->importance = $task->priority > 5 && $task->priority != 0 ? 0 : ($task->priority < 5  && $task->priority != 0 ? 2 : 1) ;

			$bpReturnType = GoSyncUtils::getBodyPreferenceMatch($contentparameters->GetBodyPreference());
			if (Request::GetProtocolVersion() >= 12.0) {
				$message->asbody = GoSyncUtils::createASBodyForMessage($task, 'description', $bpReturnType);
			} else {
				$message->body = \go\core\util\StringUtil::normalizeCrlf($task->description);
				$message->bodysize = strlen($message->body);
				$message->bodytruncated = 0;
			}
			switch ($task->privacy) {
				case "public": $message->sensitivity = "0"; break;
				case "private":$message->sensitivity = "2"; break;
				case "secret": $message->sensitivity = "3"; break;
			}

			$rule = $task->getRecurrenceRule();
			if(!empty($rule)) {
				$message->recurrence = GoSyncUtils::ParseRecurrence(Recurrence::fromArray((array)$rule, $task->start)->toString(), 'task');
			}

			// HACKS
			if (!isset($message->body) || strlen($message->body) == 0)
				$message->body = " ";
			// when set the task to complete using the WebAccess, the dateComplete property is not set correctly
			if ($message->complete == 1 && !isset($message->datecompleted))
				$message->datecompleted = time();
		}
		return $message;
	}

	/**
	 * Save the information from the phone to Group-Office.
	 *
	 * Direction: PHONE -> SERVER
	 *
	 * @param string $folderid
	 * @param int $id
	 * @param \SyncTask $message
	 * @return array
	 */
	public function ChangeMessage($folderid, $id, $message, $contentParameters) {

		try {
			$task = empty($id) ? false : Task::findById($id);

			if (!$task) {
				$task = new Task();
				$task->tasklistId = $folderid;
			} else {
				ZLog::Write(LOGLEVEL_DEBUG, "Found task");
			}

			if (isset($message->uid))
				$task->setUid($message->uid);

			if (isset($message->startdate))
				$task->start = new DateTime("@" . $message->startdate);

			if (isset($message->duedate))
				$task->due = new DateTime("@" . $message->duedate);

			if (isset($message->datecompleted))
				$task->progressUpdated = new DateTime("@" . $message->datecompleted);

			if (isset($message->subject))
				$task->title = $message->subject;

			if (isset($message->importance)) // GO = [1-9] AS = [0-2]
				$task->priority = $message->importance > 1 ? Task::PRIORITY_HIGH : ($message->importance < 1 ? Task::PRIORITY_LOW : Task::PRIORITY_NORMAL);

			$task->description = GoSyncUtils::getBodyFromMessage($message);

			if (isset($message->complete)) {
				$task->setProgress($message->complete == '0' ? Progress::$db[Progress::NeedsAction] : Progress::$db[Progress::Completed]);
			}

			if ($message->reminderset && $message->remindertime) {
				$alert = new Alert($task);
				$alert->when(new DateTime("@" . $message->remindertime));
				$task->alerts = [$alert];
			}

			if (isset($message->recurrence)) {
				$task->start = new DateTime('@'.$message->recurrence->start);
				$rrule = Recurrence::fromArray(GoSyncUtils::GenerateRecurrence($message->recurrence), $task->start);
				$task->setRecurrenceRule($rrule->toArray());
			}
			if (isset($message->sensitivity)) {
				switch ($message->sensitivity) {
					case "0":
						$task->privacy = 'public';
						break;
					case "2":
						$task->privacy = 'private';
						break;
					case "3":
						$task->privacy = 'secret';
						break;
				}
			}

			//		$message->utcduedate;
			//    $message->regenerate;
			//    $message->deadoccur;
			//    $message->reminderset;
			//    $message->sensitivity;
			//    $message->utcstartdate;
			//    $message->rtf;
			//    $message->categories;

			// When a task is created on today, then the start time needs to be fixed.
			if ($task->start > $task->due) {
				$task->start = $task->due;
			}

			if (!$task->save()) {
				ZLog::Write(LOGLEVEL_WARN, 'ZPUSH2TASK::Could not save ' . $task->id);
				ZLog::Write(LOGLEVEL_WARN, var_export($task->getValidationErrors(), true));
				return false;
			}
		}catch (Exception $e) {
			ZLog::Write(LOGLEVEL_FATAL, 'Task::EXCEPTION ~~ ' .  $e->getMessage());
			ZLog::Write(LOGLEVEL_DEBUG, $e->getTraceAsString());
		}

		return $this->StatMessage($folderid, $task->id);
	}

	/**
	 * Get the status of an item
	 *
	 * @param string $folderid
	 * @param int $id
	 * @return array
	 */
	public function StatMessage($folderid, $id) {
		$task = Task::findById($id);
		return $task ? [
			'id' => $task->id,
			'flags' => '1',
			'mod' => $task->modifiedAt->format("U")
		] : false;
	}

	/**
	 * Get the list of the items that need to be synced
	 *
	 * @param string $folderid
	 * @param type $cutoffdate
	 * @return array
	 */
	public function GetMessageList($folderid, $cutoffdate) {

		ZLog::Write(LOGLEVEL_DEBUG, "GetMessageList($folderid, $cutoffdate)");

		if (!Module::isInstalled('community', 'tasks'))
			return [];

		$tasks =  Task::find()
			->select('task.id, UNIX_TIMESTAMP(task.modifiedAt) AS `mod`, "1" AS flags')
			->fetchMode(PDO::FETCH_ASSOC)
			->filter(["permissionLevel" => Acl::LEVEL_READ, "tasklistId" => $folderid]);

		if (!empty($cutoffdate)) {
			ZLog::Write(LOGLEVEL_DEBUG, 'Client sent cutoff date for tasks: ' . \GO\Base\Util\Date::get_timestamp($cutoffdate));

			$tasks->where(
				(new \go\core\db\Criteria())
					->where('due', new DateTime('@'. $cutoffdate), '>=')
					->orWhere('due', '=',null));
		}

		return $tasks->all();
	}

	public function ChangeFolder($folderid, $oldid, $displayname, $type)
	{
		ZLog::Write(LOGLEVEL_DEBUG, "ChangeFolder($folderid, $oldid, $displayname, $type)");

		if(!empty($oldid)) {

			//remove t/ from the folder ? Shouldn't this already have been done by the combined backend wrapper?
			$oldid = substr($oldid, 2);

			$tasklist = TaskList::findById($oldid);
			if(!$tasklist) {
				ZLog::Write(LOGLEVEL_DEBUG, "Tasklist with $oldid not found");
				return false;
			}
		} else{
			$tasklist = new TaskList();
		}

		$tasklist->name = $displayname;
		if(!$tasklist->save()) {
			ZLog::Write(LOGLEVEL_DEBUG, "Tasklist with $displayname could not be created");
			return false;
		}

		if(empty($oldid)) {
			if(!go()->getDbConnection()->insert('sync_tasklist_user', ['userId' => go()->getUserId(), 'tasklistId' => $tasklist->id])->execute()) {
				ZLog::Write(LOGLEVEL_DEBUG, "Tasklist with $displayname could not be added to sync profile");
				return false;
			}
		}

		return $this->StatFolder($tasklist->id);
	}

	/**
	 * Get the syncFolder that is attached to the given id
	 *
	 * @param string $id
	 * @return \SyncFolder
	 */
	public function GetFolder($id) {
		ZLog::Write(LOGLEVEL_DEBUG, "GetFolder($id)");

		$tasklist = TaskList::findById($id);
		if(!$tasklist || !$tasklist->hasPermissionLevel(Acl::LEVEL_READ)) {
			ZLog::Write(LOGLEVEL_WARN, "GetFolder($id) not found or no permissions");
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

		$tasklists = TaskList::find()
			->selectSingleValue('tasklist.id')
			->andWhere('role', '=',1)
			->andWhere('isSubscribed', '=', 1);

		foreach($tasklists as $tasklistId) {
			$folder = $this->StatFolder($tasklistId);

			$folders[] = $folder;
		}

		return $folders;
	}


	public function getNotification($folder = null) {

		$stmt = Task::find()
				->removeJoins()
				->fetchMode(PDO::FETCH_ASSOC)
				->select('COALESCE(count(*), 0) AS count, COALESCE(max(modifiedAt), 0) AS modifiedAt')
				->where('tasklistId = :tasklistId')
				->createStatement();

		$stmt->bindValue(':tasklistId', $folder, PDO::PARAM_INT);
		$stmt->execute();
		$record = $stmt->fetch();

		$newstate = $record ? 'M'.$record['modifiedAt'].':C'.$record['count'] : "M0:C0";

		ZLog::Write(LOGLEVEL_DEBUG,'goTask->getNotification('.$folder.') State: '.$newstate);


		return $newstate;
	}

}
