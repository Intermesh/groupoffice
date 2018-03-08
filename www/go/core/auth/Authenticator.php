<?php

namespace go\core\auth;

use go\core\auth\model\Token;
use go\core\auth\model\User;

interface Authenticator{
	
	/**
	 * Get the id of this authenticator
	 * 
	 * Returns String
	 */
	public static function id();
	
	/**
	 * Check if this authenticator is available for the given user
	 * 
	 * Returns boolean
	 */
	public static function isAvailableFor(User $user);

	/**
	 * 
	 * @param Token $token
	 * @param array $params
	 * 
	 * Returns boolean
	 */
	public function authenticate(Token $token, array $params);
}