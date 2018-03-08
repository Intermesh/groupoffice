<?php

namespace go\core;

use go\core\http\Exception;

abstract class Controller {

	/**
	 * Only authenticated users can access
	 */
	public function __construct() {
		$this->authenticate();
	}

	protected function authenticate() {
		if (!App::get()->getAuthState()->isAuthenticated()) {
			throw new Exception(401);
		}
	}

}
