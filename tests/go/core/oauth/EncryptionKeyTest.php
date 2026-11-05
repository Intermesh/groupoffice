<?php
namespace go\core\oauth\server;

use Defuse\Crypto\Key;
use PHPUnit\Framework\TestCase;

class EncryptionKeyTest extends TestCase
{
	private const OLD_HARDCODED_KEY = 'lxZFUEsBCJ2Yb14IF2ygAHI5N4+ZAUXXaSeeJm6+twsUmIen';

	private function keyFile(): \go\core\fs\File
	{
		return go()->getDataFolder()->getFile('oauth2/encryption.key');
	}

	public function testKeyIsCreatedAndStable()
	{
		$key = EncryptionKey::get();
		$this->assertInstanceOf(Key::class, $key);
		$this->assertTrue($this->keyFile()->exists());

		// the same key every time, otherwise codes can't be exchanged
		$this->assertSame($key->saveToAsciiSafeString(), EncryptionKey::get()->saveToAsciiSafeString());
	}

	public function testKeyFileIsPrivate()
	{
		EncryptionKey::get();

		$this->assertSame('0600', substr(sprintf('%o', fileperms($this->keyFile()->getPath())), -4));
	}

	public function testKeyIsRandomPerInstallation()
	{
		$file = $this->keyFile();
		EncryptionKey::get();
		$original = $file->getContents();

		try {
			$file->delete();
			$new = EncryptionKey::get()->saveToAsciiSafeString();

			$this->assertNotSame($original, $new);
			$this->assertStringNotContainsString(self::OLD_HARDCODED_KEY, $new);
		} finally {
			// other tests need the same key as before
			$file->putContents($original);
			$file->chmod(0600);
		}
	}

	public function testNoTemporaryFilesAreLeftBehind()
	{
		EncryptionKey::get();

		$this->assertSame([], glob($this->keyFile()->getFolder()->getPath() . '/encryption*.tmp') ?: []);
	}

	/**
	 * Everybody can read the source. A key in it is not a key.
	 */
	public function testNoKeyIsHardcodedInTheServer()
	{
		$source = file_get_contents(__DIR__ . '/../../../../www/api/oauth.php');

		$this->assertNotFalse($source);
		$this->assertStringNotContainsString(self::OLD_HARDCODED_KEY, $source);
		$this->assertStringContainsString('EncryptionKey::get()', $source);
	}
}
