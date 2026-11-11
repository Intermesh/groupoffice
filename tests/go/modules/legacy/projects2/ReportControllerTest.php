<?php
namespace go\modules\legacy\projects2;

use GO\Base\Exception\AccessDenied;
use GO\Base\Model\Acl;
use GO\Projects2\Controller\ReportController;
use GO\Projects2\Model\Project;
use GO\Projects2\Projects2Module;
use GO\Projects2\Report\Planning;
use GO\Projects2\Report\TimeTrackingReport;
use go\core\model\User;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/fixtures/ProjectsTrait.php';

/**
 * A class that isn't a report but takes any constructor arguments
 */
class ReportControllerTestGadget
{
	public static bool $created = false;

	public function __construct(...$args)
	{
		self::$created = true;
	}
}

/**
 * Reports are created from a class name that the client sends, and they read hours, income and expenses of projects
 * without checking the ACL. They write the report to a folder that the client sends.
 */
class ReportControllerTest extends TestCase
{
	use ProjectsTrait;

	private Project $readProject;
	private Project $financeProject;
	private Project $parentProject;

	protected function setUp(): void
	{
		$this->setUpProjects();

		$readType = $this->createType(Acl::READ_PERMISSION);
		$financeType = $this->createType(Projects2Module::FINANCE_PERMISSIONS);

		$this->readProject = $this->createProject($readType);
		$this->financeProject = $this->createProject($financeType);
		$this->parentProject = $this->createProject($financeType);
		// the user may not see the finances of this sub project
		$this->createProject($readType, $this->parentProject);
	}

	protected function tearDown(): void
	{
		unset($_REQUEST['report_template_class']);
		$this->tearDownProjects();
	}

	/**
	 * Run a protected action of the report controller as the user
	 */
	private function runAction(string $action, array $params)
	{
		$this->actAs($this->user->id);
		$method = new \ReflectionMethod(ReportController::class, $action);
		ob_start();
		try {
			return $method->invoke(new ReportController(), $params);
		} finally {
			ob_end_clean();
			$this->actAs(User::ID_SUPER_ADMIN);
		}
	}

	private function checkPermission(string $reportClass, ?Project $project): bool
	{
		$this->actAs($this->user->id);
		$method = new \ReflectionMethod(ReportController::class, 'checkReportPermission');
		try {
			$method->invoke(new ReportController(), new $reportClass(), $project);
			return true;
		} catch (AccessDenied $e) {
			return false;
		} finally {
			$this->actAs(User::ID_SUPER_ADMIN);
		}
	}

	public function testReportClasses(): void
	{
		$this->assertTrue(\GO\Projects2\Report\AbstractReport::isReportClass(Planning::class));
		$this->assertFalse(\GO\Projects2\Report\AbstractReport::isReportClass(\GO\Projects2\Report\AbstractReport::class));
		$this->assertFalse(\GO\Projects2\Report\AbstractReport::isReportClass(ReportControllerTestGadget::class));
		$this->assertFalse(\GO\Projects2\Report\AbstractReport::isReportClass('NoSuchClass'));
	}

	public static function actions(): array
	{
		return [['actionFilters'], ['actionPrint'], ['actionSubmit']];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('actions')]
	public function testOnlyReportClassesAreCreated(string $action): void
	{
		ReportControllerTestGadget::$created = false;
		try {
			$this->runAction($action, ['report_template_class' => ReportControllerTestGadget::class, 'path' => 'x', 'filename' => 'x']);
			$this->fail('Created ' . ReportControllerTestGadget::class);
		} catch (AccessDenied $e) {
		}
		$this->assertFalse(ReportControllerTestGadget::$created);
	}

	public function testProjectReportNeedsFinancePermission(): void
	{
		$this->expectException(AccessDenied::class);
		$this->runAction('actionPrint', [
			'report_template_class' => TimeTrackingReport::class,
			'project_id' => $this->readProject->id,
			'filename' => 'test'
		]);
	}

	public function testReportPermissions(): void
	{
		$this->assertTrue($this->checkPermission(TimeTrackingReport::class, $this->financeProject));
		$this->assertFalse($this->checkPermission(TimeTrackingReport::class, $this->readProject));
		$this->assertFalse($this->checkPermission(TimeTrackingReport::class, $this->parentProject), "A sub project without finance permission is in the report");
		// the planning shows no finances
		$this->assertTrue($this->checkPermission(Planning::class, $this->readProject));
	}

	public function testReportOfAllProjectsNeedsModuleManagePermission(): void
	{
		$this->expectException(AccessDenied::class);
		$this->runAction('actionPrint', [
			'report_template_class' => TimeTrackingReport::class,
			'filename' => 'test'
		]);
	}

	public function testSubmitNeedsWritableFolder(): void
	{
		$adminHome = \GO\Files\Model\Folder::model()->findHomeFolder(\GO\Base\Model\User::model()->findByPk(User::ID_SUPER_ADMIN, false, true));
		$reportFile = $adminHome->fsFolder->path() . '/pr2reporttest.pdf';

		try {
			$this->runAction('actionSubmit', [
				'report_template_class' => Planning::class,
				'project_id' => $this->readProject->id,
				'path' => $adminHome->path,
				'filename' => 'pr2reporttest',
				'filters' => '[]'
			]);
			$this->fail('The report was saved in the folder of another user');
		} catch (AccessDenied $e) {
		} finally {
			$written = file_exists($reportFile);
			if ($written) {
				unlink($reportFile);
			}
		}
		$this->assertFalse($written, 'The report was written in the folder of another user');
	}
}
