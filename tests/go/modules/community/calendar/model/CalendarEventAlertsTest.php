<?php

namespace go\modules\community\calendar\model;

use go\core\acl\model\AclItemEntity;
use go\core\auth\TemporaryState;
use go\core\model\Acl;
use go\core\model\User;
use go\core\util\DateTime;
use PHPUnit\Framework\TestCase;

/**
 * Alerts of events in a calendar that several users subscribed to. Each subscriber has its own alerts, and can choose
 * to get the alerts that the creator of an event set (#37829).
 */
class CalendarEventAlertsTest extends TestCase
{
	/** @var User[] */
	private static array $users = [];
	/** Team calendar without an owner */
	private static ?Calendar $team = null;
	/** Calendar owned by the colleague */
	private static ?Calendar $owned = null;
	private static bool $sendMessages;

	private array $eventIds = [];
	/** @var array[] [user key, calendar id] of the users that turned on useCreatorAlerts in a test */
	private array $optedIn = [];

	public static function setUpBeforeClass(): void
	{
		self::$sendMessages = CalendarEvent::$sendSchedulingMessages;
		CalendarEvent::$sendSchedulingMessages = false;

		try {
			foreach (['creator', 'colleague', 'observer'] as $name) {
				$user = new User();
				$user->username = 'alerttest' . $name;
				$user->displayName = $name;
				$user->email = $user->recoveryEmail = $user->username . '@intermesh.localhost';
				$user->setPassword('sdfTgs-Dfgg-$#ddg');
				if (!$user->save()) {
					throw new \Exception(json_encode($user->getValidationErrors()));
				}
				self::$users[$name] = $user;
			}

			self::actAs(1);
			self::$team = self::createCalendar('Alert test team', null);
			self::$owned = self::createCalendar('Alert test owned', self::$users['colleague']->id);

			foreach (self::$users as $user) {
				self::actAs($user->id);
				foreach ([self::$team, self::$owned] as $c) {
					$calendar = Calendar::findById($c->id);
					$calendar->isSubscribed = true;
					$calendar->syncToDevice = true;
					$calendar->setValues(['defaultAlertsWithTime' => ['1' => ['trigger' => ['offset' => '-PT15M', 'relativeTo' => 'start']]]]);
					if ($c === self::$team) {
						// on by default in a calendar without an owner, see testOwnerlessCalendarUsesCreatorsAlertsByDefault()
						$calendar->setUseCreatorAlerts(false);
					}
					if (!$calendar->save()) {
						throw new \Exception(json_encode($calendar->getValidationErrors()));
					}
				}
			}
		} catch (\Throwable $e) {
			self::tearDownAfterClass();
			throw $e;
		}
	}

	public static function tearDownAfterClass(): void
	{
		self::actAs(1);
		foreach ([self::$team, self::$owned] as $calendar) {
			if ($calendar) {
				Calendar::delete(['id' => $calendar->id]);
			}
		}
		self::$team = self::$owned = null;
		if (!empty(self::$users)) {
			User::delete(['id' => array_map(fn(User $u) => $u->id, self::$users)]);
			self::$users = [];
		}
		CalendarEvent::$sendSchedulingMessages = self::$sendMessages;
	}

	protected function tearDown(): void
	{
		foreach ($this->optedIn as [$userKey, $calendarId]) {
			$this->useCreatorAlerts($userKey, $calendarId, false);
		}
		self::actAs(1);
		if (!empty($this->eventIds)) {
			CalendarEvent::delete(['id' => $this->eventIds]);
		}
	}

	private static function createCalendar(string $name, ?int $ownerId): Calendar
	{
		$calendar = new Calendar();
		$calendar->name = $name;
		$calendar->setOwnerId($ownerId);
		$calendar->setAcl(array_combine(
			array_map(fn(User $u) => $u->getPersonalGroup()->id, self::$users),
			array_fill(0, count(self::$users), Acl::LEVEL_WRITE)
		));
		if (!$calendar->save()) {
			throw new \Exception(json_encode($calendar->getValidationErrors()));
		}
		return $calendar;
	}

	private function useCreatorAlerts(string $userKey, int $calendarId, bool $value): void
	{
		self::actAs(self::$users[$userKey]->id);
		$calendar = Calendar::findById($calendarId);
		$calendar->setUseCreatorAlerts($value);
		$this->assertTrue($calendar->save(), json_encode($calendar->getValidationErrors()));
		if ($value) {
			$this->optedIn[] = [$userKey, $calendarId];
		}
	}

	/**
	 * Switch user. Permission levels are cached per process.
	 */
	private static function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
		(new \ReflectionProperty(AclItemEntity::class, 'aclEntityCache'))->setValue(null, []);
		(new \ReflectionProperty(Acl::class, 'permissionLevelCache'))->setValue(null, []);
	}

	private function createEvent(string $userKey, \DateTimeInterface $start, ?string $alertOffset, ?Calendar $calendar = null): CalendarEvent
	{
		self::actAs(self::$users[$userKey]->id);
		$event = new CalendarEvent();
		$event->calendarId = ($calendar ?? self::$team)->id;
		$event->title = 'Team appointment';
		$event->timeZone = 'Europe/Amsterdam';
		$event->start = $start;
		$event->duration = 'PT1H';
		if ($alertOffset) {
			$event->setValues(['alerts' => ['1' => ['trigger' => ['offset' => $alertOffset, 'relativeTo' => 'start']]]]);
		}
		$this->assertTrue($event->save(), json_encode($event->getValidationErrors()));
		$this->eventIds[] = $event->id;
		return $event;
	}

	/**
	 * The scheduled alert times of a user for an event in UTC
	 *
	 * @return string[]
	 */
	private function triggers(CalendarEvent $event, string $userKey): array
	{
		return go()->getDbConnection()
			->select('triggerAt')
			->from('core_alert')
			->where([
				'entityTypeId' => CalendarEvent::entityType()->getId(),
				'entityId' => $event->id,
				'userId' => self::$users[$userKey]->id
			])
			->orderBy(['triggerAt' => 'ASC'])
			->fetchMode(\PDO::FETCH_COLUMN, 0)
			->all();
	}

	private static function utc(\DateTimeInterface $date, string $modify): string
	{
		return (new \DateTime($date->format('c')))->setTimezone(new \DateTimeZone('UTC'))->modify($modify)->format('Y-m-d H:i:s');
	}

	private static function start(string $modify): DateTime
	{
		return (new DateTime($modify, new \DateTimeZone('Europe/Amsterdam')))->setTime(10, 0);
	}

	/**
	 * Changing the default alerts of a calendar reschedules the alerts of the events in that calendar only
	 */
	public function testChangingDefaultAlertsOnlyAffectsThatCalendar(): void
	{
		$teamEvent = $this->createEvent('colleague', self::start('+2 days'), null);
		$ownTeamEvent = $this->createEvent('creator', self::start('+2 days'), null);

		self::actAs(self::$users['creator']->id);
		$personal = new CalendarEvent();
		$personal->calendarId = Calendar::fetchPersonal(self::$users['creator']->id);
		$personal->title = 'Personal';
		$personal->timeZone = 'Europe/Amsterdam';
		$personal->start = self::start('+2 days');
		$personal->duration = 'PT1H';
		$this->assertTrue($personal->save(), json_encode($personal->getValidationErrors()));
		$this->eventIds[] = $personal->id;
		$personalTriggers = $this->triggers($personal, 'creator');

		$calendar = Calendar::findById(self::$team->id);
		$calendar->setValues(['defaultAlertsWithTime' => ['1' => ['trigger' => ['offset' => '-PT30M', 'relativeTo' => 'start']]]]);
		try {
			$this->assertTrue($calendar->save(), json_encode($calendar->getValidationErrors()));

			// The creator has no per-user data for the event of the colleague
			$this->assertEquals([self::utc($teamEvent->start, '-30 minutes')], $this->triggers($teamEvent, 'creator'));
			$this->assertEquals([self::utc($ownTeamEvent->start, '-30 minutes')], $this->triggers($ownTeamEvent, 'creator'));
			$this->assertEquals($personalTriggers, $this->triggers($personal, 'creator'));
		} finally {
			$calendar->setValues(['defaultAlertsWithTime' => ['1' => ['trigger' => ['offset' => '-PT15M', 'relativeTo' => 'start']]]]);
			$calendar->save();
		}
	}

	public function testRemovedAlertIsNotScheduled(): void
	{
		$event = $this->createEvent('creator', self::start('+2 days'), '-PT1H');
		$event->setValues(['alerts' => [
			'1' => ['trigger' => ['offset' => '-PT1H', 'relativeTo' => 'start']],
			'2' => ['trigger' => ['offset' => '-PT2H', 'relativeTo' => 'start']]
		]]);
		$this->assertTrue($event->save(), json_encode($event->getValidationErrors()));
		$this->assertEquals([self::utc($event->start, '-2 hours'), self::utc($event->start, '-1 hour')], $this->triggers($event, 'creator'));

		$event->setValues(['alerts' => ['1' => ['trigger' => ['offset' => '-PT1H', 'relativeTo' => 'start']]]]);
		$this->assertTrue($event->save(), json_encode($event->getValidationErrors()));
		$this->assertEquals([self::utc($event->start, '-1 hour')], $this->triggers($event, 'creator'));
	}

	public function testOtherSubscriberMovingEventKeepsCreatorsAlert(): void
	{
		$event = $this->createEvent('creator', self::start('+2 days'), '-PT1H');

		self::actAs(self::$users['colleague']->id);
		$event = CalendarEvent::findById($event->id);
		$newStart = self::start('+3 days');
		$event->start = $newStart;
		$this->assertTrue($event->save(), json_encode($event->getValidationErrors()));

		$this->assertEquals([self::utc($newStart, '-1 hour')], $this->triggers($event, 'creator'));
		$this->assertEquals([self::utc($newStart, '-15 minutes')], $this->triggers($event, 'colleague'));
	}

	/**
	 * Move the modification time of an event back, so a change made in the same second gets another etag
	 */
	private function backdate(CalendarEvent $event): void
	{
		go()->getDbConnection()->exec("UPDATE calendar_event e
			JOIN calendar_calendar_event cce ON cce.eventId = e.eventId
			SET e.modifiedAt = DATE_SUB(e.modifiedAt, INTERVAL 1 MINUTE)
			WHERE cce.id = " . (int) $event->id);
	}

	private function setAlert(string $userKey, CalendarEvent $event, string $offset): CalendarEvent
	{
		self::actAs(self::$users[$userKey]->id);
		$event = CalendarEvent::findById($event->id);
		$event->setValues(['alerts' => ['1' => ['trigger' => ['offset' => $offset, 'relativeTo' => 'start']]]]);
		$this->assertTrue($event->save(), json_encode($event->getValidationErrors()));
		return $event;
	}

	public function testSubscriberGetsCreatorsAlertsWhenTurnedOn(): void
	{
		$this->useCreatorAlerts('observer', self::$team->id, true);
		$start = self::start('+2 days');
		$event = $this->createEvent('creator', $start, '-PT1H');

		$this->assertEquals([self::utc($start, '-1 hour')], $this->triggers($event, 'observer'));
		// the colleague didn't turn it on
		$this->assertEquals([self::utc($start, '-15 minutes')], $this->triggers($event, 'colleague'));

		// follows when the creator changes the alert
		$event = $this->setAlert('creator', $event, '-PT2H');
		$this->assertEquals([self::utc($start, '-2 hours')], $this->triggers($event, 'observer'));

		// follows when someone else moves the event
		self::actAs(self::$users['colleague']->id);
		$event = CalendarEvent::findById($event->id);
		$event->start = $start = self::start('+4 days');
		$this->assertTrue($event->save(), json_encode($event->getValidationErrors()));
		$this->assertEquals([self::utc($start, '-2 hours')], $this->triggers($event, 'observer'));
	}

	public function testCreatorWithDefaultAlertsGivesSubscriberItsOwnDefaults(): void
	{
		$this->useCreatorAlerts('observer', self::$team->id, true);
		$start = self::start('+2 days');
		$event = $this->createEvent('creator', $start, null);

		$this->assertEquals([self::utc($start, '-15 minutes')], $this->triggers($event, 'observer'));
	}

	public function testOwnAlertOfSubscriberWins(): void
	{
		$this->useCreatorAlerts('observer', self::$team->id, true);
		$start = self::start('+2 days');
		$event = $this->createEvent('creator', $start, '-PT1H');
		$event = $this->setAlert('observer', $event, '-PT5M');

		$this->assertEquals([self::utc($start, '-5 minutes')], $this->triggers($event, 'observer'));
		$this->assertEquals([self::utc($start, '-1 hour')], $this->triggers($event, 'creator'));
	}

	/**
	 * In a calendar with an owner the alert that someone sets when creating an event goes to the owner
	 */
	public function testOwnedCalendarUsesAlertsOfOwner(): void
	{
		$this->useCreatorAlerts('observer', self::$owned->id, true);
		$start = self::start('+2 days');
		$event = $this->createEvent('creator', $start, '-PT1H', self::$owned);

		$this->assertEquals([self::utc($start, '-1 hour')], $this->triggers($event, 'colleague'));
		$this->assertEquals([self::utc($start, '-1 hour')], $this->triggers($event, 'observer'));
		// off by default in a calendar with an owner
		$this->assertEquals([self::utc($start, '-15 minutes')], $this->triggers($event, 'creator'));
	}

	public function testTurningOnAndOffReschedulesExistingEvents(): void
	{
		$start = self::start('+2 days');
		$event = $this->createEvent('creator', $start, '-PT1H');
		$this->assertEquals([self::utc($start, '-15 minutes')], $this->triggers($event, 'observer'));

		$this->useCreatorAlerts('observer', self::$team->id, true);
		$this->assertEquals([self::utc($start, '-1 hour')], $this->triggers($event, 'observer'));

		$this->useCreatorAlerts('observer', self::$team->id, false);
		$this->assertEquals([self::utc($start, '-15 minutes')], $this->triggers($event, 'observer'));
	}

	/**
	 * The creator's alerts are VALARMs for the subscriber in CalDAV. When the creator changes them, the etag changes, so
	 * the device fetches the event again. When the device sends the event back, the subscriber keeps following the
	 * creator's alerts.
	 */
	public function testCalDAVHasCreatorsAlerts(): void
	{
		$this->useCreatorAlerts('observer', self::$team->id, true);
		$start = self::start('+2 days');
		$event = $this->createEvent('creator', $start, '-PT1H');
		$calendarId = 'c-' . self::$team->id;
		$uri = $event->uri();

		self::actAs(self::$users['observer']->id);
		$backend = new CalDAVBackend();
		$this->assertStringContainsString('TRIGGER:-PT1H', $backend->getCalendarObject($calendarId, $uri)['calendardata']);
		// The etag is the modification time in seconds, and making the iCalendar data above changed it to now
		$this->backdate($event);
		$object = $backend->getCalendarObject($calendarId, $uri);

		$event = $this->setAlert('creator', $event, '-PT2H');

		self::actAs(self::$users['observer']->id);
		$changed = $backend->getCalendarObject($calendarId, $uri);
		$this->assertNotEquals($object['etag'], $changed['etag']);
		$this->assertStringContainsString('TRIGGER:-PT2H', $changed['calendardata']);
		$this->assertStringNotContainsString('TRIGGER:-PT1H', $changed['calendardata']);

		// the device sends the event back with the alert it got
		$backend->updateCalendarObject($calendarId, $uri, $changed['calendardata']);

		$event = $this->setAlert('creator', $event, '-PT3H');
		$this->assertEquals([self::utc($start, '-3 hours')], $this->triggers($event, 'observer'));
	}

	/**
	 * A device that sends another alert sets the subscriber's own alert
	 */
	public function testCalDAVOwnAlert(): void
	{
		$this->useCreatorAlerts('observer', self::$team->id, true);
		$start = self::start('+2 days');
		$event = $this->createEvent('creator', $start, '-PT1H');
		$calendarId = 'c-' . self::$team->id;

		self::actAs(self::$users['observer']->id);
		$backend = new CalDAVBackend();
		$object = $backend->getCalendarObject($calendarId, $event->uri());
		$backend->updateCalendarObject($calendarId, $event->uri(), str_replace('TRIGGER:-PT1H', 'TRIGGER:-PT10M', $object['calendardata']));

		$this->assertEquals([self::utc($start, '-10 minutes')], $this->triggers($event, 'observer'));
		$this->setAlert('creator', $event, '-PT3H');
		$this->assertEquals([self::utc($start, '-10 minutes')], $this->triggers($event, 'observer'));
	}

	/**
	 * The alerts that the creator sets on a new event are only for the creator
	 */
	public function testNewEventGivesOtherSubscribersTheirDefaults(): void
	{
		$start = self::start('+2 days');
		$event = $this->createEvent('creator', $start, '-PT1H');

		$this->assertEquals([self::utc($start, '-1 hour')], $this->triggers($event, 'creator'));
		$this->assertEquals([self::utc($start, '-15 minutes')], $this->triggers($event, 'colleague'));
	}

	/**
	 * Turning the option on doesn't change the event, so a device may still send the default alert it got before. The
	 * subscriber must keep following the creator.
	 */
	public function testCalDAVOldDefaultAlertAfterTurningOn(): void
	{
		$start = self::start('+2 days');
		$event = $this->createEvent('creator', $start, '-PT1H');
		$calendarId = 'c-' . self::$team->id;

		self::actAs(self::$users['observer']->id);
		$backend = new CalDAVBackend();
		$old = $backend->getCalendarObject($calendarId, $event->uri());
		$this->assertStringContainsString('TRIGGER:-PT15M', $old['calendardata']);

		$this->useCreatorAlerts('observer', self::$team->id, true);
		self::actAs(self::$users['observer']->id);
		$backend->updateCalendarObject($calendarId, $event->uri(), $old['calendardata']);
		$this->assertEquals([self::utc($start, '-1 hour')], $this->triggers($event, 'observer'));

		$this->setAlert('creator', $event, '-PT3H');
		$this->assertEquals([self::utc($start, '-3 hours')], $this->triggers($event, 'observer'));
	}

	/**
	 * In a calendar without an owner subscribers get the alerts of the creator, unless they turn it off. A subscriber
	 * who may only read the calendar can change it with Calendar/set, as it's a per-user setting.
	 */
	public function testOwnerlessCalendarUsesCreatorsAlertsByDefault(): void
	{
		self::actAs(1);
		$calendar = new Calendar();
		$calendar->name = 'Alert test read only';
		$calendar->setAcl([
			self::$users['creator']->getPersonalGroup()->id => Acl::LEVEL_WRITE,
			self::$users['observer']->getPersonalGroup()->id => Acl::LEVEL_READ,
		]);
		$this->assertTrue($calendar->save(), json_encode($calendar->getValidationErrors()));
		$controller = new \go\modules\community\calendar\controller\Calendar();

		try {
			self::actAs(self::$users['observer']->id);
			$response = $controller->set(['update' => [$calendar->id => ['isSubscribed' => true]]]);
			$this->assertArrayHasKey($calendar->id, $response['updated'] ?? [], json_encode($response['notUpdated'] ?? null));
			$this->assertTrue($controller->get(['ids' => [$calendar->id], 'properties' => ['useCreatorAlerts']])['list'][0]['useCreatorAlerts']);

			$start = self::start('+2 days');
			$event = $this->createEvent('creator', $start, '-PT1H', $calendar);
			$this->assertEquals([self::utc($start, '-1 hour')], $this->triggers($event, 'observer'));

			self::actAs(self::$users['observer']->id);
			$response = $controller->set(['update' => [$calendar->id => ['useCreatorAlerts' => false]]]);
			$this->assertArrayHasKey($calendar->id, $response['updated'] ?? [], json_encode($response['notUpdated'] ?? null));
			// the observer has no default alerts in this calendar
			$this->assertEquals([], $this->triggers($event, 'observer'));
		} finally {
			self::actAs(1);
			if (!empty($this->eventIds)) {
				CalendarEvent::delete(['id' => $this->eventIds]);
				$this->eventIds = [];
			}
			Calendar::delete(['id' => $calendar->id]);
		}
	}
}
