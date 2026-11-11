<?php
namespace go\modules\legacy\files;

use go\core\auth\TemporaryState;
use go\core\exception\Forbidden;
use go\core\model\Acl;
use go\core\model\User;
use GO\Files\Controller\FolderController;
use GO\Files\Model\Folder;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\addressbook\model\Contact;
use PHPUnit\Framework\TestCase;

/**
 * Opening the files of an entity creates its folder and saves the entity. That requires write permission on the
 * entity. Users with read permission can open the folder once it exists.
 */
class EntityFolderPermissionTest extends TestCase
{
	private $authState;
	private User $reader;
	private User $writer;
	private AddressBook $addressBook;
	private Contact $contact;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->actAs(User::ID_SUPER_ADMIN);

		$this->reader = $this->createUser('efreader');
		$this->writer = $this->createUser('efwriter');

		$this->addressBook = new AddressBook();
		$this->addressBook->name = uniqid('efbook');
		$this->addressBook->setAcl([
			$this->reader->getPersonalGroup()->id => Acl::LEVEL_READ,
			$this->writer->getPersonalGroup()->id => Acl::LEVEL_WRITE
		]);
		$this->assertTrue($this->addressBook->save(), $this->addressBook->getValidationErrorsAsString());

		$this->contact = new Contact();
		$this->contact->addressBookId = $this->addressBook->id;
		$this->contact->firstName = 'Entity';
		$this->contact->lastName = 'Folder';
		$this->assertTrue($this->contact->save(), $this->contact->getValidationErrorsAsString());
	}

	protected function tearDown(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		$contact = Contact::findById($this->contact->id);
		if ($contact && $contact->filesFolderId) {
			$old = \GO::setIgnoreAclPermissions(true);
			$folder = Folder::model()->findByPk($contact->filesFolderId, false, true);
			if ($folder) {
				$folder->delete(true);
			}
			\GO::setIgnoreAclPermissions($old);
		}
		Contact::delete(['id' => $this->contact->id]);
		AddressBook::delete(['id' => $this->addressBook->id]);
		Contact::delete(['goUserId' => [$this->reader->id, $this->writer->id]]);
		User::delete(['id' => [$this->reader->id, $this->writer->id]]);
		go()->setAuthState($this->authState);
	}

	private function createUser(string $prefix): User
	{
		$name = uniqid($prefix);
		$user = new User();
		$user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($user->save(), var_export($user->getValidationErrors(), true));
		return $user;
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
		\GO::session()->setCurrentUser($userId);
	}

	private function checkFolder(int $userId): array
	{
		$this->actAs($userId);
		try {
			$method = new \ReflectionMethod(FolderController::class, 'actionCheckModelFolder');
			return $method->invoke(new FolderController(), ['model' => 'Contact', 'id' => $this->contact->id]);
		} finally {
			$this->actAs(User::ID_SUPER_ADMIN);
		}
	}

	public function testReaderCannotCreateTheFolder(): void
	{
		try {
			$this->checkFolder($this->reader->id);
			$this->fail('A user with read permission created the folder of a contact');
		} catch (Forbidden $e) {
			$this->assertEmpty(Contact::findById($this->contact->id)->filesFolderId);
		}
	}

	public function testReaderCanOpenTheFolderAfterAWriterCreatedIt(): void
	{
		$created = $this->checkFolder($this->writer->id);
		$this->assertNotEmpty($created['files_folder_id']);

		$this->assertEquals($created['files_folder_id'], $this->checkFolder($this->reader->id)['files_folder_id']);
	}
}
