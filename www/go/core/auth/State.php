<?php

namespace go\core\auth;

abstract class State {
	/**
	 * Get the logged in user
	 * 
	 * @return model\User|boolean
	 */
	abstract function getUser();
	
	
	/**
	 * Check if a user is authenticated
	 * 
	 * @return boolean
	 */
	abstract function isAuthenticated();

}

