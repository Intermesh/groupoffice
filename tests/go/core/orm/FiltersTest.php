<?php

namespace go\core\orm;

use go\core\db\Criteria;
use go\core\db\Query as DbQuery;
use go\core\jmap\exception\InvalidArguments;
use go\core\model\Link;
use go\core\model\User;
use go\core\orm\exception\SaveException;
use go\modules\community\notes\model\Note;
use go\modules\community\notes\model\NoteBook;
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
		$filter['noteBookId'] = $this->noteBook->id;
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
}
