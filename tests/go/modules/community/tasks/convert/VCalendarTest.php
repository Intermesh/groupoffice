<?php

namespace go\modules\community\tasks\convert;

use go\core\auth\TemporaryState;
use go\core\fs\File;
use go\core\model\Acl;
use go\core\model\User;
use go\modules\community\tasks\model\Task;
use go\modules\community\tasks\model\TaskList;
use PHPUnit\Framework\TestCase;

/**
 * An ICS import that matches an existing task by UID may only change it when the user may write it.
 */
class VCalendarTest extends TestCase
{
	private $authState;
	private string $prefix;
	private User $user;

	/** @var TaskList[] */
	private array $taskLists = [];

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));

		$this->prefix = uniqid('icstest');
		$this->user = new User();
		$this->user->setValues([
			'username' => $this->prefix,
			'displayName' => $this->prefix,
			'email' => $this->prefix . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), $this->user->getValidationErrorsAsString());

		foreach (['write' => Acl::LEVEL_WRITE, 'create' => Acl::LEVEL_CREATE] as $name => $level) {
			$taskList = new TaskList();
			$taskList->name = $this->prefix . $name;
			$taskList->setAcl([$this->user->getPersonalGroup()->id => $level]);
			$this->assertTrue($taskList->save(), $taskList->getValidationErrorsAsString());
			$this->taskLists[$name] = $taskList;
		}
	}

	protected function tearDown(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		TaskList::delete(['id' => array_map(fn($tl) => $tl->id, $this->taskLists)]);
		User::delete(['id' => $this->user->id]);
		go()->setAuthState($this->authState);
	}

	private function createTask(string $taskList, string $uid): Task
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		$task = new Task();
		$task->tasklistId = $this->taskLists[$taskList]->id;
		$task->title = 'Original';
		$task->setUid($uid);
		$this->assertTrue($task->save(), $task->getValidationErrorsAsString());
		return $task;
	}

	private function import(string $taskList, string $uid): array
	{
		$file = File::tempFile('ics');
		$file->putContents("BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Test//EN\r\nBEGIN:VTODO\r\nUID:" . $uid . "\r\n" .
			"DTSTAMP:20261001T100000Z\r\nSUMMARY:Changed\r\nEND:VTODO\r\nEND:VCALENDAR\r\n");

		go()->setAuthState(new TemporaryState($this->user->id));
		return Task::findConverter('ics')->importFile($file, ['values' => ['tasklistId' => $this->taskLists[$taskList]->id]]);
	}

	private function storedTitle(Task $task): string
	{
		return go()->getDbConnection()
			->selectSingleValue('title')
			->from('tasks_task')
			->where('id', '=', $task->id)
			->single();
	}

	public function testImportDoesNotChangeTaskUserMayOnlyCreate(): void
	{
		$uid = $this->prefix . '-createonly';
		$task = $this->createTask('create', $uid);

		$result = $this->import('create', $uid);

		$this->assertSame(0, $result['count']);
		$this->assertSame('Original', $this->storedTitle($task));
	}

	public function testImportChangesWritableTask(): void
	{
		$uid = $this->prefix . '-writable';
		$task = $this->createTask('write', $uid);

		$result = $this->import('write', $uid);

		$this->assertSame(1, $result['count'], implode("\n", $result['errors']));
		$this->assertSame('Changed', $this->storedTitle($task));
	}
}
