<?php

namespace go\core\auth;

use Exception;
use go\core\db\Column;
use go\core\db\Criteria;
use go\core\ErrorHandler;
use go\core\exception\Forbidden;
use go\core\exception\Unavailable;
use go\core\jmap\Request;
use go\core\jmap\State as JmapState;
use go\core\model\AppPassword;
use go\core\model\AuthAllowGroup;
use go\core\model\RememberMe;
use go\core\model\Token;
use go\core\model\User;
use go\core\orm\Query;
use go\core\util\DateTime;

/**
 * Class Authenticate
 *
 * This is a helper class that should always be used to authenticate a user
 * It will be used for webclient, sync client, webdav client
 */
class Authenticate
{

	/**
	 * Cache password logins for this number of seconds to
	 * (JH: I hate it when people don't finish their)
	 */
	const CACHE_PASSWORD_LOGIN = 60;

	/**
	 * Failed attempts per username+IP before throttling starts
	 */
	const THROTTLE_MAX_USER_FAILURES = 5;

	/**
	 * Failed attempts per IP (any username) before throttling starts
	 */
	const THROTTLE_MAX_IP_FAILURES = 25;

	/**
	 * Seconds after which the failure counters are forgotten when no new failure occurs
	 */
	const THROTTLE_WINDOW = 900;

	/**
	 * Maximum back-off in seconds
	 */
	const THROTTLE_MAX_DELAY = 900;

	private array $primaryAuthenticators;
	private array $secondaryAuthenticators;

	private array $externalAuthenticators;


	/**
	 * Get the primary authenticator used with password login.
	 *
	 * @return PrimaryAuthenticator|false
	 */
	public function getPrimaryAuthenticatorForUser($username)
	{
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

	public function getExternalAuthenticatorsForUser($username): array
	{
		$auths = [];
		foreach ($this->getExternalAuthenticators() as $authenticator) {
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
	 * Get all the secondary authenticators. For example OTP auth after the password login.
	 *
	 * @return SecondaryAuthenticator[]
	 */
	public function getSecondaryAuthenticators(): array
	{
		$this->populateAuthenticators();

		return $this->secondaryAuthenticators;
	}

	/**
	 * Get all the external authenticators. For example OpenID Connect.
	 * @return array
	 */
	public function getExternalAuthenticators(): array
	{
		$this->populateAuthenticators();

		return $this->externalAuthenticators;
	}

	private function populateAuthenticators()
	{
		if (!isset($this->primaryAuthenticators)) {
			$authMethods = Method::find()->orderBy(['sortOrder' => 'DESC'])->all();
			$this->secondaryAuthenticators = [];
			$this->primaryAuthenticators = [];
			$this->externalAuthenticators = [];
			foreach ($authMethods as $method) {
				$authenticator = $method->getAuthenticator();
				if ($authenticator instanceof PrimaryAuthenticator) {
					$this->primaryAuthenticators[] = $authenticator;
				} else if ($authenticator instanceof SecondaryAuthenticator) {
					$this->secondaryAuthenticators[] = $authenticator;
				} else {
					$this->externalAuthenticators[] = $authenticator;
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


	private function throttleKeys(string $username, string $scope): array
	{
		if(go()->getEnvironment()->isCli()) {
			return [];
		}
		$ip = Request::get()->getRemoteIpAddress();
		return [
			'login-throttle-' . $scope . '-u-' . md5(strtolower($username) . '|' . $ip) => self::THROTTLE_MAX_USER_FAILURES,
			'login-throttle-' . $scope . '-ip-' . md5($ip) => self::THROTTLE_MAX_IP_FAILURES
		];
	}

	/**
	 * Throws when too many failed logins occurred recently for this username/IP.
	 * @throws Forbidden
	 */
	public function checkThrottle(string $username, string $scope = 'password'): void
	{
		foreach($this->throttleKeys($username, $scope) as $key => $max) {
			$state = go()->getCache()->get($key);
			if($state && $state['lockedUntil'] > time()) {
				throw new Forbidden(go()->t("Too many failed login attempts. Please try again later."));
			}
		}
	}

	/**
	 * Count a failed login and apply exponential back-off: delay doubles for every failure past the limit.
	 */
	public function registerFailure(string $username, string $scope = 'password'): void
	{
		foreach($this->throttleKeys($username, $scope) as $key => $max) {
			$state = go()->getCache()->get($key) ?: ['count' => 0, 'lockedUntil' => 0];
			$state['count']++;
			if($state['count'] >= $max) {
				$delay = min(self::THROTTLE_MAX_DELAY, 30 * (2 ** min(20, $state['count'] - $max)));
				$state['lockedUntil'] = time() + $delay;
			}
			go()->getCache()->set($key, $state, true, self::THROTTLE_WINDOW + self::THROTTLE_MAX_DELAY);
		}
	}

	public function clearThrottle(string $username, string $scope = 'password'): void
	{
		// Only the username+IP counter is reset, so a valid login can't be used to reset the IP counter.
		$keys = array_keys($this->throttleKeys($username, $scope));
		if($keys) {
			go()->getCache()->delete($keys[0]);
		}
	}

	private function logFailure(string $username): void
	{
		$this->registerFailure($username);
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

		go()->debug("Auth " . $username . " is " . ($isLocalUser ? "local" : "not local"));
		// When the user is local don't use
		if (!$isLocalUser && !str_contains($username, '@') && go()->getSettings()->defaultAuthenticationDomain) {
			$username .= '@' . go()->getSettings()->defaultAuthenticationDomain;
		}

		go()->debug("Authenticating " . $username);

		$this->checkThrottle($username);

		$cacheKey = 'login-' . md5($username . '|' . $password);

		if (!go()->getSettings()->maintenanceMode && $cache = go()->getCache()->get($cacheKey)) {
			$this->clearThrottle($username);
			$this->usedPasswordAuthenticator = $cache[1];
			return $cache[0];
		}

		$authenticator = $this->getPrimaryAuthenticatorForUser($username);

		if (!$authenticator) {

			// If we get here then the given username doesn't exist.
			// Do a password_verify for timing attacks as this would be done for a
			// valid user.

			// nosemgrep: detected-bcrypt-hash
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

		$this->clearThrottle($username);

		if (!$user->enabled) {
			throw new Forbidden(go()->t("Your account has been disabled."));
		}

		if (!go()->getEnvironment()->isCli()) {
			$ip = Request::get()->getRemoteIpAddress();
			if (!AuthAllowGroup::isAllowed($user, $ip)) {
				throw new Forbidden(str_replace('{ip}', $ip, go()->t("You are not allowed to login from IP address {ip}.")));
			}
		}

		if (go()->getSettings()->maintenanceMode && !$user->isAdmin()) {
			throw new Unavailable(go()->t("Service unavailable. Maintenance mode is enabled."));
		}

		if ($authenticator->needsCache()) {
			go()->getCache()->set($cacheKey, [$user, $authenticator], true, self::CACHE_PASSWORD_LOGIN);
		}

		return $user;

	}

	public function appPasswordLogin(string $username, string $password, string $protocol): bool|User
	{
		go()->debug("App password auth for " . $username . " (protocol: " . $protocol . ")");

		$cacheKey = 'apppw-' . hash('sha256', $username . '|' . $protocol . '|' . $password);

		if (!go()->getSettings()->maintenanceMode && $cached = go()->getCache()->get($cacheKey)) {
			return $cached;
		}

		$user = User::find()->where(['username' => $username])->single();

		if (!$user) {
			// same logic as passwordLogin for timing attacks
			// nosemgrep: detected-bcrypt-hash
			password_verify("randomboguspasswordstring", '$2y$10$wkP8uDjY/tt5GNrfJJO9SOknqStW0POBn5Z4zpctuQkMP7pibTz2m');

			go()->log("App password for '$username' rejected: not valid for protocol '$protocol'");

			return false;
		}

		$userAppPasswords = AppPassword::find()->where(['userId' => $user->id, 'revokedAt' => null])->all();

		foreach ($userAppPasswords as $appPassword) {

			if (!$appPassword->verifyPassword($password)) {
				continue;
			}

			if ($appPassword->hasMatchingScope($protocol)) {
				go()->log("App password login success for " . $username);

				go()->getCache()->set($cacheKey, $user, true, self::CACHE_PASSWORD_LOGIN);

				if (!$user->enabled) {
					throw new Forbidden(go()->t("Your account has been disabled."));
				}

				$ip = Request::get()->getRemoteIpAddress();

				if (!go()->getEnvironment()->isCli() && !AuthAllowGroup::isAllowed($user, $ip)) {
					throw new Forbidden(str_replace('{ip}', $ip, go()->t("You are not allowed to login from IP address {ip}.")));
				}

				if (go()->getSettings()->maintenanceMode && !$user->isAdmin()) {
					throw new Unavailable(go()->t("Service unavailable. Maintenance mode is enabled."));
				}

				$this->touchAppPassword($appPassword->id);

				return $user;
			}

			User::fireEvent(User::EVENT_BADLOGIN, $username, null);
			return false;
		}

		User::fireEvent(User::EVENT_BADLOGIN, $username, null);
		$this->logFailure($username);

		return false;
	}

	private function touchAppPassword(int $appPasswordId): void
	{
		$today = (new DateTime())->format('Y-m-d');

		go()->getDbConnection()->update(
			'core_app_password',
			[
				'lastUsedAt' => $today,
				'lastUsedIp' => Request::get()->getRemoteIpAddress()
			],
			(new Query())
				->where('id', '=', $appPasswordId)
				->andWhere(
					(new Criteria())
						->where('lastUsedAt', 'IS', null)
						->orWhere('lastUsedAt', '<', $today)
				)
		)->execute();
	}

	private $usedPasswordAuthenticator;

	/**
	 * @return ?PrimaryAuthenticator
	 */
	public function getUsedPasswordAuthenticator(): ?PrimaryAuthenticator
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
		if ($primary && !($primary instanceof Password)) {
			go()->debug("Authenticator doesn't support recovery");
			return false;
		}
		$user->sendRecoveryMail($email);
		return true;
	}

	public function recovery($hash)
	{
		if (empty($hash)) {
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
		if (!$token) {
			return false;
		}

		User::fireEvent(User::EVENT_LOGOUT, $token->getUser(), $token);

		$token->oldLogout();
		Token::delete($token->primaryKeyValues());
		Token::unsetCookie();

		go()->getLanguage()->unsetCookie();

		return true;
	}

	public function refreshToken($accessToken)
	{
		$token = Token::find()->where(['accessToken' => $accessToken])->single();
		if ($token && $token->isAuthenticated()) {
			$token->refresh();
		}
		return $token;
	}
}
