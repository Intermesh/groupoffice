<?php
namespace go\modules\business\finance\convert;

use go\core\auth\TemporaryState;
use go\core\fs\File;
use go\core\model\Acl;
use go\core\model\User;
use go\modules\business\business\model\Business;
use go\modules\business\finance\model\Payment;
use go\modules\business\ModuleRightsTrait;
use go\modules\business\ModuleTablesTrait;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../fixtures/ModuleTablesTrait.php';
require_once __DIR__ . '/../../fixtures/ModuleRightsTrait.php';

/**
 * The bank statement converters save the payments themselves, so they must check the permission before saving. Importing
 * needs the "mayChangePayments" right and write permission on the business.
 */
class Camt053Test extends TestCase
{
	use ModuleTablesTrait;
	use ModuleRightsTrait;

	private $authState;

	/* The user and the business are only read by the tests, so they are created once for the class. */
	private static ?User $user = null;
	private static ?Business $business = null;

	public static function setUpBeforeClass(): void
	{
		$authState = go()->getAuthState();
		self::actAs(User::ID_SUPER_ADMIN);

		try {
			$path = go()->getEnvironment()->getInstallPath() . '/go/modules/business/';
			self::installModuleTables('business', 'business', $path . 'business/install/install.sql');
			self::installModuleTables('business', 'finance', $path . 'finance/install/install.sql');

			$name = uniqid('camtuser');
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

			self::$business = new Business();
			self::$business->name = $name;
			self::$business->setAcl([self::$user->getPersonalGroup()->id => Acl::LEVEL_WRITE]);
			if (!self::$business->save()) {
				throw new \Exception(self::$business->getValidationErrorsAsString());
			}
		} catch (\Throwable $e) {
			// PHPUnit doesn't call tearDownAfterClass() when this fails
			self::cleanUp();
			go()->setAuthState($authState);
			throw $e;
		}
		go()->setAuthState($authState);
	}

	public static function tearDownAfterClass(): void
	{
		$authState = go()->getAuthState();
		self::actAs(User::ID_SUPER_ADMIN);
		self::cleanUp();
		go()->setAuthState($authState);
	}

	private static function cleanUp(): void
	{
		if (isset(self::$business->id)) {
			Business::delete(['id' => self::$business->id]);
		}
		if (isset(self::$user->id)) {
			User::delete(['id' => self::$user->id]);
		}
		self::uninstallModuleTables();
	}

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
	}

	protected function tearDown(): void
	{
		self::actAs(User::ID_SUPER_ADMIN);
		Payment::delete(['businessId' => self::$business->id]);
		self::revokeModuleRights('business', 'finance', self::$user);
		go()->setAuthState($this->authState);
	}

	private static function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
		// The businesses of payments are cached with the permission level of the previous user
		(new \ReflectionProperty(\go\core\acl\model\AclItemEntity::class, 'aclEntityCache'))->setValue(null, []);
	}

	/**
	 * Import the statement with one incoming payment of 12.50 as the user
	 */
	private function import(): array
	{
		self::actAs(self::$user->id);
		$converter = new Camt053('xml', Payment::class);
		return $converter->importFile(new File(__DIR__ . '/fixtures/camt053.xml'), ['values' => ['businessId' => self::$business->id]]);
	}

	private function countPayments(): int
	{
		self::actAs(User::ID_SUPER_ADMIN);
		return Payment::find()->where('businessId', '=', self::$business->id)->selectSingleValue('count(*)')->single();
	}

	public function testImportWithoutTheRightToChangePayments(): void
	{
		self::grantModuleRights('business', 'finance', self::$user, ['maySendReminders']);

		$response = $this->import();

		$this->assertEquals(0, $response['count']);
		$this->assertNotEmpty($response['errors']);
		$this->assertEquals(0, $this->countPayments());
	}

	public function testImportWithTheRightToChangePayments(): void
	{
		self::grantModuleRights('business', 'finance', self::$user, ['mayChangePayments']);

		$response = $this->import();

		$this->assertEquals([], $response['errors']);
		$this->assertEquals(1, $response['count']);
		$this->assertEquals(1, $this->countPayments());
	}
}
