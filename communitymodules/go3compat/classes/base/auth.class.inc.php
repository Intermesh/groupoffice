<?php
/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @version $Id: auth.class.inc.php 19784 2016-01-26 13:56:16Z michaelhart86 $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package go.basic
 */

/**
 * Implementation of GroupOffice Authentication. This class provides the 
 * login-function for the Group-Office SQL database,
 * which is the default authentication mechanism.
 * 
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: auth.class.inc.php 19784 2016-01-26 13:56:16Z michaelhart86 $
 * @package go.basic
 * @since Group-Office 2.17
 * 
 */

class GO_AUTH extends db
{
	/**
	 * Authenticate the user against the Group-Office SQL database.
	 * 
	 * This function authenticates a given user and password against the SQL
	 * database. First it checks if the username and the given password are
	 * available inside the database. The it fetches the userid number of the
	 * found user. When an error (or authentication failure) occours, the
	 * function returns null.
	 * 
	 * @access private
	 * 
	 * @param StringHelper $username is the username we should authenticate.
	 * @param StringHelper $password is the user's password, we should use.
	 * 
	 * @return int the userid number of the given user if the authentication
	 * was successfull and we were able to fetch the ID, true if we were able
	 * to authenticate the user, but got no ID, and null if the authentication
	 * has failed.
	 */
	function authenticate($username, $password) {
		// Query the database for the given username with the associated
		// password. We only need to get the userid from the database, all
		// other columns are not interesting for the authentication.
		

		$sql = "SELECT * FROM go_users WHERE username='".$this->escape($username)."'";
		$this->query($sql);

		// Check if we got a valid result from the SQL database. Otherwise the
		// login has failed.

		$user = $this->next_record();

		if  (!$user) {
			return false;
		}

		//We used to use MD5 but we changed it to crypt 
		if($user['password_type']=='crypt'){
			if(crypt($password, $user['password']) != $user['password']){
				return false;
			}
		}else
		{
			//pwhash is not set yet. We're going to use the old md5 hashed password
			if(md5($password)!=$user['password']){
				return false;
			}else
			{
				//clear old md5 hash and set new pwhash for improved security.
				//if(false){
					$u['id']=$user['id'];
					$user['password']=$u['password']=crypt($password);
					$u['password_type']='crypt';

					$this->update_row('go_users', 'id', $u);
				//}
			}
		}


		//A secret key used for encryption of private data
		$_SESSION['GO_SESSION']['key']=md5($user['password'].':'.$password);
		
		// There were not problems, so we can return the userid number.
		return $user;
	}	
	
	/**
	 * Actualise session, increment logins and check WebDAV status.
	 * 
	 * This function is executed when the authentication was successful, and
	 * is used to set the necessary session variables, inform the security
	 * framework that the user has been logged in, checks the permissions for
	 * WebDAV and increments the login count of the user.
	 * 
	 * @access private
	 * 
	 * @param int $user_id is the userid number of the user that has been
	 * authenticated successfully.
	 */
	function updateAfterLogin($user, $count_login=true) {
		global $GO_SECURITY, $GO_MODULES,$GO_CONFIG;


		require_once($GLOBALS['GO_CONFIG']->class_path.'base/users.class.inc.php');
		$GO_USERS = new GO_USERS();

		// Tell the security framework that a user has been logged in. The
		// security framework takes care on setting the userid as active.
		$GLOBALS['GO_SECURITY']->logged_in($user);

//		require_once($GLOBALS['GO_CONFIG']->class_path.'filesystem.class.inc');
//		$fs = new filesystem();
//
//		// Increment the number of logins of the given user.
//		if($count_login){
//			$GO_USERS->increment_logins($user['id']);
//
//			//clean temp dir only when counting the login
//			//logins are not counted for example when a synchronization is done.
//			//We also don't want to clear the temp dir in that case because that can
//			//screw up an active session in the browser.			
//			if(is_dir($GLOBALS['GO_CONFIG']->tmpdir.$user['id'].'/'))
//			{
//				$fs->delete($GLOBALS['GO_CONFIG']->tmpdir.$user['id'].'/');
//			}
//		}
//		$fs->mkdir_recursive($GLOBALS['GO_CONFIG']->tmpdir.$user['id'].'/');
		
		//count_login is false when sync or dav logs in. We want to use the 
		//cache in that case because webdav makes lots of logins.
//		if($count_login)
//			$user['cache']='';

		//reinitialise available modules
		$GLOBALS['GO_MODULES']->load_modules($user);
	}


	/**
	 * This function logs a user in
	 * 
	 * This function tries to authenticate a given username against the used
	 * authentication backend (using the authenticate() function of the active
	 * backend - that means from the used child class from this class).
	 * The authentication may have two results: successful or failed:
	 * * failed: when the authentication was not possible (the reason doesn't
	 *   matter), this method returns false to indicate the failure.
	 * * successful: when the authentication was successful, the method checks
	 *   if the authenticated user exists in the currently used user management
	 *   database. If the user doesn't exist there, it is added.
	 * 
	 * When the user exists in the user management database from the beginning,
	 * the method checks if the account is enabled.
	 * 
	 * Only when the account is in the user management database and is enabled,
	 * then the user is registered in the session (using the updateAfterLogin()
	 * method) and the function will return true to indicate that the login was
	 * successful.
	 *
	 * @access public
	 * 
	 * @param StringHelper $username
	 * @param StringHelper $password
	 * @param array $params The authentication source specified in auth_sources.inc
	 * 
	 * @return bool true if the login was possible, false otherwise.
	 */
	function login($username, $password, $type='normal', $count_login=true) {
		// This variable is used to fetch the user's profile from the current
		// user management backend database.
		global $GO_EVENTS, $GO_SECURITY;

		$args = array(&$username, &$password, $count_login);
		
		if(!isset($GO_EVENTS))
			$GO_EVENTS = new GO_EVENTS();
		
		$GLOBALS['GO_EVENTS']->fire_event('before_login', $args);

		// This variable is used to set the id of the user that is currently
		// logged in. Since we try to login a (maybe new) user, we have to
		// clear the active user from the session.

		$GLOBALS['GO_SECURITY']->user_id = 0;

		// Authenticate the user.
		$user = $this->authenticate($username, $password, $type);
		// Check if the authentication was successful, otherwise exit.
		if (!$user) {
			$GLOBALS['GO_EVENTS']->fire_event('bad_login', $args);
			go_debug('Wrong password entered for '.$username);
		}

		if($user['enabled']!=1){
			go_debug('Login attempt for disabled user '.$username);
			$user=false;
		}

		if(!$user)
		{
			//sleep for 3 seconds to slow down brute force attacks
			go_infolog("LOGIN FAILED for user: \"".$username."\" from IP: ".$_SERVER['REMOTE_ADDR']);
			sleep(3);
			return false;
		}
		
		// Actualise session and other necessary things.
		$this->updateAfterLogin($user,$count_login);

		go_debug('LOGIN Username: '.$username.'; IP: '.$_SERVER['REMOTE_ADDR']);
		
		if($count_login)
			go_infolog("LOGIN SUCCESS for user: \"".$username."\" from IP: ".$_SERVER['REMOTE_ADDR']);
		
		$args=array($username, $password, $user, $count_login);
		$GLOBALS['GO_EVENTS']->fire_event('login', $args);

		return true;
	}

	/**
	 * Check if a given user is enabled.
	 * 
	 * This function checks, if a given user is enabled (allowed to login) and
	 * return a regarding boolean value.
	 * 
	 * @access public
	 * 
	 * @param int $user_id is the userid number the function should check.
	 * 
	 * @return bool true if the user is enabled, false otherwise.
	 */
	function is_enabled( $user_id ) {
		
		require_once($GLOBALS['GO_CONFIG']->class_path.'base/users.class.inc.php');
		$GO_USERS = new GO_USERS();

		// The status of the user is stored inside the user management system,
		// so we need to fetch the user's profile from the user manager.
		$user = $GO_USERS->get_user( $user_id );
		
		// Check if the user's enabled attribute is set.
		if ( $user['enabled'] == '1' ) {
			return true;
		}

		return false;
	}
}
