<?php
namespace go\core\auth;

use go\core\model\User;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

/**
 * Accepts every password, like an LDAP server that allows "unauthenticated binds" (RFC 4513 5.1.2): a bind with a
 * valid DN and an empty password succeeds without checking a password.
 */
class AcceptAnyPasswordAuthenticator extends PrimaryAuthenticator
{
	public static function isAvailableFor(string $username): bool
	{
		return true;
	}

	public function authenticate(string $username, string $password): bool|User
	{
		return User::findById(1);
	}

	public function needsCache(): bool
	{
		return false;
	}
}

/**
 * An empty password must never reach an authenticator. It's reachable via DAV with "Authorization: Basic" and an
 * empty password.
 */
class EmptyPasswordTest extends TestCase
{
	private bool $forceAppPasswords;

	protected function setUp(): void
	{
		$this->forceAppPasswords = go()->getSettings()->forceAppPasswords;
		go()->getSettings()->forceAppPasswords = false;
	}

	protected function tearDown(): void
	{
		go()->getSettings()->forceAppPasswords = $this->forceAppPasswords;
	}

	private function newAuthenticate(): Authenticate
	{
		$auth = new Authenticate();
		foreach (['primaryAuthenticators' => [new AcceptAnyPasswordAuthenticator()], 'secondaryAuthenticators' => [], 'externalAuthenticators' => []] as $name => $value) {
			(new ReflectionProperty(Authenticate::class, $name))->setValue($auth, $value);
		}
		return $auth;
	}

	public function testNonEmptyPasswordReachesAuthenticator()
	{
		$user = $this->newAuthenticate()->passwordLogin('admin', 'secret');
		$this->assertInstanceOf(User::class, $user);
		$this->assertEquals(1, $user->id);
	}

	public function testEmptyPasswordIsRejected()
	{
		$this->assertFalse($this->newAuthenticate()->passwordLogin('admin', ''));
	}

	public function testEmptyPasswordIsRejectedForDav()
	{
		$this->assertFalse($this->newAuthenticate()->appLogin('admin', '', 'dav'));
	}
}
