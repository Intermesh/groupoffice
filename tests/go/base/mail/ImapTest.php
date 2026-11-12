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
}
