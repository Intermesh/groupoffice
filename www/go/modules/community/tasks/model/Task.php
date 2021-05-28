<?php
/**
 * @copyright (c) 2019, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

namespace go\modules\community\tasks\model;

use go\core\acl\model\AclItemEntity;
use go\core\db\Expression;
use go\core\model\Alert as CoreAlert;
use go\core\orm\CustomFieldsTrait;
use go\core\model\User;
use go\core\orm\EntityType;
use go\core\orm\exception\SaveException;
use go\core\orm\SearchableTrait;
use go\core\db\Criteria;
use go\core\orm\Query;
use go\core\util\DateTime;
use go\core\validate\ErrorCode;
use go\modules\community\tasks\convert\VCalendar;

/**
 * Task model
 */
class Task extends AclItemEntity {

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

	/** @var Duration Estimated positive duration the task takes to complete. */
  public $estimatedDuration;

  /** @var Progress Defines the progress of this task */
  protected $progress = Progress::NeedsAction;

	/** @var DateTime When the "progress" of either the task or a specific participant was last updated. */
	public $progressUpdated;

	/** @var string */
	public $title;

	/** @var string */
	public $description;

	//public $keywords; // only in jmap

	/** @var int[] */
	public $categories;

	public $color;

	protected $timeBooked;

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
	public $priority = 0;

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
	 * The users in this group
	 *
	 * @var int[]
	 */
//	public $hours;

	protected static function aclEntityClass(){
		return Tasklist::class;
	}

	protected static function aclEntityKeys(){
		return ['tasklistId' => 'id'];
	}

	protected static function defineMapping() {
		return parent::defineMapping()
			->addTable("tasks_task", "task")
			->addUserTable("tasks_task_user", "ut", ['id' => 'taskId'])
			->addMap('alerts', Alert::class, ['id' => 'taskId'])
			->addMap('group', TasklistGroup::class, ['groupId' => 'id'])
			->addScalar('categories', 'tasks_task_category', ['id' => 'taskId']);
//			->addScalar('hours', 'pr2_hours', ['id' => 'task_id']);
//			->addScalar('responsibleUser', 'core_user',['responsibleUserId' => 'id']);
	}

//	public function getTimeBooked()
//	{
//		$booked = 0;
//		if(count($this->hours) > 0) {
//			$record = (new \go\core\db\Query())
//				->select('SUM(duration) as timeBooked')
//				->from('pr2_hours')
//				->where('id IN ('. implode(',',$this->hours) . ')')
//				->single();
//			if($record) {
//				$booked = $record['timeBooked'];
//			}
//		}
//
//		return $booked;
//	}

	public static function converters() {
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

	protected static function textFilterColumns() {
		return ['title', 'description'];
	}

	protected function getSearchDescription(){
		$tasklist = Tasklist::findById($this->tasklistId);
		$desc = $tasklist->name;
		if(!empty($this->responsibleUserId) && ($user = User::findById($this->responsibleUserId,['displayName']))) {
			$desc .= ' - '.$user->displayName;
		} else{
			$desc .= ' - ' . go()->t("Unassigned", "community", "tasks");
		}

		return $desc;
	}

	protected static function defineFilters() {

		return parent::defineFilters()
			->add('tasklistId', function(Criteria $criteria, $value, Query $query) {
				if(!empty($value)) {
					$criteria->where(['tasklistId' => $value]);
				}
			}, [])
			->add('role', function(Criteria $criteria, $value, Query $query) {
				if(!$query->isJoined("tasks_tasklist", "listsonly") ){
					$query->join("tasks_tasklist", "listsonly", "task.tasklistId = listsonly.id");
				}
				$criteria->where(['listsonly.role' => $value]);
			})
			->add('categories', function(Criteria $criteria, $value, Query $query) {
				if(!empty($value)) {
					$query->join("tasks_task_category","categories","task.id = categories.taskId")
					->where(['categories.categoryId' => $value]);
				}
			})->addDate("start", function(Criteria $criteria, $comparator, $value) {
				$criteria->where(['start' => $value]);
			})->addDate("due", function(Criteria $criteria, $comparator, $value) {
				$criteria->where(['due' => $value]);
			})->addNumber('percentComplete', function(Criteria $criteria, $comparator, $value) {
				$criteria->where('percentComplete', $comparator, $value);
			})->add('complete', function(Criteria $criteria, $value) {
				$criteria->where('progress', $value?'=':'!=',Progress::Completed);
			})->addDate("late", function(Criteria $criteria, $comparator, $value) {
				$criteria->where('due', '<', $value);
			})->addDate("future", function(Criteria $criteria, $comparator, $value) {
				$criteria->where('start', '>', $value);
			})->addDate("nextWeekStart", function(Criteria $criteria, $comparator, $value) {
				$criteria->where('due', '>=', $value);
			})->addDate("nextWeekEnd", function(Criteria $criteria, $comparator, $value) {
				$criteria->where('due', '<=', $value);
			})->add('unplanned', function(Criteria $criteria, $value) {
				$criteria->where('start', $value?'IS':'IS NOT',null);
			});

	}

	protected function internalValidate()
	{
		if(isset($this->recurrenceRule)) {
			if(empty($this->start)) {
				$this->setValidationError('start', ErrorCode::REQUIRED, 'start is required when recurrence rule is set');
			}
		}
		return parent::internalValidate(); // TODO: Change the autogenerated stub
	}

	protected function internalSave()
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
		// If a task is saved for a project, check whether an actual task list exists.
		if(isset($this->projectId) && empty($this->tasklistId)) {
			Tasklist::saveForProject($this->projectId);
		}

		if($this->isModified('responsibleUserId')) {

			if (isset($this->responsibleUserId)) {
				$alert = $this->createAlert(new \DateTime())
					->setValue('userId', $this->responsibleUserId)
					->setValue('tag', 'task-assigned-' . $this->id)
					->setData(['type' => 'assigned', 'assignedBy' => go()->getAuthState()->getUserId()]);

				if (!$alert->save()) {
					throw new SaveException($alert);
				}
			} else{
				CoreAlert::delete(['tag' => 'task-assigned-' . $this->id]);
			}
		}

		$success = parent::internalSave();

		// if alert can be based on start / due of task check those properties as well
		if ($this->isModified('alerts') ||
			$this->isModified('useDefaultAlerts')) {
			$this->updateAlerts();
		}

		return $success;
	}


	private function updateAlerts() {
		if(!isset($this->alerts)){
			return;
		}
		foreach($this->alerts as $alert) {
			$coreAlert = $this->createAlert($alert->at($this));
			$coreAlert->tag = $alert->id;
			if(!$coreAlert->save()) {
				throw new \Exception(var_export($coreAlert->getValidationErrors(),true));
			}
		}
	}

	/**
	 * @param Alert[] $alerts
	 * @throws \Exception
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
			throw new \Exception("Could not save next task: ". var_export($nextTask->getValidationErrors(), true));
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

	public static function sort(Query $query, array $sort)
	{
		if(isset($sort['groupOrder'])) {
			$query->join('tasks_tasklist_group', 'listGroup', 'listGroup.id = task.groupId', 'LEFT');
			$sort['listGroup.sortOrder'] = $sort['groupOrder'];
			$sort['id'] = "ASC";
			unset($sort['groupOrder']);
		}

		return parent::sort($query, $sort);
	}

	public function etag() {
		return '"' .$this->vcalendarBlobId . '"';
	}

	public function getUid() {
		return $this->uid;		
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

}