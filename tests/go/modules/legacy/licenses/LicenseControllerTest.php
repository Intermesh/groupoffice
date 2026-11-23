<?php
namespace go\modules\legacy\licenses;

use GO\Base\Exception\AccessDenied;
use GO\Base\Exception\NotFound;
use GO\Licenses\Controller\LicenseController;
use go\core\auth\TemporaryState;
use go\core\model\User;
use go\modules\business\ModuleTablesTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../business/fixtures/ModuleTablesTrait.php';

/**
 * Licenses have no ACL. The license files may only be downloaded by the owner of the license or an administrator.
 */
class LicenseControllerTest extends TestCase
{
	use ModuleTablesTrait;

	private $authState;
	private bool $disableModelCache;
	private User $owner;
	private User $other;
	private int $licenseId;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		// The tables are created for every test, so licenses get the same id
		$this->disableModelCache = \GO::$disableModelCache;
		\GO::$disableModelCache = true;
		$this->createTables(go()->getEnvironment()->getInstallPath() . '/promodules/licenses/install/install.sql', ['li_licenses', 'li_licenses_packages']);

		$this->owner = $this->createUser();
		$this->other = $this->createUser();

		go()->getDbConnection()->insert('li_licenses', [
			'user_id' => $this->owner->id,
			'host' => 'licensetest.example.com',
			'name' => 'licensetest',
			'properties' => '',
			'ctime' => time(),
			'mtime' => time()
		])->execute();
		$this->licenseId = (int) go()->getDbConnection()->getPDO()->lastInsertId();
	}

	protected function tearDown(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		go()->getDbConnection()->delete('li_licenses', ['id' => $this->licenseId])->execute();
		User::delete(['id' => [$this->owner->id, $this->other->id]]);
		$this->uninstallModuleTables();
		go()->setAuthState($this->authState);
		\GO::$disableModelCache = $this->disableModelCache;
	}

	private function createUser(): User
	{
		$name = uniqid('licuser');
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

	private function download(string $action, array $params): void
	{
		$method = new \ReflectionMethod(LicenseController::class, $action);
		ob_start();
		try {
			$method->invoke(new LicenseController(), $params);
		} finally {
			ob_end_clean();
		}
	}

	public static function downloads(): array
	{
		return [
			'JWT license' => ['actionDownloadJWTLicense', 'id'],
			'license file' => ['actionDownloadLicenseFile', 'license_id'],
		];
	}

	#[DataProvider('downloads')]
	public function testOtherUserCantDownloadLicense(string $action, string $param): void
	{
		$this->actAs($this->other->id);
		$this->expectException(AccessDenied::class);
		$this->download($action, [$param => $this->licenseId, 'package_id' => 1]);
	}

	#[DataProvider('downloads')]
	public function testArrayIdIsRefused(string $action, string $param): void
	{
		$this->actAs($this->other->id);
		$this->expectException(NotFound::class);
		$this->download($action, [$param => [$this->licenseId], 'package_id' => 1]);
	}

	private function checkDownloadPermission($licenseId): int
	{
		$method = new \ReflectionMethod(LicenseController::class, 'checkDownloadPermission');
		return $method->invoke(new LicenseController(), $licenseId)->id;
	}

	public function testOwnerMayDownloadLicense(): void
	{
		$this->actAs($this->owner->id);
		$this->assertEquals($this->licenseId, $this->checkDownloadPermission($this->licenseId));
	}

	public function testAdminMayDownloadLicense(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		$this->assertEquals($this->licenseId, $this->checkDownloadPermission($this->licenseId));
	}

	public function testOwnerGetsNotFoundForPackageOfLicense(): void
	{
		// The permission check passes, but the license has no packages
		$this->actAs($this->owner->id);
		$this->expectException(NotFound::class);
		$this->download('actionDownloadLicenseFile', ['license_id' => $this->licenseId, 'package_id' => 1]);
	}
}
