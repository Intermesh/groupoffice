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
}
