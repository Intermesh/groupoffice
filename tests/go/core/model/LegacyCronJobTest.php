<?php
namespace go\core\model;

use GO\Base\Cron\AbstractCron;
use GO\Files\Cron\NotifyFolderChangesJob;
use PHPUnit\Framework\TestCase;

/**
 * An old job that records that it ran
 */
class LegacyCronJobTestOldJob extends AbstractCron
{
	public static bool $ran = false;

	public function enableUserAndGroupSupport()
	{
		return false;
	}

	public function getLabel()
	{
		return 'Old test job';
	}

	public function getDescription()
	{
		return '';
	}

	public function run(\GO\Base\Cron\CronJob $cronJob)
	{
		self::$ran = true;
	}
}

/**
 * The jobs of the old cron system run in the new scheduler through a LegacyCronJob, and their go_cron schedules move
 * to the new scheduler on upgrade.
 */
class LegacyCronJobTest extends TestCase
{
	private array $oldIds = [];

	protected function tearDown(): void
	{
		$this->deleteSchedule();
		if ($this->oldIds) {
			go()->getDbConnection()->delete('go_cron', ['id' => $this->oldIds])->execute();
		}
	}

	private function deleteSchedule(): void
	{
		$schedule = CronJobSchedule::findByName('NotifyFolderChangesJob', 'legacy', 'files');
		if ($schedule) {
			CronJobSchedule::delete(['id' => $schedule->id]);
		}
	}

	private function insertOld(array $values): void
	{
		go()->getDbConnection()->insert('go_cron', array_merge([
			'name' => 'Notify folder changes',
			'active' => 0,
			'minutes' => '15',
			'hours' => '3',
			'monthdays' => '*',
			'months' => '*',
			'weekdays' => '1-5',
			'job' => \GO\Files\Cron\NotifyFolderChanges::class,
		], $values))->execute();
		$this->oldIds[] = go()->getDbConnection()->getPDO()->lastInsertId();
	}

	public function testRunsTheOldJob()
	{
		LegacyCronJobTestOldJob::$ran = false;
		$job = new class extends LegacyCronJob {
			protected static function legacyClass(): string
			{
				return LegacyCronJobTestOldJob::class;
			}
		};

		$schedule = new CronJobSchedule();
		$schedule->name = 'Test';
		$job->run($schedule);

		$this->assertTrue(LegacyCronJobTestOldJob::$ran);
	}

	public function testOldScheduleIsMigrated()
	{
		$this->deleteSchedule();
		$this->insertOld([]);

		$schedule = NotifyFolderChangesJob::migrateLegacySchedule();

		$this->assertNotNull($schedule);
		$this->assertSame('15 3 * * 1-5', $schedule->expression);
		$this->assertTrue($schedule->enabled);
		$this->assertNotEmpty($schedule->description);

		// Running the update again doesn't add another one
		$this->assertNull(NotifyFolderChangesJob::migrateLegacySchedule());
	}

	public function testNothingIsScheduledWithoutAnOldJob()
	{
		$this->deleteSchedule();
		$this->insertOld(['runonce' => 1]);

		$this->assertNull(NotifyFolderChangesJob::migrateLegacySchedule());
		$this->assertNull(CronJobSchedule::findByName('NotifyFolderChangesJob', 'legacy', 'files'));
	}

	public function testInvalidOldScheduleIsSkipped()
	{
		$this->deleteSchedule();
		$this->insertOld(['minutes' => 'every']);

		$this->assertNull(NotifyFolderChangesJob::migrateLegacySchedule());
	}
}
