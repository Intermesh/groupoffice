<?php

use go\core\model\Module;
use go\modules\community\tasks\model\Task;
use go\modules\community\tasks\model\Progress;
use go\modules\community\tasks\model\Recurrence;
use go\modules\community\tasks\model\Alert;
use go\core\model\Acl;

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

		$task = Task::findById($id);

		if (!$task) {
			return true;
		} else if($task->getPermissionLevel() < Acl::LEVEL_DELETE){
			throw new StatusException(SYNC_ITEMOPERATIONSSTATUS_DL_ACCESSDENIED);
		} else {
			return $task->delete($task->primaryKeyValues()); // This throws an error when the task is read only
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
	 * @param StringHelper $folderid
	 * @param int $id
	 * @param array $contentparameters
	 * @return \SyncTask
	 */
	public function GetMessage($folderid, $id, $contentparameters) {
		$task = Task::findById($id);
		$message = new SyncTask();
		if ($task) {
			if($task->start) {
				$message->startdate = $task->start->format("U");
				$message->utcstartdate = $this->makeUTCDate($task->start);
			}
			if($task->due) {
				$message->duedate = $task->due->format("U");
				$message->utcduedate = $this->makeUTCDate($task->due);
			}
			$message->complete = in_array($task->getProgress(), [Progress::Completed, Progress::Completed]) ? 1 : 0;
			if($message->complete) $message->datecompleted = $task->progressUpdated->format("U");
			$message->reminderset = empty($task->alerts) ? 0 : 1;
			if($message->reminderset) $message->remindertime = $task->alerts[0]->at($task)->format("U");
			$message->subject = $task->title;
			// GO = [1-9] AS = [0-2]
			$message->importance = $task->priority > 6 ? 2 : ($task->priority < 6 ? 0 : 1) ;

			$bpReturnType = GoSyncUtils::getBodyPreferenceMatch($contentparameters->GetBodyPreference());
			if (Request::GetProtocolVersion() >= 12.0) {
				$message->asbody = GoSyncUtils::createASBodyForMessage($task, 'description', $bpReturnType);
			} else {
				$message->body = \GO\Base\Util\StringHelper::normalizeCrlf($task->description);
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
	 * @param StringHelper $folderid
	 * @param int $id
	 * @param \SyncTask $message
	 * @return array
	 */
	public function ChangeMessage($folderid, $id, $message, $contentParameters) {

		$task = empty($id) ? false : Task::findById($id);

		if (!$task) {
			$tasklist = GoSyncUtils::getUserSettings()->getDefaultTasklist();

			if (!$tasklist)
				ZLog::Write(LOGLEVEL_FATAL, 'ZPUSH2TASK::EXCEPTION ~~ '. "FATAL: No default tasklist configured");
				return;

			$task = new Task();
			$task->tasklist_id = $tasklist->id;
		} else {
			ZLog::Write(LOGLEVEL_DEBUG, "Found task");
		}

		if (isset($message->startdate))
			$task->start = new DateTime($message->startdate);

		if (isset($message->duedate))
			$task->due = new DateTime($message->duedate);

		if (isset($message->datecompleted))
			$task->progressUpdated = new DateTime($message->datecompleted);

		if (isset($message->subject))
			$task->title = $message->subject;

		if (isset($message->importance)) // GO = [1-9] AS = [0-2]
			$task->priority = $message->importance > 1 ? 9 : ($message->importance < 1 ? 1 : 6);

		$task->description = GoSyncUtils::getBodyFromMessage($message);

		if (isset($message->complete)) {
			$task->setProgress($message->complete == '0' ? Progress::NeedsAction : Progress::Completed);
		}

		if ($message->reminderset && $message->remindertime) {
			$alert = new Alert();
			$alert->setTrigger(['when' => gmdate("Ymd\THis\Z", $message->remindertime)]);
			$task->alerts = [$alert];
		}

		if (isset($message->recurrence)) {
			$rrule = Recurrence::fromArray(GoSyncUtils::GenerateRecurrence($message->recurrence));
			$task->start = $message->recurrence->start;
			$task->setRecurrenceRule($rrule->toArray());
		}
		if (isset($message->sensitivity)) {
			switch ($message->sensitivity) {
				case "0": $task->privacy = 'public'; break;
				case "2": $task->privacy = 'private'; break;
				case "3": $task->privacy = 'secret'; break;
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

		return $this->StatMessage($folderid, $task->id);
	}

	/**
	 * Get the status of an item
	 *
	 * @param StringHelper $folderid
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
	 * @param StringHelper $folderid
	 * @param type $cutoffdate
	 * @return array
	 */
	public function GetMessageList($folderid, $cutoffdate) {

		ZLog::Write(LOGLEVEL_DEBUG, "GetMessageList($folderid, $cutoffdate)");

		if (!Module::isInstalled('community', 'tasks'))
			return [];

		return Task::find()
			->select('task.id, UNIX_TIMESTAMP(task.modifiedAt) AS `mod`, "1" AS flags')
			->join("sync_tasklist_user", "u", "u.tasklist_id = task.tasklistId")
			->andWhere('u.user_id', '=', go()->getAuthState()->getUserId())
			->fetchMode(PDO::FETCH_ASSOC)
			->filter(["permissionLevel" => Acl::LEVEL_READ])
			->all();
	}

	/**
	 * Get the syncFolder that is attached to the given id
	 *
	 * @param StringHelper $id
	 * @return \SyncFolder
	 */
	public function GetFolder($id) {

		if ($id != BackendGoConfig::TASKSBACKENDFOLDER) {
			ZLog::Write(LOGLEVEL_WARN, "Task folder '$id' not found");
			return false;
		}

		$folder = new SyncFolder();
		$folder->serverid = $id;
		$folder->parentid = "0";
		$folder->displayname = 'Tasks';
		$folder->type = SYNC_FOLDER_TYPE_TASK;

		return $folder;
	}

	/**
	 * Get a list of folders that are located in the current folder
	 *
	 * @return array
	 */
	public function GetFolderList() {
		$folder = $this->StatFolder(BackendGoConfig::TASKSBACKENDFOLDER);
		return [$folder];
	}

	public function getNotification($folder = null) {
		ZLog::Write(LOGLEVEL_DEBUG, 'goTask->getNotification()');
		Task::entityType()->clearCache();
		return Task::getState();
	}

}
