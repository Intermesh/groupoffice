<?php
namespace go\modules\community\dev\controller;

use go\core\auth\TemporaryState;
use go\core\http\Exception;
use go\core\jmap\State;
use PHPUnit\Framework\TestCase;

/**
 * The language export is a JMAP controller, so guests must not be able to run it. The constructor must authenticate.
 */
class LanguageTest extends TestCase
{
	private $authState;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
	}

	protected function tearDown(): void
	{
		go()->setAuthState($this->authState ?? new TemporaryState(1));
	}

	public function testGuestIsRefused()
	{
		// No access token in the test request, so this is a guest
		go()->setAuthState(new State());

		$this->expectException(Exception::class);
		$this->expectExceptionCode(401);
		new Language();
	}
}
