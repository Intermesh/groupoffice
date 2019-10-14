<?php

namespace GO\Addressbook\Controller;

use GO\Base\Controller\AbstractJsonController;


class SettingsController extends AbstractJsonController {
	
	protected function actionSubmit($params){

		$response = array('success'=>true,'data'=>array());
		
		
		$this->fireEvent('submit', array(
				&$this,
				&$params,
				&$response
		));
		
		echo $this->renderJson($response);
	}
	
	protected function actionLoad($params){
		
		$response = array('success'=>true,'data'=>array());
		
		$this->fireEvent('load', array(
				&$this,
				&$params,
				&$response
		));
		
		echo $this->renderJson($response);
	}
}

