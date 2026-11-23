<?php

namespace go\modules\community\calendar\model;

use go\core\acl\model\AclItemEntity;
use go\core\auth\TemporaryState;
use go\core\model\Acl;
use go\core\model\User;
use go\core\util\DateTime;
use PHPUnit\Framework\TestCase;

/**
 * Alerts of events in a calendar that several users subscribed to. Each subscriber has its own alerts.
 */
class CalendarEventAlertsTest extends TestCase
{
	/** @var User[] */
	private static array $users = [];
	private static ?Calendar $team = null;
	private static bool $sendMessages;

	private array $eventIds = [];

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

			// A team calendar without an owner
			self::actAs(1);
			self::$team = new Calendar();
			self::$team->name = 'Alert test team';
			self::$team->setAcl(array_combine(
				array_map(fn(User $u) => $u->getPersonalGroup()->id, self::$users),
				array_fill(0, count(self::$users), Acl::LEVEL_WRITE)
			));
			if (!self::$team->save()) {
				throw new \Exception(json_encode(self::$team->getValidationErrors()));
			}

			foreach (self::$users as $user) {
				self::actAs($user->id);
				$calendar = Calendar::findById(self::$team->id);
				$calendar->isSubscribed = true;
				$calendar->syncToDevice = true;
				$calendar->setValues(['defaultAlertsWithTime' => ['1' => ['trigger' => ['offset' => '-PT15M', 'relativeTo' => 'start']]]]);
				if (!$calendar->save()) {
					throw new \Exception(json_encode($calendar->getValidationErrors()));
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
		if (self::$team) {
			Calendar::delete(['id' => self::$team->id]);
			self::$team = null;
		}
		if (!empty(self::$users)) {
			User::delete(['id' => array_map(fn(User $u) => $u->id, self::$users)]);
			self::$users = [];
		}
		CalendarEvent::$sendSchedulingMessages = self::$sendMessages;
	}

	protected function tearDown(): void
	{
		self::actAs(1);
		if (!empty($this->eventIds)) {
			CalendarEvent::delete(['id' => $this->eventIds]);
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

	private function createEvent(string $userKey, \DateTimeInterface $start, ?string $alertOffset): CalendarEvent
	{
		self::actAs(self::$users[$userKey]->id);
		$event = new CalendarEvent();
		$event->calendarId = self::$team->id;
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
	 * The alerts that the creator sets on a new event are only for the creator
	 */
	public function testNewEventGivesOtherSubscribersTheirDefaults(): void
	{
		$start = self::start('+2 days');
		$event = $this->createEvent('creator', $start, '-PT1H');

		$this->assertEquals([self::utc($start, '-1 hour')], $this->triggers($event, 'creator'));
		$this->assertEquals([self::utc($start, '-15 minutes')], $this->triggers($event, 'colleague'));
	}
}
