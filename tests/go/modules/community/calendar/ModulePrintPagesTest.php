<?php
namespace go\modules\community\calendar;

use go\core\auth\TemporaryState;
use go\core\exception\Forbidden;
use go\core\model\Token;
use go\modules\community\calendar\model\Calendar;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * The print pages are opened by the browser directly through api/page.php. Nothing checks the login before them.
 */
class ModulePrintPagesTest extends TestCase
{
	private $authState;
	private ?string $requestMethod;
	private ?Token $token = null;
	private ?Calendar $calendar = null;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->requestMethod = $_SERVER['REQUEST_METHOD'] ?? null;
		$_SERVER['REQUEST_METHOD'] = 'GET';
		unset($_COOKIE['accessToken']);
	}

	protected function tearDown(): void
	{
		unset($_COOKIE['accessToken']);

		// Don't leave it set: with it the settings think they run on a web server, which breaks other tests
		if ($this->requestMethod === null) {
			unset($_SERVER['REQUEST_METHOD']);
		} else {
			$_SERVER['REQUEST_METHOD'] = $this->requestMethod;
		}

		go()->setAuthState(new TemporaryState(1));

		if ($this->calendar) {
			Calendar::delete(['id' => $this->calendar->id]);
		}
		if ($this->token) {
			Token::delete(['accessToken' => $this->token->accessToken]);
		}

		go()->setAuthState($this->authState);
	}

	public function testPrintPageRequiresLogin()
	{
		$this->expectException(Forbidden::class);
		Module::get()->pagePrint('week', '2026-10-19');
	}

	public function testPrintRangePageRequiresLogin()
	{
		$this->expectException(Forbidden::class);
		Module::get()->pagePrintRange('week', 7, '2026-10-19');
	}

	public function testPrintListPageRequiresLogin()
	{
		$this->expectException(Forbidden::class);
		Module::get()->pagePrintList('2026-10-19', '2026-10-26');
	}

	public function testLoggedInUserGetsTheirCalendars()
	{
		go()->setAuthState(new TemporaryState(1));
		$this->calendar = new Calendar();
		$this->calendar->name = 'Print pages test ' . uniqid();
		$this->assertTrue($this->calendar->save());

		// really logged in: the print pages use the cookie like the browser sends it
		$this->token = new Token();
		$this->token->userId = 1;
		$this->assertTrue($this->token->setAuthenticated(false));
		$_COOKIE['accessToken'] = $this->token->accessToken;

		$method = new ReflectionMethod(Module::class, 'visibleCalendarIds');
		$ids = $method->invoke(Module::get());

		$this->assertContains((string) $this->calendar->id, array_map('strval', $ids));
	}
}
