<?php

namespace go\core\dav\schedule;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\ITip\Message;

class BrokerTest extends TestCase
{
	private const OCCURRENCES_ONLY = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Test//EN
BEGIN:VEVENT
UID:meeting-series-13
SEQUENCE:0
DTSTAMP:20261101T080000Z
RECURRENCE-ID:20261120T090000Z
DTSTART:20261120T090000Z
DTEND:20261120T100000Z
SUMMARY:Meeting
ORGANIZER:mailto:organizer@example.com
ATTENDEE;PARTSTAT=ACCEPTED:mailto:attendee@example.com
END:VEVENT
BEGIN:VEVENT
UID:meeting-series-13
SEQUENCE:0
DTSTAMP:20261101T080000Z
RECURRENCE-ID:20261127T090000Z
DTSTART:20261127T090000Z
DTEND:20261127T100000Z
SUMMARY:Meeting
ORGANIZER:mailto:organizer@example.com
ATTENDEE;PARTSTAT=NEEDS-ACTION:mailto:attendee@example.com
END:VEVENT
END:VCALENDAR
ICS;

	/**
	 * An attendee deleting an event with only occurrences declines each of them instead of throwing.
	 */
	public function testAttendeeDeletesOccurrencesWithoutMaster(): void
	{
		$messages = (new Broker())->parseEvent(null, ['mailto:attendee@example.com'], self::OCCURRENCES_ONLY);

		$this->assertCount(1, $messages);
		/** @var Message $message */
		$message = $messages[0];
		$this->assertEquals('REPLY', $message->method);
		$this->assertEquals('mailto:organizer@example.com', $message->recipient);

		$recurrenceIds = [];
		foreach ($message->message->VEVENT as $vevent) {
			$this->assertEquals('DECLINED', (string) $vevent->ATTENDEE['PARTSTAT']);
			$recurrenceIds[] = (string) $vevent->{'RECURRENCE-ID'};
		}
		sort($recurrenceIds);
		$this->assertEquals(['20261120T090000Z', '20261127T090000Z'], $recurrenceIds);
	}

	/**
	 * The organizer deleting the event is still handled by Sabre and sends a cancellation.
	 */
	public function testOrganizerDeletesOccurrencesWithoutMaster(): void
	{
		$messages = (new Broker())->parseEvent(null, ['mailto:organizer@example.com'], self::OCCURRENCES_ONLY);

		$this->assertCount(1, $messages);
		$this->assertEquals('CANCEL', $messages[0]->method);
	}
}
