<?php
namespace go\modules\business\approval\controller;

use go\core\auth\TemporaryState;
use go\core\exception\Forbidden;
use go\core\model\User;
use go\modules\business\ModuleTablesTrait;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../fixtures/ModuleTablesTrait.php';

/**
 * The disapproval message can only be sent to employees the user manages, so the mail can't be used for anyone else
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

		$name = uniqid('approvaluser');
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

	public function testOnlyEmployeesTheUserManages(): void
	{
		go()->setAuthState(new TemporaryState($this->user->id));
		$controller = (new \ReflectionClass(Registration::class))->newInstanceWithoutConstructor();

		$this->expectException(Forbidden::class);
		$controller->sendDisapprovalMail(['message' => 'Hello', 'employeeIds' => [User::ID_SUPER_ADMIN]]);
	}
}
