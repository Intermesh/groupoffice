<?php

namespace go\core\auth;

use go\core\App;
use go\core\ErrorHandler;
use go\core\jmap\State;
use go\core\model\AuthAllowGroup;
use go\core\model\Token;
use go\core\model\User;
use go\core\model\Log;
use go\core\jmap\Request;
use go\core\http\Response;
use go\core\validate\ErrorCode;

/**
 * Class Authenticate
 *
 * This is a helper class that should always be used to authenticate a user
 * It will be used for webclient, sync client, webdav client
 */
class Authenticate {

	const STATUS_SUCCESS = 1;
	const STATUS_FAILED = 2;
	const STATUS_DISABLED = 3;
	const STATUS_IP_BLOCK = 4;
	const STATUS_MAINTENANCE = 5;
	const STATUS_ERROR = 6;

	private static $statLabels = [
		self::STATUS_SUCCESS => "Login successfull",
		self::STATUS_FAILED => "Login failed",
		self::STATUS_DISABLED => "You're account has been disabled.",
		self::STATUS_IP_BLOCK => "You are not allowed to login from IP address {ip}.",
		self::STATUS_MAINTENANCE => "Service unavailable. Maintenance mode is enabled.",
		self::STATUS_ERROR => "Could not save token"
	];

	/**
	 * Used by clients that only support simple authentication (dav / activesync)
	 * TODO: below code is from dav/activesync but should use login() function
	 *
	 * @param string $username
	 * @param string $password
	 * @return bool|User false or the logged in user
	 */
	static function passwordLogin($username, $password) {
		$user = User::find(['id', 'username', 'password', 'enabled'])->where(['username' => $username])->single();
		/* @var $user User */

		if(!$user || !$user->enabled) {
			return false;
		}

		if(!$user->checkPassword($password)) {
			return false;
		}

		$state = new TemporaryState();
		$state->setUserId($user->id);
		go()->setAuthState($state);

		if(!defined('GO_NO_SESSION')) {
			define("GO_NO_SESSION", true);
		}
		$_SESSION['GO_SESSION'] = ['user_id' => $user->id];

		return $user;
	}

	public function login($username, $password) {
		//loop through all auth methods
		$authMethods = Method::find()->orderBy(['sortOrder' => 'DESC']);
		foreach ($authMethods as $method) {
			$authenticator = $method->getAuthenticator();
			if (!($authenticator instanceof PrimaryAuthenticator)) {
				continue; // There should only be 1 primary authentication method if there are more we just use the first one.
			}
			if (!$authenticator->isAvailableFor($username)) {
				continue;
			}

			go()->log("Trying: " . get_class($authenticator));
			if (!$user = $authenticator->authenticate($username, $password)) {
				go()->log("failed");
				return self::STATUS_FAILED;
			}

			go()->log("success");

			if(!$user->enabled) {
				return self::STATUS_DISABLED;
				output([], 403, go()->t("You're account has been disabled."));
			}

			$ip = Request::get()->getRemoteIpAddress();
			if(!AuthAllowGroup::isAllowed($user, $ip)) {
				return self::STATUS_IP_BLOCK;
				output([], 403, str_replace('{ip}', $ip, go()->t("You are not allowed to login from IP address {ip}.") ));
			}

			if(go()->getSettings()->maintenanceMode && !$user->isAdmin()) {
				return self::STATUS_MAINTENANCE;
				output([], 503, go()->t("Service unavailable. Maintenance mode is enabled."));
			}

			return self::STATUS_SUCCESS;
		}
		return self::STATUS_FAILED;
	}

	public function sendRecoveryMail($email) {
		$user = User::find()->where(['email' => $email])->orWhere(['recoveryEmail' => $email])->single();
		if (empty($user)) {
			go()->debug("User not found");
			return false;
		}
		if(!($user->getPrimaryAuthenticator() instanceof \go\core\auth\Password)) {
			go()->debug("Authenticator doesn't support recovery");
			return false;
		}
		$user->sendRecoveryMail($email);
		return true;
	}

	public function recovery($hash) {
		$oneHourAgo = (new DateTime())->modify('-1 hour');
		$user = User::find()
			->where('recoveryHash = :hash AND recoverySendAt > :time')
			->bind([
				':hash' => $hash,
				':time' => $oneHourAgo->format(DateTime::ISO8601)
			])->single();
		if (empty($user)) {
			return false;
		}
		return $user;
	}

	public function logout() {
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
	}

	public function refreshToken($accessToken) {
		$token = Token::find()->where(['accessToken' => $accessToken])->single();
		if ($token && $token->isAuthenticated()) {
			$token->refresh();
		}
		return $token;
	}

}


/// ######################################################
/// EVERYTHING BELOW GOES INTO auth.php FILE WHEN FINISHED
/// ######################################################

try {
//Create the app with the config.php file
	App::get();
	go()->getDebugger()->group("auth");
	$auth = new Authenticate();
	if(Request::get()->getMethod() == "DELETE") {
		$auth->logout()
		output();
	}

	if (!Request::get()->isJson()) {
		output([], 400, "Only Content-Type: application/json");
	}

	$data = Request::get()->getBody();

	Response::get()->setContentType("application/json;charset=utf-8");

	if (isset($data['forgot'])) {
		$auth->sendRecoveryMail($data['email'])
		output([], 200, "Recovery mail sent");	 //Don't show if user was found or not for security
	}

	if (isset($data['recover'])) {
		$user = $auth->recovery($data['hash']);
		if(empty($user)) {
			output(['success' => false]);
		}
		if(!empty($data['newPassword'])) {
			$user->setPassword($data['newPassword']);
			//$user->checkRecoveryHash($data['hash']); // already checked by recovery()
			output(['passwordChanged' => $user->save(), 'validationErrors' => $user->getValidationErrors()]);
		}
		output(["username" => $user->username, "displayName" => $user->displayName]);
	}

	if (!isset($data['loginToken']) && !isset($data['accessToken']) && !empty($data['username'])) {
		$status = $auth->login($data['username'],$data['password']);
		$token = getToken($data);
		if ($status !== Authenticate::STATUS_SUCCESS) {
			output([
				'errors' => [
					'username' => ["description" => "Bad username or password", "code" => ErrorCode::INVALID_INPUT]
				]
			], 401, "Bad username or password");
		}
	} else {
		if (isset($data['accessToken'])) {
			$token = $auth->refreshToken($data['accessToken']);
		} else if(isset($data['loginToken'])){
			$token = Token::find()->where(['loginToken' => $data['loginToken']])->single();
		} else {
			output(["error" => "Invalid token given"], 400, "No token given");
		}

		if (!$token) {
			output(["error" => "Invalid token given"], 400, "Invalid token given");
		}
	}

	$auth->validateSecondaryAuthMethods($token, $data['methods']);
	// Do the actual authentication for each authentication method where from its data is posted
	//                         THIS WILL DO AUTHENTICATION ---v
	$authenticators = empty($data['methods']) ? [] : $token->authenticateMethods($data['methods']);

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


		Response::get()->setCookie('accessToken', $token->accessToken, [
			'expires' => $expires,
			"path" => "/",
			"samesite" => "Lax",
			"domain" => Request::get()->getHost()
		]);

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
