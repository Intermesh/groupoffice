<?php
namespace go\modules\community\oidc;

use go\core\auth\TemporaryState;
use go\core\http\Exception;
use go\core\jmap\State;
use go\core\model\Module as ModuleModel;
use go\core\model\User;
use go\modules\community\oidc\controller\Client;
use PHPUnit\Framework\TestCase;

/**
 * The OpenID Connect clients are only managed by administrators. The login page gets its buttons from the server.
 */
class ClientControllerTest extends TestCase
{
	private static bool $installedByTest = false;
	private $authState;
	private array $cookie;

	public static function setUpBeforeClass(): void
	{
		if (!ModuleModel::findByName('community', 'oidc')) {
			go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
			self::assertNotFalse(Module::get()->install(), 'Could not install the oidc module');
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
	}

	protected function tearDown(): void
	{
		$_COOKIE = $this->cookie;
		go()->setAuthState($this->authState);
	}

	public function testAnonymousRequestIsRefused(): void
	{
		unset($_COOKIE['accessToken']);
		go()->setAuthState(new State());

		$this->expectException(Exception::class);
		$this->expectExceptionCode(401);
		new Client();
	}

	public function testAdministratorMayUseIt(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		$this->assertInstanceOf(Client::class, new Client());
	}
}
