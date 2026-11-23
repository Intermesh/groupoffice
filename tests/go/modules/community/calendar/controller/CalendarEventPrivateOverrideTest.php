<?php

namespace go\modules\community\calendar\controller;

use go\core\auth\TemporaryState;
use go\core\model\Acl;
use go\core\model\User;
use go\core\util\DateTime;
use go\core\util\JSON;
use go\modules\community\calendar\model\Calendar;
use go\modules\community\calendar\model\CalendarEvent as EventModel;
use go\modules\community\calendar\model\CalDAVBackend;
use go\modules\community\calendar\model\ICalendarHelper;
use Sabre\DAV;
use PHPUnit\Framework\TestCase;

/**
 * An exception of a private recurring event may have its own title, description and location. Others with access to
 * the calendar may not see them, also not through CalDAV, and must not erase them when they change the series.
 */
class CalendarEventPrivateOverrideTest extends TestCase
{
	const OVERRIDE = '2030-03-05T14:00:00';

	private $authState;
	private static User $viewer;
	private static Calendar $calendar;
	private ?EventModel $event = null;

	public static function setUpBeforeClass(): void
	{
		$authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		try {
			$name = uniqid('privoverride');
			self::$viewer = new User();
			self::$viewer->setValues(['username' => $name, 'displayName' => $name, 'email' => $name . '@example.com', 'password' => 'Pa55word!!xyz']);
			self::assertTrue(self::$viewer->save(), self::$viewer->getValidationErrorsAsString());

			// The admin owns the calendar, the viewer may change and delete its events
			self::$calendar = new Calendar();
			self::$calendar->name = uniqid('privoverride');
			self::$calendar->setOwnerId(User::ID_SUPER_ADMIN);
			self::$calendar->setAcl([self::$viewer->getPersonalGroup()->id => Acl::LEVEL_DELETE]);
			self::assertTrue(self::$calendar->save(), self::$calendar->getValidationErrorsAsString());
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
		if (isset(self::$calendar)) {
			EventModel::delete(['calendarId' => self::$calendar->id]);
			Calendar::delete(['id' => self::$calendar->id]);
		}
		if (isset(self::$viewer)) {
			User::delete(['id' => self::$viewer->id]);
		}
		go()->setAuthState($authState);
	}

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));

		// Daily and private. One occurrence is moved and has its own details.
		$event = new EventModel();
		$event->calendarId = self::$calendar->id;
		$event->title = 'Private plan';
		$event->description = 'Series secret';
		$event->timeZone = 'Europe/Amsterdam';
		$event->start = new DateTime('2030-03-04 14:00:00');
		$event->duration = 'PT1H';
		$event->privacy = EventModel::Private;
		$event->setRecurrenceRule((object) ['frequency' => 'daily', 'count' => 5]);
		$this->assertTrue($event->save(), $event->getValidationErrorsAsString());

		$this->event = EventModel::findById($event->id);
		$this->event->setValues(['recurrenceOverrides' => [
			self::OVERRIDE => ['start' => '2030-03-05T16:00:00', 'title' => 'Override title', 'description' => 'Override secret', 'location' => 'Override room']
		]]);
		$this->assertTrue($this->event->save(), $this->event->getValidationErrorsAsString());
	}

	protected function tearDown(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		if ($this->event) {
			EventModel::delete(['id' => $this->event->id]);
		}
		go()->setAuthState($this->authState);
	}

	/**
	 * The controller without its module check in the constructor
	 */
	private function get(int $userId): array
	{
		go()->setAuthState(new TemporaryState($userId));
		$controller = (new \ReflectionClass(CalendarEvent::class))->newInstanceWithoutConstructor();
		return $controller->get(['ids' => [$this->event->id]])['list'][0];
	}

	public function testOwnerSeesOverrideDetails(): void
	{
		$override = $this->get(User::ID_SUPER_ADMIN)['recurrenceOverrides'][self::OVERRIDE];

		$this->assertEquals('Override secret', $override['description']);
	}

	public function testOverrideDetailsAreHiddenFromOthers(): void
	{
		$event = $this->get(self::$viewer->id);

		$this->assertSame('', $event['description']);
		$this->assertArrayHasKey(self::OVERRIDE, $event['recurrenceOverrides']);
		$override = $event['recurrenceOverrides'][self::OVERRIDE];
		foreach (EventModel::PrivateDetails as $prop) {
			$this->assertArrayNotHasKey($prop, $override);
		}
	}

	public function testOverrideDetailsAreHiddenInICalendarExport(): void
	{
		go()->setAuthState(new TemporaryState(self::$viewer->id));
		$ics = ICalendarHelper::toVObject(EventModel::findById($this->event->id))->serialize();

		$this->assertStringContainsString('RECURRENCE-ID', $ics);
		$this->assertStringNotContainsString('Override', $ics);
		$this->assertStringNotContainsString('secret', $ics);
	}

	/**
	 * The client sends the whole map back, for example when it removes one occurrence
	 */
	public function testOthersKeepOverrideDetailsWhenSavingTheOverrides(): void
	{
		$overrides = $this->get(self::$viewer->id)['recurrenceOverrides'];
		$overrides['2030-03-06T14:00:00'] = ['excluded' => true];

		$event = EventModel::findById($this->event->id);
		$event->setValues(['recurrenceOverrides' => $overrides]);
		$this->assertTrue($event->save(), $event->getValidationErrorsAsString());

		$this->assertOverrideDetailsKept();
	}

	/**
	 * The client sends the patch of one occurrence when it changes only that occurrence
	 */
	public function testOthersKeepOverrideDetailsWhenChangingTheOccurrence(): void
	{
		$patch = $this->get(self::$viewer->id)['recurrenceOverrides'][self::OVERRIDE];
		$patch['start'] = '2030-03-05T17:00:00';

		// Like CalendarEvent/set applies an update
		$event = JSON::patch(EventModel::findById($this->event->id), ['recurrenceOverrides/' . self::OVERRIDE => $patch]);
		$this->assertTrue($event->save(), $event->getValidationErrorsAsString());

		$override = $this->assertOverrideDetailsKept();
		$this->assertEquals('2030-03-05T17:00:00', $override['start']);
	}

	private function assertOverrideDetailsKept(): array
	{
		$override = $this->get(User::ID_SUPER_ADMIN)['recurrenceOverrides'][self::OVERRIDE];
		$this->assertEquals('Override title', $override['title']);
		$this->assertEquals('Override secret', $override['description']);
		$this->assertEquals('Override room', $override['location']);
		return $override;
	}

	/**
	 * Add participants to the event and to the override, as the admin
	 */
	private function addParticipants(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		$admin = User::findById(User::ID_SUPER_ADMIN, ['email']);
		$event = EventModel::findById($this->event->id);
		$event->setValues(['participants' => [
			User::ID_SUPER_ADMIN => ['email' => $admin->email, 'name' => 'Admin', 'roles' => ['owner' => true, 'attendee' => true], 'kind' => 'individual', 'participationStatus' => 'accepted'],
			'guest@example.com' => ['email' => 'guest@example.com', 'name' => 'Guest', 'roles' => ['attendee' => true], 'kind' => 'individual'],
		]]);
		$this->assertTrue($event->save(), $event->getValidationErrorsAsString());
		$event = EventModel::findById($this->event->id);
		$event->setValues(['recurrenceOverrides' => [self::OVERRIDE => [
			'start' => '2030-03-05T16:00:00', 'title' => 'Override title',
			'participants/guest@example.com' => ['email' => 'guest@example.com', 'name' => 'Guest', 'roles' => ['attendee' => true], 'kind' => 'individual', 'participationStatus' => 'declined']
		]]]);
		$this->assertTrue($event->save(), $event->getValidationErrorsAsString());
		$this->event = $event;
	}

	private function controller(int $userId): CalendarEvent
	{
		go()->setAuthState(new TemporaryState($userId));
		return (new \ReflectionClass(CalendarEvent::class))->newInstanceWithoutConstructor();
	}

	public function testParticipantsAreHiddenFromOthers(): void
	{
		$this->addParticipants();
		$this->assertArrayHasKey('participants', $this->get(User::ID_SUPER_ADMIN));

		$event = $this->get(self::$viewer->id);
		$this->assertArrayNotHasKey('participants', $event);
		$this->assertArrayNotHasKey('replyTo', $event);
		$this->assertStringNotContainsString('guest@example.com', json_encode($event));

		$occurrence = $this->controller(self::$viewer->id)->get(['ids' => [$this->event->id . '/' . self::OVERRIDE]])['list'][0];
		$this->assertStringNotContainsString('guest@example.com', json_encode($occurrence));
	}

	public function testParticipantsAreHiddenInICalendarExportButNotInInvitations(): void
	{
		$this->addParticipants();

		go()->setAuthState(new TemporaryState(self::$viewer->id));
		$ics = ICalendarHelper::toVObject(EventModel::findById($this->event->id))->serialize();
		$this->assertStringNotContainsString('ATTENDEE', $ics);
		$this->assertStringNotContainsString('ORGANIZER', $ics);

		// Invitations go to the participants, also when sent by someone who may not see them
		$event = EventModel::findById($this->event->id);
		$this->assertStringContainsString('ATTENDEE;CN=Guest', ICalendarHelper::toInvite('CANCEL', $event)->serialize());
	}

	/**
	 * Others don't see the details, so they can't change the event, only their own properties
	 */
	public function testPrivateEventOfOthersIsReadOnly(): void
	{
		$id = $this->event->id;
		$result = $this->controller(self::$viewer->id)->set(['update' => [$id => ['start' => '2030-03-04T15:00:00']]]);
		$this->assertArrayHasKey($id, (array) $result['notUpdated']);

		// Making it public in the same update doesn't help
		$result = $this->controller(self::$viewer->id)->set(['update' => [$id => ['privacy' => 'public', 'start' => '2030-03-04T15:00:00']]]);
		$this->assertArrayHasKey($id, (array) $result['notUpdated']);

		$result = $this->controller(self::$viewer->id)->set(['update' => [$id => ['color' => '00FF00']]]);
		$this->assertArrayHasKey($id, (array) $result['updated']);

		$result = $this->controller(self::$viewer->id)->set(['destroy' => [$id]]);
		$this->assertArrayHasKey($id, (array) $result['notDestroyed']);

		$this->assertEquals('2030-03-04T14:00:00', $this->get(User::ID_SUPER_ADMIN)['start']);
	}

	public function testOwnerCanChangePrivateEvent(): void
	{
		$id = $this->event->id;
		$result = $this->controller(User::ID_SUPER_ADMIN)->set(['update' => [$id => ['start' => '2030-03-04T15:00:00']]]);

		$this->assertArrayHasKey($id, (array) $result['updated']);
	}

	/**
	 * A CalDAV client of someone else gets the event without the participants and title. When it sends the event back,
	 * only the user's own data may change.
	 */
	public function testPrivateEventOfOthersIsReadOnlyInCalDAV(): void
	{
		go()->setAuthState(new TemporaryState(self::$viewer->id));
		$event = EventModel::findById($this->event->id);
		$this->assertFalse($event->mayChangeEventData());

		$uri = go()->getDbConnection()->selectSingleValue('uri')->from('calendar_calendar_event')
			->where(['id' => $this->event->id])->single();
		try {
			(new CalDAVBackend())->deleteCalendarObject('c-' . self::$calendar->id, $uri);
			$this->fail('The private event of someone else was deleted');
		} catch (DAV\Exception\Forbidden $e) {
			$this->assertNotNull(EventModel::findById($this->event->id));
		}
	}

	/**
	 * The invitation goes to the participants, who may see the details, also when someone else sends it
	 */
	public function testInvitationHasDetailsWhenSentByOthers(): void
	{
		$this->addParticipants();

		go()->setAuthState(new TemporaryState(self::$viewer->id));
		$event = EventModel::findById($this->event->id);
		$ics = ICalendarHelper::toInvite('CANCEL', $event)->serialize();

		$this->assertStringContainsString('SUMMARY:Private plan', $ics);
		$this->assertStringContainsString('DESCRIPTION:Series secret', $ics);
		$this->assertStringNotContainsString('(Private)', $ics);
	}
}
