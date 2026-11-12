<?php

namespace go\core;

use GO\Base\Model\Module;
use go\core\model\User;
use go\core\util\ClassFinder;
use go\core\util\StringUtil;

class StringUtilTest extends \PHPUnit\Framework\TestCase {
	public function testCyrillicSearch() {
		$str = "Гугъл гъл";

		$words = StringUtil::splitTextKeywords($str);

		$this->assertEquals(2 ,count($words));

		$this->assertEquals(mb_strtolower("Гугъл") ,$words[0]);

		$this->assertEquals(mb_strtolower("гъл") ,$words[1]);
	}

	public function testXSS() {

		$xss[] = '<script>alert("XSS");</script>';
		$xss[] = '<script>alert(\'XSS\');</script>';
		$xss[] = '<script>alert(\'XSS\')</script>';
		$xss[] = '<<script>alert("XSS");//<</script>';
		$xss[] = '<sCripT>alert("XSS")</scRipt>';
		$xss[] = '<scr<script>ipt>alert(\'XSS\')</script>';
		$xss[] = '<sCripT>alert(\'XSS\')</scRipt>';
		$xss[] = '<img src="/" onerror="alert(\'XSS\')"/>';
		$xss[] = '<img src=x onMouseOver=alert(\'XSS\')>';
		$xss[] = '<svg/onload=eval("ale"+"rt")(`XSS${alert`XSS`}`)>';
		$xss[] = '<img src=\'nevermind\' onerror="alert(\'XSS\');" />';
		$xss[] = '<< script>alert("XSS");//<</ script>';
		$xss[] = '<svg/onload=alert(\'XSS\')>';
		$xss[] = 'div.innerHTML = \'<script deferred>alert("XSS");</script>\';';
		$xss[] = '<img src="aaa" onerror=alert(\'xxs\')>';
		$xss[] = '<body onload="alert(\'XSS\')">';


		foreach($xss as $x) {
			$result = StringUtil::detectXSS($x);
			$this->assertEquals(true, $result);
		}

		$false[] = '<img alt="Download on the App Store" height="40" src="">';

		foreach($false as $x) {
			$result = StringUtil::detectXSS($x);
			$this->assertEquals(false, $result);
		}
	}

	/**
	 * HTML that runs scripts. Some of these fooled the regular expressions of sanitizeHtml(), filterXSS() and detectXSS().
	 */
	public static function activeHtmlProvider(): array
	{
		return [
			'slash before handler' => ['<svg/onload=alert(1)>'],
			'slash before handler on img' => ['<img/src=x/onerror=alert(1)>'],
			'greater than in double quoted value' => ['<img alt=">" onerror=alert(1) src=x>'],
			'greater than in single quoted value' => ["<img src=x alt='>'onerror=alert(1)>"],
			'handler right after quote' => ['<a href="x"onclick="alert(1)">x</a>'],
			'uppercase handler' => ['<IMG SRC=x ONERROR=alert(1)>'],
			'handler with newline' => ["<img src=x\nonerror=alert(1)>"],
			'script element' => ['<p>a</p><script>alert(1)</script>'],
			'javascript url' => ['<a href="javascript:alert(1)">x</a>'],
			'javascript url with tab' => ["<a href=\"java\tscript:alert(1)\">x</a>"],
			'javascript url with entities' => ['<a href="&#106;avascript:alert(1)">x</a>'],
			'data html url' => ['<a href="data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg==">x</a>'],
			'iframe' => ['<iframe src="https://example.com"></iframe>'],
			'object' => ['<object data="x.swf"></object>'],
			'math' => ['<math><mtext><img src=x onerror=alert(1)></mtext></math>'],
			'css expression' => ['<div style="width: expression(alert(1))">x</div>'],
			'formaction' => ['<button formaction="javascript:alert(1)">x</button>'],
			'nested in table' => ['<table><tr><td><img src=x onerror=alert(1)></td></tr></table>'],
		];
	}

	/**
	 * Check that the HTML has nothing left that can run a script, like the browser would see it
	 */
	private function assertNoActiveContent(string $html): void
	{
		$doc = new \DOMDocument();
		libxml_use_internal_errors(true);
		$doc->loadHTML('<?xml encoding="UTF-8"><div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
		libxml_clear_errors();

		$xpath = new \DOMXPath($doc);
		foreach ($xpath->query('//*') as $element) {
			$this->assertNotContains(strtolower($element->nodeName), ['script', 'iframe', 'object', 'embed', 'svg', 'math'], $html);
			foreach ($element->attributes as $attribute) {
				$name = strtolower($attribute->nodeName);
				$value = strtolower(preg_replace('/[\x00-\x20]+/', '', $attribute->value));
				$this->assertStringStartsNotWith('on', $name, "Event handler left in: " . $html);
				$this->assertNotSame('formaction', $name, $html);
				$this->assertStringNotContainsString('javascript:', $value, $html);
				$this->assertStringNotContainsString('expression(', $value, $html);
				$this->assertStringStartsNotWith('data:text', $value, $html);
			}
		}
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('activeHtmlProvider')]
	public function testSanitizeHtmlRemovesActiveContent(string $html)
	{
		$this->assertNoActiveContent(StringUtil::sanitizeHtml($html));
		$this->assertNoActiveContent(StringUtil::sanitizeHtml($html, false));
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('activeHtmlProvider')]
	public function testFilterXSSRemovesActiveContent(string $html)
	{
		$this->assertNoActiveContent(\GO\Base\Util\StringHelper::filterXSS($html));
	}

	public function testSanitizeHtmlKeepsNormalMail()
	{
		$html = '<style>p { color: red; }</style><table width="100%"><tr><td style="color:#333">Hëllo wörld € ✓</td></tr></table>'
			. '<p><a href="https://example.com/?a=1&amp;b=2" target="_blank">Link</a> <a href="mailto:test@example.com">Mail</a></p>'
			. '<img src="cid:image001.png@01D" alt="logo" width="10"><img src="data:image/png;base64,iVBORw0KGgo=" alt="pixel">'
			. '<p online="1">Online</p>';

		$result = StringUtil::sanitizeHtml($html);

		$this->assertStringContainsString('Hëllo wörld € ✓', html_entity_decode($result, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
		$this->assertStringContainsString('href="https://example.com/?a=1&amp;b=2"', $result);
		$this->assertStringContainsString('href="mailto:test@example.com"', $result);
		$this->assertStringContainsString('src="cid:image001.png@01D"', $result);
		$this->assertStringContainsString('src="data:image/png;base64,iVBORw0KGgo="', $result);
		$this->assertStringContainsString('style="color:#333"', $result);
		$this->assertStringContainsString('<table width="100%">', $result);
		// The extracted mail style is kept
		$this->assertStringContainsString('groupoffice-extracted-style', $result);
		$this->assertNoActiveContent($result);
	}
}
