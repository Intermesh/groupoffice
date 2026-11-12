<?php
/**
 * Group-Office
 * 
 * Copyright Intermesh BV. 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @license AGPL/Proprietary http://www.group-office.com/LICENSE.TXT
 * @link http://www.group-office.com
 * @copyright Copyright Intermesh BV
 * @version $Id: Number.php 7962 2011-08-24 14:48:45Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package GO.base
 */

/**
 * Manage a Group-Office session
 * 
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: config.class.inc.php 7687 2011-06-23 12:00:34Z mschering $
 * @copyright Copyright Intermesh BV.
 * @package GO.base 
 */


namespace GO\Base;


use go\core\auth\State as AbstractState;
use go\core\ErrorHandler;
use go\core\jmap\State;
use go\core\model\Token;

class Session extends Observable{
	
	public $values;
	
	private $_user;

	public function __construct(){
		$this->start();
	}
	
	public function restart(){
    
		$this->values=['accessToken' => $this->values['accessToken']];
		$this->start();
	}
	
	public function start(){
		//start session
		
		//In some cases it doesn't make sense to use the session because the client is
		//not capable. (WebDAV for example).
		if(!defined("GO_NO_SESSION") && !$this->isActive() && !headers_sent()){		
			go()->sessionStart();
		}
		
		$this->values = &$_SESSION['GO_SESSION'];

		// A session user that was copied from an access token of the new framework only belongs to the client that
		// sends that access token. Otherwise somebody else's session could be given the user of the client.
		if (!empty($this->values['user_id']) && !empty($this->values['accessToken'])) {
			$clientAccessToken = $this->getCallerAccessToken();
			if (!is_string($clientAccessToken) || !hash_equals($this->values['accessToken'], $clientAccessToken)) {
				go()->debug("Removing the user from the session because the access token of the client doesn't match");
				$this->values = [];
			}
		}

		// if access token from new JMAP API connected to this session was destroyed then destroy this session too!
		// this is set in go/core/model/Token.php
		try {
			if (!empty($this->values['user_id']) && !empty($this->values['accessToken']) && !go()->getCache()->get('token-' . $this->values['accessToken']) && !Token::find()->where('accessToken', '=', $this->values['accessToken'])->single()) {
				ErrorHandler::log("Destroying session because the access token of the session was not found");
				$this->values = [];
			}
		} catch (\Exception $e) {
			$this->values = [];
			ErrorHandler::logException($e, "Destroying session because of db exception");
		}

		// copy auth from new framework
		if(empty($this->values['user_id'])) {
			$this->copyUserFromAuthState(go()->getAuthState());
		}
		
		if(!isset($this->values['security_token'])){
			
			//this log here causes endless loop and segfaults
			//$this->_log("security_token");
			$this->values['security_token']=Util\StringHelper::randomPassword(20,'a-z,A-Z,1-9');				
		}
	}

	/**
	 * The access token of this request
	 *
	 * The token of the auth state when the server set it, for example in the login request or a remember me login.
	 * Otherwise the token that the client sent in the cookie or the Authorization header.
	 *
	 * @return string|null
	 */
	private function getCallerAccessToken(): ?string
	{
		$state = go()->getAuthState();
		if ($state instanceof State && $state->hasToken() && ($token = $state->getToken())) {
			return $token->accessToken;
		}
		return State::getClientAccessToken();
	}

	/**
	 * Use the user of the new framework in this session
	 *
	 * When the user comes from an access token, that access token is stored too. The user is only kept in the session
	 * while the client sends the same access token. {@see start()}
	 *
	 * @param AbstractState|null $state
	 * @return string|null The user ID or null when there's no authenticated user
	 */
	private function copyUserFromAuthState(?AbstractState $state): ?string
	{
		if (!$state) {
			return null;
		}

		$userId = $state->getUserId();
		if (empty($userId)) {
			return null;
		}

		$this->values['user_id'] = $userId;
		if ($state instanceof State && ($token = $state->getToken())) {
			$this->values['accessToken'] = $token->accessToken;
		}

		return $userId;
	}

	public function isActive() {
		return session_status() == PHP_SESSION_ACTIVE;
	}
	
	/**
	 * Return security token value that should be passed with each request.
	 * 
	 * eg. index.php?r=test&security_token=token
	 * 
	 * @return string
	 */
	public function securityToken(){
		return $this->values['security_token'];
	}
	
	/**
	 * Return session ID
	 * 
	 * @return string
	 */
	public function id(){
		return session_id();
	}
	
	/**
	 * Remove the cookies of the old "remember me" of the site module.
	 *
	 * These cookies contained the username and password and are no longer used to log in. This function is called in
	 * index.php of the site module.
	 *
	 * @return null
	 */
	public function loginWithCookies(){
		if(isset($_COOKIE['GO_UN']) || isset($_COOKIE['GO_PW'])) {
			$this->_unsetRemindLoginCookies();
		}
		return null;
	}
	
	/**
	 * Erases the temporary files directory for the currently logged on user. 
	 */
	public function clearUserTempFiles($recreate=true){
		if(\GO::user()){					
			\GO::config()->getTempFolder(false)->delete();
			if($recreate)
				\GO::config()->getTempFolder();
		}
	}
	
	private function _unsetRemindLoginCookies(){
		Util\Http::unsetCookie('GO_UN');
		Util\Http::unsetCookie('GO_PW');		
	}
	
	const USERNAME_NOTLOGGEDIN = 'notloggedin';
	
	/**
	 * Log the current user out.
	 *
	 * @access public
	 * @return void
	 */
	public function logout()
	{
		$username = \GO::user() ? \GO::user()->username : self::USERNAME_NOTLOGGEDIN;
		
		\GO::debug("Logout called for ".$username);
		

		$old_session = $_SESSION;
		$_SESSION=array();
		$this->values=&$_SESSION;
		
		if (ini_get("session.use_cookies") && !headers_sent()) {
			//rRemove session cookie. PHP does not remove this automatically.
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
		}
		
		if($this->isActive()) {
			session_destroy();
		}
		
		if(!headers_sent()){
			$this->_unsetRemindLoginCookies();
		}
		//start new session
		$this->start();

		$this->fireEvent('logout', array($old_session));
		
		if($username != self::USERNAME_NOTLOGGEDIN) {
			\GO::infolog("LOGOUT for user: \"".$username."\" from IP: ".(\go\core\http\Request::get()->getRemoteIpAddress() ?? 'unknown'));
		}
	}
	
	/**
	 * Get the logged in user
	 *
	 * @return Model\User The logged in user model
	 */
	public function user(){

		try {
			if(empty($this->values['user_id'])){
				// Check Bearer token before returning null
				$userId = $this->copyUserFromAuthState(go()->getAuthState() ?: new State());
				if(!empty($userId)) {
					return Model\User::model()->findByPk($userId, array(), true);
				}
				return null;
			}else {
				//also check if the user_id matches because \GO::session()->runAsRoot() may haver changed it.
				if(empty($this->_user) || $this->_user->id!=$this->values['user_id']){
					$this->_user = Model\User::model()->findByPk($this->values['user_id'], array(), true);
				}

				return $this->_user;
			}

		} catch(\PDOException $e) {
			// not installed?
			ErrorHandler::logException($e);
			$this->values['user_id'] = null;
			return null;
		}
	}
	
	/**
	 * 
	 * Check for a double login
	 * 
	 * @param User $user
	 * 
	 * @return boolean True if all is OK, False if double login is detected
	 */
	private function _checkClientFootPrint($user){

		if(\GO::config()->use_single_login){

			$client = \GO\Base\Model\Client::lookup($user->id);

			if($client && !$client->checkLoggedInOnOtherLocation()){
				return true;
			}

			return false;
		}
		
		return true;
	}
	
	/**
	 * Logs a user in.
	 * 
	 * @param string $username
	 * @param string $password
	 * @param Boolean $countLogin
	 * 
	 * @return Model\User or false on failure.
	 */
	public function login($username, $password, $countLogin=true) {
		
		if(!$this->fireEvent('beforelogin', array($username, $password, $countLogin)))
			return false;			
		
		$user = Model\User::model()->findSingleByAttribute('username', $username);

		$success=true;
		
		if (!$user){
			\GO::debug("LOGIN: User ".$username." not found");
			$success=false;
		}elseif(!$user->enabled){
			\GO::debug("LOGIN: User ".$username." is disabled");
			$success=false;
		}elseif(!$user->checkPassword($password)){
			\GO::debug("LOGIN: Incorrect password for ".$username);
			$success=false;
		}elseif($countLogin && !$this->_checkClientFootPrint($user)){
			throw new \GO\Base\Exception\OtherLoginLocation();
		}elseif($countLogin && $user->checkPasswordChangeRequired()){
			throw new \GO\Base\Exception\PasswordNeedsChange();
		}
				
		// Implemented to authenticate with more authenticators
		if($success && !$this->fireEvent('passwordvalidated', array($username, $password, $user, $countLogin))) {
			$success = false;
		}		
		
		$str = "LOGIN ";		
		$str .= $success ? "SUCCESS" : "FAILED" ;		
		$str .= " for user: \"" . $username . "\" from IP: ";
		$str .= \go\core\http\Request::get()->getRemoteIpAddress() ?? 'unknown';
		\GO::infolog($str);
		\GO::debug($str);
		
		if(!$success){
			return false;
		}else
		{			
			$this->_user=$user;
			$this->setCurrentUser($user->id);
	
			if($countLogin){
				$user->lastlogin=time();
				$user->logins++;
				$user->save(true);
				
				$this->clearUserTempFiles();
			}
			
			//Set authentication for new framework
			\go()->setAuthState((new \go\core\auth\TemporaryState())->setUserId($user->id));

			$this->fireEvent('login', array($username, $password, $user, $countLogin));
			
			//A PHP variable named “session.use_only_cookies” controls the behaviour
			//of session_start(). When this variable is enabled (true) then session_start() on-
			//ly uses the cookies of a request for retrieving the session ID. If this variable is disa-
			//bled, then GET or POST requests can contain the session ID and can be used for
			//session fixation. This PHP variable was added in PHP 4.3.0 but is enabled by default
			//only since PHP 5.3.0. Environments with previous PHP versions, as well as non-
			//default PHP configurations are vulnerable to the session fixation attack described in
			//this finding if further measures are not taken.
			//In addition to only accepting session IDs in the form of cookies, the application
			//should force the re-generation of session IDs upon successful user authentication.
			//This way, an attacker would not be able to create a session ID that will be reused by
			//the application to identify a valid authenticated session. This is possible in PHP by
			//using the session_regenerate_id() function.

			if(PHP_SAPI!='cli' && $this->isActive()) {
				session_regenerate_id();
			}
			
		\GO::session()->values['countLogin']=$countLogin;
			

			
			return $user;
		}		
	}
	

	
	/**
	 * Close writing to session so other concurrent requests won't be blocked.
	 * When a PHP session is open the webserver won't process a new request until 
	 * the session is closed again.
	 */
	public function closeWriting(){	
		\GO::debug("Session writing closed");
		session_write_close();
	}
	
	/**
	 * Run the current action as root. This function will close session writing to prevent
	 * the user becoming root permanently. So you can't set session variables.
	 */
	public function runAsRoot(){
		self::runAs(1);
	}
	
	/**
	 * Run the current action as another user. This function will close session writing to prevent
	 * the user becoming root permanently. So you can't set session variables.
	 */
	public function runAs($id){

		\GO::session()->closeWriting();
		
		//Close session writing so that the user won't stay root in browser sessions.
		if(!isset($this->values['user_id']) || $id!=$this->values['user_id']){
			$debug = !empty(\GO::session()->values['debug']);
			$debugSql = !empty(\GO::session()->values['debugSql']);

			
			\GO::session()->values=array('debug'=>$debug, 'debugSql'=>$debugSql);
			\GO::session()->setCurrentUser($id);
		}
	}
	
	/**
	 * Sets current user for the entire session. Use it wisely!
	 * @param int/Model\User $user_id
	 * @param int $originalUserId  Remember the original user
	 */
	public function setCurrentUser($user_id, $originalUserId=false)
	{
		if(!empty($originalUserId)){
			$this->values['original_user_id'] = $originalUserId;
		}
		
		if($user_id  instanceof Model\User){
			$this->_user=$user_id;
			$this->values['user_id']=$user_id->id;
		} else {
			//remember user id in session
			$this->values['user_id']=$user_id;
		}
		
		
		
		if(!\GO::user()) {
			throw new \Exception("Could not set user with id " . $user_id . " in Session::setCurrentUser()!");
		}
		
		date_default_timezone_set(\GO::user()->timezone);
		
		\GO::language()->setLanguage(\GO::user()->language);
		
		//for logging
		\GO::session()->values['username']=\GO::user()->username;
    
	    if(isset(\GO::session()->values['accessToken'])) {
			$token = \go\core\model\Token::find()->where(['accessToken' => \GO::session()->values['accessToken']])->single();
			$token->userId = $user_id;
			if(!$token->save()) {
				throw new \Exception("Could not set token");
			}
	    }

		if (!empty(\GO::config()->debug_usernames)) {
			if (in_array(\GO::user()->username, \GO::config()->debug_usernames)){
				go()->getDebugger()->enable(true);
			}
		}
	}
	
		
	public function isUserSwitched(){

		$currentUserId = isset($this->values['user_id'])?$this->values['user_id']:null;
		$originalUserId = isset($this->values['original_user_id'])?$this->values['original_user_id']:null;

		if($currentUserId === null || $originalUserId === null){
			return false;
		}
		
		return $originalUserId != $currentUserId;
	}
	
	public function clear(){
		\GO::debug('CLEAR THE SESSION');
		$this->values=['accessToken' => $this->values['accessToken']]; //clear session
	}
	

}
