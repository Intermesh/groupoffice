<?php
namespace go\modules\community\calendar;

use go\core\auth\TemporaryState;
use go\modules\community\calendar\model\Calendar;
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
		$calendar = Calendar::findById($this->calendar->id);

		// outputIcs ends one output buffer. Ignore the "headers already sent" warnings of header() on the command line.
		ob_start();
		ob_start();
		set_error_handler(fn() => true, E_WARNING);
		try {
			(new \ReflectionMethod(Module::class, 'outputIcs'))->invoke(Module::get(), $calendar);
		} catch (\Exception $e) {
			// an invalid time zone stops the export
			ob_end_flush();
		} finally {
			restore_error_handler();
		}
		return ob_get_clean();
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
}
