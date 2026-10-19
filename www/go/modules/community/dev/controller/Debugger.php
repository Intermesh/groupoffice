<?php

namespace go\modules\community\dev\controller;

use go\core\App;
use go\core\Controller;
use go\core\jmap\Response;

class Debugger extends Controller {
	// No authenticate() override: the debug entries contain queries, parameters and stack traces, so only users who
	// are logged in and have access to this module may get them.
	public function get() {
		Response::get()->addResponse(App::get()->getDebugger()->getEntries());
	}
}
