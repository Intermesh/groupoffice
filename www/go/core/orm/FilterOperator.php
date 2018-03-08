<?php
namespace go\core\orm;

use go\core\data\Model;
use go\core\db\Query;

class FilterOperator extends Model {
	
	/**
	 *
	 * @var FilterCondition[] 
	 */
	public $conditions;
	
	/**
	 * String This MUST be one of the following strings: “AND”/”OR”/”NOT”:
	 * AND: all of the conditions must match for the filter to match.
	 * OR: at least one of the conditions must match for the filter to match.
	 * NOT: none of the conditions must match for the filter to match.
	 * 
	 * @var string 
	 */
	public $operator = "AND";
	
	/**
	 * Apply the FilterOperator to the database query
	 */
	public function apply(Query $query) {
		
		$subCriteria = new Query();
		foreach($this->conditions as $condition) {
			$condition->apply($subCriteria);
		}
		
		$query->where($subCriteria, $this->operator);
	}
}