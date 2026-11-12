<?php
namespace go\modules\legacy\core;

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
}
