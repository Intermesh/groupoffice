<?php
namespace go\core\auth;

use go\core\model\Token;
use go\core\model\User;
use go\core\util\DateTime;
use PHPUnit\Framework\TestCase;

/**
 * The login token of api/auth.php identifies a login while the user passes the authenticators, like OTP. After the
 * login it must not give the access token and CSRF token again.
 */
class LoginTokenTest extends TestCase
{
	private User $user;

	protected function setUp(): void
	{
		$name = uniqid('logintoken');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), var_export($this->user->getValidationErrors(), true));
	}

	protected function tearDown(): void
	{
		User::delete(['id' => $this->user->id]);
	}

	/**
	 * Like a password login of a user that still has to enter an OTP code
	 */
	private function pendingLogin(): Token
	{
		$token = new Token();
		$token->userId = $this->user->id;
		$this->assertTrue($token->save());
		$this->assertFalse(isset($token->accessToken));

		return $token;
	}

	public function testPendingLoginIsFound(): void
	{
		$token = $this->pendingLogin();

		$found = Token::findPendingLogin($token->loginToken);
		$this->assertNotNull($found);
		$this->assertSame($token->loginToken, $found->loginToken);
	}

	public function testFinishedLoginIsNotFound(): void
	{
		$token = $this->pendingLogin();
		$this->assertTrue($token->setAuthenticated(false));
		$this->assertNotEmpty($token->accessToken);

		$this->assertNull(Token::findPendingLogin($token->loginToken));
	}

	public function testExpiredLoginIsNotFound(): void
	{
		$token = $this->pendingLogin();
		$token->expiresAt = new DateTime('-1 minute', new \DateTimeZone('UTC'));
		$this->assertTrue($token->save());

		$this->assertNull(Token::findPendingLogin($token->loginToken));
	}

	public function testUnknownLoginTokenIsNotFound(): void
	{
		$this->assertNull(Token::findPendingLogin('unknown'));
	}
}
