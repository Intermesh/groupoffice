<?php
namespace go\modules\business\finance\controller;

use go\core\auth\TemporaryState;
use go\core\exception\Forbidden;
use go\core\model\EmailTemplate;
use go\core\model\PdfTemplate;
use go\core\model\User;
use go\modules\business\business\model\Business;
use go\core\model\Acl;
use go\modules\business\finance\model\FinanceBook;
use go\modules\business\finance\model\FinanceDocument as FinanceDocumentModel;
use go\modules\community\addressbook\model\Contact;
use go\modules\business\ModuleTablesTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../fixtures/ModuleTablesTrait.php';

/**
 * The totals of a book and the statement of a debtor are financial data. Access to the finance module is not enough:
 * the user needs read permission on the book or the business.
 */
class FinanceDocumentTest extends TestCase
{
	use ModuleTablesTrait;

	private $authState;

	/*
	 * The module tables, the user, the business and the sales invoice book are only read, so they are created once for
	 * the class. The documents and the order books that tests change are created per test.
	 */
	private static User $user;
	private static ?Business $business = null;
	private static ?FinanceBook $book = null;
	private ?FinanceBook $purchaseOrderBook = null;
	private ?FinanceBook $orderBook = null;
	private ?Contact $customer = null;
	private ?FinanceDocumentModel $document = null;

	public static function setUpBeforeClass(): void
	{
		$authState = go()->getAuthState();
		self::actAs(User::ID_SUPER_ADMIN);

		try {
			$path = go()->getEnvironment()->getInstallPath() . '/go/modules/business/';
			self::installModuleTables('business', 'business', $path . 'business/install/install.sql');
			self::installModuleTables('business', 'finance', $path . 'finance/install/install.sql');

			$name = uniqid('finuser');
			self::$user = new User();
			self::$user->setValues([
				'username' => $name,
				'displayName' => $name,
				'email' => $name . '@example.com',
				'password' => 'Pa55word!!xyz'
			]);
			self::assertTrue(self::$user->save(), self::$user->getValidationErrorsAsString());

			self::$business = new Business();
			self::$business->name = $name;
			self::assertTrue(self::$business->save(), self::$business->getValidationErrorsAsString());

			self::$book = new FinanceBook();
			self::$book->name = $name;
			self::$book->businessId = self::$business->id;
			self::$book->type = FinanceBook::TYPE_SALES_INVOICE;
			self::assertTrue(self::$book->save(), self::$book->getValidationErrorsAsString());
		} catch (\Throwable $e) {
			// PHPUnit doesn't call tearDownAfterClass() when this fails, and the module tables would stay behind
			self::tearDownAfterClass();
			throw $e;
		} finally {
			go()->setAuthState($authState);
		}
	}

	public static function tearDownAfterClass(): void
	{
		$authState = go()->getAuthState();
		self::actAs(User::ID_SUPER_ADMIN);
		if (isset(self::$book->id)) {
			PdfTemplate::delete(['key' => 'fb-def-' . self::$book->id]);
			EmailTemplate::delete(['key' => 'fb-def-' . self::$book->id]);
			FinanceBook::delete(['id' => self::$book->id]);
		}
		if (isset(self::$business->id)) {
			Business::delete(['id' => self::$business->id]);
		}
		if (isset(self::$user->id)) {
			User::delete(['id' => self::$user->id]);
		}

		self::uninstallModuleTables();
		go()->setAuthState($authState);
	}

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		self::actAs(User::ID_SUPER_ADMIN);
	}

	protected function tearDown(): void
	{
		self::actAs(User::ID_SUPER_ADMIN);
		if (isset($this->document->id)) {
			FinanceDocumentModel::delete(['id' => $this->document->id]);
		}
		if (isset($this->customer->id)) {
			Contact::delete(['id' => $this->customer->id]);
		}
		foreach ([$this->purchaseOrderBook, $this->orderBook] as $book) {
			if (isset($book->id)) {
				PdfTemplate::delete(['key' => 'fb-def-' . $book->id]);
				EmailTemplate::delete(['key' => 'fb-def-' . $book->id]);
				FinanceBook::delete(['id' => $book->id]);
			}
		}
		go()->setAuthState($this->authState);
	}

	/**
	 * The controller checks access to the finance module in its constructor. Without a license only administrators
	 * have access, so it's skipped as if the user may use the module.
	 */
	private function controller(): FinanceDocument
	{
		return (new \ReflectionClass(FinanceDocument::class))->newInstanceWithoutConstructor();
	}

	private static function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
		// The books of documents are cached with the permission level of the previous user
		(new \ReflectionProperty(\go\core\acl\model\AclItemEntity::class, 'aclEntityCache'))->setValue(null, []);
	}

	public static function totals(): array
	{
		return [
			['monthlyTotals'],
			['yearlyTotals'],
			['totalsPerCategory'],
			['totalsPerStatus'],
		];
	}

	#[DataProvider('totals')]
	public function testTotalsNeedReadPermissionOnTheBook(string $method): void
	{
		$params = ['bookId' => self::$book->id, 'year' => 2024];

		$this->assertIsArray($this->controller()->$method($params));

		self::actAs(self::$user->id);
		$this->expectException(Forbidden::class);
		$this->controller()->$method($params);
	}

	public function testStatementNeedsReadPermissionOnTheDebtor(): void
	{
		self::actAs(self::$user->id);
		$this->expectException(Forbidden::class);
		$this->controller()->statement(['businessId' => self::$business->id, 'contactId' => 1]);
	}

	/**
	 * Create a sales order without items and a purchase order book for the business. A sales order doesn't need a debtor
	 * profile.
	 */
	private function createDocument(): void
	{
		$this->orderBook = new FinanceBook();
		$this->orderBook->name = uniqid('so');
		$this->orderBook->businessId = self::$business->id;
		$this->orderBook->type = FinanceBook::TYPE_SALES_ORDER;
		$this->assertTrue($this->orderBook->save(), $this->orderBook->getValidationErrorsAsString());

		$this->purchaseOrderBook = new FinanceBook();
		$this->purchaseOrderBook->name = uniqid('po');
		$this->purchaseOrderBook->businessId = self::$business->id;
		$this->purchaseOrderBook->type = FinanceBook::TYPE_PURCHASE_ORDER;
		$this->assertTrue($this->purchaseOrderBook->save(), $this->purchaseOrderBook->getValidationErrorsAsString());

		$this->customer = new Contact();
		$this->customer->isOrganization = true;
		$this->customer->name = uniqid('customer');
		$this->customer->addressBookId = go()->getAuthState()->getUser(['addressBookSettings'])->addressBookSettings->getDefaultAddressBookId();
		$this->assertTrue($this->customer->save(), $this->customer->getValidationErrorsAsString());

		$this->document = new FinanceDocumentModel();
		$this->document->bookId = $this->orderBook->id;
		$this->document->setCustomerId($this->customer->id);
		$this->document->date = new \DateTime();
		$this->assertTrue($this->document->save(), $this->document->getValidationErrorsAsString());
	}

	private function grant(FinanceBook $book, int $level): void
	{
		$book = FinanceBook::findById($book->id);
		$book->setAcl([self::$user->getPersonalGroup()->id => $level]);
		$this->assertTrue($book->save(), $book->getValidationErrorsAsString());
	}

	public function testCreatePurchaseOrdersNeedsWritePermissionOnTheDocument(): void
	{
		$this->createDocument();
		$this->grant($this->orderBook, Acl::LEVEL_READ);
		$this->grant($this->purchaseOrderBook, Acl::LEVEL_CREATE);

		self::actAs(self::$user->id);
		$this->expectException(Forbidden::class);
		$this->controller()->createPurchaseOrders(['id' => $this->document->id]);
	}

	public function testCreatePurchaseOrdersNeedsCreatePermissionOnThePurchaseOrderBook(): void
	{
		$this->createDocument();
		$this->grant($this->orderBook, Acl::LEVEL_WRITE);

		self::actAs(self::$user->id);
		$this->expectException(Forbidden::class);
		$this->controller()->createPurchaseOrders(['id' => $this->document->id]);
	}

	public function testCreatePurchaseOrders(): void
	{
		$this->createDocument();
		$this->grant($this->orderBook, Acl::LEVEL_WRITE);
		$this->grant($this->purchaseOrderBook, Acl::LEVEL_CREATE);

		self::actAs(self::$user->id);
		$this->assertEquals(['ids' => []], $this->controller()->createPurchaseOrders(['id' => $this->document->id]));
	}

	public static function documentMethods(): array
	{
		return [['pdf'], ['email'], ['savePdf'], ['createPurchaseOrders']];
	}

	#[DataProvider('documentMethods')]
	public function testUnknownDocument(string $method): void
	{
		$this->expectException(\go\core\exception\NotFound::class);
		$this->controller()->$method(['id' => 999999999]);
	}

	public function testSavePdfNeedsWritePermission(): void
	{
		$this->createDocument();
		$this->grant($this->orderBook, Acl::LEVEL_READ);

		self::actAs(self::$user->id);
		$this->expectException(Forbidden::class);
		$this->controller()->savePdf(['id' => $this->document->id]);
	}
}
