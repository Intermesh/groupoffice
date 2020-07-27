<?php
/**
 * @copyright (c) 2019, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

namespace go\modules\community\tasks\model;

use go\core\acl\model\AclItemEntity;
use go\core\orm\CustomFieldsTrait;
use go\core\orm\EntityType;
use go\core\orm\SearchableTrait;
use go\core\db\Criteria;
use go\core\orm\Query;
use go\core\util\DateTime;

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

	/** @var int The list this Task belongs to */
	public $tasklistId;

    /** @var int */
    public $projectId = 0;

	/** @var int */
	public $createdBy;

	/** @var DateTime */
	public $createdAt;

	/** @var DateTime */
	public $modifiedAt;

	/** @var int */
	public $modifiedBy = 0;

    /** @var int */
    public $filesFolderId = 0;

    /** @var DateTime due date (when this should be finished) */
    public $due;

	/** @var DateTime local date when this task will be started */
	public $start;

	/** @var Duration Estimated positive duration the task takes to complete. */
    public $estimatedDuration;

    /** @var Progress Defines the progress of this task */
    public $progress = Progress::NeedsAction;

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
    public $alerts;

	/** @var int */
	public $vcalendarBlobId;

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
			->addMap('alerts', Alert::class, ['id' => 'taskId', 'ut.userId' => 'userId'])
			->addScalar('categories', 'tasks_task_category', ['id' => 'taskId']);
	}

	public static function converters() {
		$arr = parent::converters();
		$arr['text/calendar'] = \go\modules\community\tasks\convert\VCalendar::class;
		$arr['text/vcalendar'] = \go\modules\community\tasks\convert\VCalendar::class;
		$arr['text/csv'] = \go\modules\community\tasks\convert\Csv::class;
		return $arr;
	}

	public function getRrule() {
		return $this->recurrenceRule;
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

	public function setRecurrenceRuleEncoded($rrule) {
		$this->recurrenceRule = $rrule;
	}

	protected static function textFilterColumns() {
		return ['title', 'description'];
	}

	protected function getSearchName() {
		return $this->title;
	}

	protected function getSearchDescription(){
		return $this->description;
	}

	protected static function defineFilters() {
		return parent::defineFilters()
			->add('tasklistId', function(Criteria $criteria, $value) {
				if(!empty($value)) {
					$criteria->where(['tasklistId' => $value]);
				}
			})->add('categories', function(Criteria $criteria, $value, Query $query) {
				if(!empty($value)) {
					$query->join("task_task_category","categories","task.id = categories.taskId")
					->where(['categories.categoryId' => $value]);
				}
			})->addDate("start", function(Criteria $criteria, $comparator, $value) {
				$criteria->where(['start' => $value]);
			})->addDate("due", function(Criteria $criteria, $comparator, $value) {
				$criteria->where(['due' => $value]);
			})->add('percentComplete', function(Criteria $criteria, $value) {
				$criteria->where(['percentComplete' => $value]);
			})->addDate("late", function(Criteria $criteria, $comparator, $value) {
				$criteria->where('due', '<', $value);
			})->addDate("future", function(Criteria $criteria, $comparator, $value) {
				$criteria->where('start', '>', $value);
			})->addDate("nextWeekStart", function(Criteria $criteria, $comparator, $value) {
				$criteria->where('due', '>=', $value);
			})->addDate("nextWeekEnd", function(Criteria $criteria, $comparator, $value) {
				$criteria->where('due', '<=', $value);
			});
	}

	protected function internalSave() {
		if(!empty($this->recurrenceRule) && $this->percentageComplete == 100) {
			$next = $this->getNextRecurrence($this->getRecurrenceRule());
			if($next) {
				$this->createNewTask($next);
			} else {
				$this->recurrenceRule = null;
			}
		}

		if($this->isNew()) {
		    $this->uid = \go\core\util\UUID::v4();
        }

		// if alert can be based on start / due of task check those properties as well
		if($this->isModified('alerts') ||
           $this->isModified('useDefaultAlerts')) {
			$this->updateAlerts();
		}

		return parent::internalSave();
	}

	private function updateAlerts() {
		$entityType = EntityType::findByName('Task');
		if(!$this->isNew()) {
			$query = \go\core\model\Alert::find()->andWhere(['entityTypeId' => $entityType->getId(), 'entityId' => $this->id]);
//			if(!empty($this->recurrenceRule)) {
//				$query->andWhere(['recurrenceId' => $this->id]);
//			}
			\go\core\model\Alert::internalDelete($query);
		}
		$tasklist = Tasklist::findById($this->tasklistId);
		foreach($this->alerts as $alert) {
			$notify = new \go\core\model\Alert();
			$notify->alertId = $alert->id;
			$notify->triggerAt = $alert->when;
			$notify->userId = $tasklist->ownerId;
			$notify->entityId =  $this->id;
			$notify->entityTypeId = $entityType->getId();
			if(!$notify->save()) {
				throw new \Exception(var_export($notify->getValidationErrors(),true));
			}
		}
	}

	protected function createNewTask(\DateTime $next) {
		$nextTask = new Task();
		$values = $this->toArray();
		$nextTask->setValues($values);
		$rrule = $this->getRecurrenceRule();
			
		if(!empty($rrule->count)) {
			$rrule->count--;
			if($rrule->count > 0) {
				$nextTask->setRecurrenceRule($rrule);
			} else{
				$nextTask->recurrenceRule = null;
			}
		} else{
			$nextTask->setRecurrenceRule($rrule);
		}

		$this->recurrenceRule = "";
		
		$nextTask->percentageComplete = 0;
		$nextTask->id = NULL;
		$diff = $this->start->diff($next);
		$nextTask->start = $next;
		$nextTask->due->add($diff);

		if(!$nextTask->save()) {
			throw new \Exception("Could not save next task: ". var_export($nextTask->getValidationErrors(), true));
		}
	}

	protected function parseToRRULE($rrule) {
		if(isset($rrule->until)) {
			$rrule->until = str_replace(['-',':'], ['',''], $rrule->until);
		}

		if(isset($rrule->bySetPosition)) {
			$rrule->bySetPos = $rrule->bySetPosition;
		}

		if(empty($rrule->byDay)) {
			unset($rrule->byDay);
		} else {
			foreach($rrule->byDay as $key => $value) {
				$position = $value->position ?? '';
				$rrule->byDay->{$key} = $position . $value->day;
			}
		}

		$rrule->FREQ = $rrule->frequency;

		unset($rrule->bySetPosition);
		unset($rrule->frequency);
		return (array)$rrule;
	}

	/**
	 * @return \DateTime
	 */
	protected function getNextRecurrence($rrule){
		$rRuleIt = new \GO\Base\Util\Icalendar\RRuleIterator($this->parseToRRULE($rrule), $this->start);
		$rRuleIt->next();
		$nextTime = $rRuleIt->current();
		return $nextTime;
	}


	public function getUid() {
		return $this->uid;		
	}

	public function hasUid() {
		return !empty($this->uid);
	}

	public function getUri() {
		if(!isset($this->uri)) {
			$this->uri = $this->getUid() . '.vcf';
		}

		return $this->uri;
	}

	public function setUri($uri) {
		$this->uri = $uri;
	}
}