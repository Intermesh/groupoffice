<?php
namespace go\core\jmap;

use go\core\model\Token;
use go\core\model\User;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

/**
 * A request with the access token in a cookie must send the exact CSRF token of the session.
 */
class StateCsrfTest extends TestCase
{
	private User $user;
	private Token $token;
	private array $server;
	private array $request;
	private array $cookie;
	private bool $csrfCheck;

	protected function setUp(): void
	{
		$this->server = $_SERVER;
		$this->request = $_REQUEST;
		$this->cookie = $_COOKIE;
		$this->csrfCheck = State::$CSRFcheck;

		$name = uniqid('csrf');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), var_export($this->user->getValidationErrors(), true));

		$this->token = new Token();
		$this->token->userId = $this->user->id;
		$this->assertTrue($this->token->setAuthenticated(false));

		// a token that PHP's loose comparison considers equal to other numeric strings
		go()->getDbConnection()->update('core_auth_token', ['CSRFToken' => '1000'], ['accessToken' => $this->token->accessToken])->execute();
		go()->getCache()->delete('token-' . $this->token->accessToken);
	}

	protected function tearDown(): void
	{
		$_SERVER = $this->server;
		$_REQUEST = $this->request;
		$_COOKIE = $this->cookie;
		State::$CSRFcheck = $this->csrfCheck;
		go()->getCache()->delete('token-' . $this->token->accessToken);
		User::delete(['id' => $this->user->id]);
	}

	private function postWithCsrfToken(mixed $csrfToken): bool
	{
		(new ReflectionProperty(State::class, 'cookieAccessTokenUsed'))->setValue(null, false);
		State::$CSRFcheck = true;
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_COOKIE['accessToken'] = $this->token->accessToken;
		$_REQUEST['CSRFToken'] = $csrfToken;

		return (new State())->isAuthenticated();
	}

	public function testExactCsrfTokenIsAccepted(): void
	{
		$this->assertTrue($this->postWithCsrfToken('1000'));
	}

	public function testLooselyEqualCsrfTokenIsRefused(): void
	{
		$this->assertFalse($this->postWithCsrfToken('1e3'));
		$this->assertFalse($this->postWithCsrfToken('1000.0'));
	}

	public function testArrayCsrfTokenIsRefused(): void
	{
		$this->assertFalse($this->postWithCsrfToken(['1000']));
	}
}
