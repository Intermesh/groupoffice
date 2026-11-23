<?php
namespace go\modules\community\davclient\controller;

use go\core\auth\TemporaryState;
use go\core\model\Group;
use go\core\model\Module as ModuleModel;
use go\core\model\Permission;
use go\core\model\User;
use go\modules\community\davclient\model\DavAccount as DavAccountModel;
use PHPUnit\Framework\TestCase;

/**
 * Users need the "Change account" permission of the DAV client module to change or delete a DAV account, also when the
 * account is their own.
 */
class DavAccountTest extends TestCase
{
	private $authState;
	private User $user;
	private Group $group;
	private ?string $accountId = null;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->actAs(User::ID_SUPER_ADMIN);

		$name = uniqid('davclient');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), $this->user->getValidationErrorsAsString());

		$this->group = new Group();
		$this->group->name = $name . 'group';
		$this->group->users = [$this->user->id];
		$this->assertTrue($this->group->save());
		$this->setRights([]);

		// An account set up by the administrator for the user. Port 1 refuses the connection, so saving doesn't wait.
		$account = new DavAccountModel();
		$account->ownerId = $this->user->id;
		$account->name = 'Account of the user';
		$account->host = '127.0.0.1:1';
		$account->username = 'user';
		$account->setPassword('secret');
		$account->refreshInterval = 30;
		$this->assertTrue($account->save(), $account->getValidationErrorsAsString());
		$this->accountId = $account->id;
	}

	protected function tearDown(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		DavAccountModel::$keepData = false;
		if (isset($this->accountId)) {
			DavAccountModel::delete(['id' => $this->accountId]);
		}

		$module = ModuleModel::findByName('community', 'davclient');
		unset($module->permissions[$this->group->id]);
		$module->save();
		Group::delete(['id' => $this->group->id]);
		User::delete(['id' => $this->user->id]);

		go()->setAuthState($this->authState);
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
	}

	private function setRights(array $rights): void
	{
		$module = ModuleModel::findByName('community', 'davclient');
		$permission = new Permission($module);
		$permission->setRights($rights);
		$module->permissions[$this->group->id] = $permission;
		$this->assertTrue($module->save());
	}

	private function set(array $params): array
	{
		$this->actAs($this->user->id);
		return (new DavAccount())->set($params)->getArray();
	}

	private function storedName(): ?string
	{
		return go()->getDbConnection()->selectSingleValue('name')->from('davclient_davaccount')
			->where('id', '=', $this->accountId)->single() ?: null;
	}

	public function testUserWithoutChangeAccountCannotUpdateAccount(): void
	{
		$result = $this->set(['update' => [$this->accountId => ['name' => 'Changed', 'host' => '127.0.0.1:2']]]);

		$this->assertArrayHasKey($this->accountId, $result['notUpdated'] ?? []);
		$this->assertSame('Account of the user', $this->storedName());
	}

	public function testUserWithoutChangeAccountCannotDestroyAccount(): void
	{
		$result = $this->set(['destroy' => [$this->accountId]]);

		$this->assertArrayHasKey($this->accountId, $result['notDestroyed'] ?? []);
		$this->assertSame('Account of the user', $this->storedName());
	}

	public function testUserWithChangeAccountCanUpdateAccount(): void
	{
		$this->setRights(['mayChangeAccount' => true]);

		$result = $this->set(['update' => [$this->accountId => ['name' => 'Changed']]]);

		$this->assertArrayHasKey($this->accountId, $result['updated'] ?? [], var_export($result, true));
		$this->assertSame('Changed', $this->storedName());
	}

	public function testUserCannotSyncAccountOfOtherUser(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		$account = new DavAccountModel();
		$account->ownerId = User::ID_SUPER_ADMIN;
		$account->name = 'Account of the admin';
		$account->host = '127.0.0.1:1';
		$account->username = 'admin';
		$account->setPassword('secret');
		$account->refreshInterval = 30;
		$this->assertTrue($account->save(), $account->getValidationErrorsAsString());

		try {
			$this->actAs($this->user->id);
			$this->expectException(\go\core\exception\NotFound::class);
			(new DavAccount())->sync(['accountId' => $account->id]);
		} finally {
			$this->actAs(User::ID_SUPER_ADMIN);
			DavAccountModel::$keepData = false;
			DavAccountModel::delete(['id' => $account->id]);
		}
	}
}
