<?php
namespace go\core\orm;

use go\core\db\Query as DbQuery;

class Query extends DbQuery {
	private $model;
	
	/**
	 * Set's the entity or property model this query is for.
	 * 
	 * Used internally by go\core\orm\Propery::internalFind();
	 * 
	 * @param string $cls
	 * @return $this
	 */
	public function setModel($cls) {
		$this->model = $cls;
		return $this;
	}
	
	/**
	 * Applies conditions to the query so that only entities with the given 
	 * permission level are fetched.
	 * 
	 * 
	 * @param int $level
	 * @param int $userId Defaults to current user ID
	 * @return $this
	 */
	public function applyAcl($level = Acl::LEVEL_READ, $userId = null) {
		if(!isset($this->model)) {
			throw new \Exception("No model is set for this query");
		}
		
		$cls = $this->model;
		
		$cls::applyAclToQuery($this, $level, $userId);
		
		return $this;
	}

}
