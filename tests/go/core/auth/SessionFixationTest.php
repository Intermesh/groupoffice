<?php
namespace go\core\auth;

use go\core\jmap\State;
use go\core\model\Token;
use go\core\model\User;
use GO\Base\Exception\SecurityTokenMismatch;
use GO\Files\Controller\FolderController;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionProperty;

/**
 * The session of the old framework gets its user from the access token of the new framework. That session must only
 * be used by the client that sends that access token.
 */
class SessionFixationTest extends TestCase
{
	private array $userIds = [];
	private $authState;
	private array $session;
	private array $request;
	private array $server;
	private array $cookie;
	private bool $csrfCheck;
	private $debug;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->session = \GO::session()->values ?? [];
		$this->request = $_REQUEST;
		$this->server = $_SERVER;
		$this->cookie = $_COOKIE;
		$this->csrfCheck = State::$CSRFcheck;
		// the security token isn't checked in debug mode
		$this->debug = \GO::config()->debug;
		\GO::config()->debug = false;

		$_SERVER['REQUEST_METHOD'] = 'GET';
		unset($_COOKIE['accessToken'], $_SERVER['HTTP_AUTHORIZATION']);
		$this->resetClientToken();
	}

	protected function tearDown(): void
	{
		\GO::session()->values = $this->session;
		$_REQUEST = $this->request;
		$_SERVER = $this->server;
		$_COOKIE = $this->cookie;
		State::$CSRFcheck = $this->csrfCheck;
		\GO::config()->debug = $this->debug;
		$this->resetClientToken();
		go()->setAuthState($this->authState);

		foreach ($this->userIds as $id) {
			User::delete(['id' => $id]);
		}
	}

	/**
	 * Like a new request: State remembers how the client sent its access token
	 */
	private function resetClientToken(): void
	{
		foreach (['cookieAccessTokenUsed', 'headerAccessTokenUsed'] as $name) {
			if (property_exists(State::class, $name)) {
				(new ReflectionProperty(State::class, $name))->setValue(null, false);
			}
		}
	}

	private function newToken(): Token
	{
		$name = uniqid('fixation');
		$user = new User();
		$user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($user->save(), var_export($user->getValidationErrors(), true));
		$this->userIds[] = $user->id;

		$token = new Token();
		$token->userId = $user->id;
		$this->assertTrue($token->setAuthenticated(false));

		return $token;
	}

	/**
	 * Start the old session for a request with the given cookie
	 */
	private function startSession(array $values, ?string $cookieAccessToken): array
	{
		if ($cookieAccessToken !== null) {
			$_COOKIE['accessToken'] = $cookieAccessToken;
		}
		$this->resetClientToken();
		go()->setAuthState(new State());

		\GO::session()->values = $values;
		\GO::session()->start();

		return \GO::session()->values;
	}

	public function testGosidDoesNotDisableCsrfCheck(): void
	{
		$values = $this->startSession([], null);

		// somebody who knows the session ID and the security token that the login page shows of that session
		$_REQUEST['GOSID'] = session_id() ?: 'foreignsession';
		$_REQUEST['security_token'] = $values['security_token'];
		State::$CSRFcheck = true;

		\GO::session()->start();

		$this->assertTrue(State::$CSRFcheck, 'GOSID must not be accepted');
	}

	public function testUserCopiedFromAccessTokenIsBoundToIt(): void
	{
		$token = $this->newToken();

		$values = $this->startSession([], $token->accessToken);

		$this->assertEquals($token->userId, $values['user_id']);
		$this->assertSame($token->accessToken, $values['accessToken'] ?? null);
	}

	public function testSessionUserIsKeptWithSameAccessToken(): void
	{
		$token = $this->newToken();

		$values = $this->startSession(['user_id' => $token->userId, 'accessToken' => $token->accessToken, 'security_token' => 'abc'], $token->accessToken);

		$this->assertEquals($token->userId, $values['user_id']);
		$this->assertSame('abc', $values['security_token']);
	}

	public function testSessionUserIsDroppedWithoutAccessToken(): void
	{
		$victim = $this->newToken();

		// the session of somebody else that got the user of the victim
		$values = $this->startSession(['user_id' => $victim->userId, 'accessToken' => $victim->accessToken], null);

		$this->assertEmpty($values['user_id'] ?? null);
	}

	public function testSessionUserIsReplacedByUserOfOtherAccessToken(): void
	{
		$victim = $this->newToken();
		$other = $this->newToken();

		$values = $this->startSession(['user_id' => $victim->userId, 'accessToken' => $victim->accessToken], $other->accessToken);

		$this->assertEquals($other->userId, $values['user_id']);
		$this->assertSame($other->accessToken, $values['accessToken']);
	}

	public function testSessionUserWithInvalidAccessTokenIsDropped(): void
	{
		$victim = $this->newToken();

		$values = $this->startSession(['user_id' => $victim->userId, 'accessToken' => $victim->accessToken], 'invalid');

		$this->assertEmpty($values['user_id'] ?? null);
	}

	private function checkSecurityToken(): void
	{
		$controller = new FolderController();
		$method = new ReflectionMethod($controller, 'checkSecurityToken');
		$method->invoke($controller);
	}

	/**
	 * A form of another website that posts to the old framework. The browser sends the cookies but the form can't
	 * contain the CSRF token or the security token.
	 */
	private function crossSitePost(Token $token): void
	{
		$this->startSession(['user_id' => $token->userId, 'accessToken' => $token->accessToken, 'security_token' => 'secret'], $token->accessToken);
		$_SERVER['REQUEST_METHOD'] = 'POST';
		unset($_SERVER['HTTP_X_CSRF_TOKEN']);
		$_REQUEST['r'] = 'files/folder/delete';
		unset($_REQUEST['security_token'], $_REQUEST['CSRFToken']);
		$this->resetClientToken();
		State::$CSRFcheck = true;
		go()->setAuthState(new State());
		$this->assertFalse(go()->getAuthState()->isAuthenticated(), 'the JMAP CSRF check fails');
	}

	public function testPostWithSessionUserNeedsSecurityToken(): void
	{
		$this->crossSitePost($this->newToken());

		$this->expectException(SecurityTokenMismatch::class);
		$this->checkSecurityToken();
	}

	public function testPostWithWrongSecurityTokenIsRefused(): void
	{
		$this->crossSitePost($this->newToken());
		$_REQUEST['security_token'] = ['secret'];

		$this->expectException(SecurityTokenMismatch::class);
		$this->checkSecurityToken();
	}

	public function testPostWithSecurityTokenIsAccepted(): void
	{
		$this->crossSitePost($this->newToken());
		$_REQUEST['security_token'] = 'secret';

		$this->checkSecurityToken();
		$this->assertFalse(State::$CSRFcheck);
	}

	/**
	 * Runs Token::oldLogin() in another process, because PHPUnit already wrote output so a session can't be started.
	 */
	private function oldLogin(int $sessionUserId, int $tokenUserId): array
	{
		$script = __DIR__ . '/fixtures/oldLoginSession.php';
		$output = shell_exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($script) . ' ' . $sessionUserId . ' ' . $tokenUserId . ' 2>&1');
		$result = json_decode((string) $output, true);
		$this->assertIsArray($result, (string) $output);

		return $result;
	}

	public function testSessionIdChangesWhenUserIsSet(): void
	{
		$result = $this->oldLogin(0, 1);
		$this->assertNotEquals($result['before'], $result['after']);
		$this->assertEquals(1, $result['userId']);
	}

	public function testSessionIdChangesWhenUserChanges(): void
	{
		$result = $this->oldLogin(2, 1);
		$this->assertNotEquals($result['before'], $result['after']);
		$this->assertEquals(1, $result['userId']);
	}

	public function testSessionIdStaysForSameUser(): void
	{
		// oldLogin() runs on every request, so it must not change the ID every time
		$result = $this->oldLogin(1, 1);
		$this->assertEquals($result['before'], $result['after']);
	}

}
