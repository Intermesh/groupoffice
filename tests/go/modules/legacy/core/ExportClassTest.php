<?php
namespace go\modules\legacy\core;

use GO\Base\Controller\AbstractJsonController;
use GO\Base\Controller\AbstractModelController;
use GO\Base\Data\ArrayStore;
use GO\Base\Exception\AccessDenied;
use GO\Base\Model\AbstractExport;
use GO\Base\Model\SavedExport;
use GO\Core\Controller\ExportController;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class ExportClassTestExport extends AbstractExport
{
	public function getSupportedViews()
	{
		return [self::VIEW_CSV];
	}
}

/**
 * A class that isn't an export but takes any constructor arguments
 */
class ExportClassTestNotAnExport
{
	public static bool $created = false;

	public function __construct(...$args)
	{
		self::$created = true;
	}
}

class ExportClassTestModelController extends AbstractModelController
{
	protected $model = 'GO\Base\Model\SavedExport';
}

class ExportClassTestJsonController extends AbstractJsonController
{
}

/**
 * The export class name comes from the client or from a saved export and is instantiated, so it must be an export.
 */
class ExportClassTest extends TestCase
{
	private function newSavedExport(string $className): SavedExport
	{
		$export = new SavedExport();
		$export->name = 'test';
		$export->class_name = $className;
		$export->view = 'Csv';
		$export->export_columns = 'id';
		return $export;
	}

	public function testSavedExportMustBeAnExportClass()
	{
		$this->assertFalse($this->newSavedExport(\ArrayObject::class)->validate());
		$this->assertFalse($this->newSavedExport('NoSuchClass')->validate());
		$this->assertTrue($this->newSavedExport(ExportClassTestExport::class)->validate());
	}

	public function testExportRefusesAClassThatIsNotAnExport()
	{
		$method = new ReflectionMethod(ExportController::class, 'actionExport');

		$this->expectException(AccessDenied::class);
		$method->invoke(new ExportController(), \ArrayObject::class, 'id', true, 'V', false, 'Csv');
	}

	public static function exportTypes(): array
	{
		return [
			'not an export' => [ExportClassTestNotAnExport::class],
			'abstract export' => [\GO\Base\Export\AbstractExport::class],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('exportTypes')]
	public function testModelControllerExportRefusesAClassThatIsNotAnExport(string $type)
	{
		ExportClassTestNotAnExport::$created = false;
		$method = new ReflectionMethod(ExportClassTestModelController::class, 'actionExport');

		try {
			$method->invoke(new ExportClassTestModelController(), ['type' => $type, 'name' => 'test', 'documentTitle' => 'test']);
			$this->fail('Exported with ' . $type);
		} catch (AccessDenied $e) {
		}
		$this->assertFalse(ExportClassTestNotAnExport::$created);
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('exportTypes')]
	public function testJsonControllerExportRefusesAClassThatIsNotAnExport(string $type)
	{
		ExportClassTestNotAnExport::$created = false;
		$method = new ReflectionMethod(ExportClassTestJsonController::class, 'renderExport');

		try {
			$method->invoke(new ExportClassTestJsonController(), new ArrayStore(), ['type' => $type, 'documentTitle' => 'test']);
			$this->fail('Exported with ' . $type);
		} catch (AccessDenied $e) {
		}
		$this->assertFalse(ExportClassTestNotAnExport::$created);
	}

	public function testExportClasses()
	{
		$this->assertTrue(\GO\Base\Export\AbstractExport::isExportClass(\GO\Base\Export\ExportCSV::class));
		$this->assertTrue(\GO\Base\Storeexport\AbstractExport::isExportClass(\GO\Base\Storeexport\ExportPDF::class));
		$this->assertFalse(\GO\Base\Storeexport\AbstractExport::isExportClass(\GO\Base\Export\ExportCSV::class));
		$this->assertFalse(\GO\Base\Export\AbstractExport::isExportClass('NoSuchClass'));
	}
}
