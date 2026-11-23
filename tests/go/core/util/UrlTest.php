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

	public static function origins(): array
	{
		$origin = 'https://docs.example.com/onlyoffice/';
		return [
			'same' => ['https://docs.example.com/cache/files/x.docx', $origin, true],
			'default port' => ['https://docs.example.com:443/x', $origin, true],
			'case and trailing dot' => ['HTTPS://Docs.Example.COM./x', $origin, true],
			'longer host' => ['https://docs.example.com.evil.tld/x', $origin, false],
			'user name' => ['https://docs.example.com@evil.tld/x', $origin, false],
			'user name of same host' => ['https://evil@docs.example.com/x', $origin, false],
			'other scheme' => ['http://docs.example.com/x', $origin, false],
			'other port' => ['https://docs.example.com:8443/x', $origin, false],
			'subdomain' => ['https://evil.docs.example.com/x', $origin, false],
			'backslash' => ['https://evil.tld\\@docs.example.com/x', $origin, false],
			'white space' => ["https://docs.example.com /x", $origin, false],
			'file' => ['file:///etc/passwd', $origin, false],
			'relative' => ['/cache/files/x.docx', $origin, false],
			'empty origin' => ['https://docs.example.com/x', '', false],
			'both empty' => ['', '', false],
		];
	}

	#[DataProvider('origins')]
	public function testIsSameOrigin(string $url, string $origin, bool $expected): void
	{
		$this->assertSame($expected, Url::isSameOrigin($url, $origin));
	}
}
