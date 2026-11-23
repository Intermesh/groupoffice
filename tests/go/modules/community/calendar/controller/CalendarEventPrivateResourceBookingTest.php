<?php

namespace go\modules\community\calendar\controller;

use go\core\auth\TemporaryState;
use go\core\model\Acl;
use go\core\model\User;
use go\core\util\DateTime;
use go\modules\community\calendar\model\Calendar as CalendarModel;
use go\modules\community\calendar\model\CalendarEvent as EventModel;
use go\modules\community\calendar\model\ICalendarHelper;
use go\modules\community\calendar\model\ResourceGroup;
use PHPUnit\Framework\TestCase;

/**
 * A private event of someone else is read only and doesn't show its participants. The admin of a resource must still
 * be able to answer a private booking of the resource, so the admin sees who takes part and may change the
 * participation status of the resource, but nothing else.
 */
class CalendarEventPrivateResourceBookingTest extends TestCase
{
	private static ?User $roomAdmin = null;
	private static ?User $booker = null;
	private static ?User $colleague = null;
	private static ?ResourceGroup $group = null;
	private static ?CalendarModel $room = null;
	private static bool $sendMessages;

	private $authState;
	private ?EventModel $booking = null;
	private ?string $roomEventId = null;

	public static function setUpBeforeClass(): void
	{
		$authState = go()->getAuthState();
		self::$sendMessages = EventModel::$sendSchedulingMessages;
		EventModel::$sendSchedulingMessages = false;
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		try {
			self::$roomAdmin = self::createUser('roomadmin');
			self::$booker = self::createUser('booker');
			self::$colleague = self::createUser('roomuser');

			self::$group = new ResourceGroup();
			self::$group->name = uniqid('rooms');
			self::$group->defaultOwnerId = self::$roomAdmin->id;
			self::assertTrue(self::$group->save(), self::$group->getValidationErrorsAsString());

			// The admin of the resource group gets "write all" on the room
			self::$room = new CalendarModel();
			self::$room->name = uniqid('room');
			self::$room->groupId = self::$group->id;
			self::$room->setAcl([
				self::$booker->getPersonalGroup()->id => Acl::LEVEL_WRITE,
				self::$colleague->getPersonalGroup()->id => Acl::LEVEL_WRITE,
			]);
			self::assertTrue(self::$room->save(), self::$room->getValidationErrorsAsString());
		} catch (\Throwable $e) {
			self::tearDownAfterClass();
			throw $e;
		} finally {
			go()->setAuthState($authState);
		}
	}

	public static function tearDownAfterClass(): void
	{
		$authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		if (isset(self::$room)) {
			EventModel::delete(['calendarId' => self::$room->id]);
			CalendarModel::delete(['id' => self::$room->id]);
		}
		if (isset(self::$group)) {
			ResourceGroup::delete(['id' => self::$group->id]);
		}
		$userIds = array_filter([self::$roomAdmin?->id, self::$booker?->id, self::$colleague?->id]);
		if ($userIds) {
			// Also the events in their personal calendars
			EventModel::delete(['calendarId' => array_filter(array_map(fn($id) => CalendarModel::fetchPersonal($id), $userIds))]);
			User::delete(['id' => $userIds]);
		}
		EventModel::$sendSchedulingMessages = self::$sendMessages;
		go()->setAuthState($authState);
	}

	private static function createUser(string $prefix): User
	{
		$name = uniqid($prefix);
		$user = new User();
		$user->setValues(['username' => $name, 'displayName' => $name, 'email' => $name . '@example.com', 'password' => 'Pa55word!!xyz']);
		self::assertTrue($user->save(), $user->getValidationErrorsAsString());
		return $user;
	}

	/**
	 * The booker books the room for a private event. The event is added to the room calendar.
	 */
	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(self::$booker->id));

		$this->booking = new EventModel();
		$this->booking->calendarId = CalendarModel::fetchPersonal(self::$booker->id);
		$this->booking->title = 'Job interview';
		$this->booking->description = 'Candidate for the job of the boss';
		$this->booking->privacy = EventModel::Private;
		$this->booking->timeZone = 'Europe/Amsterdam';
		$this->booking->start = new DateTime('2030-03-04 10:00:00');
		$this->booking->duration = 'PT1H';
		$this->booking->setValues(['participants' => [
			self::$booker->id => ['email' => self::$booker->email, 'name' => 'Booker', 'roles' => ['owner' => true, 'attendee' => true], 'kind' => 'individual', 'participationStatus' => 'accepted'],
			$this->resourceId() => ['email' => self::$roomAdmin->email, 'name' => self::$room->name, 'roles' => ['attendee' => true], 'kind' => 'resource', 'participationStatus' => 'needs-action'],
		]]);
		$this->assertTrue($this->booking->save(), $this->booking->getValidationErrorsAsString());

		$this->roomEventId = EventModel::find()->selectSingleValue('cce.id')
			->where(['cce.calendarId' => self::$room->id, 'eventdata.uid' => $this->booking->uid])->single();
		$this->assertNotEmpty($this->roomEventId, 'The booking is in the room calendar');
	}

	protected function tearDown(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		if ($this->booking) {
			$ids = EventModel::find()->selectSingleValue('cce.id')->where(['eventdata.uid' => $this->booking->uid])->all();
			if ($ids) {
				EventModel::delete(['id' => $ids]);
			}
		}
		go()->setAuthState($this->authState);
	}

	private function resourceId(): string
	{
		return 'Calendar:' . self::$room->id;
	}

	private function controller(int $userId): CalendarEvent
	{
		go()->setAuthState(new TemporaryState($userId));
		return (new \ReflectionClass(CalendarEvent::class))->newInstanceWithoutConstructor();
	}

	private function get(int $userId): array
	{
		return $this->controller($userId)->get(['ids' => [$this->roomEventId]])['list'][0];
	}

	public function testRoomAdminSeesParticipantsButNotTheDetails(): void
	{
		$event = $this->get(self::$roomAdmin->id);

		$this->assertArrayHasKey($this->resourceId(), $event['participants']);
		$this->assertSame('', $event['title']);
		$this->assertSame('', $event['description']);
	}

	/**
	 * Others with write permission on the room don't manage it
	 */
	public function testOtherUserOfRoomDoesNotSeeParticipants(): void
	{
		$this->assertArrayNotHasKey('participants', $this->get(self::$colleague->id));
	}

	/**
	 * The web client sends the whole participants map with the changed status
	 */
	public function testRoomAdminCanAcceptBooking(): void
	{
		$participants = $this->get(self::$roomAdmin->id)['participants'];
		$participants[$this->resourceId()]['participationStatus'] = 'accepted';

		$result = $this->controller(self::$roomAdmin->id)->set(['update' => [$this->roomEventId => ['participants' => $participants]]]);

		$this->assertArrayHasKey($this->roomEventId, (array) $result['updated'], json_encode($result['notUpdated'] ?? null));
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		$this->assertEquals('accepted', EventModel::findById($this->roomEventId)->participants[$this->resourceId()]->participationStatus);
	}

	/**
	 * Messages to the participants, like the reply of the resource, have the details, also when the admin who sends
	 * them may not see them
	 */
	public function testMessageOfRoomAdminHasDetails(): void
	{
		go()->setAuthState(new TemporaryState(self::$roomAdmin->id));
		$event = EventModel::findById($this->roomEventId);

		$ics = ICalendarHelper::toInvite('CANCEL', $event)->serialize();

		$this->assertStringContainsString('SUMMARY:Job interview', $ics);
	}

	/**
	 * Only the admins of the resource answer for it
	 */
	public function testOtherUserOfRoomCannotAcceptBooking(): void
	{
		$participants = $this->get(self::$roomAdmin->id)['participants'];
		$participants[$this->resourceId()]['participationStatus'] = 'accepted';

		$result = $this->controller(self::$colleague->id)->set(['update' => [$this->roomEventId => ['participants' => $participants]]]);

		$this->assertArrayHasKey($this->roomEventId, (array) $result['notUpdated']);
	}

	public function testRoomAdminCannotChangeOtherParticipants(): void
	{
		$participants = $this->get(self::$roomAdmin->id)['participants'];
		$participants[self::$booker->id]['participationStatus'] = 'declined';
		$result = $this->controller(self::$roomAdmin->id)->set(['update' => [$this->roomEventId => ['participants' => $participants]]]);
		$this->assertArrayHasKey($this->roomEventId, (array) $result['notUpdated']);

		$participants = $this->get(self::$roomAdmin->id)['participants'];
		$participants['guest@example.com'] = ['email' => 'guest@example.com', 'name' => 'Guest', 'roles' => ['attendee' => true], 'kind' => 'individual'];
		$result = $this->controller(self::$roomAdmin->id)->set(['update' => [$this->roomEventId => ['participants' => $participants]]]);
		$this->assertArrayHasKey($this->roomEventId, (array) $result['notUpdated']);
	}

	public function testRoomAdminCannotChangeTheEvent(): void
	{
		$result = $this->controller(self::$roomAdmin->id)->set(['update' => [$this->roomEventId => ['start' => '2030-03-04T11:00:00']]]);

		$this->assertArrayHasKey($this->roomEventId, (array) $result['notUpdated']);
	}
}
