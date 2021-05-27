<?php


namespace go\modules\community\tasks\model;

use go\core\orm\Property;
use go\core\validate\ErrorCode;

class TasklistGroup extends Property
{
	/** @var int PK */
	public $id;

	/** @var int FK to tasklist this column belongs to */
	protected $tasklistId;

	/** @var string Column name */
	public $name;

	/** @var string 6 char hex code */
	public $color;

	protected $sortOrder;

	/** @var Progress if set the progress of a task will change when the task goes into this column */
	protected $progressChange;

	protected static function defineMapping()
	{
		return parent::defineMapping()
			->addTable("tasks_tasklist_group", "group");
	}

	public function getProgressChange() {
		return $this->progressChange ? Progress::$db[$this->progressChange] : null;
	}

	public function setProgressChange($value) {
		if($value == null) {
			$this->progressChange = null;
			return;
		}


		$key = array_search($value, Progress::$db, true);
		if($key === false) {
			$this->setValidationError('progress', ErrorCode::INVALID_INPUT, 'Incorrect Progress value for task: ' . $value);
		} else
			$this->progressChange = $key;
	}
}