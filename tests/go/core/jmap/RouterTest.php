<?php
namespace go\core\jmap;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
	private $authState;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
	}

	protected function tearDown(): void
	{
		go()->setAuthState($this->authState);
	}

	private function callRouter(string $method, array $params = []): array
	{
		[$output, $responses] = $this->callRouterMulti([[$method, $params, 'c1']]);
		return [$output, end($responses)];
	}

	/**
	 * Run several method calls in one request
	 *
	 * @return array [output, responses of these calls]
	 */
	private function callRouterMulti(array $calls): array
	{
		// not logged in, like an anonymous web request
		unset($_COOKIE['accessToken']);
		go()->setAuthState(new State());

		ob_start();
		(new Router())->run($calls, false);
		$output = ob_get_clean();

		return [$output, array_slice(Response::get()->getData(), -count($calls))];
	}

	public static function cliMethods(): array
	{
		return [
			['core/cli/System/checkLicense'],
			['core/cli/System/upgrade'],
			['core/cli/System/runCron'],
			['core/CLI/System/checkLicense'],
			['community/addressbook/cli/AddressBook/checkLicense'],
		];
	}

	#[DataProvider('cliMethods')]
	public function testCliControllersAreNeverRoutedToByJmap(string $method)
	{
		[$output, $response] = $this->callRouter($method);

		$this->assertSame('', $output, 'The controller method must not have run');
		$this->assertSame('error', $response[0]);
		$this->assertStringContainsString('not found', $response[1]['message']);
	}

	public function testRegularMethodsStillRoute()
	{
		[, $response] = $this->callRouter('Core/echo', ['foo' => 'bar']);

		$this->assertSame(['foo' => 'bar'], $response[1]);
	}

	public static function invalidMethods(): array
	{
		return [
			// magic methods
			['User/__construct'],
			['community/notes/Note/__construct'],
			// a "\" in the route could reach classes outside the controller namespace
			['community/notes/controller\\Note/query'],
		];
	}

	#[DataProvider('invalidMethods')]
	public function testOnlyPublicControllerMethodsAreRouted(string $method)
	{
		[$output, $response] = $this->callRouter($method);

		$this->assertSame('', $output, 'The controller method must not have run');
		$this->assertSame('error', $response[0]);
		$this->assertStringContainsString('Bad request', $response[1]['message']);
	}

	public function testControllerAuthenticates()
	{
		[, $response] = $this->callRouter('User/get');

		$this->assertSame('error', $response[0]);
		$this->assertStringContainsString('Unauthorized', $response[1]['message']);
	}

	public static function switchUserMethods(): array
	{
		return [
			['community/test/SwitchUser/whoAmI'],
			['community/test/SwitchUser/fail'],
		];
	}

	/**
	 * A controller that authenticates with its own token, like WOPI, must not authenticate the next methods
	 */
	#[DataProvider('switchUserMethods')]
	public function testMethodCannotChangeUserForNextMethods(string $method)
	{
		[, $responses] = $this->callRouterMulti([
			[$method, [], 'c1'],
			['User/get', ['ids' => [1]], 'c2']
		]);

		$this->assertSame('error', $responses[1][0]);
		$this->assertStringContainsString('Unauthorized', $responses[1][1]['message']);
		$this->assertFalse(go()->getAuthState()->isAuthenticated());
	}

	public function testMethodCannotChangeLegacySessionUser()
	{
		$hadSession = isset($_SESSION['GO_SESSION']);
		$hadUserId = $hadSession && array_key_exists('user_id', $_SESSION['GO_SESSION']);
		$userId = $_SESSION['GO_SESSION']['user_id'] ?? null;
		unset($_SESSION['GO_SESSION']['user_id']);

		try {
			[, $responses] = $this->callRouterMulti([['community/test/SwitchUser/whoAmI', [], 'c1']]);

			// The method itself ran as the switched user
			$this->assertEquals(1, $responses[0][1]['legacyUserId']);
			$this->assertArrayNotHasKey('user_id', $_SESSION['GO_SESSION']);
		} finally {
			if ($hadUserId) {
				$_SESSION['GO_SESSION']['user_id'] = $userId;
			} else if(!$hadSession) {
				unset($_SESSION['GO_SESSION']);
			}
		}
	}

	public function testEntityControllerClass(): void
	{
		$this->assertEquals(
			\go\modules\community\notes\controller\Note::class,
			Router::entityControllerClass(\go\modules\community\notes\model\Note::entityType())
		);
	}

	public function testGetEntityMethods(): void
	{
		$methods = Router::getEntityMethods(\go\modules\community\notes\model\Note::entityType());

		foreach (['get', 'query', 'set', 'changes'] as $method) {
			$this->assertArrayHasKey($method, $methods);
		}
		$this->assertEquals(Router::STANDARD_METHODS['get'], $methods['get']);

		// Constructors, static and non public methods can't be called through JMAP
		$this->assertArrayNotHasKey('__construct', $methods);
		$this->assertArrayNotHasKey('onEntitySave', $methods);
		$this->assertArrayNotHasKey('canDestroy', $methods);
	}

	public function testListedMethodsAreRoutable(): void
	{
		$type = \go\modules\community\notes\model\Note::entityType();
		$controller = Router::entityControllerClass($type);

		$this->assertTrue(Router::isRoutableController($controller));
		foreach (array_keys(Router::getEntityMethods($type)) as $method) {
			$this->assertTrue((new \ReflectionMethod($controller, $method))->isPublic(), $method);
		}
	}
}
