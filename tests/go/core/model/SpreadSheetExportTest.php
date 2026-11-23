<?php
namespace go\core\model;

use go\core\auth\TemporaryState;
use PHPUnit\Framework\TestCase;

/**
 * Saved spreadsheet exports belong to a user. Other users may use them but not change them or create them for others.
 */
class SpreadSheetExportTest extends TestCase
{
	private $authState;
	private User $owner;
	private User $other;
	private SpreadSheetExport $export;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));

		$this->owner = $this->newUser();
		$this->other = $this->newUser();

		go()->setAuthState(new TemporaryState($this->owner->id));
		$this->export = new SpreadSheetExport();
		$this->export->setValues([
			'userId' => $this->owner->id,
			'name' => 'Test export',
			'entity' => 'User',
			'columns' => ['id', 'displayName']
		]);
		$this->assertTrue($this->export->save(), var_export($this->export->getValidationErrors(), true));
	}

	protected function tearDown(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		SpreadSheetExport::delete(['id' => $this->export->id]);
		User::delete(['id' => [$this->owner->id, $this->other->id]]);
		go()->setAuthState($this->authState);
	}

	private function newUser(): User
	{
		$name = uniqid('export');
		$user = new User();
		$user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($user->save(), var_export($user->getValidationErrors(), true));
		return $user;
	}

	public function testOwnerMayManage()
	{
		go()->setAuthState(new TemporaryState($this->owner->id));
		$this->assertSame(Acl::LEVEL_MANAGE, SpreadSheetExport::findById($this->export->id)->getPermissionLevel());
	}

	public function testOtherUserMayOnlyRead()
	{
		go()->setAuthState(new TemporaryState($this->other->id));
		$export = SpreadSheetExport::findById($this->export->id);
		$this->assertSame(Acl::LEVEL_READ, $export->getPermissionLevel());
		$this->assertFalse($export->hasPermissionLevel(Acl::LEVEL_WRITE));
	}

	public function testUserCannotCreateExportForOthers()
	{
		go()->setAuthState(new TemporaryState($this->other->id));
		$export = new SpreadSheetExport();
		$export->setValues(['userId' => $this->owner->id, 'name' => 'Not mine', 'entity' => 'User']);
		$this->assertFalse($export->hasPermissionLevel(Acl::LEVEL_CREATE));
	}

	public function testUserCannotTakeOverExport()
	{
		go()->setAuthState(new TemporaryState($this->other->id));
		$export = SpreadSheetExport::findById($this->export->id);
		$export->userId = $this->other->id;
		$this->assertFalse($export->hasPermissionLevel(Acl::LEVEL_WRITE));
	}
}
