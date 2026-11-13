<?php
namespace go\modules\business\kanban\model;

use go\core\auth\TemporaryState;
use go\core\model\Acl;
use go\core\model\Group;
use go\core\model\User;
use go\modules\business\ModuleTablesTrait;
use go\modules\community\tasks\model\Task;
use go\modules\community\tasks\model\TaskList;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../fixtures/ModuleTablesTrait.php';

/**
 * The backlog of a board only shows the items the user may read
 */
class BoardTest extends TestCase
{
	use ModuleTablesTrait;

	private $authState;
	private User $user;
	private ?Board $board = null;
	/** @var TaskList[] */
	private array $taskLists = [];

	protected function setUp(): void
	{
		if (!go()->getModule('community', 'tasks')) {
			$this->markTestSkipped('The tasks module is required');
		}

		$this->authState = go()->getAuthState();
		$this->actAs(User::ID_SUPER_ADMIN);

		$path = go()->getEnvironment()->getInstallPath() . '/go/modules/business/';
		$this->installModuleTables('business', 'kanban', $path . 'kanban/install/install.sql');

		$name = uniqid('kanbanuser');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), $this->user->getValidationErrorsAsString());

		$this->board = new Board();
		$this->board->name = $name;
		$this->board->setAcl([$this->user->getPersonalGroup()->id => Acl::LEVEL_READ]);
		$this->assertTrue($this->board->save(), $this->board->getValidationErrorsAsString());
	}

	protected function tearDown(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		foreach ($this->taskLists as $taskList) {
			TaskList::delete(['id' => $taskList->id]);
		}
		if (isset($this->board->id)) {
			Board::delete(['id' => $this->board->id]);
		}
		User::delete(['id' => $this->user->id]);
		$this->uninstallModuleTables();
		go()->setAuthState($this->authState);
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
		(new \ReflectionProperty(\go\core\acl\model\AclItemEntity::class, 'aclEntityCache'))->setValue(null, []);
	}

	private function createTask(array $acl): Task
	{
		$taskList = new TaskList();
		$taskList->name = uniqid('backlog');
		$taskList->setAcl(array_replace([Group::ID_INTERNAL => null, Group::ID_EVERYONE => null], $acl));
		$this->assertTrue($taskList->save(), $taskList->getValidationErrorsAsString());
		$this->taskLists[] = $taskList;

		$task = new Task();
		$task->tasklistId = $taskList->id;
		$task->title = uniqid('backlogtask');
		$this->assertTrue($task->save(), $task->getValidationErrorsAsString());
		return $task;
	}

	public function testBacklogOnlyHasReadableTasks(): void
	{
		$private = $this->createTask([]);
		$readable = $this->createTask([$this->user->getPersonalGroup()->id => Acl::LEVEL_READ]);

		$this->actAs($this->user->id);
		$cards = Board::getBacklogCards(['boardId' => $this->board->id, 'limit' => 1000]);
		$ids = array_map('strval', array_column(array_filter($cards, fn($c) => $c['entity'] === 'Task'), 'entityId'));

		$this->assertContains($readable->id, $ids);
		$this->assertNotContains($private->id, $ids);
	}
}
