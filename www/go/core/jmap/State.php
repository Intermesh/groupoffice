<?php
namespace go\core\jmap;

use Exception;
use GO\Base\Model\State as OldState;
use go\core\ErrorHandler;
use go\core\exception\Forbidden;
use go\core\http\Response as HttpResponse;
use go\core\model\Module;
use go\core\model\Token;
use go\core\auth\State as AbstractState;
use go\core\model\User;
use stdClass;

class State extends AbstractState {
	
	private static function getFromHeader() : ?string {
		
		$auth = Request::get()->getHeader('Authorization');
		if(!$auth) {
			return null;
		}
		preg_match('/Bearer (.*)/', $auth, $matches);
		if(!isset($matches[1])){
			return null;
		}
		
		return $matches[1];
	}
	
	private static function getFromCookie() : ?string
	{
		if(isset($_COOKIE['accessToken'])) {
			return $_COOKIE['accessToken'];
		}

		return null;
	}

	/**
	 * Gets the access token from the Authorizaion header or Cookie
	 */
	public static function getClientAccessToken(): ?string
	{
		$tokenStr = static::getFromCookie();

		if(!$tokenStr) {
			$tokenStr = static::getFromHeader();
			self::$headerAccessTokenUsed = !empty($tokenStr);
		} else{
			self::$cookieAccessTokenUsed = true;
		}

		return $tokenStr;
	}

	private static $cookieAccessTokenUsed = false;

	private static $headerAccessTokenUsed = false;

	/**
	 * Check if cookies were used to authenticate
	 * @return bool
	 * @throws Exception
	 */
	public function isAuthenticatedUsingCookie() : bool {
		return $this->isAuthenticated() && self::$cookieAccessTokenUsed;
	}

	/**
	 * Check if the "Authorization: Bearer" header was used to authenticate
	 *
	 * Such requests can't be made by other websites, so they don't need a CSRF token.
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function isAuthenticatedUsingHeader() : bool {
		return $this->isAuthenticated() && self::$headerAccessTokenUsed && !self::$cookieAccessTokenUsed;
	}


	/**
	 * Check CSRF token on each POST request.
	 * @var bool
	 */
	public static $CSRFcheck = true;

	/**
	 * A disabled user can't use a token. Checked when the token is loaded from the database, so it's cached for
	 * {@see Token::CACHE_TTL} at most.
	 */
	private static function isUserEnabled(Token $token): bool
	{
		$user = User::find(['id', 'enabled'], true)->where(['id' => $token->userId])->single();

		return $user && $user->enabled;
	}

	/**
	 * @throws Forbidden
	 */
	private static function checkCSRF(Token $token) : bool {

		if(!self::$CSRFcheck || !self::$cookieAccessTokenUsed || $_SERVER['REQUEST_METHOD'] == 'GET') {
			return true;
		}

		// if cookie is used then we must also check the CSRF token
		$csrfToken = Request::get()->getHeader('X-CSRF-Token') ?: $_REQUEST['CSRFToken'] ?? null;
		if(!$csrfToken) {
			go()->debug("'X-CSRF-Token' header or 'CSRFToken' request parameter missing");
			return false;
		}

		if($csrfToken != $token->CSRFToken) {
			go()->debug("CSRFToken mismatch");
			return false;
		}

		return true;
	}
	
	/**	
	 *
	 * @var Token 
	 */
	private $token;

	public function hasToken(): bool
	{
		return !!$this->token;
	}

	/**
	 * Get the authorization token by reading the request header "Authorization"
	 *
	 * @return boolean|Token
	 * @throws Exception
	 */
	public function getToken() {
		
		if(!isset($this->token)) {
						
			$tokenStr = self::getClientAccessToken();

			if(!$tokenStr) {
				return false;
			}

			$this->token = go()->getCache()->get('token-' . $tokenStr);
			if($this->token) {
				if($this->token->isExpired()) {
					// The cache can outlive the database record (eg. removed by garbage collection). Never use or extend it.
					go()->getCache()->delete('token-' . $tokenStr);
					$this->token = null;
				} else {
					if(!self::checkCSRF($this->token)) {
						$this->token = null;
						return false;
					}
					$this->token->activity();
					return $this->token;
				}
			}

			$this->token = Token::find()->where(['accessToken' => $tokenStr])->single();
			
			if(!$this->token) {
				return false;
			}

			if($this->token->isExpired()) {
				try {
					$this->token->delete($this->token->primaryKeyValues());
					Token::unsetCookie();
				} catch(Exception $e) {
					ErrorHandler::logException($e);
				}
				$this->token = false;
			} else if(!self::isUserEnabled($this->token)) {
				// Not deleted: tokens that never expire (API keys) must work again when the user is enabled again.
				$this->token = false;
			} else{
				if(!self::checkCSRF($this->token)) {
					$this->token = null;
					return false;
				}
				go()->getCache()->set('token-' . $tokenStr, $this->token, true, Token::CACHE_TTL);
			}
		}
		
		return $this->token;
	}

	public function setToken(Token $token) {
		$this->token = $token;
	}

	/**
	 * Change authenticated user to somebody else.
	 *
	 * @param int $userId
	 * @return bool
	 * @throws Exception
	 */
	public function changeUser(int $userId): bool
	{
		$token = $this->getToken();
		$oldAccessToken = $token->accessToken;
		$token->userId = $userId;
		$token->clientId = null;
		// New tokens for the other user, so the tokens of the administrator's session can't be used for it
		$token->accessToken = null;
		$token->CSRFToken = null;
		$success = $token->setAuthenticated(false);

		go()->getCache()->delete('token-' . $oldAccessToken);
		go()->getCache()->delete('session-' . $oldAccessToken);
		if($success && self::$cookieAccessTokenUsed) {
			$token->setCookie();
		}
		
		//for old framework
		$_SESSION['GO_SESSION'] = array_filter($_SESSION['GO_SESSION'], function($key) {
			return in_array($key, ['user_id', 'accessToken', 'security_token']);
		}, ARRAY_FILTER_USE_KEY); 

		return $success;
	}

	/**
	 * @throws Exception
	 */
	public function isAuthenticated(): bool
	{
		return $this->getToken() !== false;
	}

	/**
	 * Return the JMAP session data.
	 * Called when the user makes an authenticated GET request
	 * @throws Exception
	 */
	public function outputSession() {		
		
		if (!$this->isAuthenticated()) {
			//use http/Response here because we don't want JMAP request output here
			HttpResponse::get()->setStatus(401);
			HttpResponse::get()->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
			HttpResponse::get()->setHeader('Pragma', 'no-cache');

			HttpResponse::get()->output([
					"auth" => [
							"domains" => User::getAuthenticationDomains()
					]
			]);
		} else
		{
			HttpResponse::get()->output($this->getSession());
		}
	}

	/**
	 *
	 */
	public function getSession(): array
	{
		$response = [
			'version' => go()->getVersion(),
			'cacheClearedAt' => go()->getSettings()->cacheClearedAt,
			// 'username' => $user->username,
			'accounts' => ['1'=> [
				'name'=>'Virtual',
				'isPrimary' => true,
				'isReadOnly' => false,
				'hasDataFor' => []
			]],
			"auth" => [
						"domains" => User::getAuthenticationDomains()
			],
			'capabilities' => Capabilities::get(),
			'apiUrl' => $this->getApiUrl(),
			'downloadUrl' => $this->getDownloadUrl("{blobId}"),
			'pageUrl' => $this->getPageUrl(),
			'uploadUrl' => $this->getUploadUrl(),
			'eventSourceUrl' => $this->getEventSourceUrl(),
			'userId' => $this->getUserId(),
		];
		$this->addModuleCapabilities($response);

		//todo optimize
		$response['state'] = OldState::model()->getFullClientState($this->getUserId());

		$response['CSRFToken'] = $this->getToken()->CSRFToken;

		return $response;
	}

	private function addModuleCapabilities(array $response) {
		$modules = Module::getInstalled();
		foreach ($modules as $module) {
			$p = $module->getUserRights();

			if($p->mayRead) {
				$response['capabilities']->{'go:' . ($module->package ?? 'legacy') . ':' . $module->name} = $p;
			}
		}
	}

	/**
	 * Get the user ID
	 * @return string|null
	 */
	public function getUserId(): ?string
	{
		return $this->getToken() ? $this->getToken()->userId : null;
	}

	/**
	 * Get the logged in user
	 *
	 * @param array $properties the properties to fetch
	 * @return User|null
	 * @throws Exception
	 */
	public function getUser(array $properties = []): ?User
	{
		return $this->getToken() ? $this->getToken()->getUser($properties) : null;
	}

	/**
	 * Check if logged in user is admin
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function isAdmin(): bool
	{
		if($this->getUserId() == User::ID_SUPER_ADMIN) {
			return true;
		}

		$user = $this->getUser(['id']);
		if(!$user) {
			return false;
		}
		return $user->isAdmin();
	}


	/**
	 * Get the permission level of the module this controller belongs to.
	 *
	 * @param string $cls
	 * @return stdClass For example ['mayRead' => true, 'mayManage'=> true, 'mayHaveSuperCowPowers' => true]
	 * @throws Exception
	 */
	public function getClassRights($cls): stdClass
	{
		return $this->getToken()->getClassRights($cls);
	}

}
