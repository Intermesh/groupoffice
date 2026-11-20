<?php
namespace go\core\model;

use go\core\auth\TemporaryState;
use go\core\exception\Forbidden;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The system groups Administrators, Everyone and Internal can't be deleted, not even by an administrator.
 */
class GroupTest extends TestCase
{
	private $authState;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
	}

	protected function tearDown(): void
	{
		go()->setAuthState($this->authState);
	}

	public static function systemGroupProvider(): array
	{
		return [
			'Administrators' => [Group::ID_ADMINS],
			'Everyone' => [Group::ID_EVERYONE],
			'Internal' => [Group::ID_INTERNAL],
		];
	}

	#[DataProvider('systemGroupProvider')]
	public function testSystemGroupCannotBeDeleted(int $groupId): void
	{
		try {
			Group::delete(['id' => $groupId]);
			$this->fail("Deleting system group " . $groupId . " didn't throw Forbidden");
		} catch (Forbidden) {
			// expected
		}

		$this->assertNotNull(Group::findById($groupId), "System group " . $groupId . " was deleted");
	}

	public function testOtherGroupCanBeDeleted(): void
	{
		$group = new Group();
		$group->name = uniqid('deletable');
		$this->assertTrue($group->save());

		$this->assertTrue(Group::delete(['id' => $group->id]));
		$this->assertNull(Group::findById($group->id));
	}
}
