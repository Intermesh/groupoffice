<?php
namespace go\core;

use ErrorException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ErrorHandlerTest extends TestCase
{
	private string $errorLog;
	private string|false $originalErrorLog;

	protected function setUp(): void
	{
		// keep the expected deprecation log lines out of the test output
		$this->errorLog = tempnam(sys_get_temp_dir(), 'errorhandlertest');
		$this->originalErrorLog = ini_set('error_log', $this->errorLog);
	}

	protected function tearDown(): void
	{
		ini_set('error_log', (string) $this->originalErrorLog);
		unlink($this->errorLog);
	}

	public static function handled(): array
	{
		return [
			[E_DEPRECATED, '/usr/share/groupoffice/vendor/erusev/parsedown/Parsedown.php'],
			[E_USER_DEPRECATED, '/usr/share/groupoffice/go/modules/business/finance/vendor/some/lib/Lib.php'],
		];
	}

	#[DataProvider('handled')]
	public function testVendorDeprecationIsLogged(int $errno, string $file)
	{
		$this->assertTrue(ErrorHandler::errorHandler($errno, "Something is deprecated", $file, 10));
		$this->assertStringContainsString("Something is deprecated in " . $file, file_get_contents($this->errorLog));
	}

	public static function thrown(): array
	{
		return [
			// our own deprecations must still be fixed
			[E_DEPRECATED, '/usr/share/groupoffice/go/core/util/Markdown.php'],
			[E_USER_DEPRECATED, '/usr/share/groupoffice/go/modules/community/notes/model/Note.php'],
			// other errors in vendor code are real errors
			[E_WARNING, '/usr/share/groupoffice/vendor/erusev/parsedown/Parsedown.php'],
			[E_NOTICE, '/usr/share/groupoffice/vendor/erusev/parsedown/Parsedown.php'],
			// "vendor" must be a folder
			[E_DEPRECATED, '/usr/share/groupoffice/go/modules/community/vendorlist/Module.php'],
		];
	}

	#[DataProvider('thrown')]
	public function testErrorIsThrown(int $errno, string $file)
	{
		$this->expectException(ErrorException::class);
		ErrorHandler::errorHandler($errno, "Something went wrong", $file, 10);
	}
}
