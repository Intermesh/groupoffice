<?php
namespace go\modules\community\task\model;
						
use go\core\jmap\Entity;
use go\core\orm\SearchableTrait;
use go\core\db\Criteria;
use go\core\orm\Query;

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
	 * @var int
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


	

	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("task_task", "task")
						->addArray('alerts', Alert::class, ['id' => 'taskId']);
	}

	public static function getClientName() {
		return "TasksTask";
	}

	public function getRecurrenceRule() {
		$value = empty($this->recurrenceRule) ? [] : json_decode($this->recurrenceRule, true);
		return $value;
	}

	public function setRecurrenceRule($rrule) {
		$this->recurrenceRule = json_encode(array_merge($this->getRecurrenceRule(), $rrule));
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
						})->add('categories', function(Criteria $criteria, $value) {
							if(!empty($value)) {
								$criteria->where(['categories' => $value]);
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

		/**
	 * Sort by database columns or creator and modifier
	 * 
	 * @param Query $query
	 * @param array $sort
	 * @return Query
	 */
	public static function sort(Query $query, array $sort) {
		
		if(isset($sort['creator'])) {			
			$query->join('core_user', 'u', 'n.createdBy = u.id', 'LEFT')->orderBy(['u.displayName' => $sort['creator']]);			
		} 
		
		if(isset($sort['modifier'])) {			
			$query->join('core_user', 'u', 'n.createdBy = u.id', 'LEFT')->orderBy(['u.displayName' => $sort['modifier']]);						
		} 
		
		return parent::sort($query, $sort);
		
	}

}