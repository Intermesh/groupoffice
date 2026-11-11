<?php
namespace go\core\fs;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
	private const OUTPUT_HEADERS = ['Content-Type', 'Content-Length', 'Content-Disposition', 'Content-Transfer-Encoding',
		'X-Content-Type-Options', 'Content-Security-Policy', 'Cache-Control', 'Expires', 'Pragma', 'Last-Modified'];

	/**
	 * Set the download headers of a temporary file with this content and return them
	 */
	private function outputHeaders(string $name, string $data, array $headers = [], bool $inline = true): array
	{
		$tmp = go()->getTmpFolder()->create();
		$file = $tmp->getFile(uniqid() . '-' . $name);
		$file->putContents($data);
		$r = \go\core\http\Response::get();
		try {
			$file->setOutputHeaders(false, $headers, $inline);
			$result = [];
			foreach (self::OUTPUT_HEADERS as $header) {
				$result[$header] = $r->getHeader($header)[0] ?? null;
			}
			return $result;
		} finally {
			foreach (self::OUTPUT_HEADERS as $header) {
				$r->removeHeader($header);
			}
			$file->delete();
		}
	}

	public static function scriptFileProvider(): array
	{
		$xhtml = '<?xml version="1.0"?><html xmlns="http://www.w3.org/1999/xhtml"><script>alert(1)</script></html>';
		return [
			'xhtml without extension' => ['file', $xhtml],
			'xhtml with xml extension' => ['file.xml', $xhtml],
			'xhtml with txt extension' => ['file.txt', $xhtml],
			'html' => ['file.html', '<html><body><script>alert(1)</script></body></html>'],
			'svg' => ['file.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'],
		];
	}

	/**
	 * Files that can run scripts are downloaded, also when the content type is detected from the contents
	 */
	#[DataProvider('scriptFileProvider')]
	public function testScriptFileIsNotShownInline(string $name, string $data)
	{
		$headers = $this->outputHeaders($name, $data);

		$this->assertFalse(\go\core\http\Response::isSafeInlineContentType($headers['Content-Type']), $headers['Content-Type']);
		$this->assertStringStartsWith('attachment;', $headers['Content-Disposition']);
		$this->assertSame('nosniff', $headers['X-Content-Type-Options']);
	}

	public function testInlineDispositionHeaderOfHtmlIsRefused()
	{
		$headers = $this->outputHeaders('file.html', '<p>x</p>', [
			'Content-Type' => 'text/html; charset=utf-8',
			'Content-Disposition' => 'inline; filename="file.html"'
		]);

		$this->assertSame('attachment; filename="file.html"', $headers['Content-Disposition']);
		$this->assertSame('nosniff', $headers['X-Content-Type-Options']);
	}

	public function testPlainTextIsShownInlineInSandbox()
	{
		$headers = $this->outputHeaders('file.txt', 'Hello');

		$this->assertSame('text/plain', $headers['Content-Type']);
		$this->assertStringStartsWith('inline;', $headers['Content-Disposition']);
		$this->assertSame('nosniff', $headers['X-Content-Type-Options']);
		$this->assertSame('sandbox', $headers['Content-Security-Policy']);
	}
}
