<?php
namespace go\base;

use go\core\model\User;
use PHPUnit\Framework\TestCase;

/**
 * The site module used to remember a login with the username and password in the GO_UN and GO_PW cookies. These
 * cookies may no longer log a user in and are removed.
 */
class SessionTest extends TestCase
{
	private User $user;
	private array $cookies;
	private $sessionUserId;

	protected function setUp(): void
	{
		$this->cookies = $_COOKIE;
		$this->sessionUserId = \GO::session()->values['user_id'] ?? null;

		$name = uniqid('cookieuser');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), $this->user->getValidationErrorsAsString());
	}

	protected function tearDown(): void
	{
		$_COOKIE = $this->cookies;
		if ($this->sessionUserId === null) {
			unset(\GO::session()->values['user_id']);
		} else {
			\GO::session()->values['user_id'] = $this->sessionUserId;
		}
		User::delete(['id' => $this->user->id]);
	}

	public function testPasswordCookiesDoNotLogIn(): void
	{
		unset(\GO::session()->values['user_id']);
		$_COOKIE['GO_UN'] = $this->user->username;
		$_COOKIE['GO_PW'] = 'Pa55word!!xyz';

		// setcookie() warns on the command line that headers were already sent
		set_error_handler(fn() => true, E_WARNING);
		try {
			$user = \GO::session()->loginWithCookies();
		} finally {
			restore_error_handler();
		}

		$this->assertNull($user);
		$this->assertNotEquals($this->user->id, \GO::session()->values['user_id'] ?? null);
		$this->assertArrayNotHasKey('GO_PW', $_COOKIE, "The password cookie must be removed");
		$this->assertArrayNotHasKey('GO_UN', $_COOKIE);
	}
}
