<?php

namespace go\core\auth;

abstract class State {
	/**
	 * Get the ID logged in user
	 * 
	 * @return int|null
	 */
	abstract function getUserId();
	
	/**
	 * Get the logged in user
	 * 
	 * @return model\User|null
	 */
	abstract function getUser();
	
	
	/**
	 * Check if a user is authenticated
	 * 
	 * @return boolean
	 */
	abstract function isAuthenticated();

	/**
	 * Check if the logged in user is an admin
	 * 
	 * @return bool
	 */
	abstract public function isAdmin();

}

