<?php


namespace go\modules\community\tasks\model;

use go\core\jmap\Entity;
use go\core\orm\Mapping;

class TaskListGrouping extends Entity
{
	/** @var int PK */
	public $id;

	/** @var string Column name */
	public $name;

	protected $order;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("tasks_tasklist_grouping", "g");
	}
}