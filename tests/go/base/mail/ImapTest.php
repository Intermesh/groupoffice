<?php
namespace go\base\mail;

use GO\Base\Mail\Imap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ImapTest extends TestCase {

	private function createImap(): Imap
	{
		$imap = new Imap();
		$imap->handle = fopen('php://memory', 'w+');
		return $imap;
	}

	private function written(Imap $imap): string
	{
		rewind($imap->handle);
		$data = stream_get_contents($imap->handle);
		// prevent LOGOUT on destruct
		fclose($imap->handle);
		$imap->handle = null;
		return $data;
	}

	public function testSendsSingleLineCommand()
	{
		$imap = $this->createImap();
		$imap->send_command("UID SORT (FROM) US-ASCII ALL\r\n");

		$this->assertEquals("A1 UID SORT (FROM) US-ASCII ALL\r\n", $this->written($imap));
	}

	public static function injectedCommands(): array
	{
		return [
			'crlf' => ["UID SORT (FROM) US-ASCII ALL\r\nA9 DELETE \"INBOX\"\r\n"],
			'lf' => ["UID SORT (FROM) US-ASCII ALL\nA9 DELETE \"INBOX\"\r\n"],
			'cr' => ["UID SORT (FROM) US-ASCII ALL\rA9 DELETE \"INBOX\"\r\n"],
			'nul' => ["UID SORT (FROM) US-ASCII ALL\0\r\n"],
			'no terminator' => ["UID SORT (FROM) US-ASCII ALL"],
		];
	}

	#[DataProvider('injectedCommands')]
	public function testRejectsInjectedCommands(string $command)
	{
		$imap = $this->createImap();
		try {
			$imap->send_command($command);
			$this->fail("Command with line break was sent");
		} catch (\Exception $e) {
			$this->assertStringContainsString("INVALID IMAP INPUT DETECTED", $e->getMessage());
		}

		$this->assertEquals("", $this->written($imap), "Nothing may be written to the IMAP connection");
	}

	public function testAclIdentifierIsQuoted()
	{
		$imap = $this->createImap();
		$imap->set_acl('INBOX', 'bob lrswipkxtecda', 'lrs');
		$this->assertEquals("A1 SETACL \"INBOX\" \"bob lrswipkxtecda\" lrs\r\n", $this->written($imap));

		$imap = $this->createImap();
		$imap->delete_acl('INBOX', 'bob" "anyone');
		$this->assertEquals("A1 DELETEACL \"INBOX\" \"bob\\\" \\\"anyone\"\r\n", $this->written($imap));
	}

	public function testAclIdentifierWithLineBreakIsRefused()
	{
		$imap = $this->createImap();
		try {
			$imap->set_acl('INBOX', "bob lrs\r\nA9 DELETE \"INBOX\"", 'lrs');
			$this->fail("Identifier with line break was sent");
		} catch (\Exception $e) {
			$this->assertStringContainsString("INVALID IMAP INPUT DETECTED", $e->getMessage());
		}
		$this->assertEquals("", $this->written($imap));
	}

	public function testAclPermissionsMustBeRights()
	{
		$imap = $this->createImap();
		try {
			$imap->set_acl('INBOX', 'bob', 'lrs anyone');
			$this->fail("Invalid rights were sent");
		} catch (\Exception $e) {
			$this->assertStringContainsString("INVALID IMAP INPUT DETECTED", $e->getMessage());
		}
		$this->assertEquals("", $this->written($imap));
	}

	#[DataProvider('injectedCredentials')]
	public function testLoginWithLineBreakIsRefused(string $auth, string $username, string $password)
	{
		$imap = $this->createImap();
		$imap->auth = $auth;
		$method = new \ReflectionMethod(Imap::class, 'authenticate');
		$message = null;
		try {
			$method->invoke($imap, $username, $password);
		} catch (\Exception $e) {
			$message = $e->getMessage();
		}
		$this->assertEquals("", $this->written($imap), "Nothing may be written to the IMAP connection");
		$this->assertStringContainsString("INVALID IMAP INPUT DETECTED", (string) $message);
	}

	public static function injectedCredentials(): array
	{
		return [
			'password crlf' => ['plain', 'user', "secret\r\nA9 DELETE \"INBOX\""],
			'password lf' => ['plain', 'user', "secret\nA9 DELETE \"INBOX\""],
			'username cr' => ['plain', "user\rA9 DELETE \"INBOX\"", 'secret'],
			'password nul' => ['plain', 'user', "secret\0"],
			'xoauth2 token' => ['xoauth2', 'user', "token\r\nA9 DELETE \"INBOX\""],
		];
	}
}
