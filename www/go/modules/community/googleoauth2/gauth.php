<?php
//require('../../../../vendor/autoload.php');
use go\core\App;
use go\core\http\Request;
use go\core\http\Response;
use go\core\jmap\State;
use go\core\http\Router;
use go\modules\community\googleoauth2\controller\Oauth2Account;


$dir =  dirname(__FILE__, 5) . DIRECTORY_SEPARATOR;
require($dir . 'GO.php');

/**
 * Get user entity from current state or token param
 */
$state = new State();
$tokenStr = Request::get()->getQueryParam('token');
if ($tokenStr) {
	$tokenStrParts = explode(';', $tokenStr);
	$accessToken = $tokenStrParts[0];

	$token = Token::find()->where(['accessToken' => $accessToken])->single();
	if($token) {
		$state->setToken($token);
	} else{
		\go\core\ErrorHandler::log("OnlyOffice: Access token '" . $accessToken . "' not found!");
	}
}

/**
 * Validate user's state
 */
App::get()->setAuthState($state);
if (!App::get()->getAuthState()->isAuthenticated()) {
	Response::get()->setStatus(401, 'Unauthorized');
	throw new \go\core\http\Exception(401);
}


$router = (new Router())
	->addRoute('/authenticate\/([0-9]+)/', 'GET', Oauth2Account::class, 'auth')
	->addRoute('/callback/', "GET", Oauth2Account::class, 'callback')
	->run();