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
use go\core\jmap\State;
use go\core\util\StringUtil;

require("../vendor/autoload.php");
App::get();

/**
 * DRY wrapper for XSS stuff
 *
 * If it looks ugly but it works, it works.
 *
 * @param string $s : string to check for bad XSS stuff
 * @throws Exception
 */
function doDetectXss(string $s)
{
	if (StringUtil::detectXSS($s)) {
		http_response_code(400);
		exit("Bad request");
	}
}


if(strpos($_SERVER['PATH_INFO'], '/') === false) {

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
doDetectXss($package);

if($package == "core") {
	$c = go();
	$method = "page" . array_shift($parts);
} else {
	$module = array_shift($parts);
	doDetectXss($module);
	$method = "page" . array_shift($parts);
	//left over are params

	$ctrlCls = "go\\modules\\" . $package . "\\". $module . "\\Module";
	if(!class_exists($ctrlCls)) {
		http_response_code(404);	
		exit("Controller class '$ctrlCls' not found");
	}
	
	$c = $ctrlCls::get();
}

doDetectXss($method);

if(!method_exists($c, $method)) {
	http_response_code(404);	
	exit("Controller method '$method' not found");
}

call_user_func_array([$c, $method], $parts);