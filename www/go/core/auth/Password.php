<?php
namespace go\core\auth;

use go\core\auth\model\Token;
use go\core\auth\model\User;
use go\core\validate\ErrorCode;

class Password extends PrimaryAuthenticator {	
	
	public static function isAvailableFor($username) {
		return User::find(['id'])->where(['username' => $username])->andWhere('password', '!=', 'null')->single() !== false;
		
	}
	
	public function authenticate($username, $password) {
		$user = User::find()->where(['username' => $username])->single();
		if(!$user) {
			return false;
		}
				
		if(!$user->checkPasswordTable($password)) {
			return false;
		}
	
		return $user;
	}
}
