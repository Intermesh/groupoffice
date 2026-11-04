<?php

namespace go\core\util;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CryptTest extends TestCase
{
	public function testValidInternalToken(): void
	{
		$token = Crypt::createInternalToken('clearcache');
		$this->assertTrue(Crypt::verifyInternalToken($token, 'clearcache'));
	}

	public static function invalidTokenProvider(): array
	{
		return [
			'empty' => [''],
			'plain text' => ['clearcache:' . time()],
			'GOCRYPT2 prefixed plain text' => ['{GOCRYPT2}clearcache:' . time()],
			'garbage' => ['def50200abcdef'],
		];
	}

	#[DataProvider('invalidTokenProvider')]
	public function testInvalidInternalToken(string $token): void
	{
		$this->assertFalse(Crypt::verifyInternalToken($token, 'clearcache'));
	}

	public function testTamperedInternalToken(): void
	{
		$token = Crypt::createInternalToken('clearcache');
		$token[strlen($token) - 1] = $token[strlen($token) - 1] === 'a' ? 'b' : 'a';
		$this->assertFalse(Crypt::verifyInternalToken($token, 'clearcache'));
	}

	public function testWrongPurposeInternalToken(): void
	{
		$token = Crypt::createInternalToken('clearapcu');
		$this->assertFalse(Crypt::verifyInternalToken($token, 'clearcache'));
	}

	public function testExpiredInternalToken(): void
	{
		$token = Crypt::createInternalToken('clearcache');
		$this->assertTrue(Crypt::verifyInternalToken($token, 'clearcache', 60));

		// Build a token with an old timestamp using the same server key
		$file = go()->getDataFolder()->getFile('defuse-crypto.txt');
		$key = Key::loadFromAsciiSafeString($file->getContents());
		$old = Crypto::encrypt('clearcache:' . (time() - 120), $key);
		$this->assertFalse(Crypt::verifyInternalToken($old, 'clearcache', 60));
	}

	public function testTokenFromOtherKey(): void
	{
		$token = Crypto::encrypt('clearcache:' . time(), Key::createNewRandomKey());
		$this->assertFalse(Crypt::verifyInternalToken($token, 'clearcache'));
	}
}
