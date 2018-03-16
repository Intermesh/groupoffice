<?php

namespace go\modules\community\googleauthenticator;

use go\core\auth\model\Token;
use go\core\auth\model\User;
use go\core\auth\SecondaryAuthenticator;
use go\core\validate\ErrorCode;

class Googleauthenticator extends SecondaryAuthenticator {

	public function authenticate(Token $token, array $data) {
		
		if(!isset($data['googleauthenticator_code'])){
			$this->setValidationError('googleauthenticator_code', ErrorCode::REQUIRED);
			return false;
		}
		
		$googleAuthenticator = $token->getUser()->googleauthenticator;
		if(!$googleAuthenticator){
			$this->setValidationError('googleauthenticator_code', ErrorCode::NOT_FOUND);
			return false;
		}
		
		if(!$googleAuthenticator->verifyCode($data['googleauthenticator_code'])){
			$this->setValidationError('googleauthenticator_code', ErrorCode::INVALID_INPUT);
			return false;
		}
		
		return true;
	}

	public static function isAvailableFor($username) {
		
		$user = User::find(['id','googleauthenticator'])->where(['username' => $username])->single();
		if(!$user) {
			return false;
		}
		
		$googleAuthenticator = $user->googleauthenticator;

		return !empty($googleAuthenticator);
	}
}
