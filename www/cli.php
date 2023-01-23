#!/usr/bin/env php
<?php
use go\core\App;
use go\core\cache\None;
use go\core\cli\Router;
use go\core\cli\State;
use go\core\Environment;

require(__DIR__ . "/vendor/autoload.php");

$router = new Router();

$args = $router->parseArgs();

if(isset($args['c'])) {
	define('GO_CONFIG_FILE', $args['c']);
}

App::get()->setAuthState(new State());
//no cache 
//go()->setCache(new None());

if(!Environment::get()->isCli()) {
	throw new Exception("You can only run this script on the Command Line Interface");
}

go()->getDebugger()->setRequestId('cli');
if(!empty($args['debug'])) {
    go()->getDebugger()->output = true;
	go()->getDebugger()->enable(false);
}

if(array_key_exists('debug', $args)) {
    go()->getDebugger()->enabled = !empty($args['debug']);
}

if(array_key_exists('debugSql', $args)) {
	go()->getDbConnection()->debug = !empty($args['debugSql']);
}

$router = new Router();
$router->run();
