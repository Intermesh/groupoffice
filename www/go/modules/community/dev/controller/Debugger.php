<?php

namespace go\modules\community\dev\controller;

use go\core\App;
use go\core\Controller;
use go\core\jmap\Response;

class Debugger extends Controller {
	public function get() {
		Response::get()->addResponse(App::get()->getDebugger()->getEntries());
	}
}
