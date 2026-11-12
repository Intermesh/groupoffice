<?php
namespace go\modules\legacy\core;

use go\core\auth\TemporaryState;
use go\core\model\Group;
use go\core\model\User;
use GO\Base\Exception\AccessDenied;
use GO\Base\Model\Acl;
use GO\Core\Controller\AclGroupController;
use PHPUnit\Framework\TestCase;

/**
 * AclUsersGroups has no ACL of its own, so the controller must check that the user may manage the ACL before it changes
 * the level of a group. The manage permission and the ACL can't be taken from the client.
 */
class AclGroupControllerTest extends TestCase
{
	private $authState;
	private bool $ignoreAcl;
	private bool $disableModelCache;

	private User $user;
	private int $personalGroupId;
	private Acl $acl;
	private Acl $otherAcl;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->ignoreAcl = \GO::$ignoreAclPermissions;
		$this->disableModelCache = \GO::$disableModelCache;

		// Models remember the permission level of the user they were first used by. The users change in this test.
		\GO::$disableModelCache = true;

		$name = uniqid('aclgroup');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), var_export($this->user->getValidationErrors(), true));
		$this->personalGroupId = $this->user->getPersonalGroup()->id;

		$this->actAs(User::ID_SUPER_ADMIN);
		\GO::$ignoreAclPermissions = true;

		$this->acl = $this->createAcl();
		$this->acl->addGroup(Group::ID_EVERYONE, Acl::READ_PERMISSION);

		// An ACL the user has no access to
		$this->otherAcl = $this->createAcl();

		\GO::$ignoreAclPermissions = false;
	}

	protected function tearDown(): void
	{
		go()->setAuthState($this->authState);
		\GO::session()->setCurrentUser(User::ID_SUPER_ADMIN);
		\GO::$ignoreAclPermissions = true;

		$this->acl->delete();
		$this->otherAcl->delete();
		User::delete(['id' => $this->user->id]);

		\GO::$ignoreAclPermissions = $this->ignoreAcl;
		\GO::$disableModelCache = $this->disableModelCache;
	}

	private function createAcl(): Acl
	{
		$acl = new Acl();
		$acl->usedIn = 'test';
		$acl->ownedBy = User::ID_SUPER_ADMIN;
		$this->assertTrue($acl->save());

		return $acl;
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
		\GO::session()->setCurrentUser($userId);
	}

	private function setUserLevel(int $level): void
	{
		go()->getDbConnection()->replace('core_acl_group', [
			'aclId' => $this->acl->id,
			'groupId' => $this->personalGroupId,
			'level' => $level
		])->execute();
	}

	private function updateRecord(array $record, array $params = []): void
	{
		(new AclGroupController())->actionUpdateRecord(array_merge([
			'model_id' => $this->acl->id,
			'record' => json_encode($record)
		], $params));
	}

	private function getLevel(int $aclId, int $groupId): ?int
	{
		$level = go()->getDbConnection()
			->selectSingleValue('level')
			->from('core_acl_group')
			->where(['aclId' => $aclId, 'groupId' => $groupId])
			->single();

		return $level === null ? null : (int) $level;
	}

	public function testUserWithReadPermissionCannotRaiseOwnLevel()
	{
		$this->setUserLevel(Acl::READ_PERMISSION);
		$this->actAs($this->user->id);

		try {
			// The client sending the manage permission must not help
			$this->updateRecord(
				['id' => $this->personalGroupId, 'level' => Acl::MANAGE_PERMISSION],
				['currentUserHasManagePermission' => 1]
			);
			$this->fail('A user without manage permission changed the level of a group');
		} catch (AccessDenied $e) {
			$this->assertTrue(true);
		}

		$this->assertSame(Acl::READ_PERMISSION, $this->getLevel($this->acl->id, $this->personalGroupId));
	}

	public function testUserWithWritePermissionCannotChangeLevel()
	{
		$this->setUserLevel(Acl::WRITE_PERMISSION);
		$this->actAs($this->user->id);

		try {
			$this->updateRecord(['id' => Group::ID_EVERYONE, 'level' => Acl::DELETE_PERMISSION]);
			$this->fail('A user without manage permission changed the level of a group');
		} catch (AccessDenied $e) {
			$this->assertTrue(true);
		}

		$this->assertSame(Acl::READ_PERMISSION, $this->getLevel($this->acl->id, Group::ID_EVERYONE));
	}

	public function testUserWithManagePermissionCanChangeLevel()
	{
		$this->setUserLevel(Acl::MANAGE_PERMISSION);
		$this->actAs($this->user->id);

		$this->updateRecord(['id' => Group::ID_EVERYONE, 'level' => Acl::WRITE_PERMISSION]);

		$this->assertSame(Acl::WRITE_PERMISSION, $this->getLevel($this->acl->id, Group::ID_EVERYONE));
	}

	public function testManagerCannotMoveGroupToOtherAcl()
	{
		$this->setUserLevel(Acl::MANAGE_PERMISSION);
		$this->actAs($this->user->id);

		$this->updateRecord([
			'id' => $this->personalGroupId,
			'level' => Acl::MANAGE_PERMISSION,
			'aclId' => $this->otherAcl->id
		]);

		$this->assertNull($this->getLevel($this->otherAcl->id, $this->personalGroupId));
		$this->assertSame(Acl::MANAGE_PERMISSION, $this->getLevel($this->acl->id, $this->personalGroupId));
	}
}
