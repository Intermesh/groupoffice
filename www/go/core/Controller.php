<?php

namespace go\core;

use go\core\http\Exception;

abstract class Controller {

	/**
	 * Only authenticated users can access
	 * @throws Exception
	 */
	public function __construct() {
		$this->authenticate();
	}

	/**
	 * @throws Exception
	 */
	protected function authenticate() {
    if (!go()->getAuthState()->isAuthenticated()) {			
      throw new Exception(401, "Unauthorized");
		}

		if(!$this->getModulePermissionLevel()) {
			throw new Exception(403, "Forbidden, you don't have access to this module.");
		}
	}

	/**
	 * Get the permission level of the module this controller belongs to.
	 * 
	 * @return int
	 */
	protected function getModulePermissionLevel(): int
	{
		return go()->getAuthState()->getClassPermissionLevel(static::class);
	}
}
