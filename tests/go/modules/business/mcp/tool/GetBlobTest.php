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
 * get_blob returns text in parts, decompresses gzip, extracts the text of documents, returns images as image content
 * and refuses other binary files
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
		$this->assertEquals(substr($log, 0, 100), $result['structuredContent']['text']);
		$this->assertEquals(strlen($log), $result['structuredContent']['total']);
		$this->assertTrue($result['structuredContent']['hasMore']);
		$this->assertEquals($result['structuredContent'], json_decode($result['content'][0]['text'], true));

		$result = $this->callTool(['blobId' => $id, 'offset' => 100, 'length' => 50]);
		$this->assertEquals(substr($log, 100, 50), $result['structuredContent']['text']);

		$tail = $this->callTool(['blobId' => $id, 'offset' => -30]);
		$this->assertEquals(substr($log, -30), $tail['structuredContent']['text']);
		$this->assertFalse($tail['structuredContent']['hasMore']);
	}

	public function testSearch(): void
	{
		$id = $this->createBlob(self::log(1000) . uniqid(), 'error.log');

		$result = $this->callTool(['blobId' => $id, 'search' => 'fatal ERROR']);
		$this->assertFalse($result['isError'], json_encode($result));
		$this->assertEquals(10, $result['structuredContent']['matchCount']);
		$this->assertEquals(['line' => 100, 'text' => '[100] PHP Fatal error: something 100'], $result['structuredContent']['matches'][0]);
		$this->assertEquals(200, $result['structuredContent']['matches'][1]['line']);
	}

	public function testDecompressesGzip(): void
	{
		$log = self::log(1000) . uniqid();
		$id = $this->createBlob(gzencode($log), 'error.log.2.gz', 'application/gzip');

		$result = $this->callTool(['blobId' => $id, 'offset' => -50]);
		$this->assertFalse($result['isError'], json_encode($result));
		$this->assertTrue($result['structuredContent']['decompressed']);
		$this->assertEquals(strlen($log), $result['structuredContent']['total']);
		$this->assertEquals(substr($log, -50), $result['structuredContent']['text']);

		$search = $this->callTool(['blobId' => $id, 'search' => 'something 500']);
		$this->assertEquals(500, $search['structuredContent']['matches'][0]['line']);
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
		$this->assertStringStartsWith('not an image ', $result['structuredContent']['text']);
	}

	/**
	 * @param array<string, string> $entries Name => content
	 */
	private static function zip(array $entries): string
	{
		$path = \go\core\fs\File::tempFile('zip')->getPath();
		try {
			$zip = new \ZipArchive();
			$zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
			foreach ($entries as $name => $content) {
				$zip->addFromString($name, $content);
			}
			$zip->close();
			return file_get_contents($path);
		} finally {
			unlink($path);
		}
	}

	private static function docx(string $text): string
	{
		return self::zip([
			'[Content_Types].xml' => '<Types/>',
			// The ID makes the content and so the blob unique
			'word/document.xml' => '<w:document xmlns:w="w"><w:body><w:p><w:r><w:t>' . $text . '</w:t></w:r></w:p>'
				. '<w:p><w:r><w:t>' . uniqid() . '</w:t></w:r></w:p></w:body></w:document>'
		]);
	}

	public function testDocumentText(): void
	{
		$lines = [];
		for ($i = 1; $i <= 300; $i++) {
			$lines[] = 'Paragraph ' . $i;
		}
		$id = $this->createBlob(self::docx(implode('</w:t></w:r></w:p><w:p><w:r><w:t>', $lines)), 'letter.docx');

		$result = $this->callTool(['blobId' => $id, 'length' => 30]);
		$this->assertFalse($result['isError'], json_encode($result));
		$this->assertEquals('docx', $result['structuredContent']['extracted']);
		$this->assertEquals("Paragraph 1\nParagraph 2\nParagr", $result['structuredContent']['text']);
		$this->assertTrue($result['structuredContent']['hasMore']);

		$search = $this->callTool(['blobId' => $id, 'search' => 'paragraph 250']);
		$this->assertEquals(['line' => 250, 'text' => 'Paragraph 250'], $search['structuredContent']['matches'][0]);
	}

	public function testPdf(): void
	{
		$pdf = new \go\core\util\PdfRenderer();
		$pdf->AddPage();
		$pdf->Write(10, 'Invoice ' . uniqid());
		$id = $this->createBlob($pdf->Output('', 'S'), 'invoice.pdf', 'application/pdf');

		$result = $this->callTool(['blobId' => $id]);
		if (trim((string) shell_exec('command -v pdftotext')) === '') {
			$this->assertTrue($result['isError']);
			$this->assertStringContainsString('poppler-utils', $result['structuredContent']['error']);
		} else {
			$this->assertFalse($result['isError'], json_encode($result));
			$this->assertEquals('pdf', $result['structuredContent']['extracted']);
			// TCPDF adds a "Powered by TCPDF" line to a page
			$this->assertMatchesRegularExpression('#^--- Page 1 ---\n.*Invoice #s', $result['structuredContent']['text']);
		}
	}

	public function testEntry(): void
	{
		// Word writes the XML on one line. The template has curly quotes, so the condition never matches.
		$padding = str_repeat('<w:p><w:r><w:t>Lorem ipsum dolor sit amet</w:t></w:r></w:p>', 50);
		$id = $this->createBlob(self::docx($padding . '&lt;gotpl if=„contact:mentor“&gt;Mentor&lt;/gotpl&gt;' . $padding), 'template.docx');

		$result = $this->callTool(['blobId' => $id, 'entry' => 'word/document.xml', 'length' => 20]);
		$this->assertFalse($result['isError'], json_encode($result));
		$this->assertEquals('word/document.xml', $result['structuredContent']['entry']);
		$this->assertEquals('<w:document xmlns:w=', $result['structuredContent']['text']);

		$search = $this->callTool(['blobId' => $id, 'entry' => 'word/document.xml', 'search' => 'GOTPL']);
		$this->assertFalse($search['isError'], json_encode($search));
		$this->assertEquals(2, $search['structuredContent']['matchCount']);
		$match = $search['structuredContent']['matches'][0];
		$this->assertEquals(1, $match['line']);
		$this->assertStringContainsString('sit amet</w:t></w:r></w:p>&lt;gotpl if=„contact:mentor“&gt;Mentor&lt;/gotpl&gt;<w:p><w:r><w:t>Lorem', $match['text']);
		$this->assertLessThan(GetBlob::MAX_LINE_LENGTH, strlen($match['text']));

		// The offset of a match reads on from there
		$part = $this->callTool(['blobId' => $id, 'entry' => 'word/document.xml', 'offset' => $match['offset'], 'length' => 5]);
		$this->assertEquals('gotpl', $part['structuredContent']['text']);
	}

	public function testEntryNotFound(): void
	{
		$id = $this->createBlob(self::docx('text'), 'letter.docx');
		$result = $this->callTool(['blobId' => $id, 'entry' => 'content.xml']);
		$this->assertTrue($result['isError']);
		$this->assertStringContainsString('[Content_Types].xml, word/document.xml', $result['structuredContent']['error']);
	}

	public function testEntryOfOtherFile(): void
	{
		$id = $this->createBlob('plain text ' . uniqid(), 'a.txt');
		$result = $this->callTool(['blobId' => $id, 'entry' => 'content.xml']);
		$this->assertTrue($result['isError']);
		$this->assertStringContainsString('not a zip file', $result['structuredContent']['error']);
	}

	public function testBinaryEntryIsRefused(): void
	{
		$id = $this->createBlob(self::zip(['image.bin' => "\0\1\2" . uniqid()]), 'archive.zip');

		$result = $this->callTool(['blobId' => $id, 'entry' => 'image.bin']);
		$this->assertTrue($result['isError']);
		$this->assertStringContainsString('binary', $result['structuredContent']['error']);

		// Without entry the error says which files are in the zip
		$result = $this->callTool(['blobId' => $id]);
		$this->assertTrue($result['isError']);
		$this->assertStringContainsString('use entry to read a file in it. The files in it are: image.bin', $result['structuredContent']['error']);
	}

	public function testBinaryIsRefused(): void
	{
		$id = $this->createBlob("\0\1\2\3binary" . uniqid(), 'file.bin');
		$result = $this->callTool(['blobId' => $id]);
		$this->assertTrue($result['isError']);
		$this->assertStringContainsString('binary', $result['structuredContent']['error']);
	}

	public function testInvalidUtf8IsReplaced(): void
	{
		$id = $this->createBlob("caf\xc3\xa9 \xff\xfe broken " . uniqid(), 'latin.log');
		$result = $this->callTool(['blobId' => $id]);
		$this->assertFalse($result['isError'], json_encode($result));
		$this->assertStringStartsWith('café ', $result['structuredContent']['text']);
		$this->assertTrue(mb_check_encoding($result['structuredContent']['text'], 'UTF-8'));
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
