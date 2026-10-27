<?php
namespace go\core\auth;

use go\core\Environment;
use go\core\exception\Forbidden;
use go\core\Singleton;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

/**
 * Not on the command line, where throttling is disabled
 */
class RecoveryWebEnvironment extends Environment
{
	public function isCli(): bool
	{
		return false;
	}
}

/**
 * Requests to send a password recovery message use the same throttle as failed logins, with scope "recovery", the way
 * api/auth.php uses it.
 */
class RecoveryThrottleTest extends TestCase
{
	private array $server;
	private array $singletons;

	protected function setUp(): void
	{
		$this->server = $_SERVER;

		$property = new ReflectionProperty(Singleton::class, 'instances');
		$this->singletons = $property->getValue();
		$instances = $this->singletons;
		$instances[Environment::class] = (new ReflectionClass(RecoveryWebEnvironment::class))->newInstanceWithoutConstructor();
		$property->setValue(null, $instances);
	}

	protected function tearDown(): void
	{
		$_SERVER = $this->server;
		(new ReflectionProperty(Singleton::class, 'instances'))->setValue(null, $this->singletons);
	}

	/**
	 * Every test uses its own addresses because the counters live in the cache
	 */
	private function uniqueEmail(): string
	{
		return uniqid('recovery') . '@example.com';
	}

	private function fromNewIp(): string
	{
		$ip = '10.' . mt_rand(1, 250) . '.' . mt_rand(1, 250) . '.' . mt_rand(1, 250);
		$_SERVER['REMOTE_ADDR'] = $ip;
		unset($_SERVER['HTTP_X_FORWARDED_FOR'], $_SERVER['HTTP_CLIENT_IP']);

		return $ip;
	}

	/**
	 * What the "forgotten" action does
	 */
	private function request(string $email): bool
	{
		$auth = new Authenticate();
		try {
			$auth->checkThrottle($email, 'recovery');
			$auth->registerFailure($email, 'recovery');
			return true;
		} catch (Forbidden $e) {
			return false;
		}
	}

	public function testAllowsAFewRequestsPerEmailAddress()
	{
		$this->fromNewIp();
		$email = $this->uniqueEmail();

		for ($i = 0; $i < Authenticate::THROTTLE_MAX_USER_FAILURES; $i++) {
			$this->assertTrue($this->request($email), 'request ' . ($i + 1));
		}

		$this->assertFalse($this->request($email));
		$this->assertFalse($this->request($email));
	}

	public function testRefusalHasAMessageAboutRecovery()
	{
		$this->fromNewIp();
		$email = $this->uniqueEmail();
		for ($i = 0; $i < Authenticate::THROTTLE_MAX_USER_FAILURES; $i++) {
			$this->request($email);
		}

		$this->expectException(Forbidden::class);
		$this->expectExceptionMessage('Too many password recovery requests');
		(new Authenticate())->checkThrottle($email, 'recovery');
	}

	public function testLimitPerEmailAddressAppliesToAllIpAddresses()
	{
		$email = $this->uniqueEmail();

		// somebody who spreads the requests over many addresses to flood one mailbox
		for ($i = 0; $i < Authenticate::THROTTLE_MAX_USER_FAILURES; $i++) {
			$this->fromNewIp();
			$this->assertTrue($this->request($email), 'request ' . ($i + 1));
		}

		$this->fromNewIp();
		$this->assertFalse($this->request($email));
	}

	public function testLimitPerIpAddressAppliesToAllEmailAddresses()
	{
		$this->fromNewIp();

		for ($i = 0; $i < Authenticate::THROTTLE_MAX_IP_FAILURES; $i++) {
			$this->assertTrue($this->request($this->uniqueEmail()), 'request ' . ($i + 1));
		}

		$this->assertFalse($this->request($this->uniqueEmail()));
	}

	public function testEmailAddressIsCaseInsensitive()
	{
		$this->fromNewIp();
		$email = $this->uniqueEmail();

		for ($i = 0; $i < Authenticate::THROTTLE_MAX_USER_FAILURES; $i++) {
			$this->assertTrue($this->request($i % 2 ? strtoupper($email) : $email));
		}

		$this->assertFalse($this->request($email));
	}

	public function testForwardedHeaderCannotBeUsedToGetAFreshIpLimit()
	{
		$ip = $this->fromNewIp();

		for ($i = 0; $i < Authenticate::THROTTLE_MAX_IP_FAILURES; $i++) {
			$this->assertTrue($this->request($this->uniqueEmail()));
		}

		$_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.' . mt_rand(1, 250);
		$_SERVER['HTTP_CLIENT_IP'] = '198.51.100.' . mt_rand(1, 250);
		$this->assertSame($ip, \go\core\http\Request::get()->getRemoteIpAddress());
		$this->assertFalse($this->request($this->uniqueEmail()));
	}

	public function testRefusedRequestsAreNotCounted()
	{
		$this->fromNewIp();
		$email = $this->uniqueEmail();

		for ($i = 0; $i < Authenticate::THROTTLE_MAX_USER_FAILURES + 5; $i++) {
			$this->request($email);
		}

		// refused requests must not make the lockout longer
		$state = go()->getCache()->get('login-throttle-recovery-e-' . md5($email));
		$this->assertSame(Authenticate::THROTTLE_MAX_USER_FAILURES, $state['count']);
	}

	public function testRequestsAreAllowedAgainWhenTheLockoutEnded()
	{
		$ip = $this->fromNewIp();
		$email = $this->uniqueEmail();
		for ($i = 0; $i < Authenticate::THROTTLE_MAX_USER_FAILURES; $i++) {
			$this->request($email);
		}
		$this->assertFalse($this->request($email));

		foreach ([
			'login-throttle-recovery-e-' . md5($email),
			'login-throttle-recovery-u-' . md5($email . '|' . $ip),
			'login-throttle-recovery-ip-' . md5($ip)
		] as $key) {
			$state = go()->getCache()->get($key);
			if ($state) {
				$state['lockedUntil'] = time() - 1;
				go()->getCache()->set($key, $state);
			}
		}

		$this->assertTrue($this->request($email));
	}

	public function testRecoveryRequestsDoNotLockPasswordLogin()
	{
		$this->fromNewIp();
		$email = $this->uniqueEmail();
		for ($i = 0; $i < Authenticate::THROTTLE_MAX_USER_FAILURES; $i++) {
			$this->request($email);
		}
		$this->assertFalse($this->request($email));

		// somebody who requests recovery messages for a user can't use that to lock the user out of logging in
		(new Authenticate())->checkThrottle($email);
		$this->assertTrue(true);
	}
}
