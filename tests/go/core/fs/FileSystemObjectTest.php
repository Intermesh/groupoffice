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
}
