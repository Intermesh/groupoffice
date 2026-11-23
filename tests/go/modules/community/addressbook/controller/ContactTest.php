<?php

namespace go\modules\community\addressbook\controller;

use go\core\auth\TemporaryState;
use go\core\model\Acl;
use go\core\model\Group;
use go\core\model\Module;
use go\core\model\Permission;
use go\core\model\User;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\addressbook\model\Contact as ContactModel;
use PHPUnit\Framework\TestCase;

/**
 * The profile contact of a user (goUserId) is copied to the user. Its e-mail address is used for password recovery, so
 * only those who may change the user may link a contact to a user or change the name and e-mail of a profile contact.
 */
class ContactTest extends TestCase
{
	private $authState;
	private string $prefix;
	private User $attacker;
	private User $victim;
	private User $admin;
	private User $manager;
	private Group $managerGroup;
	private AddressBook $addressBook;
	private array $userIds = [];

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->actAs(User::ID_SUPER_ADMIN);

		$this->prefix = uniqid('contactuser');
		$this->attacker = $this->newUser('attacker');
		$this->victim = $this->newUser('victim');
		$this->admin = $this->newUser('admin', [Group::ID_EVERYONE, Group::ID_ADMINS]);
		$this->manager = $this->newUser('manager');

		$this->managerGroup = new Group();
		$this->managerGroup->name = $this->prefix . 'managers';
		$this->managerGroup->users = [$this->manager->id];
		$this->assertTrue($this->managerGroup->save());

		$core = Module::findByName('core', 'core');
		$permission = new Permission($core);
		$permission->setRights(['mayChangeUsers' => true]);
		$core->permissions[$this->managerGroup->id] = $permission;
		$this->assertTrue($core->save());

		// The attacker and the manager may write in this address book
		$this->addressBook = new AddressBook();
		$this->addressBook->name = $this->prefix;
		$this->addressBook->setAcl([
			$this->attacker->getPersonalGroup()->id => Acl::LEVEL_WRITE,
			$this->manager->getPersonalGroup()->id => Acl::LEVEL_WRITE
		]);
		$this->assertTrue($this->addressBook->save(), $this->addressBook->getValidationErrorsAsString());
	}

	protected function tearDown(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);

		$core = Module::findByName('core', 'core');
		unset($core->permissions[$this->managerGroup->id]);
		$core->save();
		Group::delete(['id' => $this->managerGroup->id]);

		AddressBook::delete(['id' => $this->addressBook->id]);
		User::delete(['id' => $this->userIds]);

		go()->setAuthState($this->authState);
	}

	private function newUser(string $name, array $groups = []): User
	{
		$name = $this->prefix . $name;
		$user = new User();
		$user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		if ($groups) {
			$user->groups = $groups;
		}
		$this->assertTrue($user->save(), $user->getValidationErrorsAsString());
		$this->userIds[] = $user->id;
		return $user;
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
	}

	/**
	 * Remove the profile contact of a user, like users that were created before the address book was installed.
	 */
	private function removeProfile(User $user): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		ContactModel::delete(['goUserId' => $user->id]);
	}

	/**
	 * Make a contact in the shared address book the profile of a user
	 */
	private function profileInAddressBook(User $user): ContactModel
	{
		$this->removeProfile($user);
		$contact = new ContactModel();
		$contact->addressBookId = $this->addressBook->id;
		$contact->firstName = $user->displayName;
		$contact->goUserId = $user->id;
		$this->assertTrue($contact->save(), $contact->getValidationErrorsAsString());
		return $contact;
	}

	private function set(int $actingUserId, array $params): array
	{
		$this->actAs($actingUserId);
		return (new Contact())->set($params)->getArray();
	}

	private function storedUser(User $user): User
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		return User::findById($user->id);
	}

	private function attackerContact(array $values = []): array
	{
		return array_merge([
			'addressBookId' => $this->addressBook->id,
			'firstName' => 'Attacker',
			'emailAddresses' => [['email' => $this->prefix . '-evil@example.com']]
		], $values);
	}

	public function testUserMayNotCreateContactForAnotherUser(): void
	{
		$this->removeProfile($this->victim);

		$result = $this->set($this->attacker->id, ['create' => ['new' => $this->attackerContact(['goUserId' => $this->victim->id])]]);

		$this->assertArrayHasKey('new', $result['notCreated'] ?? []);
		$this->assertSame($this->victim->email, $this->storedUser($this->victim)->email);
		$this->assertEmpty(ContactModel::findForUser($this->victim->id));
	}

	public function testUserMayNotLinkContactToAnotherUser(): void
	{
		$this->removeProfile($this->victim);
		$this->actAs(User::ID_SUPER_ADMIN);
		$contact = new ContactModel();
		$contact->setValues($this->attackerContact());
		$this->assertTrue($contact->save(), $contact->getValidationErrorsAsString());

		$result = $this->set($this->attacker->id, ['update' => [$contact->id => ['goUserId' => $this->victim->id]]]);

		$this->assertArrayHasKey($contact->id, $result['notUpdated'] ?? []);
		$this->assertSame($this->victim->email, $this->storedUser($this->victim)->email);
		$this->assertEmpty(ContactModel::findForUser($this->victim->id));
	}

	public function testUserMayNotTakeProfileOfAnotherUser(): void
	{
		$profile = $this->profileInAddressBook($this->admin);
		$this->removeProfile($this->attacker);

		$result = $this->set($this->attacker->id, ['update' => [$profile->id => ['goUserId' => $this->attacker->id]]]);

		$this->assertArrayHasKey($profile->id, $result['notUpdated'] ?? []);
		$this->assertEquals($profile->id, ContactModel::findForUser($this->admin->id)->id);
	}

	public function testUserMayNotChangeEmailOfAnotherUserThroughProfile(): void
	{
		$profile = $this->profileInAddressBook($this->victim);

		$result = $this->set($this->attacker->id, ['update' => [$profile->id => [
			'emailAddresses' => [['email' => $this->prefix . '-evil@example.com']]
		]]]);

		$this->assertArrayHasKey($profile->id, $result['notUpdated'] ?? []);
		$this->assertSame($this->victim->email, $this->storedUser($this->victim)->email);
	}

	public function testUserManagerMayNotChangeEmailOfAdministratorThroughProfile(): void
	{
		$profile = $this->profileInAddressBook($this->admin);

		$result = $this->set($this->manager->id, ['update' => [$profile->id => [
			'emailAddresses' => [['email' => $this->prefix . '-evil@example.com']]
		]]]);

		$this->assertArrayHasKey($profile->id, $result['notUpdated'] ?? []);
		$this->assertSame($this->admin->email, $this->storedUser($this->admin)->email);
	}

	public function testUserManagerMayChangeEmailOfUserThroughProfile(): void
	{
		$profile = $this->profileInAddressBook($this->victim);
		$email = $this->prefix . '-new@example.com';

		$result = $this->set($this->manager->id, ['update' => [$profile->id => [
			'emailAddresses' => [['email' => $email]]
		]]]);

		$this->assertArrayNotHasKey($profile->id, $result['notUpdated'] ?? [], var_export($result['notUpdated'][$profile->id] ?? null, true));
		$this->assertSame($email, $this->storedUser($this->victim)->email);
	}

	public function testUserManagerMayLinkContactToUser(): void
	{
		$this->removeProfile($this->victim);

		$result = $this->set($this->manager->id, ['create' => ['new' => $this->attackerContact(['firstName' => 'Victim', 'emailAddresses' => [], 'goUserId' => $this->victim->id])]]);

		$this->assertArrayHasKey('new', $result['created'] ?? [], var_export($result['notCreated'] ?? null, true));
		$this->assertEquals($result['created']['new']['id'], ContactModel::findForUser($this->victim->id)->id);
	}

	public function testUserMayChangeOwnNameThroughProfile(): void
	{
		$profile = $this->profileInAddressBook($this->attacker);

		$result = $this->set($this->attacker->id, ['update' => [$profile->id => ['firstName' => 'Changed', 'lastName' => 'Name']]]);

		$this->assertArrayNotHasKey($profile->id, $result['notUpdated'] ?? [], var_export($result['notUpdated'][$profile->id] ?? null, true));
		$this->assertSame('Changed Name', $this->storedUser($this->attacker)->displayName);
	}

	public function testLoadVcfFromUnknownFile(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		$this->expectException(\go\core\exception\NotFound::class);
		(new Contact())->loadVCF(['fileId' => 999999999]);
	}

	public function testLoadVcfNeedsCreatePermissionOnTheDefaultAddressBook(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		$private = new AddressBook();
		$private->name = uniqid('private');
		$private->setAcl([Group::ID_INTERNAL => null, Group::ID_EVERYONE => null, Group::ID_ADMINS => Acl::LEVEL_MANAGE]);
		$this->assertTrue($private->save());

		try {
			// The user points the default address book to a book they can't write
			$user = User::findById($this->attacker->id, ['addressBookSettings']);
			$user->addressBookSettings->setDefaultAddressBookId($private->id);
			$this->assertTrue($user->save(), $user->getValidationErrorsAsString());

			go()->setAuthState(new TemporaryState($this->attacker->id));
			$this->expectExceptionMessage('You do not have create permissions for this address book');
			(new Contact())->loadVCF(['fileId' => 1]);
		} finally {
			go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
			AddressBook::delete(['id' => $private->id]);
		}
	}
}
