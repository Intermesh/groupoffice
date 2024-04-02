<?php
namespace go\core\auth;

use go\core\model\User;

class Password extends PrimaryAuthenticator {	
	
	public static function isAvailableFor(string $username): bool
	{
		return !!User::find()->selectSingleValue('id')->where(['username' => $username])->andWhere('password', '!=', 'null')->single();
	}
	
	/**
	 * Checks if the given password matches the password in the core_auth_password table.
	 * 
	 * @param string $password
	 * @return boolean|User
	 */
	public function authenticate(string $username, string $password): bool|User
	{
		$user = User::find(['id', 'username', 'password', 'enabled', 'forcePasswordChange'], true)->where(['username' => $username])->single();
		if(!$user) {
			return false;
		}
		if(!$user->passwordVerify($password)) {
			return false;
		}

		User::fireEvent(User::EVENT_PASSWORD_VERIFIED, $user, $password);
	
		return $user;
	}
}
