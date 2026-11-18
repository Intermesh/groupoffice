<?php

namespace go\core\model;

use GO\Files\Model\Folder;
use go\core\auth\TemporaryState;
use go\core\controller\Module as ModuleController;
use go\core\controller\Search as SearchController;
use go\core\db\Query;
use go\core\orm\EntityType;
use go\modules\community\notes\model\Note;
use go\modules\community\notes\model\NoteBook;
use PHPUnit\Framework\TestCase;

/**
 * Entities that are left out of the global search keep their core_search record, because links join it for the name
 * and ACL, but they lose their keywords and are left out of Search/query.
 */
class SearchTest extends TestCase
{
	private static NoteBook $noteBook;
	private static User $user;
	private static array $originalExcluded;
	private static $authState;

	private Note $note;
	private Note $linkedNote;
	private ?Folder $folder = null;

	public static function setUpBeforeClass(): void
	{
		self::$authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		self::$originalExcluded = Settings::get()->getSearchExcludedEntities();

		try {
			self::$noteBook = new NoteBook();
			self::$noteBook->name = "Search test " . uniqid();
			if (!self::$noteBook->save()) {
				throw new \Exception(self::$noteBook->getValidationErrorsAsString());
			}

			$name = uniqid('searchuser');
			self::$user = new User();
			self::$user->setValues([
				'username' => $name,
				'displayName' => $name,
				'email' => $name . '@example.com',
				'password' => 'Pa55word!!xyz'
			]);
			if (!self::$user->save()) {
				throw new \Exception(self::$user->getValidationErrorsAsString());
			}
		} catch (\Throwable $e) {
			self::tearDownAfterClass();
			throw $e;
		}
	}

	public static function tearDownAfterClass(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		self::setExcluded(self::$originalExcluded);
		self::rebuild();

		if (isset(self::$noteBook->id)) {
			NoteBook::delete(['id' => self::$noteBook->id]);
		}
		if (isset(self::$user->id)) {
			User::delete(['id' => self::$user->id]);
		}
		go()->setAuthState(self::$authState);
	}

	protected function setUp(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		self::setExcluded([]);

		$this->note = $this->createNote("Searchexcludetest " . uniqid());
		$this->linkedNote = $this->createNote("Linked " . uniqid());
		$this->assertNotFalse(Link::create($this->linkedNote, $this->note));
	}

	protected function tearDown(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		Note::delete(['id' => [$this->note->id, $this->linkedNote->id]]);

		if ($this->folder) {
			\GO::$ignoreAclPermissions = true;
			$this->folder->delete(true);
			\GO::$ignoreAclPermissions = false;
		}
	}

	private function createNote(string $name): Note
	{
		$note = new Note();
		$note->noteBookId = self::$noteBook->id;
		$note->name = $name;
		$note->content = "test";
		$this->assertTrue($note->save(), $note->getValidationErrorsAsString());
		return $note;
	}

	private static function setExcluded(array $entities): void
	{
		$settings = Settings::get();
		$settings->setSearchExcludedEntities($entities);
		$settings->save();
	}

	/**
	 * Rebuild the search records marked for a rebuild, as "Update search index" in Tools does.
	 */
	private static function rebuild(): void
	{
		// The rebuild flushes its output, so add two levels to keep it out of the PHPUnit output
		ob_start();
		ob_start();
		try {
			Note::rebuildSearchForEntity();
			Folder::model()->rebuildSearchCache();
		} finally {
			ob_end_clean();
			ob_end_clean();
		}
	}

	private static function searchId(string $entity, int $entityId): ?int
	{
		$id = (new Query)
			->selectSingleValue('id')
			->from('core_search')
			->where(['entityTypeId' => EntityType::findByName($entity)->getId(), 'entityId' => $entityId])
			->single();
		return $id ? (int) $id : null;
	}

	private static function wordCount(int $searchId): int
	{
		return (int) (new Query)
			->selectSingleValue('count(*)')
			->from('core_search_word')
			->where(['searchId' => $searchId])
			->single();
	}

	private function textSearch(string $text): array
	{
		$response = (new SearchController())->query(['filter' => ['text' => $text]]);
		return $response->getArray()['ids'] ?? [];
	}

	public function testExcludedEntityKeepsSearchRecordAndLinksButLosesKeywords(): void
	{
		$searchId = self::searchId('Note', $this->note->id);
		$this->assertNotNull($searchId);
		$this->assertGreaterThan(0, self::wordCount($searchId));
		$this->assertContains($searchId, array_map('intval', $this->textSearch($this->note->name)));

		self::setExcluded(['Note']);

		// Saving the setting removed the words of existing notes, but not the search record
		$this->assertSame($searchId, self::searchId('Note', $this->note->id));
		$this->assertSame(0, self::wordCount($searchId));

		// Saving a note again doesn't add words
		$this->note->content = "changed";
		$this->assertTrue($this->note->save());
		$this->assertSame(0, self::wordCount($searchId));

		// Not in the search results, also not without a text
		$this->assertEmpty($this->textSearch($this->note->name));
		$response = (new SearchController())->query(['filter' => ['entities' => [['name' => 'Note']]]]);
		$this->assertEmpty($response->getArray()['ids']);

		// The link to the note still works
		$links = Link::find()
			->where(['fromEntityTypeId' => Note::entityType()->getId(), 'fromId' => $this->linkedNote->id])
			->andWhere(['toEntityTypeId' => Note::entityType()->getId(), 'toId' => $this->note->id])
			->all();
		$this->assertCount(1, $links);
		$this->assertSame($searchId, (int) $links[0]->toSearchId);

		// Including it again marks the records for a rebuild, which adds the words again
		self::setExcluded([]);
		$this->assertTrue((bool) go()->getDbConnection()->selectSingleValue('rebuild')->from('core_search')->where(['id' => $searchId])->single());
		self::rebuild();
		$this->assertGreaterThan(0, self::wordCount($searchId));
		$this->assertContains($searchId, array_map('intval', $this->textSearch($this->note->name)));
	}

	public function testExcludedLegacyEntityLosesKeywords(): void
	{
		\GO::$ignoreAclPermissions = true;
		$this->folder = Folder::model()->findByPath('users/searchexcludetest-' . uniqid(), true);
		\GO::$ignoreAclPermissions = false;

		$searchId = self::searchId('Folder', $this->folder->id);
		$this->assertNotNull($searchId);
		$this->assertGreaterThan(0, self::wordCount($searchId));

		self::setExcluded(['Folder']);
		$this->assertSame(0, self::wordCount($searchId));

		$this->assertTrue($this->folder->cacheSearchRecord());
		$this->assertSame($searchId, self::searchId('Folder', $this->folder->id));
		$this->assertSame(0, self::wordCount($searchId));

		self::setExcluded([]);
		$this->assertTrue($this->folder->cacheSearchRecord());
		$this->assertGreaterThan(0, self::wordCount($searchId));
	}

	public function testUnknownEntitiesAreSkipped(): void
	{
		self::setExcluded(['Note', 'NoSuchEntity', 'Note']);
		$this->assertSame(['Note'], Settings::get()->getSearchExcludedEntities());

		// The web client reads it from the core module settings to hide the entity in the search panel
		$this->assertSame(['Note'], Settings::get()->toArray()['searchExcludedEntities'] ?? null);
	}

	public function testUserMayNotChangeExcludedEntities(): void
	{
		$coreId = Module::findByName('core', 'core')->id;

		go()->setAuthState(new TemporaryState(self::$user->id));
		$result = (new ModuleController())->set(['update' => [$coreId => ['settings' => ['searchExcludedEntities' => ['Note']]]]])->getArray();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));

		$this->assertSame('forbidden', $result['notUpdated'][$coreId]->type ?? null);

		Settings::flushCache();
		go()->getCache()->delete(Settings::class);
		$this->assertSame([], Settings::get()->getSearchExcludedEntities());
	}
}
