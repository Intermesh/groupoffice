<?php

namespace go\core;

use go\core\http\Exception;
use go\core\http\Request;
use go\core\model\Module;
use go\core\model\Acl;

abstract class Controller { 

	/**
	 * Only authenticated users can access
	 */
	public function __construct() {
		$this->authenticate();
	}

	protected function authenticate() {  
    if (!go()->getAuthState()->isAuthenticated()) {			
      throw new Exception(401, "Unauthorized");
		}

		if(!$this->getModulePermissionLevel()) {
			throw new Exception(403, "Forbidden");
		}
	}


	private $modulePermissionLevel;

	/**
	 * Get the permission level of the module this controller belongs to.
	 * 
	 * @return int
	 */
	protected function getModulePermissionLevel() {
		return go()->getAuthState()->getClassPermissionLevel(static::class);
	}
}
