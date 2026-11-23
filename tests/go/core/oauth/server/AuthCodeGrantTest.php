<?php
namespace go\core\oauth\server;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Apps on the user's computer listen on a random port, also when they registered "localhost" without one
 */
class AuthCodeGrantTest extends TestCase
{
	public static function redirects(): array
	{
		return [
			'other port' => ['http://localhost:56710/callback', ['http://localhost/callback'], true],
			'other port than registered' => ['http://localhost:56710/callback', ['http://localhost:3000/callback'], true],
			'other path' => ['http://localhost:56710/other', ['http://localhost/callback'], false],
			'look-alike host' => ['http://localhost.evil.example.com:56710/callback', ['http://localhost/callback'], false],
			'https' => ['https://localhost:56710/callback', ['https://localhost/callback'], false],
			'not localhost' => ['http://assistant.example.com:8080/callback', ['http://assistant.example.com/callback'], false],
			'registered elsewhere' => ['http://localhost:56710/callback', ['https://assistant.example.com/callback'], false],
		];
	}

	#[DataProvider('redirects')]
	public function testLocalhostMayUseAnyPort(string $redirectUri, array $allowed, bool $expected): void
	{
		$this->assertSame($expected, AuthCodeGrant::matchesLocalhostExceptPort($redirectUri, $allowed));
	}
}
