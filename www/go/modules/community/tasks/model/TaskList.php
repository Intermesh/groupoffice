<?php
/**
 * @copyright (c) 2019, Intermesh BV http://www.intermesh.nl
 * @author Michael de Hart <mdhart@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

namespace go\modules\community\tasks\model;

use Exception;
use go\core\acl\model\AclOwnerEntity;
use go\core\db\Criteria;
use go\core\model\Acl;
use go\core\model\Module;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Property;
use go\core\orm\Query;
use go\core\orm\Relation;
use go\core\util\ArrayObject;
use go\core\util\Color;
use GO\Projects2\Model\ProjectEntity;

/**
 * Tasklist model
 */
class TaskList extends AclOwnerEntity
{
	const UserProperties = ['color', 'sortOrder', 'isVisible', 'isSubscribed', 'syncToDevice'];

	const List = 1;
	const Board = 2;
	const Project = 3;
	const Support = 4;

	const Roles = [
		self::List => 'list',
		self::Board => 'board',
		self::Project => 'project',
		self::Support => 'support'
	];

	public ?string $id;
	public string $name;

	public int $aclId;

	protected int $role = self::List;

	/** if a longer description then name s needed */
	public ?string $description = null;

	public ?int $createdBy;
	public ?int $ownerId;

	protected string $defaultColor;

	public ?string $color = null;
	public ?int $sortOrder = null;
	public ?bool $isVisible= null;
	public ?bool $isSubscribed= null;
	public ?bool $syncToDevice = true;

	protected $highestItemModSeq;

	public $groups = [];

	public $projectId = null;

	public $groupingId = null;

	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()->add('isSubscribed', function(Criteria $criteria, $value, Query $query) {
			$criteria->where('isSubscribed','=', $value);
				if($value === false) {
					$criteria->orWhere('isSubscribed', 'IS', null);
				}
			})
			->add('role', function (Criteria $criteria, $value) {
				$roleID = array_search($value, self::Roles, true);
				$criteria->where(['role' => $roleID]);
			})
			->add('projectId', function (Criteria $criteria, $value) {
				$criteria->where(['projectId' => $value]);
			});

	}

	/** @var string What kind of list: 'list', 'board' */
	public function getRole() : string {
		return self::Roles[$this->role] ?? 'list';
	}

	/**
	 *
	 * @param string $value ['list'|'board'|'project']
	 */
	public function setRole(string $value) {
		$key = array_search($value, self::Roles, true);
		if($key === false) {
			$this->setValidationError('role', 10, 'Incorrect role value for tasklist');
		} else
			$this->role = $key;
	}

	protected static function textFilterColumns(): array
	{
		return ['name'];
	}

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("tasks_tasklist", "tasklist")
			->addUserTable('tasks_tasklist_user', "ut", ['id' => 'tasklistId'], self::UserProperties)
			->add('groups', Relation::array(TaskListGroup::class, 'sortOrder')->keys(['id' => 'tasklistId']));
	}

	protected function internalSave(): bool
	{
		if(!isset($this->color)) {
			$this->color = trim(Color::background(), '#');
		}
		if($this->isNew()) {
			$this->isSubscribed = true; // auto subscribe the creator.
			$this->isVisible = true;
			$this->defaultColor = $this->color;

			if($this->role == self::Board) {
				if (empty($this->groups)) {

					$this->setValue('groups', [
						['name' => go()->t('In progress', 'community', 'tasks'), 'progressChange' => Progress::$db[Progress::InProcess]],
						['name' => go()->t('Completed', 'community', 'tasks'), 'progressChange' => Progress::$db[Progress::Completed]]
					]);
				}
			} elseif($this->role == self::Support) {

			}

			//If this tasklist is for a project then take over the ACL
			if(isset($this->projectId)) {
				$project = ProjectEntity::findById($this->projectId, ['id', 'acl_id']);
				$this->aclId = $project->acl_id;
			}
		} else if($this->ownerId === go()->getUserId() && !empty($this->color)) {
			$this->defaultColor = $this->color;
		}
		if(empty($this->color)) {
			$this->color = $this->defaultColor;
		}
		return parent::internalSave();
	}


	/**
	 * Create a list for a project and return its id
	 *
	 * @param int $projectId
	 * @return TaskList
	 * @throws Exception
	 */
	public static function createForProject(int $projectId) :TaskList
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
		return $tasklist;

	}

	protected function canCreate(): bool
	{
		return Module::findByName('community', 'tasks')
			->getUserRights()->mayChangeTasklists;
	}

	protected static function checkAclJoinEntityTable()
	{
		return (new Query())
			->join("tasks_tasklist", 'entity', 'entity.aclId = acl.id and entity.role != ' . self::Project);

	}

	public static function sort(Query $query, ArrayObject $sort): Query
	{
		if(isset($sort['group'])) {
			$query->join("tasks_tasklist_grouping", "grouping", "grouping.id = tasklist.groupingId", "LEFT");
			$sort->renameKey("group", "grouping.name");
		}
		return parent::sort($query, $sort);
	}

	public function highestItemModSeq() {
		return $this->highestItemModSeq;
	}

	static function updateHighestModSeq($tasklistId) {
		if(empty($tasklistId)) {
			return;
		}
		go()->getDbConnection()
			->update(self::getMapping()->getPrimaryTable()->getName(),
				['highestItemModSeq' => Task::getState()],
				['id' => $tasklistId]
			)->execute();
	}
}