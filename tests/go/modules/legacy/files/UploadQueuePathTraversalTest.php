<?php

namespace go\modules\legacy\files;

use go\core\auth\TemporaryState;
use go\core\fs\Blob;
use go\core\model\User;
use GO\Files\Controller\FolderController;
use GO\Files\Model\Folder;
use PHPUnit\Framework\TestCase;

/**
 * files/folder/processUploadQueue takes the file name and sub folders from the client. They must not be able to
 * write outside the destination folder with "../".
 */
class UploadQueuePathTraversalTest extends TestCase
{
	private $authState;
	private bool $ignoreAcl;
	private User $user;
	private Folder $homeFolder;
	/** @var string[] Paths outside the home folder that a traversal would write to */
	private array $escapedPaths = [];
	/** @var string[] */
	private array $blobIds = [];

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->ignoreAcl = \GO::$ignoreAclPermissions;

		$name = uniqid('traversal');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), var_export($this->user->getValidationErrors(), true));

		$this->actAs($this->user->id);
		$this->homeFolder = Folder::model()->findHomeFolder(\GO\Base\Model\User::model()->findByPk($this->user->id));
	}

	protected function tearDown(): void
	{
		go()->setAuthState($this->authState);
		\GO::session()->setCurrentUser(User::ID_SUPER_ADMIN);
		\GO::$ignoreAclPermissions = true;

		// Only present if a guard failed
		foreach ($this->escapedPaths as $path) {
			if (is_file($path)) {
				unlink($path);
			}
		}

		if (!empty($this->blobIds)) {
			Blob::delete(['id' => $this->blobIds]);
		}

		$this->homeFolder->delete(true);
		User::delete(['id' => $this->user->id]);

		\GO::$ignoreAclPermissions = $this->ignoreAcl;
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
		\GO::session()->setCurrentUser($userId);
	}

	private function processUploadQueue(array $blobs): array
	{
		$controller = new FolderController();
		$method = new \ReflectionMethod($controller, 'actionProcessUploadQueue');
		$method->setAccessible(true);

		return $method->invoke($controller, [
			'destination_folder_id' => $this->homeFolder->id,
			'overwrite' => 'yes',
			'blobs' => json_encode($blobs)
		]);
	}

	/**
	 * Path of $name in the folder $levels above the home folder. Registered for clean up.
	 */
	private function escapedPath(int $levels, string $name): string
	{
		$path = dirname($this->homeFolder->fsFolder->path(), $levels) . '/' . $name;
		$this->escapedPaths[] = $path;
		return $path;
	}

	private function createBlob(): Blob
	{
		$blob = Blob::fromString('traversal test ' . uniqid());
		$blob->type = 'text/plain';
		$blob->name = 'test.txt';
		$this->assertTrue($blob->save(), var_export($blob->getValidationErrors(), true));
		$this->blobIds[] = $blob->id;
		return $blob;
	}

	public function testFileNameCannotEscapeDestinationFolder(): void
	{
		$name = 'traversal_' . $this->user->username . '.txt';
		$escaped = $this->escapedPath(2, $name);
		$blob = $this->createBlob();

		$this->processUploadQueue([['id' => $blob->id, 'name' => '../../' . $name]]);

		$this->assertFileDoesNotExist($escaped);
		$this->assertFileExists($this->homeFolder->fsFolder->path() . '/' . $name);
	}

	public function testSubfolderCannotEscapeDestinationFolder(): void
	{
		$name = $this->user->username . '.txt';
		$escaped = [$this->escapedPath(2, $name), $this->escapedPath(1, $name)];
		$blob = $this->createBlob();

		$this->processUploadQueue([['id' => $blob->id, 'name' => $name, 'subfolder' => ['..', '..']]]);

		$this->assertFileDoesNotExist($escaped[0]);
		$this->assertFileDoesNotExist($escaped[1]);
	}

	public function testFolderModelRejectsTraversalName(): void
	{
		$folder = new Folder();
		$folder->parent_id = $this->homeFolder->id;
		$folder->name = '../escaped';

		$this->assertFalse($folder->validate());
		$this->assertTrue($folder->hasValidationErrors('name'));
	}

	public function testFilesystemMoveRejectsTraversalName(): void
	{
		$file = \GO\Base\Fs\File::tempFile();
		$file->touch();
		$escaped = $this->escapedPath(1, 'escaped_' . $this->user->username . '.txt');

		try {
			$file->move($this->homeFolder->fsFolder, '../' . basename($escaped));
			$this->fail('File::move() accepted a name with "../"');
		} catch (\InvalidArgumentException $e) {
			$this->assertFileDoesNotExist($escaped);
		} finally {
			$file->delete();
		}
	}
}
