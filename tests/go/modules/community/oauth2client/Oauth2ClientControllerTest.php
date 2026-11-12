<?php
namespace go\modules\community\oauth2client;

use go\core\auth\TemporaryState;
use go\core\http\Exception;
use go\core\jmap\State;
use go\core\model\Module as ModuleModel;
use go\core\model\User;
use go\modules\community\oauth2client\controller\Oauth2Client;
use PHPUnit\Framework\TestCase;

/**
 * The OAuth2 clients can be read by users to choose one for their e-mail account, but not without logging in. Only
 * the OpenID login pages of gauth.php are used before logging in.
 */
class Oauth2ClientControllerTest extends TestCase
{
	private static bool $installedByTest = false;
	private $authState;
	private array $cookie;
	private array $userIds = [];

	public static function setUpBeforeClass(): void
	{
		if (!ModuleModel::findByName('community', 'oauth2client')) {
			go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
			self::assertNotFalse(Module::get()->install(), 'Could not install the oauth2client module');
			self::$installedByTest = true;
		}
	}

	public static function tearDownAfterClass(): void
	{
		if (self::$installedByTest) {
			go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
			// Module::uninstall() switches off change tracking and history without switching them on again
			$trackChanges = \go\core\jmap\Entity::$trackChanges;
			$history = \go\modules\community\history\Module::$enabled;
			Module::get()->uninstall();
			\go\core\jmap\Entity::$trackChanges = $trackChanges;
			\go\modules\community\history\Module::$enabled = $history;
			self::$installedByTest = false;
		}
	}

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->cookie = $_COOKIE;
		Oauth2Client::$isPageRequest = false;
	}

	protected function tearDown(): void
	{
		Oauth2Client::$isPageRequest = false;
		$_COOKIE = $this->cookie;
		go()->setAuthState($this->authState);
		if ($this->userIds) {
			User::delete(['id' => $this->userIds]);
		}
	}

	private function anonymous(): void
	{
		unset($_COOKIE['accessToken']);
		go()->setAuthState(new State());
	}

	public function testAnonymousJmapRequestIsRefused(): void
	{
		$this->anonymous();

		$this->expectException(Exception::class);
		$this->expectExceptionCode(401);
		new Oauth2Client();
	}

	public function testLoginPagesWorkWithoutLogin(): void
	{
		$this->anonymous();
		Oauth2Client::$isPageRequest = true;

		$this->assertInstanceOf(Oauth2Client::class, new Oauth2Client());
	}

	public function testUserWithoutModuleAccessMayReadClients(): void
	{
		$name = uniqid('oauth2user');
		$user = new User();
		$user->setValues(['username' => $name, 'displayName' => $name, 'email' => $name . '@example.com', 'password' => 'Pa55word!!xyz']);
		$this->assertTrue($user->save(), var_export($user->getValidationErrors(), true));
		$this->userIds[] = $user->id;

		go()->setAuthState(new TemporaryState($user->id));
		$result = (new Oauth2Client())->query([])->getArray();
		$this->assertArrayHasKey('ids', $result);
	}
}
