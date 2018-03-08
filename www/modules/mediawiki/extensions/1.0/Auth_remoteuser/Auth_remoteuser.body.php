<?php


class Auth_remoteuser extends AuthPlugin {

	/**
	 * Pretend all users exist.  This is checked by
	 * authenticateUserData to determine if a user exists in our 'db'.
	 * By returning true we tell it that it can create a local wiki
	 * user automatically.
	 *
	 * @param $username String: username.
	 * @return bool
	 */
	public function userExists( $username ) {
		return true;
	}

	/**
	 * Check whether the given name matches REMOTE_USER.
	 * The name will be normalized to MediaWiki's requirements, so
	 * lower it and the REMOTE_USER before checking.
	 *
	 * @param $username String: username.
	 * @param $password String: user password.
	 * @return bool
	 */
	public function authenticate( $username, $password ) {
		global $wgAuthRemoteuserAuthz;

		if ( !$wgAuthRemoteuserAuthz ) {
			return false;
		}

		$usertest = $this->getRemoteUsername();

		return ( strtolower( $username ) == strtolower( $usertest ) );
	}

	/**
	 * Modify options in the login template.  This shouldn't be very
	 * important because no one should really be bothering with the
	 * login page.
	 *
	 * @param $template UserLoginTemplate object.
	 * @param $type String
	 */
	public function modifyUITemplate( &$template, &$type ) {
		// disable the mail new password box
		$template->set( 'useemail', false );
		// disable 'remember me' box
		$template->set( 'remember', false );
		$template->set( 'create', false );
		$template->set( 'domain', false );
		$template->set( 'usedomain', false );
	}

	/**
	 * Return true because the wiki should create a new local account
	 * automatically when asked to login a user who doesn't exist
	 * locally but does in the external auth database.
	 *
	 * @return bool
	 */
	public function autoCreate() {
		return true;
	}

	/**
	 * Do not allow various changes checked for by allowPropChange.
	 */
	public function allowRealNameChange() {
		return false;
	}
	public function allowEmailChange() {
		return false;
	}

	/**
	 * Of course not here
	 *
	 * @return bool
	 */
	public function allowPasswordChange() {
		return false;
	}

	/**
	 * MediaWiki should never see passwords, but if it does, don't store them.
	 *
	 * @return bool
	 */
	public function allowSetLocalPassword() {
		return false;
	}

	/**
	 * This should not be called because we do not allow password
	 * change.  Always fail by returning false.
	 *
	 * @param $user User object.
	 * @param $password String: password.
	 * @return bool
	 */
	public function setPassword( $user, $password ) {
		return false;
	}

	/**
	 * We don't support this but we have to return true for
	 * preferences to save.
	 *
	 * @param $user User object.
	 * @return bool
	 */
	public function updateExternalDB( $user ) {
		return true;
	}

	/**
	 * Should never be called, but return false anyway.
	 */
	public function addUser( $user, $password, $email = '', $realname = '' ) {
		return false;
	}

	/**
	 * Return true to prevent logins that don't authenticate here from
	 * being checked against the local database's password fields.
	 *
	 * @return bool
	 */
	public function strict() {
		return true;
	}

	/**
	 * When creating a user account, optionally fill in
	 * preferences and such.  For instance, you might pull the
	 * email address or real name from the external user database.
	 *
	 * @param $user User object.
	 * @param $autocreate bool
	 */
	public function initUser( &$user, $autocreate = false ) {
		$username = $this->getRemoteUsername();
		if ( Hooks::run( "AuthRemoteUserInitUser",
				array( $user, $autocreate ) ) ) {

			$this->setRealName( $user );

			$this->setEmail( $user, $username );

			$user->mEmailAuthenticated = wfTimestampNow();
			$user->setToken();

			$this->setNotifications( $user );
		}
		$user->saveSettings();
	}

	/**
	 * Normalize user names to the MediaWiki standard to prevent
	 * duplicate accounts.
	 *
	 * @param $username String: username.
	 * @return StringHelper
	 */
	public function getCanonicalName( $username ) {
		// lowercase the username
		$username = strtolower( $username );
		// uppercase first letter to make MediaWiki happy
		return ucfirst( $username );
	}

	/**
	 * Extension setup hook.  Run for each request.
	 */
	public function setupExtensionForRequest() {
		// See if we're even needed
		if ( $this->skipPage() ) {
			return;
		}

		$username = $this->getRemoteUsername();
		// Process the username only if required
		if ( !$username ) {
			return;
		}

		// Check for valid session
		$user = $this->getUserFromSession( $username );
		if( $user === true ) {
			return;
		}

		$this->handleLogin( $user, $username );
	}

	/**
	 * Sets the real name of the user.
	 *
	 * @param User
	 */
	public function setRealName( User $user ) {
		global $wgAuthRemoteuserName;

		if ( $wgAuthRemoteuserName ) {
			$user->setRealName( $wgAuthRemoteuserName );
		} else {
			$user->setRealName( '' );
		}
	}

	/**
	 * Sets the email address of the user.
	 *
	 * @param User
	 * @param String username
	 */
	public function setEmail( User $user, $username ) {
		global $wgAuthRemoteuserMail, $wgAuthRemoteuserMailDomain;

		if ( $wgAuthRemoteuserMail ) {
			$user->setEmail( $wgAuthRemoteuserMail );
		} elseif ( $wgAuthRemoteuserMailDomain ) {
			$user->setEmail( $username . '@' .
				$wgAuthRemoteuserMailDomain );
		} else {
			$user->setEmail( $username . "@example.com" );
		}
	}

	/**
	 * Set up notifications for the user.
	 *
	 * @param User
	 */
	public function setNotifications( User $user ) {
		global $wgAuthRemoteuserNotify;

		// turn on e-mail notifications
		if ( $wgAuthRemoteuserNotify ) {
			$user->setOption( 'enotifwatchlistpages', 1 );
			$user->setOption( 'enotifusertalkpages', 1 );
			$user->setOption( 'enotifminoredits', 1 );
			$user->setOption( 'enotifrevealaddr', 1 );
		}
	}

	/**
	 * Check if we're needed.
	 *
	 * @return bool true if this page should be skipped.
	 */
	public function skipPage() {
		global $wgRequest;

		$title = $wgRequest->getVal( 'title' );
		if ( ( $title == Title::makeName( NS_SPECIAL, 'UserLogout' ) ) ||
			( $title == Title::makeName( NS_SPECIAL, 'UserLogin' ) ) ) {
			return true;
		}
	}

	/**
	 * Return the username to be used.  Empty string if none.
	 *
	 * @return StringHelper
	 */
	public function getRemoteUsername( ) {
		global $wgAuthRemoteuserDomain;

		if ( isset( $_SERVER['REMOTE_USER'] ) ) {
			$username = $_SERVER['REMOTE_USER'];

			if ( $wgAuthRemoteuserDomain ) {
				$username = str_replace( "$wgAuthRemoteuserDomain\\",
					"", $username );
				$username = str_replace( "@$wgAuthRemoteuserDomain",
					"", $username );
			}
		} else {
			$username = "";
		}

		return $username;
	}

	/**
	 * Load the user from session
	 * @return mixed true if user is already logged in and no further action is needed.
	 *               User object if this user needs to be logged in
	 */
	public function getUserFromSession( $username ) {
		global $wgUser;
		$this->setupSession();

		$wgUser = User::newFromSession();
		if ( !$wgUser->isAnon() ) {
			if ( $wgUser->getName() ==
				$this->getCanonicalName( $username ) ) {
				return true; // User is already logged in.
			} else {
				$wgUser->doLogout(); // Logout mismatched user.
			}
		}
		return $wgUser;
	}

	public function setupSession() {
		if ( session_id() == '' ) {
			wfSetupSession();
		}
	}

	public function handleLogin( User $user, $username ) {
		// If the login form returns NEED_TOKEN try once more with the
		// right token
		$trycount = 0;
		$token = '';
		$errormessage = '';
		do {
			$tryagain = false;
			// Submit a fake login form to authenticate the user.
			$params = new FauxRequest( array(
					'wpName' => $username,
					'wpPassword' => '',
					'wpDomain' => '',
					'wpLoginToken' => $token,
					'wpRemember' => ''
				) );

			// Authenticate user data will automatically create
			// new users.
			$loginForm = new LoginForm( $params );
			$result = $loginForm->authenticateUserData();
			switch ( $result ) {
				case LoginForm :: SUCCESS :
					$user->setOption( 'rememberpassword', 1 );
					$user->setCookies();
					break;
				case LoginForm :: NEED_TOKEN:
					$token = $loginForm->getLoginToken();
					$tryagain = ( $trycount == 0 );
					break;
				case LoginForm :: WRONG_TOKEN:
					$errormessage = 'WrongToken';
					break;
				case LoginForm :: NO_NAME :
					$errormessage = 'NoName';
					break;
				case LoginForm :: ILLEGAL :
					$errormessage = 'Illegal';
					break;
				case LoginForm :: WRONG_PLUGIN_PASS :
					$errormessage = 'WrongPluginPass';
					break;
				case LoginForm :: NOT_EXISTS :
					$errormessage = 'NotExists';
					break;
				case LoginForm :: WRONG_PASS :
					$errormessage = 'WrongPass';
					break;
				case LoginForm :: EMPTY_PASS :
					$errormessage = 'EmptyPass';
					break;
				default:
					$errormessage = 'Unknown';
					break;
			}

			if ( $result != LoginForm::SUCCESS
				&& $result != LoginForm::NEED_TOKEN ) {
				error_log( 'Unexpected REMOTE_USER authentication'.
					' failure. Login Error was:' .
					$errormessage );
			}
			$trycount++;
		} while ( $tryagain );
	}
}
