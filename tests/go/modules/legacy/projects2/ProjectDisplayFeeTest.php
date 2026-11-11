<?php
namespace go\modules\legacy\projects2;

use GO\Base\Model\Acl;
use GO\Projects2\Controller\ProjectController;
use GO\Projects2\Model\Project;
use GO\Projects2\Projects2Module;
use go\core\model\User;
use go\modules\business\ModuleTablesTrait;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/fixtures/ProjectsTrait.php';
require_once __DIR__ . '/../../business/fixtures/ModuleTablesTrait.php';

/**
 * The display panel of a project shows the time per activity. The internal and external fees in it are financial
 * data, so they are only sent to users with the finance permission on the project.
 */
class ProjectDisplayFeeTest extends TestCase
{
	use ProjectsTrait;
	use ModuleTablesTrait;

	private array $hourIds = [];

	protected function setUp(): void
	{
		$this->setUpProjects();
		// The display panel joins the activities and employees of the business module
		$this->createTables(go()->getEnvironment()->getInstallPath() . '/go/modules/business/business/install/install.sql', ['business_activity', 'business_employee']);
	}

	protected function tearDown(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		if (!empty($this->hourIds)) {
			go()->getDbConnection()->delete('pr2_hours', ['id' => $this->hourIds])->execute();
		}
		$this->tearDownProjects();
		$this->uninstallModuleTables();
	}

	private function display(int $level): array
	{
		$project = $this->createProject($this->createType($level));

		go()->getDbConnection()->insert('pr2_hours', [
			'user_id' => User::ID_SUPER_ADMIN,
			'project_id' => $project->id,
			'duration' => 60,
			'units' => 1,
			'external_fee' => 100,
			'internal_fee' => 50,
			'date' => time()
		])->execute();
		$this->hourIds[] = (int) go()->getDbConnection()->getPDO()->lastInsertId();

		$this->actAs($this->user->id);
		$method = new \ReflectionMethod(ProjectController::class, 'actionDisplay');
		ob_start();
		try {
			$method->invoke(new ProjectController(), ['id' => $project->id]);
		} finally {
			$output = ob_get_clean();
			$this->actAs(User::ID_SUPER_ADMIN);
		}

		$response = json_decode($output, true);
		$this->assertIsArray($response, $output);
		$this->assertNotEmpty($response['data']['peractivity']);
		return $response['data'];
	}

	public function testFeesAreHiddenWithoutFinancePermission(): void
	{
		$data = $this->display(Acl::READ_PERMISSION);

		$this->assertFalse($data['finance_enabled']);
		foreach ($data['peractivity'] as $activity) {
			$this->assertArrayNotHasKey('external_fee', $activity);
			$this->assertArrayNotHasKey('internal_fee', $activity);
			$this->assertEquals(60, $activity['time']);
		}
	}

	public function testFeesAreShownWithFinancePermission(): void
	{
		$data = $this->display(Projects2Module::FINANCE_PERMISSIONS);

		$this->assertTrue($data['finance_enabled']);
		$this->assertEquals(100, $data['peractivity'][0]['external_fee']);
		$this->assertEquals(50, $data['peractivity'][0]['internal_fee']);
	}
}
