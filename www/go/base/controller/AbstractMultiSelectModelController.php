<?php
/*
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 */

/**
 * Abstract class for Group-Office Models that needed to be multiselected. The
 * simplest problem you can solve with controllers of this type are when two
 * model types have a MANY-MANY relation, and the goal is to select all records
 * of model modelName that are related to a single model that can be identified
 * using linkModelField.
 * 
 * The return values of both linkModelField() and getRemoteKey() MUST together
 * identify relations between the two models. The return value of
 * linkModelName() MUST be the name of the model that contains these relations.
 * 
 * Any function that starts with action will be publicly accessible by:
 * 
 * index.php?r=module/controllername/functionNameWithoutAction&security_token=1233456
 * 
 * This function will be called with one parameter which holds all request
 * variables.
 * 
 * A security token must be supplied in each request to prevent cross site 
 * request forgeries.
 * 
 * The functions must return a response object. In case of ajax controllers this
 * should be a an array that will be converted to Json or XMl by an Exporter.
 * 
 * 
 * @package GO.base.controller
 * @version $Id: AbstractMultiSelectModelController.php 7607 2011-06-15 09:17:42Z wsmits $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits <wsmits@intermesh.nl> 
 * @author WilmarVB <wilmar@intermesh.nl>
 * @author Merijn Schering <mschering@intermesh.nl>
 * @abstract
 */

namespace GO\Base\Controller;


abstract class AbstractMultiSelectModelController extends AbstractController{
	
	
	protected $uniqueSelection=true;
		
	/**
	 * The name of the model we are showing and adding to the other model.
	 * 
	 * eg. When selecting calendars for a user in the sync settings this is set to \GO\Calendar\Model\Calendar
	 */
	abstract public function modelName();
	
	/**
	 * The link model that handles the MANY_MANY relation.
	 * 
	 * eg. \GO\Sync\Model\UserCalendars It's the link table between users and calendars.
	 */
	abstract public function linkModelName();
	
	/**
	 * The key (from the combined key) of the linkmodel that identifies the model as defined in self::modelName().
	 */
	abstract public function linkModelField();
	
	/**
	 * Return all new items for a grid. 
	 * So this are the items that are not already selected.
	 * 
	 * Parameters:
	 *	model_id =	The value of one of the keys from the combined primary key of the linkModel that is not given in the linkModelField;
	 *			Example:	The combined key of the linkModel is: [user_id,tasklist_id].
	 *								The given linkModelField is: [tasklist_id].
	 *								Then the model_id needs to be the other value of the combined key so in this example: The value for [user_id]
	 *							
	 * 
	 * @param Array $params
	 * @return type 
	 */
	protected function actionSelectNewStore($params){
		
		$model = \GO::getModel($this->modelName());
		$linkModel = \GO::getModel($this->linkModelName());
		
		$store = \GO\Base\Data\Store::newInstance($model);
		
		$joinCriteria = \GO\Base\Db\FindCriteria::newInstance()
			->addCondition($this->getRemoteKey(), $params['model_id'],'=','lt')
			->addCondition($model->primaryKey(), 'lt.'.$this->linkModelField(), '=', 't', true, true);			
		
		$this->formatColumns($store->getColumnModel());
		
		$findParams = $store->getDefaultParams($params);
		
		if($this->uniqueSelection){
			$findParams->join($linkModel->tableName(), $joinCriteria, 'lt', 'LEFT');

			$findCriteria = \GO\Base\Db\FindCriteria::newInstance()
							->addCondition($this->linkModelField(), null,'IS','lt');
			$findParams->criteria($findCriteria);
		}
		
		
		$availableModels = $model->find($findParams);
		
		$store->setStatement($availableModels);

		return $store->getData();
	}
	
	/**
	 * Override this to make changes in the columnModel of this controller's
	 * selectNewStore and selectedStore.
	 * @param \GO\Base\Data\ColumnModel $cm 
	 */
	protected function formatColumns(\GO\Base\Data\ColumnModel $cm){
		
	}
	
	/**
	 * This MUST be overridden if you extend this class to handle models with
	 * more than three primary keys.
	 * @param array $params
	 * @return array With keys being PK names and values being PK values of the
	 * model to be deleted. 
	 */
	protected function getExtraDeletePks($params){
		return array($this->getRemoteKey()=>$params['model_id']);
	}
	
	/**
	 * Return the selected items for a grid.
	 * 
	 * Parameters:
	 *	model_id =	The value of one of the keys from the combined primary key of the linkModel that is not given in the linkModelField;
	 *			Example:	The combined key of the linkModel is: [user_id,tasklist_id].
	 *								The given linkModelField is: [tasklist_id].
	 *								Then the model_id needs to be the other value of the combined key so in this example: The value for [user_id]
	 *							
	 * 
	 * @param Array $params
	 * @return type 
	 */
	protected function actionSelectedStore($params){
		
		$response = array();
		
		if(!empty($params['add'])) {
			if($this->beforeAdd($params)){
				$ids = json_decode($params['add'],true);

				$linkmodelField = $this->linkModelField();
				$remoteKey = $this->getRemoteKey();
				$linkModelName = $this->linkModelName();

				foreach($ids as $id){
					$linkModel = new $linkModelName();
					
					if(isset($params['addAttributes']) && ($attr = json_decode($params['addAttributes'], true)))
						$linkModel->setAttributes($attr);					
					
					$linkModel->$linkmodelField = $id;
					$linkModel->$remoteKey = $params['model_id'];					
					$linkModel->save();
				}
			}
		}
		
		$model = \GO::getModel($this->modelName());
		$linkModel = \GO::getModel($this->linkModelName());
		
		$store = \GO\Base\Data\Store::newInstance($model);
		$this->formatColumns($store->getColumnModel());
		
		if($model->aclField())
			$store->getColumnModel()->formatColumn('permission_level', '$model->permissionLevel');
		
		try {
			if($this->beforeDelete($params)){
				$store->processDeleteActions(
					$params,
					$this->linkModelName(),
					$this->getExtraDeletePks($params)
					);
			} else {
				$response['deleteSuccess'] = true;
			}
		} catch (\Exception $e) {
			$response['deleteSuccess'] = false;
			$response['deleteFeedback'] = $e->getMessage();
		}
		
		$joinCriteria = \GO\Base\Db\FindCriteria::newInstance()
			->addCondition(
							$model->primaryKey(),
							'lt.'.$this->linkModelField(),
							'=',
							't',
							true,
							true)
			->addCondition($this->getRemoteKey(),$params['model_id'],'=','lt');			
		
		$findParams = $store->getDefaultParams($params)
						->ignoreAcl()
						->select('t.*,lt.*');
		
		$findParams->join($linkModel->tableName(), $joinCriteria, 'lt', 'INNER');

		$selectedModels = $model->find($findParams);
		
		$store->setStatement($selectedModels);
		
		

		$response = array_merge($response,$store->getData());
		
		return $response;
	}
	
	/**
	 * Called by actionSelectedStore. It can be simultaneously overridden and
	 * called upon to handle cases where linking linkedModels to the current model
	 * is not allowed.
	 * @param array $params The client parameters. When overriding, you may find
	 * $params['model_id'] and $params['add'] to be interesting. The latter is a
	 * list of ids of linkedModels to be linked.
	 * @return boolean Iff true is returned, linking linkedModels will be
	 * initiated.
	 */
	protected function beforeAdd(array $params) {
		return true;
	}
	
	/**
	 * Called by actionSelectedStore. It can be simultaneously overridden and
	 * called upon to handle cases where unlinking linkedModels to the current
	 * model is not allowed.
	 * @param array $params The client parameters. When overriding, you may find
	 * $params['model_id'] and $params['delete_keys'] to be interesting. The
	 * latter is a list of ids of linkedModels to be unlinked.
	 * @return boolean Iff true is returned, unlinking linkedModels will be
	 * initiated.
	 */
	protected function beforeDelete(array $params) {
		return true;
	}
	
	/**
	 * This function is called in actionUpdateRecord, at the brink of initiating
	 * the record updating process. You can judge whether or not $record should be
	 * updated in this function. A nice feature is that you can force the
	 * record fields to be updated to values of your own choosing.
	 * @param array $params Client parameters.
	 * @param array &$record The record to be judged on 
	 * @return boolean Iff true, the initiating record updating process's brink
	 * will be crossed. I.e., the record updating process will be initiated.
	 */
	protected function beforeUpdateRecord($params,&$record, $linkModel) {
		return true;
	}
	
	/**
	 * Find the remote key in the combined key of the linkModel.
	 * Remote key is for example the user_id when editing settings of a user with a link model with primary keys: user_id and calendar_id
	 * 
	 * @return String The remote key 
	 */
	protected function getRemoteKey(){
		$linkModel = \GO::getModel($this->linkModelName());
		$key = $linkModel->primaryKey();
		
		return $key[0]==$this->linkModelField() ? $key[1] : $key[0];
	}	
	
	public function actionUpdateRecord($params) {
		$response = array('success'=>true);
		$record = json_decode($params['record'], true);
		
		
		$primaryKeys = array(
			$this->getRemoteKey()=>$params['model_id'], //eg. user_id
			$this->linkModelField()=>$record['id'] //eg. calendar_id
		);
		$linkModel = \GO::getModel($this->linkModelName())->findByPk($primaryKeys);
		
		
		if ($this->beforeUpdateRecord($params,$record, $linkModel)) {
			
			$linkModel->setAttributes($record);
			$linkModel->save();
		}
		return $response;
	}

}
