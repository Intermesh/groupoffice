<?php
namespace go\core\oauth\server;

use go\core\event\Listeners;
use go\core\model\OauthClient;
use go\core\oauth\server\repositories\ClientRepository;
use go\core\util\ArrayObject;
use GuzzleHttp\Psr7\ServerRequest;
use InvalidArgumentException;
use League\OAuth2\Server\Exception\OAuthServerException;
use PHPUnit\Framework\TestCase;

/**
 * Clients may only register themselves while a module offers a scope for them. They are public, may only ask for
 * module scopes, and a metadata document is only fetched from public addresses.
 */
class ClientRegistrationTest extends TestCase
{
	const DOCUMENT_URL = 'https://assistant.example.com/oauth/client.json';

	private int $fetches = 0;

	public static function addModuleScope(ArrayObject $scopes): void
	{
		$scopes['testscope'] = ['description' => 'Test scope', 'resource' => 'https://localhost/api/page.php/test/resource'];
	}

	protected function setUp(): void
	{
		$this->fetches = 0;
		$this->forgetFailedFetch();
	}

	private function forgetFailedFetch(): void
	{
		go()->getCache()->delete('oauth-metadata-failed-' . sha1(self::DOCUMENT_URL));
	}

	protected function tearDown(): void
	{
		ClientRegistration::$fetcher = null;
		Listeners::get()->clear();
		go()->getDbConnection()->delete('core_oauth_client', 'registration IS NOT NULL')->execute();
	}

	private function allowRegistration(): void
	{
		OAuthServer::on(OAuthServer::EVENT_SCOPES, self::class, 'addModuleScope');
	}

	private function serveDocument(array $document): void
	{
		ClientRegistration::$fetcher = function (string $url) use ($document) {
			$this->fetches++;
			return $document;
		};
	}

	private function validDocument(): array
	{
		return [
			'client_id' => self::DOCUMENT_URL,
			'client_name' => 'Example <b>assistant</b>',
			'redirect_uris' => ['https://assistant.example.com/callback', 'http://127.0.0.1:3000/callback'],
			'token_endpoint_auth_method' => 'none'
		];
	}

	public function testNotAllowedWithoutModuleScope(): void
	{
		$this->serveDocument($this->validDocument());

		$this->assertFalse(ClientRegistration::isAllowed());
		$this->assertArrayNotHasKey('registration_endpoint', OAuthServer::metadata());
		$this->assertArrayNotHasKey('client_id_metadata_document_supported', OAuthServer::metadata());
		$this->assertNull((new ClientRepository())->getClientEntity(self::DOCUMENT_URL));
		$this->assertSame(0, $this->fetches);
	}

	public function testDynamicRegistration(): void
	{
		$this->allowRegistration();
		$this->assertArrayHasKey('registration_endpoint', OAuthServer::metadata());

		$response = ClientRegistration::register([
			'client_name' => 'Assistant',
			'redirect_uris' => ['https://assistant.example.com/callback']
		]);

		$client = (new ClientRepository())->getClientEntity($response['client_id']);
		$this->assertNotNull($client);
		$this->assertFalse($client->isConfidential());
		$this->assertTrue(OAuthServer::needsConsent($client));
		$this->assertSame(['https://assistant.example.com/callback'], $client->getRedirectUri());
		$this->assertSame('none', $response['token_endpoint_auth_method']);
	}

	public static function invalidRegistrations(): array
	{
		return [
			'no redirect URI' => [[], 'invalid_redirect_uri'],
			'HTTP to another computer' => [['redirect_uris' => ['http://assistant.example.com/callback']], 'invalid_redirect_uri'],
			'javascript' => [['redirect_uris' => ['javascript:alert(1)']], 'invalid_redirect_uri'],
			'fragment' => [['redirect_uris' => ['https://assistant.example.com/cb#x']], 'invalid_redirect_uri'],
			'secret' => [['redirect_uris' => ['https://assistant.example.com/cb'], 'token_endpoint_auth_method' => 'client_secret_basic'], 'invalid_client_metadata']
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('invalidRegistrations')]
	public function testInvalidRegistration(array $metadata, string $error): void
	{
		$this->allowRegistration();

		try {
			ClientRegistration::register($metadata);
			$this->fail('The registration was accepted');
		} catch (InvalidArgumentException $e) {
			$this->assertSame($error, $e->getMessage());
		}
	}

	public function testMetadataDocument(): void
	{
		$this->allowRegistration();
		$this->serveDocument($this->validDocument());

		$client = (new ClientRepository())->getClientEntity(self::DOCUMENT_URL);

		$this->assertNotNull($client);
		$this->assertSame('Example assistant', $client->getName());
		$this->assertSame('metadata', $client->getRegistration());
		$this->assertCount(2, $client->getRedirectUri());

		// Not fetched again within a day
		(new ClientRepository())->getClientEntity(self::DOCUMENT_URL);
		$this->assertSame(1, $this->fetches);
	}

	public function testInvalidMetadataDocuments(): void
	{
		$this->allowRegistration();

		$this->serveDocument(['client_id' => 'https://other.example.com/client.json'] + $this->validDocument());
		$this->assertNull((new ClientRepository())->getClientEntity(self::DOCUMENT_URL), 'a document about another client');

		$this->forgetFailedFetch();
		$this->serveDocument(['redirect_uris' => ['http://evil.example.com/cb']] + $this->validDocument());
		$this->assertNull((new ClientRepository())->getClientEntity(self::DOCUMENT_URL), 'a redirect over plain HTTP');

		$this->assertFalse(ClientRegistration::isMetadataUrl('http://assistant.example.com/client.json'));
		$this->assertFalse(ClientRegistration::isMetadataUrl('https://assistant.example.com'));
		$this->assertFalse(ClientRegistration::isMetadataUrl('https://assistant.example.com:8443/client.json'), 'another port');
		$this->assertFalse(ClientRegistration::isMetadataUrl('https://user@assistant.example.com/client.json'), 'credentials');
		$this->assertFalse(ClientRegistration::isMetadataUrl('https://assistant.example.com/client.json#x'), 'fragment');
		$this->assertTrue(ClientRegistration::isMetadataUrl('https://assistant.example.com:443/client.json'));
	}

	public function testFailedDocumentIsNotFetchedAgainSoon(): void
	{
		$this->allowRegistration();
		$this->serveDocument(['client_id' => 'https://other.example.com/client.json'] + $this->validDocument());

		(new ClientRepository())->getClientEntity(self::DOCUMENT_URL);
		(new ClientRepository())->getClientEntity(self::DOCUMENT_URL);

		$this->assertSame(1, $this->fetches);
	}

	/**
	 * The client chooses the URL of its metadata document, so it may not reach internal servers. The guard itself is
	 * tested in ClientTest.
	 */
	public function testMetadataDocumentIsFetchedWithTheInternalAddressGuard(): void
	{
		$http = ClientRegistration::httpClient();

		$this->assertTrue($http->globalRangeOnly);
		$this->assertSame(0, $http->maxRedirects);
		$this->assertSame(ClientRegistration::MAX_DOCUMENT_SIZE, $http->maxResponseSize);
	}

	public function testSelfRegisteredClientMayOnlyAskForModuleScopes(): void
	{
		$this->allowRegistration();
		$response = ClientRegistration::register(['redirect_uris' => ['https://assistant.example.com/callback']]);

		$keyFile = go()->getTmpFolder()->getFile(uniqid('oauth-test-key') . '.pem');
		$keyFile->getFolder()->create();
		openssl_pkey_export_to_file(openssl_pkey_new(['private_key_bits' => 2048]), $keyFile->getPath());
		$keyFile->chmod(0600);
		$server = OAuthServer::authorizationServer($keyFile->getPath());
		$keyFile->delete();

		$request = fn(string $scope) => (new ServerRequest('GET', 'https://localhost/api/oauth.php/authorize'))->withQueryParams([
			'response_type' => 'code',
			'client_id' => $response['client_id'],
			'redirect_uri' => 'https://assistant.example.com/callback',
			'scope' => $scope,
			'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', str_repeat('v', 43), true)), '+/', '-_'), '='),
			'code_challenge_method' => 'S256'
		]);

		try {
			OAuthServer::validateAuthorizationRequest($server, $request('openid testscope'));
			$this->fail('A self-registered client got the openid scope');
		} catch (OAuthServerException $e) {
			$this->assertSame('invalid_scope', $e->getErrorType());
		}

		$this->assertNotNull(OAuthServer::validateAuthorizationRequest($server, $request('testscope')));
	}

	public function testSelfRegisteredClientStopsWorkingWhenRegistrationIsNotAllowed(): void
	{
		$this->allowRegistration();
		$response = ClientRegistration::register(['redirect_uris' => ['https://assistant.example.com/callback']]);

		// The module is uninstalled or its license expired
		Listeners::get()->clear();

		$this->assertNull((new ClientRepository())->getClientEntity($response['client_id']));
	}

	public function testRegistrationsAndFetchesAreLimitedPerAddress(): void
	{
		$ip = '198.51.100.' . random_int(1, 254) . '-' . uniqid();
		for ($i = 0; $i < ClientRegistration::MAX_REGISTRATIONS_PER_HOUR; $i++) {
			$this->assertTrue(ClientRegistration::mayRegister($ip));
		}
		$this->assertFalse(ClientRegistration::mayRegister($ip));

		for ($i = 0; $i < ClientRegistration::MAX_FETCHES_PER_HOUR; $i++) {
			$this->assertTrue(ClientRegistration::mayFetch($ip));
		}
		$this->assertFalse(ClientRegistration::mayFetch($ip));
	}
}
