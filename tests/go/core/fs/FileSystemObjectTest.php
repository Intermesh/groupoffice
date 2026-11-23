<?php

namespace go\core\fs;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FileSystemObjectTest extends TestCase
{
	public static function nameProvider(): array
	{
		return [
			['file.txt', true],
			['.hidden', true],
			['name with spaces: and !', true],
			['..file', true],
			['', false],
			['.', false],
			['..', false],
			['../file.txt', false],
			['../../groupoffice/www/x.php', false],
			['dir/file.txt', false],
			// Not a separator on Linux, and existing files may contain it
			['back\\slash.txt', true],
			["file\0.php", false],
		];
	}

	#[DataProvider('nameProvider')]
	public function testIsValidName(string $name, bool $expected): void
	{
		$this->assertSame($expected, FileSystemObject::isValidName($name));
	}

	public function testIsInsideWithoutLinks(): void
	{
		$base = sys_get_temp_dir() . '/go-links-' . uniqid();
		$outside = $base . '/outside';
		$rootTarget = $base . '/storage-target';
		$root = $base . '/storage';
		mkdir($outside, 0777, true);
		mkdir($rootTarget . '/dir', 0777, true);
		file_put_contents($outside . '/secret.txt', 'secret');
		file_put_contents($rootTarget . '/dir/file.txt', 'file');
		// The root itself may be a link, as file_storage_path often is
		symlink($rootTarget, $root);
		symlink($outside, $root . '/dir/dirlink');
		symlink($outside . '/secret.txt', $root . '/dir/filelink');

		try {
			$folder = new Folder($root);
			$this->assertTrue((new File($root . '/dir/file.txt'))->isInsideWithoutLinks($folder));
			$this->assertTrue((new Folder($root . '/dir'))->isInsideWithoutLinks($folder));
			$this->assertTrue((new File($root . '/dir/new/deeper/file.txt'))->isInsideWithoutLinks($folder), 'Paths that do not exist yet are allowed');

			$this->assertFalse((new File($root . '/dir/dirlink/secret.txt'))->isInsideWithoutLinks($folder), 'Link in a parent folder');
			$this->assertFalse((new File($root . '/dir/dirlink/new.txt'))->isInsideWithoutLinks($folder), 'New file in a linked folder');
			$this->assertFalse((new Folder($root . '/dir/dirlink'))->isInsideWithoutLinks($folder), 'Linked folder');
			$this->assertFalse((new File($root . '/dir/filelink'))->isInsideWithoutLinks($folder), 'Linked file');
			$this->assertFalse((new File($outside . '/secret.txt'))->isInsideWithoutLinks($folder), 'Outside the folder');
			$this->assertFalse((new Folder($root))->isInsideWithoutLinks($folder), 'The folder itself is not inside');
			$this->assertFalse((new File($root . 'sibling/file.txt'))->isInsideWithoutLinks($folder), 'Folder with the same prefix');
		} finally {
			unlink($root . '/dir/dirlink');
			unlink($root . '/dir/filelink');
			unlink($root);
			unlink($rootTarget . '/dir/file.txt');
			rmdir($rootTarget . '/dir');
			rmdir($rootTarget);
			unlink($outside . '/secret.txt');
			rmdir($outside);
			rmdir($base);
		}
	}
}
