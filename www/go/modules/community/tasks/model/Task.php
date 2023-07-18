<?php /** @noinspection PhpUnused */

/**
 * @copyright (c) 2019, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

namespace go\modules\community\tasks\model;

use DateTimeInterface;
use Exception;
use go\core\acl\model\AclItemEntity;
use go\core\db\Criteria;
use go\core\db\Expression;
use go\core\model\Alert as CoreAlert;
use go\core\model\User;
use go\core\model\Module;
use go\core\model\UserDisplay;
use go\core\orm\CustomFieldsTrait;
use go\core\orm\exception\SaveException;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\orm\SearchableTrait;
use go\core\util\{ArrayObject, DateTime, Recurrence, StringUtil, Time, UUID};
use go\core\validate\ErrorCode;
use go\modules\community\comments\model\Comment;
use go\modules\community\tasks\convert\Spreadsheet;
use go\modules\community\tasks\convert\VCalendar;
use go\core\util\JSON;
use JsonException;
use PDO;

/**
 * Task model
 */
class Task extends AclItemEntity {

	const PRIORITY_LOW = 9;
	const PRIORITY_HIGH = 1;
	const PRIORITY_NORMAL = 0;


	use SearchableTrait;
	use CustomFieldsTrait;
	
	/** @var int PK in the database */
	public $id;

	/** @var string global unique id for invites and sync  */
	protected $uid = '';

//	protected $userId;

	/** @var int The list this Task belongs to */
	public $tasklistId;

	/** @var int id of user responsible for completing this tasks  */
	public $responsibleUserId;

	/** @var int used for the kanban groups */
	public $groupId;

	/** @var int */
	public $projectId ;

	/** @var int */
	public $createdBy;

	/** @var DateTime */
	public $createdAt;

	/** @var DateTime */
	public $modifiedAt;

	/** @var int */
	public $modifiedBy;

	/** @var int */
	public $filesFolderId;

	/** @var DateTime due date (when this should be finished) */
	public $due;

	/** @var DateTime local date when this task will be started */
	public $start;

	/** @var int Duration Estimated duration in seconds the task takes to complete. */
	public $estimatedDuration;

	/** @var int Progress Defines the progress of this task */
	protected $progress = Progress::NeedsAction;

	/** @var DateTime When the "progress" of either the task or a specific participant was last updated. */
	public $progressUpdated;

	/** @var string */
	public $title;

	/** @var string */
	public $description;

	/** @var string */
	public $location;

	//public $keywords; // only in jmap

	/** @var int[] */
	public $categories;

	public $color;

	/**
	 * Start time in H:m
	 *
	 * @var string
	 */
	public $startTime;

	/**
	 * @var float
	 */
	public $latitude;

	/**
	 * @var float
	 */
	public $longitude;

	/**
	 * @var string
	 * @todo this is an override for Humble. This is probably not the right spot for this. Discuss interanlly
	 */
	protected $displayName;
	/**
	 * @var string
	 * @todo this is an override for Humble. This is probably not the right spot for this. Discuss internally.
	 */
	protected $projectName;

	//The scheduling status
	//public $status = 'confirmed';

	/**
     * If present, this object represents one occurrence of a
     * recurring object.  If present the "recurrenceRule" and
     * "recurrenceOverrides" properties MUST NOT be present.
     *
     * The value is a date-time either produced by the "recurrenceRules" of
     * the master event, or added as a key to the "recurrenceOverrides"
     * property of the master event.
     * @var DateTime
     */
	//public $recurrenceId;

  /** @var string */
  protected $recurrenceRule;

  /** @var DateTime[PatchObject] map of recurrenceId => Task */
  //protected $recurrenceOverrides;

  /** @var boolean only set in recurrenceOverrides */
  //protected $excluded;

	/** @var int [0-9] 1 = highest priority, 9 = lowest, 0 = undefined */
	public $priority = self::PRIORITY_NORMAL;

	/** @var string free or busy */
	public $freeBusyStatus = 'free';

	/** @var string public , private, secret */
	public $privacy = 'public';

   public $replyTo;
   public $participants;

	/** @var int between 0 and 100 */
	public $percentComplete = 0;

	protected $uri;

	/** @var bool If true, use the user's default alerts and ignore the value of the "alerts" property. */
	public $useDefaultAlerts = false;

    /** @var Alert[] List of notification alerts when $useDefaultAlerts is not set */
	public $alerts = [];

	/** @var int */
	public $vcalendarBlobId;

	/**
	 * Time booked in seconds
	 *
	 * @var int
	 */
	protected $timeBooked;

	/**
	 * @var TaskListGroup[]
	 */
	public $group = [];


	protected static function aclEntityClass(): string
	{
		return TaskList::class;
	}

	protected static function aclEntityKeys(): array
	{
		return ['tasklistId' => 'id'];
	}

	protected static function internalRequiredProperties() : array
	{
		//Needed for support module permissions
		return array_merge(parent::internalRequiredProperties(), ['createdBy']);
	}

	protected static function defineMapping() :Mapping {
		return parent::defineMapping()
			->addTable("tasks_task", "task")
			->addUserTable("tasks_task_user", "ut", ['id' => 'taskId'])
			->addMap('alerts', Alert::class, ['id' => 'taskId'])
			->addMap('group', TaskListGroup::class, ['groupId' => 'id'])
			->addScalar('categories', 'tasks_task_category', ['id' => 'taskId']);

		return $mapping;
	}

	public static function converters(): array
	{
		return array_merge(parent::converters(), [VCalendar::class, Spreadsheet::class]);
	}

	/**
	 * @throws JsonException
	 */
	public function getRecurrenceRule(): ?array {
		return empty($this->recurrenceRule) ? null : JSON::decode($this->recurrenceRule, true);
	}

	public function setRecurrenceRule($rrule) {
		if($rrule !== null) {
			$rrule = JSON::encode($rrule);
		}
		$this->recurrenceRule = $rrule;
	}

	public function getProgress(): string
	{
		return Progress::$db[$this->progress] ?? Progress::$db[1];
	}

	public function getTimeBooked(): ?int
	{
		return $this->timeBooked;
	}

	/**
	 * Set progress status
	 *
	 * @param string $value "needs-action" {@see Progress::$db}
	 * @return void
	 */
	public function setProgress(string $value) {
		$key = array_search($value, Progress::$db, true);
		if($key === false) {
			$this->setValidationError('progress', ErrorCode::INVALID_INPUT, 'Incorrect Progress value for task: ' . $value);
		} else
			$this->progress = $key;
	}

	public function setRecurrenceRuleEncoded($rrule) {
		$this->recurrenceRule = $rrule;
	}

	protected static function textFilterColumns(): array
	{
		return ['title', 'description'];
	}

	protected function getSearchKeywords(): ?array
	{
		$keywords = [$this->title, $this->description];
		if($this->responsibleUserId) {
			$rUser = UserDisplay::findById($this->responsibleUserId);
			$keywords[] = $rUser->displayName;
		}
		if($this->tasklistId) {
			$tasklist = TaskList::findById($this->tasklistId);
			$keywords[] = $tasklist->name;
		}

		if($this->createdBy) {
			$creator = UserDisplay::findById($this->createdBy);
			if($creator) {
				$keywords[] = $creator->displayName;
			}
		}
		return $keywords;
	}

	protected function getSearchFilter(): ?string
	{
		$tasklist = TaskList::findById($this->tasklistId);
		return $tasklist->getRole() == "support" ? "support" : null;
	}

	protected function getSearchDescription(): string
	{
		$tasklist = TaskList::findById($this->tasklistId);
		$desc = $tasklist->name;
		if(!empty($this->responsibleUserId) && ($user = User::findById($this->responsibleUserId, ['displayName']))) {
			$desc .= ' - '.$user->displayName;
		} else{
			$desc .= ' - ' . go()->t("Unassigned", "community", "tasks");
		}

		if(!empty($this->description)) {
			$desc .= ": " . $this->description;
		}

		return $desc;
	}

	protected static function defineFilters(): Filters
	{

		return parent::defineFilters()
			->addText("title", function(Criteria $criteria, $comparator, $value, Query $query, array $filters){
				$criteria->where('title', $comparator, $value);
			})
			->add('tasklistId', function(Criteria $criteria, $value) {
				if(!empty($value)) {
					$criteria->where(['tasklistId' => $value]);
				}
			}, [])
			->add('projectId', function(Criteria $criteria, $value, Query $query) {
				if(!empty($value)) {
					if(!$query->isJoined("tasks_tasklist", "tasklist") ){
						$query->join("tasks_tasklist", "tasklist", "task.tasklistId = tasklist.id");
					}
					$criteria->where(['tasklist.projectId' => $value]);
				}
			})
			->add('role', function(Criteria $criteria, $value, Query $query) {
				if(!$query->isJoined("tasks_tasklist", "tasklist") ){
					$query->join("tasks_tasklist", "tasklist", "task.tasklistId = tasklist.id");
				}

				$roleID = array_search($value, TaskList::Roles, true);

				$criteria->where(['tasklist.role' => $roleID]);
			})
			->add('categories', function(Criteria $criteria, $value, Query $query) {
				if(!empty($value)) {
					if(!$query->isJoined("tasks_task_category","categories")) {
						$query->join("tasks_task_category", "categories", "task.id = categories.taskId");
					}
					$criteria->where(['categories.categoryId' => $value]);
				}
			})->addDateTime("start", function(Criteria $criteria, $comparator, $value) {
				$criteria->where('start',$comparator,$value);
			})->addDateTime("due", function(Criteria $criteria, $comparator, $value) {
				$criteria->where('due', $comparator, $value);
			})->addNumber('percentComplete', function(Criteria $criteria, $comparator, $value) {
				$criteria->where('percentComplete', $comparator, $value);
			})->add('complete', function(Criteria $criteria, $value) {
				$criteria->where('progress', $value ? '=' : '!=', Progress::Completed);
			})->add('scheduled', function(Criteria $criteria, $value) {
				$criteria->where('start', $value ? 'IS NOT' : 'IS',null);
			})->add('responsibleUserId', function(Criteria $criteria, $value){
				//if(!empty($value)) {
					$criteria->where('responsibleUserId', '=',$value);
				//}//
			})
			->add('progress', function(Criteria $criteria, $value){
				if(!empty($value)) {
					if(!is_array($value)) {
						$value = [$value];
					}
					$value = array_map(function($el) {
						return array_search($el, Progress::$db, true);
					}, $value);
					$criteria->where('progress', '=',$value);
				}
			});

	}

	protected function internalValidate()
	{
		if(isset($this->recurrenceRule)) {
			if(empty($this->start)) {
				$this->setValidationError('start', ErrorCode::REQUIRED, 'start is required when recurrence rule is set');
			}
		}

		if(isset($this->projectId) && $this->hasConflicts()) {
			$this->setValidationError('start', ErrorCode::CONFLICT, 'this task is in conflict with other tasks');
		}

		parent::internalValidate();
	}

	protected function internalSave(): bool
	{
		if ($this->isNew() && empty($this->uid)) {
			$this->uid = UUID::v4();
		}

		if ($this->progress == Progress::Completed) {
			$this->percentComplete = 100;
		}
		if ($this->isModified('percentComplete')) {
			if ($this->percentComplete == 100) {
				$this->progress = Progress::Completed;

				// Remove alert for creator of this comment. Other users will get a replaced alert below.
				CoreAlert::deleteByEntity($this);


			} else if ($this->percentComplete > 0 && $this->progress == Progress::NeedsAction) {
				$this->progress = Progress::InProcess;
			}
		}

		if ($this->isModified('progress')) {
			$this->progressUpdated = new DateTime();
		}

		if (!empty($this->recurrenceRule) && $this->progress == Progress::Completed) {
			$next = $this->getNextRecurrence($this->getRecurrenceRule());
			if ($next) {
				$this->createNewTask($next);
			} else {
				$this->recurrenceRule = null;
			}
		}

		if(!parent::internalSave()) {
			return false;
		}

		$this->createSystemAlerts();

		// if alert can be based on start / due of task check those properties as well
		$modified = $this->getModified('alerts');
		if (!empty($modified)) {
			$this->updateAlerts($modified['alerts']);
		}

		return true;
	}

	private function createSystemAlerts() {
		if(!CoreAlert::$enabled) {
			return;
		}
		if($this->isModified('responsibleUserId')) {

			if (isset($this->responsibleUserId)) {

				// when assigned to someone else it's progress should be needs action
				$this->progress = Progress::NeedsAction;

				if($this->responsibleUserId != go()->getUserId()) {
					$alert = $this->createAlert(new \DateTime(), 'assigned', $this->responsibleUserId)
						->setData([
							'type' => 'assigned',
							'assignedBy' => go()->getAuthState()->getUserId()
						]);

					if (!$alert->save()) {
						throw new SaveException($alert);
					}
				}
			} else if(!$this->isNew()){
				$this->deleteAlert('assigned', $this->getOldValue('responsibleUserId'));
			}
		}

		if($this->isNew()) {

			// create an alert if someone else created a task in your default list
			$tasklist = TaskList::findById($this->tasklistId, ['id', 'createdBy', 'role']);
			if($tasklist->getRole() == "list" && $this->createdBy != $tasklist->createdBy) {

				$defaultListId = User::findById($tasklist->createdBy, ['tasksSettings'])->tasksSettings->getDefaultTasklistId();
				if($defaultListId == $this->tasklistId) {
					$alert = $this->createAlert(new \DateTime(), 'createdforyou', $tasklist->createdBy)
						->setData([
							'type' => 'assigned',
							'createdBy' => $this->createdBy
						]);

					if (!$alert->save()) {
						throw new SaveException($alert);
					}
				}
			}
		} else if($this->modifiedBy != $this->createdBy){
			$this->deleteAlert('createdforyou', $this->modifiedBy);
		}
	}


	/**
	 * @throws Exception
	 */
	private function updateAlerts($modified) {

		if(!CoreAlert::$enabled)
		{
			return;
		}

		if(isset($modified[1])) {
			foreach ($modified[1] as $model) {
				if (!isset($modified[0]) || !in_array($model, $modified[0])) {
					$this->deleteAlert($model->id);
				}
			}
		}

		if(!isset($this->alerts)){
			return;
		}
		foreach($this->alerts as $alert) {
			$coreAlert = $this->createAlert($alert->at($this), $alert->id);
			if(!$coreAlert->save()) {
				throw new Exception(var_export($coreAlert->getValidationErrors(),true));
			}
		}
	}

	/**
	 * @inheritDoc
	 *
	 * @throws Exception
	 */
	public static function dismissAlerts(array $alerts)
	{

		// When a task is updated with deleted alerts it will circle back here
		// in that case the update won't affect any rows and the changes will be empty.

		$alertIds = [];

		//The ID of the core_alert is the tag of the task_alert.
		//But in some cases it contains a string like 'assigned' for when a task is assinged.
		foreach($alerts as $alert) {
			if(is_numeric($alert->tag)) {
				$alertIds[] = $alert->tag;
			}
		}

		if(empty($alertIds)) {
			return;
		}

		go()->getDbConnection()->update(
			'tasks_alert', ['acknowledged' => new DateTime()],
			(new Query)->where('id' , 'in', $alertIds)
		)->execute();

		$changes = Task::find()
			->select("task.id, tl.aclId, '0'")
			->fetchMode(PDO::FETCH_ASSOC)
			->join("tasks_tasklist", "tl", "tl.id = task.taskListId")
			->join("tasks_alert", "a", "a.taskId = task.id")
			->where('a.id' , 'in', $alertIds)
			->groupBy(['task.id'])
			->all();

		if(!empty($changes)) {
			Task::entityType()->changes($changes);
		}
	}

	/**
	 * @throws Exception
	 */
	protected function createNewTask(DateTimeInterface $next) {

		$values = $this->toArray();
//		unset($values['id']);
//		unset($values['progress']);
//		unset($values['responsibleUserId']);
//		unset($values['percentComplete']);
//		unset($values['progressUpdated']);
//		unset($values['freeBusyStatus']);
//		$nextTask = new Task();
//		$nextTask->setValues($values);

		$nextTask = $this->copy();
		$nextTask->progress = Progress::NeedsAction;
		$nextTask->responsibleUserId = null;
		$nextTask->percentComplete = 0;
		$nextTask->progressUpdated = null;
		$nextTask->freeBusyStatus = 'free';

		$rrule = $this->getRecurrenceRule();
			
		if(!empty($rrule->count)) {
			$rrule->count--;
			$nextTask->setRecurrenceRule($rrule->count > 0 ? $rrule : null);
		} else if(!empty($rrule->until)) {
			$nextTask->setRecurrenceRule($rrule->until > $next ? $rrule : null);
		} else{
			$nextTask->setRecurrenceRule($rrule);
		}

		$this->recurrenceRule = null;

		$nextTask->start = $next;
		if(!empty($nextTask->due)) {
			$diff = $this->start->diff($next);
			$nextTask->due->add($diff);
		}
		if(!$nextTask->save()) {
			throw new Exception("Could not save next task: ". var_export($nextTask->getValidationErrors(), true));
		}
	}

	/**
	 * @param array $rrule
	 * @return ?DateTimeInterface
	 */
	protected function getNextRecurrence(array $rrule): ?DateTimeInterface
	{
		$rule = Recurrence::fromArray($rrule, $this->start);
		$rule->next();
		return $rule->current();
	}

	public static function sort(Query $query, ArrayObject $sort): Query
	{
		if(isset($sort['groupOrder'])) {
			$query->join('tasks_tasklist_group', 'listGroup', 'listGroup.id = task.groupId', 'LEFT');
			$sort->renameKey('groupOrder', 'listGroup.sortOrder');
			$sort['id'] = "ASC";
		}

		if(isset($sort['tasklist'])) {

			if(!$query->isJoined("tasks_tasklist", "tasklist")) {
				$query->join("tasks_tasklist", "tasklist", "tasklist.id = task.tasklistId");
			}
			$sort->renameKey('tasklist','tasklist.name');
		}

		//sort null dates first
		if(isset($sort['due'])) {

			$null = strtoupper($sort['due']) == 'ASC' ? 'IS NULL' : 'IS NOT NULL';

			$i = array_search('due', $sort->keys());
			$sort->insert($i, new Expression($query->getTableAlias() . '.`due` ' . $null));

		}

		if(isset($sort['start'])) {

			$null = strtoupper($sort['start']) == 'ASC' ? 'IS NULL' : 'IS NOT NULL';

			$i = array_search('start', $sort->keys());
			$sort->insert($i, new Expression($query->getTableAlias() . '.`start` ' . $null));
		}

		if(isset($sort['responsible'])) {
			$query->join('core_user', 'responsible', 'responsible.id = '.$query->getTableAlias() . '.createdBy');
			$sort->renameKey('responsible', 'responsible.displayName');
		}

		if(isset($sort['categories'])) {
			$query->join('tasks_task_category', 'tc', 'tc.taskId = '.$query->getTableAlias() . '.id', 'LEFT');
			$query->join('tasks_category', 'categorySort', 'tc.categoryId = categorySort.id', 'LEFT');
			$sort->renameKey('categories', 'categorySort.name');
		}

		return parent::sort($query, $sort);
	}

	public function etag(): string
	{
		return '"' .$this->vcalendarBlobId . '"';
	}

	public function getUid(): string
	{
		return $this->uid;		
	}

	public function setUid(string $uid) {
		$this->uid = $uid;
	}

	public function hasUid(): bool
	{
		return !empty($this->uid);
	}

	public function getUri(): string
	{
		if(!isset($this->uri)) {
			$this->uri = $this->getUid() . '.ics';
		}

		return $this->uri;
	}

	public function setUri(string $uri) {
		$this->uri = $uri;
	}

	// TODO: Refactor these functions into proper property classes within Humble Planner
	public function getUserDisplayName(): ?string
	{
		return $this->displayName;
	}

	public function getProjectName(): ?string
	{
		return $this->projectName;
	}

	public function getProjectId(): ?int
	{
		return $this->projectId;
	}
	// END TODO

	/**
	 * Try to find conflicting tasks.
	 *
	 * A task is considered conflicting when it has a start date and user id and there are other tasks with the same
	 * responsible userId and start date which are part of a project list.
	 *
	 * @return bool
	 */
	public function hasConflicts() :bool
	{
		// No start date, no user id or not marked as 'busy'? No problem!
		if(!isset($this->start) || !isset($this->responsibleUserId) /* || $this->freeBusyStatus == 'free'*/) {
			return false;
		}
		$c = new Criteria();
		$c->andWhere('task.start', '=', $this->start)
			->andWhere("task.responsibleUserId", '=', $this->responsibleUserId);
		if(!empty($this->id)) {
			$c->andWhere('task.id', '!=', $this->id);
		}
		$tasks = self::find(['id','start', 'estimatedDuration','startTime'])
			->join('tasks_tasklist','tl','task.tasklistId = tl.id')
			->andWhere($c)
			->andWhere('tl.role = '. TaskList::Project)
			->all();

		// All day tasks are to be considered conflicting by definition
		if(!isset($this->startTime) && count($tasks) > 0) {
			return true;
		}

		$selfStartSecs = 0;
		$selfEndSecs = 0;

		if(isset($this->startTime)) {
			$selfStartSecs = Time::toSeconds($this->startTime);
			$selfEndSecs = $selfStartSecs + $this->estimatedDuration;
		}

		foreach($tasks as $task) {
			if(!isset($task->startTime)) { // all day task
				return true;
			}
			$theirStartSecs = Time::toSeconds($task->startTime);
			$theirEndSecs = $theirStartSecs + ($task->estimatedDuration ?? 0);

			if($theirStartSecs < $selfEndSecs && $theirEndSecs > $selfStartSecs) {
				return true;
			}
		}

		return false;
	}


	/**
	 * @throws SaveException
	 * @throws Exception
	 */
	public function onCommentAdded(Comment $comment) {

		if(!CoreAlert::$enabled ) {
			return;
		}

		if($this->createdBy != $comment->createdBy) {
			//agent makes this message
			if($this->responsibleUserId == null) {
				// auto assign task on first comment
				$this->responsibleUserId = $comment->createdBy;
			}
		}

		if($comment->createdBy == $this->responsibleUserId) {
			$this->progress = Progress::InProcess;
		} else {
			$this->progress = Progress::NeedsAction;
		}

		// make sure modified at is updated
		$this->modifiedAt = new DateTime();
		$this->save();

		$excerpt = StringUtil::cutString(strip_tags($comment->text), 50);

		$commenters = Comment::findFor($this)->selectSingleValue("createdBy")->distinct()->all();
		if($this->responsibleUserId && !in_array($this->responsibleUserId, $commenters)) {
			$commenters[] = $this->responsibleUserId;
		}

		//add creator too
		if(!in_array($this->createdBy, $commenters)) {
			$commenters[] = $this->createdBy;
		}

		//remove creator of this comment
		$commenters = array_filter($commenters, function($c) use($comment) {return $c != $comment->createdBy;});

		// Remove alert for creator of this comment. Other users will get a replaced alert below.
		CoreAlert::deleteByEntity($this, "comment", $comment->createdBy);

		// remove you were assigned to alert when commenting
		CoreAlert::deleteByEntity($this, "assigned", $comment->createdBy);

		foreach($commenters as $userId) {
			$alert = $this->createAlert(new DateTime(), 'comment', $userId)
				->setData([
					'type' => 'comment',
					'createdBy' => $comment->createdBy,
					'excerpt' => $excerpt
				]);

			if (!$alert->save()) {
				throw new SaveException($alert);
			}
		}


	}
}
