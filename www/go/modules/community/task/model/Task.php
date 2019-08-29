<?php
namespace go\modules\community\task\model;
						
use go\core\jmap\Entity;
use go\core\orm\SearchableTrait;
use go\core\db\Criteria;
use go\core\orm\Query;
use go\modules\community\task\model\Alert;
use GO\Base\Util\Icalendar\RRuleIterator;

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
	 * @var int
	 */							
	public $createdAt;

	/**
	 * 
	 * @var int
	 */							
	public $modifiedAt;

	/**
	 * 
	 * @var int
	 */							
	public $modifiedBy = 0;

	/**
	 * 
	 * @var int
	 */							
	public $start;

	/**
	 * 
	 * @var int
	 */							
	public $due;

	/**
	 * 
	 * @var int
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
		$rrule = $this->getRecurrenceRule();

		if(!empty($rrule['FREQ']) && $this->percentageComplete == 100) {
			$this->saveOccurence();
		}

		if(!parent::internalSave()) {
			return false;
		}

		return true;
	}

	protected function getRecurrencePattern(){

		$rrule = $this->getRecurrenceRule();
		$startTime = $this->start;
		//$startDate = $rrule['start'];
		//$startDateTime = new \DateTime('@'.$this->start_time, new \DateTimeZone($this->timezone));
		//$startDateTime->setTimezone(new \DateTimeZone($this->timezone)); //iterate in local timezone for DST issues
		$rRule = new \GO\Base\Util\Icalendar\RRuleIterator($rrule, $startTime);

		//$rRule->fastForward(new \DateTime('@'.$lastReminderTime));
		$nextTime = $rRule->current();
		// && $this->hasException($nextTime->getTimeStamp())
		while($nextTime){
			$rRule->next();
			$nextTime = $rRule->current();
			//break;
		}
		return "";
		//return $rRule;
	}

	// protected function getRecurrencePattern(){

	// }

	protected function saveOccurence() {
		$this->getRecurrencePattern();


		// $nextTask = new Task();
		// $values = $this->toArray();
		// $nextTask->setValues($values);
		// $rrule = $this->getRecurrenceRule();
		// $this->recurrenceRule = "";
		// $nextTask->percentageComplete = 0;
		// $nextTask->id = NULL;

		// if(isset($rrule['repeatUntilDate']) && $rrule['repeatUntilDate']) {
		// 	$conf = new \GO\Base\Config();
		// 	$default = $conf->getdefault_timezone();
		// 	date_default_timezone_set($default);
		// 	$today = new \DateTime();
		// 	$today->settime(0,0);
		// 	$until = new \DateTime($rrule['until']);
		// 	if($today <= $until) {
		// 		$nextTask->save();
		// 	}
		// 	//$nextTask->start = $newdate;
		// } else if(isset($rrule['count'])) {
		// 	$count = $rrule['count'];
		// }


		
	}
}