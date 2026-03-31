<?php
use go\core\App;
use go\core\ErrorHandler;
use go\core\jmap\Response;
use go\core\jmap\Router;
use go\core\jmap\Request;
use go\core\jmap\State;

require("../vendor/autoload.php");

//Create the app with the database connection
$jmapState = new State();
App::get()->setAuthState($jmapState);

try {
	switch (Request::get()->getMethod()) {

		case 'POST':
			$router = new Router();
			$conf = go()->getConfig();
			if (!empty($conf['accessLog'])) {
				$router->setLogFile($conf['accessLog']);
			}
			$router->run();
			break;

		case 'GET':
			$jmapState->outputSession();
			break;

		case 'OPTIONS':
			Response::get()->sendHeaders();
			break;

		default:
			throw new Exception("Method " . Request::get()->getMethod() . " not supported");
	}
} catch (\Exception $e) {
	Response::get()->setStatus(500, $e->getMessage());
	ErrorHandler::logException($e);
}