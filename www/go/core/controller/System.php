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
	/**
	 * Create demo data
	 *
	 * @apiPermission Only administrators.
	 */
	public function demo() {

		if(!go()->getAuthState()->isAdmin()) {
			throw new Forbidden();
		}

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
	 * @apiPermission Only administrators.
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

	/**
	 * Write an error of the client to the server error log
	 *
	 * @apiPermission Module access: only writes the given message to the server error log. Nothing is returned or changed.
	 */
	public function logClientError($params) {
		if(!isset($params['message'])) {
			throw new InvalidArguments("Required parameter 'message' is missing");
		}
		ErrorHandler::log("CLIENT: " . $params['message']);

		return ['success'=>true];
	}
}