<?php
namespace go\core\controller;

use go\core\auth\TemporaryState;
use go\core\exception\Forbidden;
use go\core\model\User as UserModel;
use PHPUnit\Framework\TestCase;

/**
 * System/about returns the version to every user, but the usage of the instance only to administrators. Only
 * administrators may generate demo data.
 */
class SystemTest extends TestCase
{
	private static $authState;
	private static UserModel $user;

	public static function setUpBeforeClass(): void
	{
		self::$authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(UserModel::ID_SUPER_ADMIN));

		$name = uniqid('systemuser');
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
		go()->getCache()->delete("latestVersion");
	}

	protected function setUp(): void
	{
		// No request to GitHub in the tests
		go()->getCache()->set("latestVersion", "999.0.0", true, 60);
	}

	protected function tearDown(): void
	{
		go()->setAuthState(new TemporaryState(UserModel::ID_SUPER_ADMIN));
	}

	private function about(int $userId): array
	{
		go()->setAuthState(new TemporaryState($userId));
		return (new System())->about();
	}

	public function testUserGetsNoUsage()
	{
		$about = $this->about(self::$user->id);

		$this->assertSame(go()->getVersion(), $about['version']);
		$this->assertArrayNotHasKey('usage', $about);
	}

	public function testAdminGetsUsage()
	{
		$about = $this->about(UserModel::ID_SUPER_ADMIN);

		$this->assertArrayHasKey('usage', $about);
		$this->assertGreaterThan(0, $about['usage']['users']);
		$this->assertArrayHasKey('database', $about['usage']);
	}

	public function testLatestVersion()
	{
		if(!go()->getConfig()['checkForUpdates']) {
			$this->markTestSkipped("checkForUpdates is off");
		}

		$this->assertSame("999.0.0", $this->about(self::$user->id)['latestVersion']);

		// Not newer than the installed version
		go()->getCache()->set("latestVersion", "1.0.0", true, 60);
		$this->assertNull($this->about(self::$user->id)['latestVersion']);
	}

	public function testUserMayNotGenerateDemoData()
	{
		go()->setAuthState(new TemporaryState(self::$user->id));

		$this->expectException(Forbidden::class);
		(new System())->demo();
	}
}
