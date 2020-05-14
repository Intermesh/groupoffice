<?php

namespace go\modules\community\tasks\convert;

use GO;
use go\core\data\convert;
use go\modules\community\tasks\model\Task;

class Csv extends convert\Csv {	

	/**
	 * List headers to exclude
	 * @var string[]
	 */
	public static $excludeHeaders = ['recurrenceRule'];
	
	protected function init() {
		$this->addColumn('rrule', 'rrule', false);		
	}

	protected function exportRrule(Task $task) {
		return json_encode($task->getRecurrenceRule());
	}

	protected function importRrule(Task $task, $value, array $values) {
		if(!empty($value)) {
			$task->setRecurrenceRule(json_decode($value));
		}
	}
}
