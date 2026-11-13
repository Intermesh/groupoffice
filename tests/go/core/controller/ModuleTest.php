<?php
namespace go\core\controller;

use go\core\auth\TemporaryState;
use go\core\model\Acl;
use go\core\model\Group;
use go\core\model\Module as ModuleModel;
use go\core\model\Settings;
use go\core\model\User as UserModel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Module/set may only change module settings and default ACL's for module managers. A refused update must not leave
 * changes behind in the database or in the settings object.
 */
class ModuleTest extends TestCase
{
	private $authState;
	private UserModel $user;
	private int $coreId;
	private array $defaultGroups;
	private bool $allowRegistration;
	private ?array $groupDefaultAcl;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(UserModel::ID_SUPER_ADMIN));

		$name = uniqid('moduleuser');
		$this->user = new UserModel();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), var_export($this->user->getValidationErrors(), true));

		$this->coreId = ModuleModel::findByName('core', 'core')->id;
		$this->defaultGroups = $this->storedDefaultGroups();
		$this->allowRegistration = (bool) Settings::get()->allowRegistration;
		$this->groupDefaultAcl = Group::entityType()->getDefaultAcl();
	}

	protected function tearDown(): void
	{
		go()->setAuthState(new TemporaryState(UserModel::ID_SUPER_ADMIN));

		$this->reloadSettings();
		$settings = Settings::get();
		$settings->setDefaultGroups($this->defaultGroups);
		$settings->allowRegistration = $this->allowRegistration;
		$settings->save();

		$aclId = Group::entityType()->getDefaultAclId();
		go()->getDbConnection()->delete('core_acl_group', ['aclId' => $aclId])->execute();
		foreach($this->groupDefaultAcl ?? [] as $groupId => $level) {
			go()->getDbConnection()->insert('core_acl_group', ['aclId' => $aclId, 'groupId' => $groupId, 'level' => $level])->execute();
		}

		UserModel::delete(['id' => $this->user->id]);
		$this->reloadSettings();

		go()->setAuthState($this->authState);
	}

	private function storedDefaultGroups(): array
	{
		$groups = (new \go\core\db\Query)->selectSingleValue('groupId')->from('core_group_default_group')->all();
		sort($groups);
		return array_map('intval', $groups);
	}

	private function reloadSettings(): void
	{
		Settings::flushCache();
		go()->getCache()->delete(Settings::class);
	}

	private function update(int $userId, array $properties): array
	{
		go()->setAuthState(new TemporaryState($userId));
		return (new Module())->set(['update' => [$this->coreId => $properties]])->getArray();
	}

	public static function defaultGroupPatches(): array
	{
		return [
			'nested' => [['settings' => ['defaultGroups' => [Group::ID_ADMINS, Group::ID_INTERNAL], 'allowRegistration' => true]]],
			'pointer' => [['settings/defaultGroups' => [Group::ID_ADMINS, Group::ID_INTERNAL], 'settings/allowRegistration' => true]],
		];
	}

	#[DataProvider('defaultGroupPatches')]
	public function testUserMayNotChangeCoreSettings(array $patch)
	{
		$result = $this->update($this->user->id, $patch);

		$this->assertSame('forbidden', $result['notUpdated'][$this->coreId]->type ?? null);
		$this->assertSame($this->defaultGroups, $this->storedDefaultGroups());

		// Not in the settings object of this request either
		$this->assertNotContains((string) Group::ID_ADMINS, Settings::get()->getDefaultGroups());
		$this->assertSame($this->allowRegistration, (bool) Settings::get()->allowRegistration);

		$this->reloadSettings();
		$this->assertNotContains((string) Group::ID_ADMINS, Settings::get()->getDefaultGroups());
		$this->assertSame($this->allowRegistration, (bool) Settings::get()->allowRegistration);
	}

	public static function defaultAclPatches(): array
	{
		return [
			'nested' => [false],
			'pointer' => [true],
		];
	}

	#[DataProvider('defaultAclPatches')]
	public function testUserMayNotChangeDefaultAcl(bool $pointer)
	{
		$acl = [$this->user->getPersonalGroup()->id => Acl::LEVEL_MANAGE];
		$patch = $pointer ? ['entities/Group/defaultAcl' => $acl] : ['entities' => ['Group' => ['defaultAcl' => $acl]]];

		$result = $this->update($this->user->id, $patch);

		$this->assertSame('forbidden', $result['notUpdated'][$this->coreId]->type ?? null);
		$this->assertSame($this->groupDefaultAcl, Group::entityType()->getDefaultAcl());
	}

	/**
	 * 6.8 doesn't support JSON pointer patches like "settings/defaultGroups" in Module/set. They are refused for users
	 * above, but an administrator can only use the nested form.
	 */
	public static function adminDefaultGroupPatches(): array
	{
		return array_intersect_key(self::defaultGroupPatches(), ['nested' => true]);
	}

	#[DataProvider('adminDefaultGroupPatches')]
	public function testAdminMayChangeCoreSettings(array $patch)
	{
		$result = $this->update(UserModel::ID_SUPER_ADMIN, $patch);

		$this->assertArrayNotHasKey($this->coreId, $result['notUpdated'] ?? [], var_export($result['notUpdated'][$this->coreId] ?? null, true));
		$this->assertSame([Group::ID_ADMINS, Group::ID_INTERNAL], $this->storedDefaultGroups());

		$this->reloadSettings();
		$this->assertTrue((bool) Settings::get()->allowRegistration);
	}

	public function testAdminMayChangeDefaultAcl()
	{
		$groupId = $this->user->getPersonalGroup()->id;
		$result = $this->update(UserModel::ID_SUPER_ADMIN, ['entities' => ['Group' => ['defaultAcl' => [$groupId => Acl::LEVEL_READ]]]]);

		$this->assertArrayNotHasKey($this->coreId, $result['notUpdated'] ?? [], var_export($result['notUpdated'][$this->coreId] ?? null, true));
		$this->assertSame(Acl::LEVEL_READ, Group::entityType()->getDefaultAcl()[$groupId] ?? null);
	}
}
