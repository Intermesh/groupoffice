<?php
/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @author Wilmar van Beusekom <wilmar@intermesh.nl>
 */

namespace GO\Email\Controller;


class TemplateController extends \GO\Base\Controller\AbstractModelController{
	
	protected $model = 'GO\Base\Model\Template';
	
	protected function remoteComboFields() {
		return array(
				'user_name'=>'$model->user->name'
				);
	}
	
	protected function getStoreParams($params) {
		$params['type'] = 0;
		if(isset($params['type'])){
			$findParams = \GO\Base\Db\FindParams::newInstance();
			
			$findParams->getCriteria()->addCondition('type', $params['type']);
			return $findParams;
		}
		
		//return parent::getStoreParams($params);
	}
	
	protected function beforeStore(&$response, &$params, &$store) {
		$store->setDefaultSortOrder('name');
		return parent::beforeStore($response, $params, $store);
	}

	protected function beforeSubmit(&$response, &$model, &$params) {
		
		$message = new \GO\Base\Mail\Message();
		$message->handleEmailFormInput($params);
		
		$model->content = $message->toString();
		
		return parent::beforeSubmit($response, $model, $params);
	}
	
	protected function afterSubmit(&$response, &$model, &$params, $modifiedAttributes) {
		$message = \GO\Email\Model\SavedMessage::model()->createFromMimeData($model->content, false);
		$response['htmlbody'] = $message->getHtmlBody();
		
		// reset the temp folder created by the core controller
//		$tmpFolder = new \GO\Base\Fs\Folder(\GO::config()->tmpdir . 'uploadqueue');
//		$tmpFolder->delete();
		
		parent::afterSubmit($response, $model, $params, $modifiedAttributes);
	}
	
	protected function afterLoad(&$response, &$model, &$params) {
		
		// create message model from client's content field, turned into HTML format
		$message = \GO\Email\Model\SavedMessage::model()->createFromMimeData($model->content, false);
	
		$html = empty($params['content_type']) || $params['content_type']=='html';
		
		$response['data'] = array_merge($response['data'], $message->toOutputArray($html));
		unset($response['data']['content']);

		return parent::afterLoad($response, $model, $params);
	}
	
	protected function formatColumns(\GO\Base\Data\ColumnModel $columnModel) {
		$columnModel->formatColumn('user_name', '$model->user->name');
		return parent::formatColumns($columnModel);
	}

	
	public function actionEmailSelection($params){	
				
		// 'type' is only set by the client if a template should be selected as default.
		// The user can choose to set the default template for an email account or
		// for himself (current user).
		if (!empty($params['account_id'])) {
			$defTempForAccount = \GO\Email\Model\DefaultTemplateForAccount::model()->findByPk($params['account_id']);
			if(!$defTempForAccount){
				$defTempForAccount= new \GO\Email\Model\DefaultTemplateForAccount();
				$defTempForAccount->account_id = $params['account_id'];
				$defTempForAccount->save();
			}
		}


		$defTempForUser = \GO\Email\Model\DefaultTemplate::model()->findByPk(\GO::user()->id);
		if(!$defTempForUser){
			$defTempForUser= new \GO\Email\Model\DefaultTemplate();
			$defTempForUser->user_id = \GO::user()->id;
			$defTempForUser->save();
		}

		
		$this->_defaultTemplate = !empty($params['account_id']) && $defTempForAccount->template_id ? $defTempForAccount : $defTempForUser;
		
		if(isset($params['default_template_id']))
		{
			if ((!empty($params['type']) && $params['type']=='default_for_account') && (!empty($params['account_id']))) {
				$defTempForAccount->template_id=$params['default_template_id'];
				$defTempForAccount->save();
			} else{
				$defTempForUser->template_id=$params['default_template_id'];
				$defTempForUser->save();
			}
		}
		
		$findParams = \GO\Base\Db\FindParams::newInstance()->order('name');			
		$findParams->getCriteria()->addCondition('type', \GO\Base\Model\Template::TYPE_EMAIL);
		
		
		$store = new \GO\Base\Data\DbStore('GO\Base\Model\Template',new \GO\Base\Data\ColumnModel('GO\Base\Model\Template'),$params,$findParams);
		
		$store->getColumnModel()->setFormatRecordFunction(array($this, 'formatEmailSelectionRecord'));
		
		$store->addRecord(array(
			'group' => 'templates',
			'checked'=>isset($this->_defaultTemplate->template_id) && $this->_defaultTemplate->template_id==0,
			'text' => \GO::t("None"),
			'template_id'=>0
		));
		
		$response = $store->getData();
		
		
		return $response;
	}
	
	protected function actionDefaultTemplateId($params) {
		
		$templateModel = \GO\Email\Model\DefaultTemplateForAccount::model()->findByPk($params['account_id']);
		if (!$templateModel)
			$templateModel = \GO\Email\Model\DefaultTemplate::model()->findByPk(\GO::user()->id);
		
		if (!$templateModel)
			return array('success'=>true,'data'=>array('template_id'=>0));
		else
			return array('success'=>true,'data'=>array('template_id'=>$templateModel->template_id));
		
	}
	
	public function formatEmailSelectionRecord(array $formattedRecord, \GO\Base\Db\ActiveRecord $model, \GO\Base\Data\ColumnModel $cm){
		if(!isset($this->_defaultTemplate->template_id)){
			$this->_defaultTemplate->template_id=$model->id;
			$this->_defaultTemplate->save();
		}
		$formattedRecord['group'] = 'templates';
		$formattedRecord['checked']=$this->_defaultTemplate->template_id==$model->id;
		$formattedRecord['text']=  \GO\Base\Util\StringHelper::encodeHtml($model->name);
		$formattedRecord['template_id']=$model->id;
		unset($formattedRecord['id']);
		return $formattedRecord;
	}

	public function actionAccountTemplatesStore($params){	
		
		$findParams = \GO\Base\Db\FindParams::newInstance()->order('name');			
		$findParams->getCriteria()->addCondition('type', \GO\Base\Model\Template::TYPE_EMAIL);
				
		$stmt = \GO\Base\Model\Template::model()->find($findParams);
		
		$store = \GO\Base\Data\Store::newInstance(\GO\Base\Model\Template::model());		
//		$store->getColumnModel()->setFormatRecordFunction(array($this, 'formatEmailSelectionRecord'));
		
		$store->setStatement($stmt);
		
		$response = $store->getData();
			
		$response['total']++;
		$response['results'][] = array('id'=>0,'name'=>'-- '.\GO::t("User default template", "addressbook").' --','group'=>'','text'=>'','template_id'=>'','checked'=>false);
		return $response;
	}
	
}


