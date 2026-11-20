<?php
namespace go\core\webclient;

use DateTimeZone;
use go\core\exception\RememberMeTheft;
use go\core\http\Response;
use go\core\jmap\State;
use go\core\model\CookieRecordingResponse;
use go\core\model\RememberMe;
use go\core\model\Token;
use go\core\model\User;
use go\core\Singleton;
use go\core\util\DateTime;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

require_once __DIR__ . '/../model/CookieRecordingResponse.php';

/**
 * The logins of index.php: a token posted by the server manager and the remember me cookie. Both must follow the rules
 * of a password login, and another website must not be able to log a user in to its own account.
 */
class StartupTest extends TestCase
{
	private static User $user;

	private array $post;
	private array $cookie;
	private array $server;
	private array $instances;
	private $authState;
	private CookieRecordingResponse $response;

	public static function setUpBeforeClass(): void
	{
		self::$user = self::createUser();
	}

	public static function tearDownAfterClass(): void
	{
		User::delete(['id' => self::$user->id]);
	}

	private static function createUser(bool $enabled = true): User
	{
		$name = uniqid('startup');
		$user = new User();
		$user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz',
			'enabled' => $enabled
		]);
		if (!$user->save()) {
			throw new \Exception(var_export($user->getValidationErrors(), true));
		}

		// logging in creates the client that tokens and remember me logins belong to
		$token = new Token();
		$token->userId = $user->id;
		$token->setAuthenticated(false);

		return $user;
	}

	protected function setUp(): void
	{
		$this->post = $_POST;
		$this->cookie = $_COOKIE;
		$this->server = $_SERVER;
		$this->authState = go()->getAuthState();
		go()->setAuthState(new State());

		$property = new ReflectionProperty(Singleton::class, 'instances');
		$this->instances = $property->getValue();
		$this->response = (new ReflectionClass(CookieRecordingResponse::class))->newInstanceWithoutConstructor();
		$instances = $this->instances;
		$instances[Response::class] = $this->response;
		$property->setValue(null, $instances);
	}

	protected function tearDown(): void
	{
		$_POST = $this->post;
		$_COOKIE = $this->cookie;
		$_SERVER = $this->server;
		go()->setAuthState($this->authState);
		(new ReflectionProperty(Singleton::class, 'instances'))->setValue(null, $this->instances);
	}

	/**
	 * Create and delete users with the auth state of the test run. The listeners of the modules need it.
	 */
	private function withOriginalAuthState(callable $fn): mixed
	{
		$state = go()->getAuthState();
		go()->setAuthState($this->authState);
		try {
			return $fn();
		} finally {
			go()->setAuthState($state);
		}
	}

	private function createTestUser(bool $enabled = true): User
	{
		return $this->withOriginalAuthState(fn() => self::createUser($enabled));
	}

	private function deleteTestUser(User $user): void
	{
		$this->withOriginalAuthState(fn() => User::delete(['id' => $user->id]));
	}

	/**
	 * Like multi_instance's Instance::createAccessToken()
	 */
	private function createHandoffToken(User $user): string
	{
		$utc = new DateTimeZone('UTC');
		$accessToken = uniqid() . bin2hex(random_bytes(16));
		go()->getDbConnection()->insert('core_auth_token', [
			'loginToken' => uniqid() . bin2hex(random_bytes(16)),
			'accessToken' => $accessToken,
			'CSRFToken' => uniqid() . bin2hex(random_bytes(16)),
			'expiresAt' => (new DateTime('now', $utc))->modify('+1 hour'),
			'userId' => $user->id,
			'createdAt' => new DateTime('now', $utc),
			'clientId' => $user->currentClient()->id
		])->execute();

		return $accessToken;
	}

	/**
	 * Values of the access token cookies that were set
	 */
	private function accessTokenCookies(): array
	{
		$values = [];
		foreach ($this->response->cookies as [$name, $value]) {
			if ($name == 'accessToken' && $value !== '') {
				$values[] = $value;
			}
		}
		return $values;
	}

	public function testHandoffTokenLogsInOnce(): void
	{
		$accessToken = $this->createHandoffToken(self::$user);
		$_POST['accessToken'] = $accessToken;

		$this->assertTrue(Startup::loginWithHandoffToken());

		$cookies = $this->accessTokenCookies();
		$this->assertCount(1, $cookies);
		$this->assertNotSame($accessToken, $cookies[0], 'the posted token is replaced');
		$this->assertSame(self::$user->id, Token::find()->where(['accessToken' => $cookies[0]])->single()->userId);
		$this->assertNull(Token::find()->where(['accessToken' => $accessToken])->single());

		$_POST['accessToken'] = $accessToken;
		$this->assertFalse(Startup::loginWithHandoffToken(), 'the posted token can be used only once');
	}

	public function testTokenOfNormalLoginIsRefused(): void
	{
		$login = new Token();
		$login->userId = self::$user->id;
		$this->assertTrue($login->setAuthenticated(false));

		$_POST['accessToken'] = $login->accessToken;

		$this->assertFalse(Startup::loginWithHandoffToken());
		$this->assertSame([], $this->accessTokenCookies());
	}

	public function testHandoffTokenOfDisabledUserIsRefused(): void
	{
		$user = $this->createTestUser(false);
		try {
			$_POST['accessToken'] = $this->createHandoffToken($user);

			$this->assertFalse(Startup::loginWithHandoffToken());
			$this->assertSame([], $this->accessTokenCookies());
		} finally {
			$this->deleteTestUser($user);
		}
	}

	public function testPostedArrayIsRefused(): void
	{
		$_POST['accessToken'] = [$this->createHandoffToken(self::$user)];

		$this->assertFalse(Startup::loginWithHandoffToken());
		$this->assertSame([], $this->accessTokenCookies());
	}

	private function rememberMe(User $user): RememberMe
	{
		$rememberMe = new RememberMe();
		$rememberMe->userId = $user->id;
		$this->assertTrue($rememberMe->save(), var_export($rememberMe->getValidationErrors(), true));
		return $rememberMe;
	}

	public function testRememberMeCookieLogsIn(): void
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_COOKIE['goRememberMe'] = $this->rememberMe(self::$user)->getToken();
		unset($_COOKIE['accessToken']);

		$this->assertTrue(Startup::loginWithRememberMe());

		$cookies = $this->accessTokenCookies();
		$this->assertCount(1, $cookies);
		$this->assertSame(self::$user->id, Token::find()->where(['accessToken' => $cookies[0]])->single()->userId);
	}

	public function testRememberMeCookieIsIgnoredOnPost(): void
	{
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_COOKIE['goRememberMe'] = $this->rememberMe(self::$user)->getToken();
		unset($_COOKIE['accessToken']);

		$this->assertFalse(Startup::loginWithRememberMe());
		$this->assertSame([], $this->accessTokenCookies());
	}

	public function testRememberMeCookieOfDisabledUserShowsLogin(): void
	{
		$user = $this->createTestUser();
		try {
			$_SERVER['REQUEST_METHOD'] = 'GET';
			$_COOKIE['goRememberMe'] = $this->rememberMe($user)->getToken();
			unset($_COOKIE['accessToken']);

			$user->enabled = false;
			$this->assertTrue($this->withOriginalAuthState(fn() => $user->save()));

			$this->assertFalse(Startup::loginWithRememberMe());
			$this->assertSame([], $this->accessTokenCookies());
		} finally {
			$this->deleteTestUser($user);
		}
	}

	public function testStolenRememberMeCookieThrows(): void
	{
		$user = $this->createTestUser();
		try {
			$_SERVER['REQUEST_METHOD'] = 'GET';
			$rememberMe = $this->rememberMe($user);
			$_COOKIE['goRememberMe'] = $rememberMe->series . ':' . str_repeat('0', 40);
			unset($_COOKIE['accessToken']);

			$this->expectException(RememberMeTheft::class);
			Startup::loginWithRememberMe();
		} finally {
			$this->deleteTestUser($user);
		}
	}
}
