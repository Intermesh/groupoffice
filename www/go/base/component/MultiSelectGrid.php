<?php


namespace GO\Base\Component;


class MultiSelectGrid {

	private $_requestParamName;
	/**
	 * The selected model ID's
	 * 
	 * @var array
	 */
	public $selectedIds=array();
	private $_modelName;
	private $_models;
	/**
	 *
	 * @var \GO\Base\Data\AbstractStore 
	 */
	private $_store;
	
	
	private $_checkPermissions=false;
	
	/**
	 * When the store of the multiselectgrid changed it need a unique prefix for saving
	 * @var StringHelper unique store loading id
	 */
	private $_requestParamPrefix='';
	
	/**
	 * The extra PK's of related model that might not excist in the database
	 * @var array valid keys for selection 
	 */
	private $_extraPks;

	/**
	 * A component for a MultiSelectGrid. eg. Select multiple addressbooks to display contacts.
	 * 
	 * You must create two instances. One in AddressbookController and the other one in ContactController.
	 * 
	 * Create them in \GO\Base\Controller\AbstractModelController::beforeStoreStatement
	 * 
	 * @param StringHelper $requestParamName The name of the request parameter. It's the id of the MultiSelectGrid in the ExtJS view.
	 * @param StringHelper $modelName Name of the model that the selected ID's belong to.
     * @param \GO\Base\Data\AbstractStore $store the store that should be filtered
	 * @param array $requestParams The request parameters
	 * @param boolean $checkPermission  Enable permission checking on this model. This makes sure that only 
	 * @param StringHelper $prefix a prefix for the request param that can change every store load
	 * @param array $extraPks valid pks of models not in the database
	 * readbable addressbooks are used with contacts for example.
	 * This will disable acl checking for the contacts query which improves performance.
	 */
	public function __construct($requestParamName, $modelName, \GO\Base\Data\AbstractStore $store, array $requestParams, $checkPermissions=null, $prefix='', $extraPks=array()) {

		$this->_requestParamName = $prefix.$requestParamName;
		$this->_store = $store;
		$this->_modelName = $modelName;
		$this->_extraPks = $extraPks;
		
		if(\GO::config()->debug && !class_exists($modelName))
			throw new \Exception("Invalid argument \$modelName for MultiSelectGrid. Class $modelName does not exist.");
		
		if($checkPermissions===null)
		  $checkPermissions=(\GO::getModel($this->_modelName)->aclField()!=false);
		$this->_checkPermissions=$checkPermissions;
		
		if(empty($requestParams['noMultiSelectFilter']))
			$this->_setSelectedIds($requestParams);
	}
	
	
	/**
	 * Call this if you want the first item or all items to be selected by default.
	 * 
	 * @param \GO\Base\Db\FindParams $findParams
	 * @param boolean $selectAll 
	 */
	public function setFindParamsForDefaultSelection(\GO\Base\Db\FindParams $findParams, $selectAll=false){
		if(empty($this->selectedIds)){
			$findParamsCopy = clone $findParams;
			$findParamsCopy->ignoreAcl(false)->debugSql();
			if(!$selectAll){				
				
				$findParamsCopy->limit(1);
				$model = \GO::getModel($this->_modelName)->find($findParamsCopy)->fetch();

				if($model)
					$this->selectedIds=array($model->pk);		
			}else{
				$stmt = \GO::getModel($this->_modelName)->find($findParamsCopy);
				while($model = $stmt->fetch()){
					$this->selectedIds[]=$model->pk;
				}
			}			
			$this->_save();
		}
	}

	private function _save(){
		\GO::config()->save_setting('ms_' . $this->_requestParamName, implode(',', $this->selectedIds), \GO::session()->values['user_id']);
	}

	private function _setSelectedIds(array $requestParams) {
		if (isset($requestParams[$this->_requestParamName])) {
			$this->selectedIds = json_decode($requestParams[$this->_requestParamName], true);
			$this->_save();
		} else {
			$selectedPks = \GO::config()->get_setting('ms_' . $this->_requestParamName, \GO::session()->values['user_id']);
			$this->selectedIds = empty($selectedPks) && $selectedPks!=='0' ? array() : explode(',', $selectedPks);
			
		}
        
		//add all the allowed models if it's empty. It's faster to find all allowed 
		//addressbooks then too join the acl table.
		//That's why this component add's ignoreAcl() to the findParams automatically 
		//in the addSelectedToFindCriteria() function. The permissions are checked by 
		//the following query.
		
		if($this->_checkPermissions && empty($this->selectedIds)){
			$stmt = \GO::getModel($this->_modelName)->find();
			foreach($stmt as $model){
				$this->selectedIds[]=$model->pk;
			}
			$this->_save();
		}
	}
	
	/**
	 * Format the "checked" column for the store response.
	 * Use this in the model controller of the selected items. eg. Use in AddressbookController and not in ContactController. 
	 */
	public function formatCheckedColumn(){
		
		//validate selection only when we display the grid
		$this->_validateSelection();
		
		$this->_store->getColumnModel()->
						formatColumn('checked','in_array($model->id, $multiSelectGrid->selectedIds)', array('multiSelectGrid'=>$this));
//		$this->_save();
	}

	/**
	 * Add the selected id's to the findCriteria. You use this in the other controller. eg. ContactController and not AddressbookController.
	 * Should be called in \GO\Base\Controller\AbstractModelController::beforeStoreStatement
	 * Will be called in \GO\Base\Data\DbStore::multiSelect()
	 * @param \GO\Base\Db\FindParams $findParams (object reference)
	 * @param StringHelper $columnName database column to match keys to
	 * @param StringHelper $tableAlias table alias of the column to match
	 * @param boolean $useAnd use AND when adding where condition
	 * @param boolean $useNot use NOT when adding where condition
	 */
	public function addSelectedToFindCriteria(\GO\Base\Db\FindParams &$findParams, $columnName, $tableAlias = 't', $useAnd = true, $useNot = false) {
	
		
		$selectedCount = count($this->selectedIds);
		
		//ignore here. Permissions are checked in by _setSelectedIds.
		if($this->_checkPermissions){
			
//			$this->_validateSelection();
		
			if($selectedCount)
				$findParams->ignoreAcl();
		}


		if($selectedCount){			
			if($selectedCount>1){				
				$tableName = "ms_".$this->_requestParamName;
				$findParams->getCriteria()->addInTemporaryTableCondition($tableName,$columnName, $this->selectedIds, $tableAlias, $useAnd, $useNot);
			}else
			{
//				$findParams->getCriteria()->addInCondition($columnName, $this->selectedIds, $tableAlias, $useAnd, $useNot);
				$findParams->getCriteria()->addCondition($columnName, $this->selectedIds[0], $useNot ? '!=' : '=',$tableAlias, $useAnd);
			}			
		}
		
//		$findParams->debugSql();
		
		
//		$this->_save();
	}
	
	/**
	 * Checks if all selected id's are accessible. If not it removes the models 
	 * from the selection. 
	 */
	private function _validateSelection(){
		$models = $this->_getSelectedModels();
		if(count($models) != count($this->selectedIds)){
			//one of the selections could not be fetched. This may happen when something is
			//deleted or a user doesn't have permissions anymore.
			//remove the id's from the selection.
			$this->selectedIds=array();
			foreach($this->_models as $model){
				$this->selectedIds[]=$model->pk;
			}
			$this->_save();			
		}
	}

	/**
	 * Get all selected models
	 * 
	 * @return \GO\Base\Db\ActiveRecord[] 
	 */
	private function _getSelectedModels(){
//		throw new \Exception();
		if(!isset($this->_models))
		{			
			$this->_models=array();
			foreach ($this->selectedIds as $modelId) {
				try{
					$model = \GO::getModel($this->_modelName)->findByPk($modelId);				
					if($model)
						$this->_models[]=$model;

				}
				catch(\Exception $e){
					//might happen when a user no longer has access to a selected model
				}
			}
			foreach($this->_extraPks as $pk) {
				if(in_array($pk, $this->selectedIds)) {
					$model = \GO::getModel($this->_modelName);
					$model->pk = $pk;
					$this->_models[] = $model;
				}
			}
		}
		return $this->_models;
	}

	/**
	 * Set the title for the store. This will be outputted in the JSON response.
	 * 
	 * Should be called in \GO\Base\Controller\AbstractModelController::beforeStoreStatement
	 * 
	 * @param \GO\Base\Data\AbstractStore $store
	 * @param StringHelper $titleAttribute 
	 */
	public function setStoreTitle( $titleAttribute = 'name') {
//		$titleArray = array();
//		$models = $this->_getSelectedModels();
//		foreach ($models as $model) 
//			$titleArray[] = $model->$titleAttribute;
//		
//		if(count($titleArray))
//			$this->_store->setTitle(implode(', ',$titleArray));		
		$count = count($this->selectedIds);
		if($count==1){
			$model = \GO::getModel($this->_modelName)->findByPk($this->selectedIds[0], false, !$this->_checkPermissions);
			if (isset($model->$titleAttribute))
				$this->_store->setTitle(\GO\Base\Util\StringHelper::encodeHtml ($model->$titleAttribute));	
		}else
		{
		$this->_store->setTitle($count.' '.\GO::t("selected"));		
		}
	}
	
	/**
	 * Return information for add and delete buttons in the view. It tells wether add or delete is allowed.
	 * 
	 * @param array $response 
	 */
	public function setButtonParams(&$response){
		$models = $this->_getSelectedModels();
		foreach ($models as $model) {		
			if(!isset($response['buttonParams']) && \GO\Base\Model\Acl::hasPermission($model->getPermissionLevel(),\GO\Base\Model\Acl::CREATE_PERMISSION)){

				//instruct the view for the add action.
				$response['buttonParams']=array('id'=>$model->id,'name'=>$model->name, 'permissionLevel'=>$model->getPermissionLevel());
			}
		}
	}

}
