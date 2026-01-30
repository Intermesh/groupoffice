<?php

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



}
