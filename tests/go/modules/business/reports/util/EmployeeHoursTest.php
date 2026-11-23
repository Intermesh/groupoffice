<?php
namespace go\modules\business\reports\util;

use go\core\auth\TemporaryState;
use go\core\model\Module;
use go\core\model\User;
use go\core\TemplateParser;
use go\modules\business\business\model\EmployeeAgreement;
use go\modules\business\reports\model\Report;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Hours of the time tracking report: registered hours and contract hours (agreement minus bank holidays minus
 * approved leave) in a period.
 */
class EmployeeHoursTest extends TestCase
{
	private const TZ = 'Europe/London';

	private $authState;
	private ?User $user = null;
	private array $projectIds = [];

	public static function filterProvider(): array
	{
		return [
			'flat' => [['fromDate' => '2025-12-01', 'untilDate' => '2025-12-31'], ['2025-12-01', '2025-12-31']],
			'nested like the report UI' => [
				['operator' => 'AND', 'conditions' => [
					['operator' => 'AND', 'conditions' => []],
					['fromDate' => '2025-12-01'],
					['untilDate' => '2025-12-31'],
				]],
				['2025-12-01', '2025-12-31']
			],
			'from missing' => [['untilDate' => '2025-12-31'], null],
			'until before from' => [['fromDate' => '2025-12-31', 'untilDate' => '2025-12-01'], null],
			'invalid date' => [['fromDate' => '2025-12-01 OR 1=1', 'untilDate' => '2025-12-31'], null],
			'no filter' => [null, null],
		];
	}

	#[DataProvider('filterProvider')]
	public function testPeriodFromFilter(mixed $filter, ?array $expected): void
	{
		$this->assertSame($expected, EmployeeHours::periodFromFilter($filter));
	}

	public function testPercentageIsEmptyWithoutContractHours(): void
	{
		$this->assertSame('', EmployeeHours::percentage(600, 0));
		$this->assertSame('50', EmployeeHours::percentage(600, 1200));
		$this->assertSame('33.33', EmployeeHours::percentage(400, 1200));
	}

	private function requireModules(): void
	{
		if (!Module::isInstalled('business', 'reports', true) ||
			!Module::isInstalled('legacy', 'projects2', true) ||
			!Module::isInstalled('legacy', 'leavedays', true)) {
			$this->markTestSkipped("The reports, projects2 and leavedays modules are not installed");
		}

		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));

		$name = uniqid('hourstest');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz',
			'timezone' => self::TZ,
			'holidayset' => 'en_gb',
		]);
		$this->assertTrue($this->user->save(), $this->user->getValidationErrorsAsString());

		$businessId = go()->getDbConnection()->selectSingleValue('id')->from('business_business')->single();
		$this->assertNotFalse($businessId, "A business is needed");
		go()->getDbConnection()->insert('business_employee', ['id' => $this->user->id, 'businessId' => $businessId])->execute();
		EmployeeHours::clearCache();
	}

	protected function tearDown(): void
	{
		if (!isset($this->user)) {
			return;
		}
		$db = go()->getDbConnection();
		$db->delete('ld_leave_days', ['user_id' => $this->user->id])->execute();
		$db->delete('pr2_hours', ['user_id' => $this->user->id])->execute();
		if (!empty($this->projectIds)) {
			$db->delete('pr2_projects', ['id' => $this->projectIds])->execute();
		}
		// cascades to the employee and agreements
		User::delete(['id' => $this->user->id]);
		go()->setAuthState($this->authState);
	}

	private function agreement(string $start, array $minutes): void
	{
		$a = new EmployeeAgreement();
		$a->setValues(array_merge(['employeeId' => $this->user->id, 'start' => $start], $minutes));
		$this->assertTrue($a->save(), $a->getValidationErrorsAsString());
	}

	private function leave(string $first, string $last, float $hours, int $status): void
	{
		$tz = new \DateTimeZone(self::TZ);
		go()->getDbConnection()->insert('ld_leave_days', [
			'user_id' => $this->user->id,
			'first_date' => (new \DateTime($first, $tz))->getTimestamp(),
			'last_date' => (new \DateTime($last, $tz))->getTimestamp(),
			'n_hours' => $hours,
			'status' => $status,
			'ld_credit_type_id' => 0,
		])->execute();
	}

	private function project(string $path, int $templateId): int
	{
		$db = go()->getDbConnection();
		$db->insert('pr2_projects', [
			'name' => basename($path),
			'path' => $path,
			'acl_id' => 0,
			'type_id' => 0,
			'template_id' => $templateId,
		])->execute();
		$id = (int)$db->getPDO()->lastInsertId();
		$this->projectIds[] = $id;
		return $id;
	}

	private function entry(string $dateTime, int $minutes, ?int $projectId, int $status = 1): void
	{
		go()->getDbConnection()->insert('pr2_hours', [
			'user_id' => $this->user->id,
			'date' => (new \DateTime($dateTime, new \DateTimeZone(self::TZ)))->getTimestamp(),
			'duration' => $minutes,
			'project_id' => $projectId,
			'status' => $status,
		])->execute();
	}

	public function testContractMinutes(): void
	{
		$this->requireModules();

		$fullTime = ['mo' => 480, 'tu' => 480, 'we' => 480, 'th' => 480, 'fr' => 480, 'sa' => 0, 'su' => 0];
		$this->agreement('2025-01-01', $fullTime);
		// Second agreement in the period. Saving it finishes the first one at 2025-12-14.
		$this->agreement('2025-12-15', ['mo' => 360, 'tu' => 360, 'we' => 360, 'th' => 360, 'fr' => 0, 'sa' => 0, 'su' => 0]);

		// Agreements only, 1 - 14 Dec: 10 days of 480. 15 - 31 Dec: 11 days mo-th of 360, 25 Dec is a bank holiday
		// (26 Dec as well, but a friday without contract minutes).
		$this->assertSame(10 * 480 + 10 * 360, EmployeeHours::contractMinutes($this->user->id, '2025-12-01', '2025-12-31', 'England'));

		EmployeeHours::clearCache();
		// Leave crossing the start of the period: Fri 28 Nov - Tue 2 Dec, 3 working days. 2 days are in the period.
		$this->leave('2025-11-28', '2025-12-02', 24, \GO\Leavedays\Model\Leaveday::STATUS_APPROVED);
		// Leave inside the period counts completely
		$this->leave('2025-12-22', '2025-12-22', 6, \GO\Leavedays\Model\Leaveday::STATUS_APPROVED);
		// Not approved leave doesn't count
		$this->leave('2025-12-10', '2025-12-10', 8, \GO\Leavedays\Model\Leaveday::STATUS_DISAPPROVED);
		$this->leave('2025-12-11', '2025-12-11', 8, \GO\Leavedays\Model\Leaveday::STATUS_NONE);

		$this->assertSame(10 * 480 + 10 * 360 - 2 * 480 - 360, EmployeeHours::contractMinutes($this->user->id, '2025-12-01', '2025-12-31', 'England'));
		$this->assertSame([
			'agreement' => 10 * 480 + 11 * 360,
			'holidays' => 360, // 25 Dec
			'leave' => 2 * 480 + 360,
			'contract' => 10 * 480 + 10 * 360 - 2 * 480 - 360,
		], EmployeeHours::contractBreakdown($this->user->id, '2025-12-01', '2025-12-31', 'England'));

		// No agreement in this period
		$this->assertSame(0, EmployeeHours::contractMinutes($this->user->id, '2024-01-01', '2024-01-31', 'England'));
	}

	public function testBankHolidaysOfOtherRegionsAreIgnored(): void
	{
		$this->requireModules();
		$this->agreement('2026-01-01', ['mo' => 480, 'tu' => 480, 'we' => 480, 'th' => 480, 'fr' => 480, 'sa' => 0, 'su' => 0]);

		// 31 Aug 2026 is a bank holiday in England only, 3 Aug in Scotland only
		$this->assertSame(0, EmployeeHours::contractMinutes($this->user->id, '2026-08-31', '2026-08-31', 'England'));
		$this->assertSame(480, EmployeeHours::contractMinutes($this->user->id, '2026-08-03', '2026-08-03', 'England'));
		// Without a region only the holidays of the whole country count
		$this->assertSame(480, EmployeeHours::contractMinutes($this->user->id, '2026-08-31', '2026-08-31'));
		// Good friday is in the whole country
		$this->assertSame(0, EmployeeHours::contractMinutes($this->user->id, '2026-04-03', '2026-04-03'));
	}

	public function testRegisteredMinutes(): void
	{
		$this->requireModules();

		$headTemplate = 999999;
		$root = uniqid('HoursTestRoot');
		$rootId = $this->project($root, 1);
		$childId = $this->project($root . '/Child', 1);
		$headId = $this->project($root . '/Child/Head', $headTemplate);
		// Starts with the same text, but is not below the root
		$siblingId = $this->project($root . 'Sibling', 1);

		$this->entry('2025-12-01 09:00', 60, $rootId);
		$this->entry('2025-12-02 09:00', 120, $childId);
		$this->entry('2025-12-03 09:00', 240, $headId);
		$this->entry('2025-12-04 09:00', 30, $siblingId);
		$this->entry('2025-12-05 09:00', 15, null, 0); // open
		$this->entry('2025-12-05 10:00', 1000, $rootId, 2); // disapproved
		$this->entry('2025-11-30 23:30', 1000, $rootId); // before the period
		$this->entry('2026-01-01 00:00', 1000, $rootId); // after the period

		$this->assertSame(60 + 120 + 240 + 30 + 15, EmployeeHours::registeredMinutes($this->user->id, '2025-12-01', '2025-12-31'));
		$this->assertSame(60 + 120 + 240, EmployeeHours::registeredMinutes($this->user->id, '2025-12-01', '2025-12-31', $rootId));
		$this->assertSame(60 + 120, EmployeeHours::registeredMinutes($this->user->id, '2025-12-01', '2025-12-31', $rootId, $headTemplate));
		$this->assertSame(0, EmployeeHours::registeredMinutes($this->user->id, '2025-12-01', '2025-12-31', -1));
	}

	public function testTemplateIsEmptyWithoutContractHours(): void
	{
		$this->requireModules();
		$this->entry('2025-12-01 09:00', 60, null);

		$tpl = '[assign contract = entity.id|contractHours:$filter:England][assign hours = entity.id|registeredHours:$filter]{{hours|percentOf:$contract}}';

		$tp = new TemplateParser();
		(new \ReflectionMethod(Report::class, 'addHoursFilters'))->invoke(null, $tp);
		$tp->addModel('entity', $this->user);
		$tp->addModel('filter', ['fromDate' => '2025-12-01', 'untilDate' => '2025-12-31']);
		$this->assertSame('', $tp->parse($tpl));

		$this->agreement('2025-01-01', ['mo' => 120, 'tu' => 0, 'we' => 0, 'th' => 0, 'fr' => 0, 'sa' => 0, 'su' => 0]);
		EmployeeHours::clearCache();
		// 5 mondays of 2 hours in Dec 2025, 1 hour registered
		$this->assertSame('10', $tp->parse($tpl));

		// no period
		$tp->addModel('filter', []);
		$this->assertSame('', $tp->parse($tpl));
	}
}
