<?php
namespace go\modules\business\mcp\tool;

use go\core\auth\TemporaryState;
use go\core\fs\Blob;
use go\core\model\User;
use go\modules\business\mcp\McpFixtureTrait;
use go\modules\business\mcp\model\McpKey;
use go\modules\business\mcp\Server;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../McpFixtureTrait.php';

/**
 * get_blob returns text in parts, decompresses gzip, returns images as image content and refuses other binary files
 */
class GetBlobTest extends TestCase
{
	use McpFixtureTrait;

	private static McpKey $key;

	/** @var string[] IDs of the blobs created by the tests */
	private array $blobIds = [];

	public static function setUpBeforeClass(): void
	{
		self::setUpMcp();
		try {
			// A read-only key also gets get_blob
			self::$key = self::createMcpKey(['Note' => false]);
		} catch (\Throwable $e) {
			self::tearDownMcp();
			throw $e;
		}
	}

	public static function tearDownAfterClass(): void
	{
		self::tearDownMcp();
	}

	protected function tearDown(): void
	{
		foreach ($this->blobIds as $id) {
			$blob = Blob::findById($id);
			if ($blob && !$blob->isUsed()) {
				Blob::delete(['id' => $id]);
			}
		}
		$this->blobIds = [];
	}

	private function createBlob(string $data, string $name, string $type = 'application/octet-stream'): string
	{
		$blob = Blob::fromString($data);
		if ($blob->isNew()) {
			$blob->name = $name;
			$blob->type = $type;
			$this->assertTrue($blob->save());
			$this->blobIds[] = $blob->id;
		}
		return $blob->id;
	}

	private function callTool(array $arguments): array
	{
		$authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(self::$mcpUser->id));
		try {
			return (new Server(self::$key))->handle([
				'jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/call',
				'params' => ['name' => 'get_blob', 'arguments' => $arguments]
			])['body']['result'];
		} finally {
			go()->setAuthState($authState);
		}
	}

	private static function log(int $lines): string
	{
		$log = '';
		for ($i = 1; $i <= $lines; $i++) {
			$log .= "[" . $i . "] " . ($i % 100 == 0 ? "PHP Fatal error: something " . $i : "notice " . $i) . "\n";
		}
		return $log;
	}

	public function testReadsTextInParts(): void
	{
		$log = self::log(1000) . uniqid();
		$id = $this->createBlob($log, 'error.log');

		$result = $this->callTool(['blobId' => $id, 'length' => 100]);
		$this->assertFalse($result['isError'], json_encode($result));
		$this->assertEquals(substr($log, 0, 100), $result['content'][1]['text']);
		$this->assertEquals(strlen($log), $result['structuredContent']['total']);
		$this->assertTrue($result['structuredContent']['hasMore']);
		$this->assertArrayNotHasKey('text', $result['structuredContent']);

		$result = $this->callTool(['blobId' => $id, 'offset' => 100, 'length' => 50]);
		$this->assertEquals(substr($log, 100, 50), $result['content'][1]['text']);

		$tail = $this->callTool(['blobId' => $id, 'offset' => -30]);
		$this->assertEquals(substr($log, -30), $tail['content'][1]['text']);
		$this->assertFalse($tail['structuredContent']['hasMore']);
	}

	public function testSearch(): void
	{
		$id = $this->createBlob(self::log(1000) . uniqid(), 'error.log');

		$result = $this->callTool(['blobId' => $id, 'search' => 'fatal ERROR']);
		$this->assertFalse($result['isError'], json_encode($result));
		$this->assertEquals(10, $result['structuredContent']['matchCount']);
		$this->assertStringStartsWith("100: [100] PHP Fatal error: something 100\n200: ", $result['content'][1]['text']);
	}

	public function testDecompressesGzip(): void
	{
		$log = self::log(1000) . uniqid();
		$id = $this->createBlob(gzencode($log), 'error.log.2.gz', 'application/gzip');

		$result = $this->callTool(['blobId' => $id, 'offset' => -50]);
		$this->assertFalse($result['isError'], json_encode($result));
		$this->assertTrue($result['structuredContent']['decompressed']);
		$this->assertEquals(strlen($log), $result['structuredContent']['total']);
		$this->assertEquals(substr($log, -50), $result['content'][1]['text']);

		$search = $this->callTool(['blobId' => $id, 'search' => 'something 500']);
		$this->assertStringStartsWith('500: ', $search['content'][1]['text']);
	}

	public function testGzipIsReadUpToTheLimit(): void
	{
		// Compresses to a few kB
		$id = $this->createBlob(gzencode(str_repeat('a', GetBlob::MAX_UNCOMPRESSED + 1000) . uniqid()), 'bomb.gz');

		$result = $this->callTool(['blobId' => $id, 'length' => 10]);
		$this->assertFalse($result['isError'], json_encode($result));
		$this->assertEquals(GetBlob::MAX_UNCOMPRESSED, $result['structuredContent']['total']);
		$this->assertArrayHasKey('truncated', $result['structuredContent']);
	}

	public function testImage(): void
	{
		$image = imagecreatetruecolor(2, 2);
		ob_start();
		imagepng($image);
		$png = ob_get_clean();
		$png .= uniqid(); // unique content, so the blob is new
		// The stored type is wrong, the content decides
		$id = $this->createBlob($png, 'screenshot', 'application/octet-stream');

		$result = $this->callTool(['blobId' => $id]);
		$this->assertFalse($result['isError'], json_encode($result));
		$this->assertEquals('image', $result['content'][1]['type']);
		$this->assertEquals('image/png', $result['content'][1]['mimeType']);
		$this->assertEquals($png, base64_decode($result['content'][1]['data']));
		$this->assertArrayNotHasKey('image', $result['structuredContent']);
	}

	public function testTextStoredAsImageIsText(): void
	{
		$id = $this->createBlob('not an image ' . uniqid(), 'fake.png', 'image/png');
		$result = $this->callTool(['blobId' => $id]);
		$this->assertFalse($result['isError'], json_encode($result));
		$this->assertEquals('text', $result['content'][1]['type']);
		$this->assertStringStartsWith('not an image ', $result['content'][1]['text']);
	}

	public function testBinaryIsRefused(): void
	{
		$id = $this->createBlob("%PDF-1.4\0\1\2" . uniqid(), 'file.pdf', 'application/pdf');
		$result = $this->callTool(['blobId' => $id]);
		$this->assertTrue($result['isError']);
		$this->assertStringContainsString('binary', $result['structuredContent']['error']);
	}

	public function testInvalidUtf8IsReplaced(): void
	{
		$id = $this->createBlob("caf\xc3\xa9 \xff\xfe broken " . uniqid(), 'latin.log');
		$result = $this->callTool(['blobId' => $id]);
		$this->assertFalse($result['isError'], json_encode($result));
		$this->assertStringStartsWith('café ', $result['content'][1]['text']);
		$this->assertTrue(mb_check_encoding($result['content'][1]['text'], 'UTF-8'));
		$this->assertNotFalse(json_encode($result));
	}

	public function testNotFound(): void
	{
		foreach ([str_repeat('0', 40), '../../etc/passwd', null] as $blobId) {
			$result = $this->callTool(['blobId' => $blobId]);
			$this->assertTrue($result['isError']);
			$this->assertEquals('File not found', $result['structuredContent']['error']);
		}
	}

	public function testInvalidLength(): void
	{
		$id = $this->createBlob('text ' . uniqid(), 'a.txt');
		$this->assertTrue($this->callTool(['blobId' => $id, 'length' => GetBlob::MAX_LENGTH + 1])['isError']);
		$this->assertTrue($this->callTool(['blobId' => $id, 'length' => 0])['isError']);
	}
}
