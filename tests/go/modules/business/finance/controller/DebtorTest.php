<?php
namespace go\modules\business\finance\controller;

use go\core\auth\TemporaryState;
use go\core\exception\Forbidden;
use go\core\model\Acl;
use go\core\model\User;
use go\modules\business\business\model\Business;
use go\modules\business\ModuleTablesTrait;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../fixtures/ModuleTablesTrait.php';

/**
 * Debtor/runActions sends reminders to customers, so it needs write permission on the business. Running the actions of
 * all businesses is only for administrators.
 */
class DebtorTest extends TestCase
{
	use ModuleTablesTrait;

	private $authState;
	private User $user;
	private ?Business $business = null;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->actAs(User::ID_SUPER_ADMIN);

		$path = go()->getEnvironment()->getInstallPath() . '/go/modules/business/';
		$this->installModuleTables('business', 'business', $path . 'business/install/install.sql');
		$this->installModuleTables('business', 'finance', $path . 'finance/install/install.sql');

		$name = uniqid('debtoruser');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), $this->user->getValidationErrorsAsString());

		$this->business = new Business();
		$this->business->name = $name;
		$this->assertTrue($this->business->save(), $this->business->getValidationErrorsAsString());
	}

	protected function tearDown(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		if (isset($this->business->id)) {
			Business::delete(['id' => $this->business->id]);
		}
		User::delete(['id' => $this->user->id]);

		$this->uninstallModuleTables();
		go()->setAuthState($this->authState);
	}

	/**
	 * Without a license the module is only available to administrators, so the constructor is skipped as if the user may
	 * use the module.
	 */
	private function controller(): Debtor
	{
		return (new \ReflectionClass(Debtor::class))->newInstanceWithoutConstructor();
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
	}

	private function grant(int $level): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		$business = Business::findById($this->business->id);
		$business->setAcl([$this->user->getPersonalGroup()->id => $level]);
		$this->assertTrue($business->save(), $business->getValidationErrorsAsString());
	}

	public function testAdminMayRunTheActionsOfAllBusinesses(): void
	{
		$this->assertIsArray($this->controller()->runActions(['dryRun' => true, 'businessId' => null]));
	}

	public function testOnlyAdminsMayRunTheActionsOfAllBusinesses(): void
	{
		$this->grant(Acl::LEVEL_WRITE);

		$this->actAs($this->user->id);
		$this->expectException(Forbidden::class);
		$this->controller()->runActions(['dryRun' => true, 'businessId' => null]);
	}

	public function testRunActionsWithReadPermissionOnTheBusiness(): void
	{
		$this->grant(Acl::LEVEL_READ);

		$this->actAs($this->user->id);
		$this->expectException(Forbidden::class);
		$this->controller()->runActions(['dryRun' => true, 'businessId' => $this->business->id]);
	}

	public function testRunActionsWithWritePermissionOnTheBusiness(): void
	{
		$this->grant(Acl::LEVEL_WRITE);

		$this->actAs($this->user->id);
		$this->assertEquals(['count' => 0, 'errors' => []], $this->controller()->runActions(['dryRun' => true, 'businessId' => $this->business->id]));
	}

	public function testReportOnlyReturnsTheSqlWhenDebugging(): void
	{
		$debugger = go()->getDebugger();
		$enabled = $debugger->enabled;
		try {
			$debugger->enabled = false;
			$this->assertArrayNotHasKey('query', $this->controller()->report([]));

			$debugger->enabled = true;
			$this->assertArrayHasKey('query', $this->controller()->report([]));
		} finally {
			$debugger->enabled = $enabled;
		}
	}

	public function testRunActionsWithoutBusinessParameter(): void
	{
		$this->assertIsArray($this->controller()->runActions(['dryRun' => true]));
	}
}
