<?php
namespace go\core\controller;

use go\core\auth\TemporaryState;
use go\core\exception\Forbidden;
use go\core\model\Group;
use go\core\model\Module as ModuleModel;
use go\core\model\User as UserModel;
use go\modules\community\notes\model\NoteBook;
use PHPUnit\Framework\TestCase;

/**
 * Acl/overview lists the permissions of all users and groups, so only administrators may call it.
 */
class AclTest extends TestCase
{
	private static $authState;
	private static ?UserModel $user = null;
	private static ?NoteBook $noteBook = null;

	public static function setUpBeforeClass(): void
	{
		self::$authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(UserModel::ID_SUPER_ADMIN));

		try {
			$name = uniqid('acluser');
			self::$user = new UserModel();
			self::$user->setValues([
				'username' => $name,
				'displayName' => $name,
				'email' => $name . '@example.com',
				'password' => 'Pa55word!!xyz'
			]);
			if (!self::$user->save()) {
				throw new \Exception(self::$user->getValidationErrorsAsString());
			}

			self::$noteBook = new NoteBook();
			self::$noteBook->name = "Acl overview test " . uniqid();
			self::$noteBook->setAcl([Group::ID_ADMINS => 50]);
			if (!self::$noteBook->save()) {
				throw new \Exception(self::$noteBook->getValidationErrorsAsString());
			}
		} catch (\Throwable $e) {
			self::tearDownAfterClass();
			throw $e;
		}
	}

	public static function tearDownAfterClass(): void
	{
		go()->setAuthState(new TemporaryState(UserModel::ID_SUPER_ADMIN));
		if (isset(self::$noteBook) && !self::$noteBook->isNew()) {
			NoteBook::delete(['id' => self::$noteBook->id]);
		}
		if (isset(self::$user) && !self::$user->isNew()) {
			UserModel::delete(['id' => self::$user->id]);
		}
		go()->setAuthState(self::$authState);
	}

	protected function tearDown(): void
	{
		go()->setAuthState(new TemporaryState(UserModel::ID_SUPER_ADMIN));
	}

	public function testUserMayNotGetOverview()
	{
		go()->setAuthState(new TemporaryState(self::$user->id));
		$this->expectException(Forbidden::class);
		(new Acl())->overview([]);
	}

	public function testAdminGetsOverviewWithNamesAndPackage()
	{
		$notes = ModuleModel::findByName('community', 'notes');

		$result = (new Acl())->overview(['filter' => ['conditions' => [
			['groupId' => Group::ID_ADMINS],
			['moduleId' => $notes->id],
			['type' => 'groups']
		]]]);

		$rows = array_values(array_filter($result['list'], fn($r) => $r->type === 'NoteBook' && $r->entityId == self::$noteBook->id));
		$this->assertCount(1, $rows);
		$this->assertEquals(self::$noteBook->name, $rows[0]->name);
		$this->assertEquals('community', $rows[0]->package);
		$this->assertEquals('notes', $rows[0]->module);
		$this->assertEquals(50, $rows[0]->level);
	}
}
