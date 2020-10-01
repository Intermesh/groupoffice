<?php
/**
 * @copyright (c) 2019, Intermesh BV http://www.intermesh.nl
 * @author Michael de Hart <mdhart@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

namespace go\modules\community\tasks\model;

use go\core\acl\model\AclOwnerEntity;

/**
 * Tasklist model
 */
class Tasklist extends AclOwnerEntity
{

	const Board = 2;

	const Roles = [
		1 => 'list',
		2 => 'board',
		3 => 'project'
	];

	/** @var int */
	public $id;

	/** @var string */
	public $name;

	/** @var string What kind of list: 'list', 'board' */
	protected $role;

	public function getRole() {
		return self::Roles[$this->role];
	}

	public function setRole($value) {
		$key = array_search($value, self::Roles, true);
		if($key === false) {
			$this->setValidationError('role', 10, 'Incorrect role value for tasklist');
		} else
			$this->role = $key;
	}

	/** @var string if a longer description then name s needed */
	public $description;

	/** @var int */
	public $createdBy;

	/** @var int */
	public $ownerId;

	/** @var int */
	public $aclId;

	/** @var int */
	public $version = 1;

	public $groups = [];

	protected static function defineMapping()
	{
		return parent::defineMapping()
			->addTable("tasks_tasklist", "tasklist")
			->addUserTable('tasks_tasklist_user', "ut", ['id' => 'tasklistId'])
			->addArray('groups', TasklistGroup::class, ['id' => 'tasklistId'], ['orderBy'=>'sortOrder']);
	}

	protected function internalSave()
	{
		if ($this->isNew() && $this->role == self::Board) {
			if (empty($this->groups)) {

				$this->setValue('groups', [
					'#1' => ['name' => go()->t('Todo')],
					'#2' => ['name' => go()->t('Finished'), 'sortOrder' => 1, 'progressChange' => Progress::Completed]
				]);
			}
		}
		return parent::internalSave();
	}

}