<?php
namespace go\modules\business\registration\controller;

use go\core\auth\TemporaryState;
use go\core\exception\Forbidden;
use go\core\model\User;
use go\modules\business\ModuleTablesTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../fixtures/ModuleTablesTrait.php';

/**
 * Whether the weeks and months of an employee are closed or have declined registrations is only for the employee and
 * the managers
 */
class RegistrationTest extends TestCase
{
	use ModuleTablesTrait;

	private $authState;
	private User $user;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));

		$path = go()->getEnvironment()->getInstallPath() . '/go/modules/business/';
		$this->installModuleTables('business', 'business', $path . 'business/install/install.sql');
		$this->installModuleTables('business', 'registration', $path . 'registration/install/install.sql');

		$name = uniqid('reguser');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), $this->user->getValidationErrorsAsString());
	}

	protected function tearDown(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		User::delete(['id' => $this->user->id]);
		$this->uninstallModuleTables();
		go()->setAuthState($this->authState);
	}

	/**
	 * Without a license the module is only available to administrators, so the constructor is skipped as if the user may
	 * use the module without the "mayManage" right.
	 */
	private function controller(): Registration
	{
		$controller = (new \ReflectionClass(Registration::class))->newInstanceWithoutConstructor();
		(new \ReflectionProperty(\go\core\Controller::class, 'rights'))->setValue($controller, (object) ['mayRead' => true, 'mayManage' => false]);
		return $controller;
	}

	public static function methods(): array
	{
		return [['weeks'], ['months']];
	}

	#[DataProvider('methods')]
	public function testPeriodsOfOtherEmployee(string $method): void
	{
		go()->setAuthState(new TemporaryState($this->user->id));
		$this->expectException(Forbidden::class);
		$this->controller()->$method(['year' => 2024, 'userId' => User::ID_SUPER_ADMIN]);
	}

	/**
	 * The "employee" relation of users is only added when the business module is loaded, which is not the case in the
	 * tests. So the check is tested on its own.
	 */
	public function testOwnPeriods(): void
	{
		go()->setAuthState(new TemporaryState($this->user->id));
		(new \ReflectionMethod(Registration::class, 'checkEmployee'))->invoke($this->controller(), $this->user->id);
		$this->assertTrue(true);
	}
}
