<?php
use go\core\App;
use go\core\jmap\Router;
use go\core\jmap\State;

require(__DIR__ . "/vendor/autoload.php");

//Create the app with the database connection
App::get()->setAuthState(new State());

$router = new Router();
$router->run();
