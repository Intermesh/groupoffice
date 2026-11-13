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
		$this->assertStringContainsString('only set, on entities with write access', $result['structuredContent']['error']);
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

	/**
	 * Custom methods without the read-only tag can't be called at all: they aren't limited to their own entity, like
	 * CalendarEvent/loadICS reading mail, and may delete or change many items
	 */
	public function testCustomMethodsWithoutReadOnlyTagAreNotAvailable(): void
	{
		$key = self::createMcpKey(['CalendarEvent' => true]);

		$custom = JmapTool::customMethods(\go\modules\community\calendar\model\CalendarEvent::entityType());
		$this->assertArrayHasKey('loadICS', $custom);
		$this->assertFalse($custom['loadICS'], 'loadICS reads mail and must not be tagged read-only');

		$params = ['account_id' => 1, 'mailbox' => 'INBOX', 'uid' => 1, 'number' => '2', 'encoding' => 'base64'];
		$write = $this->write([['CalendarEvent/loadICS', $params, 'l']], $key);
		$this->assertTrue($write['isError']);
		$this->assertStringContainsString('only set', $write['structuredContent']['error']);

		$read = $this->callTool('jmap_read', ['calls' => [['CalendarEvent/loadICS', $params, 'l']]], $key)['result'];
		$this->assertTrue($read['isError']);
	}

	public function testLongKeyNameIsShortenedInTheRequestId(): void
	{
		$key = self::createMcpKey(['Note' => true], ['name' => str_repeat('x', 180)]);

		$this->write([['Note/set', ['create' => ['n' => ['noteBookId' => self::$writableBook->id, 'name' => 'Long', 'content' => 'x']]], 's']], $key);

		$this->assertSame('MCP: ' . str_repeat('x', 50) . ' JMAP Note/set', go()->getDebugger()->getRequestId());
	}

	/**
	 * The example "schedule an appointment for me and a colleague", following the recipe
	 */
	public function testCreateAppointmentWithParticipantAsTheRecipeSays(): void
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		$calendar = new \go\modules\community\calendar\model\Calendar();
		$calendar->name = uniqid('mcpcal');
		$calendar->setOwnerId(self::$mcpUser->id);
		$calendar->setAcl([self::$mcpUser->getPersonalGroup()->id => Acl::LEVEL_MANAGE]);
		$this->assertTrue($calendar->save(), $calendar->getValidationErrorsAsString());
		go()->setAuthState(new TemporaryState(self::$mcpUser->id));

		try {
			$key = self::createMcpKey(['CalendarEvent' => true, 'Calendar' => false]);
			$result = $this->write([['CalendarEvent/set', ['create' => ['e' => [
				'calendarId' => $calendar->id,
				'title' => 'Project X kick-off',
				'start' => '2030-03-05T13:00:00',
				'timeZone' => 'Europe/Amsterdam',
				'duration' => 'PT1H',
				'participants' => [
					'me' => ['email' => self::$mcpUser->email, 'name' => self::$mcpUser->displayName, 'roles' => ['owner' => true, 'attendee' => true]],
					'colleague' => ['email' => 'colleague@example.com', 'name' => 'Colleague', 'roles' => ['attendee' => true]]
				]
			]]], 'c']], $key);

			$this->assertFalse($result['isError'], json_encode($result));
			$set = $result['structuredContent']['responses'][0][1];
			$this->assertArrayHasKey('e', $set['created'], json_encode($set));
		} finally {
			go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
			\go\modules\community\calendar\model\CalendarEvent::delete(['calendarId' => $calendar->id]);
			\go\modules\community\calendar\model\Calendar::delete(['id' => $calendar->id]);
			go()->setAuthState(new TemporaryState(self::$mcpUser->id));
		}
	}

	public function testRecipeIsDescribed(): void
	{
		$result = $this->callTool('describe_entity', ['entity' => 'Note'])['result'];

		$this->assertStringContainsString('noteBookId', $result['structuredContent']['recipe']);
		$this->assertContains('set', array_keys($result['structuredContent']['methods']));
	}

	/**
	 * Like storing a functional design at a project: upload the document, then attach it to the item
	 */
	public function testUploadAndAttachAFile(): void
	{
		$id = $this->createNote(self::$writableBook, 'Project X')['structuredContent']['responses'][0][1]['created']['n']['id'];

		$blob = $this->callTool('create_blob', ['name' => 'functional-design.md', 'type' => 'text/markdown', 'content' => '# Functional design'])['result'];
		$this->assertFalse($blob['isError'], json_encode($blob));
		$blobId = $blob['structuredContent']['blobId'];

		$result = $this->write([['Note/set', ['update' => [$id => ['attachments' => [['blobId' => $blobId]]]]], 'a']]);
		$this->assertArrayHasKey($id, $result['structuredContent']['responses'][0][1]['updated'], json_encode($result));

		$folderId = Note::findById($id)->filesFolderId;
		$this->assertNotEmpty($folderId);
		$old = \GO::setIgnoreAclPermissions(true);
		try {
			$folder = \GO\Files\Model\Folder::model()->findByPk($folderId);
			$this->assertNotEmpty($folder->hasFile('functional-design.md'));
			$folder->delete(true);
		} finally {
			\GO::setIgnoreAclPermissions($old);
		}
	}

	public function testInvalidUpload(): void
	{
		$this->assertTrue($this->callTool('create_blob', ['name' => 'x.bin', 'contentBase64' => '***'])['result']['isError']);
		$this->assertTrue($this->callTool('create_blob', ['name' => '../x.md', 'content' => 'x'])['result']['isError']);
		$this->assertTrue($this->callTool('create_blob', ['name' => 'x.md'])['result']['isError']);
	}
}
