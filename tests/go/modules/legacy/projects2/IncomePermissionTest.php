<?php
namespace go\modules\legacy\projects2;

use GO\Base\Exception\AccessDenied;
use GO\Base\Model\Acl;
use GO\Projects2\Controller\IncomeController;
use GO\Projects2\Model\Income;
use GO\Projects2\Model\Project;
use GO\Projects2\Projects2Module;
use go\core\model\User;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/fixtures/ProjectsTrait.php';

/**
 * Income is financial data, so users need the finance permission on the project. Read permission on the project is
 * not enough.
 */
class IncomePermissionTest extends TestCase
{
	use ProjectsTrait;

	private Income $readIncome;
	private Income $financeIncome;

	protected function setUp(): void
	{
		$this->setUpProjects();

		$this->readIncome = $this->createIncome($this->createProject($this->createType(Acl::READ_PERMISSION)));
		$this->financeIncome = $this->createIncome($this->createProject($this->createType(Projects2Module::FINANCE_PERMISSIONS)));
	}

	protected function tearDown(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		\GO::$ignoreAclPermissions = true;
		foreach ([$this->readIncome ?? null, $this->financeIncome ?? null] as $income) {
			$income?->delete(true);
		}
		\GO::$ignoreAclPermissions = $this->ignoreAcl;
		$this->tearDownProjects();
	}

	private function createIncome(Project $project): Income
	{
		$income = new Income();
		$income->project_id = $project->id;
		$income->description = 'test income';
		$income->amount = 100;
		$income->invoice_at = time();
		$income->type = Income::TYPE_CONTRACT_PRICE;
		$this->assertTrue($income->save(true), var_export($income->getValidationErrors(), true));
		return $income;
	}

	public function testBillingInvoiceNeedsFinancePermission(): void
	{
		// billingInvoice ignores the ACL for the orders it creates
		$this->actAs($this->user->id);
		$old = \GO::setIgnoreAclPermissions(true);
		try {
			$method = new \ReflectionMethod(IncomeController::class, 'actionBillingInvoice');
			ob_start();
			try {
				$method->invoke(new IncomeController(), [
					'income_ids' => json_encode([$this->readIncome->id]),
					'book_id' => 0,
					'item_template' => '{project_name}'
				]);
			} finally {
				ob_end_clean();
			}
			$this->fail('An invoice was made of income the user may not see');
		} catch (AccessDenied $e) {
			$this->assertTrue(true);
		} finally {
			\GO::setIgnoreAclPermissions($old);
			$this->actAs(User::ID_SUPER_ADMIN);
		}
	}
}
