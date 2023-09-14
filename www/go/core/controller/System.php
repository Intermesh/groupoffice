<?php

namespace go\core\controller;

use Exception;
use GO;
use go\core\cli\controller\System as CliSystemCtrl;
use go\core\Controller;
use go\core\ErrorHandler;
use go\core\exception\Forbidden;
use go\core\fs\Blob;
use go\core\jmap\exception\InvalidArguments;
use go\core\jmap\Response;
use go\core\model;

class System extends Controller {
	public function demo() {

		go()->getEnvironment()->setMaxExecutionTime(240);

		ob_start();
		$cliCtrl = new CliSystemCtrl();
		$cliCtrl->demo();
		ob_end_clean();

		return ['success' => true];
	}

	/**
	 * Blob method for CLI programs
	 *
	 * It aborts the regular JMAP output and outputs the file instead!
	 *
	 * @param $params
	 * @return void
	 * @throws Forbidden
	 */
	public function blob($params) {
		if(!go()->getAuthState()->isAdmin()) {
			throw new Forbidden();
		}

		$blob = Blob::findById($params['id']);

		$blob->getFile()->output();
		exit();
	}

	public function logClientError($params) {
		if(!isset($params['message'])) {
			throw new InvalidArguments("Required parameter 'message' is missing");
		}
		ErrorHandler::log("CLIENT: " . $params['message']);

		return ['success'=>true];
	}
}