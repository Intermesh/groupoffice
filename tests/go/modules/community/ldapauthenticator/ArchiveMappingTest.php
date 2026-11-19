<?php

namespace go\modules\community\ldapauthenticator;

use go\core\auth\TemporaryState;
use go\core\ldap\Record;
use go\core\model\Group;
use go\core\model\User;
use go\modules\community\addressbook\model\Contact;
use PHPUnit\Framework\TestCase;

/**
 * An LDAP record without a connection, with the attributes given
 */
class ArchiveMappingTestRecord extends Record
{
	public function __construct(private readonly array $attrs)
	{
		parent::__construct();
	}

	public function getAttributes()
	{
		return $this->attrs;
	}

	public function __get($name)
	{
		return $this->attrs[strtolower($name)] ?? null;
	}
}

/**
 * The "archived" key of the ldapMapping config archives users in the LDAP sync
 */
class ArchiveMappingTest extends TestCase
{
	private static ?Group $extraGroup = null;
	private static ?User $nonAdmin = null;

	private array $config;
	private $authState;
	private ?User $user = null;

	public static function setUpBeforeClass(): void
	{
		try {
			if (!go()->getModule('community', 'ldapauthenticator')) {
				self::assertNotFalse(Module::get()->install(), 'Could not install ldapauthenticator module');
			}

			self::$extraGroup = new Group();
			self::$extraGroup->name = uniqid('ldaparchive');
			self::assertTrue(self::$extraGroup->save());

			self::$nonAdmin = self::newUser(uniqid('ldapnonadmin'));
		} catch (\Throwable $e) {
			self::tearDownAfterClass();
			throw $e;
		}
	}

	public static function tearDownAfterClass(): void
	{
		if (self::$nonAdmin) {
			User::delete(['id' => self::$nonAdmin->id]);
		}
		if (self::$extraGroup) {
			Group::delete(['id' => self::$extraGroup->id]);
		}
	}

	protected function setUp(): void
	{
		$this->config = go()->getConfig();
		$this->authState = go()->getAuthState();

		go()->setConfig(array_merge($this->config, ['ldapMapping' => [
			'email' => 'mail',
			'archived' => function ($record) {
				return !isset($record->affiliation);
			}
		]]));

		go()->setAuthState(new TemporaryState(1));

		$this->user = self::newUser(uniqid('ldaparchived'), [self::$extraGroup->id]);
	}

	protected function tearDown(): void
	{
		go()->setConfig($this->config);
		go()->setAuthState($this->authState);

		if ($this->user) {
			User::delete(['id' => $this->user->id]);
		}
	}

	private static function newUser(string $username, array $groups = []): User
	{
		$user = new User();
		$user->setValues([
			'username' => $username,
			'displayName' => $username,
			'email' => $username . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		if ($groups) {
			$user->groups = array_merge([Group::ID_EVERYONE], $groups);
		}
		self::assertTrue($user->save(), var_export($user->getValidationErrors(), true));

		return $user;
	}

	private function record(bool $affiliation): Record
	{
		$attrs = [
			'cn' => [$this->user->displayName],
			'mail' => [$this->user->email]
		];
		if ($affiliation) {
			$attrs['affiliation'] = ['staff'];
		}
		return new ArchiveMappingTestRecord($attrs);
	}

	private function sync(bool $affiliation): User
	{
		$user = User::findById($this->user->id);
		Module::ldapRecordToUser($user->username, $this->record($affiliation), $user);
		if ($user->isModified()) {
			$this->assertTrue($user->save(), var_export($user->getValidationErrors(), true));
		}
		return $user;
	}

	private function isInArchivedAddressBook(): bool
	{
		$profile = Contact::findForUser($this->user->id, ['addressBookId']);
		return $profile->addressBookId == go()->getSettings()->archivedUsersAddressBook()->id;
	}

	public function testUserIsArchived(): void
	{
		$this->sync(false);

		$user = User::findById($this->user->id);
		$this->assertFalse($user->enabled);
		$this->assertNotContains(self::$extraGroup->id, $user->groups);
		$this->assertTrue($this->isInArchivedAddressBook());
	}

	public function testArchivedUserIsNotArchivedAgain(): void
	{
		$this->sync(false);

		$user = User::findById($this->user->id);
		Module::ldapRecordToUser($user->username, $this->record(false), $user);
		$this->assertFalse($user->isModified(), "An archived user must not be archived on every sync");
	}

	public function testDisabledUserIsArchived(): void
	{
		// disabled by an admin, but not archived yet
		$user = User::findById($this->user->id);
		$user->enabled = false;
		$this->assertTrue($user->save());

		$this->sync(false);

		$this->assertTrue($this->isInArchivedAddressBook());
		$this->assertNotContains(self::$extraGroup->id, User::findById($this->user->id)->groups);
	}

	public function testDeletedProfileIsRecreatedInArchivedAddressBook(): void
	{
		$this->sync(false);

		Contact::delete(['goUserId' => $this->user->id]);
		$this->assertNull(Contact::findForUser($this->user->id));

		// the next sync archives again and moves the new profile to the archived users
		$this->sync(false);
		$this->assertTrue($this->isInArchivedAddressBook());

		// and the sync after that leaves the user alone
		$user = User::findById($this->user->id);
		Module::ldapRecordToUser($user->username, $this->record(false), $user);
		$this->assertFalse($user->isModified());
	}

	public function testDeletedProfileIsRecreatedOnce(): void
	{
		Contact::delete(['goUserId' => $this->user->id]);

		$this->sync(true);
		$this->sync(true);

		$count = go()->getDbConnection()
			->selectSingleValue('count(*)')
			->from('addressbook_contact', 'c')
			->join('addressbook_email_address', 'e', 'e.contactId = c.id')
			->where('e.email', '=', $this->user->email)
			->single();

		$this->assertEquals(1, $count, "The sync must link the new profile to the user instead of adding a contact on every run");
		$this->assertNotNull(Contact::findForUser($this->user->id));
	}

	public function testUserIsLeftAsIsWhenNotArchived(): void
	{
		$this->sync(false);
		$this->sync(true);

		$user = User::findById($this->user->id);
		$this->assertFalse($user->enabled, "Un-archiving must leave the user as it is");
		$this->assertTrue($this->isInArchivedAddressBook());
	}

	public function testActiveUserIsNotArchived(): void
	{
		$this->sync(true);

		$user = User::findById($this->user->id);
		$this->assertTrue($user->enabled);
		$this->assertContains(self::$extraGroup->id, $user->groups);
		$this->assertFalse($this->isInArchivedAddressBook());
	}

	public function testUserWithoutRightsOnlyDisables(): void
	{
		// at login the current user may not change users
		go()->setAuthState(new TemporaryState(self::$nonAdmin->id));

		$this->sync(false);

		go()->setAuthState(new TemporaryState(1));
		$user = User::findById($this->user->id);
		$this->assertFalse($user->enabled);
		$this->assertContains(self::$extraGroup->id, $user->groups);
		$this->assertFalse($this->isInArchivedAddressBook());
	}
}
