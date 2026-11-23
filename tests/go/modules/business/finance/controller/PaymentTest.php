<?php
namespace go\modules\business\finance\controller;

use go\core\auth\TemporaryState;
use go\core\model\Acl;
use go\core\model\User;
use go\modules\business\business\model\Business;
use go\modules\business\finance\model\Payment as PaymentModel;
use go\modules\business\ModuleRightsTrait;
use go\modules\business\ModuleTablesTrait;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../fixtures/ModuleTablesTrait.php';
require_once __DIR__ . '/../../fixtures/ModuleRightsTrait.php';

/**
 * Without the "mayChangePayments" right of the finance module payments are read only, also with write permission on
 * the business. An accountant can then view and export the payments without changing them.
 */
class PaymentTest extends TestCase
{
	use ModuleTablesTrait;
	use ModuleRightsTrait;

	private $authState;

	/*
	 * The user, the business it may write and the payment are created once for the class. The rights are granted per
	 * test.
	 */
	private static ?User $user = null;
	private static ?Business $business = null;
	private static ?PaymentModel $payment = null;

	public static function setUpBeforeClass(): void
	{
		$authState = go()->getAuthState();
		self::actAs(User::ID_SUPER_ADMIN);

		try {
			$path = go()->getEnvironment()->getInstallPath() . '/go/modules/business/';
			self::installModuleTables('business', 'business', $path . 'business/install/install.sql');
			self::installModuleTables('business', 'finance', $path . 'finance/install/install.sql');

			$name = uniqid('paymentuser');
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

			self::$payment = new PaymentModel();
			self::$payment->businessId = self::$business->id;
			self::$payment->amount = 10;
			self::$payment->description = 'Test payment';
			if (!self::$payment->save()) {
				throw new \Exception(self::$payment->getValidationErrorsAsString());
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
			PaymentModel::delete(['businessId' => self::$business->id]);
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
		self::revokeModuleRights('business', 'finance', self::$user);
		go()->setAuthState($this->authState);
	}

	/**
	 * The controller checks access to the finance module in its constructor. Without a license only administrators
	 * have access, so it's skipped as if the user may use the module.
	 */
	private function controller(): Payment
	{
		return (new \ReflectionClass(Payment::class))->newInstanceWithoutConstructor();
	}

	private static function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
		// The businesses of payments are cached with the permission level of the previous user
		(new \ReflectionProperty(\go\core\acl\model\AclItemEntity::class, 'aclEntityCache'))->setValue(null, []);
		// and the ACL levels aren't cleared when testWithTheRightAndReadPermissionOnTheBusinessPaymentsAreReadOnly changes the ACL
		(new \ReflectionProperty(Acl::class, 'permissionLevelCache'))->setValue(null, []);
	}

	private function set(array $params): array
	{
		return $this->controller()->set($params)->getArray();
	}

	public function testWithoutTheRightPaymentsAreReadOnly(): void
	{
		self::grantModuleRights('business', 'finance', self::$user, []);
		self::actAs(self::$user->id);

		$payment = PaymentModel::findById(self::$payment->id);
		$this->assertTrue($payment->hasPermissionLevel(Acl::LEVEL_READ));
		$this->assertFalse($payment->hasPermissionLevel(Acl::LEVEL_WRITE));

		$result = $this->set([
			'create' => ['p1' => ['businessId' => self::$business->id, 'amount' => 5]],
			'update' => [self::$payment->id => ['amount' => 20, 'checked' => false]],
			'destroy' => [self::$payment->id]
		]);

		$this->assertArrayHasKey('p1', $result['notCreated'] ?? []);
		$this->assertArrayHasKey(self::$payment->id, $result['notUpdated'] ?? []);
		$this->assertArrayHasKey(self::$payment->id, $result['notDestroyed'] ?? []);

		self::actAs(User::ID_SUPER_ADMIN);
		$this->assertEquals(10, PaymentModel::findById(self::$payment->id)->amount);
	}

	public function testTheRightToSendRemindersDoesNotAllowChangingPayments(): void
	{
		self::grantModuleRights('business', 'finance', self::$user, ['maySendReminders']);
		self::actAs(self::$user->id);

		$this->assertFalse(PaymentModel::findById(self::$payment->id)->hasPermissionLevel(Acl::LEVEL_WRITE));
	}

	public function testWithTheRightPaymentsCanBeChanged(): void
	{
		self::grantModuleRights('business', 'finance', self::$user, ['mayChangePayments']);
		self::actAs(self::$user->id);

		$result = $this->set([
			'create' => ['p1' => ['businessId' => self::$business->id, 'amount' => 5]],
			'update' => [self::$payment->id => ['description' => 'Changed']]
		]);

		$this->assertArrayHasKey('p1', $result['created'] ?? [], json_encode($result['notCreated'] ?? null));
		$this->assertArrayHasKey(self::$payment->id, $result['updated'] ?? [], json_encode($result['notUpdated'] ?? null));

		self::actAs(User::ID_SUPER_ADMIN);
		PaymentModel::delete(['id' => $result['created']['p1']['id']]);
		$payment = PaymentModel::findById(self::$payment->id);
		$payment->description = 'Test payment';
		$this->assertTrue($payment->save());
	}

	public function testWithTheRightAndReadPermissionOnTheBusinessPaymentsAreReadOnly(): void
	{
		self::actAs(User::ID_SUPER_ADMIN);
		$business = Business::findById(self::$business->id);
		$business->setAcl([self::$user->getPersonalGroup()->id => Acl::LEVEL_READ]);
		$this->assertTrue($business->save());

		try {
			self::grantModuleRights('business', 'finance', self::$user, ['mayChangePayments']);
			self::actAs(self::$user->id);
			$this->assertFalse(PaymentModel::findById(self::$payment->id)->hasPermissionLevel(Acl::LEVEL_WRITE));
		} finally {
			self::actAs(User::ID_SUPER_ADMIN);
			$business = Business::findById(self::$business->id);
			$business->setAcl([self::$user->getPersonalGroup()->id => Acl::LEVEL_WRITE]);
			$this->assertTrue($business->save());
		}
	}
}
