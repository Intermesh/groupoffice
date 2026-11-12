<?php
namespace go\modules\legacy\site;

use GO\Site\Controller\FrontController;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Guest actions skip the login and the security token check, so the site may not offer uploads or widget calls to
 * guests.
 */
class FrontControllerTest extends TestCase
{
	public function testGuestsCannotCallWidgets()
	{
		$class = new ReflectionClass(FrontController::class);

		$this->assertFalse($class->hasMethod('actionAjaxWidget'));

		$allowGuests = $class->getMethod('allowGuests');
		$this->assertNotContains('ajaxwidget', $allowGuests->invoke($class->newInstanceWithoutConstructor()));
	}
}
