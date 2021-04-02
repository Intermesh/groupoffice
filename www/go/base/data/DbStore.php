<?php

/*
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 */

/**
 * This store provide will generate a JSON response to be used in the Ext GridPanel
 * It can be used in the actionStore() if most controllers to generated data from
 * a query.
 * 
 * <pre>
 * $columnModel =  new ColumnModel(\GO\Notes\Model\Note::model());
 * $columnModel->formatColumn('user_name', '$model->user->name', array(), 'user_id');
 * 
 * $store=new Store('GO\Notes\Model\Note', $columnModel, $params);
 * </pre>
 * 
 * @version $Id$
 * @copyright Copyright Intermesh BV.
 * @author Michael de Hart <mdhart@intermesh.nl>
 * @package GO.base.data
 */

namespace GO\Base\Data;


class DbStore extends AbstractStore {
	// --- Attributes ---

	/**
	 * Will be used internaly to save the statement
	 * @var \GO\Base\Db\ActiveStatement 
	 */
	protected $_stmt;

	/**
	 * The column name to sort the resulting record set on
	 * @var StringHelper
	 */
	public $sort;
	public $defaultSort = '';

	/**
	 * The sort direction, ASC or DESC
	 * @var StringHelper 
	 */
	public $direction;
	public $defaultDirection = 'ASC';

	/**
	 * The amount of records to load at ones (per page)
	 * @var integer amount of records per page
	 */
	public $limit;

	/**
	 * offset in limit part of query @see \GO\Base\DB\findParams::start()
	 * @var integer  
	 */
	public $start = 0;

	/**
	 * Find only record that contain this word
	 * Is used by the quick search bar on top of a grid
	 * @var StringHelper word to search for
	 */
	public $query = '';

	/**
	 * Contains the loaded records from the database or empty if not loaded
	 * @var array 
	 */
	protected $_record = false;

	/**
	 * The name of the model this db store contains record from
	 * @var StringHelper name of model (eg. \GO\Base\User)
	 */
	protected $_modelClass;

	/**
	 * Extra find params the be merged with the storeparams 
	 * @var \GO\Base\Db\FindParams
	 */
	protected $_extraFindParams;

	/**
	 * Taken from old store to add a value to the primary key to search for
	 * @var array keys and value to attach to the pk to look for when deleting 
	 */
	public $extraDeletePk = null;

	/**
	 * Set this property to files if deleting records is not allowed for the store object
	 * @var boolean
	 */
	public $allow_delete = true;

	/**
	 * the primary key of the record that should be delete just before loading the store data
	 * @var array model PKs 
	 */
	protected $_deleteRecords = array();

	/**
	 * The request params passed by the controller
	 * @var array 
	 */
	protected $_requestParams = array();
	
	
	private $_multiSel;
	
	/**
	 *
	 * @var \GO\Base\Db\FindParams 
	 */
	private $_findParams;

	// --- Methods ---

	/**
	 * Create a new store
	 * @param StringHelper $modelClass the classname of the model to execute the find() method on
	 * @param ColumnModel $columnModel the column model object for formatting this store's columns
	 * @param array $storeParams the $_POST params to set to this store @see setStoreParams()
	 * @param \GO\Base\Db\FindParams $findParams extra findParams to be added to the store
	 */
	public function __construct($modelClass, $columnModel, $requestParams=null, $findParams = null) {

		$this->_modelClass = $modelClass;
		$this->_columnModel = $columnModel;
		$this->_requestParams = isset($requestParams) ? $requestParams : $_REQUEST;
		//$this->setStoreParams($requestParams);
		if ($findParams instanceof \GO\Base\Db\FindParams){
			$this->_extraFindParams = $findParams;
		}elseif($findParams!=null){
			throw new \Exception("FindParams must be an instance of '\GO\Base\Db\FindParams'. '".get_class($findParams)."' given.");
		}else{
			$this->_extraFindParams = \GO\Base\Db\FindParams::newInstance();
		}
		
		$this->_readRequestParams();
				
	}

	/**
	 * Read all parameters that are usable by the store from the actions $params array
	 * The following parametes are accepted:
	 * 'sort:string'
	 * 'dir:string'
	 * 'limit:integer'
	 * 'query:string'
	 * 'delete_keys:array'
	 * 'advancedQueryData:array'
	 * 'forEditing:boolean'
	 */
	private function _readRequestParams() {
		if (isset($this->_requestParams['sort']))
			$this->sort = str_replace('customFields.', '', $this->_requestParams['sort']);
		else
			$this->sort=$this->defaultSort;

		if (isset($this->_requestParams['dir']))
			$this->direction = $this->_requestParams['dir'];
		else
			$this->direction=$this->defaultDirection;
		
		if (isset($this->_requestParams['limit']))
			$this->limit = $this->_requestParams['limit'];

		if (isset($this->_requestParams['start']))
			$this->start = $this->_requestParams['start'];

		if (isset($this->_requestParams['query']))
			$this->query = $this->_requestParams['query'];
		
		if (isset($this->_requestParams['delete_keys']) && $this->allow_delete) { // will be deleted just before loading.
			$this->_deleteRecords = json_decode($this->_requestParams['delete_keys'], true);
			foreach ($this->_deleteRecords as $i => $modelPk) {
				if (is_array($modelPk)) {
					foreach ($modelPk as $col => $val) //format input columnvalues to database
						$modelPk[$col] = \GO::getModel($this->_modelClass)->formatInput($col, $val);
					$this->_deleteRecords[$i] = $modelPk;
				}
			}
		}

		if (!empty($this->_requestParams['advancedQueryData']))
			$this->_handleAdvancedQuery($this->_requestParams['advancedQueryData']);

		if (!empty($this->_requestParams["forEditing"]))
			$this->_columnModel->setModelFormatType("formatted");
	}

	/**
	 * FIXME: this method was copied from ModelController and never tested
	 * @param array $advancedQueryData the query data to be set to the store
	 * @param array $storeParams store params to be modied by advancedQuery
	 */
	private function _handleAdvancedQuery($advancedQueryData) {
		$advancedQueryData = is_string($advancedQueryData) ? json_decode($advancedQueryData, true) : $advancedQueryData;
		$findCriteria = $this->_extraFindParams->getCriteria();

		$criteriaGroup = \GO\Base\Db\FindCriteria::newInstance();
		$criteriaGroupAnd = true;
		for ($i = 0, $count = count($advancedQueryData); $i < $count; $i++) {

			$advQueryRecord = $advancedQueryData[$i];

			//change * into % wildcard
			$advQueryRecord['value'] = isset($advQueryRecord['value']) ? str_replace('*', '%', $advQueryRecord['value']) : '';

			if ($i == 0 || $advQueryRecord['start_group']) {
				$findCriteria->mergeWith($criteriaGroup, $criteriaGroupAnd);
				$criteriaGroupAnd = $advQueryRecord['andor'] == 'AND';
				$criteriaGroup = \GO\Base\Db\FindCriteria::newInstance();
			}

			if (!empty($advQueryRecord['field'])) {
				// Give the record a unique id, to enable the programmers to
				// discriminate between advanced search query records of the same field
				// type.
				$advQueryRecord['id'] = $i;
				// Check if current adv. search record should be handled in the standard
				// manner.

				$fieldParts = explode('.', $advQueryRecord['field']);

				if (count($fieldParts) == 2) {
					$field = $fieldParts[1];
					$tableAlias = $fieldParts[0];
				} else {
					$field = $fieldParts[0];
					$tableAlias = false;
				}

				if ($tableAlias == 't')
					$advQueryRecord['value'] = \GO::getModel($this->_modelClass)->formatInput($field, $advQueryRecord['value']);
				elseif ($tableAlias == 'cf') {
					$advQueryRecord['value'] = \GO::getModel(\GO::getModel($this->_modelClass)->customfieldsModel())->formatInput($field, $advQueryRecord['value']);
				}

				$criteriaGroup->addCondition($field, $advQueryRecord['value'], $advQueryRecord['comparator'], $tableAlias, $advQueryRecord['andor'] == 'AND');
			}
		}

		$findCriteria->mergeWith($criteriaGroup, $criteriaGroupAnd);
	}

	/**
	 * Create the PDO statment that will query the results
	 * @return \GO\Base\Db\ActiveStatement the PDO statement
	 */
	protected function createStatement() {

		$params = $this->getFindParams();
		$modelFinder = \GO::getModel($this->_modelClass);
		return $modelFinder->find($params);
	}
	
	
	/**
	 * Get the findParams object used for the database query
	 * 
	 * @return \GO\Base\Db\FindParams
	 */
	public function getFindParams(){
		
		$this->_readRequestParams();
				
		if(!isset($this->_findParams)){
			$this->_findParams=$this->createFindParams();
		}
		
		return $this->_findParams;
	}

	/**
	 * Create FindParams object to be passen the this models find() function
	 * If there are extraFind params supplied these well be merged in the end
	 * @return \GO\Base\Db\FindParams the created find params to be passen to AR's find() function
	 */
	protected function createFindParams() {

			if (!is_array($this->sort)){
				if(substr($this->sort,0,2)=='[{'){ //json sent by Sencha Touch

					$sorters = json_decode($this->sort);

					$this->sort = $this->direction = array();
					foreach($sorters as $sorter){
						$this->sort[]=$sorter->property;
						$this->direction[]=$sorter->direction;
					}
				}else{
					$this->sort = empty($this->sort) ? array() : array($this->sort);
				}
			}
		
		if (!empty($this->_requestParams['groupBy']))
			array_unshift($this->sort, $this->_requestParams['groupBy']);

		if (!is_array($this->direction))
			$this->direction = count($this->sort) ? array($this->direction) : array();

		if (isset($this->_requestParams['groupDir']))
			array_unshift($this->direction, $this->_requestParams['groupDir']);

			$this->sort = $this->getColumnModel()->getSortColumns($this->sort);

			$sortCount = count($this->sort);
			$dirCount = count($this->direction);
			for ($i = 0; $i < $sortCount - $dirCount; $i++)
				$this->direction[] = $this->direction[$dirCount-1];


		$findParams = \GO\Base\Db\FindParams::newInstance()
						->joinCustomFields()
						->order($this->sort, $this->direction);
		
		if (empty($this->_requestParams['dont_calculate_total'])) {
			$findParams->calcFoundRows();
		}

		//do not prefix search query with a wildcard by default. 
		//When you start a query with a wildcard mysql can't use indexes.
		//Correction: users can't live without the wildcard at the start.

		if (!empty($this->query))
			$findParams->searchQuery($this->query);

		if (isset($this->limit))
			$findParams->limit($this->limit);
		else
			$findParams->limit(\GO::user()->max_rows_list);

		if (!empty($this->start))
			$findParams->start($this->start);

		if (isset($this->_requestParams['permissionLevel']))
			$findParams->permissionLevel($this->_requestParams['permissionLevel']);

		if (isset($this->_extraFindParams))
			$findParams->mergeWith($this->_extraFindParams);

		return $findParams;
	}

	/**
	 * This method will be called internally before getData().
	 * It will delete all record that has the pk in $_deleteprimaryKey array
	 * @see: \GO\Base\Db\Store::processDeleteActions()
	 * @return boolean $success true if all went well
	 */
	protected function processDeleteActions() {
		if (isset($this->_records))
			throw new \Exception("deleteRecord should be called before loading data. If you run the statement before the deletes then the deleted items will still be in the result.");

		$errors = array();
		foreach ($this->_deleteRecords as $modelPk) {
			if ($this->extraDeletePk !== null) {
				$primaryKeyNames = \GO::getModel($this->_modelClass)->primaryKey(); //get the primary key names of the delete model in an array
				$newPk = array();
				foreach ($primaryKeyNames as $name) {
					if (isset($this->extraDeletePk[$name])) //pk is supplied in the extra values
						$newPk[$name] = $this->extraDeletePk[$name];
					else //it's not set in the extra values so it must be the key passed in the request
						$newPk[$name] = $modelPk;
				}
				$modelPk = $newPk;
			}
			$model = \GO::getModel($this->_modelClass)->findByPk($modelPk);
			if (!empty($model)){
				try {
					$key = is_array($model->pk) ? implode('-', $model->pk) : $model->pk;
					if(!$model->delete())
						$errors[$key] = $model->getValidationErrors();
				} catch (\Exception $e) {
					$errors[$key] = array('access_denied'=>$e->getMessage());
				}
			}
		}
		
		if (empty($errors))
			$this->_deleteRecords = array();
		else {
			$error_string = '';
			foreach($errors as $error)
				$error_string .= implode("<br>", $error)."<br>";
			$this->response['deleteFeedback'] = str_replace("{count}", count($errors), \GO::t("Errors occured while trying to delete {count} records")) . "<br><br>" . $error_string;
		}
		return empty($errors);
	}

	/**
	 * Fetch the next record from the PDO statement.
	 * Format it using the _columnModel's formatMode() function
	 * Or return false if there are no more records
	 * @return \GO\Base\Db\ActiveRecord
	 */
	public function nextRecord() {
		if (!isset($this->_stmt))
			$this->_stmt = $this->createStatement();
		
		$model = $this->_stmt->fetch();
		return $model ? $this->_columnModel->formatModel($model) : false;
	}

	/**
	 * Return total amount of record for the statement (without limit)
	 * @return integer Number of total Records
	 */
	public function getTotal() {
		if (!isset($this->_stmt))
			$this->_stmt = $this->createStatement();
		return isset($this->_stmt->foundRows) ? $this->_stmt->foundRows : $this->_stmt->rowCount();
	}

	
	/**
	 * If there are summarizeColumn provided select and format them
	 * Otherwise this returns false
	 * @return \GO\Base\Model a formatted summary
	 */
	public function getSummary() {
		$summarySelect = $this->_columnModel->getSummarySelect();
		if($summarySelect===false)
			return false;
		
//		$sumParams = \GO\Base\Db\FindParams::newInstance()->single()->select($summarySelect)->criteria($this->_extraFindParams->getCriteria());
		
		$findParams = $this->createFindParams(false);
		$sumParams = $findParams->single()->export(false)->select($summarySelect)->order(null,"");
		
		$sumRecord = \GO::getModel($this->_modelClass)->find($sumParams);
		if($sumRecord)
			return $this->_columnModel->formatSummary($sumRecord);
	}

	/**
	 * Returns the formatted data for an ExtJS grid.
	 * Also deletes the given delete_keys.
	 * @return array $this->response 
	 */
	public function getData() {

		

		if (!empty($this->_deleteRecords))
			$this->response['deleteSuccess'] = $this->processDeleteActions();

		if (!isset($this->_stmt))
			$this->_stmt = $this->createStatement();

		$this->_loaded = true;

		$columns = $this->_columnModel->getColumns();
		if (empty($columns))
			throw new \Exception('No columns given for this store');

		if(!isset($this->response['results']))
			$this->response['results']=array();

		$this->response['success'] = true;
		$this->response['total'] = $this->getTotal();

		if($summary = $this->getSummary())
			$this->response['summary'] = $summary;
		if (!empty($this->_multiSel))
			$this->_multiSel->setButtonParams($this->response);
		while ($record = $this->nextRecord())
			$this->response['results'][] = $record;
		return $this->response;
	}

	public function getDeleteSuccess() {
		return isset($this->response['deleteSuccess']) ? $this->response['deleteSuccess'] : null;
	}
	
	public function getFeedBack() {
		return isset($this->response['feedback']) ? $this->response['feedback'] : null;
	}

	/**
	 * Returns an array with the stores records
	 * @return array records
	 */
	public function getRecords() {
		$response = $this->getData();
		return $response['results'];
	}
	
	public function getModels() {
		return $this->_stmt->fetchAll();
	}

	/**
	 * Select Items that belong to one of the selected Models
	 * Call this in the grids that get filterable by other selectable stores
	 * @param StringHelper $requestParamName That key that will hold the seleted item in go_setting table
	 * @param StringHelper $selectClassName Name of the related model (eg. \GO\Notes\Model\Category)
	 * @param StringHelper $foreignKey column name to match the related models PK (eg. category_id)
	 * @param boolean $checkPermissions check Permission for item defaults to true
	 * @param StringHelper $prefix a prefix for the request param that can change every store load
	 * @param array $extraPks valid pks of models not in the database
	 * 
	 * @return \GO\Base\Component\MultiSelectGrid
	 */
	public function multiSelect($requestParamName, $selectClassName, $foreignKey, $checkPermissions = null,$prefix="",$extraPks=array(), $keyTableAlias='t') {
		$this->_multiSel = new \GO\Base\Component\MultiSelectGrid(
										$requestParamName,
										$selectClassName,
										$this,
										$this->_requestParams,
										$checkPermissions,
										$prefix,
										$extraPks
		);
		$this->_multiSel->addSelectedToFindCriteria($this->_extraFindParams, $foreignKey, $keyTableAlias);
		$this->_multiSel->setStoreTitle();
		
		
		return $this->_multiSel;


	}

	/**
	 * Call this in the selectable stores that effect other grids by selecting values
	 * @param StringHelper $requestParamName
	 * @param boolean $checkPermissions check Permission for item defaults to true
	 * @param StringHelper $prefix a prefix for the request param that can change every store load
	 * @param array $extraPks valid pks of models not in the database
	 * @param boolean $defaultSelect Set  to false if you do not when to select the first item when nothing is selected
	 * @return \GO\Base\Component\MultiSelectGrid
	 */
	public function multiSelectable($requestParamName,$checkPermissions = null,$prefix="",$extraPks=array(),$defaultSelect=true) {
		$this->_multiSel = new \GO\Base\Component\MultiSelectGrid($requestParamName, $this->_modelClass, $this, $this->_requestParams, $checkPermissions,$prefix,$extraPks);
		if($defaultSelect)
			$this->_multiSel->setFindParamsForDefaultSelection($this->_extraFindParams);
		$this->_multiSel->formatCheckedColumn();
		
		return $this->_multiSel;
	}

	/**
	 * The buttons params to be attached to the response
	 * @return array button params
	 */
	public function getButtonParams() {
		$buttonParams = array();
		$this->_multiSel->setButtonParams($buttonParams);
		if (isset($buttonParams['buttonParams']))
			return $buttonParams['buttonParams'];
		else
			return false;
	}

}
