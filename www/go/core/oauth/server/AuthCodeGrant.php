<?php

namespace go\core\oauth\server;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\Uri\Uri;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The authorization code grant, with any port for redirects to "localhost"
 *
 * Apps on the user's computer, like Claude Code, listen for the redirect on a random port (RFC 8252 7.3). league
 * allows any port for 127.0.0.1 and [::1], but not for "localhost", which such apps also register, like
 * "http://localhost/callback". Other redirect URIs must still match exactly.
 */
class AuthCodeGrant extends \League\OAuth2\Server\Grant\AuthCodeGrant
{
	protected function validateRedirectUri(string $redirectUri, ClientEntityInterface $client, ServerRequestInterface $request): void
	{
		try {
			parent::validateRedirectUri($redirectUri, $client, $request);
		} catch (OAuthServerException $e) {
			if (!self::matchesLocalhostExceptPort($redirectUri, (array) $client->getRedirectUri())) {
				throw $e;
			}
		}
	}

	/**
	 * Whether a redirect to http://localhost matches a registered URI except for the port
	 *
	 * @param string[] $allowed The redirect URIs of the client
	 */
	public static function matchesLocalhostExceptPort(string $redirectUri, array $allowed): bool
	{
		$withoutPort = self::localhostWithoutPort($redirectUri);
		if ($withoutPort === null) {
			return false;
		}

		foreach ($allowed as $uri) {
			if (is_string($uri) && self::localhostWithoutPort($uri) === $withoutPort) {
				return true;
			}
		}
		return false;
	}

	/**
	 * The URI without its port, or null when it isn't an http://localhost URI
	 */
	private static function localhostWithoutPort(string $uri): ?string
	{
		try {
			$parsed = Uri::new($uri);
		} catch (\Throwable $e) {
			return null;
		}

		if ($parsed->getScheme() !== 'http' || strtolower((string) $parsed->getHost()) !== 'localhost') {
			return null;
		}
		return (string) $parsed->withPort(null);
	}
}
