<?php
namespace go\core\oauth\server;

use go\core\model\OauthClient;
use go\core\model\OauthUser;
use go\core\model\User;
use go\core\oauth\server\repositories\AccessTokenRepository;
use go\core\oauth\server\repositories\AuthCodeRepository;
use go\core\oauth\server\repositories\ClientRepository;
use go\core\oauth\server\repositories\RefreshTokenRepository;
use go\core\oauth\server\repositories\ScopeRepository;
use go\core\oauth\server\repositories\UserRepository;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use OpenIDConnectServer\ClaimExtractor;
use OpenIDConnectServer\IdTokenResponse;
use PHPUnit\Framework\TestCase;

/**
 * Runs the real authorization server of api/oauth.php with our repositories through the normal flow of a client:
 * authorize, exchange the code and refresh the tokens.
 */
class AuthorizationServerTest extends TestCase
{
	private const REDIRECT_URI = 'https://rp.example.com/callback';
	private const CLIENT_SECRET = 'client-secret-for-tests';

	private array $userIds = [];
	private array $clientIds = [];
	private ?string $httpHost = null;

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

		foreach ($this->clientIds as $id) {
			OauthClient::delete(['id' => $id]);
		}
		foreach ($this->userIds as $id) {
			User::delete(['id' => $id]);
		}
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

	private function newClient(): OauthClient
	{
		$client = new OauthClient();
		$client->setIdentifier(uniqid('client'));
		$client->setName('Test application');
		$client->setRedirectUri(self::REDIRECT_URI);
		$client->setIsConfidential(true);
		$client->setSecret(self::CLIENT_SECRET);
		$this->assertTrue($client->save(), var_export($client->getValidationErrors(), true));
		$this->clientIds[] = $client->id;

		return $client;
	}

	/**
	 * The same setup as api/oauth.php, with a key of its own for the tokens
	 */
	private function server(): AuthorizationServer
	{
		$keyFile = go()->getTmpFolder()->getFile(uniqid('oauth-test-key') . '.pem');
		$keyFile->getFolder()->create();
		$this->assertTrue(openssl_pkey_export_to_file(openssl_pkey_new(['private_key_bits' => 2048]), $keyFile->getPath()));
		$keyFile->chmod(0600);

		$server = new AuthorizationServer(
			new ClientRepository(),
			new AccessTokenRepository(),
			new ScopeRepository(),
			'file://' . $keyFile->getPath(),
			EncryptionKey::get(),
			new IdTokenResponse(new UserRepository(), new ClaimExtractor(), 'test')
		);

		$server->enableGrantType(new AuthCodeGrant(new AuthCodeRepository(), new RefreshTokenRepository(), new \DateInterval('PT10M')), new \DateInterval('PT1H'));

		$refreshGrant = new RefreshTokenGrant(new RefreshTokenRepository());
		$refreshGrant->setRefreshTokenTTL(new \DateInterval('P1M'));
		$server->enableGrantType($refreshGrant, new \DateInterval('PT1H'));

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

	public function testNormalFlowWorks()
	{
		$server = $this->server();
		$client = $this->newClient();
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
		$client = $this->newClient();
		$code = $this->authorize($server, $client, $this->newUser());

		$this->exchangeCode($server, $client, $code);

		$this->assertRefused(fn() => $this->exchangeCode($server, $client, $code), 'a code was used twice');
	}

	public function testRefreshTokenIsReplacedEveryTimeItIsUsed()
	{
		$server = $this->server();
		$client = $this->newClient();
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
		$client = $this->newClient();
		$user = $this->newUser();
		$tokens = $this->exchangeCode($server, $client, $this->authorize($server, $client, $user));

		// directly in the database so the cleanup on disabling doesn't hide that the server checks this itself
		go()->getDbConnection()->exec("update core_user set enabled = 0 where id = " . (int) $user->id);

		$this->assertRefused(fn() => $this->refresh($server, $client, $tokens['refresh_token']));
	}

	public function testAuthorizationCodeIsRefusedWhileTheUserIsDisabled()
	{
		$server = $this->server();
		$client = $this->newClient();
		$user = $this->newUser();
		$code = $this->authorize($server, $client, $user);

		go()->getDbConnection()->exec("update core_user set enabled = 0 where id = " . (int) $user->id);

		$this->assertRefused(fn() => $this->exchangeCode($server, $client, $code));
	}

	public function testDisablingTheUserRevokesWhatWasIssued()
	{
		$server = $this->server();
		$client = $this->newClient();
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
		$client = $this->newClient();
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
		$client = $this->newClient();
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
