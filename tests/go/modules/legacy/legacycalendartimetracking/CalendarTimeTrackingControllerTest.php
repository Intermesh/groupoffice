<?php
namespace go\modules\legacy\legacycalendartimetracking;

use GO\Base\Exception\AccessDenied;
use GO\Legacycalendartimetracking\Controller\CalendarTimeTrackingController;
use go\core\auth\TemporaryState;
use go\core\model\User;
use go\core\util\DateTime;
use go\modules\community\calendar\model\Calendar;
use go\modules\community\calendar\model\CalendarEvent;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Time can only be booked on calendar events the user may read
 */
class CalendarTimeTrackingControllerTest extends TestCase
{
	private $authState;
	private User $owner;
	private User $other;
	private CalendarEvent $event;

	protected function setUp(): void
	{
		if (!class_exists(CalendarTimeTrackingController::class) || !go()->getModule('community', 'calendar')) {
			$this->markTestSkipped('The legacycalendartimetracking and calendar modules are required');
		}

		$this->authState = go()->getAuthState();
		$this->actAs(User::ID_SUPER_ADMIN);

		$this->owner = $this->createUser('ttowner');
		$this->other = $this->createUser('ttother');

		$this->actAs($this->owner->id);
		$this->event = new CalendarEvent();
		$this->event->calendarId = Calendar::fetchPersonal($this->owner->id);
		$this->event->title = 'Private';
		$this->event->timeZone = 'Europe/Amsterdam';
		$this->event->start = (new DateTime('+1 week'))->setTime(12, 0, 0, 0);
		$this->event->duration = 'PT1H';
		$this->assertTrue($this->event->save(), json_encode($this->event->getValidationErrors()));

		// Personal calendars may be shared with everyone by default
		$this->actAs(User::ID_SUPER_ADMIN);
		$calendar = Calendar::findById($this->event->calendarId);
		$calendar->setAcl([\go\core\model\Group::ID_INTERNAL => null, \go\core\model\Group::ID_EVERYONE => null, $this->owner->getPersonalGroup()->id => \go\core\model\Acl::LEVEL_MANAGE]);
		$this->assertTrue($calendar->save(), $calendar->getValidationErrorsAsString());
	}

	protected function tearDown(): void
	{
		if (!isset($this->authState)) {
			return;
		}
		$this->actAs(User::ID_SUPER_ADMIN);
		User::delete(['id' => [$this->owner->id, $this->other->id]]);
		go()->setAuthState($this->authState);
	}

	private function createUser(string $prefix): User
	{
		$name = uniqid($prefix);
		$user = new User();
		$user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($user->save(), $user->getValidationErrorsAsString());
		return $user;
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
		\GO::session()->setCurrentUser($userId);
		// Calendars are cached with the permission level of the previous user
		(new \ReflectionProperty(\go\core\acl\model\AclItemEntity::class, 'aclEntityCache'))->setValue(null, []);
	}

	public static function actions(): array
	{
		return [['actionDeleteTE'], ['actionLoadTE'], ['actionLoad'], ['actionSubmit']];
	}

	#[DataProvider('actions')]
	public function testEventOfOtherUser(string $action): void
	{
		$this->actAs($this->other->id);
		$this->expectException(AccessDenied::class);
		(new \ReflectionMethod(CalendarTimeTrackingController::class, $action))->invoke(new CalendarTimeTrackingController(), [
			'event_id' => $this->event->id,
			'recurrenceId' => ''
		]);
	}

	public function testOwnEvent(): void
	{
		$this->actAs($this->owner->id);
		$event = (new \ReflectionMethod(CalendarTimeTrackingController::class, 'findEvent'))->invoke(new CalendarTimeTrackingController(), ['event_id' => $this->event->id]);
		$this->assertEquals($this->event->id, $event->id);
	}
}
