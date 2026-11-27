<?php
namespace go\core\http;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
	public function testContentDispositionPlainName()
	{
		$this->assertSame('attachment; filename="report.pdf"', Response::contentDisposition('attachment', 'report.pdf'));
		$this->assertSame('inline; filename="report.pdf"', Response::contentDisposition('inline', 'report.pdf'));
	}

	public function testContentDispositionUnknownTypeFallsBackToAttachment()
	{
		$this->assertStringStartsWith('attachment;', Response::contentDisposition('inline; x=y', 'a.txt'));
	}

	public function testContentDispositionCannotBreakOutOfParameter()
	{
		$header = Response::contentDisposition('attachment', 'a"; filename="evil.html');

		// the only quotes left are the ones around the value
		$this->assertSame(2, substr_count(explode('; filename*=', $header)[0], '"'));
		$this->assertStringContainsString("filename*=UTF-8''a%22%3B%20filename%3D%22evil.html", $header);
	}

	public function testContentDispositionStripsControlCharacters()
	{
		$header = Response::contentDisposition('attachment', "a\r\nSet-Cookie: x=1.txt");

		$this->assertDoesNotMatchRegularExpression('/[\x00-\x1f]/', $header);
	}

	public function testContentDispositionUnicode()
	{
		$header = Response::contentDisposition('attachment', 'résumé.pdf');

		$this->assertSame('attachment; filename="r_sum_.pdf"; filename*=UTF-8\'\'r%C3%A9sum%C3%A9.pdf', $header);
	}

	public static function contentTypeProvider(): array
	{
		return [
			'html' => ['text/html', false],
			'html with charset in capitals' => ['TEXT/HTML; charset=utf-8', false],
			'svg' => ['image/svg+xml', false],
			'svg without xml' => ['image/svg', false],
			'xhtml' => ['application/xhtml+xml', false],
			'text xml' => ['text/xml', false],
			'application xml' => ['application/xml; charset=utf-8', false],
			'xsl' => ['text/xsl', false],
			'xslt' => ['application/xslt+xml', false],
			'rss' => ['application/rss+xml', false],
			'mathml' => ['application/mathml+xml', false],
			'empty' => ['', false],
			'null' => [null, false],
			'png' => ['image/png', true],
			'pdf' => ['application/pdf', true],
			'plain text' => ['text/plain; charset=utf-8', true],
			'video' => ['video/mp4', true],
		];
	}

	#[DataProvider('contentTypeProvider')]
	public function testIsSafeInlineContentType(?string $contentType, bool $safe)
	{
		$this->assertSame($safe, Response::isSafeInlineContentType($contentType));
	}

	public function testFileSecurityHeaders()
	{
		$this->assertSame(['X-Content-Type-Options' => 'nosniff'], Response::fileSecurityHeaders('text/html', false));
		$this->assertSame(
			['X-Content-Type-Options' => 'nosniff', 'Content-Security-Policy' => 'sandbox'],
			Response::fileSecurityHeaders('image/png', true)
		);
		// The PDF viewer of the browser doesn't work in a sandbox
		$this->assertSame(['X-Content-Type-Options' => 'nosniff'], Response::fileSecurityHeaders('application/pdf', true));
	}

	/**
	 * Audio and video aren't sandboxed: the media player of the browser doesn't work in a sandbox
	 */
	#[DataProvider('mediaTypeProvider')]
	public function testFileSecurityHeadersMediaNotSandboxed(string $contentType)
	{
		$this->assertSame(['X-Content-Type-Options' => 'nosniff'], Response::fileSecurityHeaders($contentType, true));
	}

	public static function mediaTypeProvider(): array
	{
		return [
			['audio/mpeg'],
			['audio/x-wav'],
			['Audio/Ogg; codecs=opus'],
			['video/mp4'],
		];
	}

	/**
	 * Other inline types keep the sandbox, also when they only look like media
	 */
	#[DataProvider('sandboxedTypeProvider')]
	public function testFileSecurityHeadersSandboxed(string $contentType)
	{
		$this->assertSame(
			['X-Content-Type-Options' => 'nosniff', 'Content-Security-Policy' => 'sandbox'],
			Response::fileSecurityHeaders($contentType, true)
		);
	}

	public static function sandboxedTypeProvider(): array
	{
		return [
			['text/plain'],
			['image/jpeg'],
			['application/octet-stream'],
			['application/audio'],
			['text/video'],
			[''],
		];
	}

	public static function downloadContentTypeProvider(): array
	{
		return [
			'svg as image' => ['image/svg+xml', 'image', 'image/svg+xml'],
			'svg as image in capitals' => ['image/svg+xml', 'Image', 'image/svg+xml'],
			'svg opened as page' => ['image/svg+xml', 'document', 'text/plain'],
			'svg in iframe' => ['image/svg+xml', 'iframe', 'text/plain'],
			'svg in object' => ['image/svg+xml', 'object', 'text/plain'],
			'svg in embed' => ['image/svg+xml', 'embed', 'text/plain'],
			'svg without header' => ['image/svg+xml', null, 'text/plain'],
			'svg with empty header' => ['image/svg+xml', '', 'text/plain'],
			'html opened as page' => ['text/html; charset=utf-8', 'document', 'text/plain'],
			'xml without header' => ['application/xml', null, 'text/plain'],
			'no type' => [null, 'document', 'text/plain'],
			'png as image' => ['image/png', 'image', 'image/png'],
			'png opened as page' => ['image/png', 'document', 'image/png'],
			'png in object' => ['image/png', 'object', 'image/png'],
			'pdf without header' => ['application/pdf', null, 'application/pdf'],
		];
	}

	/**
	 * HTML, SVG and XML only keep their type when the browser loads them as an image
	 */
	#[DataProvider('downloadContentTypeProvider')]
	public function testDownloadContentType(?string $contentType, ?string $fetchDest, string $expected)
	{
		$this->assertSame($expected, Response::downloadContentType($contentType, $fetchDest));
	}
}
