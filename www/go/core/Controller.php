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

    $this->rights = $this->getClassRights();

    if(!$this->checkModulePermissions()) {
	    $mod = Module::findByClass(static::class, ['name', 'package']);
	    throw new Exception(403, str_replace('{module}', ($mod->package ?? "legacy") . "/" . $mod->name, go()->t("Forbidden, you don't have access to module '{module}'.")));
    }

	}

	protected $rights;

	protected function checkModulePermissions() {
		return $this->rights->mayRead;
	}


	/**
	 * Get the permission level of the module this controller belongs to.
	 * 
	 * @return int
	 */
	protected function getClassRights() {
		return go()->getAuthState()->getClassRights(static::class);
	}
}
