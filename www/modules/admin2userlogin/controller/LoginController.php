<?php

namespace GO\Admin2userlogin\Controller;


class LoginController extends \GO\Base\Controller\AbstractController {
	protected function actionSwitch($params){
//		
//		if(!\GO::user()->isAdmin())
//			throw new \Exception("This feature is for admins only!");
		
		$oldUser=\GO::user();
		
		if(\GO::config()->use_single_login){
			$currentClient = \GO\Base\Model\Client::lookup($oldUser->id);
			$currentClient->in_use = false;
			$currentClient->save();
		}
		
		$debug = !empty(\GO::session()->values['debug']);
		
		$user = \GO\Base\Model\User::model()->findByPk($params['user_id']);
		
//		$token = \go\core\auth\model\Token::find()->where(['accessToken' => \GO::session()->values['accessToken']])->single();
//		$token->userId = $user->id;
//		if(!$token->save()) {
//			throw new \Exception("Could not set token");
//		}
//		
		\GO::session()->clear(); //clear session
		\GO::session()->setCurrentUser($user->id, $oldUser->id);
		
		
		
		//\GO::session()->setCompatibilitySessionVars();
		
		if($debug)
			\GO::session()->values['debug']=$debug;
		
		\GO::infolog($oldUser->username." logged-in as user: \"".$user->username."\" from IP: ".$_SERVER['REMOTE_ADDR']);
		
		if(\GO::modules()->isInstalled('log')){		
			\GO\Log\Model\Log::create('switchuser', "'".$oldUser->username."' logged in as '".$user->username."'");
		}
		
		$this->redirect();
	}
}