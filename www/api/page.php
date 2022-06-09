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
use go\core\fs\Blob;
use go\core\App;
use go\core\http\Response;
use go\core\http\Request;

require("../vendor/autoload.php");
App::get();

if(Request::get()->getMethod() == 'OPTIONS') {
	Response::get()->output();
	exit();
}

try {

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
		$method = "page" . array_shift($parts);
	} else {
		$module = array_shift($parts);
		$method = "page" . array_shift($parts);
		//left over are params

		$ctrlCls = "go\\modules\\" . $package . "\\" . $module . "\\Module";
		if (!class_exists($ctrlCls)) {
			http_response_code(404);
			exit("Class '$ctrlCls' not found");
		}

		$c = $ctrlCls::get();
	}

	if (!method_exists($c, $method)) {
		http_response_code(404);
		exit("method '$method' not found in '$ctrlCls'");
	}

	call_user_func_array([$c, $method], $parts);

} catch(Exception $e) {
	ErrorHandler::logException($e);
	Response::get()->setStatus(500);
	Response::get()->output($e->getMessage());
}