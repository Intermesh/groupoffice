<?php
namespace go\modules\business\mcp\tool;

use go\core\auth\TemporaryState;
use go\core\model\Acl;
use go\core\model\AppPassword;
use go\core\model\User;
use go\modules\business\mcp\McpFixtureTrait;
use go\modules\business\mcp\model\McpKey;
use go\modules\business\mcp\Server;
use go\modules\community\notes\model\Note;
use go\modules\community\notes\model\NoteBook;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../McpFixtureTrait.php';

/**
 * The tools only reach the entities picked for the key, only read, and only what the user may read
 */
class ToolsTest extends TestCase
{
	use McpFixtureTrait;

	private $authState;
	private static McpKey $key;
	private static NoteBook $sharedBook;
	private static NoteBook $privateBook;
	private static Note $sharedNote;
	private static Note $privateNote;

	public static function setUpBeforeClass(): void
	{
		self::setUpMcp();
		$authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		try {
			self::$key = self::createMcpKey(['Note' => false, 'NoteBook' => false]);

			// The user may read one note book, not the other
			self::$sharedBook = self::createBook([self::$mcpUser->getPersonalGroup()->id => Acl::LEVEL_READ]);
			self::$privateBook = self::createBook([]);
			self::$sharedNote = self::createNote(self::$sharedBook, 'Shared budget note');
			self::$privateNote = self::createNote(self::$privateBook, 'Private budget note');
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
		foreach (['sharedBook', 'privateBook'] as $prop) {
			if (isset(self::$$prop)) {
				Note::delete(['noteBookId' => self::$$prop->id]);
				NoteBook::delete(['id' => self::$$prop->id]);
			}
		}
		go()->setAuthState($authState);
		self::tearDownMcp();
	}

	private static function createBook(array $acl): NoteBook
	{
		$book = new NoteBook();
		$book->name = uniqid('mcpbook');
		$book->setAcl($acl);
		self::assertTrue($book->save(), $book->getValidationErrorsAsString());
		return $book;
	}

	private static function createNote(NoteBook $book, string $name): Note
	{
		$note = new Note();
		$note->noteBookId = $book->id;
		$note->name = $name;
		$note->content = 'Content of ' . $name;
		self::assertTrue($note->save(), $note->getValidationErrorsAsString());
		return $note;
	}

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(self::$mcpUser->id));
	}

	protected function tearDown(): void
	{
		go()->setAuthState($this->authState);
	}

	/**
	 * Call a tool like an AI client
	 *
	 * @return array{isError: bool, structuredContent: array}
	 */
	private function callTool(string $name, array $arguments = [], ?McpKey $key = null): array
	{
		$response = (new Server($key ?? self::$key))->handle([
			'jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/call',
			'params' => ['name' => $name, 'arguments' => $arguments]
		]);
		$this->assertArrayHasKey('result', $response['body'], json_encode($response['body']));
		return $response['body']['result'];
	}

	private function read(array $calls, ?McpKey $key = null): array
	{
		return $this->callTool('jmap_read', ['calls' => $calls], $key);
	}

	public function testListEntitiesShowsOnlyThePickedOnes(): void
	{
		$result = $this->callTool('list_entities');

		$this->assertFalse($result['isError']);
		// Principal is readable with every key, to find people
		$entities = array_column($result['structuredContent']['entities'], 'mayWrite', 'name');
		$this->assertEquals(['Note' => false, 'NoteBook' => false, 'Principal' => false], $entities);
	}

	public function testDescribeEntity(): void
	{
		$result = $this->callTool('describe_entity', ['entity' => 'Note']);

		$this->assertFalse($result['isError']);
		$this->assertEqualsCanonicalizing(['get', 'query', 'changes'], array_keys($result['structuredContent']['methods']));
		$this->assertArrayHasKey('Note', $result['structuredContent']['$defs']);
		$this->assertArrayHasKey('Filter', $result['structuredContent']['$defs']);
	}

	public function testDescribeEntityThatIsNotPicked(): void
	{
		$result = $this->callTool('describe_entity', ['entity' => 'Contact']);

		$this->assertTrue($result['isError']);
	}

	public function testQueryAndGetReturnOnlyWhatTheUserMayRead(): void
	{
		$result = $this->read([
			['Note/query', ['filter' => ['text' => 'budget note']], 'q'],
			['Note/get', ['#ids' => ['resultOf' => 'q', 'name' => 'Note/query', 'path' => '/ids'], 'properties' => ['name']], 'g']
		]);

		$this->assertFalse($result['isError'], json_encode($result));
		[$query, $get] = $result['structuredContent']['responses'];
		$this->assertEquals('Note/query', $query[0]);
		$this->assertContains(self::$sharedNote->id, $query[1]['ids']);
		$this->assertNotContains(self::$privateNote->id, $query[1]['ids']);
		$this->assertEquals('Note/get', $get[0]);
		$this->assertContains('Shared budget note', array_column($get[1]['list'], 'name'));
	}

	public function testGetOfANoteTheUserMayNotReadIsNotFound(): void
	{
		$result = $this->read([['Note/get', ['ids' => [self::$privateNote->id]], 'g']]);

		$this->assertFalse($result['isError']);
		$get = $result['structuredContent']['responses'][0];
		$this->assertEquals([self::$privateNote->id], $get[1]['notFound']);
		$this->assertEmpty($get[1]['list']);
	}

	public function testEntityThatIsNotPickedIsRefused(): void
	{
		foreach (['Contact/get', 'Search/query', 'User/get', 'McpKey/query'] as $method) {
			$result = $this->read([[$method, ['ids' => ['1']], 'c']]);
			$this->assertTrue($result['isError'], $method . ' was allowed');
			$this->assertStringContainsString('not allowed for this key', $result['structuredContent']['error']);
		}
	}

	public function testOnlyReadMethodsAreAllowed(): void
	{
		$calls = [
			[['Note/set', ['destroy' => [self::$sharedNote->id]], 's'], 'only get, query, changes'],
			[['Note/export', ['ids' => [self::$sharedNote->id], 'extension' => 'csv'], 'e'], 'only get, query, changes'],
			[['community/notes/Note/get', ['ids' => [self::$sharedNote->id]], 'n'], 'must be like'],
			[['core/System/echo', [], 'x'], 'must be like'],
			[['Core/echo', [], 'y'], 'not allowed for this key']
		];
		foreach ($calls as [$call, $message]) {
			$result = $this->read([$call]);
			$this->assertTrue($result['isError'], $call[0] . ' was allowed');
			$this->assertStringContainsString($message, $result['structuredContent']['error'], $call[0]);
		}

		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		$this->assertNotNull(Note::findById(self::$sharedNote->id));
	}

	public function testPrincipalIsReadableWithoutPicking(): void
	{
		$result = $this->read([['Principal/query', ['limit' => 5], 'p']]);

		$this->assertFalse($result['isError'], json_encode($result));
		$this->assertEquals('Principal/query', $result['structuredContent']['responses'][0][0]);
	}

	public function testOnlyIdsMayReferToAnotherCall(): void
	{
		$reference = ['resultOf' => 'q', 'name' => 'Note/query', 'path' => '/total'];
		$calls = [
			['Note/query', ['#limit' => $reference], 'a'],
			['Note/query', ['filter' => ['#text' => $reference]], 'b'],
			['Note/get', ['ids' => [], 'x' => ['#ids' => $reference]], 'c']
		];
		foreach ($calls as $call) {
			$result = $this->read([['Note/query', ['calculateTotal' => true], 'q'], $call]);
			$this->assertTrue($result['isError'], json_encode($call));
			$this->assertStringContainsString('only "#ids"', $result['structuredContent']['error']);
		}
	}

	public function testReadWorksAfterThePageCreatedTheHttpResponse(): void
	{
		// api/page.php creates the HTTP response before it calls the module
		\go\core\http\Response::get();

		$result = $this->read([['Note/get', ['ids' => [self::$sharedNote->id]], 'g']]);

		$this->assertFalse($result['isError'], json_encode($result));
		$this->assertEquals(self::$sharedNote->id, $result['structuredContent']['responses'][0][1]['list'][0]['id']);
	}

	public function testGetNeedsIds(): void
	{
		$result = $this->read([['Note/get', [], 'g']]);

		$this->assertTrue($result['isError']);
	}

	public function testExcludedEntityIsRefusedEvenWhenStoredOnTheKey(): void
	{
		// The key validation never stores it, so it's inserted directly
		$key = self::createMcpKey(['Note' => false]);
		go()->getDbConnection()->insert('mcp_key_entity', [
			'keyId' => $key->id,
			'entityTypeId' => AppPassword::entityType()->getId(),
			'mayWrite' => 0
		])->execute();
		$key = McpKey::findById($key->id);

		$result = $this->read([['AppPassword/query', [], 'a']], $key);
		$this->assertTrue($result['isError']);
		$this->assertStringContainsString('not allowed for this key', $result['structuredContent']['error']);
		$this->assertTrue($this->callTool('describe_entity', ['entity' => 'AppPassword'], $key)['isError']);
		$this->assertNotContains('AppPassword', array_column($this->callTool('list_entities', [], $key)['structuredContent']['entities'], 'name'));
	}
}
