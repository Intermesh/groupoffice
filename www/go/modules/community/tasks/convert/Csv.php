<?php

namespace go\modules\community\tasks\convert;

use GO;
use go\core\data\convert;
use go\modules\community\tasks\model\Task;

class Csv extends convert\Spreadsheet {

	/**
	 * List headers to exclude
	 * @var string[]
	 */
	public static $excludeHeaders = ['recurrenceRule'];
	
	protected function init() {
		$this->addColumn('recurrenceRule', go()->t('Recurrence'));
	}

	protected function exportRecurrenceRule(Task $task) {
		return json_encode($task->getRecurrenceRule());
	}

	protected function importRecurrenceRule(Task $task, $value, array $values) {
		if(!empty($value)) {
			$task->setRecurrenceRule(json_decode($value));
		}
	}
}
