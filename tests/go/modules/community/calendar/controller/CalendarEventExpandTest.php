<?php

namespace go\modules\community\calendar\controller;

use go\core\auth\TemporaryState;
use go\core\jmap\exception\InvalidArguments;
use go\core\model\Acl;
use go\core\model\User;
use go\core\util\DateTime;
use go\modules\community\calendar\model\Calendar;
use go\modules\community\calendar\model\CalendarEvent as EventModel;
use PHPUnit\Framework\TestCase;

/**
 * CalendarEvent/query with "expandRecurrences" returns the occurrences in a period, and CalendarEvent/get accepts
 * their IDs. Without the flag both work as before, as the web client uses them.
 */
class CalendarEventExpandTest extends TestCase
{
	const TZ = 'Europe/Amsterdam';

	private $authState;
	private static User $viewer;
	private static User $outsider;
	private static Calendar $calendar;
	private static EventModel $single;
	private static EventModel $daily;
	private static EventModel $weekly;
	private static EventModel $private;

	public static function setUpBeforeClass(): void
	{
		$authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		try {
			self::$viewer = self::createUser('expandviewer');
			self::$outsider = self::createUser('expandoutsider');

			self::$calendar = new Calendar();
			self::$calendar->name = uniqid('expand');
			self::$calendar->setAcl([self::$viewer->getPersonalGroup()->id => Acl::LEVEL_READ]);
			self::assertTrue(self::$calendar->save(), self::$calendar->getValidationErrorsAsString());

			self::$single = self::createEvent('Single', '2030-03-05 10:00:00', 'PT1H');

			// Daily at 9, one moved to the afternoon and one excluded
			self::$daily = self::createEvent('Daily', '2030-03-01 09:00:00', 'PT30M', ['frequency' => 'daily']);
			self::$daily = EventModel::findById(self::$daily->id);
			self::$daily->setValues(['recurrenceOverrides' => [
				'2030-03-06T09:00:00' => ['start' => '2030-03-06T15:00:00', 'title' => 'Moved'],
				'2030-03-07T09:00:00' => ['excluded' => true]
			]]);
			self::assertTrue(self::$daily->save(), self::$daily->getValidationErrorsAsString());

			// Fridays, starting a month earlier
			self::$weekly = self::createEvent('Weekly', '2030-02-01 12:00:00', 'PT1H', ['frequency' => 'weekly']);

			// Private, with an override that has its own title
			self::$private = self::createEvent('Private plan', '2030-03-04 14:00:00', 'PT1H', ['frequency' => 'daily'], EventModel::Private);
			self::$private = EventModel::findById(self::$private->id);
			self::$private->setValues(['recurrenceOverrides' => [
				'2030-03-05T14:00:00' => ['title' => 'Private override']
			]]);
			self::assertTrue(self::$private->save(), self::$private->getValidationErrorsAsString());
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
		$userIds = [];
		foreach (['viewer', 'outsider'] as $prop) {
			if (isset(self::$$prop)) {
				$userIds[] = self::$$prop->id;
			}
		}
		if ($userIds) {
			User::delete(['id' => $userIds]);
		}
		go()->setAuthState($authState);
	}

	private static function createUser(string $prefix): User
	{
		$name = uniqid($prefix);
		$user = new User();
		$user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		self::assertTrue($user->save(), $user->getValidationErrorsAsString());
		return $user;
	}

	private static function createEvent(string $title, string $start, string $duration, ?array $rule = null, string $privacy = EventModel::Public): EventModel
	{
		$event = new EventModel();
		$event->calendarId = self::$calendar->id;
		$event->title = $title;
		$event->timeZone = self::TZ;
		$event->start = new DateTime($start);
		$event->duration = $duration;
		$event->privacy = $privacy;
		if ($rule) {
			$event->setRecurrenceRule((object) $rule);
		}
		self::assertTrue($event->save(), $event->getValidationErrorsAsString());
		return $event;
	}

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
	}

	protected function tearDown(): void
	{
		go()->setAuthState($this->authState);
	}

	/**
	 * The controller without its module check in the constructor, so the users only need permission on the calendar
	 */
	private function controller(?int $userId = null): CalendarEvent
	{
		if (isset($userId)) {
			go()->setAuthState(new TemporaryState($userId));
			// The calendar of an event is cached with the permission level of the user that loaded it first
			(new \ReflectionProperty(\go\core\acl\model\AclItemEntity::class, 'aclEntityCache'))->setValue(null, []);
		}
		return (new \ReflectionClass(CalendarEvent::class))->newInstanceWithoutConstructor();
	}

	private function filter(string $after = '2030-03-04T00:00:00', string $before = '2030-03-08T00:00:00'): array
	{
		return ['inCalendars' => [self::$calendar->id], 'after' => $after, 'before' => $before];
	}

	private function expanded(array $params = [], ?int $userId = null): array
	{
		$params += ['filter' => $this->filter(), 'expandRecurrences' => true, 'timeZone' => self::TZ];
		return $this->controller($userId)->query($params)->getArray();
	}

	public function testWithoutTheFlagTheEventsAreReturned(): void
	{
		$ids = $this->controller()->query(['filter' => $this->filter()])['ids'];

		$this->assertEqualsCanonicalizing([self::$single->id, self::$daily->id, self::$weekly->id, self::$private->id], $ids);
	}

	public function testOccurrencesInThePeriodSortedByStart(): void
	{
		$daily = self::$daily->id;
		$private = self::$private->id;

		$this->assertSame([
			$daily . '/2030-03-04T09:00:00',
			$private . '/2030-03-04T14:00:00',
			$daily . '/2030-03-05T09:00:00',
			(string) self::$single->id,
			$private . '/2030-03-05T14:00:00',
			$private . '/2030-03-06T14:00:00',
			$daily . '/2030-03-06T09:00:00', // moved to 15:00
			$private . '/2030-03-07T14:00:00'
			// the daily one of the 7th is excluded and the weekly one is on Friday the 8th
		], $this->expanded()['ids']);
	}

	public function testDescendingPositionLimitAndTotal(): void
	{
		$all = $this->expanded()['ids'];

		$response = $this->expanded(['sort' => [['property' => 'start', 'isAscending' => false]], 'position' => 1, 'limit' => 2, 'calculateTotal' => true, 'calculateHasMore' => true]);

		$this->assertSame(array_slice(array_reverse($all), 1, 2), $response['ids']);
		$this->assertEquals(count($all), $response['total']);
		$this->assertTrue($response['hasMore']);
	}

	public function testGetOccurrences(): void
	{
		$moved = self::$daily->id . '/2030-03-06T09:00:00';
		$normal = self::$daily->id . '/2030-03-05T09:00:00';
		$excluded = self::$daily->id . '/2030-03-07T09:00:00';
		$notAnOccurrence = self::$daily->id . '/2030-03-05T10:00:00';
		$invalid = self::$daily->id . '/yesterday';

		$response = $this->controller()->get(['ids' => [$moved, (string) self::$single->id, $normal, $excluded, $notAnOccurrence, $invalid]]);

		$this->assertSame([$moved, (string) self::$single->id, $normal], array_map('strval', array_column($response['list'], 'id')));
		$this->assertEqualsCanonicalizing([$excluded, $notAnOccurrence, $invalid], $response['notFound']);

		[$movedArr, , $normalArr] = $response['list'];
		$this->assertEquals('Moved', $movedArr['title']);
		$this->assertEquals('2030-03-06T15:00:00', $movedArr['start']);
		$this->assertEquals('2030-03-06T09:00:00', $movedArr['recurrenceId']);
		$this->assertArrayNotHasKey('recurrenceRule', $movedArr);
		$this->assertArrayNotHasKey('recurrenceOverrides', $movedArr);
		$this->assertEquals('Daily', $normalArr['title']);
		$this->assertEquals('2030-03-05T09:00:00', $normalArr['start']);
	}

	public function testGetOccurrenceWithProperties(): void
	{
		$id = self::$daily->id . '/2030-03-05T09:00:00';

		$arr = $this->controller()->get(['ids' => [$id], 'properties' => ['title', 'start']])['list'][0];

		$this->assertEqualsCanonicalizing(['id', 'title', 'start'], array_keys($arr));
	}

	public function testPrivateDetailsOfOverridesAreHidden(): void
	{
		$id = self::$private->id . '/2030-03-05T14:00:00';

		$owner = $this->controller()->get(['ids' => [$id]])['list'][0];
		$this->assertEquals('Private override', $owner['title']);

		$viewer = $this->controller(self::$viewer->id)->get(['ids' => [$id]])['list'][0];
		$this->assertSame('', $viewer['title']);
		$this->assertEquals('2030-03-05T14:00:00', $viewer['start']);
	}

	public function testOccurrencesOfACalendarWithoutPermission(): void
	{
		$this->assertEmpty($this->expanded([], self::$outsider->id)['ids']);

		$id = self::$daily->id . '/2030-03-05T09:00:00';
		$response = $this->controller(self::$outsider->id)->get(['ids' => [$id]]);
		$this->assertEmpty($response['list']);
		$this->assertEquals([$id], $response['notFound']);
	}

	public function testFilterNeedsAfterAndBefore(): void
	{
		$this->expectException(InvalidArguments::class);
		$this->expanded(['filter' => ['inCalendars' => [self::$calendar->id], 'after' => '2030-03-04T00:00:00']]);
	}

	public function testFilterOperatorIsRefused(): void
	{
		$this->expectException(InvalidArguments::class);
		$this->expanded(['filter' => ['operator' => 'AND', 'conditions' => [$this->filter()]]]);
	}

	public function testOnlySortByStart(): void
	{
		$this->expectException(InvalidArguments::class);
		$this->expanded(['sort' => [['property' => 'title']]]);
	}

	public function testTooManyOccurrences(): void
	{
		$this->expectException(InvalidArguments::class);
		$this->expectExceptionMessage('Use a shorter period');
		$this->expanded(['filter' => $this->filter('2030-01-01T00:00:00', '2034-01-01T00:00:00')]);
	}
}
