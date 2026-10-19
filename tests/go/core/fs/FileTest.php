<?php
namespace go\core\fs;

use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
	public static function hostileNames(): array
	{
		return [
			['/../../../pwned.php'],
			['../pwned.php'],
			['\\..\\..\\pwned.php'],
			['/../' . str_repeat('../', 10) . 'etc/cron.d/pwned'],
			['..'],
			["/../pwned.php\0.txt"],
		];
	}

	/**
	 * @dataProvider hostileNames
	 */
	public function testTempFileStaysInTmpFolder(string $name)
	{
		$tmp = go()->getTmpFolder()->create();
		$file = File::tempFile($name);

		$this->assertSame(
			$tmp->getPath(),
			dirname($file->getPath()),
			"Temp file for '" . addcslashes($name, "\0") . "' escaped the tmp folder: " . $file->getPath()
		);
	}

	/**
	 * Reproduces api/upload.php: the file is opened for writing, which creates missing parent folders.
	 *
	 * @dataProvider hostileNames
	 */
	public function testOpenDoesNotWriteOutsideTmpFolder(string $name)
	{
		$tmp = go()->getTmpFolder()->create();
		$marker = dirname($tmp->getPath()) . '/pwned.php';
		$this->assertFileDoesNotExist($marker);

		$file = File::tempFile($name);
		$fp = $file->open('w+');
		$this->assertNotFalse($fp);
		fwrite($fp, 'x');
		fclose($fp);

		$this->assertFileDoesNotExist($marker);
		$this->assertSame($tmp->getPath(), dirname(realpath($file->getPath())));

		$file->delete();
	}

	public function testStripInvalidCharsRemovesControlCharacters()
	{
		$this->assertSame('a.txt', File::stripInvalidChars("a\0.txt"));
		$this->assertSame('ab.txt', File::stripInvalidChars("a\r\nb.txt"));
		$this->assertSame('pwned.php', File::stripInvalidChars('/../pwned.php'));
		$this->assertSame('unnamed', File::stripInvalidChars("\0"));
		$this->assertSame('Ünïcödé résumé.pdf', File::stripInvalidChars('Ünïcödé résumé.pdf'));
	}

	public function testTempFileKeepsNormalExtension()
	{
		$file = File::tempFile('txt');
		$this->assertSame('txt', $file->getExtension());

		$file = File::tempFile('my report.final.pdf');
		$this->assertSame('pdf', $file->getExtension());

		$file = File::tempFile('');
		$this->assertSame(go()->getTmpFolder()->getPath(), dirname($file->getPath()));
	}
}
