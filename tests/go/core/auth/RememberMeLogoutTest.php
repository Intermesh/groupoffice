<?php
namespace go\core\auth;

use go\core\model\RememberMe;
use go\core\model\Token;
use go\core\model\User;
use PHPUnit\Framework\TestCase;

/**
 * A remember me login must end when the user logs out, and a cookie that this server didn't create must not be
 * treated as a stolen token.
 */
class RememberMeLogoutTest extends TestCase
{
	private User $user;
	private array $cookie;
	private $authState;

	protected function setUp(): void
	{
		$this->cookie = $_COOKIE;
		$this->authState = go()->getAuthState();

		$name = uniqid('remember');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), var_export($this->user->getValidationErrors(), true));

		// logging in creates the client that the remember me login belongs to
		$token = new Token();
		$token->userId = $this->user->id;
		$this->assertTrue($token->setAuthenticated(false));
	}

	protected function tearDown(): void
	{
		$_COOKIE = $this->cookie;
		go()->setAuthState($this->authState);
		User::delete(['id' => $this->user->id]);
	}

	private function rememberMe(): RememberMe
	{
		$rememberMe = new RememberMe();
		$rememberMe->userId = $this->user->id;
		$this->assertTrue($rememberMe->save(), var_export($rememberMe->getValidationErrors(), true));
		return $rememberMe;
	}

	private function countRememberMe(): int
	{
		return (int) go()->getDbConnection()->selectSingleValue('count(*)')->from('core_auth_remember_me')->where(['userId' => $this->user->id])->single();
	}

	public function testLogoutDeletesRememberMeOfCookie(): void
	{
		$rememberMe = $this->rememberMe();
		$other = $this->rememberMe();
		$cookie = $rememberMe->getToken();
		$this->assertSame(2, $this->countRememberMe());

		$_COOKIE['goRememberMe'] = $cookie;
		unset($_COOKIE['accessToken']);
		try {
			(new Authenticate())->logout();
		} catch (\ErrorException $e) {
			// removing the cookie fails on the command line because PHPUnit already wrote output
			$this->assertStringContainsString('headers already sent', $e->getMessage());
		}

		$this->assertSame(1, $this->countRememberMe(), 'only the remember me login of this browser is deleted');
		$this->assertFalse(RememberMe::verify($cookie));
		$this->assertNotFalse(RememberMe::verify($other->getToken()));
	}

	public static function invalidCookies(): array
	{
		return [
			'no token' => ['{series}'],
			'empty token' => ['{series}:'],
			'extra part' => ['{series}:abc:def'],
			'not hex' => ['{series}:xyz'],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('invalidCookies')]
	public function testInvalidCookieIsNotTreatedAsTheft(string $cookie): void
	{
		$rememberMe = $this->rememberMe();
		$cookie = str_replace('{series}', $rememberMe->series, $cookie);

		$this->assertFalse(RememberMe::verify($cookie));
		$this->assertSame(1, $this->countRememberMe(), 'the remember me login must not be deleted');
	}
}
