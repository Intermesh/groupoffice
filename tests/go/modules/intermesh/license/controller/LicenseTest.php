<?php
namespace go\modules\intermesh\license\controller;

use go\core\auth\TemporaryState;
use go\core\exception\Forbidden;
use go\core\model\User;
use go\modules\business\business\model\Business;
use go\modules\business\contracts\model\Contract;
use go\modules\business\contracts\model\ContractBook;
use go\modules\business\finance\model\FinanceBook;
use go\modules\business\ModuleTablesTrait;
use go\modules\community\addressbook\model\Contact;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../business/fixtures/ModuleTablesTrait.php';

/**
 * Customers may only get, renew and upgrade the license contracts they created
 */
class LicenseTest extends TestCase
{
	use ModuleTablesTrait;

	private $authState;
	private User $owner;
	private User $other;
	private ?Business $business = null;
	private ?FinanceBook $invoiceBook = null;
	private ?ContractBook $book = null;
	private ?Contact $customer = null;
	private ?Contract $contract = null;

	public static function setUpBeforeClass(): void
	{
		if (!class_exists(License::class)) {
			self::markTestSkipped('The intermesh license module is not available');
		}

		$path = go()->getEnvironment()->getInstallPath() . '/go/modules/business/';
		try {
			self::installModuleTables('business', 'business', $path . 'business/install/install.sql');
			self::installModuleTables('business', 'finance', $path . 'finance/install/install.sql');
			self::installModuleTables('business', 'contracts', $path . 'contracts/install/install.sql');
		} catch (\Throwable $e) {
			// PHPUnit doesn't call tearDownAfterClass() when this fails, and the module tables would stay behind
			self::uninstallModuleTables();
			throw $e;
		}
	}

	public static function tearDownAfterClass(): void
	{
		self::uninstallModuleTables();
	}

	protected function setUp(): void
	{
		if (!class_exists(License::class)) {
			$this->markTestSkipped('The intermesh license module is not available');
		}

		$this->authState = go()->getAuthState();
		$this->actAs(User::ID_SUPER_ADMIN);

		$this->owner = $this->createUser('licenseowner');
		$this->other = $this->createUser('licenseother');

		$name = uniqid('license');
		$this->business = new Business();
		$this->business->name = $name;
		$this->assertTrue($this->business->save(), $this->business->getValidationErrorsAsString());

		$this->invoiceBook = new FinanceBook();
		$this->invoiceBook->name = $name;
		$this->invoiceBook->businessId = $this->business->id;
		$this->invoiceBook->type = FinanceBook::TYPE_SALES_INVOICE;
		$this->assertTrue($this->invoiceBook->save(), $this->invoiceBook->getValidationErrorsAsString());

		$this->book = new ContractBook();
		$this->book->name = $name;
		$this->book->businessId = $this->business->id;
		$this->book->targetBookId = $this->invoiceBook->id;
		$this->assertTrue($this->book->save(), $this->book->getValidationErrorsAsString());

		$this->customer = new Contact();
		$this->customer->isOrganization = true;
		$this->customer->name = $name;
		$this->customer->addressBookId = go()->getAuthState()->getUser(['addressBookSettings'])->addressBookSettings->getDefaultAddressBookId();
		$this->assertTrue($this->customer->save(), $this->customer->getValidationErrorsAsString());

		// The contract is created by the owner
		$this->actAs($this->owner->id);
		$this->contract = new Contract();
		$this->contract->bookId = $this->book->id;
		$this->contract->customerId = $this->customer->id;
		$this->contract->description = $name;
		$this->contract->startsAt = new \DateTime();
		$this->assertTrue($this->contract->save(), $this->contract->getValidationErrorsAsString());
		$this->actAs(User::ID_SUPER_ADMIN);
	}

	protected function tearDown(): void
	{
		if (!isset($this->authState)) {
			return;
		}
		$this->actAs(User::ID_SUPER_ADMIN);
		if (isset($this->contract->id)) {
			Contract::delete(['id' => $this->contract->id]);
		}
		if (isset($this->customer->id)) {
			Contact::delete(['id' => $this->customer->id]);
		}
		if (isset($this->book->id)) {
			ContractBook::delete(['id' => $this->book->id]);
		}
		if (isset($this->invoiceBook->id)) {
			\go\core\model\PdfTemplate::delete(['key' => 'fb-def-' . $this->invoiceBook->id]);
			\go\core\model\EmailTemplate::delete(['key' => 'fb-def-' . $this->invoiceBook->id]);
			FinanceBook::delete(['id' => $this->invoiceBook->id]);
		}
		if (isset($this->business->id)) {
			Business::delete(['id' => $this->business->id]);
		}
		User::delete(['id' => [$this->owner->id, $this->other->id]]);

		go()->setAuthState($this->authState);
	}

	private function createUser(string $prefix): User
	{
		$name = uniqid($prefix);
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

	/**
	 * The constructor checks access to the module, which needs a license, so it's skipped
	 */
	private function controller(): License
	{
		return (new \ReflectionClass(License::class))->newInstanceWithoutConstructor();
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
	}

	public static function methods(): array
	{
		return [['get'], ['renew'], ['upgrade']];
	}

	#[DataProvider('methods')]
	public function testOtherUsersMayNotUseTheContract(string $method): void
	{
		$this->actAs($this->other->id);
		$this->expectException(Forbidden::class);
		$this->controller()->$method(['contractId' => $this->contract->id]);
	}

	#[DataProvider('methods')]
	public function testUnknownContract(string $method): void
	{
		$this->actAs($this->other->id);
		$this->expectException(Forbidden::class);
		$this->controller()->$method(['contractId' => 999999999]);
	}

	public function testOwnerMayUseTheContract(): void
	{
		$this->actAs($this->owner->id);
		$method = new \ReflectionMethod(License::class, 'findOwnContract');
		$this->assertEquals($this->contract->id, $method->invoke($this->controller(), ['contractId' => $this->contract->id])->id);
	}
}
