<?php

namespace go\modules\legacy\files;

use go\core\auth\TemporaryState;
use go\core\model\User;
use GO\Base\Exception\AccessDenied;
use GO\Base\Model\Acl;
use GO\Files\Controller\FolderController;
use GO\Files\Model\File;
use GO\Files\Model\Folder;
use GO\Files\Model\TrashedItem;
use go\modules\community\addressbook\model\AddressBook;
use go\modules\community\addressbook\model\Contact;
use PHPUnit\Framework\TestCase;

/**
 * Moving a file or folder to the trash is deleting it, so it requires delete permission. The same goes for deleting it
 * permanently from the trash or restoring it.
 */
class TrashPermissionTest extends TestCase
{
	private $authState;
	private bool $ignoreAcl;
	private bool $disableModelCache;
	private User $owner;
	private User $reader;
	private Folder $ownerFolder;
	private Folder $subFolder;
	private File $file;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->ignoreAcl = \GO::$ignoreAclPermissions;
		\GO::$ignoreAclPermissions = false;
		// cached models keep the permission level of the user that loaded them
		$this->disableModelCache = \GO::$disableModelCache;
		\GO::$disableModelCache = true;

		$this->owner = $this->createUser('trashowner');
		$this->reader = $this->createUser('trashreader');

		$this->actAs($this->owner->id);
		$this->ownerFolder = Folder::model()->findHomeFolder(\GO\Base\Model\User::model()->findByPk($this->owner->id));

		file_put_contents($this->ownerFolder->fsFolder->path() . '/file.txt', 'content');
		$this->file = $this->ownerFolder->addFile('file.txt');
		$this->subFolder = $this->ownerFolder->addFolder('sub');

		\GO::$ignoreAclPermissions = true;
		Acl::model()->findByPk($this->ownerFolder->acl_id)->addGroup($this->reader->getPersonalGroup()->id, Acl::READ_PERMISSION);
		\GO::$ignoreAclPermissions = false;
	}

	protected function tearDown(): void
	{
		go()->setAuthState($this->authState);
		\GO::session()->setCurrentUser(User::ID_SUPER_ADMIN);
		\GO::$ignoreAclPermissions = true;

		foreach ([$this->file->id => 'File', $this->subFolder->id => 'Folder'] as $id => $type) {
			$cls = 'GO\\Files\\Model\\' . $type;
			$item = $cls::model()->findByPk($id, false, true, true);
			if ($item) {
				$item->delete(true);
			}
		}
		go()->getDbConnection()->delete('fs_trash', ['entityId' => [$this->file->id, $this->subFolder->id]])->execute();

		$this->ownerFolder->delete(true);
		$readerHome = Folder::model()->findByPath('users/' . $this->reader->username);
		if ($readerHome) {
			$readerHome->delete(true);
		}
		User::delete(['id' => [$this->owner->id, $this->reader->id]]);

		\GO::$ignoreAclPermissions = $this->ignoreAcl;
		\GO::$disableModelCache = $this->disableModelCache;
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

	private function invoke(string $method, array $params): mixed
	{
		$controller = new FolderController();
		$method = new \ReflectionMethod($controller, $method);
		$method->setAccessible(true);

		return $method->invoke($controller, $params);
	}

	private function trash(string $key): void
	{
		$this->invoke('_processDeletes', ['trash_keys' => json_encode([$key])]);
	}

	private function findTrashedItem(int $entityId, string $entity): TrashedItem|false
	{
		$entityTypeId = \go\core\orm\EntityType::findByName($entity)->getId();
		return TrashedItem::model()->findSingleByAttributes(['entityTypeId' => $entityTypeId, 'entityId' => $entityId]);
	}

	private function reloadFile(): File
	{
		return File::model()->findByPk($this->file->id, false, true, true);
	}

	public static function itemsToTrash(): array
	{
		return [
			'file' => ['f', 'File'],
			'folder' => ['d', 'Folder'],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('itemsToTrash')]
	public function testReaderCannotMoveToTrash(string $prefix, string $entity): void
	{
		$id = $entity == 'File' ? $this->file->id : $this->subFolder->id;

		$this->actAs($this->reader->id);
		$error = null;
		try {
			$this->trash($prefix . ':' . $id);
		} catch (\Exception $e) {
			$error = $e;
		}

		\GO::$ignoreAclPermissions = true;
		$this->assertInstanceOf(AccessDenied::class, $error);
		$this->assertFalse($this->findTrashedItem($id, $entity), 'A trash record was created for an item that was not moved to trash');
		if ($entity == 'File') {
			$this->assertEquals($this->ownerFolder->id, $this->reloadFile()->folder_id);
		} else {
			$this->assertEquals($this->ownerFolder->id, Folder::model()->findByPk($id, false, true, true)->parent_id);
		}
	}

	public function testReaderCannotDeleteFromTrash(): void
	{
		$this->actAs($this->owner->id);
		$this->trash('f:' . $this->file->id);
		$trashedItem = $this->findTrashedItem($this->file->id, 'File');
		$this->assertNotEmpty($trashedItem);

		$this->actAs($this->reader->id);
		try {
			$this->invoke('actionDeleteFromTrash', ['ids' => (string) $trashedItem->id]);
			$this->fail('A user with read permission deleted a file from the trash');
		} catch (AccessDenied $e) {
			$this->assertNotEmpty($this->reloadFile());
			$this->assertNotEmpty($this->findTrashedItem($this->file->id, 'File'));
		}
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('itemsToTrash')]
	public function testReaderCannotRestore(string $prefix, string $entity): void
	{
		$id = $entity == 'File' ? $this->file->id : $this->subFolder->id;

		$this->actAs($this->owner->id);
		$this->trash($prefix . ':' . $id);
		$trashedItem = $this->findTrashedItem($id, $entity);
		$this->assertNotEmpty($trashedItem);

		$this->actAs($this->reader->id);
		try {
			$this->invoke('actionRestore', ['ids' => (string) $trashedItem->id]);
			$this->fail('A user with read permission restored an item from the trash');
		} catch (AccessDenied $e) {
			$parentId = $entity == 'File' ? $this->reloadFile()->folder_id : Folder::model()->findByPk($id, false, true, true)->parent_id;
			$this->assertNotEquals($this->ownerFolder->id, $parentId);
		}
	}

	/**
	 * The trashed item keeps the permissions it had in its original folder, also when that folder is moved to the trash
	 * later.
	 */
	public function testOwnerCanRemoveFileFromTrashAfterItsFolderWasTrashed(): void
	{
		$this->actAs($this->owner->id);
		$this->assertTrue($this->file->move($this->subFolder));

		$this->trash('f:' . $this->file->id);
		$this->trash('d:' . $this->subFolder->id);
		$trashedFile = $this->findTrashedItem($this->file->id, 'File');
		$this->assertNotEmpty($trashedFile);

		$this->invoke('actionDeleteFromTrash', ['ids' => (string) $trashedFile->id]);
		$this->assertFalse(File::model()->findByPk($this->file->id, false, true, true));
	}

	/**
	 * Like deleting, write permission on a contact is enough to move its files to the trash, and to restore or delete
	 * them from there.
	 */
	public function testWriterOfContactCanTrashRestoreAndDeleteItsFiles(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		\GO::session()->setCurrentUser(User::ID_SUPER_ADMIN);

		$addressBook = new AddressBook();
		$addressBook->name = uniqid('trashtest');
		$addressBook->setAcl([$this->reader->getPersonalGroup()->id => \go\core\model\Acl::LEVEL_WRITE]);
		$this->assertTrue($addressBook->save(), $addressBook->getValidationErrorsAsString());

		try {
			$contact = new Contact();
			$contact->addressBookId = $addressBook->id;
			$contact->firstName = 'Trash';
			$contact->lastName = 'Test';
			$this->assertTrue($contact->save(), $contact->getValidationErrorsAsString());

			$folder = Folder::model()->findForEntity($contact);
			file_put_contents($folder->fsFolder->path() . '/contact.txt', 'content');
			$fileId = $folder->addFile('contact.txt')->id;

			$this->actAs($this->reader->id);
			$this->trash('f:' . $fileId);
			$trashedItem = $this->findTrashedItem($fileId, 'File');
			$this->assertNotEmpty($trashedItem);

			$this->invoke('actionRestore', ['ids' => (string) $trashedItem->id]);
			$this->assertEquals($folder->id, File::model()->findByPk($fileId, false, true, true)->folder_id);

			$this->trash('f:' . $fileId);
			$trashedItem = $this->findTrashedItem($fileId, 'File');
			$this->invoke('actionDeleteFromTrash', ['ids' => (string) $trashedItem->id]);
			$this->assertFalse(File::model()->findByPk($fileId, false, true, true));
		} finally {
			go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
			\GO::session()->setCurrentUser(User::ID_SUPER_ADMIN);
			\GO::$ignoreAclPermissions = true;
			if (isset($folder)) {
				$folder->delete(true);
			}
			\GO::$ignoreAclPermissions = false;
			AddressBook::delete(['id' => $addressBook->id]);
		}
	}

	public function testOwnerCanTrashRestoreAndDelete(): void
	{
		$this->actAs($this->owner->id);

		$this->trash('f:' . $this->file->id);
		$trashedItem = $this->findTrashedItem($this->file->id, 'File');
		$this->assertNotEmpty($trashedItem);
		$this->assertEquals($this->ownerFolder->id, $trashedItem->parentId);
		$this->assertEquals($this->ownerFolder->acl_id, $trashedItem->aclId);
		$this->assertNotEquals($this->ownerFolder->id, $this->reloadFile()->folder_id);

		$this->invoke('actionRestore', ['ids' => (string) $trashedItem->id]);
		$this->assertEquals($this->ownerFolder->id, $this->reloadFile()->folder_id);
		$this->assertFalse($this->findTrashedItem($this->file->id, 'File'));

		$this->trash('d:' . $this->subFolder->id);
		$trashedItem = $this->findTrashedItem($this->subFolder->id, 'Folder');
		$this->assertNotEmpty($trashedItem);
		$this->invoke('actionDeleteFromTrash', ['ids' => (string) $trashedItem->id]);
		$this->assertFalse(Folder::model()->findByPk($this->subFolder->id, false, true, true));
		$this->assertFalse($this->findTrashedItem($this->subFolder->id, 'Folder'));
	}
}
