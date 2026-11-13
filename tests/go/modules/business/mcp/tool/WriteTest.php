<?php
namespace go\modules\business\mcp\tool;

use go\core\auth\TemporaryState;
use go\core\model\Acl;
use go\core\model\User;
use go\modules\business\mcp\McpFixtureTrait;
use go\modules\business\mcp\model\McpKey;
use go\modules\business\mcp\Server;
use go\modules\community\notes\model\Note;
use go\modules\community\notes\model\NoteBook;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../McpFixtureTrait.php';

/**
 * An AI assistant may only create and update entities it has write access to, within the user's permissions, and
 * never delete
 */
class WriteTest extends TestCase
{
	use McpFixtureTrait;

	private $authState;
	private static McpKey $writeKey;
	private static McpKey $readKey;
	private static NoteBook $writableBook;
	private static NoteBook $readOnlyBook;

	public static function setUpBeforeClass(): void
	{
		self::setUpMcp();
		$authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		try {
			self::$writeKey = self::createMcpKey(['Note' => true, 'NoteBook' => false]);
			self::$readKey = self::createMcpKey(['Note' => false]);

			$group = self::$mcpUser->getPersonalGroup()->id;
			self::$writableBook = self::createBook([$group => Acl::LEVEL_DELETE]);
			self::$readOnlyBook = self::createBook([$group => Acl::LEVEL_READ]);
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
		foreach (['writableBook', 'readOnlyBook'] as $prop) {
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
		$book->name = uniqid('mcpwrite');
		$book->setAcl($acl);
		self::assertTrue($book->save(), $book->getValidationErrorsAsString());
		return $book;
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

	private function callTool(string $name, array $arguments, ?McpKey $key = null): array
	{
		return (new Server($key ?? self::$writeKey))->handle([
			'jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/call',
			'params' => ['name' => $name, 'arguments' => $arguments]
		])['body'];
	}

	private function write(array $calls, ?McpKey $key = null): array
	{
		return $this->callTool('jmap_write', ['calls' => $calls], $key)['result'];
	}

	private function createNote(NoteBook $book, string $name): array
	{
		return $this->write([['Note/set', ['create' => ['n' => ['noteBookId' => $book->id, 'name' => $name, 'content' => 'Content']]], 's']]);
	}

	public function testWriteToolOnlyForKeysWithWriteAccess(): void
	{
		$tools = fn(McpKey $key) => array_column((new Server($key))->handle(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/list'])['body']['result']['tools'], 'name');

		$this->assertContains('jmap_write', $tools(self::$writeKey));
		$this->assertNotContains('jmap_write', $tools(self::$readKey));

		$jmapWrite = array_values(array_filter((new Server(self::$writeKey))->handle(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/list'])['body']['result']['tools'], fn($t) => $t['name'] === 'jmap_write'))[0];
		$this->assertTrue($jmapWrite['annotations']['destructiveHint'], 'Clients must ask the user before a write');
	}

	public function testCreateAndUpdate(): void
	{
		$result = $this->createNote(self::$writableBook, 'Functional design');
		$this->assertFalse($result['isError'], json_encode($result));
		$set = $result['structuredContent']['responses'][0][1];
		$id = $set['created']['n']['id'] ?? null;
		$this->assertNotNull($id, json_encode($set));

		// The history stores this request ID with the change
		$this->assertSame('MCP: ' . self::$writeKey->name . ' JMAP Note/set', go()->getDebugger()->getRequestId());

		$result = $this->write([['Note/set', ['update' => [$id => ['name' => 'Functional design v2']]], 'u']]);
		$this->assertArrayHasKey($id, $result['structuredContent']['responses'][0][1]['updated'], json_encode($result));
		$this->assertSame('Functional design v2', Note::findById($id)->name);
	}

	public function testUserPermissionsStillApply(): void
	{
		$result = $this->createNote(self::$readOnlyBook, 'Not allowed');

		$this->assertFalse($result['isError']);
		$this->assertArrayHasKey('n', $result['structuredContent']['responses'][0][1]['notCreated']);
	}

	public function testEntityWithoutWriteAccessIsRefused(): void
	{
		$result = $this->write([['NoteBook/set', ['create' => ['b' => ['name' => 'New book']]], 's']]);

		$this->assertTrue($result['isError']);
		$this->assertStringContainsString('only set and custom methods that change data', $result['structuredContent']['error']);
	}

	public function testReadKeyCanNotWrite(): void
	{
		$body = $this->callTool('jmap_write', ['calls' => [['Note/set', ['create' => ['n' => ['noteBookId' => self::$writableBook->id, 'name' => 'x', 'content' => 'x']]], 's']]], self::$readKey);

		$this->assertSame(Server::INVALID_PARAMS, $body['error']['code'], 'jmap_write must not exist for a read key');
	}

	public function testDeletingIsRefused(): void
	{
		$id = $this->createNote(self::$writableBook, 'Keep me')['structuredContent']['responses'][0][1]['created']['n']['id'];

		$result = $this->write([['Note/set', ['destroy' => [$id]], 'd']]);

		$this->assertTrue($result['isError']);
		$this->assertStringContainsString('deleting', $result['structuredContent']['error']);
		$this->assertNotNull(Note::findById($id));
	}

	public function testTooManyChangesAtOnce(): void
	{
		$create = [];
		for ($i = 0; $i <= JmapWrite::MAX_CHANGES; $i++) {
			$create['n' . $i] = ['noteBookId' => self::$writableBook->id, 'name' => 'Note ' . $i, 'content' => 'x'];
		}

		$result = $this->write([['Note/set', ['create' => $create], 's']]);

		$this->assertTrue($result['isError']);
	}

	public function testCustomMethodsWithoutReadOnlyTagAreWrites(): void
	{
		$custom = JmapTool::customMethods(Note::entityType());

		foreach ($custom as $name => $readOnly) {
			$read = $this->callTool('jmap_read', ['calls' => [['Note/' . $name, [], 'c']]])['result'];
			$this->assertSame(!$readOnly, $read['isError'], 'Note/' . $name . ' with jmap_read');
		}
		$this->assertNotEmpty($custom, 'Note has custom methods to test with');
	}
}
