<?php
namespace go\modules\community\apikeys\model;

use go\core\auth\TemporaryState;
use go\core\model\Group;
use go\core\model\Module as ModuleModel;
use go\core\model\Permission;
use go\core\model\Token;
use go\core\model\User;
use go\modules\community\apikeys\controller\Key as KeyController;
use go\modules\community\apikeys\Module;
use PHPUnit\Framework\TestCase;

/**
 * API keys contain an access token of a user. In 6.8 only administrators may create keys, the token is only returned
 * when the key is created and the client can't change it.
 */
class KeyTest extends TestCase
{
	private static bool $installedByTest = false;
	private $authState;
	private string $prefix;
	private User $manager;
	private Group $managerGroup;
	private array $keyIds = [];

	public static function setUpBeforeClass(): void
	{
		if (!ModuleModel::findByName('community', 'apikeys')) {
			go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
			self::assertNotFalse(Module::get()->install(), 'Could not install the apikeys module');
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
			// Write the changes of the keys while their entity type still exists
			\go\core\orm\EntityType::push();
			Module::get()->uninstall();
			\go\core\jmap\Entity::$trackChanges = $trackChanges;
			\go\modules\community\history\Module::$enabled = $history;
			self::$installedByTest = false;
		}
	}

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->actAs(User::ID_SUPER_ADMIN);

		$this->prefix = uniqid('apikey');
		$this->manager = new User();
		$this->manager->setValues([
			'username' => $this->prefix,
			'displayName' => $this->prefix,
			'email' => $this->prefix . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->manager->save(), $this->manager->getValidationErrorsAsString());

		$this->managerGroup = new Group();
		$this->managerGroup->name = $this->prefix . 'managers';
		$this->managerGroup->users = [$this->manager->id];
		$this->assertTrue($this->managerGroup->save());

		$module = ModuleModel::findByName('community', 'apikeys');
		$permission = new Permission($module);
		$permission->setRights(['mayManage' => true]);
		$module->permissions[$this->managerGroup->id] = $permission;
		$this->assertTrue($module->save());
	}

	protected function tearDown(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);

		if (!empty($this->keyIds)) {
			Key::delete(['id' => $this->keyIds]);
		}

		$module = ModuleModel::findByName('community', 'apikeys');
		unset($module->permissions[$this->managerGroup->id]);
		$module->save();
		Group::delete(['id' => $this->managerGroup->id]);
		User::delete(['id' => $this->manager->id]);

		go()->setAuthState($this->authState);
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
	}

	private function set(int $actingUserId, array $params): array
	{
		$this->actAs($actingUserId);
		$result = (new KeyController())->set($params)->getArray();
		foreach ($result['created'] ?? [] as $created) {
			$this->keyIds[] = $created['id'];
		}
		return $result;
	}

	private function createKey(int $actingUserId, ?int $userId = null): array
	{
		$values = ['name' => uniqid($this->prefix)];
		if (isset($userId)) {
			$values['userId'] = $userId;
		}
		return $this->set($actingUserId, ['create' => ['new' => $values]]);
	}

	private function storedToken(string $keyId): string
	{
		return go()->getDbConnection()
			->selectSingleValue('accessToken')
			->from('apikeys_key')
			->where('id', '=', $keyId)
			->single();
	}

	private function tokenUserId(string $accessToken): ?int
	{
		$userId = go()->getDbConnection()
			->selectSingleValue('userId')
			->from('core_auth_token')
			->where('accessToken', '=', $accessToken)
			->single();
		return $userId ? (int) $userId : null;
	}

	public function testManagerMayNotCreateKeyForAdministrator(): void
	{
		$result = $this->createKey($this->manager->id, User::ID_SUPER_ADMIN);

		$this->assertArrayNotHasKey('new', $result['created'] ?? []);
		$this->assertArrayHasKey('new', $result['notCreated'] ?? []);
	}

	public function testManagerMayNotCreateKeyForThemselves(): void
	{
		$result = $this->createKey($this->manager->id, $this->manager->id);

		$this->assertArrayNotHasKey('new', $result['created'] ?? []);
		$this->assertArrayHasKey('new', $result['notCreated'] ?? []);
	}

	public function testAdminMayCreateKeyForAnotherUser(): void
	{
		$result = $this->createKey(User::ID_SUPER_ADMIN, $this->manager->id);

		$this->assertArrayHasKey('new', $result['created'] ?? [], var_export($result['notCreated'] ?? null, true));
		$this->assertSame((int) $this->manager->id, $this->tokenUserId($this->storedToken($result['created']['new']['id'])));
	}

	public function testAccessTokenIsOnlyReturnedOnCreate(): void
	{
		$result = $this->createKey(User::ID_SUPER_ADMIN, $this->manager->id);
		$keyId = $result['created']['new']['id'];
		$this->assertSame($this->storedToken($keyId), $result['created']['new']['accessToken'] ?? null);

		$this->actAs(User::ID_SUPER_ADMIN);
		$get = (new KeyController())->get(['ids' => [$keyId]])->getArray();
		$this->assertNull($get['list'][0]['accessToken'] ?? null);
		$this->assertSame((int) $this->manager->id, (int) $get['list'][0]['userId']);
	}

	public function testAccessTokenCanNotBeChanged(): void
	{
		$admin = $this->createKey(User::ID_SUPER_ADMIN, User::ID_SUPER_ADMIN);
		$adminToken = $this->storedToken($admin['created']['new']['id']);

		$own = $this->createKey(User::ID_SUPER_ADMIN, $this->manager->id);
		$keyId = $own['created']['new']['id'];
		$ownToken = $this->storedToken($keyId);

		$this->set(User::ID_SUPER_ADMIN, ['update' => [$keyId => ['accessToken' => $adminToken]]]);
		$this->assertSame($ownToken, $this->storedToken($keyId));

		// Destroying the key deletes its own token, not the administrator's
		$this->set(User::ID_SUPER_ADMIN, ['destroy' => [$keyId]]);
		$this->assertNull($this->tokenUserId($ownToken));
		$this->assertSame(User::ID_SUPER_ADMIN, $this->tokenUserId($adminToken));
	}

	public function testDestroyingKeysDeletesAllTheirTokens(): void
	{
		$a = $this->createKey(User::ID_SUPER_ADMIN, $this->manager->id)['created']['new']['id'];
		$b = $this->createKey(User::ID_SUPER_ADMIN, $this->manager->id)['created']['new']['id'];
		$tokenA = $this->storedToken($a);
		$tokenB = $this->storedToken($b);

		$this->actAs(User::ID_SUPER_ADMIN);
		$this->assertTrue(Key::delete(['id' => [$a, $b]]));

		$this->assertNull($this->tokenUserId($tokenA));
		$this->assertNull($this->tokenUserId($tokenB));
	}
}
