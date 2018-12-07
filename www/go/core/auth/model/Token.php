<?php
namespace go\core\auth\model;

use DateInterval;
use go\core\App;
use go\core\Environment;
use go\core\auth\Method;
use go\core\orm\Entity;
use go\core\orm\Mapping;
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
	 * 
	 * @var int
	 */							
	public $userId;

	/**
	 * Time this token expires. Defaults to one day after the token was created {@see LIFETIME}
	 * @var DateTime
	 */							
	public $expiresAt;
	
	/**
	 *
	 * @var DateTime
	 */
	public $createdAt;
	
	/**
	 *
	 * When the user was last active. Updated every 5 minutes.
	 * 
	 * @var DateTime
	 */
	public $lastActiveAt;

	/**
	 * The remote IP address of the client connecting to the server
	 * 
	 * @var string 
	 */
	public $remoteIpAddress;
	
	/**
	 * The user agent sent by the client
	 * 
	 * @var string 
	 */
	public $userAgent;
	
	/**
	 * | separated list of "core_auth" id's that are successfully applied 
	 * for this token 
	 * Example: (password,googleauth)
	 * @var string 
	 */
	protected $passedMethods;
	
	/**
	 * A date interval for the lifetime of a token
	 * 
	 * @link http://php.net/manual/en/dateinterval.construct.php
	 */
	const LIFETIME = 'P7D';
	
	/**
	 * A date interval for the login lifetime of a token
	 * 
	 * @link http://php.net/manual/en/dateinterval.construct.php
	 */
	const LOGIN_LIFETIME = 'PT10M';
	
	protected static function defineMapping() {
		return parent::defineMapping()
		->addTable('core_auth_token');
	}
	
	protected function init() {
		parent::init();
		
		if($this->isNew()) {	
			$this->lastActiveAt = new \DateTime();
			$this->setClient();
			$this->setLoginToken();
//			$this->internalRefresh();
		}else if($this->isAuthenticated ()){
			
			$this->oldLogin();
			
			if($this->lastActiveAt < new \DateTime("-5 mins")) {
				$this->lastActiveAt = new \DateTime();				
				
				//also refresh token
				$this->setExpiryDate();
				$this->internalSave();
			}
		}
	}
	
	/**
	 * Set an authentication method to completed and add it to the 
	 * "completedAuth" property
	 * 
	 * @param int $authId
	 * @param boolean $lastAuth
	 * @return boolean save success
	 */
	public function authCompleted($authId, $lastAuth=false){
		$auths = explode(',',$this->completedAuth);
		$auths[] = $authId;
		$this->completedAuth = implode(',',$auths);
		
		if($lastAuth){
			return $this->refresh();
		}
	
		return $this->save();
	}
	
	private function setClient() {
		if(isset($_SERVER['REMOTE_ADDR'])) {
			$this->remoteIpAddress = $_SERVER['REMOTE_ADDR'];
		}

		if(isset($_SERVER['HTTP_USER_AGENT'])) {
			$this->userAgent = $_SERVER['HTTP_USER_AGENT'];
		}else if(Environment::get()->isCli()) {
			$this->userAgent = 'cli';
		} else {
			$this->userAgent = 'Unknown';
		}
	}
	
	private static function generateToken(){
		return uniqid().bin2hex(openssl_random_pseudo_bytes(16));
	}

	/**
	 * Check if the token is expired.
	 * 
	 * @return boolean
	 */
	public function isExpired(){
		
		return $this->expiresAt < new DateTime();
	}
		
	private function internalRefresh() {
		if(!isset($this->accessToken)) {
			$this->accessToken = $this->generateToken();
		}
		
		$this->setExpiryDate();
	}
	
	public function setLoginToken() {
		$this->loginToken = $this->generateToken();
		$this->setLoginExpiryDate();
	}
	
	/**
	 * Set new tokens and expiry date
	 * 
	 * @return Boolean
	 */
	public function refresh() {
		
		$this->internalRefresh();
		
		return $this->save();
	}
	
	private function setExpiryDate() {
		$expireDate = new DateTime();
		$expireDate->add(new DateInterval(Token::LIFETIME));
		$this->expiresAt = $expireDate;		
	}
	
	private function setLoginExpiryDate() {
		$expireDate = new DateTime();
		$expireDate->add(new DateInterval(Token::LOGIN_LIFETIME));
		$this->expiresAt = $expireDate;		
	}

	private $user;

	/**
	 * Get the user this token belongs to
	 * 
	 * @return User
	 */
	public function getUser() {
		if(!$this->user) {
			$this->user = \go\modules\core\users\model\User::findById($this->userId);
		}
		return $this->user;
	}
	
	/**
	 * Authenticate this token
	 * 
	 * @return boolean success
	 */
	public function setAuthenticated(){
		
		$user = $this->getUser();
		$user->lastLogin = new DateTime();
		$user->loginCount++;
		if(!$user->save()) {
			return false;
		}
		
		if(!$this->refresh()) {
			return false;
		}
		
		// For backwards compatibility, set the server session for the old code
		$this->oldLogin();
		
		// Create accessToken and set expire time
		return true;						
	}
	
	/**
	 * Check if this token is authenticated
	 * 
	 * @return bool
	 */
	public function isAuthenticated() {
		return isset($this->accessToken) && !$this->isExpired();
	}
	
	/**
	 * Login function for the old GO6.2 environment.
	 * Session based
	 * @deprecated since version 6.3
	 * 
	 */
	private function oldLogin(){
		
		if(\go\core\Environment::get()->isCli()){
			return;
		}
		
    if (session_status() == PHP_SESSION_NONE) {
			
      //without cookie_httponly the cookie can be accessed by malicious scripts 
      //injected to the site and its value can be stolen. Any information stored in 
      //session tokens may be stolen and used later for identity theft or
      //user impersonation.
      ini_set("session.cookie_httponly",1);

      //Avoid session id in url's to prevent session hijacking.
      ini_set('session.use_only_cookies',1);

      if(\go\core\http\Request::get()->isHttps()) {
        ini_set('session.cookie_secure',1);
      }

      session_name('groupoffice');
      session_start();
    }
		
		if(!isset($_SESSION['GO_SESSION'])) {
				$_SESSION['GO_SESSION'] = [];
			}			
		$_SESSION['GO_SESSION']['user_id'] = $this->userId;
		$_SESSION['GO_SESSION']['accessToken'] = $this->accessToken;			
		
		
	}
	
	private function oldLogout() {
		$this->oldLogin();
		session_destroy();
	}
	
	protected function internalDelete() {
		
		
		$this->oldLogout();
		return parent::internalDelete();
	}
	
	/**
	 * Add the given method to the passed method list.
	 * 
	 * @param Method $method
	 * @return boolean
	 */
	public function addPassedMethod(Method $method){
		$method = $method->id;
		$methods = $this->getPassedMethods();
		
		if(!in_array($method,$methods)){
			$methods[] = $method;
		
			$this->passedMethods = trim(implode('|',$methods),'|');
		}
		return true;
	}
	
	/**
	 * Set the given methods as passed
	 * 
	 * @param string[] $methods
	 * @return boolean
	 */
	public function setPassedMethods($methods){
		$this->passedMethods = trim(implode('|',$methods),'|');
		return $this->save();
	}
	
	/**
	 * Get the list of passed methods from the table column
	 * 
	 * @return string[]
	 */
	public function getPassedMethods(){
		return explode('|',$this->passedMethods);
	}
	
	/**
	 * 
	 * @return Method[]
	 */
	public function getPendingAuthenticationMethods(){
		
		$pending = [];
		
		$authMethods = $this->getUser()->getAuthenticationMethods();
		$finishedAuthMethods = $this->getPassedMethods(); // array('password','googleauthenticator');
		
		foreach($authMethods as $authMethod){
			if(!in_array($authMethod->id,$finishedAuthMethods)){
				$pending[] = $authMethod;
			}	
		}
		
		return $pending;
	}
	
	/**
	 * Authenticate with the given authentication methods.
	 * First checks every method, then determine if login is successful by 
	 * checking if all needed login methods are passed.
	 * 
	 * @param array $methods
	 * @return boolean
	 */
	public function authenticateMethods(array $methods) {
		
		$authenticators = [];		
		$authMethods = $this->getPendingAuthenticationMethods();
		$pending = false;
		foreach($authMethods as $authMethod){
			if(!in_array($authMethod->id, array_keys($methods))) {
				$pending = true;
				continue;
			}
			$authenticator = $authMethod->getAuthenticator();
			$authenticators[$authMethod->id] = $authenticator;
			if($authenticator->authenticate($this, $methods[$authMethod->id])){
				$this->addPassedMethod($authMethod);
			} else
			{
				$pending = true;
			}
		}

		return $authenticators;
	}
	
}
