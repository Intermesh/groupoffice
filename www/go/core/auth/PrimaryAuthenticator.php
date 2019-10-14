<?php

namespace go\core\auth;

use go\core\model\User;

abstract class PrimaryAuthenticator extends BaseAuthenticator {
		
	/**
	 * Get a user object from this authenticator
	 * 
	 * @param string $username
	 * @param string $password
	 * 
	 * @return User|boolean
	 */
	public function authenticate($username, $password) {
		return false;
	}
	
}

