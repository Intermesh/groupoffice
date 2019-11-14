<?php
use go\core\fs\Blob;
use go\core\App;
use go\core\jmap\State;

require("../vendor/autoload.php");
App::get();

if(strpos($_GET['blob'], '/') === false) {

  $blob = Blob::findById(App::get()->getSettings()->logoId);

  if (!$blob) {
    echo "Not found";
    http_response_code(404);
    exit();
  }

  $blob->output();	
}

$parts = explode("/", $_GET['blob']);

$package = array_shift($parts);
if($package == "core") {
	$c = go();
	$method = "page" . array_shift($parts);
} else {
	$module = array_shift($parts);
	$method = "page" . array_shift($parts);
	//left over are params

	$ctrlCls = "go\\modules\\" . $package . "\\". $module . "\\Module";
	if(!class_exists($ctrlCls)) {
		http_response_code(404);	
		exit("Controller class '$ctrlCls' not found");
	}
	
	$c = new $ctrlCls;
}

if(!method_exists($c, $method)) {
	http_response_code(404);	
	exit("Controller method '$method' not found");
}

call_user_func_array([$c, $method], $parts);