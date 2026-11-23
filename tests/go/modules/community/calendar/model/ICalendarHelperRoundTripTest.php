<?php

namespace go\modules\community\calendar\model;

use go\core\auth\TemporaryState;
use PHPUnit\Framework\TestCase;

/**
 * Everything the exporter writes must come back through the importer.
 *
 * Every value is deliberately different from the property default. Otherwise a property that is
 * never imported would pass unnoticed (this is how TRANSP was missed).
 */
class ICalendarHelperRoundTripTest extends TestCase
{
	protected function setUp(): void
	{
		go()->setAuthState(new TemporaryState(1));
	}

	private function timedEvent(): CalendarEvent
	{
		$event = new CalendarEvent();
		$event->uid = 'roundtrip-timed@intermesh.localhost';
		$event->title = 'Round trip';
		$event->description = "Line 1\nLine 2";
		$event->location = 'Den Bosch';
		$event->timeZone = 'Europe/Amsterdam';
		$event->start = new \DateTime('2027-03-10 09:30:00');
		$event->duration = 'PT1H30M';
		$event->status = 'tentative';
		$event->privacy = 'private';
		$event->freeBusyStatus = CalendarEvent::FREEBUSY_FREE;
		$event->color = 'ff0000';
		$event->sequence = 3;
		$event->useDefaultAlerts = false; // don't look up the calendar defaults, the event isn't saved
		$event->alerts = [(new Alert($event))->setValues([
			'action' => 'display',
			'trigger' => ['offset' => '-PT15M', 'relativeTo' => 'start']
		])];
		$event->createdAt = new \DateTime('2027-01-01 10:00:00', new \DateTimeZone('UTC'));
		$event->modifiedAt = new \DateTime('2027-01-02 11:00:00', new \DateTimeZone('UTC'));
		return $event;
	}

	private function allDayEvent(): CalendarEvent
	{
		$event = new CalendarEvent();
		$event->uid = 'roundtrip-allday@intermesh.localhost';
		$event->title = 'Holiday';
		$event->timeZone = 'Europe/Amsterdam';
		$event->start = new \DateTime('2027-10-15 00:00:00');
		$event->duration = 'P3D';
		$event->showWithoutTime = true;
		$event->useDefaultAlerts = false;
		$event->freeBusyStatus = CalendarEvent::FREEBUSY_FREE;
		return $event;
	}

	private function recurringEvent(): CalendarEvent
	{
		$event = $this->timedEvent();
		$event->uid = 'roundtrip-recurring@intermesh.localhost';
		$event->setRecurrenceRule((object)['frequency' => 'weekly', 'interval' => 2, 'count' => 6]);
		$event->recurrenceOverrides = [
			'2027-03-24T09:30:00' => (new RecurrenceOverride($event))->setValues(['excluded' => true]),
		];
		return $event;
	}

	private function roundTrip(CalendarEvent $event): CalendarEvent
	{
		$ics = ICalendarHelper::toVObject($event)->serialize();
		return ICalendarHelper::parseVObject($ics, new CalendarEvent());
	}

	public static function timedProperties(): array
	{
		$props = ['uid', 'title', 'description', 'location', 'status', 'privacy', 'freeBusyStatus', 'color',
			'sequence', 'duration', 'timeZone', 'showWithoutTime'];
		return array_combine($props, array_map(fn($p) => [$p], $props));
	}

	/**
	 * @dataProvider timedProperties
	 */
	public function testTimedEventProperty(string $property): void
	{
		$original = $this->timedEvent();
		$imported = $this->roundTrip($original);
		$this->assertEquals($original->$property, $imported->$property, $property);
	}

	public function testTimedEventStart(): void
	{
		$original = $this->timedEvent();
		$imported = $this->roundTrip($original);
		$this->assertSame(
			$original->start($original->showWithoutTime)->format('Y-m-d H:i:s'),
			$imported->start($imported->showWithoutTime)->format('Y-m-d H:i:s'));
	}

	public function testCreatedAndModifiedAt(): void
	{
		$original = $this->timedEvent();
		$imported = $this->roundTrip($original);
		$this->assertEquals($original->createdAt->getTimestamp(), $imported->createdAt->getTimestamp(), 'createdAt');
		$this->assertEquals($original->modifiedAt->getTimestamp(), $imported->modifiedAt->getTimestamp(), 'modifiedAt');
	}

	public static function allDayProperties(): array
	{
		$props = ['title', 'duration', 'showWithoutTime', 'freeBusyStatus'];
		return array_combine($props, array_map(fn($p) => [$p], $props));
	}

	/**
	 * @dataProvider allDayProperties
	 */
	public function testAllDayEventProperty(string $property): void
	{
		$original = $this->allDayEvent();
		$imported = $this->roundTrip($original);
		$this->assertEquals($original->$property, $imported->$property, $property);
	}

	public function testAllDayEventStart(): void
	{
		$original = $this->allDayEvent();
		$imported = $this->roundTrip($original);
		$this->assertSame('2027-10-15', $imported->start(true)->format('Y-m-d'));
	}

	public function testRecurrenceRule(): void
	{
		$original = $this->recurringEvent();
		$imported = $this->roundTrip($original);

		$this->assertTrue($imported->isRecurring());
		$rule = $imported->getRecurrenceRule();
		$this->assertSame('weekly', $rule->frequency);
		$this->assertEquals(2, $rule->interval);
		$this->assertEquals(6, $rule->count);
	}

	public function testExcludedOccurrence(): void
	{
		$imported = $this->roundTrip($this->recurringEvent());
		$this->assertArrayHasKey('2027-03-24T09:30:00', $imported->recurrenceOverrides);
		$this->assertTrue((bool)$imported->recurrenceOverrides['2027-03-24T09:30:00']->excluded);
	}

	private function eventWithParticipants(): CalendarEvent
	{
		$event = $this->timedEvent();
		$event->uid = 'roundtrip-participants@intermesh.localhost';
		$participants = [];
		foreach (['organizer' => ['owner' => true, 'attendee' => true], 'required' => ['attendee' => true],
			         'optional' => ['optional' => true], 'informational' => ['informational' => true],
			         'chair' => ['chair' => true]] as $name => $roles) {
			$email = $name . '@roles.localhost';
			$participants[$email] = ['email' => $email, 'name' => $name, 'roles' => $roles, 'kind' => 'individual',
				'participationStatus' => 'accepted'];
		}
		$event->setValues(['participants' => $participants]);
		return $event;
	}

	public static function participantRoles(): array
	{
		return [
			'organizer' => ['organizer', ['owner' => true]],
			'required' => ['required', ['attendee' => true]],
			'optional' => ['optional', ['optional' => true]],
			'informational' => ['informational', ['informational' => true]],
			'chair' => ['chair', ['chair' => true]],
		];
	}

	/**
	 * @dataProvider participantRoles
	 */
	public function testParticipantRoles(string $name, array $expectedRoles): void
	{
		$imported = $this->roundTrip($this->eventWithParticipants());
		$this->assertArrayHasKey($name . '@roles.localhost', $imported->participants);
		$this->assertEquals((object)$expectedRoles, $imported->participants[$name . '@roles.localhost']->getRoles());
	}

	public function testOrganizerHasNoRole(): void
	{
		$vevent = ICalendarHelper::toVObject($this->eventWithParticipants())->VEVENT[0];
		$this->assertFalse(isset($vevent->ORGANIZER['ROLE']), 'ROLE is not a parameter of ORGANIZER');
		$this->assertSame('REQ-PARTICIPANT', (string)$vevent->ATTENDEE[0]['ROLE']);
	}

	public function testAttendeeWithoutRoleIsRequired(): void
	{
		$ics = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:test\r\nBEGIN:VEVENT\r\nUID:no-role@intermesh.localhost\r\n" .
			"DTSTART:20270310T093000Z\r\nDTEND:20270310T103000Z\r\nSUMMARY:No role\r\n" .
			"ORGANIZER:mailto:organizer@roles.localhost\r\nATTENDEE;PARTSTAT=NEEDS-ACTION:mailto:attendee@roles.localhost\r\n" .
			"END:VEVENT\r\nEND:VCALENDAR\r\n";
		$imported = ICalendarHelper::parseVObject($ics, new CalendarEvent());
		$this->assertEquals((object)['attendee' => true], $imported->participants['attendee@roles.localhost']->getRoles());
	}

	public function testAlert(): void
	{
		$imported = $this->roundTrip($this->timedEvent());
		$this->assertCount(1, $imported->alerts);
		$alert = $imported->alerts[0];
		$this->assertSame('display', $alert->action);
		$this->assertSame('-PT15M', $alert->getTrigger()['offset']);
	}
}
