<?php

namespace go\modules\community\calendar\controller;

use go\core\auth\TemporaryState;
use go\core\model\Acl;
use go\core\model\User;
use go\core\util\DateTime;
use go\core\util\JSON;
use go\modules\community\calendar\model\Calendar;
use go\modules\community\calendar\model\CalendarEvent as EventModel;
use go\modules\community\calendar\model\ICalendarHelper;
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

			// The admin owns the calendar, the viewer may change its events
			self::$calendar = new Calendar();
			self::$calendar->name = uniqid('privoverride');
			self::$calendar->setOwnerId(User::ID_SUPER_ADMIN);
			self::$calendar->setAcl([self::$viewer->getPersonalGroup()->id => Acl::LEVEL_WRITE]);
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
}
