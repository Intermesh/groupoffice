<?php

require(__DIR__ . "/vendor/autoload.php");

use go\core\App;
use go\core\auth\BaseAuthenticator;
use go\core\auth\model\Token;
use go\core\auth\model\User;
use go\core\http\Request;
use go\core\http\Response;
use go\core\jmap\Capabilities;

//Create the app with the config.php file
App::get(); // Initializes App

if (!Request::get()->isJson()) {
	Response::get()->setStatus(400, "Only Content-Type: application/json");
	Response::get()->sendHeaders();
	exit();
}

$data = Request::get()->getBody();

Response::get()->setContentType("application/json;charset=utf-8");

if (isset($data['forgot'])) {

	$user = User::find()->where(['email' => $data['email']])->orWhere(['recoveryEmail' => $data['email']])->single();
	if (empty($user)) {
		exit();
	}
	$user->sendRecoveryMail($data['email']);
	exit(); // no response given
}
if (isset($data['recover'])) {
	$oneHourAgo = (new DateTime())->modify('-1 hour');
	$user = User::find()
									->where('recoveryHash = :hash AND recoverySendAt > :time')
									->bind([
											':hash' => $data['hash'],
											':time' => $oneHourAgo->format(\DateTime::ISO8601)
									])->single();
	if (empty($user)) {
		$response = ['success' => false];
	} elseif (!empty($data['newPassword'])) {
		$user->setPassword($data['newPassword']);
		$user->checkRecoveryHash($data['hash']);
		$response = ['passwordChanged' => $user->save()];
	} else {
		$response = [
				"username" => $user->username,
				"displayName" => $user->displayName
		];
	}
	Response::get()->sendHeaders();
	Response::get()->output(json_encode($response));
	exit();
}

if (!isset($data['loginToken']) && !isset($data['accessToken']) && !empty($data['username'])) {
	
	if(!empty($data['password'])) {
		//easy short hand post for the client. In the future we might require something else then a password.
		$data['methods'] = ['password' => ['password' => $data['password']]];
	}	
	
	if(!isset($data['methods']['password'])) {
		Response::get()->setStatus(400, "Password is required");
		Response::get()->sendHeaders();
		exit();
	}
	
	// The username is posted, we need to return the possible authenticators
	$username = $data['username'];
	$token = User::login($username);

	if (!$token) {
		Response::get()->setStatus(403, "Bad username or password");
		Response::get()->sendHeaders();
		Response::get()->output(json_encode([
				'errors' => [
						'username' => ["description" => "Bad username or password", "code" => go\core\validate\ErrorCode::INVALID_INPUT]
				]
		]));
		exit();
	}
} else {

	if (isset($data['accessToken'])) {
		$token = Token::find()->where(['accessToken' => $data['accessToken']])->single();
		if($token && $token->isAuthenticated()) {
			$token->refresh();
		}
	} else {
		$token = Token::find()->where(['loginToken' => $data['loginToken']])->single();
	}

	if (!$token) {
		Response::get()->setStatus(400, "Invalid token given");
		Response::get()->sendHeaders();
		exit();
	}
}


// Do the actual authentication for each authentication method where from its data is posted
if (!empty($data['methods'])) {
	$authenticators = $token->authenticateMethods($data['methods']);
}

$methods = array_map(function($o) {
	return $o->id;
}, $token->getPendingAuthenticationMethods());

$response = [
		'loginToken' => $token->loginToken,
		'methods' => $methods
];

if ($token->isAuthenticated()) {
	$response['accessToken'] = $token->accessToken;
	$response['capabilities'] = Capabilities::get();
	$response['user'] = $token->getUser()->toArray();
	Response::get()->setStatus(201, "Authentication is complete, access token created.");
	Response::get()->sendHeaders();
	Response::get()->output(json_encode($response));
	exit();
} 


$methods = array_map(function($o) {
	return $o->id;
}, $token->getPendingAuthenticationMethods());

$validationErrors = [];
foreach ($authenticators as $methodId => $authenticator) {
	$errors = $authenticator->getValidationErrors();
	if (!empty($errors)) {
		$validationErrors[$methodId] = $errors;
	}
}

if (!empty($validationErrors)) {
	$response['errors'] = $validationErrors;
	Response::get()->setStatus(400, "Errors occurred");
} else
{
	Response::get()->setStatus(200, "Success, but more authorization required.");
}


Response::get()->sendHeaders();
Response::get()->output(json_encode($response));
