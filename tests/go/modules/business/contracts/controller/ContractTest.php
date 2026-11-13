<?php
namespace go\modules\business\contracts\controller;

use go\core\auth\TemporaryState;
use go\core\exception\Forbidden;
use go\core\model\Acl;
use go\core\model\User;
use go\modules\business\business\model\Business;
use go\modules\business\contracts\model\ContractBook;
use go\modules\business\finance\model\FinanceBook;
use go\modules\business\ModuleTablesTrait;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../fixtures/ModuleTablesTrait.php';

/**
 * Contract/createInvoices creates and sends invoices. The contract controller skips the module permission check, so
 * the method must check the contract book itself.
 */
class ContractTest extends TestCase
{
	use ModuleTablesTrait;

	private $authState;
	private User $user;
	private ?Business $business = null;
	private ?ContractBook $book = null;
	private ?FinanceBook $invoiceBook = null;

	public static function setUpBeforeClass(): void
	{
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
		$this->authState = go()->getAuthState();
		$this->actAs(User::ID_SUPER_ADMIN);

		$name = uniqid('contractuser');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), $this->user->getValidationErrorsAsString());

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
	}

	protected function tearDown(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
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
		User::delete(['id' => $this->user->id]);

		go()->setAuthState($this->authState);
	}

	/**
	 * Without a license the module is only available to administrators, so the constructor is skipped as if the user may
	 * use the module.
	 */
	private function controller(): Contract
	{
		return (new \ReflectionClass(Contract::class))->newInstanceWithoutConstructor();
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
	}

	private function grant(int $level): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		$book = ContractBook::findById($this->book->id);
		$book->setAcl([$this->user->getPersonalGroup()->id => $level]);
		$this->assertTrue($book->save(), $book->getValidationErrorsAsString());
	}

	public function testAdminMayCreateTheInvoicesOfAllBooks(): void
	{
		$this->assertIsArray($this->controller()->createInvoices([]));
	}

	public function testOnlyAdminsMayCreateTheInvoicesOfAllBooks(): void
	{
		$this->grant(Acl::LEVEL_WRITE);

		$this->actAs($this->user->id);
		$this->expectException(Forbidden::class);
		$this->controller()->createInvoices([]);
	}

	public function testCreateInvoicesWithoutPermissionOnTheBook(): void
	{
		$this->actAs($this->user->id);
		$this->expectException(Forbidden::class);
		$this->controller()->createInvoices(['bookId' => $this->book->id]);
	}

	public function testCreateInvoicesWithReadPermissionOnTheBook(): void
	{
		$this->grant(Acl::LEVEL_READ);

		$this->actAs($this->user->id);
		$this->expectException(Forbidden::class);
		$this->controller()->createInvoices(['bookId' => $this->book->id]);
	}

	public function testCreateInvoicesWithWritePermissionOnTheBook(): void
	{
		$this->grant(Acl::LEVEL_WRITE);

		$this->actAs($this->user->id);
		$this->assertEquals(['count' => 0, 'errors' => []], $this->controller()->createInvoices(['bookId' => $this->book->id]));
	}
}
