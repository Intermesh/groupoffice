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
use go\core\jmap\State;

if(!App::get()->setAuthState(new State())->getAuthState()->isAuthenticated()) {
	http_response_code(401);
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
		http_response_code(404);	
		exit("Controller class '$ctrlCls' not found");
	}
	
	$c = $ctrlCls::get();
}



if(!method_exists($c, $method)) {
	http_response_code(404);	
	exit("Controller method '$method' not found");
}

call_user_func_array([$c, $method], $parts);