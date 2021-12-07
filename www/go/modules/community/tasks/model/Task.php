<?php
/**
 * @copyright (c) 2019, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

namespace go\modules\community\tasks\model;

use Exception;
use go\core\acl\model\AclItemEntity;
use go\core\model\Alert as CoreAlert;
use go\core\model\UserDisplay;
use go\core\orm\CustomFieldsTrait;
use go\core\model\User;
use go\core\orm\exception\SaveException;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\SearchableTrait;
use go\core\db\Criteria;
use go\core\orm\Query;
use go\core\util\{DateTime, StringUtil, Time};
use go\core\validate\ErrorCode;
use go\modules\community\comments\model\Comment;
use go\modules\community\tasks\convert\VCalendar;

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

  /** @var Recurrence */
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

	protected static function aclEntityClass(): string
	{
		return Tasklist::class;
	}

	protected static function aclEntityKeys(): array
	{
		return ['tasklistId' => 'id'];
	}

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("tasks_task", "task")
			->addUserTable("tasks_task_user", "ut", ['id' => 'taskId'])
			->addMap('alerts', Alert::class, ['id' => 'taskId'])
			->addMap('group', TasklistGroup::class, ['groupId' => 'id'])
			->addScalar('categories', 'tasks_task_category', ['id' => 'taskId']);
	}

	public static function converters(): array
	{
		return array_merge(parent::converters(), [VCalendar::class, Spreadsheet::class]);
	}

	public function getRecurrenceRule() {
		return empty($this->recurrenceRule) ? null : json_decode($this->recurrenceRule);
	}

	public function setRecurrenceRule($rrule) {
		if($rrule !== null) {
			$rrule = json_encode($rrule);
		}
		$this->recurrenceRule = $rrule;
	}

	public function getProgress() {
		return Progress::$db[$this->progress];
	}

	public function getTimeBooked() {
		return $this->timeBooked;
	}

	public function setProgress($value) {
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

	protected function getSearchKeywords()
	{
		$keywords = [$this->title, $this->description];
		if($this->responsibleUserId) {
			$rUser = User::findById($this->responsibleUserId);
			$keywords[] = $rUser->displayName;
		}
		if($this->tasklistId) {
			$tasklist = TaskList::findById($this->tasklistId);
			$keywords[] = $tasklist->name;
		}
		return $keywords;
	}

	protected function getSearchDescription(){
		$tasklist = Tasklist::findById($this->tasklistId);
		$desc = $tasklist->name;
		if(!empty($this->responsibleUserId) && ($user = User::findById($this->responsibleUserId, ['displayName']))) {
			$desc .= ' - '.$user->displayName;
		} else{
			$desc .= ' - ' . go()->t("Unassigned", "community", "tasks");
		}

		return $desc;
	}

	protected static function defineFilters(): Filters
	{

		return parent::defineFilters()
			->add('tasklistId', function(Criteria $criteria, $value, Query $query) {
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
				$criteria->where(['tasklist.role' => $value]);
			})
			->add('categories', function(Criteria $criteria, $value, Query $query) {
				if(!empty($value)) {
					$query->join("tasks_task_category","categories","task.id = categories.taskId")
					->where(['categories.categoryId' => $value]);
				}
			})->addDate("start", function(Criteria $criteria, $comparator, $value) {
				$criteria->where('start',$comparator,$value);
			})->addDate("due", function(Criteria $criteria, $comparator, $value) {
				$criteria->where('due', $comparator, $value);
			})->addNumber('percentComplete', function(Criteria $criteria, $comparator, $value) {
				$criteria->where('percentComplete', $comparator, $value);
			})->add('complete', function(Criteria $criteria, $value) {
				$criteria->where('progress', $value ? '=' : '!=', Progress::Completed);
			})->add('scheduled', function(Criteria $criteria, $value) {
				$criteria->where('start', $value ? 'IS NOT' : 'IS',null);
			})->add('responsibleUserId', function(Criteria $criteria, $value){
				if(!empty($value)) {
					$criteria->where('responsibleUserId', '=',$value);
				}
			})
			->add('progress', function(Criteria $criteria, $value){
				if(!empty($value)) {
					if(!is_array($value)) {
						$value = [$value];
					}
					$value = array_map(function($el) {
						$key = array_search($el, Progress::$db, true);
						return $key;
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

		return parent::internalValidate();
	}

	protected function internalSave(): bool
	{
		if ($this->isNew()) {
			$this->uid = \go\core\util\UUID::v4();
		}

		if ($this->progress == Progress::Completed) {
			$this->percentComplete = 100;
		}
		if ($this->isModified('percentComplete')) {
			if ($this->percentComplete == 100) {
				$this->progress = Progress::Completed;
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

		if($this->isModified('responsibleUserId') && CoreAlert::$enabled) {

			if (isset($this->responsibleUserId)) {

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
			} else{
				$this->deleteAlert('assigned', $this->getOldValue('responsibleUserId'));
			}
		}

		// if alert can be based on start / due of task check those properties as well
		$modified = $this->getModified('alerts');
		if (!empty($modified)) {
			$this->updateAlerts($modified['alerts']);
		}

		return true;
	}


	private function updateAlerts($modified) {

		if(isset($modified[1])) {
			foreach ($modified[1] as $model) {
				if (!in_array($model, $modified[0])) {
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
	 * @param Alert[] $alerts
	 * @throws Exception
	 */
	public static function dismissAlerts(array $alerts)
	{
		$alertIds = [];

		foreach($alerts as $alert) {
			$alertIds[] = $alert->tag;
		}

		go()->getDbConnection()->update(
			'tasks_alert', ['acknowledged' => new DateTime()],
			(new Query)->where('id' , 'in', $alertIds)
		)->execute();

		$changes = Task::find()
			->select("task.id, tl.aclId, '0'")
			->join("tasks_tasklist", "tl", "tl.id = task.taskListId")
			->join("tasks_alert", "a", "a.taskId = task.id")
			->where('a.id' , 'in', $alertIds)
			->groupBy(['task.id']);

		Task::entityType()->changes($changes);
	}

	protected function createNewTask(\DateTimeInterface $next) {

		$values = $this->toArray();
		unset($values['id']);
		unset($values['progress']);
		unset($values['percentComplete']);
		unset($values['progressUpdated']);
		unset($values['freeBusyStatus']);

		$nextTask = new Task();
		$nextTask->setValues($values);
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
	 * @return \DateTime
	 */
	protected function getNextRecurrence($rrule){
		$rule = Recurrence::fromArray((array)$rrule, $this->start);
		$rule->next();
		return $rule->current();
	}

	public static function sort(Query $query, array $sort): Query
	{
		if(isset($sort['groupOrder'])) {
			$query->join('tasks_tasklist_group', 'listGroup', 'listGroup.id = task.groupId', 'LEFT');
			$sort['listGroup.sortOrder'] = $sort['groupOrder'];
			$sort['id'] = "ASC";
			unset($sort['groupOrder']);
		}

		if(isset($sort['tasklist'])) {

			if(!$query->isJoined("tasks_tasklist", "tasklist")) {
				$query->join("tasks_tasklist", "tasklist", "tasklist.id = task.tasklistId");
			}
			$sort['tasklist.name'] = $sort['tasklist'];

			unset($sort['tasklist']);
		}

		return parent::sort($query, $sort);
	}

	public function etag() {
		return '"' .$this->vcalendarBlobId . '"';
	}

	public function getUid() {
		return $this->uid;		
	}

	public function setUid($uid) {
		$this->uid = $uid;
	}

	public function hasUid() {
		return !empty($this->uid);
	}

	public function getUri() {
		if(!isset($this->uri)) {
			$this->uri = $this->getUid() . '.ics';
		}

		return $this->uri;
	}

	public function setUri($uri) {
		$this->uri = $uri;
	}

	// TODO: Refactor these functions into proper property classes within Humble Planner
	public function getUserDisplayName()
	{
		return $this->displayName;
	}

	public function getProjectName()
	{
		return $this->projectName;
	}

	public function getProjectId()
	{
		return $this->projectId;
	}
	// END TODO

	/**
	 * Try to find conflicting tasks.
	 *
	 * A task is considered conflicting when it has a start date and user id and there are other tasks with the same
	 * responsible userId and start date which are part of a project task list.
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
			->andWhere('tl.role = '. Tasklist::Project)
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
			$theirStartSecs = isset($task->startTime) ? Time::toSeconds($task->startTime) : 0;
			$theirEndSecs = isset($task->startTime) ? $theirStartSecs + $task->estimatedDuration ?? 0 : 0;

			if($theirStartSecs < $selfEndSecs && $theirEndSecs > $selfStartSecs) {
				return true;
			}
		}

		return false;
	}

	public function alertProps(CoreAlert $alert): array
	{
		if($alert->tag != 'assigned') {
			return parent::alertProps($alert);
		}
		$data = $alert->getData();
		$assigner = UserDisplay::findById($data->assignedBy, ['displayName']);

		$body = str_replace('{assigner}', $assigner->displayName, go()->t("You were assigned to this task by {assigner}"));
		$title = $alert->findEntity()->title() ?? null;

		return ['title' => $title, 'body' => $body];
	}


	/**
	 * @throws SaveException
	 * @throws Exception
	 */
	public function onCommentAdded(Comment $comment) {

		if($comment->createdBy != $this->responsibleUserId && $this->progress != Progress::NeedsAction) {
			$this->progress = Progress::NeedsAction;
			$this->save();
		} else if($this->progress = Progress::NeedsAction && $comment->createdBy == $this->responsibleUserId) {
			$this->progress = Progress::InProcess;
			$this->save();
		}

		if(!CoreAlert::$enabled ) {
			return;
		}


		$excerpt = StringUtil::cutString(strip_tags($comment->text), 50);

		$commenters = Comment::findFor($this)->selectSingleValue("createdBy")->distinct()->all();
		if($this->responsibleUserId && !in_array($this->responsibleUserId, $commenters)) {
			$commenters[] = $this->responsibleUserId;
		}

		$commenters = array_filter($commenters, function($c) use($comment) {return $c != $comment->createdBy;});

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