<?php

namespace go\modules\legacy\files;

use go\core\auth\TemporaryState;
use go\core\model\User;
use GO\Files\Model\Folder;
use PHPUnit\Framework\TestCase;

/**
 * Folder::addFilesystemFile() moves a file into the folder and adds its record. When the move fails, no record may be
 * added, because it would point to a file that isn't there.
 */
class FolderAddFilesystemFileTest extends TestCase
{
	private $authState;
	private bool $ignoreAcl;
	private Folder $folder;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->ignoreAcl = \GO::$ignoreAclPermissions;
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		\GO::session()->setCurrentUser(User::ID_SUPER_ADMIN);

		// Not in the root: folders there can't be deleted
		$home = Folder::model()->findHomeFolder(\GO\Base\Model\User::model()->findByPk(User::ID_SUPER_ADMIN));
		$this->folder = $home->addFolder(uniqid('addfsfile'));
	}

	protected function tearDown(): void
	{
		\GO::$ignoreAclPermissions = true;
		$this->folder->delete(true);
		\GO::$ignoreAclPermissions = $this->ignoreAcl;
		go()->setAuthState($this->authState);
	}

	private function tempFile(string $content): \GO\Base\Fs\File
	{
		$file = \GO\Base\Fs\File::tempFile();
		$file->putContents($content);
		return $file;
	}

	public function testAddsTheFileAndItsRecord(): void
	{
		$file = $this->tempFile('content');

		$record = $this->folder->addFilesystemFile($file, true, 'added.txt');

		$this->assertEquals('added.txt', $record->name);
		$this->assertEquals('content', $record->fsFile->getContents());
	}

	public function testNoRecordWhenTheMoveFails(): void
	{
		$file = $this->tempFile('content');
		// A move can fail without an exception, for example when rename() returns false
		$failingMove = new class($file->path()) extends \GO\Base\Fs\File {
			public function move(\GO\Base\Fs\Base $destination, $newFileName = false, $isUploadedFile = false, $appendNumberToNameIfDestinationExists = false)
			{
				return false;
			}
		};

		try {
			$this->folder->addFilesystemFile($failingMove, true, 'failed.txt');
			$this->fail('A failed move was not reported');
		} catch (\Exception $e) {
			$this->assertStringContainsString('Could not move file', $e->getMessage());
		} finally {
			$file->delete();
		}

		$this->assertEmpty($this->folder->hasFile('failed.txt'));
		$this->assertEmpty($this->folder->hasFile($file->name()));
	}
}
