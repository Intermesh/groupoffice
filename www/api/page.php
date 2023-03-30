<?php
/**
 *
 * Usage
 *
 * /api/page.php/$PACKAGE/$MODULENAME/$CONTROLLER/$METHOD
 *
 * eg. /api/page.php/nuw/projectsdsgvo/answer/accept
 *
 *
 */

use go\core\App;
use go\core\ErrorHandler;
use go\core\fs\Blob;
use go\core\http\Request;
use go\core\http\Response;
use go\core\util\StringUtil;

require("../vendor/autoload.php");
App::get();


//Only allow words in controller and method
function checkPathInput(string $path) {
	if(preg_match("/[^a-z0-9\/_]/i", $path)) {
		http_response_code(400);
		exit("Bad request, only alpha numeric _/ characters are allowed in the path.");
	}
}

if (Request::get()->getMethod() == 'OPTIONS') {
	Response::get()->output();
	exit();
}

try {

	Response::get()->sendDocumentSecurityHeaders();

	if (strpos($_SERVER['PATH_INFO'], '/') === false) {

		$blob = Blob::findById(App::get()->getSettings()->logoId);

		if (!$blob) {
			echo "Not found";
			http_response_code(404);
			exit();
		}

		$blob->output();
	}

	$parts = explode("/", $_SERVER['PATH_INFO']);
	array_shift($parts);
	$package = array_shift($parts);

	if ($package == "core") {
		$c = go();

		$ctrlCls = App::class;
		$method = "page" . array_shift($parts);
	} else {
		$module = array_shift($parts);
		$method = "page" . array_shift($parts);

		//left over are params
		$ctrlCls = "go\\modules\\" . $package . "\\" . $module . "\\Module";

		checkPathInput($ctrlCls);

		if (!class_exists($ctrlCls)) {
			http_response_code(404);
			exit("Class '$ctrlCls' not found");
		}

		$c = $ctrlCls::get();
	}

	checkPathInput($method);

	if (!method_exists($c, $method)) {
		http_response_code(404);
		exit("method '$method' not found in '$ctrlCls'");
	}

	call_user_func_array([$c, $method], $parts);

} catch (Exception $e) {
	ErrorHandler::logException($e);
	Response::get()->setStatus(500);
	Response::get()->setContentType("text/plain");
	Response::get()->output($e->getMessage());

	if(go()->getDebugger()->enabled) {
		go()->getDebugger()->printEntries();
	}
}