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

	/**
	 * By default, the user / authenticator combination should be cached for a certain amount of time.
	 *
	 * @return bool
	 */
	public function needsCache(): bool
	{
		return true;
	}
}

