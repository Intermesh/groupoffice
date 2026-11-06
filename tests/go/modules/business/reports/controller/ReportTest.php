<?php
namespace go\modules\business\reports\controller;

use go\core\auth\TemporaryState;
use go\core\fs\Blob;
use go\core\fs\File;
use go\core\http\Exception;
use go\core\model\Acl;
use go\core\model\Group;
use go\core\model\Module;
use go\core\model\Permission;
use go\core\model\User;
use go\modules\business\reports\model\Report as ReportModel;
use go\modules\business\reports\model\ReportFilter;
use go\modules\community\addressbook\model\Contact;
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
	/**
	 * Prefix of the names of reports created by the import tests, deleted in tearDown
	 */
	private string $importPrefix;
	/**
	 * Groups that got access to the module in a test, removed in tearDown
	 */
	private array $accessGroupIds = [];

	protected function setUp(): void
	{
		$mod = \go\modules\business\reports\Module::get();
		// The reports module requires a groupoffice-pro license and is not installed by the test bootstrap
		if (!$mod->isInstalled() || !$mod->isLicensed()) {
			$this->markTestSkipped("The licensed reports module is not installed");
		}

		$this->authState = go()->getAuthState();
		$this->actAs(User::ID_SUPER_ADMIN);
		$this->importPrefix = uniqid('importtest');

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
		$importedIds = ReportModel::find()->selectSingleValue('susreport.id')
			->where('name', 'LIKE', $this->importPrefix . '%')->all();
		if (!empty($importedIds)) {
			ReportModel::delete(['id' => $importedIds]);
		}

		$model = Module::findByName('business', 'reports');
		unset($model->permissions[$this->managerGroup->id]);
		foreach ($this->accessGroupIds as $groupId) {
			unset($model->permissions[$groupId]);
		}
		$model->save();

		Group::delete(['id' => $this->managerGroup->id]);
		$this->deleteUsers([$this->manager->id, $this->reader->id, $this->user->id]);

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

	/**
	 * Delete users and their profile contacts, which are kept when a user is deleted
	 */
	private function deleteUsers(array $ids): void
	{
		if (\go\core\model\Module::isInstalled('community', 'addressbook')) {
			Contact::delete(['goUserId' => $ids]);
		}
		User::delete(['id' => $ids]);
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
			'exportData' => ['exportData', ['extension' => 'csv']],
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
			$this->deleteUsers([$outsider->id]);
		}
	}

	private function addFilters(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		foreach ([
			['name' => 'manager', 'type' => 'variable', 'filter' => null],
			['name' => 'Smith', 'type' => 'fixed', 'filter' => '{"operator":"AND","conditions":[{"name":"%Smith%"}]}'],
		] as $values) {
			$filter = new ReportFilter();
			$filter->setValues($values);
			$filter->reportId = $this->report->id;
			$this->assertTrue($filter->save(), $filter->getValidationErrorsAsString());
		}
	}

	/**
	 * Give a user access to the module without the right to manage reports
	 */
	private function grantModuleAccess(User $user): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		$groupId = $user->getPersonalGroup()->id;
		$model = \go\modules\business\reports\Module::get()->getModel();
		$model->permissions[$groupId] = new Permission($model);
		$this->assertTrue($model->save());
		$this->accessGroupIds[] = $groupId;
	}

	/**
	 * Report/export as JSON
	 */
	private function exportJson(int $actingUserId, array $ids): array
	{
		$this->actAs($actingUserId);
		$result = (new Report())->export(['ids' => $ids, 'extension' => 'json'])->getArray();
		$data = json_decode(Blob::findById($result['blob']['id'])->getFile()->getContents(), true);
		$this->assertIsArray($data, "Export is not valid JSON");
		return $data;
	}

	/**
	 * Import with the JSON converter like the background process of Report/import does
	 */
	private function importJson(int $actingUserId, array $data): array
	{
		$file = File::tempFile('json');
		$file->putContents(json_encode($data));
		$this->actAs($actingUserId);
		return ReportModel::findConverter('json')->importFile($file);
	}

	private function findImported(string $name): ?ReportModel
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		return ReportModel::find()->where(['name' => $name])->single();
	}

	private function filtersOf(string $reportId): array
	{
		return array_map(
			fn(ReportFilter $f) => [$f->name, $f->type, $f->filter],
			ReportFilter::find()->where(['reportId' => $reportId])->orderBy(['id' => 'ASC'])->all()
		);
	}

	private function fieldValues(ReportModel $report): array
	{
		return array_map(fn($f) => [$f->label, $f->sourceField, $f->template, $f->sortType, $f->hideable,
			$f->layoutPosition, $f->hidden, $f->width, $f->sortDirection, $f->frozen], $report->fields);
	}

	public function testExportImport(): void
	{
		$this->addFilters();
		$this->saveLayout($this->manager->id, $this->layout());

		$data = $this->exportJson($this->reader->id, [$this->report->id]);
		$this->assertCount(1, $data);
		$exported = $data[0];
		foreach (['id', 'acl', 'entityTypeId', 'entityIds', 'entityCls', 'createdBy', 'reportFilters'] as $prop) {
			$this->assertArrayNotHasKey($prop, $exported);
		}
		$this->assertArrayNotHasKey('reportId', $exported['fields'][0]);
		$this->assertArrayNotHasKey('templateValue', $exported['fields'][0]);
		$this->assertSame('Contact', $exported['fromEntity']);
		$this->assertCount(2, $exported['filters']);

		$exported['name'] = $this->importPrefix . 'copy';
		// The manager is no admin, so this also proves report managers may create reports
		$result = $this->importJson($this->manager->id, [$exported]);
		$this->assertSame([], $result['errors']);
		$this->assertSame(1, $result['count']);

		$original = $this->reload();
		$imported = $this->findImported($this->importPrefix . 'copy');
		$this->assertNotNull($imported);
		$this->assertNotEquals($original->id, $imported->id);
		$this->assertNotEquals($original->findAclId(), $imported->findAclId());
		$this->assertSame($original->entityTypeId, $imported->entityTypeId);
		$this->assertSame($this->fieldValues($original), $this->fieldValues($imported));
		$this->assertSame($this->filtersOf($original->id), $this->filtersOf($imported->id));

		// The importer owns the new report, the users that had access to the original report don't
		$this->assertEquals($this->manager->id, $imported->createdBy);
		$this->actAs($this->manager->id);
		$this->assertSame(Acl::LEVEL_MANAGE, ReportModel::findById($imported->id)->getPermissionLevel());
		$this->actAs($this->user->id);
		$this->assertFalse(ReportModel::findById($imported->id)->hasPermissionLevel(Acl::LEVEL_READ));
	}

	public function testImportIgnoresIdsAndPermissions(): void
	{
		$data = $this->exportJson($this->manager->id, [$this->report->id]);
		$data[0] = array_merge($data[0], [
			'name' => $this->importPrefix . 'ids',
			'id' => $this->report->id,
			'acl' => [$this->user->getPersonalGroup()->id => Acl::LEVEL_MANAGE],
			'createdBy' => $this->user->id,
			'entityTypeId' => 1,
		]);
		$data[0]['fields'][0]['reportId'] = $this->report->id;

		$result = $this->importJson($this->manager->id, $data);
		$this->assertSame(1, $result['count'], implode("\n", $result['errors']));

		$imported = $this->findImported($this->importPrefix . 'ids');
		$this->assertNotEquals($this->report->id, $imported->id);
		$this->assertNotEquals($this->report->findAclId(), $imported->findAclId());
		$this->assertArrayNotHasKey($this->user->getPersonalGroup()->id, $imported->getAcl()->getArray());
		$this->assertEquals($this->manager->id, $imported->createdBy);
		$this->assertSame($this->report->entityTypeId, $imported->entityTypeId);
		// the original report keeps its fields
		$this->assertCount(4, $this->reload()->fields);

		// An ACL id can't be set at all, so such a report is refused
		$data[0]['name'] = $this->importPrefix . 'aclid';
		$data[0]['aclId'] = $this->report->findAclId();
		$result = $this->importJson($this->manager->id, $data);
		$this->assertSame(0, $result['count']);
		$this->assertNull($this->findImported($this->importPrefix . 'aclid'));
	}

	/**
	 * A user that may use the module, but not manage reports, may not create or import reports. Without module access
	 * the controller refuses everything, so give access to make sure the create check itself fires.
	 */
	public function testUserThatMayNotManageReportsCantCreate(): void
	{
		$this->grantModuleAccess($this->user);

		$this->actAs($this->user->id);
		$result = (new Report())->set(['create' => ['new' => [
			'name' => $this->importPrefix . 'set', 'fromEntity' => 'Contact', 'fields' => []
		]]])->getArray();
		$this->assertArrayHasKey('new', $result['notCreated'] ?? [], "A user that may not manage reports created one");

		// with the right it works
		$this->actAs($this->manager->id);
		$result = (new Report())->set(['create' => ['new' => [
			'name' => $this->importPrefix . 'set', 'fromEntity' => 'Contact', 'fields' => []
		]]])->getArray();
		$this->assertArrayHasKey('new', $result['created'] ?? [], var_export($result['notCreated'] ?? null, true));
	}

	public function testUserThatMayNotManageReportsCantImport(): void
	{
		$data = $this->exportJson($this->manager->id, [$this->report->id]);
		$data[0]['name'] = $this->importPrefix . 'denied';
		$this->grantModuleAccess($this->user);

		// Report/import refuses before it starts the background import
		$blob = Blob::fromString(json_encode($data));
		$blob->name = 'report.json';
		$blob->type = 'application/json';
		$this->assertTrue($blob->save());
		$this->actAs($this->user->id);
		try {
			(new Report())->import(['blobId' => $blob->id]);
			$this->fail("A user without the manage right could import a report");
		} catch (Exception $e) {
			$this->assertSame(403, $e->getCode());
		}

		// and the import itself checks each report
		$result = $this->importJson($this->user->id, $data);
		$this->assertSame(0, $result['count']);
		$this->assertCount(1, $result['errors']);
		$this->assertNull($this->findImported($this->importPrefix . 'denied'));
	}

	/**
	 * The outsider may use the module, but has no permission on the report
	 */
	public function testExportSkipsReportsWithoutReadPermission(): void
	{
		$outsider = $this->newUser();
		$this->grantModuleAccess($outsider);
		try {
			$this->assertSame([], $this->exportJson($outsider->id, [$this->report->id]));
		} finally {
			$this->actAs(User::ID_SUPER_ADMIN);
			$this->deleteUsers([$outsider->id]);
		}
	}

	public static function invalidReportProvider(): array
	{
		return [
			'unknown entity' => [['fromEntity' => 'DoesNotExist']],
			'invalid summary type' => [['fields' => [['label' => 'x', 'sourceField' => 'name', 'summaryType' => 'drop']]]],
			'invalid filter type' => [['filters' => [['name' => 'x', 'type' => 'other']]]],
			'invalid filters' => [['filters' => 'x']],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('invalidReportProvider')]
	public function testInvalidReportIsReported(array $values): void
	{
		$report = array_merge(
			['name' => $this->importPrefix . 'invalid', 'fromEntity' => 'Contact', 'fields' => [], 'filters' => []],
			$values
		);
		$valid = ['name' => $this->importPrefix . 'valid', 'fromEntity' => 'Contact', 'fields' => [], 'filters' => []];

		$result = $this->importJson($this->manager->id, [$report, $valid]);

		$this->assertSame(1, $result['count']);
		$this->assertCount(1, $result['errors']);
		$this->assertMatchesRegularExpression('/item 0:/i', $result['errors'][0]);
		$this->assertNull($this->findImported($this->importPrefix . 'invalid'));
		$this->assertNotNull($this->findImported($this->importPrefix . 'valid'));
	}
}
