<?php
namespace go\core\auth;

use go\core\model\User;

class Password extends PrimaryAuthenticator {	
	
	public static function isAvailableFor($username) {		
		return User::find()->selectSingleValue('id')->where(['username' => $username])->andWhere('password', '!=', 'null')->single() !== false;
	}
	
	/**
	 * Checks if the given password matches the password in the core_auth_password table.
	 * 
	 * @param string $password
	 * @return boolean 
	 */
	public function authenticate($username, $password) {		
		$user = User::find(['id', 'username', 'password', 'enabled'])->where(['username' => $username])->single();
		if(!$user) {
			return false;
		}
		if(!$user->passwordVerify($password)) {
			return false;
		}
	
		return $user;
	}
}
