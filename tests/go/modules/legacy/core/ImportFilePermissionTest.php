<?php
namespace go\modules\legacy\core;

use GO\Base\Exception\AccessDenied;
use GO\Core\Controller\AdvancedSearchController;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * A legacy import over the web may only read a file the user uploaded, not any file on the server.
 */
class ImportFilePermissionTest extends TestCase
{
	private string $path;

	protected function setUp(): void
	{
		$this->path = \GO::config()->getTempFolder()->path() . '/' . uniqid('import') . '.csv';
		file_put_contents($this->path, "name\nnot uploaded\n");
	}

	protected function tearDown(): void
	{
		@unlink($this->path);
	}

	private function import(array $params): void
	{
		// a web request
		$controller = new class extends AdvancedSearchController {
			protected function mayImportServerFile(): bool
			{
				return false;
			}
		};

		$method = new ReflectionMethod(AdvancedSearchController::class, 'actionImport');
		$method->invoke($controller, $params);
	}

	public function testWebImportRefusesAFileOnTheServer()
	{
		try {
			$this->import(['file' => $this->path]);
			$this->fail('A file on the server was imported over the web');
		} catch (AccessDenied $e) {
			$this->assertTrue(true);
		}

		$this->assertEquals("name\nnot uploaded\n", file_get_contents($this->path));
	}

	public function testImportTypeMustBeKnown()
	{
		$method = new ReflectionMethod(AdvancedSearchController::class, 'actionImport');

		$this->expectException(AccessDenied::class);
		// the command line may import files on the server
		$method->invoke(new AdvancedSearchController(), ['file' => $this->path, 'importType' => 'Log']);
	}
}
