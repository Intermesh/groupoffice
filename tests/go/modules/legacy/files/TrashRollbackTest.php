<?php

namespace go\modules\legacy\files;

use go\core\auth\TemporaryState;
use go\core\model\User;
use GO\Base\Observable;
use GO\Files\Model\File;
use GO\Files\Model\Folder;
use GO\Files\Model\TrashedItem;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * When moving to the trash fails after the item was moved on disk, the database is rolled back and the item must be
 * moved back on disk too. Otherwise the item can't be opened anymore and moving it to the trash again fails because it
 * exists in the trash folder (#38368).
 */
class TrashRollbackTest extends TestCase
{
	/**
	 * When set, saving a file or folder fails after it was moved on disk
	 */
	public static bool $failSave = false;

	private $authState;
	private bool $ignoreAcl;
	private bool $disableModelCache;
	private ?array $listeners;
	private User $owner;
	private Folder $homeFolder;
	private File $file;
	private Folder $subFolder;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->ignoreAcl = \GO::$ignoreAclPermissions;
		$this->disableModelCache = \GO::$disableModelCache;
		\GO::$disableModelCache = true;

		// Load the listeners of the modules before adding the one of this test
		File::model()->fireEvent('loadlisteners');
		$this->listeners = Observable::$listeners;
		File::model()->addListener('save', self::class, 'onSave');
		Folder::model()->addListener('save', self::class, 'onSave');

		$name = uniqid('trashrollback');
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
		$this->homeFolder = Folder::model()->findHomeFolder(\GO\Base\Model\User::model()->findByPk($this->owner->id));

		file_put_contents($this->homeFolder->fsFolder->path() . '/file.txt', 'content');
		$this->file = $this->homeFolder->addFile('file.txt');
		$this->subFolder = $this->homeFolder->addFolder('sub');
		file_put_contents($this->subFolder->fsFolder->path() . '/inner.txt', 'inner');
	}

	protected function tearDown(): void
	{
		self::$failSave = false;
		Observable::$listeners = $this->listeners;

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

		$this->homeFolder->delete(true);
		User::delete(['id' => $this->owner->id]);

		\GO::$ignoreAclPermissions = $this->ignoreAcl;
		\GO::$disableModelCache = $this->disableModelCache;
	}

	/**
	 * Listener of the "save" event, which fires after the item was moved on disk
	 */
	public static function onSave($model): void
	{
		if (self::$failSave) {
			throw new \RuntimeException('Save failed after the move on disk');
		}
	}

	public static function items(): array
	{
		return ['file' => ['File'], 'folder' => ['Folder']];
	}

	private function reload(string $type): File|Folder
	{
		return $type == 'File'
			? File::model()->findByPk($this->file->id, false, true, true)
			: Folder::model()->findByPk($this->subFolder->id, false, true, true);
	}

	private static function fsPath(File|Folder $item): string
	{
		return $item instanceof File ? $item->fsFile->path() : $item->fsFolder->path();
	}

	private function findTrashedItem(string $type, int $id): TrashedItem|false
	{
		$entityTypeId = \go\core\orm\EntityType::findByName($type)->getId();
		return TrashedItem::model()->findSingleByAttributes(['entityTypeId' => $entityTypeId, 'entityId' => $id]);
	}

	#[DataProvider('items')]
	public function testFailedMoveToTrashLeavesTheDiskAsTheDatabase(string $type): void
	{
		$item = $this->reload($type);
		$originalPath = self::fsPath($item);
		$trashPath = Folder::model()->findByPath('trash')->fsFolder->path() . '/' . $item->name;
		$this->assertFileExists($originalPath);

		self::$failSave = true;
		try {
			$item->moveToTrash();
			$this->fail('The save listener should have failed the move to the trash');
		} catch (\RuntimeException $e) {
			$this->assertEquals('Save failed after the move on disk', $e->getMessage());
		}
		self::$failSave = false;

		// The database is rolled back
		$reloaded = $this->reload($type);
		$this->assertEquals($originalPath, self::fsPath($reloaded));
		$this->assertFalse($this->findTrashedItem($type, $reloaded->id));

		// and the disk matches it
		$this->assertFileExists($originalPath, 'The item must be moved back on disk');
		$this->assertFileDoesNotExist($trashPath);
		if ($type == 'Folder') {
			$this->assertFileExists($originalPath . '/inner.txt');
		}

		// Moving it to the trash works again
		$reloaded->moveToTrash();
		$trashed = $this->reload($type);
		$this->assertFileExists(self::fsPath($trashed));
		$this->assertFileDoesNotExist($originalPath);
		$this->assertNotFalse($this->findTrashedItem($type, $trashed->id));
	}

	public function testMoveBackOnDiskDoesNotOverwrite(): void
	{
		$dir = $this->homeFolder->fsFolder->path();
		file_put_contents($dir . '/original.txt', 'original');
		file_put_contents($dir . '/moved.txt', 'moved');
		try {
			TrashedItem::moveBackOnDisk($dir . '/original.txt', [$dir . '/moved.txt']);
			$this->assertEquals('original', file_get_contents($dir . '/original.txt'));
			$this->assertFileExists($dir . '/moved.txt');
		} finally {
			@unlink($dir . '/original.txt');
			@unlink($dir . '/moved.txt');
		}
	}
}
