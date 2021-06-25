<?php
require("../vendor/autoload.php");

use go\core\App;
use go\core\auth\Authenticate;
use go\core\ErrorHandler;
use go\core\exception\Forbidden;
use go\core\exception\Unavailable;
use go\core\jmap\State;
use go\core\model\Token;
use go\core\jmap\Request;
use go\core\http\Response;
use go\core\model\User;
use go\core\validate\ErrorCode;

/**
 * @param array $data
 * @param int $status
 * @param null $statusMsg
 * @throws Exception
 */
function output($data = [], $status = 200, $statusMsg = null) {

	Response::get()->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
	Response::get()->setHeader('Pragma', 'no-cache');

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

function finishLogin(Token $token) {
	$authState = new State();
	$authState->setToken($token);
	go()->setAuthState($authState);
	$response = $authState->getSession();

	$response['accessToken'] = $token->accessToken;

	$token->setCookie();

	output($response, 201, "Authentication is complete, access token created.");
}


try {
//Create the app with the config.php file
	App::get()->setAuthState(new State());
	go()->getDebugger()->group("auth");
	$auth = new Authenticate();

	if (Request::get()->getMethod() == "DELETE") {
		$data = ['action' => 'logout'];
	} else {
		if (!Request::get()->isJson()) {
			output([], 400, "Only Content-Type: application/json");
		}

		$data = Request::get()->getBody();

		if (!isset($data['action'])) {
			$data['action'] = 'login';
		}
		Response::get()->setContentType("application/json;charset=utf-8");
	}

	switch ($data['action']) {
		case 'forgotten':
			$auth->sendRecoveryMail($data['email']);
			//Don't show if user was found or not for security
			output([], 200, "Recovery mail sent");
			break;

		case 'recover':
			$user = $auth->recovery($data['hash']);
			if (empty($user)) {
				output(['success' => false]);
			}
			if (!empty($data['newPassword'])) {
				$user->setPassword($data['newPassword']);
				//$user->checkRecoveryHash($data['hash']); // already checked by recovery()
				output(['passwordChanged' => $user->save(), 'validationErrors' => $user->getValidationErrors()]);
			}
			output(["username" => $user->username, "displayName" => $user->displayName]);
			break;

		case 'logout':
			$auth->logout();
			output();
			break;

		default:

			if (isset($data['accessToken'])) {
				$token = Token::find()->where(['accessToken' => $data['accessToken']])->single();
				if ($token && $token->isAuthenticated()) {
					$token->refresh();
				}
			} else if (isset($data['loginToken'])) {
				$token = Token::find()->where(['loginToken' => $data['loginToken']])->single();
			} else {

				if (empty($data['username']) || empty($data['password'])) {
					$msg = "Missing arguments 'username' and 'password' for authenticatoin.";
					output(["error" => $msg], 400, $msg);
				}

				$user = $auth->passwordLogin($data['username'], $data['password']);
				if (!$user) {
					output([
						'errors' => [
							'username' => ["description" => "Bad username or password", "code" => ErrorCode::INVALID_INPUT]
						]
					], 401, "Bad username or password");
				}

				$token = new Token();
				$token->userId = $user->id;
				$token->addPassedAuthenticator($auth->getUsedPasswordAuthenticator());

				if (!$token->save()) {
					throw new Exception("Could not save token");
				}
			}

			if (!$token) {
				output(["error" => "Invalid token given"], 400, "Invalid token given");
			}

			//for backwards compatibility. Assistant uses this.
			if (!isset($data['authenticators']) && isset($data['methods'])) {
				$data['authenticators'] = $data['methods'];
			}

			$testedAuthenticators = $token->validateSecondaryAuthenticators($data['authenticators'] ?? []);

			$authenticators = array_map(function ($o) {
				return $o::id();
			}, $token->getPendingAuthenticators());

			if (empty($authenticators) && !$token->isAuthenticated()) {
				$token->setAuthenticated();
			}

			if (!$token->save()) {
				throw new Exception("Could not save token: " . var_export($token->getValidationErrors(), true));
			}

			if ($token->isAuthenticated()) {
				if($data['rememberLogin']) {
					$rememberMe = new \go\core\model\RememberMe();
					$rememberMe->userId = $token->userId;
					if(!$rememberMe->save()) {
						throw new \go\core\orm\exception\SaveException($rememberMe);
					}
					$rememberMe->setCookie();
				}

				finishLogin($token);
			}

			$response = [
				'loginToken' => $token->loginToken,
				'authenticators' => $authenticators
			];


			$validationErrors = [];
			foreach ($testedAuthenticators as $authenticator) {
				$errors = $authenticator->getValidationErrors();
				if (!empty($errors)) {
					$validationErrors[$authenticator::id()] = $errors;
				} else if (in_array($authenticator::id(), $authenticators)) {
					//no validation errors set but failed. Return a default error
					$validationErrors[$authenticator::id()] = [$authenticator::id() . ' ' . go()->t('failed')];
				}
			}


			if (!empty($validationErrors)) {
				$user = $token->getUser();
				User::fireEvent(User::EVENT_BADLOGIN, $user->username, $user);

				$response['errors'] = $validationErrors;
				output($response, 400, "Validation errors occurred");
			} else {
				go()->debug($authenticators);
				output($response, 200, "Success, but more authorization required.");
			}

			break;
	}
}catch(Forbidden $e) {
	output([], 403, $e->getMessage());
} catch (Unavailable $e) {
	output([], 503, $e->getMessage());
} catch (Exception $e) {
	ErrorHandler::logException($e);
	output([], 500, $e->getMessage());
}
