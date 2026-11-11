<?php
namespace go\core\auth;

use go\core\exception\Forbidden;
use go\core\model\User;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

/**
 * Like an IMAP or LDAP authenticator: slow, so successful logins are cached
 */
class CachedPasswordAuthenticator extends PrimaryAuthenticator
{
	public static int $userId = 0;

	public static function isAvailableFor(string $username): bool
	{
		return true;
	}

	public function authenticate(string $username, string $password): bool|User
	{
		return $password === 'right' ? User::find(['id', 'username', 'password', 'enabled'], true)->where(['id' => self::$userId])->single() : false;
	}

	public function needsCache(): bool
	{
		return true;
	}
}

/**
 * Successful password logins are cached for a while. A disabled user must still be refused, and the cache must not
 * reveal passwords.
 */
class PasswordLoginCacheTest extends TestCase
{
	private User $user;

	protected function setUp(): void
	{
		$name = uniqid('logincache');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), var_export($this->user->getValidationErrors(), true));
		CachedPasswordAuthenticator::$userId = $this->user->id;
	}

	protected function tearDown(): void
	{
		go()->getCache()->delete('login-' . md5($this->user->username . '|right'));
		User::delete(['id' => $this->user->id]);
	}

	private function newAuthenticate(): Authenticate
	{
		$auth = new Authenticate();
		foreach (['primaryAuthenticators' => [new CachedPasswordAuthenticator()], 'secondaryAuthenticators' => [], 'externalAuthenticators' => []] as $name => $value) {
			(new ReflectionProperty(Authenticate::class, $name))->setValue($auth, $value);
		}
		return $auth;
	}

	public function testDisabledUserIsRefusedWhenLoginIsCached(): void
	{
		$this->assertNotFalse($this->newAuthenticate()->passwordLogin($this->user->username, 'right'));

		$user = User::findById($this->user->id);
		$user->enabled = false;
		$this->assertTrue($user->save());

		$this->expectException(Forbidden::class);
		$this->newAuthenticate()->passwordLogin($this->user->username, 'right');
	}

	public function testCacheKeyIsNotAPlainHashOfThePassword(): void
	{
		$this->assertNotFalse($this->newAuthenticate()->passwordLogin($this->user->username, 'right'));

		// somebody who can read the cache could check passwords against such a key
		$this->assertNull(go()->getCache()->get('login-' . md5($this->user->username . '|right')));
		// but it's still cached
		$this->assertNotFalse($this->newAuthenticate()->passwordLogin($this->user->username, 'right'));
	}
}
