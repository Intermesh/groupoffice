<?php
namespace go\modules\legacy\core;

use go\core\jmap\Request;
use go\core\jmap\State;
use go\core\model\Token;
use go\core\model\User;
use GO\Base\Exception\SecurityTokenMismatch;
use GO\Core\Controller\CoreController;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Legacy controller requests that authenticate with the access token cookie must carry the old session security token
 * or the CSRF token of the access token. The GOUI client only has the latter.
 */
class SecurityTokenTest extends TestCase
{
	private $authState;
	private array $server;
	private array $request;
	private array $cookie;
	private bool $debug;
	private bool $disableCheck;

	/** @var Token[] */
	private array $tokens = [];

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->server = $_SERVER;
		$this->request = $_REQUEST;
		$this->cookie = $_COOKIE;
		$this->debug = (bool) \GO::config()->debug;
		$this->disableCheck = (bool) \GO::config()->disable_security_token_check;

		// the check is skipped in debug mode
		\GO::config()->debug = false;
		\GO::config()->disable_security_token_check = false;

		$_SERVER['REQUEST_METHOD'] = 'GET';
		unset($_SERVER['HTTP_X_CSRF_TOKEN']);
		$_REQUEST = ['r' => 'core/test'];
	}

	protected function tearDown(): void
	{
		$_SERVER = $this->server;
		$_REQUEST = $this->request;
		$_COOKIE = $this->cookie;
		\GO::config()->debug = $this->debug;
		\GO::config()->disable_security_token_check = $this->disableCheck;
		$this->setCookieAccessTokenUsed(false);
		Request::destroy();
		go()->setAuthState($this->authState);

		foreach ($this->tokens as $token) {
			go()->getCache()->delete('token-' . $token->accessToken);
			Token::delete(['accessToken' => $token->accessToken]);
		}
	}

	private function setCookieAccessTokenUsed(bool $value): void
	{
		(new ReflectionProperty(State::class, 'cookieAccessTokenUsed'))->setValue(null, $value);
	}

	private function createToken(int $userId): Token
	{
		$token = new Token();
		$token->userId = $userId;
		$this->assertTrue($token->setAuthenticated(false));
		$this->tokens[] = $token;

		return $token;
	}

	/**
	 * Authenticate like a browser does: with the access token cookie
	 */
	private function loginWithCookie(Token $token, ?string $csrfHeader = null): void
	{
		$_COOKIE['accessToken'] = $token->accessToken;
		if (isset($csrfHeader)) {
			$_SERVER['HTTP_X_CSRF_TOKEN'] = $csrfHeader;
		}
		Request::destroy();
		$this->setCookieAccessTokenUsed(false);

		$state = new State();
		go()->setAuthState($state);
		$this->assertTrue($state->isAuthenticatedUsingCookie());
	}

	private function checkSecurityToken(): void
	{
		$method = new ReflectionMethod(CoreController::class, 'checkSecurityToken');
		$method->invoke(new CoreController());
	}

	public function testRequestWithoutTokenIsRejected()
	{
		$this->loginWithCookie($this->createToken(User::ID_SUPER_ADMIN));

		$this->expectException(SecurityTokenMismatch::class);
		$this->checkSecurityToken();
	}

	public function testWrongCSRFTokenIsRejected()
	{
		$this->loginWithCookie($this->createToken(User::ID_SUPER_ADMIN), 'wrong');

		$this->expectException(SecurityTokenMismatch::class);
		$this->checkSecurityToken();
	}

	public function testCSRFTokenOfAnotherAccessTokenIsRejected()
	{
		$other = $this->createToken(User::ID_SUPER_ADMIN);
		$this->loginWithCookie($this->createToken(User::ID_SUPER_ADMIN), $other->CSRFToken);

		$this->expectException(SecurityTokenMismatch::class);
		$this->checkSecurityToken();
	}

	public function testCSRFTokenHeaderIsAccepted()
	{
		$token = $this->createToken(User::ID_SUPER_ADMIN);
		$this->loginWithCookie($token, $token->CSRFToken);

		$this->checkSecurityToken();
		$this->addToAssertionCount(1);
	}

	public function testCSRFTokenParameterIsAccepted()
	{
		// for form posts and downloads that can't send a header
		$token = $this->createToken(User::ID_SUPER_ADMIN);
		$_REQUEST['CSRFToken'] = $token->CSRFToken;
		$this->loginWithCookie($token);

		$this->checkSecurityToken();
		$this->addToAssertionCount(1);
	}

	public function testSessionSecurityTokenIsAccepted()
	{
		$this->loginWithCookie($this->createToken(User::ID_SUPER_ADMIN));
		$_REQUEST['security_token'] = \GO::session()->values['security_token'];

		$this->checkSecurityToken();
		$this->addToAssertionCount(1);
	}
}
