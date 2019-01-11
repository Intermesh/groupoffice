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
require(__DIR__ . "/vendor/autoload.php");

use go\core\App;
use go\core\fs\Blob;
use go\core\jmap\State;

if(!App::get()->setAuthState(new State())->getAuthState()->isAuthenticated()) {
	http_response_code(403);	
	exit("Unauthorized.");
}

if(!isset($_GET['blob'])) {
	http_response_code(400);	
	exit("Bad request. Param 'blob' must be given.");
}

if(strpos($_GET['blob'], '/') === false) {
	$blob = Blob::findById($_GET['blob']);
	if (!$blob) {
		echo "Not found";
		http_response_code(404);
		exit();
	}

	$blob->output();	
	exit();
}

//Blob used for routing to a DownloadController if we get here.

$parts = explode("/", $_GET['blob']);

$package = array_shift($parts);
$module = array_shift($parts);
$method = "download" . array_shift($parts);
//left over are params

$ctrlCls = "go\\modules\\" . $package . "\\". $module . "\\Module";

if(!class_exists($ctrlCls)) {
	http_response_code(404);	
	exit("Controller class '$ctrlCls' not found");
}


$c = new $ctrlCls;

if(!method_exists($c, $method)) {
	http_response_code(404);	
	exit("Controller method '$method' not found");
}

call_user_func_array([$c, $method], $parts);