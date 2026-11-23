<?php

namespace go\modules\community\dev\controller;

use go\core\App;
use go\core\Controller;
use go\core\jmap\Response;

class Debugger extends Controller {

	/**
	 * When debugging is enabled any user will see it.
	 *
	 * @return true
	 */
	protected function authenticate()
	{
		return true;
	}

	/**
	 * Get the debug entries of the current request
	 *
	 * @apiPermission Module access: no authentication at all, authenticate() is overridden. Returns only the debug entries collected during the caller's own request, which only exist when debugging is enabled.
	 */
	public function get() {
		Response::get()->addResponse(App::get()->getDebugger()->getEntries());
	}
}
