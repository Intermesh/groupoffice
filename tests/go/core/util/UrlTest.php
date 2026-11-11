<?php
namespace go\core\util;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Url::isLocalPath() decides if a URL from a request may be used as redirect after a login.
 */
class UrlTest extends TestCase
{
	public static function urls(): array
	{
		return [
			'path' => ['/site/account/profile', true],
			'path with query' => ['/index.php?r=site/front/page&slug=x', true],
			'root' => ['/', true],
			'absolute' => ['https://example.com/', false],
			'protocol relative' => ['//example.com/', false],
			'backslash' => ['/\\example.com/', false],
			'backslashes' => ['\\\\example.com', false],
			'javascript' => ['javascript:alert(1)', false],
			'relative' => ['example.com', false],
			'tab' => ["/\t/example.com", false],
			'newline' => ["/x\r\nLocation: https://example.com", false],
			'empty' => ['', false],
		];
	}

	#[DataProvider('urls')]
	public function testIsLocalPath(string $url, bool $expected): void
	{
		$this->assertSame($expected, Url::isLocalPath($url));
	}
}
