<?php
namespace go\modules\community\comments\model;

use go\core\auth\TemporaryState;
use go\core\model\Acl;
use go\core\model\Group;
use go\core\model\User;
use go\modules\community\comments\controller\Comment as CommentController;
use go\modules\community\tasks\model\Task;
use go\modules\community\tasks\model\TaskList;
use PHPUnit\Framework\TestCase;

/**
 * Comments in a section, like the private notes and drafts of a support ticket, are only for users who may change the
 * entity. A user who may only read it, like the customer of a ticket, only gets the comments without a section.
 */
class CommentTest extends TestCase
{
	private $authState;
	private static User $reader;
	private static User $writer;
	private static TaskList $taskList;
	private static Task $task;
	private static Comment $public;
	private static Comment $private;

	public static function setUpBeforeClass(): void
	{
		$authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		try {
			self::$reader = self::createUser('commentreader');
			self::$writer = self::createUser('commentwriter');

			self::$taskList = new TaskList();
			self::$taskList->name = uniqid('comments');
			self::$taskList->setAcl([
				Group::ID_INTERNAL => null,
				Group::ID_EVERYONE => null,
				Group::ID_ADMINS => Acl::LEVEL_MANAGE,
				self::$reader->getPersonalGroup()->id => Acl::LEVEL_READ,
				self::$writer->getPersonalGroup()->id => Acl::LEVEL_WRITE
			]);
			self::assertTrue(self::$taskList->save(), self::$taskList->getValidationErrorsAsString());

			self::$task = new Task();
			self::$task->tasklistId = self::$taskList->id;
			self::$task->title = 'Task with comments';
			self::assertTrue(self::$task->save(), self::$task->getValidationErrorsAsString());

			self::$public = self::createComment(null, 'Public reply');
			self::$private = self::createComment('private', 'Private note');
		} catch (\Throwable $e) {
			self::tearDownAfterClass();
			throw $e;
		} finally {
			go()->setAuthState($authState);
		}
	}

	public static function tearDownAfterClass(): void
	{
		$authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		if (isset(self::$task->id)) {
			Comment::delete(['entityId' => self::$task->id, 'entityTypeId' => Task::entityType()->getId()]);
		}
		if (isset(self::$taskList->id)) {
			TaskList::delete(['id' => self::$taskList->id]);
		}
		$ids = [];
		foreach (['reader', 'writer'] as $prop) {
			if (isset(self::$$prop)) {
				$ids[] = self::$$prop->id;
			}
		}
		if ($ids) {
			User::delete(['id' => $ids]);
		}
		go()->setAuthState($authState);
	}

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
	}

	protected function tearDown(): void
	{
		go()->setAuthState($this->authState);
	}

	private static function createUser(string $prefix): User
	{
		$name = uniqid($prefix);
		$user = new User();
		$user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		self::assertTrue($user->save(), $user->getValidationErrorsAsString());
		return $user;
	}

	private static function createComment(?string $section, string $text): Comment
	{
		$comment = new Comment();
		$comment->setEntity(self::$task);
		$comment->section = $section;
		$comment->text = $text;
		self::assertTrue($comment->save(), $comment->getValidationErrorsAsString());
		return $comment;
	}

	private function get(int $userId, array $ids): \ArrayObject
	{
		go()->setAuthState(new TemporaryState($userId));
		return (new CommentController())->get(['ids' => $ids]);
	}

	public function testReaderOnlyGetsCommentsWithoutSection(): void
	{
		$result = $this->get(self::$reader->id, [self::$public->id, self::$private->id]);

		$this->assertEquals([self::$public->id], array_column($result['list'], 'id'));
		$this->assertEquals([self::$private->id], $result['notFound']);
	}

	public function testWriterGetsCommentsInSections(): void
	{
		$result = $this->get(self::$writer->id, [self::$public->id, self::$private->id]);

		$this->assertEqualsCanonicalizing([self::$public->id, self::$private->id], array_column($result['list'], 'id'));
		$this->assertEmpty($result['notFound']);
	}

	private function queryTasks(int $userId, array $filter): array
	{
		go()->setAuthState(new TemporaryState($userId));
		$result = (new \go\modules\community\tasks\controller\Task())->query([
			'filter' => ['tasklistId' => self::$taskList->id] + $filter
		]);
		return $result['ids'];
	}

	/**
	 * The "comment" filter of an entity must not reveal the text of comments the user can't read
	 */
	public function testCommentFilterOnlySearchesReadableComments(): void
	{
		$this->assertEquals([self::$task->id], $this->queryTasks(self::$reader->id, ['comment' => '%Public reply%']));
		$this->assertEquals([], $this->queryTasks(self::$reader->id, ['comment' => '%Private note%']));
		$this->assertEquals([self::$task->id], $this->queryTasks(self::$writer->id, ['comment' => '%Private note%']));
	}

	public function testCommentedAtOnlyUsesReadableComments(): void
	{
		$authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		$later = self::createComment('private', 'Later private note');
		$later->date = new \go\core\util\DateTime('+1 day');
		$this->assertTrue($later->save());
		go()->setAuthState($authState);

		try {
			$filter = ['commentedAt' => '>' . (new \DateTime('+12 hours'))->format('Y-m-d H:i:s')];
			$this->assertEquals([], $this->queryTasks(self::$reader->id, $filter));
			$this->assertEquals([self::$task->id], $this->queryTasks(self::$writer->id, $filter));
		} finally {
			go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
			Comment::delete(['id' => $later->id]);
		}
	}

	public function testReaderCannotCreateCommentInSection(): void
	{
		go()->setAuthState(new TemporaryState(self::$reader->id));
		$entity = ['entity' => 'Task', 'entityId' => self::$task->id, 'text' => 'From the reader'];
		$result = (new CommentController())->set(['create' => [
			'private' => $entity + ['section' => 'private'],
			'public' => $entity
		]]);

		$this->assertArrayHasKey('private', $result['notCreated']);
		$this->assertArrayHasKey('public', $result['created']);

		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		Comment::delete(['id' => $result['created']['public']['id']]);
	}

	/**
	 * A draft isn't added to the entity until it's posted, so it doesn't change the status of a ticket. Posting it sets
	 * its date.
	 */
	public function testDraftIsAddedWhenPosted(): void
	{
		$authState = go()->getAuthState();
		$alerts = \go\core\model\Alert::$enabled;
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		\go\core\model\Alert::$enabled = true;

		$task = new Task();
		$task->tasklistId = self::$taskList->id;
		$task->title = 'Task with a draft';
		$task->setProgress('completed');
		$this->assertTrue($task->save(), $task->getValidationErrorsAsString());

		try {
			$draft = new Comment();
			$draft->setEntity($task);
			$draft->section = Comment::SECTION_DRAFT;
			$draft->text = 'Draft reply';
			$draft->date = new \go\core\util\DateTime('-1 day');
			$this->assertTrue($draft->save(), $draft->getValidationErrorsAsString());
			$this->assertFalse($draft->isPosted());

			$this->assertEquals('completed', Task::findById($task->id)->getProgress(), 'A draft must not change the task');

			$draft = Comment::findById($draft->id);
			$draft->section = null;
			$this->assertTrue($draft->isPosted());
			$this->assertTrue($draft->save(), $draft->getValidationErrorsAsString());

			$this->assertEquals('needs-action', Task::findById($task->id)->getProgress(), 'Posting the draft adds it');
			$this->assertGreaterThan(new \DateTime('-1 hour'), Comment::findById($draft->id)->date);
		} finally {
			\go\core\model\Alert::$enabled = $alerts;
			Comment::delete(['entityId' => $task->id, 'entityTypeId' => Task::entityType()->getId()]);
			Task::delete(['id' => $task->id]);
			go()->setAuthState($authState);
		}
	}
}
