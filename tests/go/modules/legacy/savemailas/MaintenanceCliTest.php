<?php
namespace go\modules\legacy\savemailas;

use GO\Base\Exception\CliOnly;
use GO\Savemailas\Controller\LinkedEmailController;
use go\core\Environment;
use go\core\Singleton;
use PHPUnit\Framework\TestCase;

/**
 * Not on the command line
 */
class MaintenanceWebEnvironment extends Environment
{
	public function isCli(): bool
	{
		return false;
	}
}

/**
 * The maintenance actions go through all linked e-mail of all users while ignoring permissions. They may only run on
 * the command line.
 */
class MaintenanceCliTest extends TestCase
{
	private array $singletons;
	private bool $ignoreAcl;

	protected function setUp(): void
	{
		$this->ignoreAcl = \GO::$ignoreAclPermissions;
		$property = new \ReflectionProperty(Singleton::class, 'instances');
		$this->singletons = $property->getValue();
		$instances = $this->singletons;
		$instances[Environment::class] = (new \ReflectionClass(MaintenanceWebEnvironment::class))->newInstanceWithoutConstructor();
		$property->setValue(null, $instances);
	}

	protected function tearDown(): void
	{
		(new \ReflectionProperty(Singleton::class, 'instances'))->setValue(null, $this->singletons);
		\GO::$ignoreAclPermissions = $this->ignoreAcl;
	}

	public static function actions(): array
	{
		return [['actionFixDates'], ['actionUpdateCache'], ['actionFixDatesTimeZero']];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('actions')]
	public function testMaintenanceIsCliOnly(string $action): void
	{
		$method = new \ReflectionMethod(LinkedEmailController::class, $action);
		ob_start();
		try {
			$method->invoke(new LinkedEmailController(), []);
			$this->fail($action . ' ran in a web request');
		} catch (CliOnly $e) {
			$this->assertTrue(true);
		} finally {
			ob_end_clean();
		}
		$this->assertSame($this->ignoreAcl, \GO::$ignoreAclPermissions);
	}
}
