<?php

namespace go\modules\community\dev\controller;

use go\core\App;
use go\core\Controller;
use go\core\jmap\Response;

class Debugger extends Controller {
	public function get() {
		Response::get()->addResponse(["debug", App::get()->getDebugger()->getEntries()]);
	}
}
