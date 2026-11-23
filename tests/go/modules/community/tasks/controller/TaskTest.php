<?php
namespace go\modules\community\tasks\controller;

use go\core\auth\TemporaryState;
use go\core\model\Acl;
use go\core\model\Group;
use go\core\model\User;
use go\modules\community\tasks\model\Task as TaskModel;
use go\modules\community\tasks\model\TaskList;
use PHPUnit\Framework\TestCase;

/**
 * The default task list is a setting of the user, so countMine() may only count the tasks the user can read
 */
class TaskTest extends TestCase
{
	private $authState;
	private User $user;
	private ?TaskList $taskList = null;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));

		$name = uniqid('countuser');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), $this->user->getValidationErrorsAsString());

		// A private list with an open task
		$this->taskList = new TaskList();
		$this->taskList->name = uniqid('private');
		$this->taskList->setAcl([Group::ID_INTERNAL => null, Group::ID_EVERYONE => null, Group::ID_ADMINS => Acl::LEVEL_MANAGE]);
		$this->assertTrue($this->taskList->save(), $this->taskList->getValidationErrorsAsString());

		$task = new TaskModel();
		$task->tasklistId = $this->taskList->id;
		$task->title = 'Private task';
		$this->assertTrue($task->save(), $task->getValidationErrorsAsString());

		// The user points the default list to it
		$user = User::findById($this->user->id, ['tasksSettings']);
		$user->tasksSettings->setDefaultTasklistId($this->taskList->id);
		$this->assertTrue($user->save(), $user->getValidationErrorsAsString());
	}

	protected function tearDown(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		if (isset($this->taskList->id)) {
			TaskList::delete(['id' => $this->taskList->id]);
		}
		User::delete(['id' => $this->user->id]);
		go()->setAuthState($this->authState);
	}

	public function testCountMineOnlyCountsReadableTasks(): void
	{
		go()->setAuthState(new TemporaryState($this->user->id));
		$this->assertEquals(0, (new Task())->countMine());
	}
}
