<?php

namespace go\modules\community\otp;

use go\core\model\Token;
use go\core\auth\SecondaryAuthenticator;
use go\core\db\Query;
use go\core\validate\ErrorCode;
use go\core\model\User;

class OtpAuthenticator extends SecondaryAuthenticator {

	public function authenticate(Token $token, array $data) {
		
		if(!isset($data['otp_code'])){
			$this->setValidationError('otp_code', ErrorCode::REQUIRED);
			return false;
		}
		
		$otp = $token->getUser()->otp;
		if(!$otp){
			$this->setValidationError('otp_code', ErrorCode::NOT_FOUND);
			return false;
		}
		
		if(!$otp->verifyCode($data['otp_code'])){
			$this->setValidationError('otp_code', ErrorCode::INVALID_INPUT);
			return false;
		}
		
		return true;
	}

	public static function isAvailableFor(string $username) :bool {
		
		$id = (new Query)
						->selectSingleValue('id')
						->from("otp_secret", "s")
						->join("core_user", "u", "u.id = s.userId")
						->where(['username' => $username, 'verified' => true])->single();
		
		return !empty($id);
	}
}
