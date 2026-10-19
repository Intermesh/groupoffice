<?php
namespace go\core;

use go\core\model\Token;
use go\core\model\User;
use PHPUnit\Framework\TestCase;

class DebuggerOutputTest extends TestCase
{
	private bool $enabled;
	private ?string $requestMethod;
	private array $tokens = [];
	private array $userIds = [];

	protected function setUp(): void
	{
		$this->enabled = go()->getDebugger()->enabled;
		$this->requestMethod = $_SERVER['REQUEST_METHOD'] ?? null;
		$_SERVER['REQUEST_METHOD'] = 'GET';
		unset($_COOKIE['accessToken']);
	}

	protected function tearDown(): void
	{
		go()->getDebugger()->enabled = $this->enabled;
		unset($_COOKIE['accessToken']);

		// Don't leave it set: with it the settings think they run on a web server, which breaks other tests
		if ($this->requestMethod === null) {
			unset($_SERVER['REQUEST_METHOD']);
		} else {
			$_SERVER['REQUEST_METHOD'] = $this->requestMethod;
		}

		foreach ($this->tokens as $token) {
			Token::delete(['accessToken' => $token->accessToken]);
		}
		foreach ($this->userIds as $id) {
			User::delete(['id' => $id]);
		}
	}

	private function loginAs(int|string $userId): void
	{
		$token = new Token();
		$token->userId = $userId;
		$this->assertTrue($token->setAuthenticated(false));
		$this->tokens[] = $token;
		$_COOKIE['accessToken'] = $token->accessToken;
	}

	private function newUser(): User
	{
		$name = uniqid('dbg');
		$user = new User();
		$user->setValues(['username' => $name, 'displayName' => $name, 'email' => $name . '@example.com', 'password' => 'Pa55word!!xyz']);
		$this->assertTrue($user->save(), var_export($user->getValidationErrors(), true));
		$this->userIds[] = $user->id;

		return $user;
	}

	public function testNotWhenDebuggingIsDisabled()
	{
		go()->getDebugger()->enabled = false;
		$this->loginAs(User::ID_SUPER_ADMIN);

		$this->assertFalse(go()->getDebugger()->mayOutputToClient());
	}

	public function testNotForAnonymousUsers()
	{
		go()->getDebugger()->enabled = true;

		$this->assertFalse(go()->getDebugger()->mayOutputToClient());
	}

	public function testNotForUsersThatAreNotAdministrators()
	{
		go()->getDebugger()->enabled = true;
		$this->loginAs($this->newUser()->id);

		$this->assertFalse(go()->getDebugger()->mayOutputToClient());
	}

	public function testForAdministrators()
	{
		go()->getDebugger()->enabled = true;
		$this->loginAs(User::ID_SUPER_ADMIN);

		$this->assertTrue(go()->getDebugger()->mayOutputToClient());
	}

	public function testNotWithAnInvalidToken()
	{
		go()->getDebugger()->enabled = true;
		$_COOKIE['accessToken'] = 'not-a-token';

		$this->assertFalse(go()->getDebugger()->mayOutputToClient());
	}
}
