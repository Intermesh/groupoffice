<?php

namespace go\modules\legacy\files;

use go\core\auth\TemporaryState;
use go\core\model\User;
use GO\Files\Model\Folder;
use PHPUnit\Framework\TestCase;

/**
 * Read only folders, like home folders and folders of items such as projects, may not be renamed or moved.
 */
class FolderReadonlyTest extends TestCase
{
	private $authState;
	private bool $ignoreAcl;
	private User $owner;
	private Folder $ownerFolder;
	private Folder $readonlyFolder;
	private Folder $destination;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->ignoreAcl = \GO::$ignoreAclPermissions;
		\GO::$ignoreAclPermissions = false;

		$name = uniqid('readonlyowner');
		$this->owner = new User();
		$this->owner->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->owner->save(), var_export($this->owner->getValidationErrors(), true));

		go()->setAuthState(new TemporaryState($this->owner->id));
		\GO::session()->setCurrentUser($this->owner->id);
		$this->ownerFolder = Folder::model()->findHomeFolder(\GO\Base\Model\User::model()->findByPk($this->owner->id));

		$this->readonlyFolder = $this->ownerFolder->addFolder('system');
		$this->readonlyFolder->readonly = 1;
		$this->readonlyFolder->systemSave = true;
		$this->assertTrue($this->readonlyFolder->save(true));
		$this->readonlyFolder->systemSave = false;

		$this->destination = $this->ownerFolder->addFolder('destination');
	}

	protected function tearDown(): void
	{
		go()->setAuthState($this->authState);
		\GO::session()->setCurrentUser(User::ID_SUPER_ADMIN);
		\GO::$ignoreAclPermissions = true;

		$this->ownerFolder->delete(true);
		User::delete(['id' => [$this->owner->id]]);

		\GO::$ignoreAclPermissions = $this->ignoreAcl;
	}

	public function testReadonlyFolderCannotBeMoved(): void
	{
		$this->assertFalse($this->readonlyFolder->move($this->destination));

		$folder = Folder::model()->findByPk($this->readonlyFolder->id, false, true, true);
		$this->assertEquals($this->ownerFolder->id, $folder->parent_id);
		$this->assertDirectoryExists($this->ownerFolder->fsFolder->path() . '/system');
	}

	public function testReadonlyFolderCannotBeRenamed(): void
	{
		$this->readonlyFolder->name = 'renamed';
		$this->assertFalse($this->readonlyFolder->save());

		$this->assertDirectoryExists($this->ownerFolder->fsFolder->path() . '/system');
	}

	public function testReadonlyFolderCannotBeMovedToTrash(): void
	{
		$this->expectException(\Exception::class);
		try {
			$this->readonlyFolder->moveToTrash();
		} finally {
			$this->assertDirectoryExists($this->ownerFolder->fsFolder->path() . '/system');
		}
	}
}
