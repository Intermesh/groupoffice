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

	public function get() {
		Response::get()->addResponse(App::get()->getDebugger()->getEntries());
	}
}
