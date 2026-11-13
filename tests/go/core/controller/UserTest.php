<?php
namespace go\core\controller;

use go\core\auth\TemporaryState;
use go\core\exception\Forbidden;
use go\core\model\Acl;
use go\core\model\Group;
use go\core\model\Module;
use go\core\model\Permission;
use go\core\model\User as UserModel;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\addressbook\model\Contact;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Users with the "mayChangeUsers" right may not change administrators and users may not change the properties that only
 * administrators and user managers may change.
 */
class UserTest extends TestCase
{
	private $authState;

	/*
	 * The users that only act or are only read, and the user manager group, are created once for the class. Tests
	 * change $user, so every test gets a new one.
	 */
	private static Group $managerGroup;
	private static UserModel $manager;
	private static UserModel $otherUser;
	private static UserModel $admin;
	private UserModel $user;

	public static function setUpBeforeClass(): void
	{
		$authState = go()->getAuthState();
		self::actAs(UserModel::ID_SUPER_ADMIN);

		self::$manager = self::newUser();
		self::$otherUser = self::newUser();
		self::$admin = self::newUser([Group::ID_ADMINS]);

		// A group with the "mayChangeUsers" right on the core module
		self::$managerGroup = new Group();
		self::$managerGroup->name = uniqid('usermanagers');
		self::$managerGroup->users = [self::$manager->id];
		self::assertTrue(self::$managerGroup->save());

		$core = Module::findByName('core', 'core');
		$permission = new Permission($core);
		$permission->setRights(['mayChangeUsers' => true]);
		$core->permissions[self::$managerGroup->id] = $permission;
		self::assertTrue($core->save());

		go()->setAuthState($authState);
	}

	public static function tearDownAfterClass(): void
	{
		$authState = go()->getAuthState();
		self::actAs(UserModel::ID_SUPER_ADMIN);

		$core = Module::findByName('core', 'core');
		unset($core->permissions[self::$managerGroup->id]);
		$core->save();

		Group::delete(['id' => self::$managerGroup->id]);
		UserModel::delete(['id' => [self::$manager->id, self::$otherUser->id, self::$admin->id]]);

		go()->setAuthState($authState);
	}

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		self::actAs(UserModel::ID_SUPER_ADMIN);

		$this->user = self::newUser();
	}

	protected function tearDown(): void
	{
		self::actAs(UserModel::ID_SUPER_ADMIN);

		UserModel::delete(['id' => array_merge([$this->user->id], $this->createdIds)]);

		go()->setAuthState($this->authState);
	}

	private static function newUser(array $groups = []): UserModel
	{
		$name = uniqid('ctrluser');
		$user = new UserModel();
		$user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		if ($groups) {
			$user->groups = array_merge([Group::ID_EVERYONE], $groups);
		}
		self::assertTrue($user->save(), var_export($user->getValidationErrors(), true));

		return $user;
	}

	private static function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
	}

	private function update(int $actingUserId, int $id, array $properties): array
	{
		self::actAs($actingUserId);
		$result = (new User())->set(['update' => [$id => $properties]]);

		return $result->getArray();
	}

	/*
	 * The "created" and "updated" lists of a set result also contain the entities that were saved earlier in the same
	 * process, like the users created in setUp() and setUpBeforeClass(). So check "notUpdated" and the stored data.
	 */
	private function assertUpdated(array $result, int $id): void
	{
		$this->assertArrayNotHasKey($id, $result['notUpdated'] ?? [], var_export($result['notUpdated'][$id] ?? null, true));
		$this->assertArrayHasKey($id, $result['updated'] ?? []);
	}

	private function assertForbidden(array $result, int $id): void
	{
		$this->assertSame('forbidden', $result['notUpdated'][$id]->type ?? null);
	}

	public function testUserManagerMayNotChangeAnAdministrator()
	{
		$result = $this->update(self::$manager->id, self::$admin->id, ['recoveryEmail' => 'attacker@example.com']);

		$this->assertForbidden($result, self::$admin->id);
		$this->assertNotSame('attacker@example.com', UserModel::findById(self::$admin->id)->recoveryEmail);
	}

	public function testUserManagerMayChangeAUser()
	{
		$result = $this->update(self::$manager->id, $this->user->id, ['recoveryEmail' => 'new@example.com', 'disk_quota' => 1000]);

		$this->assertUpdated($result, $this->user->id);
		$user = UserModel::findById($this->user->id);
		$this->assertSame('new@example.com', $user->recoveryEmail);
		$this->assertEquals(1000, $user->disk_quota);
	}

	public static function managerProperties(): array
	{
		return [
			'enabled' => ['enabled', false],
			'forcePasswordChange' => ['forcePasswordChange', true],
			'disk_quota' => ['disk_quota', 999999],
			'disk_usage' => ['disk_usage', 1],
			'homeDir' => ['homeDir', 'users/somewhere-else'],
			'loginCount' => ['loginCount', 9999],
			'lastLogin' => ['lastLogin', '2001-01-01T00:00:00Z'],
		];
	}

	#[DataProvider('managerProperties')]
	public function testUserMayNotChangeManagerPropertiesOfOwnAccount(string $property, $value)
	{
		$before = UserModel::findById($this->user->id)->toArray([$property]);

		$result = $this->update($this->user->id, $this->user->id, [$property => $value]);

		$this->assertForbidden($result, $this->user->id);
		$this->assertEquals($before, UserModel::findById($this->user->id)->toArray([$property]));
	}

	public function testUserMayChangeOwnDisplayName()
	{
		$result = $this->update($this->user->id, $this->user->id, ['displayName' => 'Changed name']);

		$this->assertUpdated($result, $this->user->id);
		$this->assertSame('Changed name', UserModel::findById($this->user->id)->displayName);
	}

	public function testUserMayNotChangeAnotherUser()
	{
		$result = $this->update($this->user->id, self::$otherUser->id, ['displayName' => 'Changed name']);

		$this->assertForbidden($result, self::$otherUser->id);
		$this->assertNotSame('Changed name', UserModel::findById(self::$otherUser->id)->displayName);
	}

	public function testUserMayNotChangeOwnPersonalGroupMembers()
	{
		$this->expectException(Forbidden::class);
		$this->update($this->user->id, $this->user->id, ['personalGroup' => ['users' => [$this->user->id, self::$otherUser->id]]]);
	}

	public function testUserManagerMayNotChangePersonalGroupMembers()
	{
		$this->expectException(Forbidden::class);
		$this->update(self::$manager->id, $this->user->id, ['personalGroup' => ['users' => [$this->user->id, self::$manager->id]]]);
	}

	public function testUserManagerMayChangeWhoCanSeeAUser()
	{
		// The personal group must stay in its own ACL
		$groupId = UserModel::findById($this->user->id)->getPersonalGroup()->id;
		$acl = [$groupId => Acl::LEVEL_MANAGE, self::$otherUser->getPersonalGroup()->id => Acl::LEVEL_READ];

		$result = $this->update(self::$manager->id, $this->user->id, ['personalGroup' => ['acl' => $acl]]);

		$this->assertUpdated($result, $this->user->id);
		$stored = Group::findById($groupId)->toArray(['acl'])['acl'];
		foreach($acl as $aclGroupId => $level) {
			$this->assertEquals($level, $stored[$aclGroupId] ?? null);
		}
	}

	private function create(int $actingUserId, array $properties): array
	{
		$name = uniqid('ctrlnew');
		$properties = array_merge([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		], $properties);

		self::actAs($actingUserId);
		$result = (new User())->set(['create' => ['new' => $properties]])->getArray();

		if(isset($result['created']['new'])) {
			$this->createdIds[] = $result['created']['new']['id'];
		}
		return $result;
	}

	private array $createdIds = [];

	public static function privilegedGroups(): array
	{
		return [
			'admins' => [[Group::ID_EVERYONE, Group::ID_ADMINS]],
			'admins only' => [[Group::ID_ADMINS]],
		];
	}

	#[DataProvider('privilegedGroups')]
	public function testUserManagerMayNotCreateUserInOtherGroups(array $groups)
	{
		$result = $this->create(self::$manager->id, ['groups' => $groups]);

		$this->assertArrayNotHasKey('new', $result['created'] ?? []);
		$this->assertSame('forbidden', $result['notCreated']['new']->type ?? null);
	}

	public function testUserManagerMayNotCreateUserInManagerGroup()
	{
		// Even a group the manager is in may grant more than the default groups
		$result = $this->create(self::$manager->id, ['groups' => [Group::ID_EVERYONE, self::$managerGroup->id]]);

		$this->assertArrayNotHasKey('new', $result['created'] ?? []);
	}

	public function testUserManagerMayCreateUserInDefaultGroups()
	{
		self::actAs(UserModel::ID_SUPER_ADMIN);
		$defaultGroups = \go\core\model\Settings::get()->getDefaultGroups();

		$result = $this->create(self::$manager->id, ['groups' => array_merge([Group::ID_EVERYONE], $defaultGroups)]);

		$this->assertArrayHasKey('new', $result['created'] ?? [], var_export($result['notCreated'] ?? null, true));
		$user = UserModel::findById($result['created']['new']['id']);
		$this->assertFalse($user->isAdmin());
	}

	public function testUserManagerMayCreateUserWithoutGroups()
	{
		$result = $this->create(self::$manager->id, []);

		$this->assertArrayHasKey('new', $result['created'] ?? [], var_export($result['notCreated'] ?? null, true));
	}

	public function testUserManagerMayNotAddAdminGroupOnSaveOutsideController()
	{
		// Other paths, like imports, save the model without the controller's canCreate()
		self::actAs(self::$manager->id);
		$name = uniqid('ctrlmodel');
		$user = new UserModel();
		$user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz',
			'groups' => [Group::ID_EVERYONE, Group::ID_ADMINS]
		]);

		$this->assertFalse($user->save());
		$this->assertArrayHasKey('groups', $user->getValidationErrors());
	}

	public function testAdminMayCreateAnAdministrator()
	{
		$result = $this->create(UserModel::ID_SUPER_ADMIN, ['groups' => [Group::ID_EVERYONE, Group::ID_ADMINS]]);

		$this->assertArrayHasKey('new', $result['created'] ?? [], var_export($result['notCreated'] ?? null, true));
		$this->assertTrue(UserModel::findById($result['created']['new']['id'])->isAdmin());
	}

	public function testAdminMayChangeAnAdministrator()
	{
		$result = $this->update(UserModel::ID_SUPER_ADMIN, self::$admin->id, ['recoveryEmail' => 'admin@example.com', 'disk_quota' => 1000]);

		$this->assertUpdated($result, self::$admin->id);
	}

	public function testUserManagerMayNotUseProfileOfAnotherUser()
	{
		self::actAs(UserModel::ID_SUPER_ADMIN);
		$adminProfile = Contact::findForUser(self::$admin->id);
		$this->assertNotEmpty($adminProfile);

		$result = $this->create(self::$manager->id, ['profile' => ['id' => $adminProfile->id]]);

		$this->assertArrayNotHasKey('new', $result['created'] ?? []);
		self::actAs(UserModel::ID_SUPER_ADMIN);
		$this->assertEquals($adminProfile->id, Contact::findForUser(self::$admin->id)->id);
	}

	public function testUserManagerMayNotUseContactWithoutWritePermissionAsProfile()
	{
		self::actAs(UserModel::ID_SUPER_ADMIN);
		$addressBook = new AddressBook();
		$addressBook->name = uniqid('profiletest');
		$this->assertTrue($addressBook->save());
		$contact = new Contact();
		$contact->addressBookId = $addressBook->id;
		$contact->firstName = 'Unlinked';
		$this->assertTrue($contact->save());

		try {
			$result = $this->create(self::$manager->id, ['profile' => ['id' => $contact->id]]);

			$this->assertArrayNotHasKey('new', $result['created'] ?? []);
			self::actAs(UserModel::ID_SUPER_ADMIN);
			$this->assertNull(Contact::findById($contact->id)->goUserId);
		} finally {
			self::actAs(UserModel::ID_SUPER_ADMIN);
			AddressBook::delete(['id' => $addressBook->id]);
		}
	}

	public function testUserManagerMayUseUnlinkedContactAsProfile()
	{
		self::actAs(UserModel::ID_SUPER_ADMIN);
		$addressBook = new AddressBook();
		$addressBook->name = uniqid('profiletest');
		$addressBook->setAcl([self::$managerGroup->id => Acl::LEVEL_WRITE]);
		$this->assertTrue($addressBook->save());
		$contact = new Contact();
		$contact->addressBookId = $addressBook->id;
		$contact->firstName = 'Unlinked';
		$this->assertTrue($contact->save());

		try {
			$result = $this->create(self::$manager->id, ['profile' => ['id' => $contact->id]]);

			$this->assertArrayHasKey('new', $result['created'] ?? [], var_export($result['notCreated'] ?? null, true));
			self::actAs(UserModel::ID_SUPER_ADMIN);
			$this->assertEquals($result['created']['new']['id'], Contact::findById($contact->id)->goUserId);
		} finally {
			self::actAs(UserModel::ID_SUPER_ADMIN);
			AddressBook::delete(['id' => $addressBook->id]);
		}
	}

	public static function recoveryAddresses(): array
	{
		return [
			'email' => ['email'],
			'recoveryEmail' => ['recoveryEmail'],
		];
	}

	#[DataProvider('recoveryAddresses')]
	public function testUserMayNotChangeOwnRecoveryAddressWithoutPassword(string $property)
	{
		$before = UserModel::findById($this->user->id)->$property;

		$result = $this->update($this->user->id, $this->user->id, [$property => uniqid('attacker') . '@example.com']);

		$this->assertArrayHasKey($this->user->id, $result['notUpdated'] ?? []);
		$this->assertArrayHasKey('currentPassword', $result['notUpdated'][$this->user->id]->validationErrors ?? []);
		$this->assertSame($before, UserModel::findById($this->user->id)->$property);
	}

	#[DataProvider('recoveryAddresses')]
	public function testUserMayNotChangeOwnRecoveryAddressWithWrongPassword(string $property)
	{
		$before = UserModel::findById($this->user->id)->$property;

		$result = $this->update($this->user->id, $this->user->id, [$property => uniqid('attacker') . '@example.com', 'currentPassword' => 'wrong']);

		$this->assertArrayHasKey($this->user->id, $result['notUpdated'] ?? []);
		$this->assertSame($before, UserModel::findById($this->user->id)->$property);
	}

	#[DataProvider('recoveryAddresses')]
	public function testUserMayChangeOwnRecoveryAddressWithPassword(string $property)
	{
		$email = uniqid('new') . '@example.com';

		$result = $this->update($this->user->id, $this->user->id, [$property => $email, 'currentPassword' => 'Pa55word!!xyz']);

		$this->assertUpdated($result, $this->user->id);
		$this->assertSame($email, UserModel::findById($this->user->id)->$property);
	}

	public function testUserManagerNeedsPasswordForOwnEmail()
	{
		$result = $this->update(self::$manager->id, self::$manager->id, ['email' => uniqid('attacker') . '@example.com']);

		$this->assertArrayHasKey(self::$manager->id, $result['notUpdated'] ?? []);
	}

	public function testUserManagerMayChangeEmailOfAUserWithoutPassword()
	{
		$email = uniqid('new') . '@example.com';

		$result = $this->update(self::$manager->id, $this->user->id, ['email' => $email]);

		$this->assertUpdated($result, $this->user->id);
		$this->assertSame($email, UserModel::findById($this->user->id)->email);
	}

	public function testUserMayNotChangeOwnEmailThroughProfileWithoutPassword()
	{
		// Put the profile in an address book the user may write
		self::actAs(UserModel::ID_SUPER_ADMIN);
		$addressBook = new AddressBook();
		$addressBook->name = uniqid('profiletest');
		$addressBook->setAcl([$this->user->getPersonalGroup()->id => Acl::LEVEL_WRITE]);
		$this->assertTrue($addressBook->save());
		$profile = Contact::findForUser($this->user->id);
		$this->assertNotEmpty($profile);
		$profile->addressBookId = $addressBook->id;
		$this->assertTrue($profile->save(), $profile->getValidationErrorsAsString());
		$before = UserModel::findById($this->user->id)->email;

		try {
			self::actAs($this->user->id);
			$result = (new \go\modules\community\addressbook\controller\Contact())->set(['update' => [$profile->id => [
				'emailAddresses' => [['email' => uniqid('attacker') . '@example.com']]
			]]])->getArray();

			$this->assertArrayHasKey($profile->id, $result['notUpdated'] ?? []);
			$this->assertSame($before, UserModel::findById($this->user->id)->email);
		} finally {
			self::actAs(UserModel::ID_SUPER_ADMIN);
			AddressBook::delete(['id' => $addressBook->id]);
		}
	}

	public function testUserManagerCreatesUserWithProfile()
	{
		$result = $this->create(self::$manager->id, []);

		$this->assertArrayHasKey('new', $result['created'] ?? [], var_export($result['notCreated'] ?? null, true));
		self::actAs(UserModel::ID_SUPER_ADMIN);
		$this->assertNotEmpty(Contact::findForUser($result['created']['new']['id']));
	}

	public function testOwnEmailChangeWithPasswordUpdatesProfile()
	{
		$email = uniqid('new') . '@example.com';

		$result = $this->update($this->user->id, $this->user->id, ['email' => $email, 'currentPassword' => 'Pa55word!!xyz']);

		$this->assertUpdated($result, $this->user->id);
		self::actAs(UserModel::ID_SUPER_ADMIN);
		$profile = Contact::findForUser($this->user->id);
		$this->assertContains($email, array_map(fn($e) => $e->email, $profile->emailAddresses));
	}

	public function testUserMayChangeOwnProfileWithItsId()
	{
		self::actAs(UserModel::ID_SUPER_ADMIN);
		$profile = Contact::findForUser($this->user->id);

		$result = $this->update($this->user->id, $this->user->id, ['profile' => ['id' => $profile->id]]);

		$this->assertUpdated($result, $this->user->id);
		self::actAs(UserModel::ID_SUPER_ADMIN);
		$this->assertEquals($profile->id, Contact::findForUser($this->user->id)->id);
	}
}
