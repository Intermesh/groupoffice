<?php

namespace go\core\oauth\server;

use League\OAuth2\Server\RequestTypes\AuthorizationRequest;

/**
 * An authorization request of a client that waits in the session while the user logs in
 */
class PendingAuthorization
{
	/**
	 * Seconds that the user has to log in
	 */
	const LIFETIME = 600;

	/**
	 * Store the request while the user logs in
	 *
	 * @param AuthorizationRequest $authRequest
	 * @return void
	 */
	public static function save(AuthorizationRequest $authRequest): void
	{
		$_SESSION['authRequest'] = $authRequest;
		$_SESSION['authRequestAt'] = time();
	}

	/**
	 * Take the stored request when the user returns from the login page
	 *
	 * The login page returns to the authorize URL without parameters. A request with its own parameters is a new
	 * authorization and must not complete an older request of another client. The stored request is always removed.
	 *
	 * @param array $queryParams The query parameters of the current request
	 * @return AuthorizationRequest|null
	 */
	public static function take(array $queryParams): ?AuthorizationRequest
	{
		$authRequest = $_SESSION['authRequest'] ?? null;
		$savedAt = $_SESSION['authRequestAt'] ?? 0;
		unset($_SESSION['authRequest'], $_SESSION['authRequestAt']);

		if (!($authRequest instanceof AuthorizationRequest) || !empty($queryParams) || !is_int($savedAt) || $savedAt < time() - self::LIFETIME) {
			return null;
		}

		return $authRequest;
	}
}
