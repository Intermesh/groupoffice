<?php

namespace GO\Users;


class UsersModule extends \GO\Base\Module{	
	public function author() {
		return 'Merijn Schering';
	}
	
	public function authorEmail() {
		return 'mschering@intermesh.nl';
	}
	public function autoInstall() {
		return false; //is installed as core module!
	}
	
	public function adminModule() {
		return true;
	}
	public static function loadSettings($settingsController, &$params, &$response, $user) {
		$startModule = \GO\Base\Model\Module::model()->findByPk($user->start_module);
		$response['data']['start_module_name']=$startModule ? $startModule->moduleManager->name() : '';
		
		if(isset($response['data']['company_id'])){
			$company = \GO\Addressbook\Model\Company::model()->findByPk($response['data']['company_id'], false, true);
			if($company){
				$response['data']['company_name']=$company->name;
			}		
		}
		$response['remoteComboTexts']['holidayset']=\GO::t($user->holidayset);
		
		return parent::loadSettings($settingsController, $params, $response, $user);
	}
}
