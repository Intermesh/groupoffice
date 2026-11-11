<?php
namespace go\modules\legacy\projects2;

use GO\Base\Exception\AccessDenied;
use GO\Projects2\Controller\ProjectController;
use go\core\Environment;
use go\core\Singleton;
use go\core\model\User;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/fixtures/ProjectsTrait.php';

/**
 * Not on the command line
 */
class MigrationWebEnvironment extends Environment
{
	public function isCli(): bool
	{
		return false;
	}
}

/**
 * The migrations from the old projects module change all projects and files while ignoring permissions. Only
 * administrators and the command line may run them.
 */
class MigrationPermissionTest extends TestCase
{
	use ProjectsTrait;

	private array $singletons;

	protected function setUp(): void
	{
		$this->setUpProjects();

		$property = new \ReflectionProperty(Singleton::class, 'instances');
		$this->singletons = $property->getValue();
		$instances = $this->singletons;
		$instances[Environment::class] = (new \ReflectionClass(MigrationWebEnvironment::class))->newInstanceWithoutConstructor();
		$property->setValue(null, $instances);
	}

	protected function tearDown(): void
	{
		(new \ReflectionProperty(Singleton::class, 'instances'))->setValue(null, $this->singletons);
		$this->tearDownProjects();
	}

	public static function actions(): array
	{
		return [['actionV1toV2Upgrade'], ['actionMigrateFilesFromV1']];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('actions')]
	public function testMigrationIsOnlyForAdmins(string $action): void
	{
		$this->actAs($this->user->id);
		$method = new \ReflectionMethod(ProjectController::class, $action);
		ob_start();
		try {
			$method->invoke(new ProjectController(), []);
			$this->fail($action . ' was allowed');
		} catch (AccessDenied $e) {
			$this->assertTrue(true);
		} catch (\Throwable $e) {
			$this->fail($action . ' got past the permission check: ' . get_class($e) . ': ' . $e->getMessage());
		} finally {
			ob_end_clean();
			$this->actAs(User::ID_SUPER_ADMIN);
		}
	}
}
