<?php
namespace go\modules\legacy\core;

use go\core\auth\TemporaryState;
use go\core\model\User;
use GO\Base\Model\Reminder;
use GO\Core\Controller\ReminderController;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Snoozing a reminder adds the user to it. Users may only snooze their own reminders, otherwise they could read the
 * reminders of others.
 */
class ReminderControllerTest extends TestCase
{
	private $authState;
	private User $user;
	private Reminder $reminder;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->actAs(User::ID_SUPER_ADMIN);

		$name = uniqid('reminder');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), var_export($this->user->getValidationErrors(), true));

		$this->reminder = new Reminder();
		$this->reminder->name = 'Private reminder of the admin';
		$this->reminder->time = time();
		$this->reminder->vtime = time();
		$this->reminder->model_id = 0;
		$this->reminder->model_type_id = 0;
		$this->reminder->manual = 1;
		$this->assertTrue($this->reminder->save(true));
		$this->reminder->setForUser(User::ID_SUPER_ADMIN);
	}

	protected function tearDown(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		$this->reminder->delete(true);
		User::delete(['id' => $this->user->id]);
		go()->setAuthState($this->authState);
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
		\GO::session()->setCurrentUser($userId);
	}

	private function snooze(): void
	{
		$method = new ReflectionMethod(ReminderController::class, 'actionSnooze');
		$method->invoke(new ReminderController(), [
			'reminders' => json_encode([$this->reminder->id]),
			'snooze_time' => 300
		]);
	}

	public function testUserCannotSnoozeReminderOfOthers()
	{
		$this->actAs($this->user->id);
		$this->snooze();

		$this->assertEmpty($this->reminder->hasUser($this->user->id));
	}

	public function testUserCanSnoozeOwnReminder()
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		$before = $this->reminder->hasUser(User::ID_SUPER_ADMIN)->time;
		$this->snooze();

		$this->assertGreaterThan($before, (int) $this->reminder->hasUser(User::ID_SUPER_ADMIN)->time);
	}
}
