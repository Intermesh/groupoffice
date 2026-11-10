<?php

namespace go\modules\legacy\files;

use go\core\auth\TemporaryState;
use go\core\model\User;
use GO\Base\Exception\AccessDenied;
use GO\Base\Exception\NotFound;
use GO\Files\Controller\FolderController;
use GO\Files\Model\Folder;
use PHPUnit\Framework\TestCase;

/**
 * files/folder/decompress extracts archives into a folder. It must require READ permission on every archive, not only
 * WRITE permission on the folder it extracts to.
 */
class DecompressPermissionTest extends TestCase
{
	private $authState;
	private bool $ignoreAcl;
	private string $cwd;
	private User $owner;
	private User $other;
	private Folder $ownerFolder;
	private Folder $otherFolder;
	private string $archivePath;

	protected function setUp(): void
	{
		if(!class_exists(\ZipArchive::class)) {
			$this->markTestSkipped('ZipArchive is not available');
		}

		$this->authState = go()->getAuthState();
		$this->ignoreAcl = \GO::$ignoreAclPermissions;
		$this->cwd = getcwd();

		$this->owner = $this->createUser('zipowner');
		$this->other = $this->createUser('zipother');

		$this->actAs($this->owner->id);
		$this->ownerFolder = Folder::model()->findHomeFolder(\GO\Base\Model\User::model()->findByPk($this->owner->id));

		$zip = new \ZipArchive();
		$this->assertTrue($zip->open($this->ownerFolder->fsFolder->path() . '/private.zip', \ZipArchive::CREATE));
		$zip->addFromString('secret.txt', 'secret content');
		$zip->close();
		$file = $this->ownerFolder->addFile('private.zip', true);
		$this->archivePath = $file->path;

		$this->actAs($this->other->id);
		$this->otherFolder = Folder::model()->findHomeFolder(\GO\Base\Model\User::model()->findByPk($this->other->id));
	}

	protected function tearDown(): void
	{
		chdir($this->cwd);
		go()->setAuthState($this->authState);
		\GO::session()->setCurrentUser(User::ID_SUPER_ADMIN);
		\GO::$ignoreAclPermissions = true;

		$this->ownerFolder->delete(true);
		$this->otherFolder->delete(true);
		User::delete(['id' => [$this->owner->id, $this->other->id]]);

		\GO::$ignoreAclPermissions = $this->ignoreAcl;
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

	private function decompress(Folder $target, array $sources): array
	{
		$controller = new FolderController();
		$method = new \ReflectionMethod($controller, 'actionDecompress');
		$method->setAccessible(true);

		return $method->invoke($controller, [
			'working_folder_id' => $target->id,
			'decompress_sources' => json_encode($sources)
		]);
	}

	public function testUserCannotDecompressArchiveTheyCannotRead(): void
	{
		$this->actAs($this->other->id);

		try {
			$this->decompress($this->otherFolder, [$this->archivePath]);
			$this->fail('decompress extracted an archive the user may not read');
		} catch (AccessDenied $e) {
			$this->assertFileDoesNotExist($this->otherFolder->fsFolder->path() . '/secret.txt');
		}
	}

	public function testUnknownArchiveIsRejected(): void
	{
		$this->actAs($this->other->id);

		$this->expectException(NotFound::class);
		$this->decompress($this->otherFolder, [$this->ownerFolder->path . '/not-in-database.zip']);
	}

	public function testOwnerCanDecompressOwnArchive(): void
	{
		$this->actAs($this->owner->id);

		$response = $this->decompress($this->ownerFolder, [$this->archivePath]);

		$this->assertTrue($response['success']);
		$this->assertStringEqualsFile($this->ownerFolder->fsFolder->path() . '/secret.txt', 'secret content');
	}
}
