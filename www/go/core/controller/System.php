<?php

namespace go\core\controller;

use Exception;
use GO;
use go\core\cli\controller\System as CliSystemCtrl;
use go\core\Controller;
use go\core\jmap\Response;
use go\core\model;

class System extends Controller {
	public function demo() {

		go()->getEnvironment()->setMaxExecutionTime(180);

		ob_start();
		$cliCtrl = new CliSystemCtrl();
		$cliCtrl->demo();
		ob_end_clean();

		return ['success' => true];
	}
}