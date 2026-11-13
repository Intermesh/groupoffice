<?php
namespace go\core\orm;

use go\core\auth\TemporaryState;
use go\core\fs\Blob;
use go\core\model\Acl;
use go\core\model\User;
use go\core\validate\ErrorCode;
use GO\Files\Model\Folder;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\addressbook\model\Contact;
use PHPUnit\Framework\TestCase;

/**
 * Blobs set with Entity::setAttachments() are added to the files folder of the entity by the files module. So they get
 * a database record and are checked for write permission on the folder, the quota and a valid name.
 */
class EntityAttachmentsTest extends TestCase
{
	private $authState;

	/*
	 * The users that only act and the address book are created once for the class. Tests change the contact, so every
	 * test gets a new one.
	 */
	private static User $reader;
	private static User $writer;
	private static AddressBook $addressBook;
	private Contact $contact;

	public static function setUpBeforeClass(): void
	{
		$authState = go()->getAuthState();
		self::actAs(User::ID_SUPER_ADMIN);
		try {
			self::$reader = self::createUser('eareader');
			self::$writer = self::createUser('eawriter');

			self::$addressBook = new AddressBook();
			self::$addressBook->name = uniqid('eabook');
			self::$addressBook->setAcl([
				self::$reader->getPersonalGroup()->id => Acl::LEVEL_READ,
				self::$writer->getPersonalGroup()->id => Acl::LEVEL_WRITE
			]);
			self::assertTrue(self::$addressBook->save(), self::$addressBook->getValidationErrorsAsString());
		} catch (\Throwable $e) {
			self::tearDownAfterClass();
			throw $e;
		} finally {
			go()->setAuthState($authState);
		}
	}

	public static function tearDownAfterClass(): void
	{
		$authState = go()->getAuthState();
		self::actAs(User::ID_SUPER_ADMIN);
		if (isset(self::$addressBook) && isset(self::$addressBook->id)) {
			AddressBook::delete(['id' => self::$addressBook->id]);
		}
		$userIds = [];
		foreach (['reader', 'writer'] as $prop) {
			if (isset(self::$$prop)) {
				$userIds[] = self::$$prop->id;
			}
		}
		if ($userIds) {
			Contact::delete(['goUserId' => $userIds]);
			User::delete(['id' => $userIds]);
		}
		go()->setAuthState($authState);
	}

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		self::actAs(User::ID_SUPER_ADMIN);

		$this->contact = new Contact();
		$this->contact->addressBookId = self::$addressBook->id;
		$this->contact->firstName = 'Entity';
		$this->contact->lastName = uniqid('Attachments');
		$this->assertTrue($this->contact->save(), $this->contact->getValidationErrorsAsString());
	}

	protected function tearDown(): void
	{
		self::actAs(User::ID_SUPER_ADMIN);
		$contact = Contact::findById($this->contact->id);
		if ($contact) {
			// The folder can exist without the contact referring to it when a save failed after creating it
			$old = \GO::setIgnoreAclPermissions(true);
			$folder = Folder::model()->findByPath($contact->buildFilesPath());
			if ($folder) {
				$folder->delete(true);
			}
			\GO::setIgnoreAclPermissions($old);
		}
		Contact::delete(['id' => $this->contact->id]);
		go()->setAuthState($this->authState);
	}

	private static function createUser(string $prefix): User
	{
		$name = uniqid($prefix);
		$user = new User();
		$user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		self::assertTrue($user->save(), var_export($user->getValidationErrors(), true));
		return $user;
	}

	private static function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
		\GO::session()->setCurrentUser($userId);
	}

	private function createBlob(string $name, string $content): Blob
	{
		$blob = Blob::fromString($content);
		$blob->name = $name;
		$blob->type = 'text/markdown';
		$this->assertTrue($blob->save(), $blob->getValidationErrorsAsString());
		return $blob;
	}

	/**
	 * Save the contact with attachments as the given user
	 *
	 * @return Contact The contact that was saved, to check its validation errors
	 */
	private function saveWithAttachments(int $userId, array $attachments): Contact
	{
		self::actAs($userId);
		$contact = Contact::findById($this->contact->id);
		$contact->setAttachments($attachments);
		$contact->save();
		return $contact;
	}

	private function filesFolder(): ?Folder
	{
		$old = \GO::setIgnoreAclPermissions(true);
		try {
			return Folder::model()->findByPath(Contact::findById($this->contact->id)->buildFilesPath()) ?: null;
		} finally {
			\GO::setIgnoreAclPermissions($old);
		}
	}

	public function testWriterAddsAFileWithADatabaseRecord(): void
	{
		$content = uniqid('# Functional design ');
		$blob = $this->createBlob('design.md', $content);

		$contact = $this->saveWithAttachments(self::$writer->id, [['blobId' => $blob->id]]);
		$this->assertFalse($contact->hasValidationErrors(), $contact->getValidationErrorsAsString());

		$folder = $this->filesFolder();
		$this->assertNotNull($folder);
		$this->assertEquals($folder->id, Contact::findById($this->contact->id)->filesFolderId);

		$file = $folder->hasFile('design.md');
		$this->assertNotEmpty($file, 'The file has no record in fs_files');		$this->assertEquals($content, $file->fsFile->getContents());

		// A second save doesn't add the same attachments again
		$contact->jobTitle = 'Changed';
		$this->assertTrue($contact->save(), $contact->getValidationErrorsAsString());
		$this->assertEmpty($folder->hasFile('design (1).md'));
	}

	public function testReaderMayNotAddAFile(): void
	{
		$blob = $this->createBlob('design.md', uniqid('reader '));

		$contact = $this->saveWithAttachments(self::$reader->id, [['blobId' => $blob->id]]);

		$this->assertEquals(ErrorCode::FORBIDDEN, $contact->getValidationError('attachments')['code'] ?? null);
		$this->assertNotOnDiskOrRecorded('design.md');
	}

	public function testNoFileIsAddedWhenALaterAttachmentFails(): void
	{
		$blob = $this->createBlob('first.md', uniqid('first '));

		$contact = $this->saveWithAttachments(self::$writer->id, [
			['blobId' => $blob->id],
			['blobId' => str_repeat('0', 40)]
		]);

		$this->assertEquals(ErrorCode::NOT_FOUND, $contact->getValidationError('attachments')['code'] ?? null);
		$this->assertNotOnDiskOrRecorded('first.md');
	}

	/**
	 * A failed save rolls back the database, but not the disk. So check both.
	 */
	private function assertNotOnDiskOrRecorded(string $name): void
	{
		$folder = $this->filesFolder();
		if (!$folder) {
			return;
		}
		$this->assertEmpty($folder->hasFile($name), "$name has a record in fs_files");
		$this->assertFalse($folder->fsFolder->child($name), "$name was written to disk");
	}

	public function testInvalidNameIsRefused(): void
	{
		$blob = $this->createBlob('../escape.md', uniqid('escape '));

		$contact = $this->saveWithAttachments(self::$writer->id, [['blobId' => $blob->id]]);

		$this->assertEquals(ErrorCode::INVALID_INPUT, $contact->getValidationError('attachments')['code'] ?? null);
		$folderPath = rtrim(\GO::config()->file_storage_path, '/') . '/' . Contact::findById($this->contact->id)->buildFilesPath();
		$this->assertFileDoesNotExist(dirname($folderPath) . '/escape.md', 'The file was written outside the folder');
	}

	public function testQuotaIsChecked(): void
	{
		self::actAs(User::ID_SUPER_ADMIN);
		$writer = User::findById(self::$writer->id);
		$writer->disk_quota = 1; // MB
		$writer->disk_usage = 1024 * 1024; // bytes, so the quota is full
		$this->assertTrue($writer->save(), $writer->getValidationErrorsAsString());

		try {
			// The files module checks the quota of the legacy user model, which is cached for the whole test run
			\GO::modelCache()->remove(\GO\Base\Model\User::class);

			$blob = $this->createBlob('design.md', uniqid('quota '));
			$contact = $this->saveWithAttachments(self::$writer->id, [['blobId' => $blob->id]]);

			$this->assertEquals(ErrorCode::INVALID_INPUT, $contact->getValidationError('attachments')['code'] ?? null);
			$this->assertNotOnDiskOrRecorded('design.md');
		} finally {
			self::actAs(User::ID_SUPER_ADMIN);
			$writer = User::findById(self::$writer->id);
			$writer->disk_quota = null;
			$writer->disk_usage = 0;
			$writer->save();
			\GO::modelCache()->remove(\GO\Base\Model\User::class);
		}
	}

	public function testUnknownBlobIsRefused(): void
	{
		$contact = $this->saveWithAttachments(self::$writer->id, [['blobId' => str_repeat('0', 40)]]);

		$this->assertEquals(ErrorCode::NOT_FOUND, $contact->getValidationError('attachments')['code'] ?? null);
	}
}
