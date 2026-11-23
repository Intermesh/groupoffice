<?php

namespace go\modules\community\calendar\controller;

use go\core\auth\TemporaryState;
use go\core\model\Acl;
use go\core\model\User;
use go\core\util\DateTime;
use go\modules\community\calendar\model\Calendar as CalendarModel;
use go\modules\community\calendar\model\CalendarEvent as EventModel;
use go\modules\community\calendar\model\ResourceGroup;
use PHPUnit\Framework\TestCase;

/**
 * Users with "Read free/busy" permission on a calendar see its busy events without any details: not what they are
 * about, nor who takes part. They don't see secret, cancelled or "free" events, can't find events by their details and
 * can't change anything. Users with read permission see everything as before. #38255
 */
class CalendarEventFreeBusyTest extends TestCase
{
	const RECURRENCE_ID = '2030-03-05T10:00:00';

	private static ?User $freeBusyUser = null;
	private static ?User $reader = null;
	private static ?User $outsider = null;
	private static ?CalendarModel $calendar = null;
	/** @var array<string, string> event IDs by name */
	private static array $events = [];
	private static bool $sendMessages;

	private $authState;

	public static function setUpBeforeClass(): void
	{
		$authState = go()->getAuthState();
		self::$sendMessages = EventModel::$sendSchedulingMessages;
		EventModel::$sendSchedulingMessages = false;
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		try {
			self::$freeBusyUser = self::createUser('freebusy');
			self::$reader = self::createUser('fbreader');
			self::$outsider = self::createUser('fboutsider');

			self::$calendar = new CalendarModel();
			self::$calendar->name = uniqid('freebusy');
			self::$calendar->setOwnerId(User::ID_SUPER_ADMIN);
			self::$calendar->setPublish(true);
			self::$calendar->setAcl([
				self::$freeBusyUser->getPersonalGroup()->id => CalendarModel::LEVEL_FREEBUSY,
				self::$reader->getPersonalGroup()->id => Acl::LEVEL_READ,
			]);
			self::assertTrue(self::$calendar->save(), self::$calendar->getValidationErrorsAsString());

			$admin = User::findById(User::ID_SUPER_ADMIN, ['email']);
			self::$events['busy'] = self::createEvent('Budget talks', [
				'description' => 'Layoffs', 'location' => 'Board room',
				'participants' => [
					User::ID_SUPER_ADMIN => ['email' => $admin->email, 'name' => 'Admin', 'roles' => ['owner' => true, 'attendee' => true], 'kind' => 'individual', 'participationStatus' => 'accepted'],
					'guest@example.com' => ['email' => 'guest@example.com', 'name' => 'Guest', 'roles' => ['attendee' => true], 'kind' => 'individual'],
				]
			]);
			self::$events['secret'] = self::createEvent('Secret plan', ['privacy' => EventModel::Secret]);
			self::$events['cancelled'] = self::createEvent('Cancelled plan', ['status' => EventModel::Cancelled]);
			// The owner of the calendar, the admin, is free during this event
			self::$events['free'] = self::createEvent('Free plan', ['freeBusyStatus' => EventModel::FREEBUSY_FREE]);
			self::$events['recurring'] = self::createEvent('Daily plan', [
				'start' => new DateTime('2030-03-04 10:00:00'),
				'recurrenceRule' => ['frequency' => 'daily', 'count' => 5]
			]);
			$recurring = EventModel::findById(self::$events['recurring']);
			$recurring->setValues(['recurrenceOverrides' => [
				self::RECURRENCE_ID => ['start' => '2030-03-05T12:00:00', 'title' => 'Override plan', 'location' => 'Override room']
			]]);
			self::assertTrue($recurring->save(), $recurring->getValidationErrorsAsString());
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
			CalendarModel::delete(['id' => self::$calendar->id]);
		}
		$userIds = array_filter([self::$freeBusyUser?->id, self::$reader?->id, self::$outsider?->id]);
		if ($userIds) {
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

	private static function createEvent(string $title, array $values = []): string
	{
		$event = new EventModel();
		$event->calendarId = self::$calendar->id;
		$event->title = $title;
		$event->timeZone = 'Europe/Amsterdam';
		$event->start = new DateTime('2030-03-04 10:00:00');
		$event->duration = 'PT1H';
		$event->setValues($values);
		self::assertTrue($event->save(), $event->getValidationErrorsAsString());
		return $event->id;
	}

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
	}

	protected function tearDown(): void
	{
		go()->setAuthState($this->authState);
	}

	/**
	 * A controller without its module check in the constructor, acting as the user
	 */
	private function controller(string $class, int $userId): object
	{
		go()->setAuthState(new TemporaryState($userId));
		return (new \ReflectionClass($class))->newInstanceWithoutConstructor();
	}

	private function get(int $userId, array $ids): array
	{
		$result = $this->controller(CalendarEvent::class, $userId)->get(['ids' => $ids]);
		return ['list' => array_column((array) $result['list'], null, 'id'), 'notFound' => $result['notFound']];
	}

	private function query(int $userId, array $filter, array $sort = []): array
	{
		if (!isset($filter['operator'])) {
			$filter = array_merge(['inCalendars' => [self::$calendar->id], 'after' => '2030-03-01', 'before' => '2030-03-31'], $filter);
		}
		return $this->controller(CalendarEvent::class, $userId)->query(['filter' => $filter, 'sort' => $sort])['ids'];
	}

	public function testFreeBusyUserGetsBusyEventWithoutDetails(): void
	{
		$event = $this->get(self::$freeBusyUser->id, [self::$events['busy']])['list'][self::$events['busy']] ?? null;

		$this->assertNotNull($event);
		$this->assertEquals('2030-03-04T10:00:00', $event['start']);
		$this->assertEquals('PT1H', $event['duration']);
		$this->assertSame([], array_diff(array_keys($event), EventModel::BusyOnlyProperties), 'Only the busy time is returned');
		$this->assertStringNotContainsString('Budget', json_encode($event));
		$this->assertStringNotContainsString('guest@example.com', json_encode($event));
	}

	public function testFreeBusyUserFindsOnlyBusyEvents(): void
	{
		$ids = $this->query(self::$freeBusyUser->id, []);

		$this->assertContains(self::$events['busy'], $ids);
		$this->assertContains(self::$events['recurring'], $ids);
		$this->assertNotContains(self::$events['secret'], $ids);
		$this->assertNotContains(self::$events['cancelled'], $ids);
		$this->assertNotContains(self::$events['free'], $ids);
	}

	/**
	 * The web client combines its filters, like the calendars and the period, with AND
	 */
	public function testFreeBusyUserFindsBusyEventsWithFilterOfWebClient(): void
	{
		$ids = $this->query(self::$freeBusyUser->id, ['operator' => 'AND', 'conditions' => [
			['inCalendars' => [self::$calendar->id]],
			['after' => '2030-03-01', 'before' => '2030-03-31']
		]], [['property' => 'start', 'isAscending' => true]]);

		$this->assertContains(self::$events['busy'], $ids);
		$this->assertNotContains(self::$events['secret'], $ids);
	}

	/**
	 * A resource shares the permissions of its calendar with its principal. With free / busy permission the user can
	 * find the resource in the participant field to book it.
	 */
	public function testFreeBusyUserGetsResourcePrincipal(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		$group = new ResourceGroup();
		$group->name = uniqid('fbrooms');
		$group->defaultOwnerId = User::ID_SUPER_ADMIN;
		$this->assertTrue($group->save(), $group->getValidationErrorsAsString());
		$resource = null;
		try {
			$resource = new CalendarModel();
			$resource->name = uniqid('fbroom');
			$resource->groupId = $group->id;
			$resource->setAcl([self::$freeBusyUser->getPersonalGroup()->id => CalendarModel::LEVEL_FREEBUSY]);
			$this->assertTrue($resource->save(), $resource->getValidationErrorsAsString());
			$principalId = 'Calendar:' . $resource->id;

			$found = $this->controller(\go\core\controller\Principal::class, self::$freeBusyUser->id)
				->get(['ids' => [$principalId]]);
			$this->assertSame($principalId, $found['list'][0]['id'] ?? null);

			$notFound = $this->controller(\go\core\controller\Principal::class, self::$outsider->id)
				->get(['ids' => [$principalId]]);
			$this->assertSame([], (array) $notFound['list']);
		} finally {
			go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
			if ($resource && $resource->id) {
				CalendarModel::delete(['id' => $resource->id]);
			}
			ResourceGroup::delete(['id' => $group->id]);
		}
	}

	/**
	 * Which events a search finds would tell what they are about
	 */
	public function testFreeBusyUserCannotSearchOrSortOnDetails(): void
	{
		$this->assertSame([], $this->query(self::$freeBusyUser->id, ['text' => 'Budget']));
		$this->assertSame([], $this->query(self::$freeBusyUser->id, [], [['property' => 'title']]));
		// A lower permission level in the filter doesn't find more
		$this->assertSame([], $this->query(self::$freeBusyUser->id, ['text' => 'Budget', 'permissionLevel' => CalendarModel::LEVEL_FREEBUSY]));
		$this->assertSame([], $this->query(self::$freeBusyUser->id, ['operator' => 'AND', 'conditions' => [['inCalendars' => [self::$calendar->id]], ['text' => 'Budget']]]));
	}

	public function testFreeBusyUserDoesNotGetEventsThatAreNotBusy(): void
	{
		$result = $this->get(self::$freeBusyUser->id, [self::$events['secret'], self::$events['cancelled'], self::$events['free']]);

		$this->assertSame([], $result['list']);
		$this->assertCount(3, $result['notFound']);
	}

	public function testFreeBusyUserGetsOccurrenceWithoutOverrideDetails(): void
	{
		$id = self::$events['recurring'] . '/' . self::RECURRENCE_ID;
		$occurrence = $this->get(self::$freeBusyUser->id, [$id])['list'][$id] ?? null;

		$this->assertNotNull($occurrence);
		$this->assertEquals('2030-03-05T12:00:00', $occurrence['start']);
		$this->assertStringNotContainsString('Override', json_encode($occurrence));

		$series = $this->get(self::$freeBusyUser->id, [self::$events['recurring']])['list'][self::$events['recurring']];
		$this->assertSame(['start' => '2030-03-05T12:00:00'], $series['recurrenceOverrides'][self::RECURRENCE_ID]);
	}

	public function testFreeBusyUserCannotChangeEvents(): void
	{
		$result = $this->controller(CalendarEvent::class, self::$freeBusyUser->id)->set([
			'update' => [self::$events['busy'] => ['color' => '00FF00', 'useDefaultAlerts' => false]]
		]);

		$this->assertArrayHasKey(self::$events['busy'], (array) $result['notUpdated']);
	}

	public function testReaderSeesDetails(): void
	{
		$events = $this->get(self::$reader->id, [self::$events['busy'], self::$events['free']])['list'];

		$this->assertEquals('Budget talks', $events[self::$events['busy']]['title']);
		$this->assertEquals('Board room', $events[self::$events['busy']]['location']);
		$this->assertArrayHasKey('participants', $events[self::$events['busy']]);
		$this->assertEquals('Free plan', $events[self::$events['free']]['title']);
		$this->assertContains(self::$events['busy'], $this->query(self::$reader->id, ['text' => 'Budget']));
	}

	public function testOutsiderSeesNothing(): void
	{
		$this->assertSame([], $this->query(self::$outsider->id, []));
		$this->assertSame([], $this->get(self::$outsider->id, [self::$events['busy']])['list']);
	}

	/**
	 * The calendar is listed with the free / busy level only when the client asks for it, and without the public URL
	 * that would show the details
	 */
	public function testFreeBusyUserGetsCalendarWithoutPublicUrl(): void
	{
		$controller = $this->controller(Calendar::class, self::$freeBusyUser->id);

		$this->assertNotContains(self::$calendar->id, $controller->query([])['ids']);
		$this->assertContains(self::$calendar->id, $controller->query(['filter' => ['permissionLevel' => CalendarModel::LEVEL_FREEBUSY]])['ids']);

		$calendar = $controller->get(['ids' => [self::$calendar->id]])['list'][0];
		$this->assertNull($calendar['publishKey']);
		$this->assertNull($calendar['acl']);
		$this->assertFalse($calendar['myRights']['mayReadItems']);
		$this->assertTrue($calendar['myRights']['mayReadFreeBusy']);

		$this->assertNotNull($this->controller(Calendar::class, self::$reader->id)->get(['ids' => [self::$calendar->id]])['list'][0]['publishKey']);
	}

	public function testOutsiderDoesNotGetCalendar(): void
	{
		$result = $this->controller(Calendar::class, self::$outsider->id)->get(['ids' => [self::$calendar->id]]);

		$this->assertSame([], (array) $result['list']);
	}

	/**
	 * An alert shows the title of the event
	 */
	public function testNoAlertsForFreeBusySubscriber(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		$event = EventModel::findById(self::$events['busy']);
		$entityTypeId = EventModel::entityType()->getId();
		try {
			foreach ([self::$freeBusyUser, self::$reader] as $user) {
				go()->setAuthState(new TemporaryState($user->id));
				$calendar = CalendarModel::findById(self::$calendar->id);
				$calendar->setValues(['defaultAlertsWithTime' => ['1' => ['trigger' => ['offset' => '-PT15M', 'relativeTo' => 'start']]]]);
				$this->assertTrue($calendar->save(), $calendar->getValidationErrorsAsString());
				$event->rescheduleAlertsFor($user->id);
			}

			$alertUsers = array_map('intval', go()->getDbConnection()->selectSingleValue('userId')->from('core_alert')
				->where(['entityTypeId' => $entityTypeId, 'entityId' => $event->id])->all());
			$this->assertNotContains((int) self::$freeBusyUser->id, $alertUsers);
			$this->assertContains((int) self::$reader->id, $alertUsers);
		} finally {
			go()->getDbConnection()->delete('core_alert', ['entityTypeId' => $entityTypeId, 'entityId' => $event->id])->execute();
		}
	}
}
