<?php

namespace go\core\auth;

use Exception;
use go\core\model\User;

abstract class PrimaryAuthenticator extends BaseAuthenticator {
		
	/**
	 * Get a user object from this authenticator
	 * 
	 * @param string $username
	 * @param string $password
	 * 
	 * @return boolean|User
	 */
	public function authenticate(string $username, string $password): bool|User
	{
		return false;
	}
	
}

