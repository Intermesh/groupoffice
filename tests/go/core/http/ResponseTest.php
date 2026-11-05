<?php
namespace go\core\http;

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
}
