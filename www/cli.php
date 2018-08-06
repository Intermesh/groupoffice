<?php
ini_set("display_errors", 1);
error_reporting(E_ALL);

use go\core\App;
use go\core\cli\Args;
use go\core\cli\State;
use go\core\Environment;

require(__DIR__ . "/vendor/autoload.php");


try {
	App::get()->setAuthState(new State());
	if(!Environment::get()->isCli()) {
		throw new Exception("You can only run this script on the Command Line Interface");
	}
	
	$router = new \go\core\cli\Router();
	$router->run();
	
}catch (\Exception $e) {
	echo "Error: ". $e->getMessage() ."\n\n";
}