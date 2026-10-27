<?php
namespace go\core\auth;

use go\core\Environment;
use go\core\exception\Forbidden;
use go\core\exception\Unavailable;
use go\core\http\Response;
use go\core\model\AuthAllowGroup;
use go\core\model\RememberMe;
use go\core\model\Token;
use go\core\model\User;
use go\core\Singleton;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

/**
 * Not on the command line, where IP restrictions are not applied
 */
class WebEnvironment extends Environment
{
	public function isCli(): bool
	{
		return false;
	}
}

/**
 * Ignores cookies. They can't be sent because the test runner already produced output.
 */
class CookieIgnoringResponse extends Response
{
	public function setCookie(string $name, string $value, array $options = []): Response
	{
		return $this;
	}
}

/**
 * The rules of a password login (disabled user, IP restrictions of groups, maintenance mode) must also apply to the
 * other ways of logging in: remember me, refreshing a token and external logins like OpenID Connect.
 */
class LoginRulesTest extends TestCase
{
	private array $userIds = [];
	private array $allowGroupIds = [];
	private array $server;
	private bool $maintenanceMode;
	private array $singletons;
	private ?array $session = null;

	protected function setUp(): void
	{
		$this->server = $_SERVER;
		// Token::oldLogin() logs the user in for the legacy code by writing to the session. Don't leak that into other tests.
		$this->session = $_SESSION['GO_SESSION'] ?? null;
		$this->maintenanceMode = go()->getSettings()->maintenanceMode;
		$this->singletons = (new ReflectionProperty(Singleton::class, 'instances'))->getValue();
	}

	protected function tearDown(): void
	{
		$_SERVER = $this->server;
		// Assign to the element: GO\Base\Session keeps a reference to it, replacing $_SESSION would detach that.
		if ($this->session !== null) {
			$_SESSION['GO_SESSION'] = $this->session;
		}
		go()->getSettings()->maintenanceMode = $this->maintenanceMode;
		(new ReflectionProperty(Singleton::class, 'instances'))->setValue(null, $this->singletons);

		foreach ($this->allowGroupIds as $id) {
			AuthAllowGroup::delete(['id' => $id]);
		}
		foreach ($this->userIds as $id) {
			User::delete(['id' => $id]);
		}
	}

	private function newUser(): User
	{
		$name = uniqid('lr');
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

	private function newRememberMe(User $user): RememberMe
	{
		// logging in creates the client that the remember me login belongs to
		$this->newToken($user);

		$rememberMe = new RememberMe();
		$rememberMe->userId = $user->id;
		$this->assertTrue($rememberMe->save(), var_export($rememberMe->getValidationErrors(), true));

		return $rememberMe;
	}

	private function storedRememberMeHash(RememberMe $rememberMe): string
	{
		return RememberMe::find()->where('series', '=', $rememberMe->series)->single()->token;
	}

	/**
	 * Pretend the request isn't on the command line. IP restrictions are not applied on the CLI.
	 */
	private function pretendWebRequest(string $remoteAddr): void
	{
		$this->replaceSingleton(Environment::class, WebEnvironment::class);

		$_SERVER['REMOTE_ADDR'] = $remoteAddr;
		unset($_SERVER['HTTP_X_FORWARDED_FOR']);
	}

	private function replaceSingleton(string $class, string $replacementClass): void
	{
		$property = new ReflectionProperty(Singleton::class, 'instances');
		$instances = $property->getValue();
		$instances[$class] = (new ReflectionClass($replacementClass))->newInstanceWithoutConstructor();
		$property->setValue(null, $instances);
	}

	private function restrictToIp(User $user, string $pattern): void
	{
		$allow = new AuthAllowGroup();
		$allow->groupId = $user->getPersonalGroup()->id;
		$allow->ipPattern = $pattern;
		$this->assertTrue($allow->save(), var_export($allow->getValidationErrors(), true));
		$this->allowGroupIds[] = $allow->id;
	}

	public function testRememberMeLoginWorksForAllowedUser()
	{
		$rememberMe = $this->newRememberMe($this->newUser());
		$before = $this->storedRememberMeHash($rememberMe);

		$verified = RememberMe::verify($rememberMe->getToken());

		$this->assertNotFalse($verified);
		$this->assertNotSame($before, $this->storedRememberMeHash($rememberMe), 'token must be replaced after use');
	}

	public function testRememberMeLoginIsRefusedInMaintenanceMode()
	{
		$rememberMe = $this->newRememberMe($this->newUser());
		$cookie = $rememberMe->getToken();
		$before = $this->storedRememberMeHash($rememberMe);

		go()->getSettings()->maintenanceMode = true;

		try {
			RememberMe::verify($cookie);
			$this->fail('Expected maintenance mode to refuse the login');
		} catch (Unavailable $e) {
			$this->assertSame($before, $this->storedRememberMeHash($rememberMe), 'token must not be replaced when refused');
		}

		// the client still has a valid token once the maintenance is over
		go()->getSettings()->maintenanceMode = false;
		$this->assertNotFalse(RememberMe::verify($cookie));
	}

	public function testRememberMeLoginOfAdminIsAllowedInMaintenanceMode()
	{
		$admin = User::findById(User::ID_SUPER_ADMIN);
		$this->newToken($admin);
		$rememberMe = new RememberMe();
		$rememberMe->userId = $admin->id;
		$this->assertTrue($rememberMe->save());

		go()->getSettings()->maintenanceMode = true;

		try {
			$this->assertNotFalse(RememberMe::verify($rememberMe->getToken()));
		} finally {
			RememberMe::delete(['series' => $rememberMe->series]);
		}
	}

	public function testRememberMeLoginIsRefusedForDisabledUser()
	{
		$user = $this->newUser();
		$rememberMe = $this->newRememberMe($user);
		$cookie = $rememberMe->getToken();
		$before = $this->storedRememberMeHash($rememberMe);

		// directly in the database. Disabling via the user entity already removes the remember me logins.
		go()->getDbConnection()->exec("update core_user set enabled = 0 where id = " . (int) $user->id);

		try {
			RememberMe::verify($cookie);
			$this->fail('Expected the disabled user to be refused');
		} catch (Forbidden $e) {
			$this->assertSame($before, $this->storedRememberMeHash($rememberMe));
		}
	}

	public function testRememberMeLoginHonorsIpRestrictionsOfGroups()
	{
		$user = $this->newUser();
		$rememberMe = $this->newRememberMe($user);
		$cookie = $rememberMe->getToken();
		$before = $this->storedRememberMeHash($rememberMe);

		$this->restrictToIp($user, '10.9.9.*');

		$this->pretendWebRequest('203.0.113.9');
		try {
			RememberMe::verify($cookie);
			$this->fail('Expected the IP restriction to refuse the login');
		} catch (Forbidden $e) {
			$this->assertStringContainsString('203.0.113.9', $e->getMessage());
			$this->assertSame($before, $this->storedRememberMeHash($rememberMe));
		}

		$this->pretendWebRequest('10.9.9.5');
		$this->assertNotFalse(RememberMe::verify($cookie));
	}

	public function testIpRestrictionCannotBeBypassedWithForwardedHeader()
	{
		$user = $this->newUser();
		$rememberMe = $this->newRememberMe($user);
		$this->restrictToIp($user, '10.9.9.*');

		$this->pretendWebRequest('203.0.113.9');
		$_SERVER['HTTP_X_FORWARDED_FOR'] = '10.9.9.5';
		$_SERVER['HTTP_CLIENT_IP'] = '10.9.9.5';

		$this->expectException(Forbidden::class);
		RememberMe::verify($rememberMe->getToken());
	}

	public function testRefreshTokenWorksForAllowedUser()
	{
		$token = $this->newToken($this->newUser());

		$refreshed = (new Authenticate())->refreshToken($token->accessToken);

		$this->assertNotNull($refreshed);
		$this->assertTrue($refreshed->isAuthenticated());
	}

	public function testRefreshTokenIsRefusedInMaintenanceMode()
	{
		$token = $this->newToken($this->newUser());

		go()->getSettings()->maintenanceMode = true;

		$this->expectException(Unavailable::class);
		(new Authenticate())->refreshToken($token->accessToken);
	}

	public function testRefreshTokenIsRefusedForIpRestrictedUser()
	{
		$user = $this->newUser();
		$token = $this->newToken($user);
		$this->restrictToIp($user, '10.9.9.*');
		$this->pretendWebRequest('203.0.113.9');

		$this->expectException(Forbidden::class);
		(new Authenticate())->refreshToken($token->accessToken);
	}

	/**
	 * What OpenID Connect and OAuth2 logins use after they verified the identity at the provider
	 */
	public function testExternalLoginIsRefusedForDisabledUser()
	{
		$user = $this->newUser();
		go()->getDbConnection()->exec("update core_user set enabled = 0 where id = " . (int) $user->id);

		$this->expectException(Forbidden::class);
		(new Authenticate())->setAuthenticated(User::findById($user->id));
	}

	public function testExternalLoginIsRefusedInMaintenanceMode()
	{
		$user = $this->newUser();
		go()->getSettings()->maintenanceMode = true;

		$this->expectException(Unavailable::class);
		(new Authenticate())->setAuthenticated($user);
	}

	public function testExternalLoginWorksForAllowedUser()
	{
		$user = $this->newUser();
		$this->replaceSingleton(Response::class, CookieIgnoringResponse::class);

		$token = (new Authenticate())->setAuthenticated($user);

		$this->assertTrue($token->isAuthenticated());
	}

	public function testUnknownUserIsRefused()
	{
		$this->expectException(Forbidden::class);
		(new Authenticate())->assertUserIdMayLogin(99999999);
	}
}
