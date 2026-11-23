<?php
namespace go\modules\business\absence\controller;

use go\core\auth\TemporaryState;
use go\core\exception\Forbidden;
use go\core\model\User;
use go\modules\business\absence\model\Manager;
use go\modules\business\ModuleTablesTrait;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../fixtures/ModuleTablesTrait.php';

/**
 * The budgets of an employee are for the employee and the managers. Status mails about leave are sent by managers, to
 * the employee. Only administrators start the migration from leave days.
 */
class AbsenceControllerTest extends TestCase
{
	use ModuleTablesTrait;

	private static $authState;
	private static User $user;

	public static function setUpBeforeClass(): void
	{
		$path = go()->getEnvironment()->getInstallPath() . '/go/modules/business/';
		self::$authState = go()->getAuthState();
		try {
			self::installModuleTables('business', 'business', $path . 'business/install/install.sql');
			self::installModuleTables('business', 'registration', $path . 'registration/install/install.sql');
			self::installModuleTables('business', 'absence', $path . 'absence/install/install.sql');

			go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
			$name = uniqid('absenceuser');
			self::$user = new User();
			self::$user->setValues([
				'username' => $name,
				'displayName' => $name,
				'email' => $name . '@example.com',
				'password' => 'Pa55word!!xyz'
			]);
			if (!self::$user->save()) {
				throw new \Exception(self::$user->getValidationErrorsAsString());
			}
		} catch (\Throwable $e) {
			// PHPUnit doesn't call tearDownAfterClass() when this fails
			self::uninstallModuleTables();
			go()->setAuthState(self::$authState);
			throw $e;
		}
	}

	public static function tearDownAfterClass(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		if (isset(self::$user)) {
			User::delete(['id' => self::$user->id]);
		}
		self::uninstallModuleTables();
		go()->setAuthState(self::$authState);
	}

	protected function tearDown(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
	}

	/**
	 * Without a license the module is only available to administrators, so the constructor is skipped as if the user may
	 * use the module.
	 *
	 * @template T
	 * @param class-string<T> $class
	 * @return T
	 */
	private function controller(string $class): object
	{
		go()->setAuthState(new TemporaryState(self::$user->id));
		$controller = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
		(new \ReflectionProperty(\go\core\Controller::class, 'rights'))->setValue($controller, (object) ['mayRead' => true, 'mayManage' => false]);
		return $controller;
	}

	public function testUserMayNotReadTheBudgetsOfAnotherEmployee()
	{
		$this->expectException(Forbidden::class);
		$this->controller(Employee::class)->getYearBudgets(['employeeId' => User::ID_SUPER_ADMIN, 'year' => 2026]);
	}

	public function testUserMayNotMailAboutLeaveTheyDontManage()
	{
		$this->expectException(Forbidden::class);
		$this->controller(Registration::class)->sendStatusChangeMail(['id' => PHP_INT_MAX, 'status' => 1, 'emailAddress' => ['email' => 'someone@example.com', 'name' => 'Someone']]);
	}

	public function testUserMayNotMailAboutSpecialLeaveTheyDontManage()
	{
		$this->expectException(Forbidden::class);
		$this->controller(SpecialLeaveBudget::class)->sendStatusChangeMail(['id' => PHP_INT_MAX, 'status' => 1]);
	}

	public function testUserMayNotStartTheMigration()
	{
		$this->expectException(Forbidden::class);
		$this->controller(Registration::class)->migrate();
	}

	public function testManagesEmployee()
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		$this->assertTrue(Manager::managesEmployee(self::$user->id));

		// A user without an employee record manages nobody
		go()->setAuthState(new TemporaryState(self::$user->id));
		$this->assertFalse(Manager::managesEmployee(User::ID_SUPER_ADMIN));
	}
}
