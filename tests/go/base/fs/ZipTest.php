<?php
namespace GO\Base\Fs;

use PHPUnit\Framework\TestCase;

/**
 * File names come from users, for example from e-mail attachments, so they may not be read as zip options.
 */
class ZipTest extends TestCase
{
	private Folder $folder;

	protected function setUp(): void
	{
		$this->folder = new Folder(\GO::config()->getTempFolder()->path() . '/' . uniqid('ziptest'));
		$this->folder->create();
	}

	protected function tearDown(): void
	{
		$old = File::setAllowDeletes(true);
		$this->folder->delete();
		File::setAllowDeletes($old);
	}

	public function testFileNamesAreNotReadAsOptionsByZipCommand()
	{
		$files = $this->folder->createChild('files', false);
		$files->create();

		// zip runs the --unzip-command when testing the archive with -TT
		$names = ['-TT', '--unzip-command=touch injected', 'attachment.txt'];
		foreach ($names as $name) {
			$files->createChild($name)->putContents('test');
		}

		$archive = $this->folder->createChild('test.zip');

		// $utf8 = true uses the zip command instead of ZipArchive
		Zip::create($archive, $files, $files->ls(), true);

		$this->assertFileDoesNotExist($files->path() . '/injected', 'A file name was executed as a command');

		$zip = new \ZipArchive();
		$this->assertTrue($zip->open($archive->path()));
		$entries = [];
		for ($i = 0; $i < $zip->numFiles; $i++) {
			$entries[] = $zip->getNameIndex($i);
		}
		$zip->close();

		sort($names);
		sort($entries);
		$this->assertEquals($names, $entries);
	}

	public function testHiddenFilesInFoldersAreAdded()
	{
		// like an unpacked .docx file, which needs _rels/.rels
		$files = $this->folder->createChild('docx', false);
		$files->create();
		$rels = $files->createChild('_rels', false);
		$rels->create();
		$rels->createChild('.rels')->putContents('test');
		$files->createChild('[Content_Types].xml')->putContents('test');

		$archive = $this->folder->createChild('test.docx');
		Zip::create($archive, $files, $files->ls());

		$zip = new \ZipArchive();
		$this->assertTrue($zip->open($archive->path()));
		$this->assertNotFalse($zip->locateName('_rels/.rels'));
		$this->assertNotFalse($zip->locateName('[Content_Types].xml'));
		$zip->close();
	}
}
