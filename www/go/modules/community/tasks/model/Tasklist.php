<?php
/**
 * @copyright (c) 2019, Intermesh BV http://www.intermesh.nl
 * @author Michael de Hart <mdhart@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

namespace go\modules\community\tasks\model;

use go\core\acl\model\AclOwnerEntity;
use go\core\db\Criteria;
use go\core\model\Acl;
use go\core\orm\Property;
use go\core\orm\Query;
use GO\Projects2\Model\ProjectEntity;

/**
 * Tasklist model
 */
class Tasklist extends AclOwnerEntity
{

	const List = 1;
	const Board = 2;
	const Project = 3;

	const Roles = [
		self::List => 'list',
		self::Board => 'board',
		self::Project => 'project'
	];

	/** @var int */
	public $id;

	/** @var string */
	public $name;

	/** @var string What kind of list: 'list', 'board' */
	protected $role = self::List;

	public function getRole() {
		return self::Roles[$this->role] ?? 'list';
	}

	/**
	 *
	 * @param string $value ['list'|'board'|'project']
	 */
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

	public $projectId = null;

	protected static function defineFilters()
	{
		return parent::defineFilters()
			->add('role', function (Criteria $criteria, $value) {
				$roleID = array_search($value, self::Roles, true);
				$criteria->where(['role' => $roleID]);
			})
			->add('projectId', function (Criteria $criteria, $value) {

				$criteria->where(['projectId' => $value]);
			});

	}

	protected static function textFilterColumns()
	{
		return ['name'];
	}

	protected static function defineMapping()
	{
		return parent::defineMapping()
			->addTable("tasks_tasklist", "tasklist")
			->addUserTable('tasks_tasklist_user', "ut", ['id' => 'tasklistId'])
			->addArray('groups', TasklistGroup::class, ['id' => 'tasklistId'], ['orderBy'=>'sortOrder']);
	}

	protected function internalSave()
	{
		if ($this->isNew()) {

			if($this->role == self::Board) {
				if (empty($this->groups)) {

					$this->setValue('groups', [
						'#1' => ['name' => go()->t('In progress', 'community', 'tasks'), 'progressChange' => Progress::$db[Progress::InProcess]],
						'#2' => ['name' => go()->t('Completed', 'community', 'tasks'), 'progressChange' => Progress::$db[Progress::Completed]]
					]);
				}
			}

			//If this tasklist is for a project then take over the ACL
			if(isset($this->projectId)) {
				$project = ProjectEntity::findById($this->projectId, ['id', 'acl_id']);
				$this->aclId = $project->acl_id;
			}
		}
		return parent::internalSave();
	}


	/**
	 * Create a task list for a project and return its id
	 *
	 * @param int $projectId
	 * @return int
	 * @throws \Exception
	 */
	public static function createForProject(int $projectId) :int
	{
		$project = ProjectEntity::findById($projectId, ['id', 'name', 'acl_id']);

		$tasklist = new self();
		$tasklist->setRole('project');
		$tasklist->setValues([
			'name' => go()->t('Tasklist', 'community','tasks') . ' ' . $project->name,
			'createdBy' => go()->getUserId(),
			'aclId' => $project->acl_id,
			'projectId' => $projectId
		]);
		$tasklist->save();
		return $tasklist->id;

	}
}