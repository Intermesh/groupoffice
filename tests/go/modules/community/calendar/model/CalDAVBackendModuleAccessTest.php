<?php
namespace go\modules\community\calendar\model;

use go\core\auth\TemporaryState;
use go\core\model\Group;
use go\core\model\Module as ModuleModel;
use go\core\model\Permission;
use go\core\model\User;
use PHPUnit\Framework\TestCase;

/**
 * The general /dav/ entry point only checks access to the DAV module. Calendars may only be served to users that also
 * have access to the calendar module, like address books need the CardDAV module.
 */
class CalDAVBackendModuleAccessTest extends TestCase
{
	private $authState;
	private User $user;
	private Group $group;
	private int $calendarId;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));

		$name = uniqid('caldavmod');
		$this->group = new Group();
		$this->group->name = $name . 'group';
		$this->assertTrue($this->group->save());

		// Not in the internal group, so only the modules of the test group are available
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->user->groups = [Group::ID_EVERYONE, $this->group->id];
		$this->assertTrue($this->user->save(), $this->user->getValidationErrorsAsString());

		$this->grant('legacy', 'dav');

		$this->calendarId = (int) Calendar::fetchPersonal($this->user->id);
		go()->setAuthState(new TemporaryState($this->user->id));
		$calendar = Calendar::findById($this->calendarId);
		$calendar->isSubscribed = true;
		$calendar->syncToDevice = true;
		$this->assertTrue($calendar->save(), $calendar->getValidationErrorsAsString());
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
	}

	protected function tearDown(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		Calendar::delete(['id' => $this->calendarId]);
		foreach ([['legacy', 'dav'], ['community', 'calendar']] as [$package, $name]) {
			$module = ModuleModel::findByName($package, $name);
			if (isset($module->permissions[$this->group->id])) {
				unset($module->permissions[$this->group->id]);
				$module->save();
			}
		}
		User::delete(['id' => $this->user->id]);
		Group::delete(['id' => $this->group->id]);
		go()->setAuthState($this->authState);
	}

	private function grant(string $package, string $name): void
	{
		$module = ModuleModel::findByName($package, $name);
		$module->permissions[$this->group->id] = new Permission($module);
		$this->assertTrue($module->save());
		// The rights of a user are cached per request
		(new \ReflectionProperty(ModuleModel::class, 'userRightsCache'))->setValue(null, []);
	}

	private function calendarIds(): array
	{
		go()->setAuthState(new TemporaryState($this->user->id));
		try {
			$calendars = (new CalDAVBackend())->getCalendarsForUser('principals/' . $this->user->username);
		} finally {
			go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		}
		return array_column($calendars, 'id');
	}

	public function testNoCalendarsWithoutCalendarModule(): void
	{
		$this->assertFalse(ModuleModel::isAvailableFor('community', 'calendar', $this->user->id));
		$this->assertEquals([], $this->calendarIds());
	}

	public function testCalendarsWithCalendarModule(): void
	{
		$this->grant('community', 'calendar');
		$this->assertContains('c-' . $this->calendarId, $this->calendarIds());
	}
}
