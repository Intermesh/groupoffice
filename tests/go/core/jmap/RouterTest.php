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
		// not logged in, like an anonymous web request
		unset($_COOKIE['accessToken']);
		go()->setAuthState(new State());

		ob_start();
		(new Router())->run([[$method, $params, 'c1']], false);
		$output = ob_get_clean();

		$data = Response::get()->getData();
		return [$output, end($data)];
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
}
