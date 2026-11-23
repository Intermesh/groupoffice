<?php
namespace go\core\imap;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Values like the username, password and mailbox name are put into IMAP commands. A line break in such a value
 * must not start a new command on the connection.
 */
class ConnectionTest extends TestCase
{
	private Connection $connection;
	private $handle;

	protected function setUp(): void
	{
		$this->connection = new Connection();
		$this->handle = fopen('php://memory', 'w+');
		(new \ReflectionProperty(Connection::class, 'handle'))->setValue($this->connection, $this->handle);
	}

	private function written(): string
	{
		rewind($this->handle);
		$data = stream_get_contents($this->handle);
		// prevent LOGOUT on disconnect
		(new \ReflectionProperty(Connection::class, 'handle'))->setValue($this->connection, null);
		fclose($this->handle);
		return $data;
	}

	public function testSendsCommand(): void
	{
		$this->connection->sendCommand('SELECT "INBOX"');
		$this->assertEquals("A1 SELECT \"INBOX\"\r\n", $this->written());
	}

	public static function injected(): array
	{
		return [
			'crlf' => ["secret\r\nA9 DELETE \"INBOX\""],
			'lf' => ["secret\nA9 DELETE \"INBOX\""],
			'cr' => ["secret\rA9 DELETE \"INBOX\""],
			'nul' => ["secret\0"],
		];
	}

	#[DataProvider('injected')]
	public function testLoginWithLineBreakIsRefused(string $password): void
	{
		$message = null;
		try {
			$this->connection->authenticate('user', $password);
		} catch (\Exception $e) {
			$message = $e->getMessage();
		}
		$this->assertEquals("", $this->written(), "Nothing may be written to the IMAP connection");
		$this->assertStringContainsString("INVALID IMAP INPUT DETECTED", (string) $message);
	}

	#[DataProvider('injected')]
	public function testMailboxNameStaysOneLine(string $name): void
	{
		try {
			$this->connection->selectMailbox($name);
		} catch (\Exception $e) {
			// there is no server to respond
		}
		$written = $this->written();
		$this->assertStringStartsWith('A1 SELECT "', $written);
		$this->assertEquals(strlen($written) - 2, strpos($written, "\r\n"), "The command must be one line");
		$this->assertFalse(strpbrk(substr($written, 0, -2), "\r\n\0"));
	}

	public static function logins(): array
	{
		return [
			'plain' => ['LOGIN "user" "s3cret"', 'LOGIN "user" "***"'],
			'escaped quote' => ['LOGIN "us\\"er" "s3c\\"ret\\\\"', 'LOGIN "us\\"er" "***"'],
			'other command' => ['SELECT "INBOX"', 'SELECT "INBOX"'],
		];
	}

	#[DataProvider('logins')]
	public function testMaskCommand(string $command, string $expected): void
	{
		$this->assertSame($expected, Connection::maskCommand($command));
	}

	public function testLoginPasswordIsNotInTheDebugLog(): void
	{
		$debugger = go()->getDebugger();
		$enabled = $debugger->enabled;
		$debugger->enable(false);
		try {
			$this->connection->sendCommand('LOGIN "user" "s3cret"');
			$entries = var_export($debugger->getEntries(), true);
		} finally {
			$debugger->enabled = $enabled;
		}
		$this->written();
		$this->assertStringContainsString('LOGIN "user" "***"', $entries);
		$this->assertStringNotContainsString('s3cret', $entries);
	}

	public function testGetQuota(): void
	{
		[$client, $server] = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
		fwrite($server, "* QUOTAROOT \"INBOX\" \"\"\r\n* QUOTA \"\" (STORAGE 10 512)\r\nA1 OK Getquotaroot completed\r\n");
		(new \ReflectionProperty(Connection::class, 'handle'))->setValue($this->connection, $client);
		(new \ReflectionProperty(Connection::class, 'capability'))->setValue($this->connection, 'IMAP4rev1 QUOTA');
		fclose($this->handle);
		$this->handle = $client;

		try {
			$this->assertSame(['usage' => 10, 'limit' => 512], $this->connection->getQuota());
			$this->assertSame("A1 GETQUOTAROOT \"INBOX\"\r\n", fread($server, 1024));
		} finally {
			(new \ReflectionProperty(Connection::class, 'handle'))->setValue($this->connection, null);
			fclose($server);
			fclose($client);
		}
	}
}
