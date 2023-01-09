<?php
/**
 * Download entry point
 * 
 * GET parameter "blob" is required. Typically a blob hash from the core_blob table.
 * 
 * You can also implement custom download methods in "Module.php" of your module.
 * Methods prefixed with "download" can be accessed. For example method 
 * go\modules\community\addressbook\Module::downloadVcard($contactId) 
 * can be accessed with: "download.php?blob=community/addressbook/vcard/1"
 * 
 */
require("../vendor/autoload.php");

use go\core\App;
use go\core\ErrorHandler;
use go\core\fs\Blob;
use go\core\http\Response;
use go\core\jmap\State;
use go\core\http\Request;

App::get();
if(Request::get()->getMethod() == 'OPTIONS') {
	Response::get()->output();
	exit();
}

if(!go()->setAuthState(new State())->getAuthState()->isAuthenticated()) {
	Response::get()->setStatus(401);
	Response::get()->output("Unauthorized.");
	exit();
}

if(!isset($_GET['blob'])) {
	Response::get()->setStatus(400);
	Response::get()->output("Bad request. Param 'blob' must be given.");
	exit();
}

try {

	if (strpos($_GET['blob'], '/') === false) {
		$blob = Blob::findById($_GET['blob']);
		if (!$blob) {

			Response::get()->setStatus(404);
			Response::get()->output("Not found");
			exit();
		}

		$inline = !empty($_GET['inline']);

		// prevent html to render on same domain having access to all global JS stuff
		if($blob->type == 'text/html') {
			$inline = false;
		}

		$ua_info = \donatj\UserAgent\parse_user_agent();
		if($ua_info['browser'] == 'Safari' && substr(strtolower($blob->name), -5) == '.webm' && !strstr(Request::get()->getUri(), 'webm')) {
			//workaround webm bug in safari that needs a webm extension :(
			header("Location: " . str_replace('download.php?', 'download.php/' . rawurlencode($blob->name) . '?', Request::get()->getFullUrl()));
			exit();
		}

		$inline = !empty($_GET['inline']);

		// prevent html to render on same domain having access to all global JS stuff
		if($blob->type == 'text/html') {
			$inline = false;
		}

		$blob->output($inline);
		exit();
	}

//Blob used for routing to a download method in the module file if we get here.

	$parts = explode("/", $_GET['blob']);

	$package = array_shift($parts);
	if ($package == "core") {
		$c = GO();
		$method = "download" . array_shift($parts);
	} else {
		$module = array_shift($parts);
		$method = "download" . array_shift($parts);
		//left over are params

		$ctrlCls = "go\\modules\\" . $package . "\\" . $module . "\\Module";
		if (!class_exists($ctrlCls)) {
			Response::get()->setStatus(404);
			Response::get()->output("Class '$ctrlCls' not found");
			exit();
		}

		$c = $ctrlCls::get();
	}

	if (!method_exists($c, $method)) {
		Response::get()->setStatus(404);
		Response::get()->output("Controller method '$method' not found in '$ctrlCls'");
		exit();
	} else {
		Response::get()->sendHeaders();
	}

	call_user_func_array([$c, $method], $parts);
} catch(Exception $e) {
	Response::get()->setContentType('text/plain');
	ErrorHandler::logException($e);
	Response::get()->setStatus(500);
	Response::get()->output($e->getMessage());

	if(go()->getDebugger()->enabled) {
		go()->getDebugger()->printEntries();
	}
}