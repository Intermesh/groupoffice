<?php
namespace go\modules\community\oauth2client;

use go\core\auth\TemporaryState;
use go\core\http\Exception;
use go\core\jmap\State;
use go\core\model\Group;
use go\core\model\Module as ModuleModel;
use go\core\model\Permission;
use go\core\model\User;
use go\modules\community\oauth2client\controller\Oauth2Client;
use go\modules\community\oauth2client\model\Oauth2Client as Oauth2ClientModel;
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
	private array $groupIds = [];
	private array $clientIds = [];

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
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		if ($this->clientIds) {
			Oauth2ClientModel::delete(['id' => $this->clientIds]);
		}
		if ($this->groupIds) {
			$module = ModuleModel::findByName('community', 'oauth2client');
			foreach ($this->groupIds as $groupId) {
				unset($module->permissions[$groupId]);
			}
			$module->save();
			Group::delete(['id' => $this->groupIds]);
		}
		if ($this->userIds) {
			User::delete(['id' => $this->userIds]);
		}
		go()->setAuthState($this->authState);
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

	/**
	 * A user who manages the module, but isn't an administrator, can't add or change the connections
	 */
	public function testModuleManagerMayNotChangeClients(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		$url = go()->getSettings()->URL ?? null;
		go()->getSettings()->URL = 'http://localhost/';

		try {
			$name = uniqid('oauth2manager');
			$user = new User();
			$user->setValues(['username' => $name, 'displayName' => $name, 'email' => $name . '@example.com', 'password' => 'Pa55word!!xyz']);
			$this->assertTrue($user->save(), var_export($user->getValidationErrors(), true));
			$this->userIds[] = $user->id;

			$group = new Group();
			$group->name = $name . 'managers';
			$group->users = [$user->id];
			$this->assertTrue($group->save());
			$this->groupIds[] = $group->id;

			$module = ModuleModel::findByName('community', 'oauth2client');
			$permission = new Permission($module);
			$permission->setRights(['mayManage' => true]);
			$module->permissions[$group->id] = $permission;
			$this->assertTrue($module->save());

			$client = new Oauth2ClientModel();
			$client->setValues(['name' => $name, 'defaultClientId' => 1, 'clientId' => 'client-id', 'clientSecret' => 'secret', 'projectId' => 'project']);
			$this->assertTrue($client->save(), var_export($client->getValidationErrors(), true));
			$this->clientIds[] = $client->id;

			go()->setAuthState(new TemporaryState($user->id));
			$this->assertTrue(ModuleModel::findByName('community', 'oauth2client')->getUserRights()->mayManage);

			$values = ['name' => $name . 'new', 'defaultClientId' => 1, 'clientId' => 'client-id', 'clientSecret' => 'secret', 'projectId' => 'project'];
			$result = (new Oauth2Client())->set([
				'create' => ['new' => $values],
				'update' => [$client->id => ['clientId' => 'changed']],
				'destroy' => [$client->id]
			])->getArray();

			$this->assertSame('forbidden', $result['notCreated']['new']->type ?? null);
			$this->assertSame('forbidden', $result['notUpdated'][$client->id]->type ?? null);
			$this->assertSame('forbidden', $result['notDestroyed'][$client->id]->type ?? null);

			go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
			$this->assertSame('client-id', Oauth2ClientModel::findById($client->id)->clientId);
		} finally {
			go()->getSettings()->URL = $url;
		}
	}
}
