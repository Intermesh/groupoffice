<?php
namespace go\modules\business\finance\model;

use go\core\auth\TemporaryState;
use go\core\model\EmailTemplate;
use go\core\model\PdfTemplate;
use go\core\model\User;
use go\core\util\DateTime;
use go\modules\business\business\model\Business;
use go\modules\business\ModuleTablesTrait;
use go\modules\community\addressbook\model\Contact;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../fixtures/ModuleTablesTrait.php';

/**
 * An invoice that is on hold doesn't trigger reminders and is left out of the customer's statement.
 */
class DebtorTest extends TestCase
{
	use ModuleTablesTrait;

	private $authState;

	/*
	 * The module tables, the business with its debtor profile, the sales invoice book, the customer and the debtor are
	 * only read, so they are created once for the class. The invoice is created per test.
	 */
	private static ?Business $business = null;
	private static ?FinanceBook $book = null;
	private static ?Contact $customer = null;
	private static ?Debtor $debtor = null;
	private ?FinanceDocument $invoice = null;

	public static function setUpBeforeClass(): void
	{
		$authState = go()->getAuthState();
		self::actAs(User::ID_SUPER_ADMIN);

		try {
			$path = go()->getEnvironment()->getInstallPath() . '/go/modules/business/';
			self::installModuleTables('business', 'business', $path . 'business/install/install.sql');
			self::installModuleTables('business', 'finance', $path . 'finance/install/install.sql');

			$name = uniqid('onhold');
			self::$business = new Business();
			self::$business->name = $name;
			self::assertTrue(self::$business->save(), self::$business->getValidationErrorsAsString());

			// The invoice needs a profile for its expiry date
			$profile = DebtorProfile::find()->where(['businessId' => self::$business->id])->single();
			if (!$profile) {
				$profile = new DebtorProfile();
				$profile->businessId = self::$business->id;
				$profile->name = $name;
				self::assertTrue($profile->save(), $profile->getValidationErrorsAsString());
			}

			self::$book = new FinanceBook();
			self::$book->name = $name;
			self::$book->businessId = self::$business->id;
			self::$book->type = FinanceBook::TYPE_SALES_INVOICE;
			self::assertTrue(self::$book->save(), self::$book->getValidationErrorsAsString());

			self::$customer = new Contact();
			self::$customer->isOrganization = true;
			self::$customer->name = $name;
			self::$customer->addressBookId = go()->getAuthState()->getUser(['addressBookSettings'])->addressBookSettings->getDefaultAddressBookId();
			self::assertTrue(self::$customer->save(), self::$customer->getValidationErrorsAsString());

			// Not in the grace period after a previous reminder
			self::$debtor = Debtor::findOrCreate(self::$business->id . '-' . self::$customer->id);
			self::$debtor->graceUntil = new DateTime('-1 day');
			self::assertTrue(self::$debtor->save(), self::$debtor->getValidationErrorsAsString());
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
			// Also deletes the debtor and the debtor profile
			Business::delete(['id' => self::$business->id]);
		}
		if (isset(self::$customer->id)) {
			Contact::delete(['id' => self::$customer->id]);
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
		if (isset($this->invoice->id)) {
			FinanceDocument::delete(['id' => $this->invoice->id]);
		}
		go()->setAuthState($this->authState);
	}

	private static function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
	}

	/**
	 * Create a sent sales invoice of 100 that expired two weeks ago
	 */
	private function createOverdueInvoice(bool $onHold): void
	{
		$this->invoice = new FinanceDocument();
		$this->invoice->bookId = self::$book->id;
		$this->invoice->setCustomerId(self::$customer->id);
		$this->invoice->date = new DateTime('-1 month');
		$this->invoice->sentAt = new DateTime('-1 month');
		$this->invoice->onHold = $onHold;
		$this->invoice->setValues([
			'itemGroups' => [
				['items' => [['description' => 'Work', 'quantity' => 1, 'unitPrice' => 100, 'vatRate' => 0]]]
			]
		]);
		$this->assertTrue($this->invoice->save(), $this->invoice->getValidationErrorsAsString());
		$this->assertLessThan(new DateTime(), $this->invoice->expiresAt);
	}

	private function isActionable(): bool
	{
		foreach (Debtor::findActionable(self::$business->id) as $debtor) {
			if ($debtor->contactId == self::$customer->id) {
				return true;
			}
		}
		return false;
	}

	private function statementInvoiceIds(): array
	{
		return array_map('intval', Debtor::findById(self::$business->id . '-' . self::$customer->id)->findStatementInvoices()->selectSingleValue('document.id')->all());
	}

	public function testOverdueInvoiceTriggersReminder(): void
	{
		$this->createOverdueInvoice(false);

		$this->assertTrue($this->isActionable());
		$this->assertContains((int) $this->invoice->id, $this->statementInvoiceIds());
	}

	public function testInvoiceOnHoldDoesNotTriggerReminder(): void
	{
		$this->createOverdueInvoice(true);

		$this->assertFalse($this->isActionable());
	}

	public function testInvoiceOnHoldIsLeftOutOfStatement(): void
	{
		$this->createOverdueInvoice(true);

		$this->assertNotContains((int) $this->invoice->id, $this->statementInvoiceIds());
	}

	public function testCopyIsNotOnHold(): void
	{
		$this->createOverdueInvoice(true);

		$this->assertFalse($this->invoice->copy()->onHold);
	}
}
