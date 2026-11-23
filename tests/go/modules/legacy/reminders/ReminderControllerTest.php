<?php
namespace go\modules\legacy\reminders;

use go\core\auth\TemporaryState;
use go\core\model\User;
use GO\Base\Exception\AccessDenied;
use GO\Base\Model\Reminder;
use GO\Reminders\Controller\ReminderController;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * actionReminderUsers switches ACL checks off to add users and groups to a reminder. They must be switched on again
 * afterwards, otherwise the rest of the request runs without permission checks.
 */
class ReminderControllerTest extends TestCase
{
	private $authState;
	private Reminder $reminder;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		\GO::session()->setCurrentUser(User::ID_SUPER_ADMIN);
		\GO::$ignoreAclPermissions = false;

		$this->reminder = new Reminder();
		$this->reminder->name = 'Reminder users test';
		$this->reminder->time = time();
		$this->reminder->vtime = time();
		$this->reminder->model_id = 0;
		$this->reminder->model_type_id = 0;
		$this->reminder->manual = 1;
		$this->assertTrue($this->reminder->save(true));
	}

	protected function tearDown(): void
	{
		\GO::$ignoreAclPermissions = false;
		$this->reminder->delete(true);
		go()->setAuthState($this->authState);
	}

	private function reminderUsers(array $params): array
	{
		$method = new ReflectionMethod(ReminderController::class, 'actionReminderUsers');
		return $method->invoke(new ReminderController(), $params);
	}

	public function testAclIsRestoredAfterAddingUsers()
	{
		$response = $this->reminderUsers([
			'reminder_id' => $this->reminder->id,
			'add_users' => json_encode([User::ID_SUPER_ADMIN])
		]);

		$this->assertTrue($response['success']);
		$this->assertNotEmpty($this->reminder->hasUser(User::ID_SUPER_ADMIN));
		$this->assertFalse(\GO::$ignoreAclPermissions);
	}

	public function testAclIsRestoredWhenAccessIsDenied()
	{
		$this->reminder->manual = 0;
		$this->assertTrue($this->reminder->save(true));

		try {
			$this->reminderUsers([
				'reminder_id' => $this->reminder->id,
				'add_users' => json_encode([User::ID_SUPER_ADMIN])
			]);
			$this->fail("Changing users of a reminder that wasn't made in the reminders module must be denied");
		} catch (AccessDenied $e) {
			$this->assertFalse(\GO::$ignoreAclPermissions);
			$this->assertEmpty($this->reminder->hasUser(User::ID_SUPER_ADMIN));
		}
	}
}
