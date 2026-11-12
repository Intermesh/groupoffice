<?php

namespace go\core\orm;

use go\core\db\Criteria;
use go\core\db\Query as DbQuery;
use go\core\jmap\exception\InvalidArguments;
use go\core\model\Link;
use go\core\model\User;
use go\core\orm\exception\SaveException;
use go\core\util\DateTime;
use go\modules\community\comments\model\Comment;
use go\modules\community\notes\model\Note;
use go\modules\community\notes\model\NoteBook;
use go\modules\community\test\model\A;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FiltersTest extends TestCase
{
	private NoteBook $noteBook;

	/**
	 * @var Note[]
	 */
	private array $notes = [];

	protected function setUp(): void
	{
		$this->noteBook = new NoteBook();
		$this->noteBook->name = "Filters test " . uniqid();
		if (!$this->noteBook->save()) {
			throw new SaveException($this->noteBook);
		}
	}

	protected function tearDown(): void
	{
		$ids = array_map(fn(Note $n) => $n->id, $this->notes);
		if (!empty($ids)) {
			$typeId = Note::entityType()->getId();
			go()->getDbConnection()->delete('core_link', (new DbQuery())
				->where('fromEntityTypeId', '=', $typeId)->andWhere('fromId', 'IN', $ids)
				->orWhere((new Criteria())->where('toEntityTypeId', '=', $typeId)->andWhere('toId', 'IN', $ids))
			)->execute();

			Comment::delete(['entityTypeId' => $typeId, 'entityId' => $ids]);
			Note::delete(['id' => $ids]);
		}

		NoteBook::delete(['id' => $this->noteBook->id]);
	}

	private function createNote(string $name): Note
	{
		$note = new Note();
		$note->noteBookId = $this->noteBook->id;
		$note->name = $name;
		$note->content = "test";
		if (!$note->save()) {
			throw new SaveException($note);
		}
		$this->notes[] = $note;
		return $note;
	}

	/**
	 * @return int[]
	 */
	private function findNoteIds(array $filter): array
	{
		// Wrapped so that it also restricts filters with an operator
		$filter = ['operator' => 'AND', 'conditions' => [['noteBookId' => $this->noteBook->id], $filter]];
		return array_map('intval', Note::find()->selectSingleValue('n.id')->filter($filter)->orderBy(['n.id' => 'ASC'])->all());
	}

	public static function createdByProvider(): array
	{
		return [
			'id' => [fn(User $u) => [$u->id], true],
			'id as string' => [fn(User $u) => [(string) $u->id], true],
			'me' => [fn(User $u) => ["{{me}}"], true],
			'display name' => [fn(User $u) => [$u->displayName], true],
			'unknown id' => [fn(User $u) => [PHP_INT_MAX], false],
			'unknown name' => [fn(User $u) => ["No such user " . uniqid()], false],
			// The first value used to decide whether all values were ID's or names
			'unknown name then id' => [fn(User $u) => ["No such user " . uniqid(), $u->id], true],
			'unknown id then name' => [fn(User $u) => [PHP_INT_MAX, $u->displayName], true],
		];
	}

	#[DataProvider('createdByProvider')]
	public function testCreatedByAndModifiedBy(callable $value, bool $expectMatch): void
	{
		$note = $this->createNote("createdBy");
		$user = User::findById(go()->getUserId(), ['id', 'displayName']);

		foreach (['createdBy', 'modifiedBy'] as $filterName) {
			$expected = $expectMatch ? [$note->id] : [];
			$this->assertEquals($expected, $this->findNoteIds([$filterName => $value($user)]), $filterName);
		}
	}

	public function testLinkFilter(): void
	{
		$a = $this->createNote("a");
		$b = $this->createNote("b");
		$c = $this->createNote("c");
		$d = $this->createNote("d");

		Link::create($a, $b);
		Link::create($c, $b);
		Link::create($a, $d);

		$this->assertEquals([$b->id, $d->id], $this->findNoteIds(['link' => ['entity' => 'Note', 'id' => $a->id]]));

		// Two link filters must each use their own join alias
		$this->assertEquals([$b->id], $this->findNoteIds([
			'operator' => 'AND',
			'conditions' => [
				['link' => ['entity' => 'Note', 'id' => $a->id]],
				['link' => ['entity' => 'Note', 'id' => $c->id]]
			]
		]));
	}

	public function testLinkFilterWithNot(): void
	{
		$a = $this->createNote("a");
		$b = $this->createNote("b");
		$c = $this->createNote("c");

		Link::create($a, $b);
		Link::create($c, $b);

		// b is linked to a and c, so it must be excluded even though it also has a link to c
		$this->assertEquals([$a->id, $c->id], $this->findNoteIds([
			'operator' => 'NOT',
			'conditions' => [['link' => ['entity' => 'Note', 'id' => $a->id]]]
		]));
	}

	private function addComment(Note $note, string $text): void
	{
		$comment = new Comment();
		$comment->setEntity($note);
		$comment->text = $text;
		$comment->date = new DateTime();
		if (!$comment->save()) {
			throw new SaveException($comment);
		}
	}

	/**
	 * Creates notes: a with "apple", b without comments, c with "apple" and "pear" and d with "pear"
	 *
	 * @return Note[]
	 */
	private function createCommentedNotes(): array
	{
		$a = $this->createNote("a");
		$b = $this->createNote("b");
		$c = $this->createNote("c");
		$d = $this->createNote("d");

		$this->addComment($a, "apple");
		$this->addComment($c, "apple");
		$this->addComment($c, "pear");
		$this->addComment($d, "pear");

		return [$a, $b, $c, $d];
	}

	public function testCommentFilter(): void
	{
		[$a, $b, $c, $d] = $this->createCommentedNotes();

		$this->assertEquals([$a->id, $c->id], $this->findNoteIds(['comment' => '%apple%']));

		// A note without comments must still match the other condition
		$this->assertEquals([$a->id, $b->id, $c->id], $this->findNoteIds([
			'operator' => 'OR',
			'conditions' => [['comment' => '%apple%'], ['exclude' => [$a->id, $c->id, $d->id]]]
		]));

		// c has a pear comment too but must be excluded because it has an apple comment
		$this->assertEquals([$b->id, $d->id], $this->findNoteIds([
			'operator' => 'NOT',
			'conditions' => [['comment' => '%apple%']]
		]));
	}

	public function testCommentedAtFilter(): void
	{
		[$a, $b, $c, $d] = $this->createCommentedNotes();

		$this->assertEquals([$a->id, $c->id, $d->id], $this->findNoteIds(['commentedAt' => '>2000-01-01']));
		$this->assertEquals([], $this->findNoteIds(['commentedAt' => '<2000-01-01']));

		$this->assertEquals([$a->id, $b->id], $this->findNoteIds([
			'operator' => 'OR',
			'conditions' => [['commentedAt' => '<2000-01-01'], ['exclude' => [$c->id, $d->id]]]
		]));

		$this->assertEquals([$b->id], $this->findNoteIds([
			'operator' => 'NOT',
			'conditions' => [['commentedAt' => '>2000-01-01']]
		]));

		// null finds entities without comments
		$this->assertEquals([$b->id], $this->findNoteIds(['commentedAt' => null]));
		$this->assertEquals([$a->id, $c->id, $d->id], $this->findNoteIds([
			'operator' => 'NOT',
			'conditions' => [['commentedAt' => null]]
		]));
	}

	public function testLinkFilterWithUnknownEntity(): void
	{
		$this->expectException(InvalidArguments::class);
		$this->findNoteIds(['link' => ['entity' => 'NoSuchEntity']]);
	}

	public function testLinkFilterWithInvalidValue(): void
	{
		$this->expectException(InvalidArguments::class);
		$this->findNoteIds(['link' => 'Note']);
	}

	public function testCustomRelationsFilterIsRemoved(): void
	{
		$this->assertFalse(Note::getFilters()->hasFilter('customrelations'));
	}

	public function testAddColumnUsesDateTimeForDatetimeColumns(): void
	{
		$filters = (new Filters(A::class))
			->addColumn('createdAt')
			->addColumn('propA');

		$prop = new \ReflectionProperty(Filters::class, 'filters');
		$defs = $prop->getValue($filters);

		$this->assertEquals('datetime', $defs['createdat']['type']);
		$this->assertEquals('generic', $defs['propa']['type']);
	}

	public function testAddColumnThrowsForUnknownColumn(): void
	{
		$this->expectException(\InvalidArgumentException::class);
		(new Filters(A::class))->addColumn('noSuchColumn');
	}
}
