<?php

namespace go\core\auth;

use go\core\App;
use go\core\db\Column;
use go\core\ErrorHandler;
use go\core\exception\Forbidden;
use go\core\exception\Unavaiable;
use go\core\exception\Unavailable;
use go\core\jmap\State;
use go\core\model\AuthAllowGroup;
use go\core\model\RememberMe;
use go\core\model\Token;
use go\core\model\User;
use go\core\model\Log;
use go\core\jmap\Request;
use go\core\http\Response;
use go\core\util\DateTime;
use go\core\validate\ErrorCode;

/**
 * Class Authenticate
 *
 * This is a helper class that should always be used to authenticate a user
 * It will be used for webclient, sync client, webdav client
 */
class Authenticate {

	/**
	 * Cache password logins for this number of seconds to
	 */
	const CACHE_PASSWORD_LOGIN = 60;

	private $primaryAuthenticators;
	private $secondaryAuthenticators;


	/**
	 * Get the primary authenticator used with password login.
	 *
	 * @return PrimaryAuthenticator
	 */
	public function getPrimaryAuthenticatorForUser($username) {
		$this->populateAuthenticators();
		foreach ($this->getPrimaryAuthenticators() as $authenticator) {
			if ($authenticator->isAvailableFor($username)) {
				return $authenticator;
			}
		}

		return false;
	}

	public function getSecondaryAuthenticatorsForUser($username) {
		$auths = [];
		foreach ($this->getSecondaryAuthenticators() as $authenticator) {
			if ($authenticator->isAvailableFor($username)) {
				$auths[] = $authenticator;
			}
		}

		return $auths;

	}

	/**
	 * Get all the primary authenticators
	 *
	 * @return PrimaryAuthenticator[]
	 * @throws \Exception
	 */
	public function getPrimaryAuthenticators() {

		$this->populateAuthenticators();

		return $this->primaryAuthenticators;
	}

	/**
	 * Get all the primary authenticators
	 *
	 * @return SecondaryAuthenticator[]
	 * @throws \Exception
	 */
	public function getSecondaryAuthenticators() {

		$this->populateAuthenticators();

		return $this->secondaryAuthenticators;
	}

	private function populateAuthenticators() {
		if(!isset($this->primaryAuthenticators)) {
			$authMethods = Method::find()->orderBy(['sortOrder' => 'DESC'])->all();
			$this->secondaryAuthenticators = [];
			$this->primaryAuthenticators = [];
			foreach ($authMethods as $method) {
				$authenticator = $method->getAuthenticator();
				if ($authenticator instanceof PrimaryAuthenticator) {
					$this->primaryAuthenticators[] = $authenticator;
				} else {
					$this->secondaryAuthenticators[] = $authenticator;
				}
			};
		}
	}


	/**
	 * Checks if this user was created in the database and not by an authenticator like LDAP or IMAP
	 *
	 * @param $username
	 * @return bool
	 * @throws \Exception
	 */
	private function isLocalUser($username) {
		return go()->getDbConnection()
			->selectSingleValue('id')
			->from('core_auth_password', 'p')
			->join('core_user', 'u', 'u.id=p.userId')
			->where('username', '=', explode('@', $username)[0])
			->single() != null;
	}

	/**
	 * Does the password authentication.
	 *
	 * Our web interface may require secondary authenticator like OTP. But some other protocols like DAV and ActiveSync
	 * only require a username and password.
	 *
	 * @param $username
	 * @param $password
	 * @return false|User
	 * @throws \Exception
	 */
	public function passwordLogin($username, $password) {

		// When the user is local don't use
		if(!$this->isLocalUser($username) && !strstr($username, '@') && go()->getSettings()->defaultAuthenticationDomain) {
			$username .= '@' . go()->getSettings()->defaultAuthenticationDomain;
		}

		$cacheKey = 'login-' . md5($username. '|' . $password);

		if($cache = go()->getCache()->get($cacheKey)) {
			$this->usedPasswordAuthenticator = $cache[1];
			return $cache[0];
		}

		$authenticator = $this->getPrimaryAuthenticatorForUser($username);

		if(!$authenticator) {

			User::fireEvent(User::EVENT_BADLOGIN, $username, null);

			return false;
		}

		$this->usedPasswordAuthenticator = $authenticator;

		go()->log("Trying: " . get_class($authenticator));

		if (!$user = $authenticator->authenticate($username, $password)) {

			User::fireEvent(User::EVENT_BADLOGIN, $username, $user ? $user : null);

			return false;
		}

		go()->log("success");

		if(!$user->enabled) {
			throw new Forbidden(go()->t("You're account has been disabled."));
		}

		$ip = Request::get()->getRemoteIpAddress();
		if(!AuthAllowGroup::isAllowed($user, $ip)) {
			throw new Forbidden(str_replace('{ip}', $ip, go()->t("You are not allowed to login from IP address {ip}.") ));
		}

		if(go()->getSettings()->maintenanceMode && !$user->isAdmin()) {
			throw new Unavailable(go()->t("Service unavailable. Maintenance mode is enabled."));
		}

		go()->getCache()->set($cacheKey, [$user, $authenticator], true, self::CACHE_PASSWORD_LOGIN);

		return $user;

	}

	private $usedPasswordAuthenticator;

	/**
	 * @return PrimaryAuthenticator
	 */
	public function getUsedPasswordAuthenticator() {
		return $this->usedPasswordAuthenticator;
	}

	public function sendRecoveryMail($email) {
		$user = User::find()->where(['email' => $email])->orWhere(['recoveryEmail' => $email])->single();
		if (empty($user)) {
			go()->debug("User not found");
			return false;
		}

		$primary = $this->getPrimaryAuthenticatorForUser($user->username);
		if(!($primary instanceof \go\core\auth\Password)) {
			go()->debug("Authenticator doesn't support recovery");
			return false;
		}
		$user->sendRecoveryMail($email);
		return true;
	}

	public function recovery($hash) {
		if(empty($hash)) {
			return false;
		}
		$oneHourAgo = new DateTime('-1 hour');
		$user = User::find()
			->where('recoveryHash = :hash AND recoverySendAt > :time')
			->bind([
				':hash' => $hash,
				':time' => $oneHourAgo->format(Column::DATETIME_FORMAT)
			])->single();
		if (empty($user)) {
			return false;
		}
		return $user;
	}

	public function logout() {

		RememberMe::unsetCookie();
		$state = new \go\core\jmap\State();
		$token = $state->getToken();
		if(!$token) {
			return false;
		}

		User::fireEvent(User::EVENT_LOGOUT, $token->getUser(), $token);

		$token->oldLogout();
		Token::delete($token->primaryKeyValues());


		return true;
	}

	public function refreshToken($accessToken) {
		$token = Token::find()->where(['accessToken' => $accessToken])->single();
		if ($token && $token->isAuthenticated()) {
			$token->refresh();
		}
		return $token;
	}
}
