<?php

namespace go\modules\community\calendar\controller;

use go\core\auth\TemporaryState;
use go\core\model\Acl;
use go\core\model\User;
use go\core\util\DateTime;
use go\modules\community\calendar\model\Calendar;
use go\modules\community\calendar\model\CalendarEvent as EventModel;
use PHPUnit\Framework\TestCase;

/**
 * CalendarEvent/get with "properties" returns only those properties. The event must still be complete enough to hide
 * the details of private events.
 */
class CalendarEventPropertiesTest extends TestCase
{
	private $authState;
	private static User $viewer;
	private static Calendar $calendar;
	private static EventModel $private;

	public static function setUpBeforeClass(): void
	{
		$authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		try {
			$name = uniqid('propviewer');
			self::$viewer = new User();
			self::$viewer->setValues(['username' => $name, 'displayName' => $name, 'email' => $name . '@example.com', 'password' => 'Pa55word!!xyz']);
			self::assertTrue(self::$viewer->save(), self::$viewer->getValidationErrorsAsString());

			// The admin owns the calendar, the viewer may read it
			self::$calendar = new Calendar();
			self::$calendar->name = uniqid('props');
			self::$calendar->setOwnerId(User::ID_SUPER_ADMIN);
			self::$calendar->setAcl([self::$viewer->getPersonalGroup()->id => Acl::LEVEL_READ]);
			self::assertTrue(self::$calendar->save(), self::$calendar->getValidationErrorsAsString());

			self::$private = new EventModel();
			self::$private->calendarId = self::$calendar->id;
			self::$private->title = 'Private plan';
			self::$private->timeZone = 'Europe/Amsterdam';
			self::$private->start = new DateTime('2030-03-05 10:00:00');
			self::$private->duration = 'PT1H';
			self::$private->privacy = EventModel::Private;
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
		if (isset(self::$viewer)) {
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
	 * The controller without its module check in the constructor
	 */
	private function get(int $userId, array $properties): array
	{
		go()->setAuthState(new TemporaryState($userId));
		$controller = (new \ReflectionClass(CalendarEvent::class))->newInstanceWithoutConstructor();
		return $controller->get(['ids' => [self::$private->id], 'properties' => $properties])['list'][0];
	}

	public function testGetWithoutStart(): void
	{
		$event = $this->get(User::ID_SUPER_ADMIN, ['title']);

		$this->assertEquals('Private plan', $event['title']);
	}

	public function testPrivateTitleIsHiddenWhenOnlyTheTitleIsAsked(): void
	{
		$event = $this->get(self::$viewer->id, ['title']);

		$this->assertSame('', $event['title']);
	}
}
