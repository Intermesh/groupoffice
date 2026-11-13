<?php

namespace go\core\oauth\server;

use DateInterval;
use Exception;
use go\core\ErrorHandler;
use go\core\event\EventEmitterTrait;
use go\core\fs\File;
use go\core\model\OauthClient;
use go\core\oauth\server\repositories;
use go\core\util\ArrayObject;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\ResourceServer;
use League\OAuth2\Server\RequestTypes\AuthorizationRequestInterface;
use OpenIDConnectServer\ClaimExtractor;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The OAuth 2.0 / OpenID Connect server of GroupOffice
 *
 * Builds the authorization server for api/oauth.php and the resource server that validates its access tokens, so
 * modules that accept these tokens use the same keys and repositories.
 */
final class OAuthServer
{
	use EventEmitterTrait;

	/**
	 * Fired to collect the scopes of modules, like "mcp"
	 *
	 * The listener gets an ArrayObject and adds scope name => ['description' => string, 'resource' => URL of the
	 * resource server the tokens are for].
	 */
	const EVENT_SCOPES = 'scopes';

	/**
	 * Fired when the user approves a client that registered itself
	 *
	 * The listener gets the AuthorizationRequestInterface, the ID of the user and the array of data the consent screen
	 * sent for the module, like the items an MCP client may read. It may throw an OAuthServerException to refuse.
	 */
	const EVENT_CONSENT = 'consent';

	/**
	 * Lifetime of an access token
	 */
	const ACCESS_TOKEN_TTL = 'PT1H';

	/**
	 * Lifetime of an authorization code
	 */
	const AUTH_CODE_TTL = 'PT10M';

	/**
	 * Lifetime of a refresh token
	 */
	const REFRESH_TOKEN_TTL = 'P1M';

	/**
	 * The authorization server with the authorization code and refresh token grants
	 *
	 * @param string|null $privateKeyPath Path of the private key. The key of the installation when null.
	 * @return AuthorizationServer
	 * @throws Exception
	 */
	public static function authorizationServer(?string $privateKeyPath = null): AuthorizationServer
	{
		$privateKeyPath ??= self::privateKeyFile()->getPath();

		$server = new AuthorizationServer(
			new repositories\ClientRepository(),
			new repositories\AccessTokenRepository(),
			new repositories\ScopeRepository(),
			'file://' . $privateKeyPath,
			// Encrypts the authorization codes and refresh tokens. Random for each installation. Never put a fixed
			// key in the source: everybody could then create codes and tokens that this server accepts.
			EncryptionKey::get(),
			// OpenID Connect Response Type
			new IdTokenResponse(new repositories\UserRepository(), new ClaimExtractor(), self::kid())
		);

		$server->enableGrantType(
			new AuthCodeGrant(
				new repositories\AuthCodeRepository(),
				new repositories\RefreshTokenRepository(),
				new DateInterval(self::AUTH_CODE_TTL)
			),
			new DateInterval(self::ACCESS_TOKEN_TTL)
		);

		$refreshGrant = new RefreshTokenGrant(new repositories\RefreshTokenRepository());
		$refreshGrant->setRefreshTokenTTL(new DateInterval(self::REFRESH_TOKEN_TTL));
		$server->enableGrantType($refreshGrant, new DateInterval(self::ACCESS_TOKEN_TTL));

		return $server;
	}

	/**
	 * The scopes that modules add
	 *
	 * @return array<string, array{description: string, resource: string}>
	 */
	public static function moduleScopes(): array
	{
		$scopes = new ArrayObject();
		static::fireEvent(self::EVENT_SCOPES, $scopes);
		return $scopes->getArray();
	}

	/**
	 * Check the "resource" parameter of an authorization or token request (RFC 8707)
	 *
	 * A token of a module scope is only for the resource servers of the module scopes, like the MCP server. A request
	 * for another resource is refused, so a token can't be issued for a server that isn't ours.
	 *
	 * @param mixed $resource The "resource" parameter of the request
	 * @throws OAuthServerException "invalid_target" when the resource is unknown
	 */
	public static function validateResource(mixed $resource): void
	{
		if ($resource === null || $resource === '') {
			return;
		}

		$known = array_column(self::moduleScopes(), 'resource');
		if (!is_string($resource) || !in_array(self::canonicalResource($resource), array_map([self::class, 'canonicalResource'], $known), true)) {
			throw new OAuthServerException('The requested resource is not served by this authorization server', 3, 'invalid_target', 400);
		}
	}

	/**
	 * A resource URL with lower case scheme and host and without a trailing slash, for comparing
	 */
	private static function canonicalResource(string $url): string
	{
		$parts = parse_url($url);
		if (!is_array($parts) || !isset($parts['scheme'], $parts['host'])) {
			return $url;
		}
		return strtolower($parts['scheme']) . '://' . strtolower($parts['host'])
			. (isset($parts['port']) ? ':' . $parts['port'] : '')
			. rtrim($parts['path'] ?? '', '/');
	}

	/**
	 * Validate an authorization request
	 *
	 * Public clients, like AI assistants and apps without a secret, must use PKCE with the S256 method. The "plain"
	 * method sends the secret code verifier in the clear, so it's refused.
	 *
	 * @throws OAuthServerException When the request is invalid
	 */
	public static function validateAuthorizationRequest(AuthorizationServer $server, ServerRequestInterface $request): AuthorizationRequestInterface
	{
		$authRequest = $server->validateAuthorizationRequest($request);

		self::validateResource($request->getQueryParams()['resource'] ?? null);

		// A client that registered itself may only ask for the scopes of modules, like "mcp"
		if (self::needsConsent($authRequest->getClient())) {
			$moduleScopes = self::moduleScopes();
			foreach ($authRequest->getScopes() as $scope) {
				if (!isset($moduleScopes[$scope->getIdentifier()])) {
					throw OAuthServerException::invalidScope($scope->getIdentifier());
				}
			}
		}

		if (!$authRequest->getClient()->isConfidential() && $authRequest->getCodeChallengeMethod() !== 'S256') {
			throw OAuthServerException::invalidRequest('code_challenge_method', 'Public clients must use PKCE with code_challenge_method S256');
		}

		return $authRequest;
	}

	/**
	 * Whether the user must approve the client before it gets a token
	 *
	 * Clients that registered themselves, like AI assistants, need consent. Clients that an administrator registered
	 * are trusted.
	 */
	public static function needsConsent(ClientEntityInterface $client): bool
	{
		return $client instanceof OauthClient && $client->isSelfRegistered();
	}

	/**
	 * What the consent screen shows about a request
	 *
	 * @return array{clientId: string, clientName: string, redirectHost: string, localhostOnly: bool, scopes: array<array{name: string, description: string}>}
	 */
	public static function consentDetails(AuthorizationRequestInterface $authRequest): array
	{
		$client = $authRequest->getClient();
		$redirectUri = $authRequest->getRedirectUri() ?? (is_array($client->getRedirectUri()) ? ($client->getRedirectUri()[0] ?? '') : $client->getRedirectUri());
		$host = (string) parse_url((string) $redirectUri, PHP_URL_HOST);

		$moduleScopes = self::moduleScopes();
		$scopes = [];
		foreach ($authRequest->getScopes() as $scope) {
			$name = $scope->getIdentifier();
			$scopes[] = [
				'name' => $name,
				'description' => $moduleScopes[$name]['description'] ?? $name
			];
		}

		return [
			'clientId' => $client->getIdentifier(),
			'clientName' => $client->getName(),
			// The spec of client ID metadata documents requires showing where the user is sent back to
			'redirectHost' => $host,
			'localhostOnly' => in_array(strtolower($host), ['localhost', '127.0.0.1', '[::1]', '::1'], true),
			'scopes' => $scopes
		];
	}

	/**
	 * Complete a request after the user approved or denied it on the consent screen
	 *
	 * @param array $data What the consent screen sent for the modules, see {@see EVENT_CONSENT}
	 * @throws OAuthServerException When a module refuses the approval
	 */
	public static function completeConsent(AuthorizationServer $server, AuthorizationRequestInterface $authRequest, UserEntityInterface $user, bool $approved, array $data): ResponseInterface
	{
		$authRequest->setUser($user);

		if ($approved) {
			static::fireEvent(self::EVENT_CONSENT, $authRequest, (int) $user->getIdentifier(), $data);
		}

		$authRequest->setAuthorizationApproved($approved);

		return $server->completeAuthorizationRequest($authRequest, new Response());
	}

	/**
	 * The resource server that validates the access tokens of this server
	 *
	 * @param string|null $publicKeyPath Path of the public key. The key of the installation when null.
	 * @throws Exception
	 */
	public static function resourceServer(?string $publicKeyPath = null): ResourceServer
	{
		return new ResourceServer(
			new repositories\AccessTokenRepository(),
			new CryptKey($publicKeyPath ?? self::publicKeyFile()->getPath())
		);
	}

	/**
	 * The response of the userinfo endpoint: the claims of the user of the access token
	 *
	 * Only for tokens with the "openid" scope. A token for a module, like an MCP client, may not read the profile.
	 */
	public static function userInfo(ServerRequestInterface $request, ResourceServer $server): ResponseInterface
	{
		$response = new Response();

		try {
			$request = $server->validateAuthenticatedRequest($request);
		} catch (OAuthServerException $exception) {
			ErrorHandler::logException($exception);
			return $exception->generateHttpResponse($response);
		} catch (Exception $exception) {
			ErrorHandler::logException($exception);
			return (new OAuthServerException($exception->getMessage(), 0, 'unknown_error', 500))
				->generateHttpResponse($response);
		}

		if (!in_array('openid', (array) $request->getAttribute('oauth_scopes'), true)) {
			return (new OAuthServerException('The token has no openid scope', 9, 'insufficient_scope', 403))
				->generateHttpResponse($response);
		}

		$userEntity = (new repositories\UserRepository())->getUserEntityByIdentifier($request->getAttribute("oauth_user_id"));

		$data = $userEntity->getClaims();
		$data['sub'] = $userEntity->getIdentifier();

		$response->getBody()->write(json_encode($data));
		return $response
			->withStatus(200)
			->withHeader('pragma', 'no-cache')
			->withHeader('cache-control', 'no-store')
			->withHeader('content-type', 'application/json; charset=UTF-8');
	}

	/**
	 * The private key that signs the tokens. It's created when it doesn't exist.
	 *
	 * @throws Exception
	 */
	public static function privateKeyFile(): File
	{
		$file = go()->getDataFolder()->getFile('oauth2/private.key');
		if (!$file->exists()) {

			$file->getFolder()->create();

			$private = openssl_pkey_new();
			if (!openssl_pkey_export_to_file($private, $file->getPath())) {
				throw new Exception("Could not create private key file");
			}
			$file->chmod(0600);

			$details = openssl_pkey_get_details($private);

			$pubKeyFile = go()->getDataFolder()->getFile('oauth2/public.key');
			if (!$pubKeyFile->putContents($details["key"])) {
				throw new Exception("Could not create public key file");
			}
			$pubKeyFile->chmod(0600);
		}
		return $file;
	}

	/**
	 * The public key that validates the tokens
	 *
	 * @throws Exception
	 */
	public static function publicKeyFile(): File
	{
		$file = go()->getDataFolder()->getFile('oauth2/public.key');
		if (!$file->exists()) {
			self::privateKeyFile();
		}
		return $file;
	}

	/**
	 * Key ID of the public key, for the JSON Web Key Set
	 *
	 * @throws Exception
	 */
	public static function kid(): string
	{
		return md5(self::publicKeyFile()->getContents());
	}

	/**
	 * URL of the endpoints of api/oauth.php, like ".../api/oauth.php/token"
	 */
	public static function endpoint(string $name): string
	{
		return rtrim((string) go()->getSettings()->URL, '/') . '/api/oauth.php/' . $name;
	}

	/**
	 * The discovery document of the server, served at .well-known/openid-configuration
	 *
	 * @link https://openid.net/specs/openid-connect-discovery-1_0.html
	 */
	public static function metadata(): array
	{
		$signing_alg_values_supported = ['HS256', 'RS256'];

		return [
			'issuer' => IdTokenResponse::issuer(),
			'authorization_endpoint' => self::endpoint('authorize'),
			'token_endpoint' => self::endpoint('token'),
			'userinfo_endpoint' => self::endpoint('userinfo'),
			'jwks_uri' => self::endpoint('certs'),
			'scopes_supported' => array_merge(['openid'], array_keys(self::moduleScopes())),
			'response_types_supported' => ['code', 'code token', 'code id_token', 'token', 'id_token token', 'code id_token token', 'id_token'],
			'grant_types_supported' => ['authorization_code', 'refresh_token'],
			'acr_values_supported' => [],
			'subject_types_supported' => ['pairwise'],
			'userinfo_signing_alg_values_supported' => $signing_alg_values_supported,
			'id_token_signing_alg_values_supported' => $signing_alg_values_supported,
			// "none" for public clients without a secret, which must use PKCE
			'token_endpoint_auth_methods_supported' => ['client_secret_post', 'client_secret_basic', 'client_secret_jwt', 'private_key_jwt', 'none'],
			'code_challenge_methods_supported' => ['S256'],
			'token_endpoint_auth_signing_alg_values_supported' => $signing_alg_values_supported,
			'display_values_supported' => ['page'],
			'claim_types_supported' => ['normal'],
			'claims_supported' => ['aud', 'iss', 'iat', 'exp', 'nonce']
		] + (ClientRegistration::isAllowed() ? [
			'registration_endpoint' => self::endpoint('register'),
			'client_id_metadata_document_supported' => true
		] : []);
	}
}
