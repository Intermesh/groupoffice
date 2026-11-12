<?php
namespace go\modules\community\oidc;

use go\core\auth\TemporaryState;
use go\core\model\Module as ModuleModel;
use go\core\model\User;
use go\modules\community\oidc\model\Client;
use PHPUnit\Framework\TestCase;

/**
 * The callback of the identity provider must have the state that was stored in the session when the user was sent to
 * the provider. Otherwise somebody could make a user log in with the authorization code of another account.
 */
class ClientStateTest extends TestCase
{
	private static bool $installedByTest = false;
	private $authState;
	private array $request;
	private array $session;
	private Client $client;

	public static function setUpBeforeClass(): void
	{
		if (!ModuleModel::findByName('community', 'oidc')) {
			go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
			self::assertNotFalse(Module::get()->install(), 'Could not install the oidc module');
			self::$installedByTest = true;
		}
	}

	public static function tearDownAfterClass(): void
	{
		if (self::$installedByTest) {
			go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
			// Module::uninstall() switches off change tracking and history without switching them on again
			$trackChanges = \go\core\jmap\Entity::$trackChanges;
			$history = \go\modules\community\history\Module::$enabled;
			Module::get()->uninstall();
			\go\core\jmap\Entity::$trackChanges = $trackChanges;
			\go\modules\community\history\Module::$enabled = $history;
			self::$installedByTest = false;
		}
	}

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		$this->request = $_REQUEST;
		$this->session = $_SESSION ?? [];

		$this->client = new Client();
		$this->client->name = uniqid('oidcstate');
		$this->client->url = 'https://idp.invalid';
		$this->client->clientId = 'client';
		$this->client->setClientSecret('secret');
		$this->assertTrue($this->client->save(), var_export($this->client->getValidationErrors(), true));

		// No discovery request: the token endpoint can't be reached
		$_SESSION['oidc_wellknown_' . $this->client->id] = [
			'authorization_endpoint' => 'https://idp.invalid/auth',
			'token_endpoint' => 'https://idp.invalid/token',
			'userinfo_endpoint' => 'https://idp.invalid/userinfo',
		];
	}

	protected function tearDown(): void
	{
		Client::delete(['id' => $this->client->id]);
		$_REQUEST = $this->request;
		$_SESSION = $this->session;
		go()->setAuthState($this->authState);
	}

	private function returnFromProvider(?string $sessionState, mixed $requestState): string
	{
		if ($sessionState === null) {
			unset($_SESSION['oidc_state']);
		} else {
			$_SESSION['oidc_state'] = $sessionState;
		}
		$_REQUEST['code'] = 'code';
		if ($requestState === null) {
			unset($_REQUEST['state']);
		} else {
			$_REQUEST['state'] = $requestState;
		}

		try {
			$this->client->authenticate();
			return 'authenticated';
		} catch (\Throwable $e) {
			return $e->getMessage();
		}
	}

	public function testEmptyStateWithoutSessionStateIsRefused(): void
	{
		$this->assertSame('Unable to determine state', $this->returnFromProvider(null, ''));
	}

	public function testWrongStateIsRefused(): void
	{
		$this->assertSame('Unable to determine state', $this->returnFromProvider('expected', 'other'));
		$this->assertSame('Unable to determine state', $this->returnFromProvider('expected', ['expected']));
	}

	public function testStateCanOnlyBeUsedOnce(): void
	{
		// the code exchange itself fails because the provider doesn't exist
		$this->assertNotSame('Unable to determine state', $this->returnFromProvider('expected', 'expected'));
		$this->assertArrayNotHasKey('oidc_state', $_SESSION);

		$_REQUEST['state'] = 'expected';
		try {
			$this->client->authenticate();
			$this->fail('The state was accepted twice');
		} catch (\Exception $e) {
			$this->assertSame('Unable to determine state', $e->getMessage());
		}
	}
}
