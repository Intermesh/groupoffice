<?php

namespace go\modules\community\calendar\model;

use go\core\auth\TemporaryState;
use go\core\model\Acl;
use go\core\model\User;
use go\core\util\DateTime;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The "title", "description" and "location" filters match only that property. Others may not use them on the details of a
 * private event, or of an event in a calendar of which they may only see the free / busy time, because the match
 * would tell them what the hidden text contains.
 */
class CalendarEventDetailFilterTest extends TestCase
{
	private $authState;
	private static ?User $viewer = null;
	/** @var Calendar[] the admin owns them, the viewer may read "read" and see the free / busy time of "freebusy" */
	private static array $calendars = [];
	/** @var array<string, int> event data id by key */
	private static array $eventIds = [];

	public static function setUpBeforeClass(): void
	{
		$authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		try {
			$name = uniqid('detailfilter');
			self::$viewer = new User();
			self::$viewer->setValues(['username' => $name, 'displayName' => $name, 'email' => $name . '@example.com', 'password' => 'Pa55word!!xyz']);
			if (!self::$viewer->save()) {
				throw new \Exception(self::$viewer->getValidationErrorsAsString());
			}

			$levels = ['read' => Acl::LEVEL_READ, 'freebusy' => Calendar::LEVEL_FREEBUSY];
			foreach ($levels as $key => $level) {
				$calendar = new Calendar();
				$calendar->name = uniqid('detailfilter');
				$calendar->setOwnerId(User::ID_SUPER_ADMIN);
				$calendar->setAcl([self::$viewer->getPersonalGroup()->id => $level]);
				if (!$calendar->save()) {
					throw new \Exception($calendar->getValidationErrorsAsString());
				}
				self::$calendars[$key] = $calendar;
			}

			$events = [
				'public' => ['calendar' => 'read', 'privacy' => CalendarEvent::Public, 'description' => 'Agenda detailfiltertest', 'location' => 'Room detailfiltertest'],
				'private' => ['calendar' => 'read', 'privacy' => CalendarEvent::Private, 'description' => 'Agenda detailfiltertest', 'location' => 'Room detailfiltertest'],
				'other' => ['calendar' => 'read', 'privacy' => CalendarEvent::Public, 'description' => 'Something else', 'location' => 'Elsewhere'],
				'freebusy' => ['calendar' => 'freebusy', 'privacy' => CalendarEvent::Public, 'description' => 'Agenda detailfiltertest', 'location' => 'Room detailfiltertest'],
			];
			foreach ($events as $key => $values) {
				$event = new CalendarEvent();
				$event->calendarId = self::$calendars[$values['calendar']]->id;
				$event->title = ($key === 'other' ? 'Lunch' : 'Meeting') . ' detailfiltertest';
				$event->description = $values['description'];
				$event->location = $values['location'];
				$event->privacy = $values['privacy'];
				$event->timeZone = 'Europe/Amsterdam';
				$event->start = (new DateTime('+1 week'))->setTime(12, 0, 0, 0);
				$event->duration = 'PT1H';
				if (!$event->save()) {
					throw new \Exception($event->getValidationErrorsAsString());
				}
				self::$eventIds[$key] = (int)$event->customFieldsModelId();
			}
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
		foreach (self::$calendars as $calendar) {
			CalendarEvent::delete(['calendarId' => $calendar->id]);
			Calendar::delete(['id' => $calendar->id]);
		}
		if (self::$viewer && isset(self::$viewer->id)) {
			User::delete(['id' => self::$viewer->id]);
		}
		go()->setAuthState($authState);
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
	 * @return int[] sorted event data ids in the test calendars that match the filter for the user
	 */
	private function find(int $userId, array $filter): array
	{
		go()->setAuthState(new TemporaryState($userId));
		$calendarIds = array_map(fn(Calendar $c) => $c->id, array_values(self::$calendars));
		$ids = array_map('intval', CalendarEvent::find()
			->filter(['inCalendars' => $calendarIds] + $filter)
			->selectSingleValue('cce.eventId')
			->all());
		sort($ids);
		return $ids;
	}

	private function expected(string ...$keys): array
	{
		$ids = array_map(fn($key) => self::$eventIds[$key], $keys);
		sort($ids);
		return $ids;
	}

	public static function provideFilters(): array
	{
		return [
			'title' => [['title' => '%Meeting%']],
			'description' => [['description' => '%Agenda%']],
			'location' => [['location' => '%Room%']],
		];
	}

	#[DataProvider('provideFilters')]
	public function testOwnerMatchesAllOwnEvents(array $filter): void
	{
		$this->assertEquals($this->expected('public', 'private', 'freebusy'), $this->find(User::ID_SUPER_ADMIN, $filter));
	}

	#[DataProvider('provideFilters')]
	public function testOthersDontMatchHiddenDetails(array $filter): void
	{
		$this->assertEquals($this->expected('public'), $this->find(self::$viewer->id, $filter),
			'The details of a private event or of an event in a free / busy calendar must not match for others');
	}

	public function testMatchesAnyOfSeveralValues(): void
	{
		$this->assertEquals($this->expected('public', 'other'),
			$this->find(self::$viewer->id, ['location' => ['%Room%', '%Elsewhere%']]));
	}
}
