<?php

namespace go\core\auth;

use Exception;
use go\core\db\Column;
use go\core\ErrorHandler;
use go\core\exception\Forbidden;
use go\core\exception\Unavailable;
use go\core\model\AuthAllowGroup;
use go\core\model\RememberMe;
use go\core\model\Token;
use go\core\model\User;
use go\core\jmap\Request;
use go\core\util\DateTime;
use go\core\jmap\State as JmapState;
use go\modules\community\addressbook\model\Contact;

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
	 * @return PrimaryAuthenticator|false
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

	public function getSecondaryAuthenticatorsForUser($username): array
	{
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
	 */
	public function getPrimaryAuthenticators(): array
	{
		$this->populateAuthenticators();

		return $this->primaryAuthenticators;
	}

	/**
	 * Get all the primary authenticators
	 *
	 * @return SecondaryAuthenticator[]
	 */
	public function getSecondaryAuthenticators(): array
	{

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
			}
		}
	}


	/**
	 * Checks if this user was created in the database and not by an authenticator like LDAP or IMAP
	 *
	 * @param $username
	 * @return bool
	 * @throws Exception
	 */
	private function isLocalUser($username): bool
	{
		return go()->getDbConnection()
			->selectSingleValue('id')
			->from('core_auth_password', 'p')
			->join('core_user', 'u', 'u.id=p.userId')
			->where('username', '=', explode('@', $username)[0])
			->single() != null;
	}

	/**
	 * Danger: Set's a user to authenticated state
	 * @param User $user
	 * @return Token
	 * @throws Exception
	 */
	public function setAuthenticated(User $user): Token
	{
		$token = new Token();
		$token->userId = $user->id;
		$token->setAuthenticated(true);

		$token->setCookie();

		return $token;
	}


	private function logFailure(string $username): void
	{
		// Don't change log message as fail2ban relies on it
		ErrorHandler::log("Password authentication failed for '" . $username . "' from IP: '" . Request::get()->getRemoteIpAddress() . "'");
	}

	/**
	 * Does the password authentication.
	 *
	 * Our web interface may require secondary authenticator like OTP. But some other protocols like DAV and ActiveSync
	 * only require a username and password.
	 *
	 * @param string $username
	 * @param string $password
	 * @return false|User For performance reasons the user is fetched read only and partially with properties: ['id', 'username', 'password', 'enabled']
	 * @throws Exception
	 */
	public function passwordLogin(string $username, string $password): bool|User
	{
		$isLocalUser = $this->isLocalUser($username);

		go()->debug("Auth ". $username . " is " . ($isLocalUser ? "local" : "not local"));
		// When the user is local don't use
		if(!$isLocalUser && !str_contains($username, '@') && go()->getSettings()->defaultAuthenticationDomain) {
			$username .= '@' . go()->getSettings()->defaultAuthenticationDomain;
		}

		go()->debug("Authenticating " . $username);

		$cacheKey = 'login-' . md5($username. '|' . $password);

		if(!go()->getSettings()->maintenanceMode && $cache = go()->getCache()->get($cacheKey)) {
			$this->usedPasswordAuthenticator = $cache[1];
			return $cache[0];
		}

		$authenticator = $this->getPrimaryAuthenticatorForUser($username);

		if(!$authenticator) {

			// If we get here then the given username doesn't exist.
			// Do a password_verify for timing attacks as this would be done for a
			// valid user.

			password_verify("randomboguspasswordstring", '$2y$10$wkP8uDjY/tt5GNrfJJO9SOknqStW0POBn5Z4zpctuQkMP7pibTz2m');

			User::fireEvent(User::EVENT_BADLOGIN, $username, null);

			$this->logFailure($username);

			return false;
		}

		$this->usedPasswordAuthenticator = $authenticator;

		go()->log("Trying: " . get_class($authenticator));

		if (!($user = $authenticator->authenticate($username, $password))) {

			User::fireEvent(User::EVENT_BADLOGIN, $username, null);
			$this->logFailure($username);
			return false;
		}

		go()->log("success");

		if(!$user->enabled) {
			throw new Forbidden(go()->t("Your account has been disabled."));
		}

		if(!go()->getEnvironment()->isCli()) {
			$ip = Request::get()->getRemoteIpAddress();
			if (!AuthAllowGroup::isAllowed($user, $ip)) {
				throw new Forbidden(str_replace('{ip}', $ip, go()->t("You are not allowed to login from IP address {ip}.")));
			}
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
	public function getUsedPasswordAuthenticator(): PrimaryAuthenticator
	{
		return $this->usedPasswordAuthenticator;
	}

	public function sendRecoveryMail($email): bool
	{
		$email = trim($email);

		$user = User::find([
			'id',
			'username',
			'email'
		])
			->where(['email' => $email])
			->orWhere(['recoveryEmail' => $email])
			->single();
		if (empty($user)) {
			go()->debug("User not found");
			return false;
		}

		$primary = $this->getPrimaryAuthenticatorForUser($user->username);
		// if no primary authenticator was found then also allow password recovery to create a password
		if($primary && !($primary instanceof Password)) {
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

	/**
	 * @throws Exception
	 */
	public function logout(): bool
	{
		RememberMe::unsetCookie();
		$state = new JmapState();
		$token = $state->getToken();
		if(!$token) {
			return false;
		}

		User::fireEvent(User::EVENT_LOGOUT, $token->getUser(), $token);

		$token->oldLogout();
		Token::delete($token->primaryKeyValues());
		Token::unsetCookie();

		go()->getLanguage()->unsetCookie();

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
