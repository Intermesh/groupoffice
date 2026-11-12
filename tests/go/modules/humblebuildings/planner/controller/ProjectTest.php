<?php
namespace go\modules\humblebuildings\planner\controller;

use GO\Base\Model\Acl as LegacyAcl;
use go\core\exception\Forbidden;
use go\core\model\Acl;
use go\core\model\Group;
use go\core\model\User;
use go\modules\community\tasks\model\TaskList;
use go\modules\legacy\projects2\ProjectsTrait;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../legacy/projects2/fixtures/ProjectsTrait.php';

/**
 * The printed planning shows a project and the tasks of task lists. The user must be able to read them.
 */
class ProjectTest extends TestCase
{
	use ProjectsTrait;

	/** @var TaskList[] */
	private array $taskLists = [];

	protected function setUp(): void
	{
		if (!class_exists(Project::class) || !go()->getModule('community', 'tasks')) {
			$this->markTestSkipped('The planner, projects2 and tasks modules are required');
		}
		$this->setUpProjects();
	}

	protected function tearDown(): void
	{
		if (!isset($this->authState)) {
			return;
		}
		$this->actAs(User::ID_SUPER_ADMIN);
		foreach ($this->taskLists as $taskList) {
			TaskList::delete(['id' => $taskList->id]);
		}
		$this->tearDownProjects();
	}

	/**
	 * The constructor checks access to the module, which needs a license, so it's skipped
	 */
	private function controller(): Project
	{
		return (new \ReflectionClass(Project::class))->newInstanceWithoutConstructor();
	}

	private function createTaskList(array $acl): TaskList
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		$taskList = new TaskList();
		$taskList->name = uniqid('planner');
		$taskList->setAcl(array_replace([Group::ID_INTERNAL => null, Group::ID_EVERYONE => null], $acl));
		$this->assertTrue($taskList->save(), $taskList->getValidationErrorsAsString());
		$this->taskLists[] = $taskList;
		return $taskList;
	}

	public function testUnreadableProject(): void
	{
		$project = $this->createProject($this->createType(0));

		$this->actAs($this->user->id);
		$this->expectException(Forbidden::class);
		$this->controller()->doHardcopy(['projectId' => $project->id, 'ids' => []]);
	}

	public function testOnlyReadableTaskListsAreShown(): void
	{
		$readable = $this->createTaskList([$this->user->getPersonalGroup()->id => Acl::LEVEL_READ]);
		$private = $this->createTaskList([]);

		$this->actAs($this->user->id);
		$ids = (new \ReflectionMethod(Project::class, 'readableTaskListIds'))->invoke($this->controller(), [$readable->id, $private->id]);
		$this->assertEquals([$readable->id], array_map('strval', $ids));
	}
}
