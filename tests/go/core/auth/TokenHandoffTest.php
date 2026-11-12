<?php
namespace go\core\auth;

use DateTimeZone;
use go\core\model\Token;
use go\core\model\User;
use go\core\util\DateTime;
use PHPUnit\Framework\TestCase;

/**
 * index.php accepts a posted access token, so the multi_instance module can log in to an instance directly. Another
 * website must not be able to post the access token of its own login to log a user in to that account.
 */
class TokenHandoffTest extends TestCase
{
	private User $user;
	private Token $login;

	protected function setUp(): void
	{
		$name = uniqid('handoff');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), var_export($this->user->getValidationErrors(), true));

		// a normal login
		$this->login = new Token();
		$this->login->userId = $this->user->id;
		$this->assertTrue($this->login->setAuthenticated(false));
	}

	protected function tearDown(): void
	{
		User::delete(['id' => $this->user->id]);
	}

	/**
	 * Like multi_instance's Instance::createAccessToken()
	 */
	private function createHandoffToken(string $created = 'now'): string
	{
		$utc = new DateTimeZone('UTC');
		$accessToken = uniqid() . bin2hex(random_bytes(16));
		go()->getDbConnection()->insert('core_auth_token', [
			'loginToken' => uniqid() . bin2hex(random_bytes(16)),
			'accessToken' => $accessToken,
			'CSRFToken' => uniqid() . bin2hex(random_bytes(16)),
			'expiresAt' => (new DateTime($created, $utc))->modify('+1 hour'),
			'userId' => $this->user->id,
			'createdAt' => new DateTime($created, $utc),
			'clientId' => $this->login->clientId
		])->execute();

		return $accessToken;
	}

	public function testTokenOfNormalLoginIsRefused(): void
	{
		$this->assertNull(Token::findHandoff($this->login->accessToken));
	}

	public function testHandoffTokenCanBeUsedOnce(): void
	{
		$accessToken = $this->createHandoffToken();

		$token = Token::findHandoff($accessToken);
		$this->assertNotNull($token);
		$this->assertEquals($this->user->id, $token->userId);

		$this->assertTrue($token->rotate());
		$this->assertNotEquals($accessToken, $token->accessToken);

		$this->assertNull(Token::findHandoff($accessToken));
		$this->assertNull(Token::find()->where(['accessToken' => $accessToken])->single());
	}

	public function testOldHandoffTokenIsRefused(): void
	{
		$this->assertNull(Token::findHandoff($this->createHandoffToken('-10 minutes')));
	}

	public function testUnknownTokenIsRefused(): void
	{
		$this->assertNull(Token::findHandoff('unknown'));
	}
}
