<?php
namespace go\modules\community\task\model;

use GO\Base\Util\Icalendar\Rrule;
use go\core\jmap\Entity;
use go\core\orm\SearchableTrait;
use go\core\db\Criteria;
use go\core\orm\Query;
use go\modules\community\task\model\Alert;
use go\core\util\DateTime;
use go\modules\community\task\convert\Csv;

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
	protected $uid = '';

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

	protected $uri;

	/**
	 * 
	 * @var int
	 */	
	public $vcalendarBlobId;


	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("task_task", "task")
						->addArray('alerts', Alert::class, ['id' => 'taskId'])
						->addScalar('categories', 'task_task_category', ['id' => 'taskId']);
	}

	public static function getClientName() {
		return "Task";
	}

	public static function converters() {
		$arr = parent::converters();
		$arr['text/calendar'] = \go\modules\community\task\convert\VCalendar::class;	
		$arr['text/vcalendar'] = \go\modules\community\task\convert\VCalendar::class;		
		$arr['text/csv'] = Csv::class;
		return $arr;
	}

	public function getRrule() {
		return $this->recurrenceRule;
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

	public function setRecurrenceRuleEncoded($rrule) {
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
		} else {
			foreach($recurrenceRule["byDay"] as $key => $value) {
				$position = isset($value['position']) ? $value['position'] : '';
				$recurrenceRule["byDay"][$key] = $position.$value['day'];
			}
		}

		$recurrenceRule['FREQ'] = $recurrenceRule['frequency'];

		unset($recurrenceRule['bySetPosition']);
		unset($recurrenceRule['frequency']);
		return $recurrenceRule;
	}

	public function createRRULE() {
		$rrule = $this->parseToRRULE($this->getRecurrenceRule());
		$rruleObj = new Rrule($rrule);
		return $rruleObj->createRrule();
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


	public function getUid() {
		
		if(empty($this->uid)) {
			$url = trim(go()->getSettings()->URL, '/');
			$uid = substr($url, strpos($url, '://') + 3);
			$uid = str_replace('/', '-', $uid );
			$this->uid = $this->id . '@' . $uid;
		}

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
			$this->uri = $this->getUid() . '.vcf';
		}

		return $this->uri;
	}

	public function setUri($uri) {
		$this->uri = $uri;
	}
}