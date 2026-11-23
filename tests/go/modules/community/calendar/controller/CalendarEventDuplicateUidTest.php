<?php

namespace go\modules\community\calendar\controller;

use go\core\auth\TemporaryState;
use go\core\model\User;
use go\core\util\DateTime;
use go\modules\community\calendar\model\Calendar;
use go\modules\community\calendar\model\CalendarEvent as EventModel;
use go\modules\community\calendar\model\ICalendarHelper;
use go\modules\community\calendar\Module;
use PHPUnit\Framework\TestCase;

/**
 * An event with participants is stored once for all calendars it's in. A client must not create a second event with
 * the same UID, for example by adding an invitation from an e-mail attachment to the calendar. The participants
 * would see the event twice and their replies went to the user who added the copy.
 */
class CalendarEventDuplicateUidTest extends TestCase
{
	/** @var User[] */
	private static array $users = [];
	private bool $sendMessages;
	private EventModel $event;
	private array $eventIds = [];

	public static function setUpBeforeClass(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		try {
			foreach (['organizer', 'attendee', 'outsider'] as $name) {
				$username = uniqid('dupuid' . $name);
				$user = new User();
				$user->setValues([
					'username' => $username,
					'displayName' => $name,
					'email' => $username . '@intermesh.localhost',
					'password' => 'Pa55word!!xyz'
				]);
				if (!$user->save()) {
					throw new \Exception(var_export($user->getValidationErrors(), true));
				}
				self::$users[$name] = $user;
			}
		} catch (\Throwable $e) {
			self::tearDownAfterClass();
			throw $e;
		}
	}

	public static function tearDownAfterClass(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		if (self::$users) {
			User::delete(['id' => array_map(fn(User $u) => $u->id, self::$users)]);
		}
		self::$users = [];
	}

	protected function setUp(): void
	{
		$this->sendMessages = EventModel::$sendSchedulingMessages;
		EventModel::$sendSchedulingMessages = false;

		$organizer = self::$users['organizer'];
		$attendee = self::$users['attendee'];
		go()->setAuthState(new TemporaryState($organizer->id));

		$this->event = new EventModel();
		$this->event->calendarId = Calendar::fetchPersonal($organizer->id);
		$this->event->title = 'Invite';
		$this->event->timeZone = 'Europe/Amsterdam';
		$this->event->start = (new DateTime('+1 week'))->setTime(12, 0, 0, 0);
		$this->event->duration = 'PT1H';
		$this->event->setValues(['participants' => $this->participants()]);
		$this->assertTrue($this->event->save(), json_encode($this->event->getValidationErrors()));
		$this->eventIds[] = $this->event->id;
	}

	protected function tearDown(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		go()->getDbConnection()->delete('calendar_calendar_event', ['eventId' => $this->event->customFieldsModelId()])->execute();
		if ($this->eventIds) {
			EventModel::delete(['id' => $this->eventIds]);
		}
		$this->eventIds = [];
		Module::onGarbageCollection();
		EventModel::$sendSchedulingMessages = $this->sendMessages;
	}

	private function participants(): array
	{
		$organizer = self::$users['organizer'];
		$attendee = self::$users['attendee'];
		return [
			$organizer->id => ['email' => $organizer->email, 'name' => 'Organizer', 'roles' => ['owner' => true, 'attendee' => true], 'kind' => 'individual', 'participationStatus' => 'accepted'],
			$attendee->id => ['email' => $attendee->email, 'name' => 'Attendee', 'roles' => ['attendee' => true], 'kind' => 'individual'],
		];
	}

	/**
	 * The controller without the constructor, which checks the module permissions of the test user
	 */
	private function controller(): CalendarEvent
	{
		return (new \ReflectionClass(CalendarEvent::class))->newInstanceWithoutConstructor();
	}

	/**
	 * Create an event with CalendarEvent/set as the given user, like the "Add" button of an opened ICS attachment
	 */
	private function createAs(string $userKey, array $props): array
	{
		$user = self::$users[$userKey];
		go()->setAuthState(new TemporaryState($user->id));
		$response = $this->controller()->set(['create' => ['new' => array_merge([
			'calendarId' => Calendar::fetchPersonal($user->id),
			'title' => 'Copy',
			'timeZone' => 'Europe/Amsterdam',
			'start' => $this->event->start->format('Y-m-d\TH:i:s'),
			'duration' => 'PT1H',
		], $props)]]);
		$response = json_decode(json_encode($response), true);
		if (isset($response['created']['new']['id'])) {
			$this->eventIds[] = $response['created']['new']['id'];
		}
		return $response;
	}

	private function countEventsWithUid(string $uid): int
	{
		return (int) go()->getDbConnection()->selectSingleValue('count(*)')
			->from('calendar_event')->where(['uid' => $uid])->single();
	}

	public static function users(): array
	{
		return ['attendee' => ['attendee'], 'user without access' => ['outsider']];
	}

	/**
	 * @dataProvider users
	 */
	public function testCopyOfScheduledEventIsRefused(string $userKey): void
	{
		$response = $this->createAs($userKey, ['uid' => $this->event->uid, 'participants' => $this->participants()]);

		$this->assertArrayNotHasKey('new', $response['created'] ?? [], 'A second event with the same UID must not be created');
		$this->assertArrayHasKey('uid', $response['notCreated']['new']['validationErrors'] ?? [], json_encode($response));
		$this->assertSame(1, $this->countEventsWithUid($this->event->uid));

		go()->setAuthState(new TemporaryState(self::$users['organizer']->id));
		$original = EventModel::findById($this->event->id);
		$this->assertSame('Invite', $original->title);
		$this->assertSame(self::$users['organizer']->email, $original->replyTo);
	}

	public function testNewEventWithoutUidIsCreated(): void
	{
		$response = $this->createAs('attendee', []);
		$this->assertArrayHasKey('new', $response['created'] ?? [], json_encode($response));
	}

	private function openInvitationAs(string $userKey): EventModel
	{
		go()->setAuthState(new TemporaryState(self::$users['organizer']->id));
		$ics = ICalendarHelper::toVObject(EventModel::findById($this->event->id))->serialize();

		go()->setAuthState(new TemporaryState(self::$users[$userKey]->id));
		$method = new \ReflectionMethod(CalendarEvent::class, 'eventFromIcs');
		return $method->invoke($this->controller(), $ics);
	}

	public function testOpeningInvitationShowsTheEventOfTheAttendee(): void
	{
		$event = $this->openInvitationAs('attendee');

		$this->assertFalse($event->isNew(), 'The attendee must get the event in their calendar, not an unsaved copy to add');
		$this->assertEquals(Calendar::fetchPersonal(self::$users['attendee']->id), $event->calendarId);
		$this->assertSame($this->event->customFieldsModelId(), $event->customFieldsModelId());
	}

	public function testOpeningInvitationWithoutAccessDoesNotShowTheEvent(): void
	{
		$event = $this->openInvitationAs('outsider');

		$this->assertTrue($event->isNew(), 'A user without access must not get the event of others');
		$this->assertSame($this->event->uid, $event->uid);
	}

	public function testEventWithoutParticipantsMayShareUid(): void
	{
		// Importing the same plain event, like a holiday, in two calendars still works
		go()->getDbConnection()->delete('calendar_participant', ['eventId' => $this->event->customFieldsModelId()])->execute();

		$response = $this->createAs('attendee', ['uid' => $this->event->uid]);
		$this->assertArrayHasKey('new', $response['created'] ?? [], json_encode($response));
	}
}
