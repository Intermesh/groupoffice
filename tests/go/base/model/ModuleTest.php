<?php
namespace GO\Base\Model;

use go\core\auth\TemporaryState;
use go\core\model\Group;
use go\core\model\User as UserModel;
use PHPUnit\Framework\TestCase;

/**
 * Users without a permission record for a legacy module must not get access to it. BIT_OR() returns 0 on an empty set,
 * which gave every user read access to every legacy module.
 */
class ModuleTest extends TestCase
{
	private $authState;
	private bool $disableModelCache;

	private UserModel $user;
	private int $personalGroupId;
	private Module $module;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->disableModelCache = \GO::$disableModelCache;
		\GO::$disableModelCache = true;

		$this->actAs(UserModel::ID_SUPER_ADMIN);

		$name = uniqid('moduleperm');
		$this->user = new UserModel();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), var_export($this->user->getValidationErrors(), true));

		$this->personalGroupId = Group::findPersonalGroupID($this->user->id);

		// Only keep the groups that can't be left: "Everyone" and the personal group
		go()->getDbConnection()->delete('core_user_group', (new \go\core\db\Query())
			->where('userId', '=', $this->user->id)
			->andWhere('groupId', 'NOT IN', [Group::ID_EVERYONE, $this->personalGroupId])
		)->execute();

		// A legacy module that "Everyone" has no permission for
		$moduleId = go()->getDbConnection()->selectSingleValue('m.id')
			->from('core_module', 'm')
			->join('core_permission', 'p', 'p.moduleId = m.id AND p.groupId = ' . Group::ID_EVERYONE, 'LEFT')
			->where('m.package IS NULL')
			->andWhere('m.enabled', '=', true)
			->andWhere('p.moduleId IS NULL')
			->single();

		if (!$moduleId) {
			$this->markTestSkipped('No legacy module without a permission for everyone is installed');
		}

		$this->module = Module::model()->findByPk($moduleId, false, true);
	}

	protected function tearDown(): void
	{
		$this->actAs(UserModel::ID_SUPER_ADMIN);

		if (isset($this->user)) {
			go()->getDbConnection()->delete('core_permission', ['groupId' => $this->personalGroupId])->execute();
			UserModel::delete(['id' => $this->user->id]);
		}

		go()->setAuthState($this->authState);
		\GO::$disableModelCache = $this->disableModelCache;
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
		\GO::session()->setCurrentUser($userId);
	}

	private function grant(int $rights): void
	{
		go()->getDbConnection()->insert('core_permission', [
			'moduleId' => $this->module->id,
			'groupId' => $this->personalGroupId,
			'rights' => $rights
		])->execute();
	}

	public function testUserWithoutPermissionHasNoAccess()
	{
		$this->actAs($this->user->id);

		$this->assertSame(0, $this->module->getPermissionLevel());
		$this->assertFalse($this->module->getUserRights()->mayRead);
	}

	public function testUserWithPermissionMayRead()
	{
		$this->grant(0);
		$this->actAs($this->user->id);

		$this->assertSame(Acl::READ_PERMISSION, $this->module->getPermissionLevel());
	}

	public function testUserWithManageRightMayManage()
	{
		$this->grant(1);
		$this->actAs($this->user->id);

		$this->assertSame(Acl::MANAGE_PERMISSION, $this->module->getPermissionLevel());
	}

	public function testAdminMayManage()
	{
		$this->actAs(UserModel::ID_SUPER_ADMIN);

		$this->assertSame(Acl::MANAGE_PERMISSION, $this->module->getPermissionLevel());
	}
}
