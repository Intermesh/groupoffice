<?php

namespace go\core\auth;
use go\core\model\Module;

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
	 * @return go\core\model\User|null
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

	private static $classPermissionLevels = [];

	/**
	 * Get the permission level of the module this controller belongs to.
	 * 
	 * @return int
	 */
	public function getClassPermissionLevel($cls) {
		if(!isset(self::$classPermissionLevels[$cls])) {
			$mod = Module::findByClass($cls, ['aclId', 'permissionLevel']);
			self::$classPermissionLevels[$cls]= $mod->getPermissionLevel();
		}


		return self::$classPermissionLevels[$cls];
	}

}

