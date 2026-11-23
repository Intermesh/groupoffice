<?php
namespace go\modules\community\calendar;

use go\core\auth\TemporaryState;
use go\core\exception\Forbidden;
use go\core\model\User;
use go\core\util\DateTime;
use go\modules\community\calendar\model\Calendar;
use go\modules\community\calendar\model\CalendarEvent;
use PHPUnit\Framework\TestCase;
use Sabre\VObject\Reader;

/**
 * The description and time zone of a calendar are written to the iCalendar export and the published calendar. A line
 * break or ";" in them must not add properties or events.
 */
class ModuleIcsTest extends TestCase
{
	private $authState;
	private ?Calendar $calendar = null;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(1));
	}

	protected function tearDown(): void
	{
		if ($this->calendar) {
			Calendar::delete(['id' => $this->calendar->id]);
		}
		go()->setAuthState($this->authState);
	}

	private function export(array $values): string
	{
		$this->calendar = new Calendar();
		$this->calendar->name = 'ICS test ' . uniqid();
		$this->assertTrue($this->calendar->save(), $this->calendar->getValidationErrorsAsString());
		// set without validation, like a value that was stored earlier
		go()->getDbConnection()->update('calendar_calendar', $values, ['id' => $this->calendar->id])->execute();
		return $this->outputIcs(Calendar::findById($this->calendar->id));
	}

	/**
	 * Module::outputIcs() closes all output buffers to stream the file, so the test reads the parts it writes
	 */
	private function outputIcs(Calendar $calendar): string
	{
		$ics = '';
		try {
			foreach ((new \ReflectionMethod(Module::class, 'ics'))->invoke(null, $calendar) as $part) {
				$ics .= $part;
			}
		} catch (\Exception $e) {
			// an invalid time zone stops the export
		}
		return $ics;
	}

	public function testDescriptionIsEscaped(): void
	{
		$description = "Team;calendar,1\\\r\nEND:VCALENDAR\r\nBEGIN:VEVENT\r\nUID:injected\r\nSUMMARY:Injected\r\nEND:VEVENT";
		$ics = $this->export(['description' => $description]);

		$this->assertStringNotContainsString("\r\nBEGIN:VEVENT", $ics);
		$vcalendar = Reader::read($ics);
		$this->assertCount(0, $vcalendar->select('VEVENT'));
		$this->assertEquals(str_replace("\r\n", "\n", $description), (string) $vcalendar->{'X-WR-CALDESC'});
	}

	public function testInvalidTimeZoneIsNotWritten(): void
	{
		$ics = $this->export(['timeZone' => "Europe/Amsterdam\r\nBEGIN:VEVENT"]);
		$this->assertStringNotContainsString("\r\nBEGIN:VEVENT", $ics);
	}

	public function testTimeZone(): void
	{
		$ics = $this->export(['timeZone' => "Europe/Amsterdam"]);
		$this->assertStringContainsString("X-WR-TIMEZONE:Europe/Amsterdam\r\nBEGIN:VTIMEZONE", $ics);
	}

	/**
	 * A published calendar is read with its secret URL, without a logged-in user. It shows the details of the events
	 * that aren't private, also now that users with free / busy permission only see the time.
	 */
	public function testPublishedCalendarShowsDetailsWithoutLogin(): void
	{
		$this->calendar = new Calendar();
		$this->calendar->name = 'ICS test ' . uniqid();
		$this->calendar->setOwnerId(1);
		$this->calendar->setPublish(true);
		$this->assertTrue($this->calendar->save(), $this->calendar->getValidationErrorsAsString());

		$event = new CalendarEvent();
		$event->calendarId = $this->calendar->id;
		$event->title = 'Open day';
		$event->location = 'Main hall';
		$event->start = new DateTime('+1 week');
		$event->duration = 'PT1H';
		$this->assertTrue($event->save(), $event->getValidationErrorsAsString());

		try {
			go()->setAuthState(new TemporaryState());
			$ics = $this->outputIcs(Calendar::findById($this->calendar->id));
		} finally {
			go()->setAuthState(new TemporaryState(1));
			CalendarEvent::delete(['calendarId' => $this->calendar->id]);
		}

		$this->assertStringContainsString('SUMMARY:Open day', $ics);
		$this->assertStringContainsString('LOCATION:Main hall', $ics);
	}

	/**
	 * The ICS file of an event has all its details, so free / busy permission on the calendar is not enough
	 */
	public function testDownloadIcsRequiresReadPermission(): void
	{
		$name = uniqid('icsfreebusy');
		$user = new User();
		$user->setValues(['username' => $name, 'displayName' => $name, 'email' => $name . '@example.com', 'password' => 'Pa55word!!xyz']);
		$this->assertTrue($user->save(), $user->getValidationErrorsAsString());
		try {
			$this->calendar = new Calendar();
			$this->calendar->name = 'ICS test ' . uniqid();
			$this->calendar->setAcl([$user->getPersonalGroup()->id => Calendar::LEVEL_FREEBUSY]);
			$this->assertTrue($this->calendar->save(), $this->calendar->getValidationErrorsAsString());

			$event = new CalendarEvent();
			$event->calendarId = $this->calendar->id;
			$event->title = 'Budget talks';
			$event->start = new DateTime('2030-03-04 10:00:00');
			$event->duration = 'PT1H';
			$this->assertTrue($event->save(), $event->getValidationErrorsAsString());

			go()->setAuthState(new TemporaryState($user->id));
			$this->expectException(Forbidden::class);
			Module::get()->downloadIcs($event->id);
		} finally {
			go()->setAuthState(new TemporaryState(1));
			if ($this->calendar) {
				CalendarEvent::delete(['calendarId' => $this->calendar->id]);
			}
			User::delete(['id' => $user->id]);
		}
	}
}
