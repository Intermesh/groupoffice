<?php
namespace go\core\jmap;

use go\core\model\RememberMe;
use go\core\model\Token;
use go\core\model\User;
use go\core\util\DateTime;
use PHPUnit\Framework\TestCase;

class StateTest extends TestCase
{
	private array $userIds = [];
	private $requestMethod;

	protected function setUp(): void
	{
		$this->requestMethod = $_SERVER['REQUEST_METHOD'] ?? null;
		$_SERVER['REQUEST_METHOD'] = 'GET';
	}

	protected function tearDown(): void
	{
		foreach ($this->userIds as $id) {
			User::delete(['id' => $id]);
		}
		unset($_COOKIE['accessToken']);

		if ($this->requestMethod === null) {
			unset($_SERVER['REQUEST_METHOD']);
		} else {
			$_SERVER['REQUEST_METHOD'] = $this->requestMethod;
		}
	}

	private function newUser(): User
	{
		$name = uniqid('st');
		$user = new User();
		$user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($user->save(), var_export($user->getValidationErrors(), true));
		$this->userIds[] = $user->id;

		return $user;
	}

	private function newToken(User $user): Token
	{
		$token = new Token();
		$token->userId = $user->id;
		$this->assertTrue($token->setAuthenticated(false));

		return $token;
	}

	/**
	 * Like a new request that has the token in its cookie
	 */
	private function isAuthenticatedWith(Token $token): bool
	{
		$_COOKIE['accessToken'] = $token->accessToken;

		return (new State())->isAuthenticated();
	}

	private function forgetCache(Token $token): void
	{
		go()->getCache()->delete('token-' . $token->accessToken);
	}

	public function testFreshTokenIsAuthenticated()
	{
		$token = $this->newToken($this->newUser());

		$this->assertTrue($this->isAuthenticatedWith($token));
		// second time comes from the cache
		$this->assertTrue($this->isAuthenticatedWith($token));
	}

	public function testExpiredTokenInCacheIsRejected()
	{
		$token = $this->newToken($this->newUser());
		$this->assertTrue($this->isAuthenticatedWith($token));

		$cached = clone $token;
		$cached->expiresAt = new DateTime('-1 hour');
		go()->getCache()->set('token-' . $token->accessToken, $cached);

		$expiredInDb = go()->getDbConnection()->exec(
			"update core_auth_token set expiresAt = '2000-01-01 00:00:00' where accessToken = '" . $token->accessToken . "'"
		);
		$this->assertSame(1, $expiredInDb);

		$this->assertFalse($this->isAuthenticatedWith($token));
		$this->assertNull(go()->getCache()->get('token-' . $token->accessToken), 'expired token must be dropped from the cache');
	}

	public function testTokenRemovedByGarbageCollectionIsRejected()
	{
		$token = $this->newToken($this->newUser());
		$this->assertTrue($this->isAuthenticatedWith($token));

		go()->getDbConnection()->exec(
			"update core_auth_token set expiresAt = '2000-01-01 00:00:00' where accessToken = '" . $token->accessToken . "'"
		);
		// the cached copy still has the old expiry. Garbage collection doesn't clear the cache.
		$cached = go()->getCache()->get('token-' . $token->accessToken);
		$cached->expiresAt = new DateTime('-1 hour');
		go()->getCache()->set('token-' . $token->accessToken, $cached);

		Token::collectGarbage();
		$this->assertNull(Token::find()->where(['accessToken' => $token->accessToken])->single());

		$this->assertFalse($this->isAuthenticatedWith($token));
	}

	public function testDisablingUserEndsSessionsImmediately()
	{
		$user = $this->newUser();
		$token = $this->newToken($user);
		$this->assertTrue($this->isAuthenticatedWith($token));

		$user = User::findById($user->id);
		$user->enabled = false;
		$this->assertTrue($user->save());

		// no need to wait for the cache to expire
		$this->assertFalse($this->isAuthenticatedWith($token));
	}

	public function testDisablingUserRemovesRememberMe()
	{
		$user = $this->newUser();
		// logging in creates the client that the remember me login belongs to
		$this->newToken($user);

		$rememberMe = new RememberMe();
		$rememberMe->userId = $user->id;
		$this->assertTrue($rememberMe->save(), var_export($rememberMe->getValidationErrors(), true));
		$cookie = $rememberMe->getToken();

		$this->assertNotFalse(RememberMe::verify($cookie));

		$user = User::findById($user->id);
		$user->enabled = false;
		$this->assertTrue($user->save());

		$this->assertFalse(RememberMe::verify($cookie));
	}

	public function testTokenThatNeverExpiresIsRefusedWhileUserIsDisabled()
	{
		$user = $this->newUser();
		$token = $this->newToken($user);
		// like an API key
		$token->expiresAt = null;
		$this->assertTrue($token->save());
		$this->forgetCache($token);

		$this->assertTrue($this->isAuthenticatedWith($token));

		$u = User::findById($user->id);
		$u->enabled = false;
		$this->assertTrue($u->save());

		// not deleted by disabling, but refused as soon as it's not served from the cache anymore
		$this->forgetCache($token);
		$this->assertFalse($this->isAuthenticatedWith($token));

		$u = User::findById($user->id);
		$u->enabled = true;
		$this->assertTrue($u->save());

		$this->forgetCache($token);
		$this->assertTrue($this->isAuthenticatedWith($token), 'works again after enabling the user');
	}

	public function testTokenIsCachedWithTtl()
	{
		$token = $this->newToken($this->newUser());
		$this->forgetCache($token);
		$this->assertTrue($this->isAuthenticatedWith($token));

		if (function_exists('apcu_enabled') && apcu_enabled()) {
			$entries = iterator_to_array(new \APCUIterator('/-token-' . preg_quote($token->accessToken, '/') . '$/'));
			$this->assertCount(1, $entries);
			$entry = reset($entries);
			$this->assertSame(Token::CACHE_TTL, $entry['ttl']);
			return;
		}

		$file = go()->getDataFolder()->getFolder('cache/disk')->getFile('token-' . $token->accessToken);
		$this->assertTrue($file->exists());
		$data = unserialize($file->getContents(), ['allowed_classes' => true]);

		$this->assertIsArray($data);
		$this->assertArrayHasKey('e', $data, 'cache entry must have an expiry time');
		$this->assertLessThanOrEqual(time() + Token::CACHE_TTL, $data['e']);
	}
}
