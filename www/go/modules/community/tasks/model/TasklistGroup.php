<?php


namespace go\modules\community\tasks\model;

use go\core\orm\Property;

class TasklistGroup extends Property
{
	/** @var int PK */
	public $id;

	/** @var int FK to tasklist this column belongs to */
	public $tasklistId;

	/** @var string Column name */
	public $name;

	/** @var string 6 char hex code */
	public $color;

	protected $sortOrder;

	/** @var Progress if set the progress of a task will change when the task goes into this column */
	public $progressChange;

	protected static function defineMapping()
	{
		return parent::defineMapping()
			->addTable("tasks_tasklist_group", "group");
	}
}