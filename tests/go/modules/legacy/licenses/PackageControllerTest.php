<?php
namespace go\modules\legacy\licenses;

use GO\Base\Exception\AccessDenied;
use GO\Licenses\Controller\PackageController;
use go\core\auth\TemporaryState;
use go\core\model\User;
use PHPUnit\Framework\TestCase;

/**
 * Sending a package uploads a file of the server with the upload account from config.php. Guests and users may not do
 * that.
 */
class PackageControllerTest extends TestCase
{
	private $authState;
	private User $user;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$name = uniqid('licuser');
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
		$this->actAs(User::ID_SUPER_ADMIN);
		User::delete(['id' => $this->user->id]);
		go()->setAuthState($this->authState);
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
		\GO::session()->setCurrentUser($userId);
	}

	public function testSendPackageIsNotForGuests(): void
	{
		$method = new \ReflectionMethod(PackageController::class, 'allowGuests');
		$this->assertNotContains('sendpackage', $method->invoke(new PackageController()));
	}

	public function testDownloadPackageFileIsNotForGuests(): void
	{
		// Packages need a license, so only logged in users with access to the licenses module may download them
		$method = new \ReflectionMethod(PackageController::class, 'allowGuests');
		$this->assertNotContains('downloadpackagefile', $method->invoke(new PackageController()));
	}

	public function testSendPackageIsOnlyForAdmins(): void
	{
		$this->actAs($this->user->id);
		ob_start();
		try {
			(new PackageController())->actionSendPackage(['f' => '/nonexistent/groupoffice-pro-1.0.0.tar.gz']);
			$this->fail('A user sent a package');
		} catch (AccessDenied $e) {
			$this->assertTrue(true);
		} finally {
			ob_end_clean();
			$this->actAs(User::ID_SUPER_ADMIN);
		}
	}

	public function testSendPackageNeedsTheUploadAccountInConfig(): void
	{
		$config = go()->getConfig();
		if (!empty($config['licensesPackageUploadUsername']) || !empty($config['licensesPackageUploadPassword'])) {
			$this->markTestSkipped('An upload account is configured');
		}

		$file = \GO::config()->getTempFolder()->path() . '/groupoffice-pro-1.0.0.tar.gz';
		file_put_contents($file, 'test');
		$this->actAs(User::ID_SUPER_ADMIN);
		ob_start();
		try {
			(new PackageController())->actionSendPackage(['f' => $file]);
			$this->fail('The package was sent without an upload account');
		} catch (\Exception $e) {
			$this->assertStringContainsString('licensesPackageUploadUsername', $e->getMessage());
		} finally {
			ob_end_clean();
			unlink($file);
		}
	}
}
