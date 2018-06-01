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
 * Create "where" criteria for the SQL query ActiveRecord::find() function
 *
 * @package GO.base.db
 * @version $Id: File.class.inc.php 7607 2011-06-15 09:17:42Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Merijn Schering <mschering@intermesh.nl> 
 * @author Wesley Smits <wsmits@intermesh.nl> 
 */

namespace GO\Base\Db;


class FindCriteria {
	
	private $_condition='';
	
	private static $_paramCount = 0;
	
	private $_paramPrefix = ':go';
	
	private $_params=array();
	
	private $_columns;
		
	private $_ignoreUnknownColumns=false;
	/**
	 * Get a new instance object of this class file
	 * 
	 * @return FindCriteria 
	 */
	public static function newInstance(){
		return new self;
	}
	
	/**
	 * Add a model to the criteria object so it can determine of which PDO type a column is.
	 * You can also give an alias with it. If not then the alias defaults to "t".
	 * 
	 * @param ActiveRecord $model An ActiveRecord model.
	 * @param String $tableAlias The alias that this model needs to use. Default: 't'.
	 */
	public function addModel($model, $tableAlias='t'){
		$this->_columns[$tableAlias]=$model->getColumns();
		return $this;
	}
	
	
	/**
	 * Private function to add 'AND' or 'OR' to the current condition.
	 * 
	 * @param Boolean $useAnd True for 'AND', false for 'OR'.
	 */
	private function _appendOperator($useAnd){
		if($this->_condition!='')
			$this->_condition .= $useAnd ? ' AND' : ' OR';
		
	}
	
	/**
	 * Prevent warnings on column types when the model is unknown
	 * 
	 * @return FindCriteria 
	 */
	public function ignoreUnknownColumns(){
		$this->_ignoreUnknownColumns=true;
		
		return $this;
	}
	
	/**
	 * Private function to recognize the PDOTYPE(http://www.php.net/manual/en/pdo.constants.php) of the given fields.
	 * 
	 * @param String $tableAlias The alias of the table in this SQL statement.
	 * @param String $field The field for where the PDOTYPE needs to be checked.
	 * @return int type The constant of the found PDO type .
	 */
	private function _getPdoType($tableAlias, $field){
		if(isset($this->_columns[$tableAlias][$field]['type']))
			$type = $this->_columns[$tableAlias][$field]['type'];
		else{
			$type= PDO::PARAM_STR;
			if(!$this->_ignoreUnknownColumns){
				//\GO::debug("WARNING: Could not find column type for $tableAlias. $field in FindCriteria. Using PDO::PARAM_STR. Do you need to use addModel?");
//				$trace = debug_backtrace();
//				for($i=0;$i<count($trace);$i++){
//					\GO::debug($trace[$i]['class'].'::'.$trace[$i]['function']);
//				}
			
			}
			
		}
		return $type;
	}
	
	/**
	 * Private function to add the given condition to the rest of this object's condition string.
	 * 
	 * @param String $tableAlias The alias of the table in this SQL statement.
	 * @param String $field The field where this condition is for.
	 * @param Mixed $value The value of the field for this condition.
	 * @param String $comparator How needs this field be compared with the value. Can be ('<','>','<>','=<','>=','=').
	 */
	private function _appendConditionString($tableAlias, $field, $value, $comparator, $valueIsColumn){
		
		$this->_validateComparator($comparator);
		
		if (is_null($value)) {
			if ($comparator == "=")
				$comparator = "IS";
			$paramTag = "NULL";
		}elseif(!$valueIsColumn){
			$paramTag = $this->_getParamTag();		
			$this->_params[$paramTag]=array($value, $this->_getPdoType($tableAlias, $field));
		}else
		{
			$paramTag=$value;
		}
		
		$this->_condition .= ' `'.$tableAlias.'`.`'.$field.'` '.$comparator.' '.$paramTag;
	}
	
	
	private function _validateComparator($comparator){
		if(!preg_match("/[=!><a-z]/i", $comparator))
			throw new \Exception("Invalid comparator: ".$comparator);
	}
	
	/**
	 * Adds a condition to this object and returns itself.
	 * 
	 * @param String $field The field where this condition is for.
	 * @param String $value The value of the field for this condition.
	 * @param String $comparator How needs this field be compared with the value. Can be ('<','>','<>','=<','>=','=').
	 * @param String $tableAlias The alias of the table for the $field parameter
	 * @param Boolean $useAnd True for 'AND', false for 'OR'. Default: true. 
	 * @param Boolean $valueIsColumn Treat the value as a column name. In this case the value must contain the table alias too if necessary.
	 * @return FindCriteria The complete FindCriteria object is given as a return value.
	 */
	public function addCondition($field, $value, $comparator='=',$tableAlias='t', $useAnd=true, $valueIsColumn=false) {
		
		if(!is_string($field))
			throw new \Exception("field parameter for addCondition should be a string");
		
		$this->_appendOperator($useAnd);
		$this->_appendConditionString($tableAlias, $field, $value, $comparator, $valueIsColumn);		
		return $this;
	}
	
	/**
	 * Adds a condition to this object and returns itself. 
	 * 
	 * You can also create field IS NULL with this function for example.
	 * 
	 * WARNING: This function does not do any sanity checks on the input! It just 
	 * inserts the plain values so user input may not be passed to this function.
	 * You can use parameter tags like :paramName and use the addBindParameter function.
	 *
	 * 
	 * @param String $value1 The field value where this condition is for.
	 * @param String $value The value of the field for this condition.
	 * @param String $comparator How needs this field be compared with the value. Can be ('<','>','<>','=<','>=','=').
	 * @param Boolean $useAnd True for 'AND', false for 'OR'. Default: true. 
	 * @return FindCriteria The complete FindCriteria object is given as a return value.
	 */
	public function addRawCondition($value1, $value2=null, $comparator='=', $useAnd=true) {
		$this->_appendOperator($useAnd);
		if($value2===null)
			$this->_condition .= ' '.$value1;
		else
			$this->_appendRawConditionString($value1, $value2, $comparator);		
		return $this;
	}
	
	/**
	 * Add a custom bind parameter. Only useful in combination with addRawCondition.
	 * 
	 * @param StringHelper $paramTag eg. ":paramName"
	 * @param mixed $value
	 * @param int $pdoType
 	 * @return FindCriteria The complete FindCriteria object is given as a return value.
	 */
	public function addBindParameter($paramTag, $value, $pdoType=PDO::PARAM_STR){
		$this->addParams(array($paramTag=>array($value, $pdoType)));
		return $this;
	}
	
	/**
	 * Private function to add the given condition to the rest of this object's condition string.
	 * 
	 * WARNING: This function does not do any sanity checks on the input! It just 
	 * inserts the plain values so user input may not be passed to this function.
	 * 
	 * @param String $value1 The raw field where this condition is for.
	 * @param Mixed $value2 The raw value of the field for this condition.
	 * @param String $comparator How needs this field be compared with the value. Can be ('<','>','<>','=<','>=','=').
	 */
	private function _appendRawConditionString($value1, $value2, $comparator) {
		$this->_validateComparator($comparator);
		$this->_condition .= ' '.$value1.' '.$comparator.' '.$value2;
	}
	
	/**
	 * Add an IN condition to this object and returns itself.
	 * 
	 * @param String $field The field where this condition is for.
	 * @param Array $value The value of the field for this condition.
	 * @param String $tableAlias The alias of the table in this SQL statement.
	 * @param Boolean $useAnd True for 'AND', false for 'OR'. Default: true.
	 * @param Boolean $useNot True for 'NOT IN', false for 'IN'. Default: false.
	 * @return FindCriteria The complete FindCriteria object is given as a return value.
	 */
	public function addInCondition($field, $values, $tableAlias='t', $useAnd=true, $useNot=false) {	
				
		if(!is_array($values))
			throw new \Exception("ERROR: Value for addInCondition must be an array");
		
//		if(!count($value))
//			throw new \Exception("ERROR: Value for addInCondition can't be empty");
		
		if(!count($values))
				return $this;
		
		$this->_appendOperator($useAnd);
		$comparator = $useNot ? 'NOT IN' : 'IN';
		
		$paramTags=array();
		foreach($values as $val){
			$paramTag = $this->_getParamTag();
			$paramTags[]=$paramTag;
			$this->_params[$paramTag]=array($val, $this->_getPdoType($tableAlias, $field));
		}
		
		
		$this->_condition .= ' `'.$tableAlias.'`.`'.$field.'` '.$comparator.' ('.implode(',',$paramTags).')';
		
		return $this;
		
	}
	
	
	private static $_temporaryTables=array();
	/**
	 * IN conditions can be slow on large datasets. Creating a temporary table
	 * for the query can be much faster.
	 * This function does the same as addInCondition but uses a subselect in a temporary table.
	 * 
	 * @param String $tableName The name of the temporary table
	 * @param String $field The field where this condition is for.
	 * @param Array $value The value of the field for this condition.
	 * @param String $tableAlias The alias of the table in this SQL statement.
	 * @param Boolean $useAnd True for 'AND', false for 'OR'. Default: true.
	 * @param Boolean $useNot True for 'NOT IN', false for 'IN'. Default: false.
	 * @return \FindCriteria
	 * @throws Exception
	 */
	public function addInTemporaryTableCondition($tableName, $field, $values, $tableAlias='t', $useAnd=true, $useNot=false){
		if(!is_array($values))
			throw new \Exception("ERROR: Value for addInCondition must be an array");
		
		if(!count($values))
				return $this;
		
			$this->_createTemporaryTable($tableName, $values);

			$this->addRawCondition($tableAlias.'.'.$field, '(SELECT id FROM `'.$tableName.'`)' , $useNot ? 'NOT IN' : 'IN', $useAnd);
			
			return $this;
	}
	
	private function _createTemporaryTable($tableName, $values){
		if(!isset(self::$_temporaryTables[$tableName])){
			$sql = "CREATE TEMPORARY TABLE `$tableName` (
				`id` int(11) NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE = MEMORY;";
			\GO::getDbConnection()->query($sql);

			self::$_temporaryTables[$tableName]=true;
		}else
		{
			\GO::getDbConnection()->query("TRUNCATE TABLE `$tableName`");
		}
		
		$this->sleepingTempTables[$tableName]=$values;


		$sql = "INSERT INTO `$tableName` (id) VALUES (".implode('),(', $values).")";
		\GO::getDbConnection()->query($sql);
	}
	
	private $sleepingTempTables=array();

	
	public function recreateTemporaryTables() {	
		
		//when this object is in session for export we need to recreate the temp tables.
		
		foreach($this->sleepingTempTables as $tableName=>$values){
			$this->_createTemporaryTable($tableName,$values);
		}
	}
	
	/**
	 * Add a fulltext search query
	 * 
	 * @param StringHelper $field
	 * @param StringHelper $matchQuery
	 * @param StringHelper $tableAlias
	 * @param boolean $useAnd
	 * @param StringHelper $mode
	 * @return FindCriteria 
	 */
	public function addMatchCondition($field, $matchQuery, $tableAlias='t', $useAnd=true, $mode='BOOLEAN'){
		$this->_appendOperator($useAnd);
		
		$paramTag = $this->_getParamTag();
		$this->_params[$paramTag]=array($matchQuery, PDO::PARAM_STR);
		
		$fields = array();
		if(!is_array($field))
			$field = array($field);
		
		foreach($field as $f)
		 $fields[]='`'.$tableAlias.'`.`'.$f.'`';
		
		$this->_condition .= ' MATCH('.implode(",", $fields).') AGAINST ('.$paramTag;
		
		if($mode!='NATURAL')
			$this->_condition .= ' IN '.$mode.' MODE';
		
		$this->_condition .= ')';
		return $this;
	}
	
	/**
	 * Add a search condition to this object and returns itself.
	 * The $useExact parameter verifies the given value as an exact string or adds a '%' before and after the given value.
	 * 
	 * @param String $field The field where this condition is for.
	 * @param String $value The value of the field for this condition.
	 * @param Boolean $useAnd True for 'AND', false for 'OR'. Default: true.
	 * @param Boolean $useNot True for 'NOT LIKE', false for 'LIKE'. Default: false.
	 * @param Boolean $partialMatch True if want to search on partials of the term. Default: false.
	 * @param String $tableAlias The table alias to apply this searchcondition to.
	 * 
	 * @return FindCriteria The complete FindCriteria object is given as a return value.
	 */
	public function addSearchCondition($field, $value, $useAnd=true, $useNot=false, $partialMatch=false, $tableAlias = 't') {
		
		$this->_appendOperator($useAnd);
		
		$comparator = $useNot ? 'NOT LIKE' : 'LIKE';
		
		if($partialMatch) {
			$value = '%' . preg_replace('/[\s*]+/', '%', $value) . '%';
		}
		
		$this->_appendConditionString($tableAlias, $field, $value, $comparator, false);	
	
		return $this;
	}

	/**
	 * Private function to get the current parameter prefix.
	 * 
	 * @return String The next available parameter prefix.
	 */
	private function _getParamTag() {
		self::$_paramCount++;
		return $this->_paramPrefix.self::$_paramCount;
	}
	
	/**
	 * Returns the current condition value of this FindCriteria object as a string.
	 * 
	 * @return String Current condition value.
	 */
	public function getCondition() {
		return $this->_condition;
	}
	
	/**
	 * Returns the current parameter values of this FindCriteria object as an array.
	 * 
	 * @return Array Current parameter values.
	 */
	public function getParams() {
		return $this->_params;
	}
	
	/**
	 * Merge an other FindCriteria object together with this FindCriteria object.
	 * Then returns the complete merged FindCriteria object.
	 * 
	 * @param FindCriteria $criteria The FindCriteria object that needs to be merged with this FindCriteria object.
	 * @param Boolean $useAnd True for 'AND', false for 'OR'. Default: true.
	 * @return FindCriteria The complete FindCriteria object is given as a return value.
	 */
	public function mergeWith(FindCriteria $criteria, $useAnd=true) {
		
		$condition = $criteria->getCondition();
		
		if(!empty($condition)){		
			$operator = $useAnd ? 'AND' : 'OR';
			
			$thisCondition = $this->getCondition();
			if(!empty($thisCondition))
			{
				$this->_condition = ' ('.$thisCondition.') '.$operator.' ('.$condition .')';
				
			}else
			{
				$this->_condition = '('.$condition.')';				
			}		
		}
		//always merge params. FindParams::join can add params without a condtion.
		$this->_params = array_merge($this->getParams(), $criteria->getParams());
		return $this;
	}
	
	/**
	 * Add extra params to bind to the query. This is used by FindParams::join()
	 * 
	 * @var array $params
	 */
	public function addParams($params){
		$this->_params = array_merge($this->getParams(), $params);
	}
}
