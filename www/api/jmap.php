<?php
use go\core\App;
use go\core\jmap\Router;
use go\core\jmap\Request;
use go\core\jmap\State;

require("../vendor/autoload.php");

//Create the app with the database connection
App::get()->setAuthState(new State());

switch(Request::get()->getMethod() ) {

	case 'POST':
	$router = new Router();
	$router->run();

	break;

	case 'GET':

		App::get()->getAuthState()->outputSession();

		break;

	case 'OPTIONS':
		\go\core\jmap\Response::get()->sendHeaders();
		break;

	default:

	throw new Exception("Method " . Request::get()->getMethod() . " not supported");
}
