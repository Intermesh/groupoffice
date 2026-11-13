<?php
namespace go\modules\business\mcp;

use go\core\auth\TemporaryState;
use go\core\event\Listeners;
use go\core\model\OauthClient;
use go\core\model\OauthUser;
use go\core\model\User;
use go\core\oauth\server\ClientRegistration;
use go\core\oauth\server\OAuthServer;
use go\core\util\ArrayObject;
use go\modules\business\mcp\model\McpKey;
use go\modules\community\notes\model\Note;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/McpFixtureTrait.php';

/**
 * AI assistants that connect with OAuth only reach the MCP server with a token of the "mcp" scope of an assistant
 * that the user approved, until the user deletes its key
 */
class OAuthTest extends TestCase
{
	use McpFixtureTrait;

	const REDIRECT_URI = 'https://assistant.example.com/callback';
	const VERIFIER = 'a-verifier-of-at-least-43-characters-long-123';

	private $authState;
	private static string $privateKeyPath;
	private static string $publicKeyPath;
	private static OauthClient $client;

	/**
	 * Offers the "mcp" scope. The real listener only does that with a license, which the tests don't have.
	 */
	public static function addScope(ArrayObject $scopes): void
	{
		OAuth::addScope($scopes);
	}

	public static function setUpBeforeClass(): void
	{
		self::setUpMcp();
		try {
			$key = openssl_pkey_new(['private_key_bits' => 2048]);
			self::$privateKeyPath = go()->getTmpFolder()->getFile(uniqid('mcp-oauth-key') . '.pem')->getPath();
			self::$publicKeyPath = go()->getTmpFolder()->getFile(uniqid('mcp-oauth-pub') . '.pem')->getPath();
			go()->getTmpFolder()->create();
			openssl_pkey_export_to_file($key, self::$privateKeyPath);
			file_put_contents(self::$publicKeyPath, openssl_pkey_get_details($key)['key']);
			chmod(self::$privateKeyPath, 0600);
			chmod(self::$publicKeyPath, 0600);

			OAuthServer::on(OAuthServer::EVENT_SCOPES, self::class, 'addScope');
			$response = ClientRegistration::register(['client_name' => 'Test assistant', 'redirect_uris' => [self::REDIRECT_URI]]);
			self::$client = OauthClient::find()->where('identifier', '=', $response['client_id'])->single();
		} catch (\Throwable $e) {
			self::tearDownAfterClass();
			throw $e;
		} finally {
			Listeners::get()->clear();
		}
	}

	public static function tearDownAfterClass(): void
	{
		if (isset(self::$client)) {
			OauthClient::delete(['id' => self::$client->id]);
		}
		foreach ([self::$privateKeyPath ?? null, self::$publicKeyPath ?? null] as $path) {
			if ($path && is_file($path)) {
				unlink($path);
			}
		}
		self::tearDownMcp();
	}

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		OAuthServer::on(OAuthServer::EVENT_SCOPES, self::class, 'addScope');
	}

	protected function tearDown(): void
	{
		Listeners::get()->clear();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		McpKey::delete(['oauthClientId' => self::$client->id]);
		go()->setAuthState($this->authState);
	}

	private function authorizationRequest(string $scope, ?OauthClient $client = null): ServerRequest
	{
		return (new ServerRequest('GET', 'https://localhost/api/oauth.php/authorize'))->withQueryParams([
			'response_type' => 'code',
			'client_id' => ($client ?? self::$client)->getIdentifier(),
			'redirect_uri' => self::REDIRECT_URI,
			'scope' => $scope,
			'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', self::VERIFIER, true)), '+/', '-_'), '='),
			'code_challenge_method' => 'S256'
		]);
	}

	/**
	 * Run the OAuth flow of the assistant for the MCP user and return the access token
	 */
	private function accessToken(string $scope = OAuth::SCOPE, ?OauthClient $client = null): string
	{
		$client ??= self::$client;
		$server = OAuthServer::authorizationServer(self::$privateKeyPath);
		$authRequest = OAuthServer::validateAuthorizationRequest($server, $this->authorizationRequest($scope, $client));
		$authRequest->setUser(new OauthUser(User::findById(self::$mcpUser->id, ['username', 'displayName', 'id', 'email', 'modifiedAt'])));
		$authRequest->setAuthorizationApproved(true);
		parse_str(parse_url($server->completeAuthorizationRequest($authRequest, new Response())->getHeaderLine('Location'), PHP_URL_QUERY), $query);

		$tokens = json_decode((string) $server->respondToAccessTokenRequest((new ServerRequest('POST', 'https://localhost/api/oauth.php/token'))->withParsedBody([
			'grant_type' => 'authorization_code',
			'client_id' => $client->getIdentifier(),
			'redirect_uri' => self::REDIRECT_URI,
			'code' => $query['code'],
			'code_verifier' => self::VERIFIER
		]), new Response())->getBody(), true);

		return $tokens['access_token'];
	}

	private function grant(): McpKey
	{
		go()->setAuthState(new TemporaryState(self::$mcpUser->id));
		return OAuth::grant(self::$client, (int) self::$mcpUser->id, [(string) Note::entityType()->getId()]);
	}

	/**
	 * A licensed endpoint that lets the user use the module and validates tokens with the test key
	 */
	private function endpoint(): Endpoint
	{
		return new class(self::$publicKeyPath) extends Endpoint {
			public function __construct(private string $publicKeyPath)
			{
			}

			protected function isLicensed(): bool
			{
				return true;
			}

			protected function mayUseModule(int $userId): bool
			{
				return true;
			}

			protected function resourceServer(): ?ResourceServer
			{
				return OAuthServer::resourceServer($this->publicKeyPath);
			}
		};
	}

	private function callTool(string $token, string $tool): array
	{
		return $this->endpoint()->handle('POST', ['authorization' => 'Bearer ' . $token], json_encode([
			'jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/call', 'params' => ['name' => $tool, 'arguments' => new \stdClass()]
		]));
	}

	public function testProtectedResourceMetadata(): void
	{
		$metadata = OAuth::protectedResourceMetadata();

		$this->assertStringEndsWith('/api/page.php/business/mcp/mcp', $metadata['resource']);
		$this->assertSame([\go\core\oauth\server\IdTokenResponse::issuer()], $metadata['authorization_servers']);
		$this->assertSame(['mcp'], $metadata['scopes_supported']);
	}

	public function testScopeIsOnlyOfferedWithLicense(): void
	{
		Listeners::get()->clear(); // only the listeners of the installed modules

		$this->assertArrayNotHasKey(OAuth::SCOPE, OAuthServer::moduleScopes());
	}

	public function testUnauthorizedResponseLinksToTheMetadata(): void
	{
		$response = $this->endpoint()->handle('POST', [], '{}');

		$this->assertSame(401, $response['status']);
		$this->assertSame('Bearer resource_metadata="' . OAuth::metadataUrl() . '", scope="mcp"', $response['headers']['WWW-Authenticate']);
	}

	public function testApprovedAssistantReadsTheGrantedItems(): void
	{
		$this->grant();
		$token = $this->accessToken();

		$response = $this->callTool($token, 'list_entities');

		$this->assertSame(200, $response['status'], json_encode($response['body']));
		$names = array_column($response['body']['result']['structuredContent']['entities'], 'name');
		$this->assertContains('Note', $names);
		$this->assertNotContains('Contact', $names);
		$this->assertEquals(self::$mcpUser->id, go()->getAuthState()->getUserId());
	}

	public function testTokenWithoutApprovalIsRefused(): void
	{
		$token = $this->accessToken();

		$this->assertSame(401, $this->callTool($token, 'whoami')['status']);
	}

	public function testDeletingTheKeyRevokesTheTokens(): void
	{
		$key = $this->grant();
		$token = $this->accessToken();
		$this->assertSame(200, $this->callTool($token, 'whoami')['status']);

		go()->setAuthState(new TemporaryState(self::$mcpUser->id));
		$this->assertTrue(McpKey::delete(['id' => $key->id]));

		$this->assertSame(401, $this->callTool($token, 'whoami')['status']);
		$this->assertSame(0, (int) go()->getDbConnection()->selectSingleValue('COUNT(*)')->from('core_oauth_access_token')
			->where(['userIdentifier' => self::$mcpUser->id, 'clientId' => self::$client->id])->single());
	}

	public function testKeyTokenStillWorksNextToOAuth(): void
	{
		$token = self::createMcpKey(['Note' => false])->getToken();

		$this->assertSame(200, $this->callTool($token, 'whoami')['status']);
	}

	public function testTokenWithoutMcpScopeIsRefused(): void
	{
		// A client of an administrator, approved for the user, but the token is only for OpenID Connect
		$client = new OauthClient();
		$client->setIdentifier(uniqid('admin'));
		$client->setName('Single sign-on');
		$client->setRedirectUri(self::REDIRECT_URI);
		$client->setIsConfidential(false);
		$this->assertTrue($client->save());
		try {
			go()->setAuthState(new TemporaryState(self::$mcpUser->id));
			OAuth::grant($client, (int) self::$mcpUser->id, [(string) Note::entityType()->getId()]);

			// The ID token of OpenID Connect needs the host
			$host = $_SERVER['HTTP_HOST'] ?? null;
			$_SERVER['HTTP_HOST'] = 'localhost';
			try {
				$token = $this->accessToken('openid', $client);
			} finally {
				if ($host === null) {
					unset($_SERVER['HTTP_HOST']);
				} else {
					$_SERVER['HTTP_HOST'] = $host;
				}
			}

			$this->assertSame(401, $this->callTool($token, 'whoami')['status']);
		} finally {
			OauthClient::delete(['id' => $client->id]);
		}
	}

	public function testApprovalNeedsAccessToTheModule(): void
	{
		$server = OAuthServer::authorizationServer(self::$privateKeyPath);
		$authRequest = OAuthServer::validateAuthorizationRequest($server, $this->authorizationRequest(OAuth::SCOPE));

		// Without a license only administrators may use the module
		$this->expectException(OAuthServerException::class);
		OAuth::onConsent($authRequest, (int) self::$mcpUser->id, ['mcp' => ['entities' => [(string) Note::entityType()->getId()]]]);
	}

	public function testApprovingAgainUpdatesTheKey(): void
	{
		$first = $this->grant();
		$second = $this->grant();

		$this->assertSame($first->id, $second->id);
		$this->assertNull($second->getToken(), 'A key of an OAuth client has no secret');
	}

	public function testGrantWithWriteAccess(): void
	{
		go()->setAuthState(new TemporaryState(self::$mcpUser->id));
		$note = (string) Note::entityType()->getId();
		$book = (string) \go\modules\community\notes\model\NoteBook::entityType()->getId();

		$key = OAuth::grant(self::$client, (int) self::$mcpUser->id, [['entityTypeId' => $note, 'mayWrite' => true], ['entityTypeId' => $book, 'mayWrite' => false]]);
		$mayWrite = array_column(array_map(fn($e) => ['id' => $e->entityTypeId, 'w' => $e->mayWrite], $key->entities), 'w', 'id');
		$this->assertSame([$note => true, $book => false], $mayWrite);

		// Plain IDs give read access
		$key = OAuth::grant(self::$client, (int) self::$mcpUser->id, [$note]);
		$this->assertFalse($key->entities[0]->mayWrite);
	}
}
