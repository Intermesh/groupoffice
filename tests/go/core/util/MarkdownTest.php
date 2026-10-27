<?php
namespace go\core\util;

use PHPUnit\Framework\TestCase;

class MarkdownTest extends TestCase
{
	public function testToHtmlEscapesRawHtml() {
		$html = Markdown::toHtml("**bold** <script>alert('xss')</script> [link](javascript:alert(1))");

		$this->assertStringContainsString("<strong>bold</strong>", $html);
		$this->assertStringNotContainsString("<script>", $html);
		$this->assertStringNotContainsString('href="javascript:', $html);
	}

	public function testToText() {
		$this->assertEquals("Title\nSome bold & text", Markdown::toText("# Title\nSome **bold** & text"));
	}
}
