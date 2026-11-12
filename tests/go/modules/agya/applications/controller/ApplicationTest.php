<?php
namespace go\modules\agya\applications\controller;

use go\core\auth\TemporaryState;
use go\core\exception\NotFound;
use go\core\model\User;
use go\modules\business\ModuleTablesTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../business/fixtures/ModuleTablesTrait.php';

/**
 * Sending the e-mail of an unknown application gives a clean error
 */
class ApplicationTest extends TestCase
{
	use ModuleTablesTrait;

	private $authState;

	protected function setUp(): void
	{
		if (!class_exists(Application::class)) {
			$this->markTestSkipped('The applications module is required');
		}
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		$this->installModuleTables('agya', 'applications', go()->getEnvironment()->getInstallPath() . '/go/modules/agya/applications/install/install.sql');
	}

	protected function tearDown(): void
	{
		if (!isset($this->authState)) {
			return;
		}
		$this->uninstallModuleTables();
		go()->setAuthState($this->authState);
	}

	public static function methods(): array
	{
		return [['sendSubmission'], ['sendInvitationForReturningApplicant']];
	}

	#[DataProvider('methods')]
	public function testUnknownApplication(string $method): void
	{
		$controller = (new \ReflectionClass(Application::class))->newInstanceWithoutConstructor();
		$this->expectException(NotFound::class);
		$controller->$method(['id' => 999999999]);
	}
}
