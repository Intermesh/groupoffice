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
use go\core\fs\Blob;
use go\core\jmap\Response;
use go\core\jmap\State;
use go\core\jmap\Request;

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


if(strpos($_GET['blob'], '/') === false) {
	$blob = Blob::findById($_GET['blob']);
	if (!$blob) {

		Response::get()->setStatus(404);
		Response::get()->output("Not found");
		exit();
	}

	$blob->output(!empty($_GET['inline']));	
	exit();
}

//Blob used for routing to a download method in the module file if we get here.

$parts = explode("/", $_GET['blob']);

$package = array_shift($parts);
if($package == "core") {
	$c = GO();
	$method = "download" . array_shift($parts);
} else {
	$module = array_shift($parts);
	$method = "download" . array_shift($parts);
	//left over are params

	$ctrlCls = "go\\modules\\" . $package . "\\". $module . "\\Module";
	if(!class_exists($ctrlCls)) {
		Response::get()->setStatus(404);
		Response::get()->output("Controller class '$ctrlCls' not found");
		exit();
	}
	
	$c = $ctrlCls::get();
}

if(!method_exists($c, $method)) {
	Response::get()->setStatus(404);
	Response::get()->output("Controller method '$method' not found");
	exit();
}

call_user_func_array([$c, $method], $parts);