<?php
namespace go\modules\legacy\leavedays;

use GO\Base\Exception\AccessDenied;
use GO\Leavedays\Controller\SpecialleavebudgetController;
use go\core\auth\TemporaryState;
use go\core\model\User;
use go\modules\business\ModuleTablesTrait;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../business/fixtures/ModuleTablesTrait.php';

/**
 * The special leave budgets of an employee are only for that employee and their managers
 */
class SpecialleavebudgetControllerTest extends TestCase
{
	use ModuleTablesTrait;

	private $authState;
	private User $employee;
	private User $other;
	private int $agreementId;

	protected function setUp(): void
	{
		if (!class_exists(SpecialleavebudgetController::class)) {
			$this->markTestSkipped('The leavedays module is required');
		}

		$this->authState = go()->getAuthState();
		$this->actAs(User::ID_SUPER_ADMIN);

		$install = go()->getEnvironment()->getInstallPath();
		$this->installModuleTables('business', 'business', $install . '/go/modules/business/business/install/install.sql');
		$this->installModuleTables(null, 'leavedays', $install . '/promodules/leavedays/install/install.sql');

		$this->employee = $this->createUser('ldemployee');
		$this->other = $this->createUser('ldother');

		go()->getDbConnection()->insert('business_agreement', [
			'employeeId' => $this->employee->id,
			'start' => '2020-01-01',
			'mo' => 480, 'tu' => 480, 'we' => 480, 'th' => 480, 'fr' => 480, 'sa' => 0, 'su' => 0
		])->execute();
		$this->agreementId = (int) go()->getDbConnection()->getPDO()->lastInsertId();
	}

	protected function tearDown(): void
	{
		if (!isset($this->authState)) {
			return;
		}
		$this->actAs(User::ID_SUPER_ADMIN);
		go()->getDbConnection()->delete('business_agreement', ['id' => $this->agreementId])->execute();
		User::delete(['id' => [$this->employee->id, $this->other->id]]);
		$this->uninstallModuleTables();
		go()->setAuthState($this->authState);
	}

	private function createUser(string $prefix): User
	{
		$name = uniqid($prefix);
		$user = new User();
		$user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($user->save(), $user->getValidationErrorsAsString());
		return $user;
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
		\GO::session()->setCurrentUser($userId);
	}

	private function store(int $userId): string
	{
		$this->actAs($userId);
		ob_start();
		try {
			(new \ReflectionClass(SpecialleavebudgetController::class))->newInstanceWithoutConstructor()->actionStore(['agreement_id' => $this->agreementId]);
		} finally {
			$output = ob_get_clean();
		}
		return $output;
	}

	public function testBudgetsOfOtherEmployee(): void
	{
		$this->expectException(AccessDenied::class);
		$this->store($this->other->id);
	}

	public function testOwnBudgets(): void
	{
		$this->assertStringContainsString('"success":true', $this->store($this->employee->id));
	}
}
