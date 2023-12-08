<?php

use go\core\App;
use go\core\http\Request;
use go\core\http\Response;
use go\core\http\Router;
use go\core\jmap\State;
use go\core\model\Token;
use go\modules\community\oauth2client\controller\Oauth2Client;

$dir = dirname(__FILE__, 5) . DIRECTORY_SEPARATOR;
require($dir . 'GO.php');

/**
 * Get user entity from current state or token param
 */
$state = new State();
//not possible with oauth flow.
State::$CSRFcheck = false;

$router = (new Router())
	->addRoute('/authenticate\/([0-9]+)/', 'GET', Oauth2Client::class, 'auth')
	->addRoute('/callback/', "GET", Oauth2Client::class, 'callback')
	->addRoute('/openid\/([0-9]+)/', "GET", Oauth2Client::class, 'openid')
	->run();