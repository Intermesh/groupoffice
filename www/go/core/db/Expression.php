<?php
namespace go\core\db;

/**
 * An expression that does not need escaping or sanity checks
 * 
 * Used in {@see Query::orderBy()} and {@see Query::groupBy()}
 * 
 * @example
 * `````````````````````````````````````````````````````````````````````````````
 * $query = (new Query)										
 *				->orderBy([new go\core\db\Expression('ISNULL(t.dueAt) ASC'), 'dueAt' => 'ASC']);
 * `````````````````````````````````````````````````````````````````````````````
 *
 * @copyright (c) 2016, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Expression {
	private $expression;
	
	/**
	 * Constructor
	 * 
	 * @param string $expression eg. "ISNULL(colName) ASC"
	 */
	public function __construct(string $expression) {
		$this->expression = $expression;
	}
	
	public function __toString() {
		return $this->expression;
	}
}
