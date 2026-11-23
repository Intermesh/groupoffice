<?php

namespace go\modules\community\calendar\model;

use go\core\auth\TemporaryState;
use go\core\model\User;
use go\core\util\DateTime;
use PHPUnit\Framework\TestCase;

/**
 * A recurrence "until" without time includes that whole day. The web client saved it as a date, which left out the last
 * occurrence of events with a time.
 */
class RecurrenceUntilTest extends TestCase
{
	const TZ = 'Europe/Amsterdam';

	private $authState;
	private static Calendar $calendar;

	public static function setUpBeforeClass(): void
	{
		$authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		try {
			self::$calendar = new Calendar();
			self::$calendar->name = uniqid('until');
			self::assertTrue(self::$calendar->save(), self::$calendar->getValidationErrorsAsString());
		} finally {
			go()->setAuthState($authState);
		}
	}

	public static function tearDownAfterClass(): void
	{
		$authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		if (isset(self::$calendar)) {
			CalendarEvent::delete(['calendarId' => self::$calendar->id]);
			Calendar::delete(['id' => self::$calendar->id]);
		}
		go()->setAuthState($authState);
	}

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
	}

	protected function tearDown(): void
	{
		CalendarEvent::delete(['calendarId' => self::$calendar->id]);
		go()->setAuthState($this->authState);
	}

	private function createEvent(string $start, string $until, bool $withoutTime = false): CalendarEvent
	{
		$event = new CalendarEvent();
		$event->calendarId = self::$calendar->id;
		$event->title = 'Daily';
		$event->timeZone = self::TZ;
		$event->showWithoutTime = $withoutTime;
		$event->start = new DateTime($start);
		$event->duration = $withoutTime ? 'P1D' : 'PT30M';
		$event->setRecurrenceRule((object) ['frequency' => 'daily', 'until' => $until]);
		$this->assertTrue($event->save(), $event->getValidationErrorsAsString());
		return $event;
	}

	/**
	 * @return string[] The start of the occurrences as "Y-m-d H:i"
	 */
	private function occurrences(CalendarEvent $event): array
	{
		$starts = [];
		foreach (RecurrenceRule::expand($event, '2030-03-01', '2030-04-01') as $instance) {
			$starts[] = $instance->start()->format('Y-m-d H:i');
		}
		return $starts;
	}

	private function eventId(CalendarEvent $event): int
	{
		return (int) go()->getDbConnection()->selectSingleValue('eventId')
			->from('calendar_calendar_event')->where('id', '=', $event->id)->single();
	}

	/**
	 * The stored lastOccurrence, a local date time in the time zone of the event
	 */
	private function lastOccurrence(CalendarEvent $event): string
	{
		return go()->getDbConnection()->selectSingleValue('lastOccurrence')
			->from('calendar_event')->where('eventId', '=', $this->eventId($event))->single();
	}

	private function rrule(CalendarEvent $event): string
	{
		$this->assertSame(1, preg_match('/^RRULE:(.*?)\r?$/m', $event->toVObject(), $m));
		return $m[1];
	}

	public function testDateOnlyUntilIncludesTheLastDay(): void
	{
		$event = $this->createEvent('2030-03-02 09:00:00', '2030-03-04');

		$this->assertEquals('2030-03-04T23:59:59', $event->getRecurrenceRule()->until);
		$this->assertSame(['2030-03-02 09:00', '2030-03-03 09:00', '2030-03-04 09:00'], $this->occurrences($event));
		// the end of the 4th plus the duration
		$this->assertEquals('2030-03-05 00:29:59', $this->lastOccurrence($event));

		// 23:59:59 in Amsterdam is 22:59:59 UTC in winter
		$this->assertStringContainsString('UNTIL=20300304T225959Z', $this->rrule($event));
	}

	public function testUntilWithTimeIsKept(): void
	{
		$event = $this->createEvent('2030-03-02 09:00:00', '2030-03-04T09:00:00');

		$this->assertEquals('2030-03-04T09:00:00', $event->getRecurrenceRule()->until);
		$this->assertSame(['2030-03-02 09:00', '2030-03-03 09:00', '2030-03-04 09:00'], $this->occurrences($event));
	}

	public function testFullDayEventGetsADateOnlyUntil(): void
	{
		$event = $this->createEvent('2030-03-02 00:00:00', '2030-03-04T00:00:00', true);

		$this->assertEquals('2030-03-04', $event->getRecurrenceRule()->until);
		$this->assertSame(['2030-03-02 00:00', '2030-03-03 00:00', '2030-03-04 00:00'], $this->occurrences($event));
		$this->assertStringContainsString('UNTIL=20300304', $this->rrule($event));
		$this->assertStringNotContainsString('UNTIL=20300304T', $this->rrule($event));
	}

	public function testSwitchingToAnEventWithTimeKeepsTheLastDay(): void
	{
		$event = $this->createEvent('2030-03-02 00:00:00', '2030-03-04', true);

		$event->showWithoutTime = false;
		$event->start = new DateTime('2030-03-02 09:00:00');
		$event->duration = 'PT30M';
		$this->assertTrue($event->save(), $event->getValidationErrorsAsString());

		$this->assertEquals('2030-03-04T23:59:59', $event->getRecurrenceRule()->until);
		$this->assertSame(['2030-03-02 09:00', '2030-03-03 09:00', '2030-03-04 09:00'], $this->occurrences($event));
	}

	public function testUpdateFixesSavedEvents(): void
	{
		$event = $this->createEvent('2030-03-02 09:00:00', '2030-03-04');

		// as the web client saved it before: a date and the end of the series at the start of that day
		go()->getDbConnection()->update('calendar_event', [
			'recurrenceRule' => json_encode(['frequency' => 'daily', 'until' => '2030-03-04']),
			'lastOccurrence' => '2030-03-04 00:30:00'
		], ['eventId' => $this->eventId($event)])->execute();

		$updates = [];
		require go()->getEnvironment()->getInstallFolder()->getFile('go/modules/community/calendar/install/updates.php')->getPath();
		foreach ($updates['202611232200'] as $update) {
			$update();
		}

		$event = CalendarEvent::findById($event->id);
		$this->assertEquals('2030-03-04T23:59:59', $event->getRecurrenceRule()->until);
		$this->assertEquals('2030-03-05 00:29:59', $this->lastOccurrence($event));
		$this->assertSame(['2030-03-02 09:00', '2030-03-03 09:00', '2030-03-04 09:00'], $this->occurrences($event));
	}
}
