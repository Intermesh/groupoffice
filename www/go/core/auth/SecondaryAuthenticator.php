<?php

namespace go\core\auth;

use go\core\model\Token;
use go\core\model\User;

abstract class SecondaryAuthenticator extends BaseAuthenticator {
	
	/**
	 * Get a user object from this authenticator
	 * 
	 * @param string $username
	 * @param string $password
	 * 
	 * @return User|boolean
	 */
	public function authenticate(Token $token, array $data) {
		return false;
	}
	
}

	

