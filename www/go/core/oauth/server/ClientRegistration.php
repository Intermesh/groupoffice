<?php

namespace go\core\oauth\server;

use Exception;
use go\core\http\Client;
use go\core\model\OauthClient;
use InvalidArgumentException;

/**
 * OAuth clients that register themselves, like AI assistants
 *
 * Two ways are supported:
 *
 * - Dynamic client registration (RFC 7591): the client posts its metadata to api/oauth.php/register.
 * - Client ID metadata documents: the client ID is an HTTPS URL of a JSON document that this server fetches.
 *
 * Only while a module offers a scope for such clients, like the MCP module. Such clients are public (no secret), may
 * only ask for module scopes, and need the user's consent.
 *
 * @link https://www.rfc-editor.org/rfc/rfc7591
 * @link https://datatracker.ietf.org/doc/draft-ietf-oauth-client-id-metadata-document/
 */
final class ClientRegistration
{
	/**
	 * A fetched metadata document is used this long before it's fetched again
	 */
	const METADATA_TTL = 86400;

	/**
	 * Maximum size of a metadata document
	 */
	const MAX_DOCUMENT_SIZE = 65536;

	/**
	 * Maximum number of dynamic registrations per IP address per hour
	 */
	const MAX_REGISTRATIONS_PER_HOUR = 10;

	/**
	 * A document that couldn't be fetched or was invalid isn't fetched again for this many seconds
	 */
	const FAILED_FETCH_TTL = 600;

	/**
	 * Maximum number of metadata documents fetched per IP address per hour
	 */
	const MAX_FETCHES_PER_HOUR = 30;

	/**
	 * Fetches a metadata document: fn(string $url): array. For tests, which may not call external services.
	 *
	 * @var callable|null
	 */
	public static $fetcher = null;

	/**
	 * Whether clients may register themselves. Only when a module offers a scope for them.
	 */
	public static function isAllowed(): bool
	{
		return !empty(OAuthServer::moduleScopes());
	}

	/**
	 * Register a client (RFC 7591)
	 *
	 * @param array $metadata The client metadata that the client posted
	 * @return array The client information response
	 * @throws InvalidArgumentException With the RFC 7591 error code as message, like "invalid_redirect_uri"
	 */
	public static function register(array $metadata): array
	{
		$redirectUris = self::validRedirectUris($metadata['redirect_uris'] ?? null);

		$authMethod = $metadata['token_endpoint_auth_method'] ?? 'none';
		if ($authMethod !== 'none') {
			// Only public clients that use PKCE. A secret couldn't be kept by an AI assistant anyway.
			throw new InvalidArgumentException('invalid_client_metadata');
		}

		$client = new OauthClient();
		$client->setIdentifier('dyn_' . bin2hex(random_bytes(16)));
		$client->setName(self::clientName($metadata['client_name'] ?? null));
		$client->setRedirectUri($redirectUris);
		$client->setIsConfidential(false);
		$client->setRegistration('dynamic');
		if (!$client->save()) {
			throw new Exception('Could not save OAuth client: ' . $client->getValidationErrorsAsString());
		}

		return [
			'client_id' => $client->getIdentifier(),
			'client_id_issued_at' => time(),
			'client_name' => $client->getName(),
			'redirect_uris' => $redirectUris,
			'token_endpoint_auth_method' => 'none',
			'grant_types' => ['authorization_code', 'refresh_token'],
			'response_types' => ['code']
		];
	}

	/**
	 * Whether a client ID is the URL of a metadata document: HTTPS with a path
	 */
	public static function isMetadataUrl(string $clientId): bool
	{
		$parts = parse_url($clientId);
		return is_array($parts)
			&& ($parts['scheme'] ?? '') === 'https'
			&& !empty($parts['host'])
			&& !empty($parts['path']) && $parts['path'] !== '/'
			&& !isset($parts['fragment']) && !isset($parts['user']) && !isset($parts['pass'])
			// Only the HTTPS port, so it can't be used to probe other services
			&& (!isset($parts['port']) || $parts['port'] === 443)
			// The identifier column holds 128 characters
			&& strlen($clientId) <= 128;
	}

	/**
	 * The client of a metadata document. It's fetched when it's new or older than {@see METADATA_TTL}.
	 *
	 * @return OauthClient|null Null when the document is invalid or can't be fetched
	 */
	public static function fromMetadataDocument(string $url, ?OauthClient $existing): ?OauthClient
	{
		if (!self::isMetadataUrl($url)) {
			return null;
		}

		if ($existing && $existing->modifiedAt && $existing->modifiedAt->getTimestamp() > time() - self::METADATA_TTL) {
			return $existing;
		}

		$failedKey = 'oauth-metadata-failed-' . sha1($url);
		if (go()->getCache()->get($failedKey)) {
			return null;
		}

		try {
			$document = self::fetch($url);

			// The document must be about itself
			if (($document['client_id'] ?? null) !== $url) {
				throw new InvalidArgumentException('client_id of the document is not its URL');
			}
			$redirectUris = self::validRedirectUris($document['redirect_uris'] ?? null);
			if (($document['token_endpoint_auth_method'] ?? 'none') !== 'none') {
				throw new InvalidArgumentException('only public clients');
			}
		} catch (Exception $e) {
			go()->debug("Invalid client ID metadata document " . $url . ": " . $e->getMessage());
			// Not fetched on every authorization request
			go()->getCache()->set($failedKey, true, true, self::FAILED_FETCH_TTL);
			return null;
		}

		$client = $existing ?? new OauthClient();
		$client->setIdentifier($url);
		$client->setName(self::clientName($document['client_name'] ?? null));
		$client->setRedirectUri($redirectUris);
		$client->setIsConfidential(false);
		$client->setRegistration('metadata');
		// Also when nothing changed, so it isn't fetched again until the TTL passed
		$client->modifiedAt = new \go\core\util\DateTime();
		if (!$client->save()) {
			go()->debug("Could not save client of metadata document " . $url . ": " . $client->getValidationErrorsAsString());
			return null;
		}

		return $client;
	}

	/**
	 * Fetch a metadata document
	 *
	 * The URL is chosen by the client, so it may only reach public addresses (no internal servers), without redirects,
	 * with a size limit and a short timeout.
	 *
	 * @throws Exception
	 */
	private static function fetch(string $url): array
	{
		if (isset(self::$fetcher)) {
			$document = (self::$fetcher)($url);
		} else {
			if (!self::mayFetch(\go\core\http\Request::get()->getRemoteIpAddress())) {
				throw new Exception('Too many metadata documents fetched for this address');
			}
			$response = self::httpClient()->get($url);
			if ($response['status'] !== 200) {
				throw new Exception('HTTP status ' . $response['status']);
			}
			$document = json_decode((string) $response['body'], true);
		}

		if (!is_array($document)) {
			throw new InvalidArgumentException('not a JSON object');
		}
		return $document;
	}

	/**
	 * The HTTP client for metadata documents
	 *
	 * The URL is chosen by the client, so it may only reach global addresses (no internal servers), without redirects,
	 * with a size limit and a short timeout. {@see Client::$globalRangeOnly} checks the addresses.
	 */
	public static function httpClient(): Client
	{
		$http = new Client();
		$http->globalRangeOnly = true;
		$http->maxRedirects = 0;
		$http->maxResponseSize = self::MAX_DOCUMENT_SIZE;
		$http->setOption(CURLOPT_TIMEOUT, 5);
		$http->setHeader('Accept', 'application/json');
		return $http;
	}

	/**
	 * Redirect URIs must be HTTPS, or HTTP to the user's own computer (RFC 8252), without a fragment
	 *
	 * @return string[]
	 * @throws InvalidArgumentException "invalid_redirect_uri"
	 */
	private static function validRedirectUris(mixed $uris): array
	{
		if (!is_array($uris) || empty($uris) || count($uris) > 10) {
			throw new InvalidArgumentException('invalid_redirect_uri');
		}

		foreach ($uris as $uri) {
			$parts = is_string($uri) ? parse_url($uri) : false;
			if (!is_array($parts) || empty($parts['host']) || isset($parts['fragment']) || strlen($uri) > 512) {
				throw new InvalidArgumentException('invalid_redirect_uri');
			}
			$scheme = strtolower($parts['scheme'] ?? '');
			$loopback = in_array(strtolower($parts['host']), ['localhost', '127.0.0.1', '[::1]'], true);
			if ($scheme !== 'https' && !($scheme === 'http' && $loopback)) {
				throw new InvalidArgumentException('invalid_redirect_uri');
			}
		}

		return array_values($uris);
	}

	/**
	 * The name shown on the consent screen. It's chosen by the client, so it's shortened and stripped of tags.
	 */
	private static function clientName(mixed $name): string
	{
		$name = is_string($name) ? trim(strip_tags($name)) : '';
		return $name === '' ? go()->t("Unknown application") : mb_substr($name, 0, 128);
	}

	/**
	 * Whether this IP address may register another client. Counts the registration.
	 */
	public static function mayRegister(?string $ip): bool
	{
		return self::count('oauth-registrations', $ip, self::MAX_REGISTRATIONS_PER_HOUR);
	}

	/**
	 * Whether this IP address may make the server fetch another metadata document. Counts the fetch.
	 */
	public static function mayFetch(?string $ip): bool
	{
		return self::count('oauth-metadata-fetches', $ip, self::MAX_FETCHES_PER_HOUR);
	}

	private static function count(string $name, ?string $ip, int $max): bool
	{
		$key = $name . '-' . sha1((string) $ip) . '-' . date('YmdH');
		$count = (int) go()->getCache()->get($key);
		if ($count >= $max) {
			return false;
		}
		go()->getCache()->set($key, $count + 1, true, 3600);
		return true;
	}
}
