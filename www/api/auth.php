<?php
require("../vendor/autoload.php");

use go\core\App;
use go\core\auth\Authenticate;
use go\core\ErrorHandler;
use go\core\exception\Forbidden;
use go\core\exception\Unavailable;
use go\core\jmap\State;
use go\core\model\RememberMe;
use go\core\model\Token;
use go\core\jmap\Request;
use go\core\http\Response;
use go\core\model\User;
use go\core\orm\exception\SaveException;
use go\core\util\StringUtil;
use go\core\validate\ErrorCode;
use go\core\util\JSON;

/**
 * @param array $data
 * @param int $status
 * @param ?string $statusMsg
 */
function output(array $data = [], int $status = 200, string|null $statusMsg = null) {

	Response::get()->setHeader('Content-Type', 'application/json;charset=utf-8');
	Response::get()->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
	Response::get()->setHeader('Pragma', 'no-cache');
	Response::get()->setHeader('Expires', '01-07-2003 12:00:00 GMT');

	Response::get()->setStatus($status, $statusMsg ? str_replace("\n", " - " , $statusMsg) : null);
	Response::get()->sendHeaders();

	go()->getDebugger()->groupEnd();
	$data['debug'] = go()->getDebugger()->getEntries();

	try {
		$json = JSON::encode($data);
		Response::get()->output($json);
	} catch(Exception $e) {
		Response::get()->setStatus(500, get_class($e));

		echo get_class($e);

		ErrorHandler::logException($e);
	}

	exit();
}

function finishLogin(Token $token, string|null $rememberMeToken = null) {
	$authState = new State();
	$authState->setToken($token);
	go()->setAuthState($authState);

	$token->setAuthenticated();

	$response = $authState->getSession();

	// Browsers should not store this for security. They must use the httpOnly flagged cookie that
	// can't be stolen. The CSRFToken must be used to prevent CSRF attacks.
	// @see Token::CSRFToken
	$response['accessToken'] = $token->accessToken;
	$response['CSRFToken'] = $token->CSRFToken;

	if($rememberMeToken != null) {
		$response['rememberMeToken'] = $rememberMeToken;
	}

	$token->setCookie();

	output($response, 201, "Authentication is complete, access token created.");
}


try {


//Create the app with the config.php file
	App::get();

	if (Request::get()->getMethod() == "OPTIONS") {
		output();
	}

	go()->getDebugger()->group("auth");

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

		// we don't want to use that here. Because otherwise the CSRFToken is needed too.
		unset($_COOKIE['accessToken']);
	}

	go()->setAuthState(new State());
	$auth = new Authenticate();



	switch ($data['action']) {

		case 'register':

			if(!go()->getSettings()->allowRegistration) {
				output([], 403, 'Registration is not allowed');
				exit();
			}

			$user = new User();
			$user->setValues($data['user']);
			if(!$user->save()) {
				throw new SaveException($user);
			}

			$token = new Token();
			$token->userId = $user->id;

			finishLogin($token);

			break;
		case 'forgotten':

			// Don't change log message as fail2ban relies on it
			ErrorHandler::log("Lost password request from IP: '" . Request::get()->getRemoteIpAddress() . "'");

			$start = (int) (microtime(true) * 1000);

			$auth->sendRecoveryMail($data['email']);

			//always take 4s to prevent timing attacks
			$wait = 4000 + $start - ((int) (microtime(true) * 1000));
			if($wait > 0) {
				usleep($wait * 1000);
			} else {
				ErrorHandler::log("Warning: sending lost password message took longer than 4s. Timing attack possible because of this. Make sure your SMTP is faster.");
			}
			//Don't show if user was found or not for security
			output([], 200, "Recovery mail sent");
			break;

		case 'recover':
			$user = $auth->recovery($data['hash']);
			if (empty($user)) {
				output(['success' => false]);
			}
			if (!empty($data['newPassword'])) {
				$user->setPassword($data['newPassword'], true);
				//$user->checkRecoveryHash($data['hash']); // already checked by recovery()
				output(['passwordChanged' => $user->save(), 'validationErrors' => $user->getValidationErrors()]);
			}
			output(["username" => $user->username, "displayName" => $user->displayName]);
			break;

		case 'logout':
			$auth->logout();
			output();
			break;

		case 'login':
		default:
			/** @var ?Token $token */
			$token = null;

			if (isset($data['accessToken'])) {
				$token = Token::find()->where(['accessToken' => $data['accessToken']])->single();
				if ($token && $token->isAuthenticated()) {
					$token->refresh();
				}
			} else if(isset($data['rememberMeToken'])) {
				// Process remember me persistent cookie. This is not used by the browser. The browser verifies the remember me token in index.php with a cookie.
				// The assistant uses this method.
				if(($rememberMe = RememberMe::verify($data['rememberMeToken']))) {
					$rememberMe->setCookie();

					$token = new Token();
					$token->userId = $rememberMe->userId;


					finishLogin($token, $rememberMe->getToken());
				} else {
					output(["error" => "Invalid remember me token"], 400, "Invalid remember me token");
				}
			} elseif (isset($data['loginToken'])) {
				$token = Token::find()->where(['loginToken' => $data['loginToken']])->single();
			} else {

				if (empty($data['username']) || empty($data['password'])) {
					$msg = "Missing arguments 'username' and 'password' for authentication.";
					output(["error" => $msg], 400, $msg);
				}

				//trim username as mysql doesn't care about trialing or leading spaces but other systems might like IMAP or LDAP.
				$data['username'] = trim($data['username']);

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
					throw new SaveException($token);
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

			$authenticated = empty($authenticators);

			if ($authenticated) {

				$rememberMeToken = null;
				if(!empty($data['rememberLogin'])) {
					$rememberMe = new RememberMe();
					$rememberMe->userId = $token->userId;
					if(!$rememberMe->save()) {
						throw new SaveException($rememberMe);
					}
					$rememberMe->setCookie();

					$rememberMeToken = $rememberMe->getToken();
				}

				finishLogin($token, $rememberMeToken);
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
} catch (Throwable $e) {
	ErrorHandler::logException($e);

	output([], 500, get_class($e));
}
