<?php
namespace go\modules\business\automation\controller;

use go\core\auth\TemporaryState;
use go\core\exception\Forbidden;
use go\core\exception\NotFound;
use go\core\model\User;
use go\modules\business\ModuleTablesTrait;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../fixtures/ModuleTablesTrait.php';

/**
 * Running an automation acts on all entities that match its filter, so it needs the same permission as changing it
 */
class AutomationTest extends TestCase
{
	use ModuleTablesTrait;

	private $authState;
	private User $user;
	private int $automationId;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->actAs(User::ID_SUPER_ADMIN);

		$path = go()->getEnvironment()->getInstallPath() . '/go/modules/business/';
		$this->installModuleTables('business', 'automation', $path . 'automation/install/install.sql');

		$name = uniqid('automationuser');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), $this->user->getValidationErrorsAsString());

		go()->getDbConnection()->insert('business_automation_automation', [
			'createdBy' => User::ID_SUPER_ADMIN,
			'createdAt' => new \DateTime(),
			'modifiedBy' => User::ID_SUPER_ADMIN,
			'name' => $name
		])->execute();
		$this->automationId = (int) go()->getDbConnection()->getPDO()->lastInsertId();
	}

	protected function tearDown(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		go()->getDbConnection()->delete('business_automation_automation', ['id' => $this->automationId])->execute();
		User::delete(['id' => $this->user->id]);

		$this->uninstallModuleTables();
		go()->setAuthState($this->authState);
	}

	/**
	 * Without a license the module is only available to administrators, so the constructor is skipped as if the user may
	 * use the module.
	 */
	private function controller(): Automation
	{
		return (new \ReflectionClass(Automation::class))->newInstanceWithoutConstructor();
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
	}

	public function testRunWithoutPermission(): void
	{
		$this->actAs($this->user->id);
		$this->expectException(Forbidden::class);
		$this->controller()->run(['id' => $this->automationId]);
	}

	public function testRunUnknownAutomation(): void
	{
		$this->expectException(NotFound::class);
		$this->controller()->run(['id' => 999999999]);
	}
}
