<?php

namespace go\core\oauth\server;

use DateInterval;
use Exception;
use go\core\fs\File;
use go\core\oauth\server\repositories;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\ResourceServer;
use OpenIDConnectServer\ClaimExtractor;

/**
 * The OAuth 2.0 / OpenID Connect server of GroupOffice
 *
 * Builds the authorization server for api/oauth.php and the resource server that validates its access tokens, so
 * modules that accept these tokens use the same keys and repositories.
 */
final class OAuthServer
{
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
	 * The resource server that validates the access tokens of this server
	 *
	 * @throws Exception
	 */
	public static function resourceServer(): ResourceServer
	{
		return new ResourceServer(
			new repositories\AccessTokenRepository(),
			new CryptKey(self::publicKeyFile()->getPath())
		);
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
		return rtrim(go()->getSettings()->URL, '/') . '/api/oauth.php/' . $name;
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
			'scopes_supported' => ['openid'],
			'response_types_supported' => ['code', 'code token', 'code id_token', 'token', 'id_token token', 'code id_token token', 'id_token'],
			'grant_types_supported' => ['authorization_code', 'refresh_token'],
			'acr_values_supported' => [],
			'subject_types_supported' => ['pairwise'],
			'userinfo_signing_alg_values_supported' => $signing_alg_values_supported,
			'id_token_signing_alg_values_supported' => $signing_alg_values_supported,
			'token_endpoint_auth_methods_supported' => ['client_secret_post', 'client_secret_basic', 'client_secret_jwt', 'private_key_jwt'],
			'token_endpoint_auth_signing_alg_values_supported' => $signing_alg_values_supported,
			'display_values_supported' => ['page'],
			'claim_types_supported' => ['normal'],
			'claims_supported' => ['aud', 'iss', 'iat', 'exp', 'nonce']
		];
	}
}
