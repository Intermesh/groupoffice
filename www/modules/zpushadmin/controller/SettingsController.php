<?php

namespace GO\Zpushadmin\Controller;


class SettingsController extends \GO\Base\Controller\AbstractModelController {
	
	protected function actionLoad($params) {
		
		$settings =  \GO\Zpushadmin\Model\Settings::load();
		
		return array(
				'success'=>true,
				'data'=>$settings->getArray()
		);
	}
	
	protected function actionSubmit($params) {
		
		$settings =  \GO\Zpushadmin\Model\Settings::load();

		return array(
				'success'=>$settings->saveFromArray($params),
				'data'=>$settings->getArray()
		);
	}
	
}
