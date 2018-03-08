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
 * @author WilmarVB <wilmar@intermesh.nl>
 */

namespace GO\Servermanager\Controller;


class AutomaticEmailController extends \GO\Base\Controller\AbstractModelController{
	
	protected $model = 'GO\Servermanager\Model\AutomaticEmail';
		
	protected function beforeStore(&$response, &$params, &$store) {
		$store->setDefaultSortOrder('name');
		return parent::beforeStore($response, $params, $store);
	}

	protected function beforeSubmit(&$response, &$model, &$params) {
		$message = new \GO\Base\Mail\Message();
		$message->handleEmailFormInput($params);
		$model->mime = $message->toString();
		return parent::beforeSubmit($response, $model, $params);
	}
	
	protected function afterSubmit(&$response, &$model, &$params, $modifiedAttributes) {
		$message = \GO\Email\Model\SavedMessage::model()->createFromMimeData($model->mime);
		$response['htmlbody'] = $message->getHtmlBody();
		
		// reset the temp folder created by the core controller
//		$tmpFolder = new \GO\Base\Fs\Folder(\GO::config()->tmpdir . 'uploadqueue');
//		$tmpFolder->delete();
		
		parent::afterSubmit($response, $model, $params, $modifiedAttributes);
	}
	
	protected function beforeLoad(&$response, &$model, &$params) {
		
		return parent::beforeLoad($response, $model, $params);
	}
	
	protected function afterLoad(&$response, &$model, &$params) {
		
		// create message model from client's content field, turned into HTML format
		$message = \GO\Email\Model\SavedMessage::model()->createFromMimeData($model->mime);
	
		$html = empty($params['content_type']) || $params['content_type']=='html';
		
		$response['data'] = array_merge($response['data'], $message->toOutputArray($html));
//		unset($response['data']['content']);

		return parent::afterLoad($response, $model, $params);
	}
	
	protected function formatColumns(\GO\Base\Data\ColumnModel $columnModel) {
		$columnModel->formatColumn('user_name', '$model->user->name');
		return parent::formatColumns($columnModel);
	}
	
	private $_defaultTemplate;
	
	public function actionEmailSelection($params){	
		
		$this->_defaultTemplate = \GO\Addressbook\Model\DefaultTemplate::model()->findByPk(\GO::user()->id);
		if(!$this->_defaultTemplate){
			$this->_defaultTemplate= new \GO\Addressbook\Model\DefaultTemplate();
			$this->_defaultTemplate->user_id=\GO::user()->id;
		}
		
		if(isset($params['default_template_id']))
		{
			$this->_defaultTemplate->template_id=$params['default_template_id'];
			$this->_defaultTemplate->save();
		}
		
		$findParams = \GO\Base\Db\FindParams::newInstance()->order('name');			
		$findParams->getCriteria()->addCondition('type', \GO\Addressbook\Model\Template::TYPE_EMAIL);
				
		$stmt = \GO\Addressbook\Model\Template::model()->find($findParams);
		
		$store = \GO\Base\Data\Store::newInstance(\GO\Addressbook\Model\Template::model());		
		$store->getColumnModel()->setFormatRecordFunction(array($this, 'formatEmailSelectionRecord'));
		
		$store->setStatement($stmt);
		$store->addRecord(array(
			'group' => 'templates',
			'checked'=>isset($this->_defaultTemplate->template_id) && $this->_defaultTemplate->template_id==0,
			'text' => \GO::t('none'),
			'template_id'=>0
		));
		
		$response = $store->getData();
		
		if($response['total']>0){

			$response['results'][] = '-';

			$record = array(
				'text' => \GO::t('setCurrentTemplateAsDefault','addressbook'),
				'template_id'=>'default'
			);

			$response['results'][] = $record;
		}
		
		return $response;
	}
	
	public function formatEmailSelectionRecord(array $formattedRecord, \GO\Base\Db\ActiveRecord $model, \GO\Base\Data\ColumnModel $cm){
		if(!isset($this->_defaultTemplate->template_id)){
			$this->_defaultTemplate->template_id=$model->id;
			$this->_defaultTemplate->save();
		}
		$formattedRecord['group'] = 'templates';
		$formattedRecord['checked']=$this->_defaultTemplate->template_id==$model->id;
		$formattedRecord['text']=$model->name;
		$formattedRecord['template_id']=$model->id;
		return $formattedRecord;
	}
	
}