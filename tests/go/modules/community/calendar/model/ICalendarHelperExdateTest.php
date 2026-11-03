<?php

namespace go\modules\community\calendar\model;

use go\core\auth\TemporaryState;
use PHPUnit\Framework\TestCase;

/**
 * EXDATE values from foreign clients must not break the import
 */
class ICalendarHelperExdateTest extends TestCase
{
	protected function setUp(): void
	{
		go()->setAuthState(new TemporaryState(1));
	}

	private function ics(string $exdate): string
	{
		return "BEGIN:VCALENDAR\r\n" .
			"PRODID:QIS-LSF HIS eG\r\n" .
			"VERSION:2.0\r\n" .
			"METHOD:PUBLISH\r\n" .
			"BEGIN:VEVENT\r\n" .
			"DTSTART;TZID=Europe/Berlin:20261019T083000\r\n" .
			"DTEND;TZID=Europe/Berlin:20261019T110000\r\n" .
			"RRULE:FREQ=WEEKLY;UNTIL=20270125T235900Z;INTERVAL=1;BYDAY=MO\r\n" .
			$exdate . "\r\n" .
			"DTSTAMP:20261030T153001Z\r\n" .
			"UID:76835116713\r\n" .
			"SUMMARY:5104026 - Tierbestimmungsübungen\r\n" .
			"END:VEVENT\r\n" .
			"END:VCALENDAR\r\n";
	}

	public function testEmptyExdateIsIgnored(): void
	{
		$event = ICalendarHelper::parseVObject($this->ics("EXDATE;TZID=Europe/Berlin:"), new CalendarEvent());
		$this->assertTrue($event->isRecurring());
		$this->assertEmpty($event->recurrenceOverrides);
	}

	public function testMultipleExdateValues(): void
	{
		$event = ICalendarHelper::parseVObject($this->ics("EXDATE;TZID=Europe/Berlin:20261026T083000,20261102T083000"), new CalendarEvent());
		$this->assertArrayHasKey('2026-10-26T08:30:00', $event->recurrenceOverrides);
		$this->assertArrayHasKey('2026-11-02T08:30:00', $event->recurrenceOverrides);
		$this->assertTrue((bool)$event->recurrenceOverrides['2026-11-02T08:30:00']->excluded);
	}
}
