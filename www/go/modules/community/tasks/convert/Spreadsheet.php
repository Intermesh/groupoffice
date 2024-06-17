<?php

namespace go\modules\community\tasks\convert;

use GO;
use go\core\data\convert;
use go\modules\community\tasks\model\Task;
use go\modules\community\tasks\model\TaskList;

class Spreadsheet extends convert\Spreadsheet {

	/**
	 * List headers to exclude
	 * @var string[]
	 */
	public static $excludeHeaders = ['recurrenceRule'];
	
	protected function init() {
		$this->addColumn('recurrenceRule', go()->t('Recurrence', 'community', 'tasks'));
		$this->addColumn('list', go()->t('List', 'community', 'tasks'));
	}

	protected function exportRecurrenceRule(Task $task) {
		return json_encode($task->getRecurrenceRule());
	}

	protected function importRecurrenceRule(Task $task, $value, array $values) {
		if(!empty($value)) {
			$task->setRecurrenceRule(json_decode($value));
		}
	}


	protected function exportList(Task $task) {
		$tasklist = TaskList::findById($task->tasklistId, ['name']);
		return $tasklist->name;
	}

	protected function importList(Task $task, $value, array $values) {
		$tasklist = TaskList::find(['id'])->where('name', '=', $value);
		if($tasklist) {
			$task->tasklistId = $tasklist->id;
		}
	}
}
