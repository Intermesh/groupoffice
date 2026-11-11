<?php

namespace go\core\data\convert;

use go\core\auth\TemporaryState;
use go\core\fs\File;
use go\core\model\Acl;
use go\core\model\ImportMapping;
use go\core\model\User;
use go\modules\community\notes\model\Note;
use go\modules\community\notes\model\NoteBook;
use PHPUnit\Framework\TestCase;

/**
 * A CSV import that updates existing items may only change items the user may write. Uses notes because a note is an
 * item of a note book with an ACL.
 */
class SpreadsheetTest extends TestCase
{
	private $authState;
	private string $prefix;
	private User $user;

	/** @var NoteBook[] */
	private array $noteBooks = [];

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));

		$this->prefix = uniqid('csvtest');
		$this->user = new User();
		$this->user->setValues([
			'username' => $this->prefix,
			'displayName' => $this->prefix,
			'email' => $this->prefix . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), $this->user->getValidationErrorsAsString());

		$groupId = $this->user->getPersonalGroup()->id;
		foreach (['write' => Acl::LEVEL_WRITE, 'create' => Acl::LEVEL_CREATE, 'none' => null] as $name => $level) {
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
		ImportMapping::delete(['checksum' => $this->checksum()]);
		go()->setAuthState($this->authState);
	}

	private function headers(): array
	{
		// A unique header so the import mapping that the import saves can be deleted
		return ['name', 'content', $this->prefix];
	}

	private function checksum(): string
	{
		return md5(implode(",", $this->headers()));
	}

	private function createNote(string $noteBook, string $name): Note
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		$note = new Note();
		$note->noteBookId = $this->noteBooks[$noteBook]->id;
		$note->name = $name;
		$note->content = 'original';
		$this->assertTrue($note->save(), $note->getValidationErrorsAsString());
		return $note;
	}

	private function storedNote(Note $note): array
	{
		return go()->getDbConnection()
			->select(['noteBookId', 'content'])
			->from('notes_note')
			->where('id', '=', $note->id)
			->single();
	}

	private function import(array $row, array $params): array
	{
		$file = File::tempFile('csv');
		$fp = $file->open('w');
		fputcsv($fp, $this->headers(), ',', '"', '');
		fputcsv($fp, $row, ',', '"', '');
		fclose($fp);

		go()->setAuthState(new TemporaryState($this->user->id));
		return Note::findConverter('csv')->importFile($file, array_merge([
			'mapping' => [
				'name' => ['csvIndex' => 0, 'fixed' => null],
				'content' => ['csvIndex' => 1, 'fixed' => null]
			]
		], $params));
	}

	private function noteBookNoteCount(string $noteBook): int
	{
		return (int) go()->getDbConnection()
			->selectSingleValue('count(*)')
			->from('notes_note')
			->where('noteBookId', '=', $this->noteBooks[$noteBook]->id)
			->single();
	}

	public function testUpdateByDoesNotMoveNoteOfOtherNoteBook(): void
	{
		$name = $this->prefix . 'victim';
		$victim = $this->createNote('none', $name);

		$this->import([$name, 'changed', ''], [
			'updateBy' => 'name',
			'values' => ['noteBookId' => $this->noteBooks['write']->id]
		]);

		$stored = $this->storedNote($victim);
		$this->assertEquals($this->noteBooks['none']->id, $stored['noteBookId']);
		$this->assertSame('original', $stored['content']);
	}

	public function testUpdateByDoesNotChangeNoteUserMayOnlyCreate(): void
	{
		$name = $this->prefix . 'createonly';
		$note = $this->createNote('create', $name);

		$this->import([$name, 'changed', ''], [
			'updateBy' => 'name',
			'values' => ['noteBookId' => $this->noteBooks['create']->id]
		]);

		$this->assertSame('original', $this->storedNote($note)['content']);
		// A new note was added instead
		$this->assertSame(2, $this->noteBookNoteCount('create'));
	}

	public function testUpdateByIdMayNotMoveToNoteBookWithoutAccess(): void
	{
		$note = $this->createNote('write', $this->prefix . 'mine');

		$result = $this->import([$this->prefix . 'mine', 'changed', ''], [
			'updateBy' => 'id',
			'mapping' => [
				'id' => ['csvIndex' => -1, 'fixed' => $note->id],
				'name' => ['csvIndex' => 0, 'fixed' => null],
				'content' => ['csvIndex' => 1, 'fixed' => null]
			],
			'values' => ['noteBookId' => $this->noteBooks['none']->id]
		]);

		$this->assertSame(0, $result['count']);
		$stored = $this->storedNote($note);
		$this->assertEquals($this->noteBooks['write']->id, $stored['noteBookId']);
		$this->assertSame('original', $stored['content']);
	}

	public function testUpdateByUpdatesWritableNote(): void
	{
		$name = $this->prefix . 'writable';
		$note = $this->createNote('write', $name);

		$result = $this->import([$name, 'changed', ''], [
			'updateBy' => 'name',
			'values' => ['noteBookId' => $this->noteBooks['write']->id]
		]);

		$this->assertSame(1, $result['count'], implode("\n", $result['errors']));
		$this->assertSame('changed', $this->storedNote($note)['content']);
		$this->assertSame(1, $this->noteBookNoteCount('write'));
	}
}
