<?php
namespace go\modules\humblebuildings\planner\util;

use go\core\auth\TemporaryState;
use go\core\exception\Forbidden;
use go\core\model\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The planner overviews show the data of all employees and projects, so they need the "mayManage" right
 */
class ManagerTraitTest extends TestCase
{
	private $authState;
	private User $user;

	protected function setUp(): void
	{
		if (!trait_exists(ManagerTrait::class)) {
			$this->markTestSkipped('The planner module is required');
		}
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));

		$name = uniqid('planneruser');
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
		if (!isset($this->authState)) {
			return;
		}
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		User::delete(['id' => $this->user->id]);
		go()->setAuthState($this->authState);
	}

	public static function methods(): array
	{
		return [
			['Employee', 'stats', []],
			['Employee', 'distance', ['userId' => User::ID_SUPER_ADMIN, 'latitude' => 52, 'longitude' => 5]],
			['Leaveday', 'forPeriod', []],
			['Project', 'stats', []],
		];
	}

	/**
	 * The module can't be installed in the tests, so the constructor is skipped and the user gets module access without
	 * the "mayManage" right
	 */
	#[DataProvider('methods')]
	public function testPlannerUserWithoutManageRight(string $controller, string $method, array $params): void
	{
		go()->setAuthState(new TemporaryState($this->user->id));
		$cls = 'go\\modules\\humblebuildings\\planner\\controller\\' . $controller;
		$instance = (new \ReflectionClass($cls))->newInstanceWithoutConstructor();
		(new \ReflectionProperty(\go\core\Controller::class, 'rights'))->setValue($instance, (object) ['mayRead' => true, 'mayManage' => false]);

		$this->expectException(Forbidden::class);
		$instance->$method($params);
	}
}
