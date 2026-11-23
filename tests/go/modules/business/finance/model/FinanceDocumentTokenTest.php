<?php
namespace go\modules\business\finance\model;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Customers view and pay a document with a link that has its token, without logging in. After paying they go to the
 * "returnUrl" of the link, which may only be a page of this Group-Office (no open redirect).
 *
 * The finance module needs a license, so the document is created without its constructor and database.
 */
class FinanceDocumentTokenTest extends TestCase
{
	private function document(?string $token): FinanceDocument
	{
		$document = (new \ReflectionClass(FinanceDocument::class))->newInstanceWithoutConstructor();
		$document->id = 5;
		$document->token = $token;
		return $document;
	}

	public static function tokens(): array
	{
		return [
			'same' => ['abcdefghij0123456789', 'abcdefghij0123456789', true],
			'other' => ['abcdefghij0123456789', 'abcdefghij0123456780', false],
			'empty link token' => ['abcdefghij0123456789', '', false],
			'document without token' => [null, '', false],
			'document with empty token' => ['', '', false],
			'numeric strings' => ['0e1234', '0e5678', false],
		];
	}

	#[DataProvider('tokens')]
	public function testCheckToken(?string $documentToken, string $token, bool $expected): void
	{
		$this->assertSame($expected, $this->document($documentToken)->checkToken($token));
	}

	public static function returnUrls(): array
	{
		$pageUrl = go()->getAuthState()->getPageUrl();
		$origin = substr($pageUrl, 0, strpos($pageUrl, '/', strlen('https://')));
		return [
			'none' => [null, false],
			'local path' => ['/account', true],
			'same server' => [$origin . '/account', true],
			'other website' => ['https://evil.example.com/', false],
			'longer host' => [$origin . '.evil.example.com/', false],
			'protocol relative' => ['//evil.example.com/', false],
			'javascript' => ['javascript:alert(document.cookie)', false],
			'html' => ['"><script>alert(1)</script>', false],
		];
	}

	#[DataProvider('returnUrls')]
	public function testReturnUrl(?string $returnUrl, bool $allowed): void
	{
		$document = $this->document('abcdefghij0123456789');
		$this->assertSame($allowed ? $returnUrl : $document->getCustomerUrl(), $document->getReturnUrl($returnUrl));
	}
}
