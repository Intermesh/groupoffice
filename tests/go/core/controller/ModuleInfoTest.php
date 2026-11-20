<?php
namespace go\core\controller;

use go\core\auth\TemporaryState;
use go\core\exception\Forbidden;
use go\core\model\Module as ModuleModel;
use go\core\model\User as UserModel;
use PHPUnit\Framework\TestCase;

/**
 * ModuleInfo/set installs, uninstalls, enables and deletes modules, so only administrators may call it.
 */
class ModuleInfoTest extends TestCase
{
	private static $authState;
	private static UserModel $user;

	public static function setUpBeforeClass(): void
	{
		self::$authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(UserModel::ID_SUPER_ADMIN));

		$name = uniqid('moduleinfouser');
		self::$user = new UserModel();
		self::$user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		if(!self::$user->save()) {
			go()->setAuthState(self::$authState);
			throw new \Exception(var_export(self::$user->getValidationErrors(), true));
		}
	}

	public static function tearDownAfterClass(): void
	{
		go()->setAuthState(new TemporaryState(UserModel::ID_SUPER_ADMIN));
		UserModel::delete(['id' => self::$user->id]);
		go()->setAuthState(self::$authState);
	}

	protected function tearDown(): void
	{
		go()->setAuthState(new TemporaryState(UserModel::ID_SUPER_ADMIN));
	}

	private function set(int $userId, array $params): array
	{
		go()->setAuthState(new TemporaryState($userId));
		return (new ModuleInfo())->set($params)->getArray();
	}

	private function notesEnabled(): bool
	{
		return (bool) go()->getDbConnection()
			->selectSingleValue('enabled')
			->from('core_module')
			->where(['package' => 'community', 'name' => 'notes'])
			->single();
	}

	public function testUserMayNotDisableModule()
	{
		$this->assertTrue($this->notesEnabled());

		try {
			$this->set(self::$user->id, ['update' => ['community/notes' => ['enabled' => false]]]);
			$this->fail("A user without admin rights could change a module");
		} catch (Forbidden) {
		}

		$this->assertTrue($this->notesEnabled());
	}

	public function testUserMayNotDestroyModule()
	{
		$this->expectException(Forbidden::class);
		$this->set(self::$user->id, ['destroy' => ['community/doesnotexist']]);
	}

	private function getIds(int $userId): array
	{
		go()->setAuthState(new TemporaryState($userId));
		$controller = new ModuleInfo();
		$fromGet = array_column($controller->get([])['list'], 'id');
		$fromQuery = $controller->query([])['ids'];
		sort($fromGet);
		sort($fromQuery);
		$this->assertSame($fromQuery, array_values(array_diff($fromGet, $this->unavailableIds())));
		return $fromGet;
	}

	private function unavailableIds(): array
	{
		go()->setAuthState(new TemporaryState(UserModel::ID_SUPER_ADMIN));
		$list = (new ModuleInfo())->get([])['list'];
		return array_column(array_filter($list, fn($m) => $m['status'] === 'unavailable'), 'id');
	}

	public function testUserOnlyGetsModulesTheyManage()
	{
		// The module model holds its settings, so users only get the modules they manage
		$ids = $this->getIds(self::$user->id);
		$this->assertNotContains('community/notes', $ids);
		$this->assertNotContains('community/jitsimeet', $ids);
	}

	public function testAdminGetsAllModules()
	{
		$ids = $this->getIds(UserModel::ID_SUPER_ADMIN);
		$this->assertContains('community/notes', $ids);
	}

	public function testAdminMayDisableAndEnableModule()
	{
		try {
			$this->set(UserModel::ID_SUPER_ADMIN, ['update' => ['community/notes' => ['enabled' => false]]]);
			$this->assertFalse($this->notesEnabled());
		} finally {
			$this->set(UserModel::ID_SUPER_ADMIN, ['update' => ['community/notes' => ['enabled' => true]]]);
		}
		$this->assertTrue($this->notesEnabled());
	}
}
