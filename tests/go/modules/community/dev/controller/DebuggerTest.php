<?php
namespace go\modules\community\dev\controller;

use go\core\jmap\Response;
use go\core\jmap\Router;
use go\core\jmap\State;
use PHPUnit\Framework\TestCase;

class DebuggerTest extends TestCase
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

	public function testDebugEntriesAreNotAvailableWithoutLoggingIn()
	{
		unset($_COOKIE['accessToken']);
		go()->setAuthState(new State());
		$this->assertFalse(go()->getAuthState()->isAuthenticated());

		(new Router())->run([['community/dev/Debugger/get', [], 'c1']], false);

		$data = Response::get()->getData();
		$response = end($data);

		$this->assertSame('error', $response[0]);
		$this->assertSame('unauthorized', $response[1]['type']);
	}
}
