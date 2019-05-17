<?php

namespace go\core;

use go\core\http\Exception;
use go\core\http\Request;

abstract class Controller { 

	/**
	 * Only authenticated users can access
	 */
	public function __construct() {
		$this->authenticate();
	}

	protected function authenticate() {  
    if (!GO()->getAuthState()->isAuthenticated()) {			
      throw new Exception(401, "Unauthorized");
    }  
	}

}
