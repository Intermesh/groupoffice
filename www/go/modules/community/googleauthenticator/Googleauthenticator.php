<?php

namespace go\modules\community\googleauthenticator;

use go\core\model\Token;
use go\core\auth\SecondaryAuthenticator;
use go\core\db\Query;
use go\core\validate\ErrorCode;
use go\core\model\User;

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
		
		$id = (new Query)
						->selectSingleValue('id')
						->from("googleauth_secret", "s")
						->join("core_user", "u", "u.id = s.userId")
						->where(['username' => $username])->single();
		
		return !empty($id);
	}
}
