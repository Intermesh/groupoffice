<?php
namespace go\modules\community\task\model;

use DateInterval;
use go\core\jmap\Entity;
use go\core\orm\SearchableTrait;
use go\core\db\Criteria;
use go\core\orm\Query;
use go\modules\community\task\model\Alert;
use GO\Base\Util\Icalendar\RRuleIterator;
use go\core\util\DateTime;
use Sabre\VObject\Recur\RRuleIterator as SabreRRuleIterator;

/**
 * Task model
 *
 * @copyright (c) 2019, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class Task extends Entity {

	use SearchableTrait;
	
	/**
	 * 
	 * @var int
	 */							
	public $id;

	/**
	 * 
	 * @var Alert[]
	 */							
	public $alerts;

	/**
	 * 
	 * @var string
	 */							
	public $uid = '';

	/**
	 * 
	 * @var int
	 */							
	public $tasklistId;

	/**
	 * 
	 * @var int
	 */							
	public $createdBy;

	/**
	 * 
	 * @var DateTime
	 */							
	public $createdAt;

	/**
	 * 
	 * @var DateTime
	 */							
	public $modifiedAt;

	/**
	 * 
	 * @var int
	 */							
	public $modifiedBy = 0;

	/**
	 * 
	 * @var DateTime
	 */							
	public $start;

	/**
	 * 
	 * @var DateTime
	 */							
	public $due;

	/**
	 * 
	 * @var DateTime
	 */							
	public $completed;

	/**
	 * 
	 * @var string
	 */							
	public $title;

	/**
	 * 
	 * @var string
	 */							
	public $description;

	/**
	 * 
	 * @var string
	 */							
	public $status;

	/**
	 * 
	 * @var string
	 */							
	protected $recurrenceRule = '';

	/**
	 * 
	 * @var int
	 */							
	public $filesFolderId = 0;

	/**
	 * 
	 * @var int[]
	 */							
	public $categories;

	/**
	 * 
	 * @var int
	 */							
	public $priority = 1;

	/**
	 * 
	 * @var int
	 */							
	public $percentageComplete = 0;

	/**
	 * 
	 * @var int
	 */							
	public $projectId = 0;

	/**
	 * 
	 * @var string
	 */							
	protected $byDay = '';


	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("task_task", "task")
						->addArray('alerts', Alert::class, ['id' => 'taskId'])
						->addScalar('categories', 'task_task_category', ['id' => 'taskId']);
	}

	public static function getClientName() {
		return "TasksTask";
	}

	public function getRecurrenceRule() {
		return empty($this->recurrenceRule) ? null : json_decode($this->recurrenceRule, true);
	}

	public function setRecurrenceRule($rrule) {
		if($rrule !== null) {
			$rrule = json_encode($rrule);
		}
		$this->recurrenceRule = $rrule;
	}

	protected static function textFilterColumns() {
		return ['title', 'description'];
	}

	/**
	 * The name for the search results
	 * 
	 * @return string
	 */
	protected function getSearchName() {
		return $this->title;
	}
	
	/**
	 * The description in the search results
	 * 
	 * @return string
	 */
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
						})->add('percentageComplete', function(Criteria $criteria, $value) {
								$criteria->where(['percentageComplete' => $value]);
						})->addDate("late", function(Criteria $criteria, $comparator, $value) {	
								$criteria->where('due', '<', $value);
						})->addDate("future", function(Criteria $criteria, $comparator, $value) {	
								$criteria->where('start', '>', $value);
						})->addDate("nextweek", function(Criteria $criteria, $comparator, $value) {	
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

		if(!parent::internalSave()) {
			return false;
		}

		return true;
	}

	protected function createNewTask(\DateTime $next) {
		$nextTask = new Task();
		$values = $this->toArray();
		$nextTask->setValues($values);
		$rrule = $this->getRecurrenceRule();
			
		if(!empty($rrule['count'])) {
			$rrule['count']--;
			if($rrule['count'] > 0) {
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

	protected function parseToRRULE($recurrenceRule) {

		if(isset($recurrenceRule['until'])) {
			$recurrenceRule['until'] = str_replace(['-',':'], ['',''], $recurrenceRule['until']);
		}

		if(isset($recurrenceRule['bySetPosition'])) {
			$recurrenceRule['bySetPos'] = $recurrenceRule['bySetPosition'];
		}

		if(empty($recurrenceRule['byDay'])) {
			unset($recurrenceRule['byDay']);
		}
		$recurrenceRule['FREQ'] = $recurrenceRule['frequency'];

		unset($recurrenceRule['bySetPosition']);
		unset($recurrenceRule['frequency']);
		return $recurrenceRule;
	}

	/**
	 * @return \DateTime
	 */
	protected function getNextRecurrence($rrule){
		$rrule = $this->parseToRRULE($rrule);
		$rRule = new \GO\Base\Util\Icalendar\RRuleIterator($rrule, $this->start);
		$rRule->next();
		$nextTime = $rRule->current();
		return $nextTime;
	}
}