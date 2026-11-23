<?php
namespace go\modules\business\catalog\controller;

use go\core\auth\TemporaryState;
use go\core\model\Acl;
use go\core\model\Group;
use go\core\model\User;
use go\modules\business\business\model\Business;
use go\modules\business\finance\model\FinanceBook;
use go\modules\business\ModuleTablesTrait;
use go\modules\community\addressbook\model\Contact;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../fixtures/ModuleTablesTrait.php';

/**
 * The monthly sales of an article only count the invoices in books the user may read
 */
class ArticleTest extends TestCase
{
	use ModuleTablesTrait;

	private const ARTICLE_ID = 424242;

	private $authState;
	private User $user;
	private ?Business $business = null;
	private ?FinanceBook $book = null;
	private ?Contact $customer = null;
	private int $documentId;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->actAs(User::ID_SUPER_ADMIN);

		$path = go()->getEnvironment()->getInstallPath() . '/go/modules/business/';
		$this->installModuleTables('business', 'business', $path . 'business/install/install.sql');
		$this->installModuleTables('business', 'finance', $path . 'finance/install/install.sql');

		$name = uniqid('cataloguser');
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

		$this->book = new FinanceBook();
		$this->book->name = $name;
		$this->book->businessId = $this->business->id;
		$this->book->type = FinanceBook::TYPE_SALES_INVOICE;
		$this->book->setAcl([Group::ID_INTERNAL => null, Group::ID_EVERYONE => null]);
		$this->assertTrue($this->book->save(), $this->book->getValidationErrorsAsString());

		$this->customer = new Contact();
		$this->customer->isOrganization = true;
		$this->customer->name = $name;
		$this->customer->addressBookId = go()->getAuthState()->getUser(['addressBookSettings'])->addressBookSettings->getDefaultAddressBookId();
		$this->assertTrue($this->customer->save(), $this->customer->getValidationErrorsAsString());

		// An invoice with 3 of the article. Inserted directly because saving an invoice needs a debtor profile.
		$db = go()->getDbConnection();
		$db->insert('business_finance_document', [
			'bookId' => $this->book->id,
			'customerId' => $this->customer->id,
			'date' => '2024-03-15',
			'createdBy' => User::ID_SUPER_ADMIN,
			'createdAt' => new \DateTime(),
			'modifiedBy' => User::ID_SUPER_ADMIN
		])->execute();
		$this->documentId = (int) $db->getPDO()->lastInsertId();
		$db->insert('business_finance_document_item_group', ['documentId' => $this->documentId])->execute();
		$db->insert('business_finance_document_item', [
			'groupId' => (int) $db->getPDO()->lastInsertId(),
			'articleId' => self::ARTICLE_ID,
			'description' => 'Article',
			'quantity' => 3,
			'vatRate' => 0
		])->execute();
	}

	protected function tearDown(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		go()->getDbConnection()->delete('business_finance_document', ['id' => $this->documentId])->execute();
		if (isset($this->customer->id)) {
			Contact::delete(['id' => $this->customer->id]);
		}
		if (isset($this->book->id)) {
			\go\core\model\PdfTemplate::delete(['key' => 'fb-def-' . $this->book->id]);
			\go\core\model\EmailTemplate::delete(['key' => 'fb-def-' . $this->book->id]);
			FinanceBook::delete(['id' => $this->book->id]);
		}
		if (isset($this->business->id)) {
			Business::delete(['id' => $this->business->id]);
		}
		User::delete(['id' => $this->user->id]);

		$this->uninstallModuleTables();
		go()->setAuthState($this->authState);
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
	}

	/**
	 * Without a license the module is only available to administrators, so the constructor is skipped as if the user may
	 * use the module.
	 */
	private function totals(): array
	{
		$this->actAs($this->user->id);
		$controller = (new \ReflectionClass(Article::class))->newInstanceWithoutConstructor();
		return $controller->monthlyTotals(['id' => self::ARTICLE_ID, 'year' => 2024]);
	}

	public function testInvoicesOfUnreadableBooksAreNotCounted(): void
	{
		$this->assertEquals([], $this->totals());
	}

	public function testInvoicesOfReadableBooksAreCounted(): void
	{
		$book = FinanceBook::findById($this->book->id);
		$book->setAcl([$this->user->getPersonalGroup()->id => Acl::LEVEL_READ]);
		$this->assertTrue($book->save(), $book->getValidationErrorsAsString());

		$this->assertEquals([['month' => 3, 'quantity' => 3]], $this->totals());
	}
}
