<?php
namespace go\core\orm;

use Exception;
use go\core\acl\model\Acl;
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
	
	public function getModel() {
		return $this->model;
	}
	
	/**
	 * Applies JMAP filters to the query
	 * 
	 * @param array $filters
	 * 
	 * @return $this
	 */
	public function filter(array $filters) {		
		$cls = $this->model;		
		$cls::filter($this, $filters);
		
		return $this;
	}

}
