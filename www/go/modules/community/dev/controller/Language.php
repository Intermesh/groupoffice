<?php

namespace go\modules\community\dev\controller;

use go\core\App;
use go\core\Controller;
use go\core\jmap\Response;

class Language extends Controller {
	public function export() {
		Response::get()->addResponse(["debug", App::get()->getDebugger()->getEntries()]);
	}
}

