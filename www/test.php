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

$types = \go\core\orm\EntityType::findAll();

foreach($types as $type) {
	echo $type->getName() . ' : ' . $type->getId() . "\n";
}
