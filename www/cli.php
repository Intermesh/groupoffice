<?php
ini_set("display_errors", 1);
error_reporting(E_ALL);

use go\core\App;
use go\core\cache\None;
use go\core\cli\Router;
use go\core\cli\State;
use go\core\Environment;

require(__DIR__ . "/vendor/autoload.php");


try {
	App::get()->setAuthState(new State());
	//no cache
	GO()->setCache(new None());
	
	if(!Environment::get()->isCli()) {
		throw new Exception("You can only run this script on the Command Line Interface");
	}
	
	$router = new Router();
	$router->run();
	
}catch (Exception $e) {
	echo "Error: ". $e->getMessage() ."\n\n";
	
	if(GO()->getDebugger()->enabled) {
		echo "\n\nDEBUGGER:\n\n";
		echo implode("\n", GO()->getDebugger()->getEntries()) ."\n\n";
	}
}