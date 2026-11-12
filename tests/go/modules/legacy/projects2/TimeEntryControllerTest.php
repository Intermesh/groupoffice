<?php
namespace go\modules\legacy\projects2;

use GO\Base\Exception\AccessDenied;
use GO\Base\Model\Acl;
use GO\Projects2\Controller\TimeEntryController;
use go\core\model\Acl as CoreAcl;
use go\core\model\Group;
use go\core\model\User;
use go\modules\community\tasks\model\Task;
use go\modules\community\tasks\model\TaskList;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/fixtures/ProjectsTrait.php';

/**
 * A new time entry can be filled with the duration, start and responsible user of a task. The user must be able to
 * read the task.
 */
class TimeEntryControllerTest extends TestCase
{
	use ProjectsTrait;

	private ?TaskList $taskList = null;
	private ?Task $task = null;
	private int $projectId;

	protected function setUp(): void
	{
		if (!class_exists(TimeEntryController::class) || !go()->getModule('community', 'tasks')) {
			$this->markTestSkipped('The projects2 and tasks modules are required');
		}

		$this->setUpProjects();

		$this->projectId = $this->createProject($this->createType(Acl::WRITE_PERMISSION))->id;

		$this->taskList = new TaskList();
		$this->taskList->name = uniqid('private');
		$this->taskList->setAcl([Group::ID_INTERNAL => null, Group::ID_EVERYONE => null, Group::ID_ADMINS => CoreAcl::LEVEL_MANAGE]);
		$this->assertTrue($this->taskList->save(), $this->taskList->getValidationErrorsAsString());

		$this->task = new Task();
		$this->task->tasklistId = $this->taskList->id;
		$this->task->title = 'Private task';
		$this->assertTrue($this->task->save(), $this->task->getValidationErrorsAsString());
	}

	protected function tearDown(): void
	{
		if (!isset($this->authState)) {
			return;
		}
		$this->actAs(User::ID_SUPER_ADMIN);
		if (isset($this->taskList->id)) {
			TaskList::delete(['id' => $this->taskList->id]);
		}
		$this->tearDownProjects();
	}

	private function load(): void
	{
		$this->actAs($this->user->id);
		(new \ReflectionMethod(TimeEntryController::class, 'actionLoad'))->invoke(new TimeEntryController(), [
			'project_id' => $this->projectId,
			'task_id' => $this->task->id
		]);
	}

	public function testUnreadableTask(): void
	{
		// Refused before the task is copied, not only by the permission check of renderForm()
		try {
			$this->load();
			$this->fail('The unreadable task was accepted');
		} catch (AccessDenied $e) {
			$this->assertStringNotContainsString('renderForm', $e->getTraceAsString());
		}
	}

	public function testReadableTask(): void
	{
		$taskList = TaskList::findById($this->taskList->id);
		$taskList->setAcl([$this->user->getPersonalGroup()->id => CoreAcl::LEVEL_READ]);
		$this->assertTrue($taskList->save(), $taskList->getValidationErrorsAsString());
		(new \ReflectionProperty(\go\core\acl\model\AclItemEntity::class, 'aclEntityCache'))->setValue(null, []);

		// The task check passes. The form may still be refused later because the user can't book time for the
		// responsible user of the task, which renderForm() checks.
		try {
			$this->load();
			$this->assertTrue(true);
		} catch (AccessDenied $e) {
			$this->assertStringContainsString('renderForm', $e->getTraceAsString());
		}
	}
}
