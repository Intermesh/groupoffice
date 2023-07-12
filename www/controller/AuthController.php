<?php

/**
 * 
 * TODO
 * 
 * The whole init process of Group-Office has to be remodelled.
 * The default_scripts.inc.php file is ugly and bad design. Instead all init
 * views in modules should register client scripts and css files.
 */

namespace GO\Core\Controller;

use GO;
use GO\Base\Model\User;
use go\core\http\Response;

class AuthController extends \GO\Base\Controller\AbstractController {

	protected $defaultAction = 'Init';
	
	/**
	 * Guests need to access these actions.
	 * 
	 * @return array. 
	 */
	protected function allowGuests() {
		return array('init', 'setview','logout','login','resetpassword','setnewpassword','sendresetpasswordmail','resetexpiredpassword','acceptnewclient');
	}
	
	protected function ignoreAclPermissions() {
		return array('setnewpassword','resetexpiredpassword','acceptnewclient');
	}

	private function loadInit() {
		if(\GO::config()->getOriginalValue('debug')){
			\GO\Base\Observable::cacheListeners();
		}

		//when GO initializes modules need to perform their first run actions.
		unset(\GO::session()->values['firstRunDone']);

		if (\GO::user())
			$this->fireEvent('loadapplication', array(&$this));
	}

	protected function actionInit($params) {

		Response::get()->sendDocumentSecurityHeaders();
		Response::get()->sendHeaders();
		
		if(!empty($params['SET_LANGUAGE']))
			\GO::config()->language=$params['SET_LANGUAGE'];

		$this->loadInit();
		require(\GO::view()->getTheme()->getPath().'Layout.php');	
	}

	/**
	 * This function is called when a login on multiple locations is found and 
	 * the user clicked on "Continue"
	 * 
	 * @param int $userId
	 * @param string $userToken
	 * @return array $response
	 */
	protected function actionAcceptNewClient($userId,$userToken){
		
		$response = array('success'=>true);
		$currentClient = \GO\Base\Model\Client::lookup($userId);

		// Extra security layer, the digest of the user must match the token
		if($currentClient && $currentClient->user->digest != $userToken){
			Throw new \Exception('Token invalid');
		}
				
		$userClients = \GO\Base\Model\Client::lookupByUser($currentClient->user_id);
		
		foreach($userClients as $userClient){
			$userClient->in_use = ($userClient->id == $currentClient->id);
			
			// Update the last_active to now
			if($userClient->in_use){
				$userClient->last_active = time();
			}
			
			$userClient->save();
		}
		
		return $response;	
	}
	
	/**
	 * This is the function that checks if the user is still logged in in the same client
	 * This only checks for the client when $config['use_single_login'] is set to true
	 * 
	 * @return array $response
	 */
	protected function actionCheckClient(){
	
		$response = array(
			'loginValid'=>true,
			'success'=>true
		);
		
		$isUserSwitched = \GO::session()->isUserSwitched();
		
		\GO::debug('Is the user switched: '.($isUserSwitched?'true':'false'));
		
		if(\GO::config()->use_single_login && !$isUserSwitched){
			$user = GO::user();
			$currentClient = \GO\Base\Model\Client::lookup($user->id);

			if(!$currentClient->in_use){
				\GO::session()->logout();
				$response['loginValid'] = false;
			}
		}
		return $response;	
	}
	
	

	protected function actionLogin($params) {
		
		if(!empty($params["login_language"])){
			GO::language()->setLanguage($params["login_language"]);
		}
		
		if(!empty($params['domain'])){
			$params['username'].=$params['domain'];	
		}
		
		$response = array();
		
		if(!$this->fireEvent('beforelogin', array(&$params, &$response))){
			$response['success'] = false;
			
			if(!isset($response['feedback']))
				$response['feedback']=GO::t("Wrong username or password");

			return $response;		
		}
			
		try{
			$user = \GO::session()->login($params['username'], $params['password'], true);
		}catch(\GO\Base\Exception\OtherLoginLocation $e){
			
			$user = User::model()->findSingleByAttribute('username', $params['username']);
			$client = GO\Base\Model\Client::lookup($user->id);
			
			$otherClient = $client->checkLoggedInOnOtherLocation();
			
			$response['success'] = false;
			$response['userId'] = $user->id;
			$response['userToken'] = $user->digest;
			$response['feedback']= nl2br(str_replace(array('{last_login_ip}','{last_login_time}'), array($otherClient->ip,\GO\Base\Util\Date::get_timestamp($otherClient->last_active)), GO::t("You are already logged in from another computer at IP {last_login_ip} since {last_login_time}.
If you log in here, your other instance will be logged out.")));
			$response['exceptionCode']=$e->getCode();
			return $response;
		}
		
		$response['success'] = $user != false;		

		if (!$response['success']) {		
			$response['feedback']=\GO::t("Wrong username or password");			
		} else {		
			if (\GO::config()->remember_login && !empty($params['remind'])) {

				$encUsername = \GO\Base\Util\Crypt::encrypt($params['username']);
				if (!$encUsername)
					$encUsername = $params['username'];

				$encPassword = \GO\Base\Util\Crypt::encrypt($params['password']);
				if (!$encPassword)
					$encPassword = $params['password'];

				\GO\Base\Util\Http::setCookie('GO_UN', $encUsername);
				\GO\Base\Util\Http::setCookie('GO_PW', $encPassword);
			}
			
			// When single login is activated and the login is successfull then set in_use to true for this client
			if(\GO::config()->use_single_login){
				$currentClient = \GO\Base\Model\Client::lookup($user->id);
				$currentClient->in_use = true;
				$currentClient->save();
			}
			
			$response['groupoffice_version']=\GO::config()->version;
			$response['user_id']=$user->id;
			$response['security_token']=\GO::session()->values["security_token"];
			$response['sid']=session_id();
			
			if(!empty($params['return_user_info'])){
				$response['modules']=array();
				
				foreach(\GO::modules()->getAllModules() as $module){
					$response['modules'][]=$module->name;
				}
				
				$response['user']=\GO::user()->getAttributes();
			}
			
			
			if(!empty($params["login_language"]))
			{
				GO::language()->setLanguage($params["login_language"]); 

				
				\GO::user()->language=\GO::language()->getLanguage();
				\GO::user()->save();
			}
			
		}
		
//		return $response;

		if (\GO\Base\Util\Http::isAjaxRequest())
		{
			return $response;
		}else{
			$this->redirect();
		
		}
	}


}
