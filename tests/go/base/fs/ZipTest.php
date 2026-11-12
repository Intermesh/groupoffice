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

	public function testLinksAreNotFollowedWhenZipping()
	{
		$secret = $this->folder->createChild('secret.txt');
		$secret->putContents('secret');

		$files = $this->folder->createChild('files', false);
		$files->create();
		$files->createChild('content.xml')->putContents('test');
		symlink($secret->path(), $files->path() . '/link.xml');
		symlink($this->folder->path(), $files->path() . '/linkdir');

		$archive = $this->folder->createChild('test.zip');
		try {
			Zip::create($archive, $files, $files->ls());
		} finally {
			unlink($files->path() . '/link.xml');
			unlink($files->path() . '/linkdir');
		}

		$zip = new \ZipArchive();
		$this->assertTrue($zip->open($archive->path()));
		$this->assertNotFalse($zip->locateName('content.xml'));
		$this->assertSame(1, $zip->numFiles, 'Only the file may be added, not what the links point to');
		$zip->close();
	}

	/**
	 * Create an archive with the given entries. A value of null makes the entry a symbolic link to /etc/passwd.
	 */
	private function createArchive(array $entries): File
	{
		$archive = $this->folder->createChild(uniqid('archive') . '.zip');
		$zip = new \ZipArchive();
		$this->assertTrue($zip->open($archive->path(), \ZipArchive::CREATE));
		foreach ($entries as $name => $content) {
			if ($content === null) {
				$zip->addFromString($name, '/etc/passwd');
				$zip->setExternalAttributesName($name, \ZipArchive::OPSYS_UNIX, (0120777 << 16));
			} else {
				$zip->addFromString($name, $content);
			}
		}
		$zip->close();
		return $archive;
	}

	public function testExtract()
	{
		$archive = $this->createArchive(['content.xml' => 'content', 'word/document.xml' => 'document']);
		$destination = $this->folder->createChild('extract', false);
		$destination->create();

		Zip::extract($archive, $destination);

		$this->assertStringEqualsFile($destination->path() . '/content.xml', 'content');
		$this->assertStringEqualsFile($destination->path() . '/word/document.xml', 'document');
	}

	public static function unsafeEntries(): array
	{
		return [
			'parent folder' => [['../outside.xml' => 'test']],
			'parent folder in path' => [['word/../../outside.xml' => 'test']],
			'absolute path' => [['/tmp/outside.xml' => 'test']],
			'symbolic link' => [['content.xml' => null]],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('unsafeEntries')]
	public function testExtractRefusesEntriesOutsideTheFolderAndLinks(array $entries)
	{
		$archive = $this->createArchive($entries);
		$destination = $this->folder->createChild('extract', false);
		$destination->create();

		try {
			Zip::extract($archive, $destination);
			$this->fail('The archive was extracted');
		} catch (\Exception $e) {
		}

		$this->assertSame([], array_diff(scandir($destination->path()), ['.', '..']), 'Nothing may be extracted');
		$this->assertFileDoesNotExist($this->folder->path() . '/outside.xml');
	}
}
