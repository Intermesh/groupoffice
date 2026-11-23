<?php
namespace go\modules\community\tasks\controller;

use go\core\auth\TemporaryState;
use go\core\model\Acl;
use go\core\model\Group;
use go\core\model\Module;
use go\core\model\Permission;
use go\core\model\User;
use go\modules\community\tasks\model\TaskList as TaskListModel;
use PHPUnit\Framework\TestCase;

/**
 * The module right "mayChangeTasklists" is only for creating and deleting lists. Manage permission on a list is
 * needed to change its name and permissions.
 */
class TaskListTest extends TestCase
{
	private $authState;

	/** Group with access to the tasks module, but without "mayChangeTasklists" */
	private static Group $group;
	private static User $writer;
	private static User $manager;
	private ?TaskListModel $taskList = null;

	public static function setUpBeforeClass(): void
	{
		$authState = go()->getAuthState();
		self::actAs(User::ID_SUPER_ADMIN);

		try {
			self::$group = new Group();
			self::$group->name = uniqid('tasklistusers');
			self::assertTrue(self::$group->save(), self::$group->getValidationErrorsAsString());

			$module = Module::findByName('community', 'tasks');
			$module->permissions[self::$group->id] = (new Permission($module))
				->setRights(['mayRead' => true, 'mayChangeTasklists' => false]);
			self::assertTrue($module->save(), $module->getValidationErrorsAsString());

			self::$writer = self::newUser();
			self::$manager = self::newUser();
		} catch (\Throwable $e) {
			self::tearDownAfterClass();
			throw $e;
		} finally {
			go()->setAuthState($authState);
		}
	}

	public static function tearDownAfterClass(): void
	{
		$authState = go()->getAuthState();
		self::actAs(User::ID_SUPER_ADMIN);

		$ids = [];
		foreach (['writer', 'manager'] as $prop) {
			if (isset(self::$$prop)) {
				$ids[] = self::$$prop->id;
			}
		}
		if ($ids) {
			User::delete(['id' => $ids]);
		}

		if (isset(self::$group->id)) {
			$module = Module::findByName('community', 'tasks');
			unset($module->permissions[self::$group->id]);
			$module->save();
			Group::delete(['id' => self::$group->id]);
		}

		go()->setAuthState($authState);
	}

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		self::actAs(User::ID_SUPER_ADMIN);

		$this->taskList = new TaskListModel();
		$this->taskList->name = uniqid('list');
		$this->taskList->setAcl([
			Group::ID_INTERNAL => null,
			Group::ID_EVERYONE => null,
			Group::ID_ADMINS => Acl::LEVEL_MANAGE,
			self::$writer->getPersonalGroup()->id => Acl::LEVEL_WRITE,
			self::$manager->getPersonalGroup()->id => Acl::LEVEL_MANAGE
		]);
		$this->assertTrue($this->taskList->save(), $this->taskList->getValidationErrorsAsString());
	}

	protected function tearDown(): void
	{
		self::actAs(User::ID_SUPER_ADMIN);
		if (isset($this->taskList->id)) {
			TaskListModel::delete(['id' => $this->taskList->id]);
		}
		go()->setAuthState($this->authState);
	}

	private static function newUser(): User
	{
		$name = uniqid('tasklistuser');
		$user = new User();
		$user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		// Not in the Internal group, which has "mayChangeTasklists" by default
		$user->groups = [Group::ID_EVERYONE, self::$group->id];
		self::assertTrue($user->save(), $user->getValidationErrorsAsString());

		return $user;
	}

	private static function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
	}

	private function update(array $properties): array
	{
		return (new TaskList())->set(['update' => [$this->taskList->id => $properties]])->getArray();
	}

	public function testManagerWithoutModuleRightMayRename(): void
	{
		self::actAs(self::$manager->id);
		$result = $this->update(['name' => 'Renamed']);

		$this->assertArrayHasKey($this->taskList->id, $result['updated'] ?? [], var_export($result, true));
		$this->assertEquals('Renamed', TaskListModel::findById($this->taskList->id)->name);
	}

	public function testWriterMayNotRename(): void
	{
		self::actAs(self::$writer->id);
		$result = $this->update(['name' => 'Renamed']);

		$this->assertArrayHasKey($this->taskList->id, $result['notUpdated'] ?? [], var_export($result, true));
		$this->assertNotEquals('Renamed', TaskListModel::findById($this->taskList->id)->name);
	}

	public function testWriterMayUnsubscribe(): void
	{
		self::actAs(self::$writer->id);
		$result = $this->update(['isSubscribed' => false]);

		$this->assertArrayHasKey($this->taskList->id, $result['updated'] ?? [], var_export($result, true));
	}

	public function testManagerWithoutModuleRightMayNotCreate(): void
	{
		self::actAs(self::$manager->id);
		$result = (new TaskList())->set(['create' => ['new' => ['name' => uniqid('list')]]])->getArray();

		$this->assertArrayHasKey('new', $result['notCreated'] ?? [], var_export($result, true));
	}

	public function testManagerWithoutModuleRightMayNotDelete(): void
	{
		self::actAs(self::$manager->id);
		$result = (new TaskList())->set(['destroy' => [$this->taskList->id]])->getArray();

		$this->assertArrayHasKey($this->taskList->id, $result['notDestroyed'] ?? [], var_export($result, true));
		$this->assertNotNull(TaskListModel::findById($this->taskList->id));
	}
}
