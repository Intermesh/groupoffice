<?php

namespace go\modules\core\users\controller;

use GO;
use go\core\exception\Forbidden;
use go\core\jmap\EntityController;
use go\core\jmap\exception\InvalidArguments;
use go\core\jmap\Response;
use go\core\orm\Entity;
use go\modules\core\users\model;



class User extends EntityController {	
	
	protected function canUpdate(Entity $entity) {
		
		if(!GO()->getAuthState()->getUser()->isAdmin()) {
			if($entity->isModified('groups')) {
				return false;
			}
		}
		
		return parent::canUpdate($entity);
	}
	
	protected function canCreate() {
		return GO()->getAuthState()->getUser()->isAdmin();
	}
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass() {
		return model\User::class;
	}
	
	public function loginAs($params) {
		
		if(!isset($params['userId'])) {
			throw new InvalidArguments("Missing parameter userId");
		}
		
		if(!GO()->getAuthState()->getUser()->isAdmin()) {
			throw new Forbidden();
		}
		
		$user = model\User::findById($params['userId']);
		
		if(!$user->enabled) {
			throw new Exception("This user is disabled");
		}
		
		$token = GO()->getAuthState()->getToken();
		$token->userId = $params['userId'];
		$success = $token->setAuthenticated();
		
		$_SESSION['GO_SESSION'] = array_filter($_SESSION['GO_SESSION'], function($key) {
			return in_array($key, ['user_id', 'accessToken', 'security_token']);
		}, ARRAY_FILTER_USE_KEY); 
		
		Response::get()->addResponse(['success' => true]);
	}
}
