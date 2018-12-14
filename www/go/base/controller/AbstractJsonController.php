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
 * This controller can be extended for most of the groupoffice controllers
 * It has support for rendering JSON data from a loaded model or render failure output
 * 
 * @package GO.base.controller
 * @version $Id$
 * @copyright Copyright Intermesh BV.
 * @author Michael de Hart <mdhart@intermesh.nl>
 *
 */

namespace GO\Base\Controller;


abstract class AbstractJsonController extends AbstractController {

	/**
	 * @deprecated
	 * Get a Json object from the response data
	 * @param array $data
	 * @return \GO\Base\Data\JsonResponse response object
	 */
	public function renderJson($data) {
		return new \GO\Base\Data\JsonResponse($data);
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
	 * 'category_id'=>'$model->category->name'
	 * 
	 * The category name would be looked up in the model model ->category->name.
	 * A relation for this must be defined. See ActiveRecord->relations.
	 * @see AbstractModelController::remoteComboFields()
	 * @param array $extraFields the extra fields that should be attached to the data array as key => value
	 * @return \GO\Base\Data\JsonResponse Response object
	 * @throws \GO\Base\Exception\AccessDenied
	 */
	public function renderForm($model, $remoteComboFields = array(), $extraFields = array()) {

		$response = array('data' => array(), 'success' => true);

		//TODO: check if this can be moved. This methode renders JSON and should not check permissions.
		if (!$model->checkPermissionLevel($model->isNew ? \GO\Base\Model\Acl::CREATE_PERMISSION : \GO\Base\Model\Acl::WRITE_PERMISSION))
			throw new \GO\Base\Exception\AccessDenied();

		//Init data array
		$response['data'] = array_merge($model->getAttributes(), $extraFields);
		$response['data']['permission_level'] = $model->getPermissionLevel();
		$response['data']['write_permission'] = true;

		if($model->hasCustomfields())
		{
			$response['data']['customFields'] = $model->getCustomFields();
		}

		if (!empty($remoteComboFields))
			$response = $this->_loadComboTexts($model, $remoteComboFields, $response);
		
		$this->fireEvent('form', array(
				&$this,
				&$response,
				&$model,
				&$remoteComboFields
		));

		return new \GO\Base\Data\JsonResponse($response);
	}

	/**
	 * Can be used in actionDisplay like actions
	 * @param \GO\Base\Db\ActiveRecord $model the model to render display data for
	 * @param array $extraFields the extra fields that should be attached to the data array as key => value
	 * @return \GO\Base\Data\JsonResponse Response object
	 */
	public function renderDisplay($model, $extraFields = array()) {
		$response = array('data' => array(), 'success' => true);
		$response['data'] = array_merge_recursive($extraFields, $model->getAttributes('html'));
		
		if (!empty($model->user))
			$response['data']['username'] = $model->user->name;
		if (!empty($model->mUser))
			$response['data']['musername'] = $model->mUser->name;
		
		//$response['data'] = $model->getAttributes('html');
		//$response['data']['model'] = $model->className();
		$response['data']['permission_level'] = $model->getPermissionLevel();
		$response['data']['write_permission'] = \GO\Base\Model\Acl::hasPermission($response['data']['permission_level'], \GO\Base\Model\Acl::WRITE_PERMISSION);


		if($model->hasCustomfields())
		{
			$response['data']['customFields'] = $model->getCustomFields();
		}

		if (!isset($response['data']['workflow']) && \GO::modules()->workflow)
			$response = $this->_processWorkflowDisplay($model, $response);
		
		if (\GO::modules()->lists)
			$response = \GO\Lists\ListsModule::displayResponse($model, $response);

		$this->fireEvent('display', array(
				&$this,
				&$response,
				&$model
		));

		return new \GO\Base\Data\JsonResponse($response);
	}

	/**
	 * Render the JSON outbut for a submit action to be used by ExtJS Form submit
	 * @param \GO\Base\Db\ActiveRecord $model
	 * @return \GO\Base\Data\JsonResponse Response object
	 */
	public function renderSubmit($model) {

		$response = array('success' => true);
		//$ret = $this->beforeSubmit($response, $model, $params);
		//$modifiedAttributes = $model->getModifiedAttributes();
		if (!$model->hasValidationErrors() && !$model->isNew) { //model was saved
			$response['id'] = $model->pk;

			//If the model has it's own ACL id then we return the newly created ACL id.
			//The model automatically creates it.
			if ($model->aclField() && !$model->isJoinedAclField)
				$response[$model->aclField()] = $model->{$model->aclField()};
			if($model->aclOverwrite())
				$response[$model->aclOverwrite()] = $model->{$model->aclOverwrite()};

			//TODO: move the link saving to the model someday
			if (!empty($_POST['link']) && $model->hasLinks()) {
				//a link is sent like  \GO\Notes\Model\Note:1
				//where 1 is the id of the model
				$linkProps = explode(':', $_POST['link']);
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
			$response['validationErrors'] = array($model->getModelName()=>$model->getValidationErrors());
		}
		
		$this->fireEvent('submit', array(
					&$this,
					&$response,
					&$model
			));

		return new \GO\Base\Data\JsonResponse($response);
	}

	/**
	 * Renders DbStore object to a valid JSON response
	 * @param \GO\Base\Date\JsonStore $store I JsonStore object to get JSON from
	 * @deprecated boolean $return still here for buttonParams (should button params be set in DbStore
	 * @param mixed $buttonParams ???
	 * @return \GO\Base\Data\JsonResponse Response object
	 */
	public function renderStore(\GO\Base\Data\AbstractStore $store, $return = false, $buttonParams=false) {

//		$response = array(
//				"success" => true,
//				"results" => $store->getRecords(),
//				'total' => $store->getTotal()
//		);
//		if($summary = $store->getSummary())
//			$response['summary'] = $summary;
		
		$this->fireEvent('renderStore', [$store]);
		
		$response=$store->getData();
		if($summary = $store->getSummary())
			$response['summary'] = $summary;

		$title = $store->getTitle();
		if (!empty($title))
			$response['title'] = $title;

//		if ($store instanceof \GO\Base\Data\DbStore) {
//			if ($store->getDeleteSuccess() !== null) {
//				$response['deleteSuccess'] = $store->getDeleteSuccess();
//				if(!$response['deleteSuccess'])
//					$response['deleteFeedback'] = $store->getFeedBack();
//			}
//			if($buttonParams){
//				$buttonParams = $store->getButtonParams();
//				if (!empty($buttonParams))
//					$response['buttonParams'] = $buttonParams;
//			}
//		}
		
		

		return new \GO\Base\Data\JsonResponse($response);
	}
	
	/**
	 * 
	 * @param \GO\Base\Data\AbstractStore $store
	 * @param type $params
	 */
	protected function renderExport(\GO\Base\Data\AbstractStore $store, $params) {
		//define('EXPORTING', true);
		//used by custom fields to format diffently

		$checkboxSettings = array(
			'export_include_headers'=>!empty($params['includeHeaders']),
			'export_human_headers'=>empty($params['humanHeaders']),
			'export_include_hidden'=>!empty($params['includeHidden'])
		);
		
		$settings =  \GO\Base\Export\Settings::load();
		$settings->saveFromArray($checkboxSettings);
		
		if(!empty($params['exportOrientation']) && ($params['exportOrientation']=="H"))
			$orientation = 'L'; // Set the orientation to Landscape
		else
			$orientation = 'P'; // Set the orientation to Portrait
		
		
		if(!empty($params['columns'])) {
			$columnModel = $store->getColumnModel();
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
		}
		
		if(!empty($params['type'])){
			//temporary fix for compatibility with AbsractModelController
			$params['type']=str_replace('GO\Base\Export', 'GO\Base\Storeexport', $params['type']);
			$export = new $params['type']($store, $settings->export_include_headers, $settings->export_human_headers, $params['documentTitle'], $orientation);
		}else
			$export = new \GO\Base\Storeexport\ExportCSV($store, $settings->export_include_headers, $settings->export_human_headers, $params['documentTitle'], $orientation); // The default Export is the CSV outputter.

		if(isset($params['extraLines']))
			$export->addLines($params['extraLines']);
		
		$export->output();
	}

	public function run($action = '', $params = array(), $render = true, $checkPermissions = true) {
		if (empty($action))
			$action = $this->defaultAction;

//		$this->fireEvent($action, array(
//			&$this,
//			&$params
//		));

		$response = parent::run($action, $params, $render, $checkPermissions);

		if (isset($params['firstRun']) && is_array($params['firstRun'])) {
			$response = array_merge($response, $params['firstRun']);
		}

		return $response;
	}

	/**
	 * Adds remoteComboTexts array to response
	 * Will be called in renderLoad()
	 * @param array $response the response data
	 * @return StringHelper modified response data
	 * @throws Exception if no valid key defined
	 */
	private function _loadComboTexts($model, $combofields, $response) {

		$response['remoteComboTexts'] = array();

		foreach ($combofields as $property => $map) {
			if (is_numeric($property))
				throw new \Exception("remoteComboFields() must return a key=>value array.");

			try {
				eval('$value = ' . $map . ';');
			}catch (\Exception $e) {
				$value = '';
			}

			$response['remoteComboTexts'][$property] = $value;

			//hack for comboboxes displaying 0 instead of the emptyText in extjs
			if (isset($response['data'][$property]) && $response['data'][$property] === 0) {
				$response['data'][$property] = "";
			}
		}

		return $response;
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



}
