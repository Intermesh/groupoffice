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
 * The Store provider is useful to generate response for a grid store in a 
 * controller.
 * @TODO: RENAME THIS STORE TO DBSTORE so it will be DBStore. NEEDS TO BE FIXED IN THE WHOLE PROJECT THEN
 * 

 * 
 * @version $Id: Group.php 7607 2011-08-04 13:41:42Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package GO.base.data
 */

namespace GO\Base\Data;


class Store extends AbstractStore {
	
  /**
   *
   * @var \GO\Base\Db\ActiveStatement 
   */
  private $_stmt;
	
	
	protected $_limit;
	protected $_defaultSortOrder='';
	protected $_defaultSortDirection='ASC';
  
	/**
	 * Create a new grid with column model and query result
	 * 
	 * @param \GO\Base\Db\ActiveRecord $model
	 * @param array $excludeColumns Exlude columns if you autoload all columns
	 * @param array $excludeColumns Set the columns to load from the model. If ommitted it will load all columns.
	 * @param array $findParams
	 * @return Store 
	 */
	public static function newInstance($model, $excludeColumns=array(), $includeColumns=array())
	{
		$cm = new ColumnModel($model, $excludeColumns, $includeColumns=array());		
		$store = new self($cm);
		return $store;
		
	}
	
	/**
	 * Set the default column to sort on.
	 * @param String / Array $order 
	 */
	public function setDefaultSortOrder($order, $direction='ASC'){
		$this->_defaultSortOrder=$order;
		$this->_defaultSortDirection=$direction;
	}

	/**
	 * Set the statement that contains the models for the grid data.
	 * Run the statement after you construct this grid. Otherwise the delete
	 * actions will be ran later and they will still be in the result set.
	 * 
	 * @param \GO\Base\Db\ActiveStatement $stmt 
	 */
	public function setStatement(\GO\Base\Db\ActiveStatement $stmt){
		$this->_stmt = $stmt;
		
		
//		if(!$this->_columnModelProvided)
//			$this->_columns = array_merge(array_keys($stmt->model->columns), $this->_columns);
//		
//		if($stmt->model->customfieldsRecord){
//			
//			$cfColumns = array_keys($stmt->model->customfieldsRecord->columns);
//			array_shift($cfColumns); //remove link_id column
//			
//			$this->_columns=array_merge($this->_columns, $cfColumns);
//		}
		
//    if (isset($stmt->relation))
//      $this->_relation = $stmt->relation;
	}
	
  /**
   * Handle a delete request when a grid loads. For proper use, make sure that
	 * the array $params['delete_keys'] combined with the array $extraPkValue are
	 * enough to identify the model that have to be deleted.
	 * 
	 * @param array $params The action request params
   * @param type $deleteModelName Name of the model to delete
   * @param array $extraPkValue If your model has more then one pk. Then you can supply the other keys in an array eg. array('group_id'=>9,'user_id'=>0)
   */
	public function processDeleteActions($params, $deleteModelName, $extraPkValue=false){
		
		if(isset($this->_stmt))
			throw new \Exception("processDeleteActions should be called before setStatement. If you run the statement before the deletes then the deleted items will still be in the result.");
		
		if (isset($params['delete_keys'])) {
      try {
        $deleteIds = json_decode($params['delete_keys']);
		  if(empty($deleteIds)) {
			  return;
		  }
        foreach ($deleteIds as $modelPk) {

//          $deleteModelName = $this->_stmt->model->className();
//
//          //If this is a MANY_MANY relational query. For example when you're displaying the users in a 
//          // group in a grid then you don't want to delete the \GO\BAse\Model\User but the linking table record \GO\Base\MOdel\UserGroup
//          if (!empty($this->_stmt->relation)) {
//            $relations = $this->stmt->model->relations();
//            if (isset($relations[$this->stmt->relation]['linksModel']))
//              $deleteModelName = $relations[$this->stmt->relation]['linksModel'];
//          }
          $staticModel = call_user_func(array($deleteModelName,'model'));
          if($extraPkValue){           
            
            //get the primary key names of the delete model in an array
            $primaryKeyNames = $staticModel->primaryKey();
            
            $newPk=array();
            foreach($primaryKeyNames as $name){
              
              if(isset($extraPkValue[$name]))
              {
                //pk is supplied in the extra values
                $newPk[$name]=$extraPkValue[$name];
              }else
              {
                //it's not set in the extra values so it must be the key passed in the request
                $newPk[$name]=$modelPk;
              }
            }
            
            $modelPk=$newPk;
          }

          $model = $staticModel->findByPk($modelPk);
					if (!empty($model))
						if(!$model->delete()){
							Throw new \Exception('Failed to run delete from model!');
						}
        }
				if(!isset($this->response['deleteSuccess']) || $this->response['deleteSuccess'] !== false) {
					$this->response['deleteSuccess'] = true;
				}
      } catch (\Exception $e) {
        $this->response['deleteSuccess'] = false;
        $this->response['deleteFeedback'] = $e->getMessage();
				if(\GO::config()->debug)
					$this->response['deleteTrace'] = $e->getTraceAsString ();
      }
    }
	}
	

	public function nextRecord() {
		
		$model = $this->nextModel();
		
		return $model ? $this->getColumnModel()->formatModel($model) : false;
	}
	
	
	public function nextModel(){		
		
		$model = $this->_stmt->fetch();
		
//		\GO::debugPageLoadTime("fetch");
		
		return $model;
	}
	
	public function getTotal() {
		return isset($this->_stmt->foundRows) ? $this->_stmt->foundRows : $this->_stmt->rowCount();
	}

  /**
   * Returns the data for the grid.
   * Also deletes the given delete_keys.
   *
   * @return array $this->response 
   */
  public function getData() {
		
		if(!isset($this->_stmt))
			throw new \Exception('You must provide a statement with setStatement()');

		$columns = $this->_columnModel->getColumns();
    if (empty($columns))
      throw new \Exception('No columns given for this grid.');   		
		
		while ($record = $this->nextRecord()) {			
			$this->response['results'][] = $record;
		}
		$this->response['total']=$this->getTotal();
		$this->response['success']=true;
    return $this->response;
  }
  
  public function getRecords() {
	$response = $this->getData();
	return $response['results'];
  }
	
	private $_extraSortColumnNames=array();
	private $_extraSortDirections=array();
	
	/**
	 * Add additional sort columns that will always be appended.
	 * 
	 * @param array $columnNames
	 * @param array $directions 
	 */
	public function addExtraSortColumns(array $columnNames, array $directions){
		$this->_extraSortColumnNames=$columnNames;
		$this->_extraSortDirections=$directions;
	}
  
  

  /**
   * Returns a set of default parameters for use with a grid.
	 * 
   * @var array $requestParams The request parameters passed to the controller. (Similar to $_REQUEST)
   * @var \GO\Base\Db\FindParams $extraFindParams Supply parameters to add to or override the default ones
   * @return \GO\Base\Db\FindParams defaultParams 
   */
  public function getDefaultParams($requestParams, $extraFindParams=false) {
		
		$sort = !empty($requestParams['sort']) ? $requestParams['sort'] : $this->_defaultSortOrder;
		$dir = !empty($requestParams['dir']) ? $requestParams['dir'] : $this->_defaultSortDirection;

		$sort= str_replace('customFields', 'cf', $sort);
		
		if (!is_array($sort))
			$sort=empty($sort) ? array() : array($sort);
		
		if(!empty($requestParams['groupBy']))
			array_unshift ($sort, $requestParams['groupBy']);

		if (!is_array($dir))
			$dir=count($sort) ? array($dir) : array();
		
		if(isset($requestParams['groupDir']))
			array_unshift ($dir, $requestParams['groupDir']);
		
		$sort = $this->getColumnModel()->getSortColumns($sort);
		
		$sortCount = count($sort);
		$dirCount = count($dir);
		for($i=0;$i<$sortCount-$dirCount;$i++){
			$dir[]=$dir[$dirCount-1];
		}
		
		$sort = array_merge($sort, $this->_extraSortColumnNames);
		$dir = array_merge($dir, $this->_extraSortDirections);
		
//		for($i=0;$i<count($sort);$i++){
//			$sort[$i] = $this->getColumnModel()->getSortColumn($sort[$i]);
//		}
		
		$findParams = \GO\Base\Db\FindParams::newInstance()						
						->joinCustomFields()
						->order($sort, $dir);
		
		if(empty($requestParams['dont_calculate_total'])){
			$findParams->calcFoundRows();
		}
		
		//do not prefix search query with a wildcard by default. 
		//When you start a query with a wildcard mysql can't use indexes.
		//Correction: users can't live without the wildcard at the start.
		
		if(!empty($requestParams['query'])) {
//			$findParams->searchQuery ('%'.preg_replace ('/[\s*]+/','%', $requestParams['query']).'%');
			$findParams->searchQuery(preg_replace('/[\s*]+/', ' ', $requestParams['query']));
		}

		if(isset($requestParams['limit']))
			$findParams->limit ($requestParams['limit']);
		else
			$findParams->limit (\GO::user()->max_rows_list);
		
		if(!empty($requestParams['start']))
			$findParams->start ($requestParams['start']);
		
		if(isset($requestParams['permissionLevel']))
			$findParams->permissionLevel ($requestParams['permissionLevel']);
		
		if($extraFindParams)
			$findParams->mergeWith($extraFindParams);
		
		return $findParams;
		
    
  }
}

