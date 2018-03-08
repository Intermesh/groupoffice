<?php

ini_set('error_reporting', E_ALL); 
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

use go\core\App;
use go\core\cli\State;

$autoLoader = require(__DIR__ . "/../www/vendor/autoload.php");
$autoLoader->add('go\\', __DIR__);

try {
	App::get()->setAuthState(new State());
} catch (Exception $e) {
	echo $e;
	throw $e;
}