<?php
namespace go\modules\legacy\projects2;

use GO\Base\Exception\AccessDenied;
use GO\Base\Model\Acl;
use GO\Projects2\Controller\InvoiceController;
use go\core\model\Acl as CoreAcl;
use go\core\model\Group;
use go\core\model\User;
use go\modules\business\business\model\Business;
use go\modules\business\finance\model\FinanceBook;
use go\modules\business\ModuleTablesTrait;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/fixtures/ProjectsTrait.php';
require_once __DIR__ . '/../../business/fixtures/ModuleTablesTrait.php';

/**
 * An invoice of a project is created in the finance book the user chooses. The user needs create permission on it.
 */
class InvoiceControllerTest extends TestCase
{
	use ProjectsTrait;
	use ModuleTablesTrait;

	private ?Business $business = null;
	private ?FinanceBook $book = null;
	private int $projectId;

	protected function setUp(): void
	{
		if (!class_exists(InvoiceController::class)) {
			$this->markTestSkipped('The projects2 module is required');
		}

		$this->setUpProjects();

		$path = go()->getEnvironment()->getInstallPath() . '/go/modules/business/';
		$this->installModuleTables('business', 'business', $path . 'business/install/install.sql');
		$this->installModuleTables('business', 'finance', $path . 'finance/install/install.sql');

		$this->projectId = $this->createProject($this->createType(Acl::READ_PERMISSION))->id;

		$this->business = new Business();
		$this->business->name = uniqid('pr2business');
		$this->assertTrue($this->business->save(), $this->business->getValidationErrorsAsString());

		$this->book = new FinanceBook();
		$this->book->name = uniqid('pr2book');
		$this->book->businessId = $this->business->id;
		$this->book->type = FinanceBook::TYPE_SALES_INVOICE;
		$this->book->setAcl([Group::ID_INTERNAL => null, Group::ID_EVERYONE => null]);
		$this->assertTrue($this->book->save(), $this->book->getValidationErrorsAsString());
	}

	protected function tearDown(): void
	{
		if (!isset($this->authState)) {
			return;
		}
		$this->actAs(User::ID_SUPER_ADMIN);
		if (isset($this->book->id)) {
			\go\core\model\PdfTemplate::delete(['key' => 'fb-def-' . $this->book->id]);
			\go\core\model\EmailTemplate::delete(['key' => 'fb-def-' . $this->book->id]);
			FinanceBook::delete(['id' => $this->book->id]);
		}
		if (isset($this->business->id)) {
			Business::delete(['id' => $this->business->id]);
		}
		// Projects first: uninstallModuleTables() clears the caches, which must not still know the projects module
		$this->tearDownProjects();
		$this->uninstallModuleTables();
	}

	private function createInvoice(): void
	{
		$this->actAs($this->user->id);
		(new \ReflectionMethod(InvoiceController::class, 'actionCreateFinanceDocument'))->invoke(new InvoiceController(), [
			'project_id' => $this->projectId,
			'bookId' => $this->book->id,
			'incomeIds' => '[]',
			'timeIds' => '[1]'
		]);
	}

	public function testBookWithoutCreatePermission(): void
	{
		$this->expectException(AccessDenied::class);
		$this->createInvoice();
	}

	public function testBookWithCreatePermission(): void
	{
		$book = FinanceBook::findById($this->book->id);
		$book->setAcl([$this->user->getPersonalGroup()->id => CoreAcl::LEVEL_CREATE]);
		$this->assertTrue($book->save(), $book->getValidationErrorsAsString());

		// It continues after the permission check and stops at the customer that the project doesn't have
		$this->expectExceptionMessage(go()->t("It's required to set a customer for the project when you create invoices from the project.", "legacy", "projects2"));
		$this->createInvoice();
	}
}
