<?php
namespace GO\Dropbox\Controller;

use GO\Base\Controller\AbstractJsonController;
use GO\Dropbox\Model\Settings;

class SettingsController extends AbstractJsonController {
	
	protected function actionSubmit($params){
		
		$settings = Settings::load();
		
		$success = $settings->saveFromArray($params);

		$data = array(
			'callback_uri'=> \GO\Dropbox\DropboxModule::getCallbackUri(),
			'webhook_uri'=> \GO\Dropbox\DropboxModule::getWebhookUri()
		);
		
		$response = array(
			'success'=>$success,
			'data'=>array_merge($data,$settings->getArray())
		);
		
		if(!$success){
			$response['feedback'] = 'Oops, Something went wrong while saving the settings.';
		}
		
		echo $this->renderJson($response);
	}
	
	protected function actionLoad($params){
		
		$settings = Settings::load();
		
		$data = array(
			'callback_uri'=> \GO\Dropbox\DropboxModule::getCallbackUri(),
			'webhook_uri'=> \GO\Dropbox\DropboxModule::getWebhookUri()
		);
		
		$response = array(
			'success'=>true,
			'data'=>array_merge($data,$settings->getArray())
		);
		
		echo $this->renderJson($response);
	}
}

