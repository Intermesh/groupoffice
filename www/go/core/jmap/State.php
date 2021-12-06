<?php
namespace go\core\jmap;

use Exception;
use GO\Base\Model\State as OldState;
use go\core\ErrorHandler;
use go\core\model\Module;
use go\core\model\Token;
use go\core\auth\State as AbstractState;
use go\core\http\Response;
use go\core\model\User;
use PDO;
use stdClass;

class State extends AbstractState {
	
	private static function getFromHeader() {
		
		$auth = Request::get()->getHeader('Authorization');
		if(!$auth) {
			return false;
		}
		preg_match('/Bearer (.*)/', $auth, $matches);
		if(!isset($matches[1])){
			return false;
		}
		
		return $matches[1];
	}
	
	private static function getFromCookie() {
//		if(Request::get()->getMethod() != "GET") {
//			return false;
//		}
		
		if(!isset($_COOKIE['accessToken'])) {
			return false;
		}
		return $_COOKIE['accessToken'];
	}

	/**
	 * Gets' the access token from the Authorizaion header or Cookie
	 */
	public static function getClientAccessToken() {
		$tokenStr = static::getFromHeader();
		if(!$tokenStr) {
			$tokenStr = static::getFromCookie();
		}

		return $tokenStr;
	}
	
	/**	
	 *
	 * @var Token 
	 */
	private $token;

	/**
	 * Get the authorization token by reading the request header "Authorization"
	 *
	 * @return boolean|Token
	 */
	public function getToken() {
		
		if(!isset($this->token)) {
						
			$tokenStr = $this->getFromHeader();
			if(!$tokenStr) {
				$tokenStr = $this->getFromCookie();
			}

			if(!$tokenStr) {
				return false;
			}

			$this->token = go()->getCache()->get('token-' . $tokenStr);
			if($this->token) {
				$this->token->activity();
				return $this->token;
			}
		
			$this->token = Token::find()->where(['accessToken' => $tokenStr])->single();
			
			if(!$this->token) {
				return false;
			}		

			if($this->token->isExpired()) {
				try {
					$this->token->delete($this->token->primaryKeyValues());
				} catch(Exception $e) {
					ErrorHandler::logException($e);
				}
				$this->token = false;
			} else{
				go()->getCache()->set('token-' . $tokenStr, $this->token);
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
		$token->userId = $userId;
		$success = $token->setAuthenticated();

		go()->getCache()->delete('token-' . $token->accessToken);
		go()->getCache()->delete('session-' . $token->accessToken);
		
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
			Response::get()->setStatus(401);
			Response::get()->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
			Response::get()->setHeader('Pragma', 'no-cache');

			Response::get()->output([
					"auth" => [
							"domains" => User::getAuthenticationDomains()
					]
			]);
		} else
		{
			Response::get()->output($this->getSession());
		}
	}

	/** @noinspection HttpUrlsUsage */
	protected function getBaseUrl(): string
	{
		$url = Request::get()->isHttps() ? 'https://' : 'http://';
		$url .= Request::get()->getHost(false) . dirname($_SERVER['PHP_SELF']);
		return $url;
	}

	public function getPageUrl(): string
	{
		return $this->getBaseUrl(). "/page.php";
	}
	
	public function getApiUrl(): string
	{
		return $this->getBaseUrl() . '/jmap.php';
	}
	
	public function getUploadUrl(): string
	{
		return $this->getBaseUrl(). '/upload.php';
	}
	
	public function getEventSourceUrl(): ?string
	{
		return go()->getConfig()['sseEnabled'] ? $this->getBaseUrl() . '/sse.php' : null;
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

		return $response;
	}

	private function addModuleCapabilities(array $response) {
		$modules = Module::getInstalled();
		$groupedRights = "SELECT moduleId, BIT_OR(rights) as rights FROM core_permission WHERE groupId IN (SELECT groupId from core_user_group WHERE userId = ".go()->getAuthState()->getUserId().") GROUP BY moduleId;";
		$rights = go()->getDbConnection()->query($groupedRights)->fetchAll(PDO::FETCH_KEY_PAIR);
		foreach ($modules as $module) {
			if(go()->getAuthState()->isAdmin()) {
				$p = $module->may(PHP_INT_MAX);
			} else if(isset($rights[$module->id])) {
				$p = $module->may($rights[$module->id]);
			}
			if(!empty($p)) {
				$response['capabilities']->{'go:' . ($module->package ?? 'legacy') . ':' . $module->name} = $p;
			}
		}
	}
	
//	private function clientSettings(): array
//	{
//		$user = GO::user();
//		return [
//			'state' => OldState::model()->getFullClientState($user->id)
//			,'user_id' => $user->id
//			,'avatarId' => $user->avatarId
//			,'has_admin_permission' => $user->isAdmin()
//			,'username' => $user->username
//			,'displayName' => $user->displayName
//			,'email' => $user->email
//			,'thousands_separator' => $user->thousandsSeparator
//			,'decimal_separator' => $user->decimalSeparator
//			,'date_format' => $user->completeDateFormat
//			,'time_format' => $user->timeFormat
//			,'currency' => $user->currency
//			,'lastlogin' => $user->getLastlogin()
//			,'max_rows_list' => $user->max_rows_list
//			,'timezone' => $user->timezone
//			,'start_module' => $user->start_module
//			,'theme' => $user->theme
//			,'mute_sound' => $user->mute_sound
//			,'mute_reminder_sound' => $user->mute_reminder_sound
//			,'mute_new_mail_sound' => $user->mute_new_mail_sound
//			,'popup_reminders' => $user->popup_reminders
//			,'popup_emails' => $user->popup_emails
//			,'show_smilies' => $user->show_smilies
//			,'auto_punctuation' => $user->auto_punctuation
//			,'first_weekday' => $user->firstWeekday
//			,'sort_name' => $user->sort_name
//			,'list_separator' => $user->listSeparator
//			,'text_separator' => $user->textSeparator
//			,'modules' => GO::view()->exportModules()
//		];
//	}

	/**
	 * Get the user ID
	 * @return int|null
	 */
	public function getUserId(): ?int
	{
		return $this->getToken() ? $this->getToken()->userId : null;
	}

	/**
	 * Get the logged in user
	 *
	 * @param array $properties the properties to fetch
	 * @return User|null
	 * @throws Exception
	 * @throws Exception
	 * @throws Exception
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
