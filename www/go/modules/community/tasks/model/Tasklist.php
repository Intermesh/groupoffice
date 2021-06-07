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

	protected static function defineFilters()
	{
		return parent::defineFilters()
			->add('role', function (Criteria $criteria, $value) {
				$roleID = array_search($value, self::Roles, true);
				$criteria->where(['role' => $roleID]);
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
		if ($this->isNew() && $this->role == self::Board) {
			if (empty($this->groups)) {

				$this->setValue('groups', [
					'#1' => ['name' => go()->t('In progress','community', 'tasks'), 'progressChange' => Progress::$db[Progress::InProcess]],
					'#2' => ['name' => go()->t('Completed','community', 'tasks'), 'progressChange' => Progress::$db[Progress::Completed]]
				]);
			}
		}
		return parent::internalSave();
	}

	public static function saveForProject(int $projectId)
	{
		$id = self::find(['id'])
			->selectSingleValue("tasklist.id")
			->filter(['projectId' => $projectId])
			->single();

		if (!empty($id)) {
			go()->getDebugger()->debug('tasklist ' . $id . ' found for project ' . $projectId);
		} else {
			$project = ProjectEntity::findById($projectId, ['name', 'user_id', 'acl_id']);

			// Create a new tasklist record
			$tl = new Tasklist();
			$tl->name = go()->t('Task list') . ' ' . $project->name;
			$tl->setRole('project');
			$tl->createdBy = go()->getUserId();
			$tl->ownerId = $project->user_id;
			$tl->aclId = $project->acl_id;
			$tl->save();
			if (!go()->getDbConnection()->insert('pr2_project_tasklist', ['tasklist_id' => $tl->id, 'project_id' => $projectId])->execute()) {
				throw new \Exception("could not save project tasklist pivot table");
			}

		}

	}

}