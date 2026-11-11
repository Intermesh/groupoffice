<?php
namespace go\modules\business\finance\controller;

use go\core\auth\TemporaryState;
use go\core\exception\Forbidden;
use go\core\model\EmailTemplate;
use go\core\model\PdfTemplate;
use go\core\model\User;
use go\modules\business\business\model\Business;
use go\modules\business\finance\model\FinanceBook;
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
	private User $user;
	private ?Business $business = null;
	private ?FinanceBook $book = null;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->actAs(User::ID_SUPER_ADMIN);

		$path = go()->getEnvironment()->getInstallPath() . '/go/modules/business/';
		$this->installModuleTables('business', 'business', $path . 'business/install/install.sql');
		$this->installModuleTables('business', 'finance', $path . 'finance/install/install.sql');

		$name = uniqid('finuser');
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
		$this->assertTrue($this->book->save(), $this->book->getValidationErrorsAsString());
	}

	protected function tearDown(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		if (isset($this->book->id)) {
			PdfTemplate::delete(['key' => 'fb-def-' . $this->book->id]);
			EmailTemplate::delete(['key' => 'fb-def-' . $this->book->id]);
			FinanceBook::delete(['id' => $this->book->id]);
		}
		if (isset($this->business->id)) {
			Business::delete(['id' => $this->business->id]);
		}
		User::delete(['id' => $this->user->id]);

		$this->uninstallModuleTables();
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

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
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
		$params = ['bookId' => $this->book->id, 'year' => 2024];

		$this->assertIsArray($this->controller()->$method($params));

		$this->actAs($this->user->id);
		$this->expectException(Forbidden::class);
		$this->controller()->$method($params);
	}

	public function testStatementNeedsReadPermissionOnTheDebtor(): void
	{
		$this->actAs($this->user->id);
		$this->expectException(Forbidden::class);
		$this->controller()->statement(['businessId' => $this->business->id, 'contactId' => 1]);
	}
}
