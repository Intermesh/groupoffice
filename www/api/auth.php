<?php
require("../vendor/autoload.php");

use go\core\App;
use go\core\auth\Method;
use go\core\ErrorHandler;
use go\core\jmap\State;
use go\core\model\AuthAllowGroup;
use go\core\model\Token;
use go\core\model\User;
use go\core\model\Log;
use go\core\auth\PrimaryAuthenticator;
use go\core\jmap\Request;
use go\core\http\Response;
use go\core\jmap\Capabilities;
use go\core\validate\ErrorCode;

/**
 * @param array $data
 * @param int $status
 * @param null $statusMsg
 * @throws Exception
 */
function output($data = [], $status = 200, $statusMsg = null) {
	Response::get()->setStatus($status, $statusMsg);
	Response::get()->sendHeaders();

	go()->getDebugger()->groupEnd();
	$data['debug'] = go()->getDebugger()->getEntries();
	
	//var_dump($data);
	
	$json = json_encode($data);
	if(!$json) {
		throw new Exception("Failed to encode JSON: " . json_last_error_msg());
	}	
	Response::get()->output($json);

	exit();
}
	

try {
//Create the app with the config.php file
	App::get();

	go()->getDebugger()->group("auth");
	
	if(Request::get()->getMethod() == "DELETE") {
		$state = new go\core\jmap\State();
		$token = $state->getToken();
		if(!$token) {
			output([], 404);
		}
		if(go()->getModule(null,'log')) {
			$oUser = User::findById($token->userId);
			$oLog = new Log();
			$oLog->setValues(['user_id' => $oUser->id, 'message' => $oUser->username, 'action' => $oLog::ACTION_LOGOUT,
				'controller_route' => 'auth', 'model' => '', 'model_id' => $oUser->id,
				'username' => $oUser->username]);
			$oLog->save();

		}

		$token->oldLogout();
		Token::delete($token->primaryKeyValues());
		
		output();		
	}

	if (!Request::get()->isJson()) {
		output([], 400, "Only Content-Type: application/json");	
	}

	$data = Request::get()->getBody();

	Response::get()->setContentType("application/json;charset=utf-8");

	if (isset($data['forgot'])) {

		$user = User::find()->where(['email' => $data['email']])->orWhere(['recoveryEmail' => $data['email']])->single();
		if (empty($user)) {
			go()->debug("User not found");
			output([], 200, "Recovery mail sent");	//Don't show if user was found or not for security
		}
		
		if(!($user->getPrimaryAuthenticator() instanceof \go\core\auth\Password)) {
			go()->debug("Authenticator doesn't support recovery");
			output([], 200, "Recovery mail sent");	
		}
		$user->sendRecoveryMail($data['email']);
		output([], 200, "Recovery mail sent");	
	}
	if (isset($data['recover'])) {
		$oneHourAgo = (new DateTime())->modify('-1 hour');
		$user = User::find()
										->where('recoveryHash = :hash AND recoverySendAt > :time')
										->bind([
												':hash' => $data['hash'],
												':time' => $oneHourAgo->format(DateTime::ISO8601)
										])->single();
		if (empty($user)) {
			$response = ['success' => false];
		} elseif (!empty($data['newPassword'])) {
			$user->setPassword($data['newPassword']);
			$user->checkRecoveryHash($data['hash']);
			$success = $user->save();
			$response = ['passwordChanged' => $success, 'validationErrors' => $user->getValidationErrors()];
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


	function getToken($data) {		
		//loop through all auth methods
		$authMethods = Method::find()->orderBy(['sortOrder' => 'DESC']);
		foreach ($authMethods as $method) {
			$authenticator = $method->getAuthenticator();
			if (!($authenticator instanceof PrimaryAuthenticator)) {
				continue;
			}
			if (!$authenticator->isAvailableFor($data['username'])) {
				continue;
			}

			go()->log("Trying: " . get_class($authenticator));
			if (!$user = $authenticator->authenticate($data['username'], $data['password'])) {
				go()->log("failed");
				return false;
			}

			go()->log("success");
			
			if(!$user->enabled) {				
				output([], 403, go()->t("You're account has been disabled."));
			}

			$ip = Request::get()->getRemoteIpAddress();
			if(!AuthAllowGroup::isAllowed($user, $ip)) {
        output([], 403, str_replace('{ip}', $ip, go()->t("You are not allowed to login from IP address {ip}.") ));
      }
			
			if(go()->getSettings()->maintenanceMode && !$user->isAdmin()) {
				output([], 503, go()->t("Service unavailable. Maintenance mode is enabled."));
			}

			$token = new Token();
			$token->userId = $user->id;
			$token->addPassedMethod($method);

			if (!$token->save()) {
				throw new Exception("Could not save token");
			}

			if(go()->getModule(null, 'log')){
				$oLog = new \go\core\model\Log();
				$oLog->setValues(['user_id' => $user->id, 'message' => $user->username, 'action' => $oLog::ACTION_LOGIN,
					'controller_route' => 'auth', 'model' => get_class($authenticator), 'model_id' => $user->id,
					'username' => 'notloggedin']);
				$oLog->save();
			}

			return $token;
		}
		return false;
	}

	

	if (!isset($data['loginToken']) && !isset($data['accessToken']) && !empty($data['username'])) {

		$token = getToken($data);
		if (!$token) {
			output([
					'errors' => [
							'username' => ["description" => "Bad username or password", "code" => ErrorCode::INVALID_INPUT]
					]
							], 401, "Bad username or password");
		}
	} else {
		if (isset($data['accessToken'])) {
			$token = Token::find()->where(['accessToken' => $data['accessToken']])->single();
			if ($token && $token->isAuthenticated()) {
				$token->refresh();
			}
		} else if(isset($data['loginToken'])){
			$token = Token::find()->where(['loginToken' => $data['loginToken']])->single();
		} else {
      output(["error" => "Invalid token given"], 400, "No token given");
    }

		if (!$token) {
			output(["error" => "Invalid token given"], 400, "Invalid token given");
		}
	}


// Do the actual authentication for each authentication method where from its data is posted
	if (!empty($data['methods'])) {
		$authenticators = $token->authenticateMethods($data['methods']);
	} else {
		$authenticators = [];
	}

	$methods = array_map(function($o) {
		return $o->id;
	}, $token->getPendingAuthenticationMethods());

	if (empty($methods) && !$token->isAuthenticated()) {
		$token->setAuthenticated();
		if(!$token->save()) {
			throw new Exception("Could not save token: ". var_export($token->getValidationErrors(), true));
		}
	}

	if ($token->isAuthenticated()) {
    $authState = new State();
    $authState->setToken($token);
		go()->setAuthState($authState);
    $response = $authState->getSession();
    
		$response['accessToken'] = $token->accessToken;
		
		//Server side cookie worked better on safari. Client side cookies were removed on reboot.
		$expires = !empty($data['rememberLogin']) ? strtotime("+1 year") : 0;
		setcookie('accessToken', $token->accessToken, $expires, "/", Request::get()->getHost(), false, false);
		
		output($response, 201, "Authentication is complete, access token created.");
	} 
  
  $response = [
    'loginToken' => $token->loginToken,
    'methods' => $methods
  ];

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
		output($response, 400, "Validation errors occurred");
	} else {
		go()->debug($methods);
		output($response, 200, "Success, but more authorization required.");
	}
} catch (Exception $e) {
  ErrorHandler::logException($e);
	output([], 500, $e->getMessage());
}
