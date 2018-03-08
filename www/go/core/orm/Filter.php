<?php
namespace go\core\orm;

use go\core\acl\model\Acl;
use go\core\data\Model;
use go\core\db\Query;

/**
 * Abstract filter class
 * 
 * To create a filter class append "Filter" to the entity class name.
 * For example Enity Foo will get FooFilter.
 * 
 * Create a set function for each filter option you'd like to apply. For example
 * 
 * ```
 * public function setName($value) {
 *	$this->query->andWhere(["name' => $value]);
 * }
 * 
 * ```
 * 
 * 
 */
abstract class Filter extends Model {
	
	/**
	 *
	 * @var Query
	 */
	protected $query;
	
	/**
	 *
	 * @var string
	 */
	protected $entityClass;
	
	private $permissionLevel = Acl::LEVEL_READ;
	
	private function __construct($entityClass, Query $query) {
		$this->entityClass = $entityClass;
		$this->query = $query;
	}
	
	/**
	 * Create FilterCondition objects from an array.
	 * 
	 * @param array $filterConditionValues
	 * @return \static[]
	 */
	public static function fromArray(array $filterConditionValues, $entityClass, Query $query) {
		$filters = [];
		foreach($filterConditionValues as $values) {
			
			if(isset($values['conditions']) && isset($values['operator'])) {
				$filter = new FilterOperator();
				$filter->operator = $values['operator'];
				foreach($values['conditions'] as $subValues) {
					$subfilter = new static($entityClass, $query);
					$subfilter->setValues($subValues);
					$filter->conditions[] = $subfilter;
				}
			}else {			
				$filter = new static($entityClass, $query);
				$filter->setValues($values);
			}		
			
			$filters[] = $filter;
		}
		
		return $filters;
	}
	
	/**
	 * Filter on the permission level. Defaults to readable items.
	 * 
	 * @param int $level
	 */
	public function setPermissionLevel($level) {
		$this->permissionLevel = $level;
	}
	
	public function getPermissionLevel() {
		return $this->permissionLevel;
	}
}