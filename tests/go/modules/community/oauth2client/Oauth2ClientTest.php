<?php
namespace go\modules\community\oauth2client;

use go\core\jmap\Response;
use go\core\jmap\Router;
use go\core\jmap\State;
use go\core\model\Module as ModuleModel;
use go\modules\community\oauth2client\model\Oauth2Client;
use PHPUnit\Framework\TestCase;

class Oauth2ClientTest extends TestCase
{
	private const SECRET = 'S3CR3T-do-not-leak';

	private static bool $installedByTest = false;
	private array $clientIds = [];
	private $authState;
	private ?string $url;

	public static function setUpBeforeClass(): void
	{
		// The module is not installed by default in the test environment
		if (!ModuleModel::findByName('community', 'oauth2client')) {
			// returns the module model on success
			self::assertNotFalse(Module::get()->install(), 'Could not install the oauth2client module');
			self::$installedByTest = true;
		}
	}

	public static function tearDownAfterClass(): void
	{
		if (self::$installedByTest) {
			Module::get()->uninstall();
			self::$installedByTest = false;
		}
	}

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();

		// Reading an entity also builds the OAuth2 provider (getProvider()) which needs the site URL
		$this->url = go()->getSettings()->URL ?? null;
		go()->getSettings()->URL = 'http://localhost/';
	}

	protected function tearDown(): void
	{
		go()->setAuthState($this->authState);
		go()->getSettings()->URL = $this->url;

		foreach ($this->clientIds as $id) {
			Oauth2Client::delete(['id' => $id]);
		}
	}

	private function newClient(array $values = []): Oauth2Client
	{
		$client = new Oauth2Client();
		$client->setValues(array_merge([
			'name' => uniqid('client'),
			'defaultClientId' => 1,
			'clientId' => 'client-id-123',
			'clientSecret' => self::SECRET,
			'projectId' => 'project'
		], $values));

		$this->assertTrue($client->save(), var_export($client->getValidationErrors(), true));
		$this->clientIds[] = $client->id;

		return $client;
	}

	private function storedSecret(string $id): ?string
	{
		return go()->getDbConnection()
			->selectSingleValue('clientSecret')
			->from('oauth2client_oauth2client')
			->where('id', '=', $id)
			->single();
	}

	/**
	 * Call the API without being logged in, like the login page does
	 */
	private function anonymousApiCall(string $method, array $params): array
	{
		unset($_COOKIE['accessToken']);
		go()->setAuthState(new State());
		$this->assertFalse(go()->getAuthState()->isAuthenticated());

		(new Router())->run([[$method, $params, 'c1']], false);

		$data = Response::get()->getData();
		return end($data);
	}

	public function testSecretIsNotPartOfTheEntityData()
	{
		$client = $this->newClient();

		$this->assertNull($client->getClientSecret());
		$this->assertStringNotContainsString(self::SECRET, json_encode($client));
		$this->assertStringNotContainsString(self::SECRET, json_encode($client->toArray()));
	}

	public function testSecretIsStoredAndUsedInternally()
	{
		$client = $this->newClient();

		$this->assertSame(self::SECRET, $this->storedSecret($client->id));

		$fresh = Oauth2Client::findById($client->id);
		$provider = $fresh->getProvider();
		$this->assertInstanceOf(\League\OAuth2\Client\Provider\Google::class, $provider);
		// protected property of the provider that is built with the secret
		$property = new \ReflectionProperty(\League\OAuth2\Client\Provider\AbstractProvider::class, 'clientSecret');
		$this->assertSame(self::SECRET, $property->getValue($provider));
	}

	public function testEmptySecretKeepsCurrentSecretWhenEditing()
	{
		$client = $this->newClient();

		// what the edit dialog does when only the name was changed and the secret field was left empty
		$fresh = Oauth2Client::findById($client->id);
		$fresh->setValues(['name' => 'renamed', 'clientSecret' => '']);
		$this->assertTrue($fresh->save());

		$this->assertSame('renamed', Oauth2Client::findById($client->id)->name);
		$this->assertSame(self::SECRET, $this->storedSecret($client->id));
	}

	public function testSecretCanBeChanged()
	{
		$client = $this->newClient();

		$fresh = Oauth2Client::findById($client->id);
		$fresh->setValues(['clientSecret' => 'a-new-secret']);
		$this->assertTrue($fresh->save());

		$this->assertSame('a-new-secret', $this->storedSecret($client->id));
	}

	public function testNewClientRequiresASecret()
	{
		foreach ([['clientSecret' => ''], []] as $override) {
			$client = new Oauth2Client();
			$client->setValues(array_merge([
				'name' => 'no secret',
				'defaultClientId' => 1,
				'clientId' => 'client-id-123',
				'projectId' => 'project'
			], $override));

			$this->assertFalse($client->save());
			$this->assertNotNull($client->getValidationError('clientSecret'));
		}
	}

	public function testApiDoesNotReturnTheSecretToAnonymousUsers()
	{
		$client = $this->newClient(['name' => 'anonymous-visible-name']);

		$get = $this->anonymousApiCall('Oauth2Client/get', ['ids' => [$client->id]]);
		$json = json_encode($get);

		// make sure the read really worked, otherwise the assertion below proves nothing
		$this->assertStringContainsString('anonymous-visible-name', $json, $json);
		$this->assertStringNotContainsString(self::SECRET, $json);

		$query = $this->anonymousApiCall('Oauth2Client/query', []);
		$this->assertStringNotContainsString(self::SECRET, json_encode($query));
	}

	public function testApiDoesNotLetAnonymousUsersChangeTheSecret()
	{
		$client = $this->newClient();

		$this->anonymousApiCall('Oauth2Client/set', ['update' => [$client->id => ['clientSecret' => 'hijacked']]]);

		$this->assertSame(self::SECRET, $this->storedSecret($client->id));
	}
}
