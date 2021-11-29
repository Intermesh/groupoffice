<?php
/*
 * Copyright Intermesh BV
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */

/**
 * Extend this class for your models. It implements default actions for
 * 1. The grid
 * 2. The edit dialog
 * 3. The display panel
 * 
 * @package GO.base.controller
 * @version $Id: File.class.inc.php 7607 2011-06-15 09:17:42Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Merijn Schering <mschering@intermesh.nl>
 *
 */

namespace GO\Base\Controller;


class AbstractModelController extends AbstractController {

	/**
	 *
	 * @var StringHelper 
	 */
	protected $model;
	
	
	/**
	 * Can be overriden if you have a primary key that's not 'id' or is an array.
	 * Must return false if the PK is empty.
	 * 
	 * @param array $params
	 * @return mixed 
	 */
	protected function getPrimaryKeyFromParams($params){
	
		return empty($params['id']) ? false : $params['id'];
	}
	

	/**
	 * The default action when the form in an edit dialog is submitted.
	 */
	protected function actionSubmit($params)
	{

		$model = $this->getModelFromParams($params);

		$ret = $this->beforeSubmit($response, $model, $params);
		
		if($ret!==false)
		{		
			$model->setAttributes($params);
			
			$modifiedAttributes = $model->getModifiedAttributes();
			if($model->save() ){
				$response['success'] = true;

				$response['id'] = $model->pk;

				//If the model has it's own ACL id then we return the newly created ACL id.
				//The model automatically creates it.
				if ($model->aclField() && !$model->isJoinedAclField) {
					$response[$model->aclField()] = $model->{$model->aclField()};
				}


				if (!empty($params['link']) && $model->hasLinks()) {

					//a link is sent like  \GO\Notes\Model\Note:1
					//where 1 is the id of the model

					$linkProps = explode(':', $params['link']);			
					$linkModel = \GO::getModel($linkProps[0])->findByPk($linkProps[1]);
					$model->link($linkModel);			
				}

				if(!empty($_FILES['importFiles'])){

					$attachments = $_FILES['importFiles'];
					$count = count($attachments['name']);

					$params['enclosure'] = $params['importEnclosure'];
					$params['delimiter'] = $params['importDelimiter'];

					for($i=0;$i<$count;$i++){
						if(is_uploaded_file($attachments['tmp_name'][$i])) {
							$params['file']= $attachments['tmp_name'][$i];
							//$params['model'] = $params['importModel'];

							$controller = new $params['importController'];

							$controller->run("import",$params,false);
						}
					}
				}


				$this->afterSubmit($response, $model, $params, $modifiedAttributes);

				$this->fireEvent('submit', array(
					&$this,
					&$response,
					&$model,
					&$params,
					$modifiedAttributes
			));

			}else{
				$response['success']=false;
				//can't use <br /> tags in response because this goes wrong with the extjs fileupload hack with an iframe.
				$response['feedback']=sprintf(\GO::t("Couldn't save %s:"),strtolower($model->localizedName))."\n\n" . implode("\n", $model->getValidationErrors())."\n";			
				if(empty($_FILES)){ //if you return html when using the extjs iframe file upload hack it throws a json exception
					$response['feedback']=nl2br($response['feedback']);
				}
				
				$response['validationErrors']=$model->getValidationErrors();
			}	
		}
		return $response;
	}

	/**
	 * Useful to override
	 * 
	 * @param array $response The response array
	 * @param mixed $model
	 */
	protected function beforeSubmit(&$response, &$model, &$params) {
		
	}

	/**
	 * Useful to override
	 *
	 * @param array $response The response array
	 * @param mixed $model
	 */
	protected function afterSubmit(&$response, &$model, &$params, $modifiedAttributes) {
		
	}
	
	protected function getModelFromParams($params){
		$modelName = $this->model;
		
		$pk = $this->getPrimaryKeyFromParams($params);
		if(!empty($pk)){
			$model = \GO::getModel($modelName)->findByPk($pk);
			
			if(!$model)
				throw new \GO\Base\Exception\NotFound();
			
		}else{
			$model = new $modelName;
			
			//We need to set the attributes here so we can pass an addressbook_id for a contact for example.
			//Without this attribute we can't check the permissions for the contact properly.
			$model->setAttributes($params);
		}
		
		return $model;
	}

	/**
	 * Action to load a single record.
	 */
	protected function actionLoad($params) {

		$model = $this->getModelFromParams($params);
		
		$response = array();

		if(!$this->checkLoadPermissionLevel($model)){
			throw new \GO\Base\Exception\AccessDenied();
		}

		$response = $this->beforeLoad($response, $model, $params);

		$response['data'] = !empty($response['data']) ? array_merge($response['data'],$model->getAttributes()) : $model->getAttributes();
		$response['data']['permission_level']=$model->getPermissionLevel();
		$response['data']['write_permission']=true;
			
		
		if($model->hasCustomfields())
		{
			$response['data']['customFields'] = $model->getCustomFields();
		}
						
		$response['success'] = true;

		$response = $this->_loadComboTexts($response, $model);

		$response = $this->afterLoad($response, $model, $params);
		
		$this->fireEvent('load', array(
				&$this,
				&$response,
				&$model,
				&$params
		));

		return $response;
	}

	protected function beforeLoad(&$response, &$model, &$params) {
		return $response;
	}
	
	
	protected function afterLoad(&$response, &$model, &$params) {
		return $response;
	}

	/**
	 * List all fields that require a remote text to load for a remote combobox.
	 * eg. with a model you want to provide the category name so that that the
	 * category combo store does not need to be loaded to show it.
	 *
	 * You would list that like this:
	 *
	 * 'category_id'=>'$model->category->name'
	 *
	 * The category name would be looked up in the model model ->category->name.
	 * A relation for this must be defined. See ActiveRecord->relations.
	 *
	 *
	 * @return array remote combo mappings
	 */
	protected function remoteComboFields(){
		return array();
	}

	protected function _loadComboTexts($response, $model) {

		$response['remoteComboTexts'] = array();

		
		set_error_handler(function(){});
		foreach ($this->remoteComboFields() as $property => $map) {			
			if(is_numeric($property))
				throw new \Exception("remoteComboFields() must return a key=>value array.");			
			
			$value='';
			$eval = '$value = '.$map.';';
			eval($eval);
						
			$response['remoteComboTexts'][$property] = $value;
			
			//hack for comboboxes displaying 0 instead of the emptyText in extjs
			if(isset($response['data'][$property]) && $response['data'][$property]===0)
				$response['data'][$property]="";
		}
		
		restore_error_handler();

		return $response;
	}
	

	/**
	 * Override this function to supply additional parameters to the 
	 * \GO\Base\Db\ActiveRecord->find() function
	 * 
	 * @var array() $params The request parameters of actionStore
	 * 
	 * @return \GO\Base\Db\FindParams parameters for the \GO\Base\Db\ActiveRecord->find() function 
	 */
	protected function getStoreParams($params) {
		return array();
	}
	
	/**
	 * Override to pass an array of columns to exclude in the store.
	 * @return array 
	 */
	protected function getStoreExcludeColumns(){
		return array();
	}
	
	public function formatStoreRecord($record, $model, $store){
		return $record;
	}

	/**
	 * Override this function to format the grid record data.
	 * @TODO: THIS DESCRIPTION IS NOT OK
	 * @param array $record The grid record returned from the \GO\Base\Db\ActiveRecord->getAttributes
	 * @param \GO\Base\Db\ActiveRecord $model
	 * @return array The grid record data
	 */
	protected function getStoreColumnModel($withCustomfields=true) {
		$cm =  new \GO\Base\Data\ColumnModel();
		$cm->setColumnsFromModel(\GO::getModel($this->model), $this->getStoreExcludeColumns(),array(),$withCustomfields);	
		return $cm;
	}
	
	/**
	 * Override this function to format the grid record data.
	 * @TODO: THIS DESCRIPTION IS NOT OK
	 * @param array $record The grid record returned from the \GO\Base\Db\ActiveRecord->getAttributes
	 * @param \GO\Base\Db\ActiveRecord $model
	 * @return array The grid record data
	 */
	protected function prepareStore(\GO\Base\Data\Store $store) {
		
		return $store;
	}
	
  /**
   * Override this function to format columns if necessary.
   * You can also use formatColumn to add extra columns
   * 
   * @param \GO\Base\Data\ColumnModel $columnModel
   * @return \GO\Base\Data\ColumnModel 
   */
  protected function formatColumns(\GO\Base\Data\ColumnModel $columnModel){
    return $columnModel;
  }
	
	protected function processStoreDelete($store, &$params){
		$store->processDeleteActions($params, $this->model);
	}
  
  /**
   * The default grid action for the current model. This action also handles:
	 * 
	 * 1. Advanced queries. See _handleAdvancedQuery, the contacts advanced search
	 * use case in Group-Office, and
	 * \GO\Addressbook\Controller\Contact::beforeIntegrateRegularSql.
	 * 2. Deleting models
   */
  protected function actionStore($params){	
    $modelName = $this->model;  

    $store = new \GO\Base\Data\Store($this->getStoreColumnModel());	
		$store->getColumnModel()->setFormatRecordFunction(array($this, 'formatStoreRecord'));		
		
		if(!empty($params["forEditing"]))
			$store->getColumnModel ()->setModelFormatType ("formatted");
		
		$response=array("success"=>true,"results"=>array());
		
		if($this->beforeStore($response, $params, $store)===false)
			return $response;
		
		$this->processStoreDelete($store, $params);


		$columnModel = $store->getColumnModel();
		$this->formatColumns($columnModel);		
	
		$this->prepareStore($store);
		
		$storeParams = $store->getDefaultParams($params)->mergeWith($this->getStoreParams($params));
		
		if (!empty($params['advancedQueryData']))
			$this->_handleAdvancedQuery($params['advancedQueryData'],$storeParams);
		
		$this->beforeStoreStatement($response, $params, $store, $storeParams);
			
		$store->setStatement(\GO::getModel($modelName)->find($storeParams));
		
		$response = array_merge($response, $store->getData());
		
		
   if($this->afterStore($response, $params, $store, $storeParams)===false)
			return $response;
		
		//this parameter is set when this request is the first request of the module.
		//We pass the response on to the output.
		if(isset($params['firstRun']) && is_array($params['firstRun'])){
			$response=array_merge($response, $params['firstRun']);
		}
		
		$this->fireEvent('store', array(
				&$this,
				&$response,
				&$store,
				&$params));		
		
		return $response;
  }	
	
	protected function afterStore(&$response, &$params, &$store, $storeParams){
		return true;
	}
	
	protected function beforeStore(&$response, &$params, &$store){
		return true;
	}
	
	/**
	 * Fires just before the store SQL statement is executed.
	 * 
	 * @param array $response
	 * @param array $params
	 * @param \GO\Base\Data\AbstractStore $store
	 * @param \GO\Base\Db\FindParams $storeParams
	 * @return array 
	 */
	protected function beforeStoreStatement(array &$response, array &$params, \GO\Base\Data\AbstractStore &$store, \GO\Base\Db\FindParams $storeParams){
		
		$this->fireEvent('beforeStoreStatement', array(
				&$this,
				&$response,
				&$store,
				&$params,
				&$storeParams));		
		
		
		return $response;
	}

	/**
	 * The default action for displaying a model in a DisplayPanel.
	 */
	protected function actionDisplay($params) {

		$response = array('data'=>array(),'success'=>true);
				
		$modelName = $this->model;
		$model = \GO::getModel($modelName)->findByPk($this->getPrimaryKeyFromParams($params));
		
		if(!$model)
			throw new \GO\Base\Exception\NotFound();
		
		$response = $this->beforeDisplay($response, $model, $params);
		
		//todo build in new style. Now it's necessary for old library functions
		//require_once(\GO::config()->root_path.'Group-Office.php');

		$response['data'] = array_merge($response['data'], $model->getAttributes('html'));
		$response['data']['model']=$model->className();
		$response['data']['permission_level']=$model->getPermissionLevel();
		$response['data']['write_permission']=\GO\Base\Model\Acl::hasPermission($response['data']['permission_level'],\GO\Base\Model\Acl::WRITE_PERMISSION);
		if (!empty($model->ctime))
			$response['data']['ctime'] = \GO\Base\Util\Date::get_timestamp ($model->ctime);
		if (!empty($model->mtime))
			$response['data']['mtime'] = \GO\Base\Util\Date::get_timestamp ($model->mtime);
		if (!empty($model->user))
			$response['data']['username'] =  \GO\Base\Util\StringHelper::encodeHtml ($model->user->name);
		if (!empty($model->mUser))
			$response['data']['musername'] = \GO\Base\Util\StringHelper::encodeHtml ($model->mUser->name);

		$response['data']['customfields']=array();
		
		
		if(!isset($response['data']['workflow']) && \GO::modules()->workflow)
			$response = $this->_processWorkflowDisplay($model,$response);
		
		if($model->hasCustomfields()) {
			$response['data']['customFields'] = $model->getCustomFields();
		}

		if (\GO::modules()->lists)
			$response = \GO\Lists\ListsModule::displayResponse($model, $response);
		
		$response = $this->afterDisplay($response, $model, $params);
		
		$this->fireEvent('display', array(
				&$this,
				&$response,
				&$model
		));

		return $response;
	}
	
	private function _processWorkflowDisplay($model,$response){

		
		$response['data']['workflow']=array();
			
		if($model->hasLinks()){

			$workflowModelstmnt = \GO\Workflow\Model\Model::model()->find(
				\GO\Base\Db\FindParams::newInstance()
					->criteria(\GO\Base\Db\FindCriteria::newInstance()
						->addCondition('model_id',$model->id)
						->addCondition('model_type_id',$model->modelTypeId())
					)
					->order('ctime','DESC')
			);

			while($workflowModel = $workflowModelstmnt->fetch()){

				$currentStep = $workflowModel->step;
				if((!$currentStep && $workflowModel->step_id != '-1') || empty($workflowModel->process)) {
					// we have an incomplete workflow with a missing step.
					// delete outself and continue;
					$workflowModel->delete();
					continue;
				}

				$workflowResponse = $workflowModel->getAttributes('html');

	//			$workflowResponse['id'] = $workflowModel->id;
				$workflowResponse['process_name'] = $workflowModel->process->name;
	//			$workflowResponse['due_time'] = $workflowModel->due_time;
	//			$workflowResponse['shift_due_time'] = $workflowModel->shift_due_time;			

				$workflowResponse['user'] = !empty($workflowModel->user_id)?$workflowModel->user->name:'';

				$workflowResponse['approvers'] = array();
				$workflowResponse['approver_groups'] = array();
				$workflowResponse['step_id'] = $workflowModel->step_id;

				if($workflowModel->step_id == '-1'){
					$workflowResponse['step_progress'] = '';
					$workflowResponse['step_name'] = \GO::t("Complete", "workflow");
					$workflowResponse['is_approver']=false;
					$workflowResponse['step_all_must_approve']=false;
				}else{
					$workflowResponse['step_progress'] = $workflowModel->getStepProgress();
					$workflowResponse['step_name'] = $currentStep->name;
					$workflowResponse['step_all_must_approve']=$currentStep->all_must_approve;

					$is_approver = \GO\Workflow\Model\RequiredApprover::model()->findByPk(array("user_id"=>\GO::user()->id,"process_model_id"=>$workflowModel->id,"approved"=>false));

					if($is_approver)
						$workflowResponse['is_approver']=true;
					else
						$workflowResponse['is_approver']=false;

					// Add the approvers of the current step to the response
					$approversStmnt = $workflowModel->requiredApprovers;

					while($approver = $approversStmnt->fetch()){
						$approver_hasapproved = $currentStep->hasApproved($workflowModel->id,$approver->id);
						$workflowResponse['approvers'][] = array('name'=>$approver->name,'approved'=>$approver_hasapproved,'last'=>'0');
					}
					// Set the last flag for the latest approver in the list
					$i = count($workflowResponse['approvers'])-1;

					if($i >= 0)
						$workflowResponse['approvers'][$i]['last'] = "1";

					// Add the approver groups of the current step to the response
					$approverGroupsStmnt = $currentStep->approverGroups;
					while($approverGroup = $approverGroupsStmnt->fetch()){
						$workflowResponse['approver_groups'][] = array('name'=>$approverGroup->name);
					}
				}

				$workflowResponse['history'] = array();
				$historiesStmnt = \GO\Workflow\Model\StepHistory::model()->findByAttribute('process_model_id',$workflowModel->id, \GO\Base\Db\FindParams::newInstance()->select('t.*')->order('ctime','DESC'));
				while($history = $historiesStmnt->fetch()){
					\GO\Base\Db\ActiveRecord::$attributeOutputMode = 'html';


					if($history->step_id == '-1'){
						$step_name = \GO::t("Complete", "workflow");
					}else{
						if($history->step)
							$step_name = $history->step->name;
						else
							$step_name = \GO::t("Step is deleted", "workflow");
					}

					$workflowResponse['history'][] = array(
							'history_id'=>$history->id,
							'step_name'=>$step_name,
							'approver'=>$history->user?$history->user->name:'',
							'ctime'=>$history->ctime,
							'comment'=>$history->comment,
							'status'=>$history->status?"1":"0",
							'status_name'=>$history->status?\GO::t("Approved", "workflow"):\GO::t("Declined", "workflow")
					);

					\GO\Base\Db\ActiveRecord::$attributeOutputMode = 'raw';

				}

				$response['data']['workflow'][] = $workflowResponse;
			}
		}
		
		return $response;
	}
	
	
	
	public function formatTaskLinkRecord($record, $model, $cm){
		
		$statuses = \GO::t("statuses", "tasks");
		
		$record['status']=$statuses[$model->status];
		
		if($model->percentage_complete>0 && $model->status!='COMPLETED')
			$record['status'].= ' ('.$model->percentage_complete.'%)';
		
		return $record;
	}

	
	/**
	 * Useful to override
	 * 
	 * @param array $response The response array
	 * @param mixed $model
	 */
	protected function beforeDisplay(&$response, &$model, &$params) {
		return $response;
	}
	
	/**
	 * Useful to override
	 * 
	 * @param array $response The response array
	 * @param mixed $model
	 */
	protected function afterDisplay(&$response, &$model, &$params) {
		return $response;
	}

	/**
	 * Deletes a specific record.
	 * @param type $params The POST parameters 
	 */
	protected function actionDelete($params) {
		
		$model = \GO::getModel($this->model)->findByPk($this->getPrimaryKeyFromParams($params));
		
		$response=array();
		
		$response = $this->beforeDelete($response, $model, $params);

		$response['success'] = $model->delete();

		$response = $this->afterDelete($response, $model, $params);

		return $response;
	}
	
	/**
	 * Useful to override
	 * 
	 * @param array $response The response array
	 * @param mixed $model
	 */
	protected function beforeDelete(&$response, &$model, &$params) {
		return $response;
	}
	
	/**
	 * Useful to override
	 * 
	 * @param array $response The response array
	 * @param mixed $model
	 */
	protected function afterDelete(&$response, &$model, &$params) {
		return $response;
	}
	
	/**
	 * This function can export the current data to a given format.
	 * 
	 * The $params array has a couple of keys wich you maybe want to set:
	 * 
	 * * title	: The title of the file that will be created. (Without extention)
	 * * type		: Which class needs to be used to export. (Eg. \GO\Base\Export\ExportCSV)
	 * * showHeader : Do you want to show the column headers in the file? (True or False)
	 * 
	 * @param Array $params 
	 */
	protected function actionExport($params) {	
		
		\GO::setMaxExecutionTime(0);
		
		$orientation = false;
		
		$showHeader = false;
  	$humanHeaders = true;
		$includeHidden = false;
		
		if(!empty($params['includeHeaders']))
			$showHeader = true;
		
		if(!empty($params['humanHeaders']))
			$humanHeaders = false;
		
		if(!empty($params['includeHidden']))
			$includeHidden = true;		
		
		$checkboxSettings = array(
			'export_include_headers'=>$showHeader,
			'export_human_headers'=>!$humanHeaders,
			'export_include_hidden'=>$includeHidden
		);
		
		$settings =  \GO\Base\Export\Settings::load();
		$settings->saveFromArray($checkboxSettings);
		
		//define('EXPORTING', true);
		//used by custom fields to format diffently

		if(!empty($params['exportOrientation']) && ($params['exportOrientation']=="H"))
			$orientation = 'L'; // Set the orientation to Landscape
		else
			$orientation = 'P'; // Set the orientation to Portrait

		if(!empty($params['documentTitle']))
			$title = $params['documentTitle'];
		else
			$title = \GO::session()->values[$params['name']]['name'];
			
		$findParams = \GO::session()->values[$params['name']]['findParams'];
		$findParams->limit(0); // Let the export handle all found records without a limit
		$findParams->getCriteria()->recreateTemporaryTables();
		$model = \GO::getModel(\GO::session()->values[$params['name']]['model']);

		$store = new \GO\Base\Data\Store($this->getStoreColumnModel());	
		$store->getColumnModel()->setFormatRecordFunction(array($this, 'formatStoreRecord'));		
		//$store->getColumnModel()->setModelFormatType('formatted'); //no html
		
		$response = array();
		
		$this->beforeStore($response, $params, $store);
		$this->prepareStore($store);
		
		$storeParams = $store->getDefaultParams($params)->mergeWith($this->getStoreParams($params));		
		$this->beforeStoreStatement($response, $params, $store, $storeParams);
		
		$this->afterStore($response, $params, $store, $storeParams);
		
		$columnModel = $store->getColumnModel();
		$this->formatColumns($columnModel);		
		
		
		if(!$includeHidden && !empty($params['columns'])) {
			$includeColumns = explode(',',$params['columns']);
			
			foreach($includeColumns as $incColumn){
				if(!$columnModel->getColumn($incColumn))
					$columnModel->addColumn (new \GO\Base\Data\Column($incColumn,$incColumn));
			}
			
			$columnModel->sort($includeColumns);
			
			foreach($columnModel->getColumns() as $c){
				if(!in_array($c->getDataIndex(), $includeColumns))
					$columnModel->removeColumn($c->getDataIndex());
			}
		} elseif ($includeHidden) {
			
			$columnOrder = array();
			$colNames = $model->getColumns();
			if (\GO::modules()->customfields) {
				$cfRecord = $model->getCustomfieldsRecord(false);
				if ($cfRecord) {
					$cfColNames = $cfRecord->getColumns();
					unset($cfColNames['model_id']);
					$colNames = array_merge($colNames,$cfColNames);
				}
			}
			foreach ($colNames as $colName=>$record)
				$columnOrder[] = $colName;
			$columnModel->sort($columnOrder);
		}
		$extraParams = empty($params['params']) ? array() : json_decode($params['params'], true);

		$this->beforeExport($store, $columnModel,$model, $findParams, $showHeader, $humanHeaders, $title, $orientation, $extraParams);

		if ($includeHidden) {
			$select = $storeParams->getParam('fields');
			$select = trim($select);
			if (!empty($select) && substr($select,0,1)!=',')
				$select = ','.$select;
						
			if (\GO::modules()->customfields && $cfRecord)
				$select = 't.*,cf.*'.$select;
			else
				$select = 't.*'.$select;

			$findParams->select($select);
		}
		
		if(!empty($params['type']))
			$export = new $params['type']($store, $columnModel,$model, $findParams, $showHeader, $humanHeaders, $title, $orientation, $extraParams);
		else
			$export = new \GO\Base\Export\ExportCSV($store, $columnModel, $model, $findParams, $showHeader, $humanHeaders, $title, $orientation, $extraParams); // The default Export is the CSV outputter.

		$export->output();
	}
	
	protected function beforeExport(&$store, &$columnModel,&$model, &$findParams, &$showHeader, &$humanHeaders, &$title, &$orientation, &$extraParams){
		
	}
	
	/**
	 *
	 * Defaults to a CSV import.
	 * 
	 * Custom fields can be specified in the header with cf\$categoryName\$fieldName
	 * 
	 * eg. name,attribute,cf\Test\Textfield
	 * 
	 * Command line:
	 * 
	 * ./groupoffice biling/order/import --file=/path/to/file.csv --delimiter=, --enclosure="
	 * 
	 * @param array $params 
	 */
	protected function actionImport($params) {
				
		$summarylog = new \GO\Base\Component\SummaryLog();
		
		\GO::$disableModelCache=true; //for less memory usage		
		\GO::setMaxExecutionTime(0);
		\GO::session()->closeWriting(); //close writing otherwise concurrent requests are blocked.
		
		$attributeIndexMap = isset($params['attributeIndexMap'])
			? $attributeIndexMap = json_decode($params['attributeIndexMap'],true)
			: array();
		
		if(is_file($params['file'])){
						
			if (!isset($params['importType']))
				$params['importType'] = 'Csv';
			
			$fileClassName = 'GO\\Base\\Fs\\'.$params['importType'].'File';
			if ($params['importType']=='Xls' && !empty($params['maxColumnNr']))
				$importFile = new $fileClassName($params['file'],$params['maxColumnNr']);
			else
				$importFile = new $fileClassName($params['file']);
		
			if(!empty($params['delimiter']))
				$importFile->delimiter = $params['delimiter'];

			if(!empty($params['enclosure']))
				$importFile->enclosure = $params['enclosure'];

			if(php_sapi_name()=='cli'){
				if (!empty($importFile->delimiter))
					echo "Delimiter: ".$importFile->delimiter."\n";
				if (!empty($importFile->enclosure))
					echo "Enclosure: ".$importFile->enclosure."\n";
				echo "File: ".$importFile->path()."\n\n";
			}
			
			if(!$importFile->convertToUtf8())
				exit("ERROR: Could not convert to UTF8. Is the file writable?\n\n");

			$headers = $importFile->getRecord();

			//Map the field headers to the index in the record.
			//eg. name=>2,user_id=>4, etc.
			if (empty($attributeIndexMap)) {
				for ($i = 0, $m = count($headers); $i < $m; $i++) {
					if(substr($headers[$i],0,3)=='cf\\'){				
						$cf = $this->_resolveCustomField($headers[$i]);
						if($cf)
							$attributeIndexMap[$i] = $cf;
					}else
					{
						$attributeIndexMap[$i] = $headers[$i];
					}
				}
			}

			while ($record = $importFile->getRecord()) {
				$attributes = array();
				$model = false;

				foreach($attributeIndexMap as $index=>$attributeName){
					if ($index>=0)
						$attributes[trim($attributeName)] = $record[$index];
				}

				if(!empty($params['updateExisting']) && !empty($params['updateFindAttributes'])){

					$findBy = explode(',', $params['updateFindAttributes']);

					$attr = array();
					foreach($findBy as $attrib){
						$attr[$attrib] = $attributes[$attrib];
					}

					$model = \GO::getModel($this->model)->findSingleByAttributes($attr);				
				}

				if(!$model)
					$model = new $this->model;	
				
				
					// If there are given baseparams to the importer
					if(isset($params['importBaseParams'])) {
						$baseParams = json_decode($params['importBaseParams'],true);
						foreach($baseParams as $attr=>$val){
							$attributes[$attr]=$val;
						}
					}

				
				if($this->beforeImport($params, $model, $attributes, $record)){
					
//					//Unset some default attributes set in the code
//					$defaultAttributes = $model->defaultAttributes();
//					foreach($defaultAttributes as $column => $value)
//						unset($model->{$column});
					
					$columns = $model->getColumns();
					foreach($columns as $col=>$attr){
						if(isset($attributes[$col])){
	//						if($attr['gotype']=='unixtimestamp' || $attr['gotype']=='unixdate'){
	//							$attributes[$col]=strtotime($attributes[$col]);
	//							
							if($attr['gotype']=='number')
							{						
								$attributes[$col]=preg_replace('/[^.,\s0-9]+/','',$attributes[$col]);
							}
						}
					}
					
					foreach ($attributes as $name => $value) {
						$attributes[$name] = trim($value);
						if (empty($attributes[$name])) {
							unset($attributes[$name]);
						}
					}

					// True is set because import needs to be checked by the model.
					$model->setAttributes($attributes, $params['importType']!='Xls');



					$this->_parseImportDates($model);
					
					try{
						if($model->save()){
							$this->afterImport($model, $attributes, $record);
							$summarylog->addSuccessful();
						} else {
							$nameStr = !empty($record[0]) ? $record[0] : '"'.\GO::t("Nameless item").'"';
							$summarylog->addError($nameStr, implode("\n", $model->getValidationErrors()));
						}
					}
					catch(\Exception $e){
						$summarylog->addError($record[0], $e->getMessage());
					}
					
//					try{
//						$model->save();
//						$summarylog->addSuccessful();
//					}
//					catch(\Exception $e){
//						$summarylog->addError($record[0], $e->getMessage());
//					}
				//	$summarylog->add();
				}
			}
						
		} else {
			//$summarylog->addError('NO FILE FOUND', 'There is no file found that can be imported!');
		}
		
		return $summarylog;
	}
	
	protected function beforeImport($params, &$model, &$attributes, $record){
		return true;
	}
	protected function afterImport(&$model, &$attributes, $record){
		return true;
	}
	
	private function _resolveCustomField($header){
		$parts = explode('\\', $header);
		
		if(count($parts)<3)
			return false;
		
		$categoryName = $parts[1];
		$fieldName = $parts[2];
		
		$category = \GO\Customfields\Model\Category::model()->createIfNotExists($this->model,$categoryName);		
		$field = \GO\Customfields\Model\Field::model()->createIfNotExists($category->id,$fieldName);	
		
		return $field->columnName();
	}
	
	protected static function sortByLabel($attrA,$attrB) {
		if ($attrA['label'] == $attrB['label'])
			return 0;		
		return $attrA['label']>$attrB['label'] ? 1 : -1;
	}
	
	protected function actionAttributes($params){
		if(!isset($params['exclude']))
			$params['exclude']=array();
		else
			$params['exclude']=explode(',', $params['exclude']);
		
		$params['exclude_cf_datatypes'] = !empty($params['exclude_cf_datatypes'])
			? json_decode($params['exclude_cf_datatypes'])
			: array();
		
		$params['exclude_attributes'] = !empty($params['exclude_attributes'])
			? json_decode($params['exclude_attributes'])
			: array();
		
		array_push($params['exclude'], 'id','acl_id','files_folder_id');
		
		$response['results']=array();
		
		$model = \GO::getModel($this->model);
		
		$attributes = array();
		
		$unsorted = array();
		$columns = $model->getColumns();
		foreach($columns as $name=>$attr){
			if(!in_array($name, $params['exclude'])
							&& (empty($params['hide_unknown_gotypes']) || !empty($attr['gotype']))
							&& !in_array($name,$params['exclude_attributes'])
				)
				$unsorted[$name]=array('name'=>'t.'.$name,'label'=>$model->getAttributeLabel($name),'gotype'=>$attr['gotype']);				
		}
				
		usort($unsorted,array('\\GO\\Base\\Controller\\AbstractModelController','sortByLabel'));
		foreach($unsorted as $a){
			$attributes[$a['name']]=$a;
		}
		
		$this->afterAttributes($attributes, $response, $params, $model);
		
		foreach($attributes as $field=>$attr)
			$response['results'][]=$attr;
		
		$response['success']=true;
		
		return $response;		
	}
	
	/**
	 * Customizations to the attributes in the store for the view can be done
	 * here. This function should be used in your controller in conjunction with
	 * beforeIntegrateRegularSql(). See for an example: the advanced search use
	 * case in Group-Office, \GO\Addressbook\Controller\Contact::afterAttributes
	 * and \GO\Addressbook\Controller\Contact::beforeIntegrateRegularSql().
	 * @param Array $attributes Array of attributes. Keys of the array are how the
	 * attributes will be known as search record field names after the
	 * view passes an advanced search record to the controller. Values of the
	 * array are how they will be named in the view's advanced search dialog's
	 * select box.
	 * @param Array $response The response to be passed to the client.
	 * @param type $params The request parameters from the client.
	 * @param \GO\Base\Db\ActiveRecord $model 
	 */
	protected function afterAttributes(&$attributes, &$response, &$params, \GO\Base\Db\ActiveRecord $model)
	{
	//unset($attributes['t.company_id']);
	//$attributes['companies.name']=\GO::t("Company", "addressbook");
	//return parent::afterAttributes($attributes, $response, $params, $model);
	}
	
	/**
	 * Adds advanced query request parameters to a findCriteria object. 
	 * The advanced query panel view can be found in GO.query.QueryPanel
	 * 
	 * @param String-or-array $advancedQueryData 
	 * @param \GO\Base\Db\FindParams $storeParams
	 */
	private function _handleAdvancedQuery($advancedQueryData, &$storeParams){
		$advancedQueryData = is_string($advancedQueryData) ? json_decode($advancedQueryData, true) : $advancedQueryData;
		$findCriteria = $storeParams->getCriteria();
		
		$criteriaGroup = \GO\Base\Db\FindCriteria::newInstance();
		$criteriaGroupAnd=true;
		for($i=0,$count=count($advancedQueryData);$i<$count;$i++){
			
			$advQueryRecord=$advancedQueryData[$i];
			
			//change * into % wildcard
			$advQueryRecord['value']=isset($advQueryRecord['value']) ? str_replace('*','%', $advQueryRecord['value']) : '';
			
			if($i==0 || $advQueryRecord['start_group']){
				$findCriteria->mergeWith($criteriaGroup,$criteriaGroupAnd);
				$criteriaGroupAnd=$advQueryRecord['andor']=='AND';
				$criteriaGroup = \GO\Base\Db\FindCriteria::newInstance();
			}
			
			if(!empty($advQueryRecord['field'])){	
				// Give the record a unique id, to enable the programmers to
				// discriminate between advanced search query records of the same field
				// type.
				$advQueryRecord['id'] = $i;
				// Check if current adv. search record should be handled in the standard
				// manner.
				if($this->beforeHandleAdvancedQuery($advQueryRecord, $criteriaGroup ,$storeParams)){
					
					$fieldParts = explode('.',$advQueryRecord['field']);
				
					if(count($fieldParts)==2){
						$field = $fieldParts[1];
						$tableAlias=$fieldParts[0];
					}else
					{
						$field = $fieldParts[0];
						$tableAlias=false;
					}

					if($tableAlias=='t')
						$advQueryRecord['value']=\GO::getModel($this->model)->formatInput($field, $advQueryRecord['value']);						
					elseif($tableAlias=='cf'){
						$advQueryRecord['value']=\GO::getModel(\GO::getModel($this->model)->customfieldsModel())->formatInput ($field, $advQueryRecord['value']);
					}
					
					$cfRec = \GO::getModel($this->model)->getCustomfieldsRecord();
					if($cfRec){
						$cfColRecord = $cfRec->getColumn($field);
					}
					if (!empty($cfColRecord['customfield']->attributes['multiselect']))
						$advQueryRecord['value']='%'.$advQueryRecord['value'].'%';
					
					$criteriaGroup->addCondition($field, $advQueryRecord['value'], $advQueryRecord['comparator'],$tableAlias,$advQueryRecord['andor']=='AND');
				}
			}
		}
			
		$findCriteria->mergeWith($criteriaGroup,$criteriaGroupAnd);
	}
	
	/**
	 * If this function is not overridden in your controller, advanced search will
	 * be only possible for model fields that correspond directly to fields in the
	 * model's database table.
	 * You can catch advanced search query records that have to be handled
	 * differently by overriding this function. For example, if the purpose is to
	 * search through fields of models related to the current model, such as
	 * 'company name' for 'contacts', you can handle it here and return false. The
	 * resulting overridden function should be a switch.
	 * In your controller, this should be used in conjunction with
	 * afterAttributes(). See for an example: the advanced search use case in
	 * Group-Office, \GO\Addressbook\Controller\Contact::afterAttributes and
	 * \GO\Addressbook\Controller\Contact::beforeIntegrateRegularSql().
	 * @param Array $advQueryRecord
	 * @param \GO\Base\Db\FindCriteria $findCriteria
	 * @param \GO\Base\Db\FindParams $storeParams
	 * @return boolean Return true if the current $advQueryRecord must be handled
	 * in the regular way, return false after it has been handled differently.
	 */
	protected function beforeHandleAdvancedQuery($advQueryRecord, \GO\Base\Db\FindCriteria &$findCriteria, \GO\Base\Db\FindParams &$storeParams){
		return true;
	}
	
	/**
	 * Checks if query data $advancedQueryData contains a field with name $fieldName,
	 * and returns the record with that name, if any.
	 * @param String $fieldName
	 * @param Array $advancedQueryData
	 * @return Array The advanced query record, or false if not found. 
	 */
//	protected function getAdvancedQueryRecord($fieldName, $advancedQueryData) {
//		$advancedQueryData = json_decode($advancedQueryData, true);
//		foreach ($advancedQueryData as $record) {
//			if ($record['field']==$fieldName)
//				return $record;
//		}
//		return false;
//	}
	
		/**
	 * Removes record with name $fieldName from $advancedQueryData contains.
	 * @param String $fieldName
	 * @param Array $advancedQueryData
	 */
//	protected function removeAdvancedQueryRecord($fieldName, &$advancedQueryData) {
//		$advancedQueryData = json_decode($advancedQueryData, true);
//		foreach ($advancedQueryData as $k=>$record) {
//			if ($record['field']==$fieldName)
//				unset($advancedQueryData[$k]);
//		}
//		$advancedQueryData = json_encode($advancedQueryData);
//	}
	
	
	/**
	 * Checks for dates in the import model and performs an strtotime on it.
	 * 
	 * @param \GO\Base\Db\ActiveRecord $model 
	 */
	private function _parseImportDates(&$model){
		
		$columns = $model->getColumns();
		
		foreach($columns as $attributeName => $column){
			if(!empty($column['gotype']) && $column['gotype'] == 'date' && !empty($model->$attributeName)){
				$model->$attributeName = date('Y-m-d',strtotime($model->$attributeName));
			}
		}		
	}
	
	/**
	 * Merge models. The supplied "merge_models" array of primary keys will be merged to the given target_model_id.
	 * 
	 * All merge_models will be deleted!
	 * 
	 * @param type $params 
	 */
	public function actionMerge($params){
		
		if(isset($params['entity'])) {
			$entityType = \go\core\orm\EntityType::findByName($params['entity']);
			$params['model_name'] = $entityType->getClassName();
		}
		
		$mergeModels = json_decode($params['merge_models'], true);
		
		$targetModel = \GO::getModel($params['model_name'])->findByPk($params['target_model_id']);
		
		foreach($mergeModels as $id){
			$mergeModel = \GO::getModel($params['model_name'])->findByPk($id);
			$targetModel->mergeWith($mergeModel, !empty($params['merge_attributes']), !empty($params['delete_merge_models']));			
		}
		
		return array('success'=>true);
	}
	
	
	protected function actionSubmitMultiple($params) {
						
		$records = json_decode($params['records'], true);
		$sort = 0;
		foreach ($records as $attributes) {
			$model = \GO::getModel($this->model)->findByPk($attributes['id']);
			$model->setAttributes($attributes);
			
			if($model->getSortOrderColumn())
				$model->{$model->getSortOrderColumn()} = $sort;
				
			$model->save();
			$sort++;
		}

		return array('success' => true);
	}
	
	
	protected function actionCheck($params){
		$model = \GO::getModel($this->model)->findByPk($params["id"]);
		$model->checkDatabase();
		
		echo "Done\n";
	}

	protected function actionReadCSVHeaders($params) {
		$response['success'] = true;
		$response['results'] = array();
		$response['total'] = 0;

		$importFile = new \GO\Base\Fs\CsvFile($_FILES['files']['tmp_name'][0]);
		$importFile->delimiter = $params['delimiter'];
		$importFile->enclosure = $params['enclosure'];

		$response['results'] = $importFile->getRecord();
		$response['total'] = count($response['results']);
		
		return $response;
	}
	
	protected function actionReadXLSHeaders($params) {
		$response['success'] = true;
		$response['results'] = array();
		$response['total'] = 0;

		$importFile = new \GO\Base\Fs\XlsFile($_FILES['files']['tmp_name'][0],false,1);

		$response['results'] = $importFile->getRecord();
		$response['total'] = count($response['results']);
		
		return $response;
	}
	
	
	/**
	 * Default headers to send. 
	 */
	protected function headers(){
		//iframe hack for file uploads fails with application/json		
		
		if(!\GO\Base\Util\Http::isAjaxRequest(false) || \GO\Base\Util\Http::isMultipartRequest()){
			header('Content-Type: text/html; charset=UTF-8');
		}else
		{
			header('Content-Type: application/json; charset=UTF-8');
		}
		
			
		foreach(\GO::config()->extra_headers as $header){
			header($header);
		}
			
			
	}

	/**
	 * @param $model
	 * @return mixed
	 */
	protected function checkLoadPermissionLevel($model)
	{
		return $model->checkPermissionLevel($model->isNew() ?\GO\Base\Model\Acl::CREATE_PERMISSION : \GO\Base\Model\Acl::WRITE_PERMISSION);
	}
}
