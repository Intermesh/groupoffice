<?php
/*
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */

/**
 * Build the find params object with the criteria inside
 *
 * @package GO.base.db
 * @version $Id: Query.php 21970 2017-12-18 09:23:20Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Michael de Hart <mdhart@intermesh.nl> 
 */
namespace GO\Base\Db;

class Query {
	
	/**
	 * Internal FindParam object
	 * @var FindParams 
	 */
	protected $params;
	
	protected $where;
	
	/**
	 * Internal Criteria object
	 * There can be multiple?
	 * @var FindCriteria
	 */
	protected $criteria;
	
	/**
	 * List of valid operators
	 * @var array valid operators for queries 
	 */
	private $operators = array(
		'IN',
		'NOT IN',
		'LIKE',
		'NOT LIKE',
		'=',
		'!=',
		'>',
		'<',
		'>=',
		'<=',
		'<>',
	);
	
	/**
	 * The active record class the sets the tablename
	 * @see all() 
	 * @see one()
	 * @var ActiveRecord
	 */
	protected $model;
	
	public function __construct($model) {
		$this->model = $model;
		$this->params = FindParams::newInstance();
	}
	
	/**
	 * PHP magic method.
	 * This method allows calling methods defined in [[model]] via this query object.
	 * The first parameter passen throw is $this the extend this object and return itself
	 * Example:
	 * ~~~
	 * static public function invoiceable($query) {
	 *    return $query->andWhere(array('is_invoiceable'=>true));
	 * }
	 * ~~~
	 * @param StringHelper $name the method name to be called
	 * @param array $args the parameters passed to the method
	 * @return mixed the method return result
	 */
	public function __call($name, $args)
	{
		if (method_exists($this->model, $name)) {
			array_unshift($args, $this);
			return call_user_func_array(array($this->model, $name), $args);
		} else {
			throw new Exception('Scope function not found in Query or ActiveRecord: '.$name.'()');
		}
	}
	
	/**
	 * Get the FindParams object with the FindCriteria
	 * @return FindParams the created FindParams object
	 */
	public function get() {
		if($this->criteria instanceof FindCriteria)
			$this->params->criteria($this->criteria);
		return $this->params;
	}
	
	/**
	 * Get the first ActiveRecord returned in the query
	 * @return static
	 */
	public function one() {
		return $this->model->findSingle($this->get());
	}
	
	/**
	 * Return the statement
	 * @return Statement
	 */
	public function all() {
		return $this->model->find($this->get());
	}
	
	
	public function select($columns) {
		$this->params->select($columns);
		return $this;
	}
	
	/**
     * WHERE part of the query.
     *
     * The method requires a $condition parameter, and optionally a $params parameter
     * specifying the values to be bound to the query.
     *
     * The $condition parameter should be either a string (e.g. 'id=1') or an array.
     * Key-value must be in one of the following format:
     *
     * - key values: `['column1' => value1, 'column2' => value2, ...]`
     * - operator based: `[column, value, operator, (tableAlias)]`
	 * - bind params: `[queryString, param1 => value1, param2 => value2, ...]`
     *
     * In key-value presentation this generates the following SQL expression in general:
     * `column1=value1 AND column2=value2 AND ...`. It could replace findByAttributes()
	 * In case when a value is an array, an `IN` expression will be generated. 
	 * And if a value is null, `IS NULL` will be used. 
	 * Examples:
     * - `['type' => 1, 'status' => 2]` generates `type = 1 AND status = 2`.
     * - `['id' => [1, 2, 3], 'status' => 2]` generates `id IN (1, 2, 3) AND status = 2`.
     * - `['status' => null] generates `status IS NULL`
     *
     * A condition in operator format the oparator need to be in $this->operators or
	 * - `['<', 'ctime', time()]` generates `ctime < 1431233223`.
     * - `['IN', 'id'm [1,2,3]]` generates `id IN (1, 2, 3)`.
     * - `['LIKE', 'q', 'michael'] generates `q LIKE '%michael%'`
     * - `['=', 'name', 'michael'] generates `name = 'michael'`
	 * 
	 * The Bind params presentation will use a string the has `:param` in it. No
	 * variables should be concatenated into this string. The :param parts will 
	 * be filled with key value from the rest of the array. eg.
	 * - `['user.name = :user OR email = :email, 'user'=>'michael','email'=>'mdhart@intermesh.nl']`
	 * - `['ctime != mtime AND ctime < :time', 'time' => time()]`
	 * - `[''(ctime > :time || mtime < :time) AND delete=1', 'time'=>time()]`
	 * 
     * @param StringHelper|array $condition the conditions that should be put in the WHERE part.
     * @param StringHelper|boolean $merge 'AND'|'OR'|true add condition to previously added criteria
     * @return static the query object itself
     * @see andWhere()
     * @see orWhere()
     */
    public function where($condition, $merge = null) {
		$criteria = FindCriteria::newInstance();
		if (!isset($condition[0])) { // key value pairs
			foreach ($condition as $field => $value) {
				if (is_array($value)) {
					$criteria->addInCondition($field, $value);
				} else {
					$criteria->addCondition($field, $value);
				}
			}
		} elseif(!isset($condition[1])) { //string + bind params 
			$criteria->addRawCondition($condition[0]);
			array_shift($condition);
			foreach($condition as $param => $value) {
				$criteria->addBindParameter(':'.$param, $value);
			}
		} else { // condition specified
			$operator = strtoupper($condition[0]);
            if (in_array($operator,$this->operators)) {
				$alias = isset($condition[3])?$condition[3]:'t';
				switch($operator){
					case 'IN': $criteria->addInCondition($condition[1], $condition[2],$alias); break;
					case 'NOT IN': $criteria->addInCondition($condition[1], $condition[2], $alias, true, true); break;
					case 'LIKE': $criteria->addSearchCondition($condition[1], $condition[2], $alias); break;
					case 'NOT LIKE': $criteria->addSearchCondition($condition[1], $condition[2], $alias, true, true); break;
					default: $criteria->addCondition($condition[1], $condition[2], $operator, $alias);
				}
            } else {
                throw new \Exception('Found unknown operator in query: ' . $operator);
            }
		}
		if($merge === true)
			return $criteria;
		if($merge !== null && $this->criteria !== null) {
			if($merge === 'AND')
				$this->criteria->mergeWith($criteria, true);
			if($merge === 'OR')
				$this->criteria->mergeWith($criteria, false);
		} else {
			$this->criteria = $criteria;
		}
		return $this;
    }
	
	/**
     * Adds an additional WHERE condition to the existing one.
     * The new condition and the existing one will be joined using the 'AND' operator.
     * @param StringHelper|array $condition the new WHERE condition. Please refer to [[where()]]
     * on how to specify this parameter.
     * @param array $params the parameters (name => value) to be bound to the query.
     * @return static the query object itself
     * @see where()
     * @see orWhere()
     */
    public function andWhere($condition) {
        return $this->where($condition, 'AND');
    }

    /**
     * Adds an additional WHERE condition to the existing one.
     * The new condition and the existing one will be joined using the 'OR' operator.
     * @param StringHelper|array $condition the new WHERE condition. Please refer to [[where()]]
     * on how to specify this parameter.
     * @param array $params the parameters (name => value) to be bound to the query.
     * @return static the query object itself
     * @see where()
     * @see andWhere()
     */
    public function orWhere($condition) {
        return $this->where($condition, 'OR');
    }
	
	/**
	 * Join by table (eg. `join(array('core_user', 'core_user.id = user_id'))
	 * @param StringHelper $tableName
	 * @param array $condition key value pair of primary_key => foreign_key
	 * @see where() for conditions
	 * @return \GO\Base\Db\Query
	 */
	public function join($tableName, $condition, $alias=false) {
		$this->params->join($tableName, $this->where($condition,true), $alias, 'INNER');
		return $this;
	}
	
	/**
	 * @see join()
	 * @return \GO\Base\Db\Query
	 */
	public function leftJoin($tableName, $condition) {
		$this->params->join($tableName, $this->where($condition,true), $alias, 'LEFT');
		return $this;
	}
	
	/**
	 * @todo make join type bepend on relation type
	 * @param type $relations
	 * @param type $type
	 */
	public function with($relations, $type='INNER') {
		if(!is_array($relations))
			$relations = array($relations);
		foreach($relations as $relation) {
			$this->params->joinRelation($relation, $type);
		}
		return $this;
	}
	
	public function group($by, $relation=null) {
		$this->params->group($by);
		return $this;
	}
	
	/**
	 * HAVING part of the query
	 * @param StringHelper $rawSQL the raw SQL the paste in the having part
	 * @return \GO\Base\Db\Query
	 */
	public function having($rawSQL) {
		$this->params->having($rawSQL);
		return $this;
	}
	
	/**
	 * ORDER BY part of the query
	 * @param StringHelper|array $columns the columns (and the directions) to be ordered by.
     * Columns can be specified in either a string (e.g. "id ASC, name DESC") or an array
     * (e.g. `array('is ASC', 'name DESC')`)
	 * @return \GO\Base\Db\Query
	 */
	public function order($columns) {
		if(!is_array($columns))
			$columns = array($columns);
		$cols=array();
		$dirs=array();
		foreach($columns as $column) {
			$exp = explode(' ', $column, 2);
			$cols[]=$exp[0];
			$dir = isset($exp[1])?$exp[1]:'ASC';
			$dirs[]=$dir;
		}
		$this->params->order($cols, $dirs);
		return $this;
	}
	
	/**
     * LIMIT part of the query.
     * @param integer $limit the limit. Use null or negative value to disable limit.
     * @return static the query object itself
     */
	public function limit($limit, $start=0) {
		$this->params->limit($limit)->start($start);
		return $this;
	}
	
}
