<?php

namespace go\core\model;

use PHPUnit\Framework\TestCase;

class PermissionTest extends TestCase
{
	private int $moduleId;
	/** @var int[] */
	private array $groupIds = [];
	private int $userId = 1;

	protected function setUp(): void
	{
		$db = go()->getDbConnection();
		$db->insert('core_module', ['name' => 'permissiontest', 'package' => 'test', 'version' => 0, 'enabled' => false])->execute();
		$this->moduleId = (int) $db->getPDO()->lastInsertId();

		foreach (['permissiontest1', 'permissiontest2'] as $name) {
			$db->insert('core_group', ['name' => $name, 'createdBy' => $this->userId])->execute();
			$groupId = (int) $db->getPDO()->lastInsertId();
			$db->insert('core_user_group', ['groupId' => $groupId, 'userId' => $this->userId])->execute();
			$this->groupIds[] = $groupId;
		}
	}

	protected function tearDown(): void
	{
		// cascades to core_permission and core_user_group
		go()->getDbConnection()->delete('core_group', ['id' => $this->groupIds])->execute();
		go()->getDbConnection()->delete('core_module', ['id' => $this->moduleId])->execute();
	}

	private function grant(int $groupId, int $rights): void
	{
		go()->getDbConnection()->insert('core_permission', ['moduleId' => $this->moduleId, 'groupId' => $groupId, 'rights' => $rights])->execute();
	}

	public function testNoRecordReturnsNull()
	{
		$this->assertNull(Permission::findUserRights($this->moduleId, $this->userId));
	}

	public function testRecordWithoutRightsGrantsRead()
	{
		$this->grant($this->groupIds[0], 0);
		$this->assertSame(0, Permission::findUserRights($this->moduleId, $this->userId));
	}

	public function testRightsOfGroupsAreCombined()
	{
		$this->grant($this->groupIds[0], 1);
		$this->grant($this->groupIds[1], 2);
		$this->assertSame(3, Permission::findUserRights($this->moduleId, $this->userId));
	}

	public function testOtherUsersGroupsAreIgnored()
	{
		$this->grant($this->groupIds[0], 1);
		$this->assertNull(Permission::findUserRights($this->moduleId, -1));
	}
}
