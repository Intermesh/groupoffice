<?php


namespace go\modules\community\tasks\model;

use go\core\db\Criteria;
use go\core\jmap\Entity;
use go\core\orm\Filters;
use go\core\orm\Mapping;

class TaskListGrouping extends Entity
{
	/** @var int PK */
	public $id;

	/** @var string Column name */
	public $name;

	protected $order;

	protected int $role = TaskList::List;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("tasks_tasklist_grouping", "g");
	}

	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()
			->add('role', function(Criteria $criteria, $value) {
				$roleID = array_search($value, TaskList::Roles, true);
				$criteria->where('role', '=', $roleID);
			}, 'list');
	}


	/** @var string What kind of list: 'list', 'board' */
	public function getRole() : string {
		return TaskList::Roles[$this->role] ?? 'list';
	}

	/**
	 *
	 * @param string $value ['list'|'board'|'project']
	 */
	public function setRole(string $value) {
		$key = array_search($value, TaskList::Roles, true);
		if($key === false) {
			$this->setValidationError('role', 10, 'Incorrect role value for tasklist');
		} else
			$this->role = $key;
	}
}