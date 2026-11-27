<?php

namespace go\modules\legacy\files;

use GO\Files\Model\File;
use GO\Files\Model\Folder;
use PHPUnit\Framework\TestCase;

/**
 * A file that disappears or can't be read anymore during a save, like a temporary file during a file system sync, made
 * the save fail with "Incorrect integer value: '' for column fs_files.size" (#38368).
 */
class FileSizeTest extends TestCase
{
	private bool $ignoreAcl;
	private bool $disableModelCache;
	private Folder $folder;
	private File $file;

	protected function setUp(): void
	{
		$this->ignoreAcl = \GO::setIgnoreAclPermissions(true);
		$this->disableModelCache = \GO::$disableModelCache;
		\GO::$disableModelCache = true;

		$homeFolder = Folder::model()->findHomeFolder(\GO\Base\Model\User::model()->findByPk(1));
		$this->folder = $homeFolder->addFolder(uniqid('filesizetest'));
		file_put_contents($this->folder->fsFolder->path() . '/file.txt', 'content');
		$this->file = $this->folder->addFile('file.txt');
	}

	protected function tearDown(): void
	{
		$this->folder->delete(true);

		\GO::setIgnoreAclPermissions($this->ignoreAcl);
		\GO::$disableModelCache = $this->disableModelCache;
	}

	public function testSaveKeepsSizeAndMtimeWhenTheFileCantBeRead(): void
	{
		$this->assertEquals(7, $this->file->size);
		$mtime = $this->file->mtime;

		$file = UnreadableFile::model(UnreadableFile::class)->findByPk($this->file->id, false, true);
		$file->comment = 'changed';
		$this->assertTrue($file->save(), var_export($file->getValidationErrors(), true));

		$file = File::model()->findByPk($this->file->id, false, true);
		$this->assertEquals('changed', $file->comment);
		$this->assertEquals(7, $file->size);
		$this->assertEquals($mtime, $file->mtime);
	}
}

/**
 * A file that exists, but of which the size and modification time can't be read, like a file that is removed right after
 * the check if it exists
 */
class UnreadableFile extends File
{
	protected function getFsFile()
	{
		return new class(\GO::config()->file_storage_path . $this->path) extends \GO\Base\Fs\File {
			public function size()
			{
				return false;
			}

			public function mtime()
			{
				return false;
			}
		};
	}
}
