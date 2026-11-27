<?php

namespace go\core\model;

use go\core\util\DateTime;
use PHPUnit\Framework\TestCase;

class CronJobScheduleTest extends TestCase
{
	public static function nextRunAtProvider(): array
	{
		return [
			'on the minute stays' => ['2026-11-28 04:00:00', '2026-11-28 04:00:00'],
			'seconds round up' => ['2026-11-28 04:00:01', '2026-11-28 04:01:00'],
			'last second rounds up' => ['2026-11-28 04:00:59', '2026-11-28 04:01:00'],
		];
	}

	/**
	 * A next run time on the minute must not move a minute later, like "0 4 * * *" running at 04:01 #38392
	 *
	 * @dataProvider nextRunAtProvider
	 */
	public function testNextRunAtRoundsUpToMinute(string $nextRunAt, string $expected): void
	{
		$job = CronJobSchedule::findByName("GarbageCollection");
		$this->assertNotNull($job);

		$debugger = go()->getDebugger();
		$wasEnabled = $debugger->enabled;
		$debugger->enabled = false;
		try {
			$job->nextRunAt = new DateTime($nextRunAt, new \DateTimeZone("UTC"));
			$this->assertTrue($job->validate());
			$this->assertEquals($expected, $job->nextRunAt->format("Y-m-d H:i:s"));
		} finally {
			$debugger->enabled = $wasEnabled;
		}
		// The job is not saved, so the database stays as it was
	}
}
