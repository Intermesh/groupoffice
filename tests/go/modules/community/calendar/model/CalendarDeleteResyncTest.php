<?php

namespace go\modules\community\calendar\model;

use go\core\auth\TemporaryState;
use go\core\model\User;
use go\modules\community\calendar\Module;
use PHPUnit\Framework\TestCase;

/**
 * Documents what happens to shared events and their participants when a calendar is deleted and
 * later recreated by re-syncing an older state (e.g. Outlook over CalDAV).
 *
 * Events are one shared row in calendar_event, linked to calendars through calendar_calendar_event.
 * Participants belong to the event, so a calendar delete only removes that calendar's link.
 */
class CalendarDeleteResyncTest extends TestCase
{
	/** @var User[] */
	private array $users = [];
	private string $uid;
	private bool $sendMessages;

	protected function setUp(): void
	{
		$this->sendMessages = CalendarEvent::$sendSchedulingMessages;
		CalendarEvent::$sendSchedulingMessages = false;

		// Users are reused and never deleted so other tests that rely on low user ids keep working.
		foreach (['alice', 'bob'] as $name) {
			$username = 'resync' . $name;
			$user = User::find()->where(['username' => $username])->single();
			if (!$user) {
				$user = new User();
				$user->username = $username;
				$user->displayName = $name;
				$user->email = $user->recoveryEmail = $username . '@intermesh.localhost';
				$user->setPassword('sdfTgs-Dfgg-$#ddg');
				$this->assertTrue($user->save(), json_encode($user->getValidationErrors()));
			}
			$this->users[$name] = $user;
		}

		go()->setAuthState(new TemporaryState($this->users['alice']->id));
		$this->uid = 'resync-' . uniqid();
	}

	protected function tearDown(): void
	{
		go()->setAuthState(new TemporaryState(1));
		Module::onGarbageCollection();
		CalendarEvent::$sendSchedulingMessages = $this->sendMessages;
	}

	private function ics(string $title): string
	{
		$alice = $this->users['alice']->email;
		$bob = $this->users['bob']->email;
		return "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Outlook//EN\r\nBEGIN:VEVENT\r\n" .
			"UID:{$this->uid}\r\nSEQUENCE:1\r\nDTSTAMP:20261001T100000Z\r\n" .
			"DTSTART:20271001T100000Z\r\nDTEND:20271001T110000Z\r\nSUMMARY:$title\r\n" .
			"ORGANIZER;CN=alice:mailto:$alice\r\n" .
			"ATTENDEE;CN=alice;PARTSTAT=ACCEPTED;ROLE=CHAIR:mailto:$alice\r\n" .
			"ATTENDEE;CN=bob;PARTSTAT=NEEDS-ACTION;RSVP=TRUE:mailto:$bob\r\n" .
			"END:VEVENT\r\nEND:VCALENDAR\r\n";
	}

	private function createCalendar(string $name): Calendar
	{
		$calendar = new Calendar();
		$calendar->name = $name;
		$this->assertTrue($calendar->save());
		return $calendar;
	}

	private function put(Calendar $calendar, string $title): void
	{
		(new CalDAVBackend())->createCalendarObject('c-' . $calendar->id, $this->uid . '.ics', $this->ics($title));
	}

	/** @return int[] */
	private function eventIds(): array
	{
		$stmt = go()->getDbConnection()->getPDO()->prepare("select eventId from calendar_event where uid = ?");
		$stmt->execute([$this->uid]);
		return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
	}

	/** @return int[] sorted calendar ids the event is linked to */
	private function linkedCalendarIds(int $eventId): array
	{
		$stmt = go()->getDbConnection()->getPDO()->prepare("select calendarId from calendar_calendar_event where eventId = ?");
		$stmt->execute([$eventId]);
		$ids = array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
		sort($ids);
		return $ids;
	}

	private function participantStatus(int $eventId, string $userKey): ?string
	{
		$stmt = go()->getDbConnection()->getPDO()->prepare(
			"select participationStatus from calendar_participant where eventId = ? and email = ?");
		$stmt->execute([$eventId, $this->users[$userKey]->email]);
		$status = $stmt->fetchColumn();
		return $status === false ? null : $status;
	}

	private function setParticipantStatus(int $eventId, string $userKey, string $status): void
	{
		$stmt = go()->getDbConnection()->getPDO()->prepare(
			"update calendar_participant set participationStatus = ? where eventId = ? and email = ?");
		$stmt->execute([$status, $eventId, $this->users[$userKey]->email]);
	}

	public function testInternalParticipantsShareTheEventRow(): void
	{
		$calA = $this->createCalendar('Calendar A');
		$this->put($calA, 'Original');

		$ids = $this->eventIds();
		$this->assertCount(1, $ids);

		$linked = $this->linkedCalendarIds($ids[0]);
		$this->assertContains((int)$calA->id, $linked);
		$this->assertContains((int)Calendar::fetchPersonal($this->users['alice']->id), $linked);
		$this->assertContains((int)Calendar::fetchPersonal($this->users['bob']->id), $linked);
	}

	public function testDeletingCalendarKeepsEventAndParticipants(): void
	{
		$calA = $this->createCalendar('Calendar A');
		$this->put($calA, 'Original');
		$eventId = $this->eventIds()[0];
		$this->setParticipantStatus($eventId, 'bob', 'accepted');

		$this->assertTrue(Calendar::delete(['id' => $calA->id]));

		// Event and participants survive, only the link to the deleted calendar is gone.
		$this->assertSame([$eventId], $this->eventIds());
		$linked = $this->linkedCalendarIds($eventId);
		$this->assertNotContains((int)$calA->id, $linked);
		$this->assertContains((int)Calendar::fetchPersonal($this->users['bob']->id), $linked);
		$this->assertSame('accepted', $this->participantStatus($eventId, 'bob'));
		$this->assertSame('accepted', $this->participantStatus($eventId, 'alice'));

		// Still linked in personal calendars so garbage collection must keep it.
		Module::onGarbageCollection();
		$this->assertSame([$eventId], $this->eventIds());
	}

	public function testResyncIntoRecreatedCalendarReusesEventByUid(): void
	{
		$calA = $this->createCalendar('Calendar A');
		$this->put($calA, 'Original');
		$eventId = $this->eventIds()[0];
		$this->setParticipantStatus($eventId, 'bob', 'accepted');

		$this->assertTrue(Calendar::delete(['id' => $calA->id]));

		$recreated = $this->createCalendar('Calendar A recreated');
		$this->assertNotEquals($calA->id, $recreated->id);
		$this->put($recreated, 'Re-synced');

		// No duplicate: the UID matched the surviving event, which is re-linked to the new calendar.
		$this->assertSame([$eventId], $this->eventIds());
		$this->assertContains((int)$recreated->id, $this->linkedCalendarIds($eventId));
		$this->assertContains((int)Calendar::fetchPersonal($this->users['bob']->id), $this->linkedCalendarIds($eventId));

		// The shared event is overwritten with the stale client state.
		$stmt = go()->getDbConnection()->getPDO()->prepare("select title from calendar_event where eventId = ?");
		$stmt->execute([$eventId]);
		$this->assertSame('Re-synced', $stmt->fetchColumn());
		// Current behaviour: the stale PARTSTAT overwrites what bob answered in the meantime.
		$this->assertSame('needs-action', $this->participantStatus($eventId, 'bob'));
	}

	public function testResyncAfterEventWasGarbageCollectedCreatesNewEvent(): void
	{
		$calA = $this->createCalendar('Calendar A');
		$this->put($calA, 'Original');
		$oldId = $this->eventIds()[0];

		// Nobody links the event anymore: calendar A, and the personal calendars of alice and bob.
		$this->assertTrue(Calendar::delete(['id' => $calA->id]));
		go()->getDbConnection()->delete('calendar_calendar_event', ['eventId' => $oldId])->execute();
		Module::onGarbageCollection();
		$this->assertSame([], $this->eventIds());

		$recreated = $this->createCalendar('Calendar A recreated');
		$this->put($recreated, 'Re-synced');

		$ids = $this->eventIds();
		$this->assertCount(1, $ids);
		$this->assertNotSame($oldId, $ids[0]);
		$this->assertContains((int)$recreated->id, $this->linkedCalendarIds($ids[0]));
		// Participants are new again, so they are linked into their personal calendars again.
		$this->assertContains((int)Calendar::fetchPersonal($this->users['bob']->id), $this->linkedCalendarIds($ids[0]));
		$this->assertSame('needs-action', $this->participantStatus($ids[0], 'bob'));
	}
}
