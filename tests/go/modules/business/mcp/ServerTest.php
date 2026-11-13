<?php
namespace go\modules\business\mcp;

use go\core\auth\TemporaryState;
use go\modules\business\mcp\model\McpKey;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/McpFixtureTrait.php';

/**
 * The JSON-RPC protocol of the MCP server, for clients of protocol version 2026-07-28 and of the older versions that
 * start with "initialize"
 */
class ServerTest extends TestCase
{
	use McpFixtureTrait;

	private $authState;
	private static McpKey $key;

	public static function setUpBeforeClass(): void
	{
		self::setUpMcp();
		try {
			self::$key = self::createMcpKey(['Note' => false, 'NoteBook' => false]);
		} catch (\Throwable $e) {
			self::tearDownMcp();
			throw $e;
		}
	}

	public static function tearDownAfterClass(): void
	{
		self::tearDownMcp();
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

	private function legacy(string $method, array $params = [], mixed $id = 1): array
	{
		return (new Server(self::$key))->handle(['jsonrpc' => '2.0', 'id' => $id, 'method' => $method, 'params' => $params], ['mcp-protocol-version' => '2025-11-25']);
	}

	/**
	 * A request of protocol version 2026-07-28 with the headers it requires
	 */
	private function modern(string $method, array $params = [], array $headers = []): array
	{
		$params['_meta'][Server::META_VERSION] = Server::VERSION;
		$headers += ['mcp-protocol-version' => Server::VERSION, 'mcp-method' => $method];
		if ($method == 'tools/call') {
			$headers += ['mcp-name' => $params['name']];
		}
		return (new Server(self::$key))->handle(['jsonrpc' => '2.0', 'id' => 'a', 'method' => $method, 'params' => $params], $headers);
	}

	public function testInitializeNegotiatesTheVersion(): void
	{
		$result = $this->legacy('initialize', ['protocolVersion' => '2025-06-18'])['body']['result'];
		$this->assertEquals('2025-06-18', $result['protocolVersion']);
		$this->assertEquals('groupoffice', $result['serverInfo']['name']);
		$this->assertStringContainsString(self::$mcpUser->displayName, $result['instructions']);

		// An unknown version gets the latest one that starts with initialize
		$result = $this->legacy('initialize', ['protocolVersion' => '2024-11-05'])['body']['result'];
		$this->assertEquals(Server::LEGACY_VERSIONS[0], $result['protocolVersion']);
	}

	public function testNotificationsAndResponsesGetNoBody(): void
	{
		$server = new Server(self::$key);

		$this->assertEquals(['status' => 202, 'body' => null], $server->handle(['jsonrpc' => '2.0', 'method' => 'notifications/initialized']));
		$this->assertEquals(['status' => 202, 'body' => null], $server->handle(['jsonrpc' => '2.0', 'id' => 5, 'result' => new \stdClass()]));
	}

	public function testBatchesAndInvalidMessagesAreRefused(): void
	{
		$server = new Server(self::$key);

		$batch = $server->handle([['jsonrpc' => '2.0', 'id' => 1, 'method' => 'ping']]);
		$this->assertEquals(400, $batch['status']);
		$this->assertEquals(Server::INVALID_REQUEST, $batch['body']['error']['code']);

		$this->assertEquals(400, $server->handle(['id' => 1, 'method' => 'ping'])['status']);
		$this->assertEquals(400, $server->handle('ping')['status']);
	}

	public function testUnknownMethod(): void
	{
		$this->assertEquals(Server::METHOD_NOT_FOUND, $this->legacy('resources/list')['body']['error']['code']);

		$modern = $this->modern('resources/list');
		$this->assertEquals(404, $modern['status']);
		$this->assertEquals(Server::METHOD_NOT_FOUND, $modern['body']['error']['code']);

		// A 2026-07-28 client discovers instead of initializing
		$this->assertEquals(Server::METHOD_NOT_FOUND, $this->modern('initialize')['body']['error']['code']);
	}

	public function testUnsupportedLegacyVersionHeader(): void
	{
		$response = (new Server(self::$key))->handle(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/list'], ['mcp-protocol-version' => '1999-01-01']);

		$this->assertEquals(400, $response['status']);
		$this->assertEquals(Server::UNSUPPORTED_PROTOCOL_VERSION, $response['body']['error']['code']);
	}

	public function testDiscover(): void
	{
		$response = $this->modern('server/discover');

		$this->assertEquals(200, $response['status']);
		$result = $response['body']['result'];
		$this->assertEquals('complete', $result['resultType']);
		$this->assertEquals([Server::VERSION], $result['supportedVersions']);
		$this->assertEquals('private', $result['cacheScope']);
		$this->assertEquals('groupoffice', $result['_meta']['io.modelcontextprotocol/serverInfo']['name']);
	}

	public function testModernRequestNeedsMatchingHeaders(): void
	{
		$missing = (new Server(self::$key))->handle([
			'jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/list',
			'params' => ['_meta' => [Server::META_VERSION => Server::VERSION]]
		]);
		$this->assertEquals(400, $missing['status']);
		$this->assertEquals(Server::HEADER_MISMATCH, $missing['body']['error']['code']);

		$mismatch = $this->modern('tools/list', [], ['mcp-method' => 'tools/call']);
		$this->assertEquals(Server::HEADER_MISMATCH, $mismatch['body']['error']['code']);

		$name = $this->modern('tools/call', ['name' => 'whoami'], ['mcp-name' => 'other']);
		$this->assertEquals(Server::HEADER_MISMATCH, $name['body']['error']['code']);
	}

	public function testUnsupportedModernVersion(): void
	{
		$response = (new Server(self::$key))->handle([
			'jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/list',
			'params' => ['_meta' => [Server::META_VERSION => '2099-01-01']]
		], ['mcp-protocol-version' => '2099-01-01', 'mcp-method' => 'tools/list']);

		$this->assertEquals(400, $response['status']);
		$this->assertEquals(Server::UNSUPPORTED_PROTOCOL_VERSION, $response['body']['error']['code']);
		$this->assertEquals([Server::VERSION], $response['body']['error']['data']['supported']);
	}

	public function testListTools(): void
	{
		$legacy = $this->legacy('tools/list')['body']['result'];
		$names = array_column($legacy['tools'], 'name');
		$this->assertContains('whoami', $names);
		foreach ($legacy['tools'] as $tool) {
			$this->assertMatchesRegularExpression('/^[a-zA-Z0-9_-]{1,64}$/', $tool['name']);
			$this->assertEquals('object', $tool['inputSchema']['type']);
		}

		$modern = $this->modern('tools/list')['body']['result'];
		$this->assertEquals('complete', $modern['resultType']);
		$this->assertEquals('private', $modern['cacheScope']);
		$this->assertGreaterThan(0, $modern['ttlMs']);
	}

	public function testWhoAmI(): void
	{
		$result = $this->modern('tools/call', ['name' => 'whoami', 'arguments' => []])['body']['result'];

		$this->assertFalse($result['isError']);
		$this->assertEquals(self::$mcpUser->id, $result['structuredContent']['user']['id']);
		$this->assertEqualsCanonicalizing(['Note', 'NoteBook', 'Principal'], array_column($result['structuredContent']['entities'], 'name'));
		$this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $result['structuredContent']['today']);
		$this->assertEquals($result['structuredContent'], json_decode($result['content'][0]['text'], true));
	}

	public function testUnknownToolIsInvalidParams(): void
	{
		$response = $this->modern('tools/call', ['name' => 'doesnotexist']);

		$this->assertEquals(400, $response['status']);
		$this->assertEquals(Server::INVALID_PARAMS, $response['body']['error']['code']);
	}
}
