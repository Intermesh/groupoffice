<?php
/**
 * 
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @package GO.base.db
 * @version $Id: File.class.inc.php 7607 2011-06-15 09:17:42Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Merijn Schering <mschering@intermesh.nl> 
 */

/**
 * The parameters for ActiveRecord::find() can be constructed with this class
 *
 * @package GO.base.db
 * @version $Id: File.class.inc.php 7607 2011-06-15 09:17:42Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Merijn Schering <mschering@intermesh.nl>  * 
 */


namespace GO\Base\Db;
use GO;

class FindParams{
	
	private $_params=array();
	
	/**
	 * Get all the parameters in an array.
	 * 
	 * @return array  
	 */
	public function getParams(){
		return $this->_params;
	}
	
	public function getParam($paramName){
		return isset($this->_params[$paramName]) ? $this->_params[$paramName] : false;
	}
	
	/**
	 * Set the Distinct select option
	 * 
	 * @param boolean $useDistinct
	 * @return \FindParams 
	 */
	public function distinct($useDistinct = true){
		$this->_params['distinct'] = $useDistinct;
		return $this;
	}
	
	
	/**
	 * Create a new instance of FindParams
	 * 
	 * @return FindParams 
	 */
	public static function newInstance(){
		return new self;
	}
	
	
	/**
	 * Fetch a stored findparams object for export purposes.
	 * You can save it with ->export('name');
	 * 
	 * @param StringHelper $name
	 * @return \GO\Base\Db\FindParams
	 */
	public static function loadExportFindParams($name){
		$findParams=GO::session()->values[$name]['findParams'];
		
		$findParams->getCriteria()->recreateTemporaryTables();
		
		return $findParams;
	}
	
	/**
	 * Merge this with another findParams object.
	 * 
	 * @param FindParams $findParams 
	 * @return FindParams 
	 */
	public function mergeWith($findParams){
		if(!$findParams)
			$findParams=array();
		elseif(!is_array($findParams)){
			
			if($findParams instanceof FindParams)
				$findParams = $findParams->getParams();
			else
				throw new \Exception('$findParams must be an instance of FindParams');
		}
		
		
		if(isset($this->_params['criteriaObject']) && isset($findParams['criteriaObject'])){
			$this->_params['criteriaObject']->mergeWith($findParams['criteriaObject']);
			unset($findParams['criteriaObject']);
		}
		
		
		if(isset($this->_params['join']) && isset($findParams['join'])){
			$findParams['join']=$this->_params['join']."\n".$findParams['join'];
		}
	
		
		$this->_params = array_merge($this->_params, $findParams);
		
		
		return $this;
	}
	
	/**
	 * Set to true if you want to ignore ACL permissions.
	 * 
	 * @param boolean $value
	 * @return FindParams 
	 */
	public function ignoreAcl($value=true){
		$this->_params['ignoreAcl']=$value;
		return $this;
	}
	
	/**
	 * Join the table that contains the ACL field. For example ab_addressbook AS addressbook for the contact model.
	 * 
	 * @param boolean $value
	 * @return \FindParams
	 */
	public function joinAclFieldTable($value=true){
		$this->_params['joinAclFieldTable']=$value;
		return $this;
	}
	
	
	/**
	 * Set to true if you want to ignore permissions by admin group. This is used
	 * for e-mail accounts for example. The admin has access to all accounts but
	 * for display will only show them when an admin has access by user to prevent
	 * too much info on screen.
	 * 
	 * @param boolean $value
	 * @return FindParams 
	 */
	public function ignoreAdminGroup($value=true){
		$this->_params['ignoreAdminGroup']=$value;
		return $this;
	}
	
	/**
	 * Set the selected fields for the select query.
	 * Defaults to all fields except for TEXT and BLOB fields.
	 * 
	 * Remember the model table is aliased with 't'. Using this may result in incomplete models.
	 * 
	 * @param StringHelper $fields
	 * @return FindParams 
	 */					
	public function select($fields='t.*'){
		$this->_params['fields']=$fields;
		return $this;
	}
	
	/**
	 * Makes sure all fields from the table/alias are selected.
	 * 
	 * For example it replaces all t.field, t.field2 with t.* and leaves all other parts of the select query alone.
	 * 
	 * @param StringHelper $table
	 * @return \GO\Base\Db\FindParams
	 */
	public function selectAllFromTable($table='t'){
		
		

		// Fields can be empty, if they are empty then we fill it with 't.*'
		if(empty($this->_params['fields']))
			$this->_params['fields'] = 't.*';
		
		if(preg_match('/[^\.`]*\s*\*/', $this->_params['fields']))
			return $this;
		
		$parts = explode(',', $this->_params['fields']);

		$new = array($table.'.*');

		foreach($parts as $part){
			
			if(preg_match('/\sAS\s/i',$part)){
				//leave aliases alone
				$new[]=$part;
			}elseif(!preg_match('/'.preg_quote ($table,'/').'\..*/', $part) && !preg_match('/'.preg_quote ('`'.$table.'`','/').'\..*/', $part)){

				//remove all t.something parts				
				$new[]=$part;
			}
		}
		
		$this->_params['fields']=implode(', ', $new);
		
		return $this;
		
	}

	
	/**
	 * Insert a plain join SQL string
	 * 
	 * 
	 * @example
	 * 
	 * ```
	 * $findParams = FindParams::newInstance()
							->join('core_search', FindCriteria::newInstance()->addRawCondition('search.entityId', 't.id')->addRawCondition("search.entityTypeId", $entityTypeId), 'search', 'LEFT');
			
	 * ```
	 * 
	 * @param StringHelper $tableName
	 * @param FindCriteria $criteria
	 * @param String $tableAlias
	 * @param String $type INNER or LEFT etc.
	 * 
	 * @return FindParams 
	 */
	public function join($tableName, $criteria, $tableAlias = false, $type='INNER'){

		if(is_string($criteria)) {
			$criteria = (new FindCriteria())->addRawCondition($criteria);
		}
				
		if(!isset($this->_params['join']))
			$this->_params['join']='';
		else
			$this->_params['join'].="\n";
		
		$this->_params['join'].="$type JOIN `$tableName` ";
		
		if($tableAlias)
			$this->_params['join'] .= $tableAlias.' ';
		
		$this->_params['join'] .= ' ON ('.$criteria->getCondition().')';
		
		//add the bind params to the main criteria object.
		$this->getCriteria()->addParams($criteria->getParams());
				
		//register join so activerecord can check if it needs to join the acl table
		if(!isset($this->_params['joinedTables']))
			$this->_params['joinedTables']=array();
		
		$this->_params['joinedTables'][$tableName]=$tableAlias;		
		
		return $this;
	}
	
	/**
	 * Join a relation in the find query. Relation models are fetched together and
	 * can be accessed without the need for an extra select query.
	 * 
	 * @param StringHelper $name
	 * @param StringHelper $type
	 */
	public function joinRelation($name, $type='INNER'){
		
		if(!isset($this->_params['joinRelations']))
			$this->_params['joinRelations']=array();
		
		$this->_params['joinRelations'][$name]=array('name'=>$name, 'type'=>$type);
		
		return $this;
	}
	
	/**
	 * Join a HAS_MANY relation so you can select counts, sums etc of the relation.
	 * 
	 * For example select all users with their event counts:
	 * 
	 *  \GO\Base\Model\User::model()->addRelation('events', array(
	 *		'type'=>  ActiveRecord::HAS_MANY, 
	 *		'model'=>'GO\Calendar\Model\Event',
	 *		'field'=>'user_id'				
	 *	));
	 *		
	 *		$fp = FindParams::newInstance()->groupRelation('events', 'count(events.id) as eventCount');
	 *
	 *				
	 *		$stmt = \GO\Base\Model\User::model()->find($fp);
	 *		
	 *		foreach($stmt as $user){
	 *			echo $user->name.': '.$user->eventCount."<br />";
	 *			echo '<hr>';
	 *		}
	 * 
	 * @param StringHelper $name Name of the HAS_MANY relation
	 * @param StringHelper $select The select string to add. eg. count(events.id) AS eventCount Note that 'events' must match the name of the relation
	 */
	public function groupRelation($name, $select, $joinType='INNER'){
		$this->joinRelation($name, $joinType);
		$this->_params['groupRelationSelect']=$select;
		
		return $this;
	}
	
	/**
	 * Check if a table has been joined
	 * 
	 * @param StringHelper $tableName
	 * @return mixed false or table alias used for join 
	 */
	public function tableIsJoined($tableName){
		return isset($this->_params['joinedTables'][$tableName]) ? $this->_params['joinedTables'][$tableName] : false;
	}
	
	
//	public function joinRelation($relationName){
//		if(!isset($this->_params['joinRelations']))
//			$this->_params['joinRelations']=array();
//		
//		$this->_params['joinRelations'][]=$relationName;
//	}
	
	/**
	 * Add a find criteria object to add where conditions
	 * 
	 * @param FindCriteria $criteria
	 * @return FindParams 
	 */
	public function criteria(FindCriteria $criteria){
		if(!isset($this->_params['criteriaObject']))
			$this->_params['criteriaObject']=$criteria;
		else
			$this->_params['criteriaObject']->mergeWith($criteria);
		
		return $this;
	}
	
	/**
	 * Get the find criteria object so you can add more conditions.
	 * 
	 * @return FindCriteria 
	 */
	public function getCriteria(){
		if(!isset($this->_params['criteriaObject']))
			$this->_params['criteriaObject']= new FindCriteria();
		
		return $this->_params['criteriaObject'];
	}
	
	/**
	 * Make this query available for exports to CSV, PDF etc.
	 * It will be stored in the session so that 
	 * \GO\Base\Controller\AbstractModelController can reuse the params.
	 * 
	 * @param StringHelper $name
	 * @return FindParams 
	 */
	public function export($name, $totalizeColumns=array()){
		$this->_params['export']=$name;
		$this->_params['export_totalize_columns']=$totalizeColumns;
		return $this;
	}
	
	/**
	 * Execute a simple search query
	 * 
	 * @param StringHelper $query
	 * @param array $fields When you ommit this it will search all text fields
	 * @return FindParams 
	 */
	public function searchQuery($query, $fields=array()){
		$this->_params['searchQuery']=$query;
		$this->_params['searchQueryFields']=$fields;
		
		return $this;
	}
	
	/**
	 * Set the search fields to use when executing search queries
	 * This needs to be RAW value of the field with the "table" prefix. Example: array('`pr`.`name`','`t`.`name`')
	 * 
	 * @param array $fields 
	 * @return FindParams 
	 */
	public function searchFields($fields=array()){
		$this->_params['searchQueryFields']=$fields;
		
		return $this;
	}
	
	/**
	 * Join the custom fields table if it's available for the model.
	 * 
	 * @param boolean $value
	 * @return FindParams 
	 */
	public function joinCustomFields($value=true){
		$this->_params['joinCustomFields']=$value;
		return $this;
	}
	
	/**
	 * Set tot true to in combination with a LIMIT option only. This will do an 
	 * extra calculation of the total number of rows. It will do 
	 * SELECT SQL_CALC_FOUND_ROWS .. and after the query an extra
	 * SELECT FOUND_ROWS(). It's very useful for paging grids but for other 
	 * purposes you probably just want to use rowCount() on the ActiveStatement.
	 * 
	 * (See class ActiveStatement 
	 * 
	 * @param boolean $value
	 * @return FindParams 
	 */
	public function calcFoundRows($value=true){
		$this->_params['calcFoundRows']=$value;
		return $this;
	}
	
	/**
	 * Set sort order
	 * 
	 * @param string/array $field or array('field1','field2') for multiple values
	 * @param string/array $direction 'ASC' or array('ASC','DESC') for multiple values
	 * @return FindParams 
	 */
	public function order($field, $direction='ASC'){
		$this->_params['order']=$field;
		$this->_params['orderDirection']=$direction;
		
		return $this;
	}
	
	/**
	 * Adds a group by clause.
	 * 
	 * @param array $fields eg. array('t.id');
	 * @return FindParams 
	 */
	public function group($fields){
		$this->_params['group']=$fields;
		return $this;
	}
	
	/**
	 * Adds a having clause. Warning. RAW SQL is passed to the query. Be careful
	 * with user input.
	 * 
	 * @param StringHelper $rawSQL
	 * @return FindParams 
	 */
	public function having($rawSQL){
		$this->_params['having']=$rawSQL;
		return $this;
	}
	
	/**
	 * Set to true to return a single model instead of a statement.
	 * Permissions will not be checked when using this option!
	 * 
	 * @param boolean $value
	 * @return FindParams 
	 */
	public function single($value=true, $disableModelCache=false){
		$this->_params['single']=$value;
		$this->_params['disableModelCache']=$disableModelCache;
		return $this;
	}
	
	/**
	 * Join a model table on the query
	 * Caution: Please be sure that the model has only a single key. If the model 
	 *					has multiple keys then you need to build the Join criteria 
	 *					manually with the Join function.
	 * 
	 * @param array $config
	 * 
	 * array(
	 *			'model'=>'GO\Billing\Model\OrderStatusLanguage',
	 * 
	 *			'localTableAlias'=>'t', //defaults to "t"
	 *			'localField'=>'id', //defaults to "id"
	 * 
	 *			'foreignField'=>'status_id', //defaults to primary key of the remote model
	 *			'tableAlias'=>'l', //Optional table alias
	 * 
	 *			'type'=>'INNER' //defaults to INNER,
	 *			'criteria'=>FindCriteria // Optional extra join parameters
	 *			)
	 * 
	 * @return FindParams 
	 */
	public function joinModel($params){	
			
		$joinModel = GO::getModel($params['model']);

		if(!isset($params['foreignField']))
			$params['foreignField']=$joinModel->primaryKey();

		if(!isset($params['localField']))
			$params['localField']="id";
		
		if(!isset($params['localTableAlias']))
			$params['localTableAlias']="t";

		if(!isset($params['type']))
			$params['type']='INNER';

		if(!isset($params['tableAlias']))
			$params['tableAlias']=false;

		if(!isset($params['criteria'])){
			$params['criteria'] = new FindCriteria();
		}				
		
		$table = $params['tableAlias'] ? $params['tableAlias'] : $joinModel->tableName();

		$params['criteria']->addRawCondition("`".$table."`.`".$params['foreignField']."`", "`".$params['localTableAlias']."`.`".$params['localField']."`");

		return $this->join($joinModel->tableName(), $params['criteria'], $params['tableAlias'],$params['type']);
	}
	
//	/**
//	 * Join a relation in the query. The fields will be selected as RelationName@AttributeName.
//	 * 
//	 * @param string $relationName 
//	 */
//	public function joinRelation($relationName, $type='INNER'){
//		if($type!='INNER' && $type!='LEFT' && $type!='RIGHT')
//			throw new \Exception("Must be INNER, LEFT or RIGHT");
//		
//		$this->_params['joinRelations'][$relationName] = $type;
//	}
	
	
	/**
	 * Skip this number of items
	 * 
	 * @param int $start
	 * @return FindParams 
	 */
	public function start($start=0){
		$this->_params['start']=$start;
		return $this;
	}
	
	/**
	 * Limit the number of models returned
	 * 
	 * @param int $limit
	 * @return FindParams 
	 */
	public function limit($limit=0){
		$this->_params['limit']=$limit;
		return $this;
	}
	
	/**
	 * Only return rows that the user has this level of access to.
	 * 
	 * Note: this is ignored when you use ignoreAcl()
	 * 
	 * @param int $level See \GO\Base\Model\Acl constants for available levels. It defaults to \GO\Base\Model\Acl::READ_PERMISSION
	 * @param int $user_id Defaults to the currently logged in user
	 * @return FindParams 
	 */
	public function permissionLevel($level, $user_id=false){
		$this->_params['permissionLevel']=$level;
		$this->_params['userId']=$user_id;
		
		return $this;
	}
	
	/**
	 * Set to true to debug the SQL code in the debug log
	 * 
	 * @param boolean $value
	 * @return FindParams 
	 */
	public function debugSql($value=true){
		$this->_params['debugSql']=$value;
		
		return $this;
	}
	
	/**
	 * For internal use by ActiveRecord only. This will be set to the 
	 * relation name when a relational query is made.
	 * 
	 * @param StringHelper $name
	 * @return FindParams 
	 */
	public function relation($name){
		$this->_params['relation']=$name;
		
		return $this;
	}
	
	/**
	 * For internal use by ActiveRecord only. This is set with 
	 * MANY_MANY relations that use a link table with a model.
	 * 
	 * @param StringHelper $modelName The model name
	 * @param StringHelper $localPkField Attribute field that holds the pk of the other model.
	 * @param int $localPk Primary key of the model
	 * @return FindParams 
	 */
	public function linkModel($modelName, $localPkField, $localPk){
		
		$this->_params['linkModel']=$modelName;
    $this->_params['linkModelLocalField']=$localPkField;
    $this->_params['linkModelLocalPk']=$localPk;
		
		return $this;
	}
	
	
	/**
	 * Limit the number of models returned
	 * 
	 * @param int $limit
	 * @return FindParams 
	 */
	public function fetchClass($className=null){
		$this->_params['fetchClass']=$className;
		return $this;
	}
}
