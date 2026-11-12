<?php
namespace go\modules\agya\applications;

use go\core\auth\TemporaryState;
use go\core\exception\Forbidden;
use go\core\model\Module as ModuleModel;
use go\core\model\Permission;
use go\core\model\User;
use go\modules\agya\applications\model\Application;
use go\modules\business\ModuleTablesTrait;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../business/fixtures/ModuleTablesTrait.php';

/**
 * The download and PDF of an application don't go through JMAP, so they must check access to the applications module
 * themselves.
 */
class ModuleTest extends TestCase
{
	use ModuleTablesTrait;

	private $authState;
	private User $user;
	private ?string $applicationId = null;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));

		$this->installModuleTables('agya', 'applications', go()->getEnvironment()->getInstallPath() . '/go/modules/agya/applications/install/install.sql');

		$name = uniqid('agya');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), $this->user->getValidationErrorsAsString());

		// Saving an application sends an invitation, so it's inserted directly
		$this->applicationId = $name . '@example.com';
		go()->getDbConnection()->insert('agya_applications_application', [
			'email' => $this->applicationId,
			'token' => 'token'
		])->execute();
	}

	protected function tearDown(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		go()->getDbConnection()->delete('agya_applications_application', ['email' => $this->applicationId])->execute();

		$module = ModuleModel::findByName('agya', 'applications');
		if ($module && isset($module->permissions[$this->user->getPersonalGroup()->id])) {
			unset($module->permissions[$this->user->getPersonalGroup()->id]);
			$module->save();
		}
		User::delete(['id' => $this->user->id]);

		$this->uninstallModuleTables();
		go()->setAuthState($this->authState);
	}

	private function findAs(int $userId): Application
	{
		go()->setAuthState(new TemporaryState($userId));
		try {
			return Module::get()->findApplication($this->applicationId);
		} finally {
			go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		}
	}

	public function testUserWithoutModuleAccessCannotDownload(): void
	{
		$this->expectException(Forbidden::class);
		$this->findAs($this->user->id);
	}

	public function testUserWithModuleAccessCanDownload(): void
	{
		$module = ModuleModel::findByName('agya', 'applications');
		$module->permissions[$this->user->getPersonalGroup()->id] = new Permission($module);
		$this->assertTrue($module->save());

		$this->assertSame($this->applicationId, $this->findAs($this->user->id)->email);
	}
}
