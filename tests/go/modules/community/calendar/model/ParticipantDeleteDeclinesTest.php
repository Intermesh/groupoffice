<?php

namespace go\modules\community\calendar\model;

use go\core\auth\TemporaryState;
use go\core\model\User;
use go\core\orm\EntityType;
use go\core\util\DateTime;
use go\modules\community\calendar\Module;
use PHPUnit\Framework\TestCase;

/**
 * When an internal participant deletes an invitation from their calendar, it is declined. The participants are shared
 * with the organizer's event, so the organizer must see the decline, also when no reply is sent ("Save only").
 */
class ParticipantDeleteDeclinesTest extends TestCase
{
	/** @var User[] */
	private array $users = [];
	private bool $sendMessages;
	private ?CalendarEvent $event = null;

	protected function setUp(): void
	{
		$this->sendMessages = CalendarEvent::$sendSchedulingMessages;
		// "Save only" in the UI
		CalendarEvent::$sendSchedulingMessages = false;

		// Users are reused and never deleted so other tests that rely on low user ids keep working.
		foreach (['organizer', 'attendee'] as $name) {
			$username = 'decline' . $name;
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
	}

	protected function tearDown(): void
	{
		go()->setAuthState(new TemporaryState(1));
		if ($this->event) {
			go()->getDbConnection()->delete('calendar_calendar_event', ['eventId' => $this->event->customFieldsModelId()])->execute();
		}
		Module::onGarbageCollection();
		CalendarEvent::$sendSchedulingMessages = $this->sendMessages;
	}

	private function createInvite(): CalendarEvent
	{
		$organizer = $this->users['organizer'];
		$attendee = $this->users['attendee'];
		go()->setAuthState(new TemporaryState($organizer->id));

		$event = new CalendarEvent();
		$event->calendarId = Calendar::fetchPersonal($organizer->id);
		$event->title = 'Invite';
		$event->timeZone = 'Europe/Amsterdam';
		$event->start = (new DateTime('+1 week'))->setTime(12, 0, 0, 0);
		$event->duration = 'PT1H';
		$event->setValues(['participants' => [
			$organizer->id => ['email' => $organizer->email, 'name' => 'Organizer', 'roles' => ['owner' => true], 'kind' => 'individual', 'participationStatus' => 'accepted'],
			$attendee->id => ['email' => $attendee->email, 'name' => 'Attendee', 'roles' => ['attendee' => true], 'kind' => 'individual'],
		]]);
		$this->assertTrue($event->save(), json_encode($event->getValidationErrors()));
		$this->event = $event;
		return $event;
	}

	private function instanceIn(int $calendarId, CalendarEvent $event): ?CalendarEvent
	{
		return CalendarEvent::find()->where(['cce.calendarId' => $calendarId, 'cce.eventId' => $event->customFieldsModelId()])->single();
	}

	private function participantStatus(CalendarEvent $event, string $userKey): ?string
	{
		return go()->getDbConnection()->selectSingleValue('participationStatus')
			->from('calendar_participant')
			->where(['eventId' => $event->customFieldsModelId(), 'email' => $this->users[$userKey]->email])
			->single();
	}

	private function changedEventIds(): array
	{
		$ref = new \ReflectionProperty(EntityType::class, 'changes');
		$ids = [];
		foreach ($ref->getValue() as $entityTypeId => $changes) {
			if ($entityTypeId == CalendarEvent::entityType()->getId()) {
				foreach ($changes as $change) {
					$ids[] = $change['entityId'];
				}
			}
		}
		return $ids;
	}

	public function testAttendeeDeleteDeclinesForOrganizer(): void
	{
		$event = $this->createInvite();
		$attendeeCalendarId = Calendar::fetchPersonal($this->users['attendee']->id);
		$attendeeInstance = $this->instanceIn($attendeeCalendarId, $event);
		$this->assertNotNull($attendeeInstance, 'The invite must be added to the attendee calendar');
		$this->assertSame(Participant::NeedsAction, $this->participantStatus($event, 'attendee'));

		go()->setAuthState(new TemporaryState($this->users['attendee']->id));
		$this->assertTrue(CalendarEvent::delete(['id' => $attendeeInstance->id]));

		$this->assertNull($this->instanceIn($attendeeCalendarId, $event));
		$organizerInstance = $this->instanceIn($event->calendarId, $event);
		$this->assertNotNull($organizerInstance, 'The organizer keeps the event');
		$this->assertSame(Participant::Declined, $this->participantStatus($event, 'attendee'));
		$this->assertSame(Participant::Accepted, $this->participantStatus($event, 'organizer'));
		$this->assertContains($organizerInstance->id, $this->changedEventIds(), 'The organizer event must be marked as changed so clients sync the decline');
	}

	public function testOrganizerDeleteDoesNotDecline(): void
	{
		$event = $this->createInvite();

		$this->assertTrue(CalendarEvent::delete(['id' => $event->id]));

		$this->assertSame(Participant::Accepted, $this->participantStatus($event, 'organizer'));
		$this->assertSame(Participant::NeedsAction, $this->participantStatus($event, 'attendee'));
	}
}
