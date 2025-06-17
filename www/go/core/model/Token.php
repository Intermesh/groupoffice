<?php
namespace go\core\model;

use DateInterval;
use DateTimeZone;
use Exception;
use go\core\auth\BaseAuthenticator;
use go\core\auth\SecondaryAuthenticator;
use go\core\cron\GarbageCollection;
use go\core\Environment;
use go\core\ErrorHandler;
use go\core\jmap\State;
use go\core\orm\Mapping;
use stdClass;
use go\core\http\Request;
use go\core\http\Response;
use go\core\orm\Query;
use go\core\orm\Entity;
use go\core\util\DateTime;

class Token extends Entity {
	
	/**
	 * The token that identifies the user in the login process.
	 * @var string
	 */							
	public $loginToken;
	
	/**
	 * The token that identifies the user. Sent in HTTPOnly cookie.
	 * @var string
	 */							
	public $accessToken;

	/**
	 * Cross Site Request Forgery token
	 *
	 * It's checked on each request that is not a GET request and where the accessToken is sent as a cookie.
	 *
	 * It can be passed as a 'X-CSRF-Token' header or 'CSRFToken' parameter
	 *
	 * @var string
	 */
	public $CSRFToken;

	/**
	 * 
	 * @var int
	 */							
	public $userId;

	/**
	 * Time this token expires. Defaults to one day after the token was created {@see LIFETIME}
	 * @var ?DateTime
	 */							
	public $expiresAt;
	
	/**
	 *
	 * @var DateTime
	 */
	public $createdAt;

	/**
	 * FK to the core_client table
	 *
	 * @var int
	 */
	public $clientId;

	/**
	 *
	 * When the user was last active. Updated every 5 minutes.
	 * 
	 * @var DateTime
	 */
	public $lastActiveAt;

	/**
	 * | separated list of "core_auth" id's that are successfully applied 
	 * for this token 
	 * Example: (password,googleauth)
	 * @var string 
	 */
	protected $passedAuthenticators;
	
	/**
	 * A date interval for the lifetime of a token
	 *
	 * On each JMAP request the token's expiry time will be pushed with this interval forward in time.
	 * So a request within this life time will keep it alive.
	 * The client (browser) will keep it alive by using SSE or checking for updates every 2 minutes. When the
	 * client is closed the token will be cleaned up after this lifetime.
	 * 
	 * @link http://php.net/manual/en/dateinterval.construct.php
	 */
	const LIFETIME = 'P1D';
	
	/**
	 * A date interval for the login lifetime of a token
	 * 
	 * @link http://php.net/manual/en/dateinterval.construct.php
	 */
	const LOGIN_LIFETIME = 'PT10M';
	
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
		->addTable('core_auth_token', 'token');
	}

	/**
	 * @throws Exception
	 */
	protected function init() {
		parent::init();
		
		if($this->isNew()) {	
			$this->setExpiryDate();
			$this->lastActiveAt = new DateTime("now", new DateTimeZone("UTC"));
			$this->setLoginToken();
//			$this->internalRefresh();
		}else if($this->isAuthenticated ()) {
			
			$this->oldLogin();
			
			$this->activity();
		}
	}

	/**
	 * @throws Exception
	 */
	public function activity(): bool
	{
		if($this->lastActiveAt < new DateTime("-1 mins", new DateTimeZone("UTC"))) {
			$this->lastActiveAt = new DateTime("now", new DateTimeZone("UTC"));

			//also refresh token
			if(isset($this->expiresAt)) {
				$this->setExpiryDate();
			}
			$this->internalSave();

			go()->getCache()->set('token-' . $this->accessToken, $this);

			return true;
		}

		return false;
	}

	protected function internalSave(): bool
	{
		$this->setClient();
		return parent::internalSave();
	}

//	/**
//	 * Set an authentication method to completed and add it to the
//	 * "completedAuth" property
//	 *
//	 * @param int $authId
//	 * @param boolean $lastAuth
//	 * @return boolean save success
//	 * @throws Exception
//	 */
//	public function authCompleted($authId, $lastAuth=false): bool
//	{
//		$auths = explode(',',$this->completedAuth);
//		$auths[] = $authId;
//		$this->completedAuth = implode(',',$auths);
//
//		if($lastAuth){
//			return $this->refresh();
//		}
//
//		return $this->save();
//	}
//
	private function setClient() {

		if(empty($this->clientId)) {
			$user = $this->getUser();
			$client = $user->currentClient();
			if (!$client) {
				$client = new Client($user);
				$client->userId = $this->userId;
				$client->status = 'allowed';
			}
			$client->lastSeen = $this->lastActiveAt;
			if($client->save()) {
				$this->clientId = $client->id;
			} else {
				throw new Exception('Failed to save the client identity'.var_export($client->getValidationErrors(), true));
			}
		}
	}

	/**
	 * @throws Exception
	 */
	private static function generateToken(): string
	{
		return uniqid().bin2hex(random_bytes(16));
	}

	/**
	 * Check if the token is expired.
	 *
	 * @return boolean
	 * @throws Exception
	 */
	public function isExpired(): bool
	{

		if(!isset($this->expiresAt)) {
			return false;
		}
		
		return $this->expiresAt < new DateTime("now", new DateTimeZone("UTC"));
	}

	/**
	 * @throws Exception
	 */
	private function internalRefresh() {
		if(!isset($this->accessToken)) {
			$this->accessToken = $this->generateToken();
		}
		if(!isset($this->CSRFToken)) {
			$this->CSRFToken = $this->generateToken();
		}
		if(isset($this->expiresAt)) {
			$this->setExpiryDate();
		}
	}

	/**
	 * @throws Exception
	 */
	public function setLoginToken() {
		$this->loginToken = $this->generateToken();
		$this->setLoginExpiryDate();
	}

	/**
	 * Set new tokens and expiry date
	 *
	 * @return Boolean
	 * @throws Exception
	 */
	public function refresh(): bool
	{
		
		$this->internalRefresh();
		
		return $this->save();
	}

	/**
	 * @throws Exception
	 */
	private function setExpiryDate() {
		$expireDate = new DateTime("now", new DateTimeZone("UTC"));
		$expireDate->add(new DateInterval(Token::LIFETIME));
		$this->expiresAt = $expireDate;		
	}

	/**
	 * @throws Exception
	 */
	private function setLoginExpiryDate() {
		$expireDate = new DateTime("now", new DateTimeZone("UTC"));
		$expireDate->add(new DateInterval(Token::LOGIN_LIFETIME));
		$this->expiresAt = $expireDate;		
	}

	private $user;

	/**
	 * Get the user this token belongs to
	 * 
	 * @param array $properties the properties to fetch
	 * @return User
	 */
	public function getUser(array $properties = []): User
	{
		if(!empty($properties)) {
			return $this->user ?? User::findById($this->userId, $properties, false);
		}

		if(!$this->user) {
			$this->user = User::findById($this->userId, []);
		}
		return $this->user;
	}

	/**
	 * Authenticate this token
	 *
	 * @param bool $increaseLogins True if login must be counted and last login must be updated.
	 * @return bool success
	 * @throws Exception
	 */
	public function setAuthenticated(bool $increaseLogins = true): bool
	{
		
		$user = $this->getUser(['loginCount', 'lastLogin', 'language']);

		if(!$this->refresh()) {
			return false;
		}

		if($increaseLogins) {
			$user->lastLogin = new DateTime("now", new DateTimeZone("UTC"));
			$user->loginCount++;

			if (isset($_COOKIE['GO_LANGUAGE'])) {
				$user->language = $_COOKIE['GO_LANGUAGE'];
			}

			if (!$user->save()) {
				return false;
			}
		}
		
		// For backwards compatibility, set the server session for the old code
		$this->oldLogin();

		User::fireEvent(User::EVENT_LOGIN, $user);
		
		// Create accessToken and set expire time
		return true;						
	}

	/**
	 * Check if this token is authenticated
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function isAuthenticated(): bool
	{
		return isset($this->accessToken) && !$this->isExpired();
	}
	
	/**
	 * Login function for the old GO6.2 environment.
	 * Session based
	 * @deprecated since version 6.3
	 * 
	 */
	private function oldLogin(){
		
		if(Environment::get()->isCli() || basename($_SERVER['PHP_SELF']) == 'index.php') {
			return;
		}		
		
    if (session_status() == PHP_SESSION_NONE && !headers_sent()) {
      //without cookie_httponly the cookie can be accessed by malicious scripts 
      //injected to the site and its value can be stolen. Any information stored in 
      //session tokens may be stolen and used later for identity theft or
      //user impersonation.
      ini_set("session.cookie_httponly",1);

      //Avoid session id in url's to prevent session hijacking.
      ini_set('session.use_only_cookies',1);

      ini_set('session.cookie_secure', Request::get()->isHttps());
   
			session_name('groupoffice');
      session_start();
    }
		
		if(!isset($_SESSION['GO_SESSION'])) {
			$_SESSION['GO_SESSION'] = [];
		}			

		$_SESSION['GO_SESSION']['user_id'] = $this->userId;
		$_SESSION['GO_SESSION']['accessToken'] = $this->accessToken;
	}
	
	public function oldLogout() {
		$this->oldLogin();
		session_destroy();
	}
	
	/**
	 * Add the given method to the passed method list.
	 * 
	 * @param BaseAuthenticator $authenticator
	 * @return boolean
	 */
	public function addPassedAuthenticator(BaseAuthenticator $authenticator): bool
	{
		$id = $authenticator::id();
		$methods = $this->getPassedAuthenticators();
		
		if(!in_array($id,$methods)){
			$methods[] = $id;
		
			$this->passedAuthenticators = trim(implode('|',$methods),'|');
		}
		return true;
	}

	/**
	 * Set the given authenticator ID's as passed
	 *
	 * @param string[] $authenticators
	 * @return boolean
	 * @throws Exception
	 */
	public function setPassedAuthenticator(array $authenticators): bool
	{
		$this->passedAuthenticators = trim(implode('|',$authenticators),'|');
		return $this->save();
	}
	
	/**
	 * Get the list of passed authenticator ID's
	 * 
	 * @return string[]
	 */
	public function getPassedAuthenticators(): array
	{
		return empty($this->passedAuthenticators) ? [] : explode('|', $this->passedAuthenticators);
	}
	
	/**
	 * Get authenticators that need to be validated to login
	 *
	 * @return BaseAuthenticator[]
	 */
	public function getPendingAuthenticators(): array
	{
		
		$pending = [];
		
		$authenticators = $this->getUser(User::getMapping()->getColumnNames())->getAuthenticators();
		$finishedAuthMethods = $this->getPassedAuthenticators(); // array('password','googleauthenticator');
		
		foreach($authenticators as $authenticator){
			if(!in_array($authenticator::id(), $finishedAuthMethods)){
				$pending[] = $authenticator;
			}	
		}
		
		return $pending;
	}
	
	/**
	 * Authenticate with the given authentication methods.
	 * First checks every method, then determine if login is successful by 
	 * checking if all needed login methods are passed.
	 * 
	 * @param array $data
	 * @return SecondaryAuthenticator[]
	 */
	public function validateSecondaryAuthenticators(array $data): array
	{
		
		$response = [];
		$authenticators = $this->getPendingAuthenticators();

		foreach($authenticators as $authenticator){
			if(!in_array($authenticator::id(), array_keys($data))) {
				continue;
			}
			$response[] = $authenticator;
			if($authenticator->authenticate($this, $data[$authenticator::id()])){
				$this->addPassedAuthenticator($authenticator);
			}
		}

		return $response;
	}

	/**
	 * Called by GarbageCollection cron job
	 *
	 * @see GarbageCollection
	 * @return bool
	 * @throws Exception
	 */
	public static function collectGarbage(): bool
	{
		return static::delete(
			(new Query)
				->where('expiresAt', '!=', null)
				->andWhere('expiresAt', '<', new DateTime("now", new DateTimeZone("UTC"))));
	}

	/**
	 * @throws Exception
	 */
	protected static function internalDelete(Query $query): bool
	{
		$deleteQuery = self::find()->mergeWith($query)->selectSingleValue('accessToken') ;

		foreach($deleteQuery as $accessToken) {
			go()->debug("Delete token: " . $accessToken);
			go()->getCache()->delete('token-' . $accessToken);
		}

		return parent::internalDelete($query);
	}

	/**
	 * Get the permission level of the module this controller belongs to.
	 *
	 * @param class-string<Entity> $cls
	 * @return stdClass For example ['mayRead' => true, 'mayManage'=> true, 'mayHaveSuperCowPowers' => true]
	 * @throws Exception
	 * @todo: improve performance by cache rights per user?
	 */
	public function getClassRights(string $cls): stdClass
	{
		$mod = Module::findByClass($cls, ['id', 'name', 'package']);
		return  $mod->getUserRights();
	}


	/**
	 * Destroys all tokens except
	 *
	 * @return bool
	 * @throws Exception
	 */
	public static function logoutEveryoneButAdmins(): bool
	{
		$admins = (new Query)->select('userId')->from('core_user_group')->where('groupId', '=', Group::ID_ADMINS);
		$q = (new Query)
			->where('expiresAt', '!=', null)
			->where('userId', 'NOT IN ', $admins);

		ErrorHandler::log("Logout everyone but admins is used!");

		return self::delete($q) && RememberMe::delete($q);
	}

	/**
	 * Destroy all sessions for the current user except the current.
	 * Used on password change.
	 * @throws Exception
	 */
	public static function destroyOtherSessons(int|null $userId = null) : bool {

		$q = (new Query)
			->where('expiresAt', '!=', null)
			->where('userId', '=', $userId ?? go()->getUserId()
			);

		if(!RememberMe::delete($q)) {
			return false;
		}
		$authState = go()->getAuthState();
		if($authState instanceof State) {
			$token = $authState->getToken();
			if($token) {
				$q->andWhere('loginToken', '!=', $token->loginToken);
			}
			$q->andWhere('accessToken', '!=', null);
		}

		return self::delete($q);
	}

	public function setCookie() {
		Response::get()->setCookie('accessToken', $this->accessToken, [
			'expires' => 0,
			"path" => "/",
			"samesite" => "Lax", // strict not possible or oauth won't work as it will not send cookie on callback page
			"secure" => Request::get()->isHttps(),
			"domain" => Request::get()->getHost(),
			"httpOnly" => true
		]);
	}

	public static function unsetCookie() {
		Response::get()->setCookie('accessToken', "", [
			'expires' => time() - 3600,
			"path" => "/",
			"samesite" => "Lax", // strict not possible or oauth won't work as it will not send cookie on callback page
			"secure" => Request::get()->isHttps(),
			"domain" => Request::get()->getHost(),
			"httpOnly" => true
		]);
	}
	
}
