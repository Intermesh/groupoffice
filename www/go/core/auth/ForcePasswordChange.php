<?php

namespace go\core\auth;

use go\core\model\Token;
use go\core\auth\SecondaryAuthenticator;
use go\core\db\Query;
use go\core\validate\ErrorCode;
use go\core\model\User;

class ForcePasswordChange extends SecondaryAuthenticator {

	public function authenticate(Token $token, array $data) {

		if(!isset($data['password'])){
			$this->setValidationError('password', ErrorCode::REQUIRED);
			return false;
		}

		$user = $token->getUser();

		$user->setCurrentPassword($data['currentPassword']);
		if($user->checkPassword($data['password'])) {
			$this->setValidationError('password', ErrorCode::INVALID_INPUT, go()->t("You must choose a different password"));
			return false;
		}

		$user->setPassword($data['password']);

		$user->save();

		if($user->hasValidationErrors()) {
			foreach($user->getValidationErrors() as $key => $e) {
				$this->setValidationError($key, $e['code'], $e['description']);
			}
			return false;
		}

		return true;
	}

	public static function isAvailableFor(string $username) : bool
	{
		$id = (new Query)
			->selectSingleValue('id')
			->from("core_user", "u")
			->where(['username' => $username, 'forcePasswordChange' => true])
			->single();

		return !empty($id);
	}
}
