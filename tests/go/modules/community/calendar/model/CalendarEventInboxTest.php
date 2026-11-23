<?php

namespace go\modules\community\calendar\model;

use go\core\auth\TemporaryState;
use go\core\model\User;
use go\core\util\DateTime;
use go\modules\community\calendar\Module;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The "inbox" filter lists open invitations of the current user. When the organizer can't see the invited user, the
 * participant gets another principal id, like "Contact:1", instead of the user id. The invitation is delivered by
 * e-mail and must still show in the inbox.
 */
class CalendarEventInboxTest extends TestCase
{
	/** @var User[] */
	private array $users = [];
	private bool $sendMessages;
	private ?CalendarEvent $event = null;

	protected function setUp(): void
	{
		$this->sendMessages = CalendarEvent::$sendSchedulingMessages;
		CalendarEvent::$sendSchedulingMessages = false;

		// Users are reused and never deleted so other tests that rely on low user ids keep working.
		foreach (['organizer', 'attendee', 'other'] as $name) {
			$username = 'inbox' . $name;
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

	/**
	 * Participant ids of the attendee: the user id, or the id of another principal like a contact
	 */
	public static function attendeeIdProvider(): array
	{
		return [
			'user id' => [null],
			'contact principal id' => ['Contact:999999'],
		];
	}

	private function createInvite(string $attendeeId): CalendarEvent
	{
		$organizer = $this->users['organizer'];
		$attendee = $this->users['attendee'];
		go()->setAuthState(new TemporaryState($organizer->id));

		$event = new CalendarEvent();
		$event->calendarId = Calendar::fetchPersonal($organizer->id);
		$event->title = 'Inbox invite';
		$event->timeZone = 'Europe/Amsterdam';
		$event->start = (new DateTime('+1 week'))->setTime(12, 0, 0, 0);
		$event->duration = 'PT1H';
		$event->setValues(['participants' => [
			$organizer->id => ['email' => $organizer->email, 'name' => 'Organizer', 'roles' => ['owner' => true], 'kind' => 'individual', 'participationStatus' => 'accepted'],
			$attendeeId => ['email' => $attendee->email, 'name' => 'Attendee', 'roles' => ['attendee' => true], 'kind' => 'individual'],
		]]);
		$this->assertTrue($event->save(), json_encode($event->getValidationErrors()));
		$this->event = $event;

		// For a non-user participant id the event reaches the attendee's calendar through the e-mail invitation
		// (Scheduler::eventByVEvent()). Add it like that does.
		go()->getDbConnection()->insertIgnore('calendar_calendar_event', [
			['calendarId' => Calendar::fetchPersonal($attendee->id), 'eventId' => $event->customFieldsModelId()]
		])->execute();

		return $event;
	}

	/**
	 * @return int[] event data ids in the inbox of the user
	 */
	private function inboxEventIds(string $userKey): array
	{
		go()->setAuthState(new TemporaryState($this->users[$userKey]->id));
		return array_map('intval', CalendarEvent::find()
			->filter(['inbox' => true])
			->selectSingleValue('cce.eventId')
			->all());
	}

	#[DataProvider('attendeeIdProvider')]
	public function testInvitationIsInAttendeeInbox(?string $attendeeId): void
	{
		$event = $this->createInvite($attendeeId ?? (string)$this->users['attendee']->id);

		$this->assertContains((int)$event->customFieldsModelId(), $this->inboxEventIds('attendee'));
		$this->assertNotContains((int)$event->customFieldsModelId(), $this->inboxEventIds('organizer'), 'The organizer has no invitation to answer');
		$this->assertNotContains((int)$event->customFieldsModelId(), $this->inboxEventIds('other'), 'Other users must not see the invitation');
	}

	#[DataProvider('attendeeIdProvider')]
	public function testAnsweredInvitationLeavesInbox(?string $attendeeId): void
	{
		$event = $this->createInvite($attendeeId ?? (string)$this->users['attendee']->id);

		go()->getDbConnection()->update('calendar_participant',
			['participationStatus' => Participant::Accepted],
			['eventId' => $event->customFieldsModelId(), 'email' => $this->users['attendee']->email]
		)->execute();

		$this->assertNotContains((int)$event->customFieldsModelId(), $this->inboxEventIds('attendee'));
	}
}
