<?php

namespace go\modules\community\addressbook\convert;

use go\core\auth\TemporaryState;
use go\core\fs\File;
use go\core\model\Acl;
use go\core\model\User;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\addressbook\model\Contact;
use PHPUnit\Framework\TestCase;

/**
 * vCard imports may only change contacts the user may write and may not create contacts
 * in address books the user may not create in.
 */
class VCardTest extends TestCase
{
	private $authState;
	private string $prefix;
	private User $user;

	/** @var AddressBook[] */
	private array $addressBooks = [];

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));

		$this->prefix = uniqid('vcardtest');
		$this->user = new User();
		$this->user->setValues([
			'username' => $this->prefix,
			'displayName' => $this->prefix,
			'email' => $this->prefix . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), $this->user->getValidationErrorsAsString());

		$groupId = $this->user->getPersonalGroup()->id;
		foreach (['write' => Acl::LEVEL_WRITE, 'create' => Acl::LEVEL_CREATE, 'read' => Acl::LEVEL_READ, 'none' => null] as $name => $level) {
			$addressBook = new AddressBook();
			$addressBook->name = $this->prefix . $name;
			if ($level) {
				$addressBook->setAcl([$groupId => $level]);
			}
			$this->assertTrue($addressBook->save(), $addressBook->getValidationErrorsAsString());
			$this->addressBooks[$name] = $addressBook;
		}
	}

	protected function tearDown(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		AddressBook::delete(['id' => array_map(fn($ab) => $ab->id, $this->addressBooks)]);
		User::delete(['id' => $this->user->id]);
		go()->setAuthState($this->authState);
	}

	private function createContact(string $addressBook, string $name, bool $isOrganization = false, ?string $uid = null): Contact
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		$contact = new Contact();
		$contact->addressBookId = $this->addressBooks[$addressBook]->id;
		$contact->isOrganization = $isOrganization;
		if ($isOrganization) {
			$contact->name = $name;
		} else {
			$contact->firstName = $name;
		}
		if (isset($uid)) {
			$contact->setUid($uid);
		}
		$this->assertTrue($contact->save(), $contact->getValidationErrorsAsString());
		return $contact;
	}

	private function import(string $addressBook, string $vcard): array
	{
		$file = File::tempFile('vcf');
		$file->putContents($vcard);

		go()->setAuthState(new TemporaryState($this->user->id));
		return Contact::findConverter('vcf')->importFile($file, ['values' => ['addressBookId' => $this->addressBooks[$addressBook]->id]]);
	}

	private function vcard(string $uid, string $firstName, ?string $org = null): string
	{
		return "BEGIN:VCARD\r\nVERSION:3.0\r\nUID:" . $uid . "\r\nFN:" . $firstName . "\r\nN:;" . $firstName . ";;;\r\n" .
			(isset($org) ? "ORG:" . $org . "\r\n" : "") .
			"END:VCARD\r\n";
	}

	private function contactCount(string $addressBook): int
	{
		return (int) go()->getDbConnection()
			->selectSingleValue('count(*)')
			->from('addressbook_contact')
			->where('addressBookId', '=', $this->addressBooks[$addressBook]->id)
			->single();
	}

	private function linkedOrganizationNames(Contact $contact): array
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		return Contact::find()
			->withLink($contact)
			->andWhere('isOrganization', '=', true)
			->selectSingleValue('c.name')
			->all();
	}

	private function importedContact(string $addressBook, string $uid): Contact
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		$contact = Contact::find()->where(['addressBookId' => $this->addressBooks[$addressBook]->id, 'uid' => $uid])->single();
		$this->assertNotEmpty($contact);
		return $contact;
	}

	public function testImportDoesNotChangeContactUserMayOnlyCreate(): void
	{
		$uid = $this->prefix . '-existing';
		$contact = $this->createContact('create', 'Original', false, $uid);

		$result = $this->import('create', $this->vcard($uid, 'Changed'));

		$this->assertSame(0, $result['count']);
		$this->assertSame('Original', Contact::findById($contact->id)->firstName);
	}

	public function testImportDoesNotCreateContactInReadOnlyAddressBook(): void
	{
		$result = $this->import('read', $this->vcard($this->prefix . '-new', 'New'));

		$this->assertSame(0, $result['count']);
		$this->assertSame(0, $this->contactCount('read'));
	}

	public function testImportChangesWritableContact(): void
	{
		$uid = $this->prefix . '-writable';
		$contact = $this->createContact('write', 'Original', false, $uid);

		$result = $this->import('write', $this->vcard($uid, 'Changed'));

		$this->assertSame(1, $result['count'], implode("\n", $result['errors']));
		$this->assertSame('Changed', Contact::findById($contact->id)->firstName);
	}
}
