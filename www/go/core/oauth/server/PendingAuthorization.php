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
		// Identifies this request on the consent screen, so the approval can't complete a request that replaced it
		$_SESSION['authRequestId'] = bin2hex(random_bytes(16));
	}

	/**
	 * ID of the stored request, for the consent screen. See {@see takeForConsent()}.
	 */
	public static function id(): ?string
	{
		return self::peek() ? ($_SESSION['authRequestId'] ?? null) : null;
	}

	/**
	 * Take the stored request when the user decided on the consent screen
	 *
	 * Only the request the user saw: another authorization request may have replaced it in the meantime, for example
	 * from another tab or a page that sent the user to the authorize URL. The stored request is always removed.
	 *
	 * @param string $requestId The ID that the consent screen got with the request, see {@see id()}
	 */
	public static function takeForConsent(string $requestId): ?AuthorizationRequest
	{
		$authRequest = self::peek();
		$storedId = $_SESSION['authRequestId'] ?? null;
		unset($_SESSION['authRequest'], $_SESSION['authRequestAt'], $_SESSION['authRequestId']);

		if (!$authRequest || !is_string($storedId) || $requestId === '' || !hash_equals($storedId, $requestId)) {
			return null;
		}
		return $authRequest;
	}

	/**
	 * The stored request, without removing it. For showing it to the user before they approve it.
	 */
	public static function peek(): ?AuthorizationRequest
	{
		$authRequest = $_SESSION['authRequest'] ?? null;
		$savedAt = $_SESSION['authRequestAt'] ?? 0;

		if (!($authRequest instanceof AuthorizationRequest) || !is_int($savedAt) || $savedAt < time() - self::LIFETIME) {
			return null;
		}

		return $authRequest;
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
		unset($_SESSION['authRequest'], $_SESSION['authRequestAt'], $_SESSION['authRequestId']);

		if (!($authRequest instanceof AuthorizationRequest) || !empty($queryParams) || !is_int($savedAt) || $savedAt < time() - self::LIFETIME) {
			return null;
		}

		return $authRequest;
	}
}
