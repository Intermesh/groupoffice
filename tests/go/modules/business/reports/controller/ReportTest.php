<?php
namespace go\modules\business\reports\controller;

use go\core\auth\TemporaryState;
use go\core\http\Exception;
use go\core\model\Acl;
use go\core\model\Group;
use go\core\model\Module;
use go\core\model\Permission;
use go\core\model\User;
use go\modules\business\reports\model\Report as ReportModel;
use PHPUnit\Framework\TestCase;

/**
 * Report/saveLayout stores the default column layout of a report. Only report managers with write permission may do
 * that and it may only change the layout of the report.
 */
class ReportTest extends TestCase
{
	private $authState;

	private Group $managerGroup;
	private User $manager;
	private User $reader;
	private User $user;
	private ReportModel $report;

	protected function setUp(): void
	{
		$mod = \go\modules\business\reports\Module::get();
		// The reports module requires a groupoffice-pro license and is not installed by the test bootstrap
		if (!$mod->isInstalled() || !$mod->isLicensed()) {
			$this->markTestSkipped("The licensed reports module is not installed");
		}

		$this->authState = go()->getAuthState();
		$this->actAs(User::ID_SUPER_ADMIN);

		$this->manager = $this->newUser();
		$this->reader = $this->newUser();
		$this->user = $this->newUser();

		// The manager and reader may manage reports, the plain user may only use them
		$this->managerGroup = new Group();
		$this->managerGroup->name = uniqid('reportmanagers');
		$this->managerGroup->users = [$this->manager->id, $this->reader->id];
		$this->assertTrue($this->managerGroup->save());

		$model = $mod->getModel();
		$permission = new Permission($model);
		$permission->setRights(['mayManage' => true]);
		$model->permissions[$this->managerGroup->id] = $permission;
		$this->assertTrue($model->save());

		$this->report = new ReportModel();
		$this->report->setValues([
			'name' => uniqid('layouttest'),
			'fromEntity' => 'Contact',
			'fields' => [
				['label' => 'Name', 'sourceField' => 'name', 'hideable' => true],
				['label' => 'Id', 'sourceField' => 'id', 'hideable' => false],
				['label' => 'Template', 'sourceField' => null, 'template' => '{{entity.name}}', 'sortType' => ''],
				['label' => 'Sorted template', 'sourceField' => null, 'template' => '{{entity.id}}', 'sortType' => 'number'],
			],
			'acl' => [
				$this->manager->getPersonalGroup()->id => Acl::LEVEL_WRITE,
				$this->reader->getPersonalGroup()->id => Acl::LEVEL_READ,
				$this->user->getPersonalGroup()->id => Acl::LEVEL_WRITE,
			]
		]);
		$this->assertTrue($this->report->save(), $this->report->getValidationErrorsAsString());
	}

	protected function tearDown(): void
	{
		if (!isset($this->authState)) {
			// skipped
			return;
		}
		$this->actAs(User::ID_SUPER_ADMIN);

		ReportModel::delete(['id' => $this->report->id]);

		$model = Module::findByName('business', 'reports');
		unset($model->permissions[$this->managerGroup->id]);
		$model->save();

		Group::delete(['id' => $this->managerGroup->id]);
		User::delete(['id' => [$this->manager->id, $this->reader->id, $this->user->id]]);

		go()->setAuthState($this->authState);
	}

	private function newUser(): User
	{
		$name = uniqid('reportuser');
		$user = new User();
		$user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($user->save(), var_export($user->getValidationErrors(), true));

		return $user;
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
	}

	private function saveLayout(int $actingUserId, array $params): array
	{
		$this->actAs($actingUserId);
		return (new Report())->saveLayout(array_merge(['id' => $this->report->id], $params))->getArray();
	}

	private function reload(): ReportModel
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		return ReportModel::findById($this->report->id);
	}

	private function layout(): array
	{
		return [
			'columns' => [
				['field' => 3, 'hidden' => false, 'width' => 120, 'frozen' => true],
				['field' => 0, 'hidden' => true, 'width' => 250.6, 'frozen' => false],
				['field' => 1, 'hidden' => true, 'width' => 80, 'frozen' => false],
				['field' => 2, 'hidden' => false, 'width' => null, 'frozen' => false],
			],
			'sort' => ['field' => 3, 'isAscending' => false]
		];
	}

	public function testSaveLayout(): void
	{
		$before = $this->reload();

		$result = $this->saveLayout($this->manager->id, $this->layout());
		$this->assertTrue($result['changed']);

		$report = $this->reload();
		$fields = $report->fields;

		$this->assertSame([1, 2, 3, 0], array_map(fn($f) => $f->layoutPosition, $fields));
		$this->assertSame([251, 80, null, 120], array_map(fn($f) => $f->width, $fields));
		$this->assertSame([false, false, false, true], array_map(fn($f) => $f->frozen, $fields));
		$this->assertSame(['', '', '', 'desc'], array_map(fn($f) => $f->sortDirection, $fields));

		// Hidden columns stay in the report. A column that is not hideable can not be hidden.
		$this->assertCount(4, $fields);
		$this->assertSame([true, false, false, false], array_map(fn($f) => $f->hidden, $fields));

		// The report itself is unchanged
		$this->assertSame(
			array_map(fn($f) => [$f->label, $f->sourceField, $f->template, $f->sortType, $f->hideable], $before->fields),
			array_map(fn($f) => [$f->label, $f->sourceField, $f->template, $f->sortType, $f->hideable], $fields)
		);
		$this->assertSame($before->name, $report->name);
		$this->assertSame($before->findAclId(), $report->findAclId());

		$this->assertEquals($this->manager->id, $report->modifiedBy);
		$this->assertNotNull($report->modifiedAt);

		// Saving the same layout again changes nothing
		$result = $this->saveLayout($this->manager->id, $this->layout());
		$this->assertFalse($result['changed']);
	}

	public function testSortOnlyOnSortableColumn(): void
	{
		$layout = $this->layout();
		// A template column without a sort type is not sortable
		$layout['sort'] = ['field' => 2, 'isAscending' => true];
		$this->saveLayout($this->manager->id, $layout);

		$fields = $this->reload()->fields;
		$this->assertSame(['', '', '', ''], array_map(fn($f) => $f->sortDirection, $fields));

		$layout['sort'] = ['field' => 0, 'isAscending' => true];
		$this->saveLayout($this->manager->id, $layout);
		$fields = $this->reload()->fields;
		$this->assertSame(['asc', '', '', ''], array_map(fn($f) => $f->sortDirection, $fields));
	}

	public function testLayoutIsNormalizedOnSet(): void
	{
		$this->actAs($this->manager->id);
		$fields = array_map(fn($f) => $f->toArray(), $this->report->fields);
		$fields[1]['hidden'] = true; // not hideable
		$fields[2]['sortDirection'] = 'asc'; // template without sort type
		$fields[3]['sortDirection'] = 'desc';
		$fields[0]['sortDirection'] = 'asc'; // second sort
		$result = (new Report())->set(['update' => [$this->report->id => ['fields' => $fields]]])->getArray();
		$this->assertArrayHasKey($this->report->id, $result['updated'] ?? [], var_export($result['notUpdated'] ?? null, true));

		$fields = $this->reload()->fields;
		$this->assertSame([false, false, false, false], array_map(fn($f) => $f->hidden, $fields));
		$this->assertSame(['asc', '', '', ''], array_map(fn($f) => $f->sortDirection, $fields));
	}

	public function testMissingColumnsAreKept(): void
	{
		$this->saveLayout($this->manager->id, [
			'columns' => [
				['field' => 2, 'hidden' => false],
				['field' => 0, 'hidden' => false],
			]
		]);

		$fields = $this->reload()->fields;
		$this->assertCount(4, $fields);
		$this->assertSame([1, 2, 0, 3], array_map(fn($f) => $f->layoutPosition, $fields));
	}

	public function testInvalidField(): void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionCode(412);
		$this->saveLayout($this->manager->id, ['columns' => [['field' => 99]]]);
	}

	public function testUserThatMayNotManageReportsIsForbidden(): void
	{
		try {
			$this->saveLayout($this->user->id, $this->layout());
			$this->fail("A user without the manage right could save the layout");
		} catch (Exception $e) {
			$this->assertSame(403, $e->getCode());
		}
		$this->assertSame([null, null, null, null], array_map(fn($f) => $f->layoutPosition, $this->reload()->fields));
	}

	public function testManagerWithoutWritePermissionIsForbidden(): void
	{
		try {
			$this->saveLayout($this->reader->id, $this->layout());
			$this->fail("A manager with only read permission could save the layout");
		} catch (Exception $e) {
			$this->assertSame(403, $e->getCode());
		}
		$this->assertSame([null, null, null, null], array_map(fn($f) => $f->layoutPosition, $this->reload()->fields));
	}

	public static function readMethodProvider(): array
	{
		return [
			'loadTemplates' => ['loadTemplates', ['entityIds' => []]],
			'sortedIds' => ['sortedIds', ['field' => 'template_0_3']],
			'export' => ['export', ['extension' => 'csv']],
			'summary' => ['summary', []],
		];
	}

	/**
	 * Template columns can show data of other entities (e.g. hours of employees), so reading report data requires
	 * read permission on the report.
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider('readMethodProvider')]
	public function testReadingDataRequiresReadPermission(string $method, array $params): void
	{
		$outsider = $this->newUser();
		try {
			$this->actAs($outsider->id);
			try {
				(new Report())->$method(array_merge(['id' => $this->report->id], $params));
				$this->fail("A user without read permission on the report could call Report/" . $method);
			} catch (Exception $e) {
				$this->assertSame(403, $e->getCode());
			}

			// with read permission it works
			$this->actAs($this->reader->id);
			$result = (new Report())->loadTemplates(['id' => $this->report->id, 'entityIds' => []])->getArray();
			$this->assertTrue($result['success']);
		} finally {
			$this->actAs(User::ID_SUPER_ADMIN);
			User::delete(['id' => $outsider->id]);
		}
	}
}
