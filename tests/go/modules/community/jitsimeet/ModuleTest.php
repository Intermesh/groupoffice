<?php
namespace go\modules\community\jitsimeet;

use go\core\auth\TemporaryState;
use go\core\model\Group;
use go\core\model\Module as ModuleModel;
use go\core\model\Permission;
use go\core\model\User;
use go\core\util\DateTime;
use go\modules\community\calendar\model\Calendar;
use go\modules\community\calendar\model\CalendarEvent;
use go\modules\community\jitsimeet\model\Settings;
use PHPUnit\Framework\TestCase;

/**
 * A calendar event saved with "onlineMeeting": "jitsi" gets a Jitsi Meet link, for every API client like the MCP module
 */
class ModuleTest extends TestCase
{
	private static User $user;
	private static Group $group;
	private static int $userCalendarId;
	private static int $adminCalendarId;

	private array $settings;
	private $authState;
	private array $eventIds = [];

	public static function setUpBeforeClass(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		try {
			$name = uniqid('jitsi');
			self::$group = new Group();
			self::$group->name = $name . 'group';
			if (!self::$group->save()) {
				throw new \Exception(self::$group->getValidationErrorsAsString());
			}

			// Not in the internal group, so this user only has the calendar module and not jitsimeet
			self::$user = new User();
			self::$user->setValues([
				'username' => $name,
				'displayName' => $name,
				'email' => $name . '@example.com',
				'password' => 'Pa55word!!xyz'
			]);
			self::$user->groups = [Group::ID_EVERYONE, self::$group->id];
			if (!self::$user->save()) {
				throw new \Exception(self::$user->getValidationErrorsAsString());
			}

			$calendar = ModuleModel::findByName('community', 'calendar');
			$calendar->permissions[self::$group->id] = new Permission($calendar);
			if (!$calendar->save()) {
				throw new \Exception("Could not grant the calendar module");
			}
			(new \ReflectionProperty(ModuleModel::class, 'userRightsCache'))->setValue(null, []);

			self::$userCalendarId = (int) Calendar::fetchPersonal(self::$user->id);
			self::$adminCalendarId = (int) Calendar::fetchPersonal(User::ID_SUPER_ADMIN);
		} catch (\Throwable $e) {
			self::tearDownAfterClass();
			throw $e;
		}
	}

	public static function tearDownAfterClass(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		if (isset(self::$group)) {
			$calendar = ModuleModel::findByName('community', 'calendar');
			if (isset($calendar->permissions[self::$group->id])) {
				unset($calendar->permissions[self::$group->id]);
				$calendar->save();
			}
		}
		if (isset(self::$userCalendarId)) {
			Calendar::delete(['id' => self::$userCalendarId]);
		}
		if (isset(self::$user->id)) {
			User::delete(['id' => self::$user->id]);
		}
		if (isset(self::$group->id)) {
			Group::delete(['id' => self::$group->id]);
		}
	}

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));

		// Changed in memory only, see tearDown()
		$s = Settings::get();
		$this->settings = [$s->videoUri, $s->videoJwtEnabled, $s->videoJwtAppId];
		$s->videoUri = 'https://meet.example.com/';
		$s->videoJwtEnabled = false;
	}

	protected function tearDown(): void
	{
		$s = Settings::get();
		[$s->videoUri, $s->videoJwtEnabled, $s->videoJwtAppId] = $this->settings;

		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		if (!empty($this->eventIds)) {
			CalendarEvent::delete(['id' => $this->eventIds]);
		}
		go()->setAuthState($this->authState);
	}

	private function createEvent(int $calendarId, array $values): CalendarEvent
	{
		$event = new CalendarEvent();
		$event->setValues(array_merge([
			'calendarId' => $calendarId,
			'title' => 'Jitsi test',
			'start' => (new DateTime('tomorrow'))->setTime(13, 0)->format('Y-m-d\TH:i:s'),
			'timeZone' => 'Europe/Amsterdam',
			'duration' => 'PT1H'
		], $values));
		if ($event->save()) {
			$this->eventIds[] = $event->id;
		}
		return $event;
	}

	public function testLinkWithoutJwt(): void
	{
		$event = $this->createEvent(self::$adminCalendarId, ['description' => 'Agenda', 'onlineMeeting' => 'jitsi']);

		$this->assertFalse($event->hasValidationErrors(), $event->getValidationErrorsAsString());
		$this->assertMatchesRegularExpression('~^https://meet\.example\.com/[0-9a-f]{16}$~', $event->location);
		$this->assertStringStartsWith("Agenda\n\n--------------------------------\n" . go()->t("Join the online meeting now"), $event->description);
		$this->assertStringContainsString($event->location, $event->description);
	}

	public function testLinkWithJwt(): void
	{
		$s = Settings::get();
		$s->videoJwtEnabled = true;
		$s->videoJwtAppId = 'groupoffice';

		$event = $this->createEvent(self::$adminCalendarId, ['onlineMeeting' => 'jitsi']);

		$this->assertMatchesRegularExpression('~^https://meet\.example\.com/([0-9a-f]{16})\?jwt=[\w-]+\.[\w-]+\.[\w-]+$~', $event->location);

		// The token is for the room of the link
		preg_match('~/([0-9a-f]{16})\?jwt=[\w-]+\.([\w-]+)\.~', $event->location, $matches);
		$payload = json_decode(base64_decode(strtr($matches[2], '-_', '+/')), true);
		$this->assertEquals($matches[1], $payload['room']);
	}

	public function testUnknownServiceIsRefused(): void
	{
		$event = $this->createEvent(self::$adminCalendarId, ['onlineMeeting' => 'nosuchservice']);

		$this->assertTrue($event->hasValidationErrors('onlineMeeting'));
		$this->assertNull($event->id ?? null);
	}

	public function testUserWithoutModuleAccessGetsNoLink(): void
	{
		go()->setAuthState(new TemporaryState(self::$user->id));
		$this->assertFalse(ModuleModel::isAvailableFor('community', 'jitsimeet'), 'The test user must not have the jitsimeet module');

		$event = $this->createEvent(self::$userCalendarId, ['onlineMeeting' => 'jitsi']);

		$this->assertTrue($event->hasValidationErrors('onlineMeeting'));
		$this->assertNull($event->id ?? null);
	}

	public function testNewMeetingReplacesTheOldOne(): void
	{
		$event = $this->createEvent(self::$adminCalendarId, ['description' => 'Agenda', 'onlineMeeting' => 'jitsi']);
		$first = $event->location;

		$event->setValues(['onlineMeeting' => 'jitsi']);
		$this->assertTrue($event->save(), $event->getValidationErrorsAsString());

		$this->assertNotEquals($first, $event->location);
		$this->assertStringNotContainsString($first, $event->description);
		$this->assertEquals(1, substr_count($event->description, go()->t("Join the online meeting now")));
		$this->assertStringStartsWith("Agenda\n\n", $event->description);
	}

	public function testOnlineMeetingIsDescribed(): void
	{
		$schema = \go\core\orm\PropertySchema::build(CalendarEvent::class);
		$props = (array) $schema['$defs']['CalendarEvent']['properties'];

		$this->assertArrayHasKey('onlineMeeting', $props, implode(', ', array_keys($props)));
		$this->assertTrue($props['onlineMeeting']['writeOnly']);
		$this->assertStringContainsString('jitsi', $props['onlineMeeting']['description']);
	}

	public function testOnlineMeetingIsWriteOnly(): void
	{
		$event = $this->createEvent(self::$adminCalendarId, ['onlineMeeting' => 'jitsi']);

		$this->assertArrayNotHasKey('onlineMeeting', $event->toArray());
	}
}
