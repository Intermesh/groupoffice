<?php
namespace GO\Base\View;

class JsonView extends AbstractView{
	public function render($viewName, $data) {
		
		$this->headers();
		
		$fn = "render".$viewName;
		return $this->$fn($data);
		
////		$args = func_get_args();
////		array_shift($args);
//		
//		$method = new \ReflectionMethod($this, $fn);
//		
//		$rParams = $method->getParameters();
//		
//		//call method with all parameters from the $_REQUEST object.
//		$methodArgs = array();
//		foreach($rParams as $param){
//			if(!isset($data[$param->getName()]) && !$param->isOptional())
//				throw new \GO\Base\Exception\MissingParameter("Missing argument '".$param->getName()."' for action method '".get_class ($this)."->".$fn."'");
//
//			$methodArgs[]=isset($data[$param->getName()]) ? $data[$param->getName()] : $param->getDefaultValue();
//
//		}
//
//		return call_user_func_array(array($this, $fn),$methodArgs);

	}
	
	
	private function renderJson($data){
		
		echo json_encode($data);
	}
	
	private function renderException($data){
		echo $data['response'];
	}
	
	private function renderDelete($data){

		return new \GO\Base\Data\JsonResponse(array('success'=>!$data['model']->hasValidationErrors(), 'validationErrors'=>$data['model']->getValidationErrors()));
	}
	
	
	private function renderGet($data) {

		$response = array('data' => array(), 'success' => true);

	
		//Init data array
		foreach($data as $modelName=>$model){
			
			// $modelName cannot be the same as the reserved results
			if($modelName == 'data' || $modelName == 'success')
				Throw new \Exception('Cannot use "'.$modelName.'" as key for your data. Please change the key.');
			

			if(is_a($model, "\GO\Base\Model")){
				//TODO: check if this can be moved. This methode renders JSON and should not check permissions.
				if (!$model->checkPermissionLevel($model->isNew ? \GO\Base\Model\Acl::CREATE_PERMISSION : \GO\Base\Model\Acl::WRITE_PERMISSION))
					throw new \GO\Base\Exception\AccessDenied();


				$response['data'][$modelName]['attributes'] = $model->getAttributes(); 
				$response['data'][$modelName]['permission_level'] = $model->getPermissionLevel();

				$r = $model->getRelations();

				foreach($r as $relationName=>$options){
					if(isset($options['labelAttribute'])){
						$label = call_user_func($options['labelAttribute'], $model);
						$field = $options['field'];
						if($options['type'] === \GO\Base\Db\ActiveRecord::MANY_MANY) {
							$field = $options['remoteField'];
						}
						$response['data'][$modelName]['relatedLabels'][$field]=$label;
					}
				}

			} else {
				$response[$modelName] = $model;
			}

			
		}
			
		
//		$this->fireEvent('form', array(
//				&$this,
//				&$response,
//				&$model,
//				&$remoteComboFields
//		));


		return new \GO\Base\Data\JsonResponse($response);
	}
	
	
	/**
	 * Render JSON response for forms
	 * @param \GO\Base\Db\ActiveRecord $model the AWR to renerated the JSON form data for
	 * @param array $remoteComboField List all fields that require a remote text to load for a remote combobox.
	 * eg. with a model you want to provide the category name so that that the
	 * category combo store does not need to be loaded to show it.
	 * 
	 * You would list that like this:
	 * 
	 * 'category_id'=>array('category','name')
	 * 
	 * The category name would be looked up in the model model ->category->name.
	 * A relation for this must be defined. See ActiveRecord->relations.
	 * @see AbstractModelController::remoteComboFields()
	 * @param array $extraFields the extra fields that should be attached to the data array as key => value
	 * @return \GO\Base\Data\JsonResponse Response object
	 * @throws \GO\Base\Exception\AccessDenied
	 */
	private function renderForm($data) {

		$response = array('data' => array(), 'success' => true);

	
		//Init data array
		foreach($data as $modelName=>$model){
			
			// $modelName cannot be the same as the reserved results
			if($modelName == 'data' || $modelName == 'success')
				Throw new \Exception('Cannot use "'.$modelName.'" as key for your data. Please change the key.');
			

			if(is_a($model, "\GO\Base\Model")){
				//TODO: check if this can be moved. This methode renders JSON and should not check permissions.
				if (!$model->checkPermissionLevel($model->isNew ? \GO\Base\Model\Acl::CREATE_PERMISSION : \GO\Base\Model\Acl::WRITE_PERMISSION))
					throw new \GO\Base\Exception\AccessDenied();


				$response['data'][$modelName]['attributes'] = $model->getAttributes(); 
				$response['data'][$modelName]['permission_level'] = $model->getPermissionLevel();

				$r = $model->getRelations();

				foreach($r as $relationName=>$options){
					if(isset($options['labelAttribute'])){
						$label = call_user_func($options['labelAttribute'], $model);
						$field = $options['field'];
						if($options['type'] === \GO\Base\Db\ActiveRecord::MANY_MANY) {
							$field = $options['remoteField'];
						}
						$response['data'][$modelName]['relatedLabels'][$field]=$label;
					}
				}


				//Add the customerfields to the data array
//				if (\GO::user()->getModulePermissionLevel('customfields') && $model->customfieldsRecord){
//					$response['data'][$modelName]['attributes'] = array_merge($response['data'][$modelName]['attributes'], $model->customfieldsRecord->getAttributes());
//					$response['data'][$modelName]['customfields']['categories']=$this->_getCustomFieldDefinitions($model);
//				}
			} else {
				$response[$modelName] = $model;
			}

			
		}
			
		
//		$this->fireEvent('form', array(
//				&$this,
//				&$response,
//				&$model,
//				&$remoteComboFields
//		));


		return new \GO\Base\Data\JsonResponse($response);
	}

	/**
	 * Can be used in actionDisplay like actions
	 * @param \GO\Base\Db\ActiveRecord $data['model'] the model to render display data for
	 * @param array $extraFields the extra fields that should be attached to the data array as key => value
	 * @return \GO\Base\Data\JsonResponse Response object
	 */
	public function renderDisplay($data) {
		$response = array('data' => array(), 'success' => true);
		$response['data'] = $data['model']->getAttributes('html');
		
		if (!empty($data['model']->user))
			$response['data']['username'] = \GO\Base\Util\StringHelper::encodeHtml($data['model']->user->name);
		if (!empty($data['model']->mUser))
			$response['data']['musername'] = \GO\Base\Util\StringHelper::encodeHtml($data['model']->mUser->name);
		
		//$response['data'] = $model->getAttributes('html');
		//$response['data']['model'] = $model->className();
		$response['data']['permission_level'] = $data['model']->getPermissionLevel();
		$response['data']['write_permission'] = \GO\Base\Model\Acl::hasPermission($response['data']['permission_level'], \GO\Base\Model\Acl::WRITE_PERMISSION);


		$response['data']['customfields'] = array();

		if (!isset($response['data']['workflow']) && \GO::modules()->workflow)
			$response = $this->_processWorkflowDisplay($data['model'], $response);

		if ($data['model']->customfieldsRecord)
			$response = $this->_processCustomFieldsDisplay($data['model'], $response);

		if ($data['model']->hasLinks()) {
			$response = $this->_processLinksDisplay($data['model'], $response);

			if (!isset($response['data']['events']) && \GO::modules()->calendar)
				$response = $this->_processEventsDisplay($data['model'], $response);

		}

		if (\GO::modules()->files && !isset($response['data']['files']))
			$response = $this->_processFilesDisplay($data['model'], $response);

		if (\GO::modules()->comments)
			$response = $this->_processCommentsDisplay($data['model'], $response);
		
		if (\GO::modules()->lists)
			$response = \GO\Lists\ListsModule::displayResponse($data['model'], $response);
//
//		$this->fireEvent('display', array(
//				&$this,
//				&$response,
//				&$model
//		));

		return new \GO\Base\Data\JsonResponse($response);
	}

	/**
	 * Render the JSON outbut for a submit action to be used by ExtJS Form submit
	 * @param \GO\Base\Db\ActiveRecord $$data['model']
	 * @return \GO\Base\Data\JsonResponse Response object
	 */
	public function renderSubmit($data) {

		$response = array('feedback' => '', 'success' => true, 'validationErrors'=>array(),'data'=>array());
		
		//Init data array
		foreach($data as $modelName=>$model){
			
			if(!is_object($model) || !method_exists($model, "getAttributes") ){
				$response['data'][$modelName] = $model;
			} else {
				$response['data'][$modelName] = $model->getAttributes();
			}

			// $modelName cannot be the same as the reserved results
			if($modelName == 'feedback' || $modelName == 'success' ||  $modelName == 'validationErrors')
				Throw new \Exception('Cannot use "'.$modelName.'" as key for your data. Please change the key.');

			if(is_a($model, "\GO\Base\Model")){
				//$ret = $this->beforeSubmit($response, $model, $params);
				//$modifiedAttributes = $model->getModifiedAttributes();
				if (!$model->hasValidationErrors() && !$model->isNew) { //model was saved
					$response['id'] = $model->pk;

					//If the model has it's own ACL id then we return the newly created ACL id.
					//The model automatically creates it.
					if ($model->aclField() && !$model->isJoinedAclField)
						$response[$model->aclField()] = $model->{$model->aclField()};

					//TODO: move the link saving to the model someday
					if (!empty(\GO::request()->post['link']) && $model->hasLinks()) {
						//a link is sent like  \GO\Notes\Model\Note:1
						//where 1 is the id of the model
						$linkProps = explode(':', \GO::request()->post['link']);
						$linkModel = \GO::getModel($linkProps[0])->findByPk($linkProps[1]);
						$model->link($linkModel);
					}

				} else { // model was not saved
					$response['success'] = false;
					//can't use <br /> tags in response because this goes wrong with the extjs fileupload hack with an iframe.
					$response['feedback'] = sprintf(\GO::t("Couldn't save %s:"), strtolower($model->localizedName)) . "\n\n" . implode("\n", $model->getValidationErrors()) . "\n";
					if (\GO\Base\Util\Http::isAjaxRequest(false)) {
						$response['feedback'] = nl2br($response['feedback']);
					}
					
					
					$response['errors']=array(
							sprintf(\GO::t("Couldn't save %s:"), strtolower($model->localizedName)) . "\n\n" . implode("\n", $model->getValidationErrors()) . "\n"
					);
					
					
					$response['validationErrors'][$modelName] = $model->getValidationErrors();
				}
			} else {
				$response[$modelName] = $model;
			}
		}
		
		return new \GO\Base\Data\JsonResponse($response);
	}

	/**
	 * Renders DbStore object to a valid JSON response
	 * @param \GO\Base\Date\JsonStore $data['store'] I JsonStore object to get JSON from
	 * @deprecated boolean $return still here for buttonParams (should button params be set in DbStore
	 * @param mixed $buttonParams ???
	 * @return \GO\Base\Data\JsonResponse Response object
	 */
	private function renderStore($data){//\GO\Base\Data\AbstractStore $store, $return = false, $buttonParams=false) {
		
		if(!isset($data['store']))
			Throw new \Exception('The "store" parameter is required.');

		foreach($data as $key=>$value){
			
			// $modelName cannot be the same as the reserved results
			if($key == 'summary' || $key == 'title' ||  $key == 'results')
				Throw new \Exception('Cannot use "'.$key.'" as key for your data. Please change the key.');
			
			if($key === 'store'){
				$response=$data['store']->getData();
				if($summary = $data['store']->getSummary())
					$response['summary'] = $summary;

				$title = $data['store']->getTitle();
				if (!empty($title))
					$response['title'] = $title;
				
			} elseif(is_a($key, "\GO\Base\Model")){
				
				// Threath as a model
				$response[$key] = $value->getAttributes();
				
			} else {
				$response[$key] = $value;
			}
			
		}

		return new \GO\Base\Data\JsonResponse($response);
	}
	
	
	

	/**
	 * 
	 * Below follow all process display functions
	 * 
	 */
	private function _processWorkflowDisplay($model, $response) {

		$response['data']['workflow'] = array();

		$workflowModelstmnt = \GO\Workflow\Model\Model::model()->findByAttributes(array("model_id" => $model->id, "model_type_id" => $model->modelTypeId()));

		while ($workflowModel = $workflowModelstmnt->fetch()) {

			$currentStep = $workflowModel->step;

			$workflowResponse = $workflowModel->getAttributes('html');

//			$workflowResponse['id'] = $workflowModel->id;
			$workflowResponse['process_name'] = $workflowModel->process->name;
//			$workflowResponse['due_time'] = $workflowModel->due_time;
//			$workflowResponse['shift_due_time'] = $workflowModel->shift_due_time;			

			$workflowResponse['user'] = !empty($workflowModel->user_id) ? $workflowModel->user->name : '';

			$workflowResponse['approvers'] = array();
			$workflowResponse['approver_groups'] = array();
			$workflowResponse['step_id'] = $workflowModel->step_id;

			if ($workflowModel->step_id == '-1') {
				$workflowResponse['step_progress'] = '';
				$workflowResponse['step_name'] = \GO::t("Complete", "workflow");
				$workflowResponse['is_approver'] = false;
				$workflowResponse['step_all_must_approve'] = false;
			} else {
				$workflowResponse['step_progress'] = $workflowModel->getStepProgress();
				$workflowResponse['step_name'] = $currentStep->name;
				$workflowResponse['step_all_must_approve'] = $currentStep->all_must_approve;

				$is_approver = \GO\Workflow\Model\RequiredApprover::model()->findByPk(array("user_id" => \GO::user()->id, "process_model_id" => $workflowModel->id, "approved" => false));

				if ($is_approver)
					$workflowResponse['is_approver'] = true;
				else
					$workflowResponse['is_approver'] = false;

				// Add the approvers of the current step to the response
				$approversStmnt = $workflowModel->requiredApprovers;

				while ($approver = $approversStmnt->fetch()) {
					$approver_hasapproved = $currentStep->hasApproved($workflowModel->id, $approver->id);
					$workflowResponse['approvers'][] = array('name' => $approver->name, 'approved' => $approver_hasapproved, 'last' => '0');
				}
				// Set the last flag for the latest approver in the list
				$i = count($workflowResponse['approvers']) - 1;

				if ($i >= 0)
					$workflowResponse['approvers'][$i]['last'] = "1";

				// Add the approver groups of the current step to the response
				$approverGroupsStmnt = $currentStep->approverGroups;
				while ($approverGroup = $approverGroupsStmnt->fetch()) {
					$workflowResponse['approver_groups'][] = array('name' => $approverGroup->name);
				}
			}

			$workflowResponse['history'] = array();
			$historiesStmnt = \GO\Workflow\Model\StepHistory::model()->findByAttribute('process_model_id', $workflowModel->id, \GO\Base\Db\FindParams::newInstance()->select('t.*')->order('ctime', 'DESC'));
			while ($history = $historiesStmnt->fetch()) {
				\GO\Base\Db\ActiveRecord::$attributeOutputMode = 'html';


				if ($history->step_id == '-1')
					$step_name = \GO::t("Complete", "workflow");
				else
					$step_name = $history->step->name;

				$workflowResponse['history'][] = array(
						'history_id' => $history->id,
						'step_name' => $step_name,
						'approver' => $history->user->name,
						'ctime' => $history->ctime,
						'comment' => $history->comment,
						'status' => $history->status ? "1" : "0",
						'status_name' => $history->status ? \GO::t("Approved", "workflow") : \GO::t("Declined", "workflow")
				);

				\GO\Base\Db\ActiveRecord::$attributeOutputMode = 'raw';
			}

			$response['data']['workflow'][] = $workflowResponse;
		}

		return $response;
	}

	private function _processFilesDisplay($model, $response) {
		if (isset(\GO::modules()->files) && $model->hasFiles() && $response['data']['files_folder_id'] > 0) {

			$fc = new \GO\Files\Controller\FolderController();
			$listResponse = $fc->run("list", array('skip_fs_sync'=>true, 'folder_id' => $response['data']['files_folder_id'], "limit" => 20, "sort" => 'mtime', "dir" => 'DESC'), false);
			$response['data']['files'] = $listResponse['results'];
		} else {
			$response['data']['files'] = array();
		}
		return $response;
	}

	private function _processLinksDisplay($model, $response) {
		$findParams = \GO\Base\Db\FindParams::newInstance()
						->limit(15);

		$ignoreModelTypes = array();
		if (\GO::modules()->calendar)
			$ignoreModelTypes[] = \GO\Calendar\Model\Event::model()->modelTypeId();

		$findParams->getCriteria()->addInCondition('model_type_id', $ignoreModelTypes, 't', true, true);

		$stmt = \GO\Base\Model\SearchCacheRecord::model()->findLinks($model, $findParams);

		$store = \GO\Base\Data\Store::newInstance(\GO\Base\Model\SearchCacheRecord::model());
		$store->setStatement($stmt);

		$columnModel = $store->getColumnModel();
		$columnModel->formatColumn('link_count', '\GO::getModel($model->model_name)->countLinks($model->model_id)');
		$columnModel->formatColumn('link_description', '$model->link_description');

		$data = $store->getData();
		$response['data']['links'] = $data['results'];

		return $response;
	}

	private function _processEventsDisplay($model, $response) {
		$startOfDay = \GO\Base\Util\Date::clear_time(time());

		$findParams = \GO\Base\Db\FindParams::newInstance()->order('start_time', 'DESC');
		$findParams->getCriteria()->addCondition('start_time', $startOfDay, '>=');

		$stmt = \GO\Calendar\Model\Event::model()->findLinks($model, $findParams);

		$store = \GO\Base\Data\Store::newInstance(\GO\Calendar\Model\Event::model());
		$store->setStatement($stmt);

		$columnModel = $store->getColumnModel();
		$columnModel->formatColumn('calendar_name', '$model->calendar->name');
		$columnModel->formatColumn('link_count', '$model->countLinks()');
		$columnModel->formatColumn('link_description', '$model->link_description');

		$data = $store->getData();
		$response['data']['events'] = $data['results'];

		return $response;
	}

	private function _processCommentsDisplay($model, $response) {
		$stmt = \GO\Comments\Model\Comment::model()->find(\GO\Base\Db\FindParams::newInstance()
										->limit(5)
										->select('t.*,cat.name AS categoryName')
										->order('id', 'DESC')
										->joinModel(array(
												'model' => 'GO\Comments\Model\Category',
												'localTableAlias' => 't',
												'localField' => 'category_id',
												'foreignField' => 'id',
												'tableAlias' => 'cat',
												'type' => 'LEFT'
										))
										->criteria(\GO\Base\Db\FindCriteria::newInstance()
														->addModel(\GO\Comments\Model\Comment::model())
														->addCondition('model_id', $model->id)
														->addCondition('model_type_id', $model->modelTypeId())
										));

		$store = \GO\Base\Data\Store::newInstance(\GO\Comments\Model\Comment::model());
		$store->setStatement($stmt);

		$columnModel = $store->getColumnModel();
		$columnModel->formatColumn('user_name', '$model->user->name');

		$data = $store->getData();
		foreach ($data['results'] as $k => $v) {
			$data['results'][$k]['categoryName'] = !empty($v['categoryName']) ? $v['categoryName'] : \GO::t("No category", "comments");
		}
		$response['data']['comments'] = $data['results'];

		return $response;
	}


}
