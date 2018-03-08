<?php

namespace go\modules\community\googleauthenticator;

use go\core\auth\BaseAuthenticator;
use go\core\auth\model\Token;
use go\core\auth\model\User;
use go\core\validate\ErrorCode;

class Googleauthenticator extends BaseAuthenticator {

	public function authenticate(Token $token, array $params) {
		
		if(!isset($params['googleauthenticator_code'])){
			$this->setValidationError('googleauthenticator_code', ErrorCode::REQUIRED);
			return false;
		}
		
		$googleAuthenticator = $token->getUser()->googleauthenticator;
		if(!$googleAuthenticator){
			$this->setValidationError('googleauthenticator_code', ErrorCode::NOT_FOUND);
			return false;
		}
		
		if(!$googleAuthenticator->verifyCode($params['googleauthenticator_code'])){
			$this->setValidationError('googleauthenticator_code', ErrorCode::INVALID_INPUT);
			return false;
		}
		
		return true;
	}

	public static function isAvailableFor(User $user) {
		$googleAuthenticator = $user->googleauthenticator;

		return !empty($googleAuthenticator);
	}
}
