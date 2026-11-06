<?php
namespace go\modules\legacy\summary;

use go\core\auth\TemporaryState;
use go\core\model\User;
use go\core\util\DateTime;
use GO\Base\Exception\AccessDenied;
use go\modules\community\calendar\model\Calendar;
use go\modules\community\calendar\model\CalendarEvent;
use GO\Summary\Controller\CalendarController;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * The appointments portlet shows the events of the calendars a user selected. Users may only select and see calendars
 * they may read.
 */
class CalendarControllerTest extends TestCase
{
	private $authState;
	private bool $sendMessages;
	private User $user;
	private int $adminCalendarId;
	private ?int $eventId = null;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->sendMessages = CalendarEvent::$sendSchedulingMessages;
		CalendarEvent::$sendSchedulingMessages = false;
		$this->actAs(User::ID_SUPER_ADMIN);

		$name = uniqid('portlet');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), var_export($this->user->getValidationErrors(), true));

		// A calendar of the admin that isn't shared
		$calendar = new Calendar();
		$calendar->name = uniqid('private calendar ');
		$this->assertTrue($calendar->save(), json_encode($calendar->getValidationErrors()));
		go()->getDbConnection()->delete('core_acl_group', (new \go\core\db\Query())
			->where('aclId', '=', $calendar->findAclId())
			->andWhere('groupId', '!=', \go\core\model\Group::ID_ADMINS))->execute();
		$this->adminCalendarId = (int) $calendar->id;
		$this->assertSame(0, (int) \GO\Base\Model\Acl::getUserPermissionLevel($calendar->findAclId(), $this->user->id));

		$event = new CalendarEvent();
		$event->calendarId = $this->adminCalendarId;
		$event->title = uniqid('portlet event ');
		$event->timeZone = 'Europe/Amsterdam';
		$event->start = (new DateTime('tomorrow'))->setTime(10, 0, 0, 0);
		$event->duration = 'PT1H';
		$this->assertTrue($event->save(), json_encode($event->getValidationErrors()));
		$this->eventId = $event->id;

		// Both see the calendar of the admin in the portlet
		go()->getDbConnection()->insertIgnore('su_visible_calendars', [
			['user_id' => $this->user->id, 'calendar_id' => $this->adminCalendarId],
			['user_id' => User::ID_SUPER_ADMIN, 'calendar_id' => $this->adminCalendarId]
		])->execute();
	}

	protected function tearDown(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		go()->getDbConnection()->delete('su_visible_calendars', ['user_id' => [$this->user->id, User::ID_SUPER_ADMIN], 'calendar_id' => $this->adminCalendarId])->execute();
		if ($this->eventId) {
			CalendarEvent::delete(['id' => $this->eventId]);
		}
		Calendar::delete(['id' => $this->adminCalendarId]);
		User::delete(['id' => $this->user->id]);
		CalendarEvent::$sendSchedulingMessages = $this->sendMessages;
		go()->setAuthState($this->authState);
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
		\GO::session()->setCurrentUser($userId);
	}

	private function portletEventIds(): array
	{
		$method = new ReflectionMethod(CalendarController::class, 'actionPortletGrid');
		$data = $method->invoke(new CalendarController(), []);
		return array_map('intval', array_column($data['results'], 'id'));
	}

	public function testUserDoesNotSeeEventsOfCalendarWithoutPermission()
	{
		$this->actAs($this->user->id);
		$this->assertNotContains($this->eventId, $this->portletEventIds());
	}

	public function testOwnerSeesEvents()
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		$this->assertContains($this->eventId, $this->portletEventIds());
	}

	public function testUserCannotSelectCalendarWithoutPermission()
	{
		$this->actAs($this->user->id);

		$method = new ReflectionMethod(CalendarController::class, 'beforeAdd');
		$this->expectException(AccessDenied::class);
		$method->invoke(new CalendarController(), ['add' => json_encode([$this->adminCalendarId])]);
	}
}
