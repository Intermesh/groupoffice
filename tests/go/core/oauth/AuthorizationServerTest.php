<?php
namespace go\core\oauth\server;

use go\core\model\OauthClient;
use go\core\model\OauthUser;
use go\core\model\User;
use go\core\oauth\server\repositories\AccessTokenRepository;
use go\core\oauth\server\repositories\AuthCodeRepository;
use go\core\oauth\server\repositories\RefreshTokenRepository;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use PHPUnit\Framework\TestCase;

/**
 * Runs the real authorization server of api/oauth.php with our repositories through the normal flow of a client:
 * authorize, exchange the code and refresh the tokens.
 */
class AuthorizationServerTest extends TestCase
{
	private const REDIRECT_URI = 'https://rp.example.com/callback';
	private const CLIENT_SECRET = 'client-secret-for-tests';

	/** Hashing the secret of a client takes a while, and the tests don't change the client, so they share one */
	private static ?OauthClient $client = null;

	/** A client without a secret, like an AI assistant or an app */
	private static ?OauthClient $publicClient = null;

	private array $userIds = [];
	private ?\go\core\fs\File $publicKeyFile = null;

	const MODULE_SCOPE = 'testscope';
	const MODULE_RESOURCE = 'https://localhost/api/page.php/test/resource';

	/**
	 * Listener that adds a scope like a module does
	 */
	public static function addModuleScope(\go\core\util\ArrayObject $scopes): void
	{
		$scopes[self::MODULE_SCOPE] = ['description' => 'Test scope', 'resource' => self::MODULE_RESOURCE];
	}
	private ?string $httpHost = null;

	public static function setUpBeforeClass(): void
	{
		$client = new OauthClient();
		$client->setIdentifier(uniqid('client'));
		$client->setName('Test application');
		$client->setRedirectUri(self::REDIRECT_URI);
		$client->setIsConfidential(true);
		$client->setSecret(self::CLIENT_SECRET);
		self::assertTrue($client->save(), var_export($client->getValidationErrors(), true));
		self::$client = $client;

		$public = new OauthClient();
		$public->setIdentifier(uniqid('public'));
		$public->setName('Public test application');
		$public->setRedirectUri(self::REDIRECT_URI);
		$public->setIsConfidential(false);
		self::assertTrue($public->save(), var_export($public->getValidationErrors(), true));
		self::$publicClient = $public;
	}

	public static function tearDownAfterClass(): void
	{
		foreach ([self::$client, self::$publicClient] as $client) {
			if (isset($client->id)) {
				OauthClient::delete(['id' => $client->id]);
			}
		}
		self::$client = null;
		self::$publicClient = null;
	}

	protected function setUp(): void
	{
		$this->httpHost = $_SERVER['HTTP_HOST'] ?? null;
		$_SERVER['HTTP_HOST'] = 'localhost';
	}

	protected function tearDown(): void
	{
		if ($this->httpHost === null) {
			unset($_SERVER['HTTP_HOST']);
		} else {
			$_SERVER['HTTP_HOST'] = $this->httpHost;
		}

		foreach ($this->userIds as $id) {
			User::delete(['id' => $id]);
		}

		$this->publicKeyFile?->delete();
		\go\core\event\Listeners::get()->clear();
	}

	private function newUser(): User
	{
		$name = uniqid('oauth');
		$user = new User();
		$user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($user->save(), var_export($user->getValidationErrors(), true));
		$this->userIds[] = $user->id;

		return $user;
	}

	/**
	 * The server of api/oauth.php, with a key of its own for the tokens
	 */
	private function server(): AuthorizationServer
	{
		$keyFile = go()->getTmpFolder()->getFile(uniqid('oauth-test-key') . '.pem');
		$keyFile->getFolder()->create();
		$key = openssl_pkey_new(['private_key_bits' => 2048]);
		$this->assertTrue(openssl_pkey_export_to_file($key, $keyFile->getPath()));
		$keyFile->chmod(0600);

		// For a resource server that validates the tokens of this server
		$this->publicKeyFile = go()->getTmpFolder()->getFile(uniqid('oauth-test-pub') . '.pem');
		$this->publicKeyFile->putContents(openssl_pkey_get_details($key)['key']);
		$this->publicKeyFile->chmod(0600);

		$server = OAuthServer::authorizationServer($keyFile->getPath());

		$keyFile->delete();

		return $server;
	}

	/**
	 * The user logs in and approves. Returns the authorization code the application gets.
	 */
	private function authorize(AuthorizationServer $server, OauthClient $client, User $user): string
	{
		$request = (new ServerRequest('GET', 'https://localhost/api/oauth.php/authorize'))->withQueryParams([
			'response_type' => 'code',
			'client_id' => $client->getIdentifier(),
			'redirect_uri' => self::REDIRECT_URI,
			'scope' => 'openid',
			'state' => 'abc'
		]);

		$authRequest = $server->validateAuthorizationRequest($request);
		$authRequest->setUser(new OauthUser(User::findById($user->id, ['username', 'displayName', 'id', 'email', 'modifiedAt'])));
		$authRequest->setAuthorizationApproved(true);

		$response = $server->completeAuthorizationRequest($authRequest, new Response());
		parse_str(parse_url($response->getHeaderLine('Location'), PHP_URL_QUERY), $query);
		$this->assertSame('abc', $query['state']);
		$this->assertNotEmpty($query['code']);

		return $query['code'];
	}

	private function token(AuthorizationServer $server, OauthClient $client, array $params): array
	{
		$request = (new ServerRequest('POST', 'https://localhost/api/oauth.php/token'))->withParsedBody(array_merge([
			'client_id' => $client->getIdentifier(),
			'client_secret' => self::CLIENT_SECRET,
		], $params));

		$response = $server->respondToAccessTokenRequest($request, new Response());

		return json_decode((string) $response->getBody(), true);
	}

	private function exchangeCode(AuthorizationServer $server, OauthClient $client, string $code): array
	{
		return $this->token($server, $client, [
			'grant_type' => 'authorization_code',
			'redirect_uri' => self::REDIRECT_URI,
			'code' => $code
		]);
	}

	private function refresh(AuthorizationServer $server, OauthClient $client, string $refreshToken): array
	{
		return $this->token($server, $client, ['grant_type' => 'refresh_token', 'refresh_token' => $refreshToken]);
	}

	private function assertRefused(callable $call, string $message = ''): void
	{
		try {
			$call();
		} catch (OAuthServerException $e) {
			$this->assertTrue(true);
			return;
		}

		$this->fail('Expected the server to refuse this. ' . $message);
	}

	public function testMetadataAdvertisesPkceAndPublicClients(): void
	{
		$metadata = OAuthServer::metadata();

		// MCP clients refuse an authorization server without code_challenge_methods_supported
		$this->assertSame(['S256'], $metadata['code_challenge_methods_supported']);
		$this->assertContains('none', $metadata['token_endpoint_auth_methods_supported']);
	}

	private function publicAuthorizationRequest(array $params): ServerRequest
	{
		return (new ServerRequest('GET', 'https://localhost/api/oauth.php/authorize'))->withQueryParams(array_merge([
			'response_type' => 'code',
			'client_id' => self::$publicClient->getIdentifier(),
			'redirect_uri' => self::REDIRECT_URI,
			'scope' => 'openid',
			'state' => 'abc'
		], $params));
	}

	public function testPublicClientMustUsePkceWithS256(): void
	{
		$server = $this->server();
		$challenge = rtrim(strtr(base64_encode(hash('sha256', 'a-verifier-of-at-least-43-characters-long-123', true)), '+/', '-_'), '=');

		$this->assertRefused(fn() => OAuthServer::validateAuthorizationRequest($server, $this->publicAuthorizationRequest([])), 'no PKCE');
		$this->assertRefused(fn() => OAuthServer::validateAuthorizationRequest($server, $this->publicAuthorizationRequest([
			'code_challenge' => 'a-verifier-of-at-least-43-characters-long-123',
			'code_challenge_method' => 'plain'
		])), 'plain PKCE');

		$authRequest = OAuthServer::validateAuthorizationRequest($server, $this->publicAuthorizationRequest([
			'code_challenge' => $challenge,
			'code_challenge_method' => 'S256'
		]));
		$user = $this->newUser();
		$authRequest->setUser(new OauthUser(User::findById($user->id, ['username', 'displayName', 'id', 'email', 'modifiedAt'])));
		$authRequest->setAuthorizationApproved(true);
		$response = $server->completeAuthorizationRequest($authRequest, new Response());
		parse_str(parse_url($response->getHeaderLine('Location'), PHP_URL_QUERY), $query);

		// A public client exchanges the code without a secret, with the code verifier
		$request = (new ServerRequest('POST', 'https://localhost/api/oauth.php/token'))->withParsedBody([
			'grant_type' => 'authorization_code',
			'client_id' => self::$publicClient->getIdentifier(),
			'redirect_uri' => self::REDIRECT_URI,
			'code' => $query['code'],
			'code_verifier' => 'a-verifier-of-at-least-43-characters-long-123'
		]);
		$tokens = json_decode((string) $server->respondToAccessTokenRequest($request, new Response())->getBody(), true);
		$this->assertNotEmpty($tokens['access_token']);
	}

	/**
	 * Authorize the public client with PKCE and exchange the code. Returns the tokens.
	 */
	private function publicClientTokens(AuthorizationServer $server, string $scope, array $params = []): array
	{
		$verifier = 'a-verifier-of-at-least-43-characters-long-123';
		$authRequest = OAuthServer::validateAuthorizationRequest($server, $this->publicAuthorizationRequest(array_merge([
			'scope' => $scope,
			'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '='),
			'code_challenge_method' => 'S256'
		], $params)));
		$user = $this->newUser();
		$authRequest->setUser(new OauthUser(User::findById($user->id, ['username', 'displayName', 'id', 'email', 'modifiedAt'])));
		$authRequest->setAuthorizationApproved(true);
		$response = $server->completeAuthorizationRequest($authRequest, new Response());
		parse_str(parse_url($response->getHeaderLine('Location'), PHP_URL_QUERY), $query);

		$request = (new ServerRequest('POST', 'https://localhost/api/oauth.php/token'))->withParsedBody([
			'grant_type' => 'authorization_code',
			'client_id' => self::$publicClient->getIdentifier(),
			'redirect_uri' => self::REDIRECT_URI,
			'code' => $query['code'],
			'code_verifier' => $verifier
		]);
		return json_decode((string) $server->respondToAccessTokenRequest($request, new Response())->getBody(), true);
	}

	public function testUnknownResourceIsRefused(): void
	{
		OAuthServer::on(OAuthServer::EVENT_SCOPES, self::class, 'addModuleScope');
		$server = $this->server();

		$this->assertRefused(fn() => $this->publicClientTokens($server, self::MODULE_SCOPE, ['resource' => 'https://evil.example.com/mcp']), 'a token for another resource');

		// The same resource, written differently
		$tokens = $this->publicClientTokens($server, self::MODULE_SCOPE, ['resource' => 'https://LOCALHOST/api/page.php/test/resource/']);
		$this->assertNotEmpty($tokens['access_token']);

		$this->assertRefused(fn() => OAuthServer::validateResource('https://evil.example.com/mcp'), 'the token endpoint must refuse it too');
	}

	public function testTokenOfModuleScopeCanNotReadUserInfo(): void
	{
		OAuthServer::on(OAuthServer::EVENT_SCOPES, self::class, 'addModuleScope');
		$server = $this->server();
		$resourceServer = OAuthServer::resourceServer($this->publicKeyFile->getPath());

		$moduleToken = $this->publicClientTokens($server, self::MODULE_SCOPE)['access_token'];
		$response = OAuthServer::userInfo((new ServerRequest('GET', 'https://localhost/api/oauth.php/userinfo'))->withHeader('Authorization', 'Bearer ' . $moduleToken), $resourceServer);
		$this->assertSame(403, $response->getStatusCode());

		$openIdToken = $this->exchangeCode($server, self::$client, $this->authorize($server, self::$client, $this->newUser()))['access_token'];
		$response = OAuthServer::userInfo((new ServerRequest('GET', 'https://localhost/api/oauth.php/userinfo'))->withHeader('Authorization', 'Bearer ' . $openIdToken), $resourceServer);
		$this->assertSame(200, $response->getStatusCode());
	}

	public function testNormalFlowWorks()
	{
		$server = $this->server();
		$client = self::$client;
		$user = $this->newUser();

		$tokens = $this->exchangeCode($server, $client, $this->authorize($server, $client, $user));

		$this->assertNotEmpty($tokens['access_token']);
		$this->assertNotEmpty($tokens['refresh_token']);
		$this->assertNotEmpty($tokens['id_token']);

		$refreshed = $this->refresh($server, $client, $tokens['refresh_token']);
		$this->assertNotEmpty($refreshed['access_token']);
	}

	public function testAuthorizationCodeCanOnlyBeUsedOnce()
	{
		$server = $this->server();
		$client = self::$client;
		$code = $this->authorize($server, $client, $this->newUser());

		$this->exchangeCode($server, $client, $code);

		$this->assertRefused(fn() => $this->exchangeCode($server, $client, $code), 'a code was used twice');
	}

	public function testRefreshTokenIsReplacedEveryTimeItIsUsed()
	{
		$server = $this->server();
		$client = self::$client;
		$first = $this->exchangeCode($server, $client, $this->authorize($server, $client, $this->newUser()));

		$second = $this->refresh($server, $client, $first['refresh_token']);
		$this->assertNotSame($first['refresh_token'], $second['refresh_token']);

		$this->assertRefused(fn() => $this->refresh($server, $client, $first['refresh_token']), 'a used refresh token was accepted again');

		// the new one is fine
		$this->assertNotEmpty($this->refresh($server, $client, $second['refresh_token'])['access_token']);
	}

	public function testRefreshTokenIsRefusedWhileTheUserIsDisabled()
	{
		$server = $this->server();
		$client = self::$client;
		$user = $this->newUser();
		$tokens = $this->exchangeCode($server, $client, $this->authorize($server, $client, $user));

		// directly in the database so the cleanup on disabling doesn't hide that the server checks this itself
		go()->getDbConnection()->exec("update core_user set enabled = 0 where id = " . (int) $user->id);

		$this->assertRefused(fn() => $this->refresh($server, $client, $tokens['refresh_token']));
	}

	public function testAuthorizationCodeIsRefusedWhileTheUserIsDisabled()
	{
		$server = $this->server();
		$client = self::$client;
		$user = $this->newUser();
		$code = $this->authorize($server, $client, $user);

		go()->getDbConnection()->exec("update core_user set enabled = 0 where id = " . (int) $user->id);

		$this->assertRefused(fn() => $this->exchangeCode($server, $client, $code));
	}

	public function testDisablingTheUserRevokesWhatWasIssued()
	{
		$server = $this->server();
		$client = self::$client;
		$user = $this->newUser();
		$tokens = $this->exchangeCode($server, $client, $this->authorize($server, $client, $user));

		$u = User::findById($user->id);
		$u->enabled = false;
		$this->assertTrue($u->save());

		$u = User::findById($user->id);
		$u->enabled = true;
		$this->assertTrue($u->save());

		// enabling the user again must not bring the old tokens back
		$this->assertRefused(fn() => $this->refresh($server, $client, $tokens['refresh_token']));
	}

	public function testChangingThePasswordRevokesWhatWasIssued()
	{
		$server = $this->server();
		$client = self::$client;
		$user = $this->newUser();
		$tokens = $this->exchangeCode($server, $client, $this->authorize($server, $client, $user));

		$u = User::findById($user->id);
		$u->setPassword('An0ther!!Passw0rd');
		$u->setCurrentPassword('Pa55word!!xyz');
		$this->assertTrue($u->save(), var_export($u->getValidationErrors(), true));

		$this->assertRefused(fn() => $this->refresh($server, $client, $tokens['refresh_token']));
	}

	public function testRepositoriesRefuseWhatTheyNeverIssued()
	{
		$this->assertTrue((new AuthCodeRepository())->isAuthCodeRevoked(bin2hex(random_bytes(20))));
		$this->assertTrue((new RefreshTokenRepository())->isRefreshTokenRevoked(bin2hex(random_bytes(20))));
		// used to fail with an error instead of saying the token is revoked
		$this->assertTrue((new AccessTokenRepository())->isAccessTokenRevoked(bin2hex(random_bytes(20))));
	}

	public function testAccessTokenIsRefusedWhileTheUserIsDisabled()
	{
		$server = $this->server();
		$client = self::$client;
		$user = $this->newUser();
		$this->exchangeCode($server, $client, $this->authorize($server, $client, $user));

		$identifier = go()->getDbConnection()->selectSingleValue('identifier')->from('core_oauth_access_token')
			->where(['userIdentifier' => $user->id])->single();
		$this->assertNotNull($identifier);

		$repository = new AccessTokenRepository();
		$this->assertFalse($repository->isAccessTokenRevoked($identifier));

		go()->getDbConnection()->exec("update core_user set enabled = 0 where id = " . (int) $user->id);
		$this->assertTrue($repository->isAccessTokenRevoked($identifier));
	}
}
