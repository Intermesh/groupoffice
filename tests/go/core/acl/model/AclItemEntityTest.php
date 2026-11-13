<?php
namespace go\core\acl\model;

use go\core\auth\TemporaryState;
use go\core\model\Acl;
use go\core\model\User;
use go\modules\community\notes\controller\Note as NoteController;
use go\modules\community\notes\model\Note;
use go\modules\community\notes\model\NoteBook;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Moving an item to another container, like a note to another note book, requires write permission on the old and
 * the new container. Uses notes because a note is an AclItemEntity of its note book.
 */
class AclItemEntityTest extends TestCase
{
	private $authState;
	private User $user;
	private string $prefix;

	/** @var NoteBook[] */
	private array $noteBooks = [];

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));

		$this->prefix = uniqid('aclitem');
		$this->user = new User();
		$this->user->setValues([
			'username' => $this->prefix,
			'displayName' => $this->prefix,
			'email' => $this->prefix . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), $this->user->getValidationErrorsAsString());

		$groupId = $this->user->getPersonalGroup()->id;
		foreach (['write' => Acl::LEVEL_WRITE, 'otherWrite' => Acl::LEVEL_WRITE, 'read' => Acl::LEVEL_READ, 'create' => Acl::LEVEL_CREATE, 'none' => null] as $name => $level) {
			$noteBook = new NoteBook();
			$noteBook->name = $this->prefix . $name;
			if ($level) {
				$noteBook->setAcl([$groupId => $level]);
			}
			$this->assertTrue($noteBook->save(), $noteBook->getValidationErrorsAsString());
			$this->noteBooks[$name] = $noteBook;
		}
	}

	protected function tearDown(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		NoteBook::delete(['id' => array_map(fn($nb) => $nb->id, $this->noteBooks)]);
		User::delete(['id' => $this->user->id]);
		go()->setAuthState($this->authState);
	}

	private function createNote(string $noteBook): Note
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		$note = new Note();
		$note->noteBookId = $this->noteBooks[$noteBook]->id;
		$note->name = 'Test';
		$note->content = 'Test';
		$this->assertTrue($note->save(), $note->getValidationErrorsAsString());
		return $note;
	}

	private function storedNoteBookId(Note $note): int
	{
		return (int) go()->getDbConnection()
			->selectSingleValue('noteBookId')
			->from('notes_note')
			->where('id', '=', $note->id)
			->single();
	}

	private function move(Note $note, int $noteBookId): array
	{
		go()->setAuthState(new TemporaryState($this->user->id));
		return (new NoteController())->set(['update' => [$note->id => ['noteBookId' => $noteBookId]]])->getArray();
	}

	public static function forbiddenMoves(): array
	{
		return [
			'to a note book without access' => ['write', 'none'],
			'to a read only note book' => ['write', 'read'],
			'to a note book where the user may only create' => ['write', 'create'],
			'from a read only note book' => ['read', 'write'],
		];
	}

	#[DataProvider('forbiddenMoves')]
	public function testMoveNeedsWritePermissionOnBothNoteBooks(string $from, string $to): void
	{
		$note = $this->createNote($from);

		$result = $this->move($note, $this->noteBooks[$to]->id);

		$this->assertArrayNotHasKey($note->id, $result['updated'] ?? []);
		$this->assertArrayHasKey($note->id, $result['notUpdated'] ?? []);
		$this->assertSame($this->noteBooks[$from]->id, $this->storedNoteBookId($note));
	}

	public function testMoveToNoteBookThatDoesNotExist(): void
	{
		$note = $this->createNote('write');

		$result = $this->move($note, 999999999);

		$this->assertArrayHasKey($note->id, $result['notUpdated'] ?? []);
		$this->assertSame($this->noteBooks['write']->id, $this->storedNoteBookId($note));
	}

	public function testMoveBetweenWritableNoteBooks(): void
	{
		$note = $this->createNote('write');

		$result = $this->move($note, $this->noteBooks['otherWrite']->id);

		$this->assertArrayNotHasKey($note->id, $result['notUpdated'] ?? [], var_export($result['notUpdated'][$note->id] ?? null, true));
		$this->assertSame($this->noteBooks['otherWrite']->id, $this->storedNoteBookId($note));
	}

	/**
	 * Other code, like imports, saves without the controller's permission check.
	 */
	public function testModelSaveMayNotMoveToNoteBookWithoutAccess(): void
	{
		$note = $this->createNote('write');

		go()->setAuthState(new TemporaryState($this->user->id));
		$note = Note::findById($note->id);
		$this->assertTrue($note->hasPermissionLevel(Acl::LEVEL_WRITE));
		$note->noteBookId = $this->noteBooks['none']->id;

		$this->assertFalse($note->hasPermissionLevel(Acl::LEVEL_WRITE));
		$this->assertFalse($note->save());
		$this->assertArrayHasKey('noteBookId', $note->getValidationErrors());
		$this->assertSame($this->noteBooks['write']->id, $this->storedNoteBookId($note));
	}

	public function testAdminMayMove(): void
	{
		$note = $this->createNote('write');

		$note = Note::findById($note->id);
		$note->noteBookId = $this->noteBooks['none']->id;
		$this->assertTrue($note->save(), $note->getValidationErrorsAsString());
		$this->assertSame($this->noteBooks['none']->id, $this->storedNoteBookId($note));
	}
}
