<?php
namespace go\modules\community\carddav;

use go\core\auth\TemporaryState;
use go\core\model\Acl;
use go\core\model\Group;
use go\core\model\Module as ModuleModel;
use go\core\model\Permission;
use go\core\model\User;
use go\modules\community\addressbook\model\AddressBook;
use PHPUnit\Framework\TestCase;

/**
 * The general /dav/ entry point only checks access to the DAV module. Address books may only be served to users that
 * also have access to the CardDAV module.
 */
class BackendTest extends TestCase
{
	private $authState;
	private User $user;
	private Group $group;
	private AddressBook $addressBook;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));

		$name = uniqid('carddav');
		$this->group = new Group();
		$this->group->name = $name . 'group';
		$this->assertTrue($this->group->save());

		// Not in the internal group, so only the modules of the test group are available
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->user->groups = [Group::ID_EVERYONE, $this->group->id];
		$this->assertTrue($this->user->save(), $this->user->getValidationErrorsAsString());

		$this->grant('legacy', 'dav');
		$this->grant('community', 'addressbook');

		$this->addressBook = new AddressBook();
		$this->addressBook->name = $name;
		$this->addressBook->setAcl([$this->group->id => Acl::LEVEL_READ]);
		$this->assertTrue($this->addressBook->save(), $this->addressBook->getValidationErrorsAsString());

		go()->getDbConnection()->insertIgnore('sync_addressbook_user', [
			'addressBookId' => $this->addressBook->id,
			'userId' => $this->user->id,
			'isDefault' => 0
		])->execute();
	}

	protected function tearDown(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		go()->getDbConnection()->delete('sync_addressbook_user', ['userId' => $this->user->id])->execute();
		AddressBook::delete(['id' => $this->addressBook->id]);
		foreach ([['legacy', 'dav'], ['community', 'addressbook'], ['community', 'carddav']] as [$package, $name]) {
			$module = ModuleModel::findByName($package, $name);
			if (isset($module->permissions[$this->group->id])) {
				unset($module->permissions[$this->group->id]);
				$module->save();
			}
		}
		User::delete(['id' => $this->user->id]);
		Group::delete(['id' => $this->group->id]);
		go()->setAuthState($this->authState);
	}

	private function grant(string $package, string $name): void
	{
		$module = ModuleModel::findByName($package, $name);
		$module->permissions[$this->group->id] = new Permission($module);
		$this->assertTrue($module->save());
	}

	private function addressBookIds(): array
	{
		go()->setAuthState(new TemporaryState($this->user->id));
		$_SERVER['HTTP_USER_AGENT'] = 'DAVx5/4.3';
		$books = (new Backend())->getAddressBooksForUser('principals/' . $this->user->username);
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		return array_column($books, 'id');
	}

	public function testNoAddressBooksWithoutCardDAVModule(): void
	{
		$this->assertFalse(ModuleModel::isAvailableFor('community', 'carddav', $this->user->id));
		$this->assertEquals([], $this->addressBookIds());
	}

	public function testAddressBooksWithCardDAVModule(): void
	{
		$this->grant('community', 'carddav');
		$this->assertContains($this->addressBook->id, $this->addressBookIds());
	}
}
