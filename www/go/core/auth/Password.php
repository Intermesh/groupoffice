<?php
namespace go\core\auth;

use go\core\auth\model\Token;
use go\core\auth\model\User;
use go\core\validate\ErrorCode;

class Password extends BaseAuthenticator {	
	
	public function authenticate(Token $token, array $params) {
		if(!isset($params['password'])){
			$this->setValidationError('password', ErrorCode::REQUIRED);
			return false;
		}
		
		if(!$token->getUser()->checkPassword($params['password'])){
			$this->setValidationError('password', ErrorCode::INVALID_INPUT);
			return false;
		}
		
		return true;
	}

	public static function isAvailableFor(User $user) {
		return true;
	}

	

}