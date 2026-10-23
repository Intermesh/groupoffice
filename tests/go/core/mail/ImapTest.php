<?php

namespace go\core\mail;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Imap talking to a scripted server over a socket pair
 */
class ScriptedImap extends Imap {
	/** @var resource */
	public $server;

	public function __construct(string $serverResponse) {
		[$this->socket, $this->server] = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
		fwrite($this->server, $serverResponse);
	}

	public function __destruct() {
		fclose($this->socket);
		fclose($this->server);
	}

	public function sentCommand(): string {
		return fgets($this->server);
	}
}

class ImapTest extends TestCase {

	public static function quotaRootProvider(): array {
		return [
			'empty root name' => [
				"* QUOTAROOT INBOX \"\"\r\n* QUOTA \"\" (STORAGE 10 512)\r\nTAG1 OK Getquotaroot completed\r\n",
				['' => ['STORAGE' => ['usage' => 10, 'limit' => 512]]]
			],
			'several roots and resources' => [
				"* QUOTAROOT INBOX \"User quota\" shared\r\n" .
				"* QUOTA \"User quota\" (STORAGE 2048 4096 MESSAGE 5 1000)\r\n" .
				"* QUOTA shared (storage 1 2)\r\n" .
				"TAG1 OK Getquotaroot completed\r\n",
				[
					'User quota' => ['STORAGE' => ['usage' => 2048, 'limit' => 4096], 'MESSAGE' => ['usage' => 5, 'limit' => 1000]],
					'shared' => ['STORAGE' => ['usage' => 1, 'limit' => 2]],
				]
			],
			'lowercase response and a zero limit' => [
				"* quotaroot INBOX root\r\n* quota root (STORAGE 0 0)\r\nTAG1 OK Getquotaroot completed\r\n",
				['root' => ['STORAGE' => ['usage' => 0, 'limit' => 0]]]
			],
			'no quota root' => [
				"* QUOTAROOT INBOX\r\nTAG1 OK Getquotaroot completed\r\n",
				[]
			],
			'refused' => [
				"TAG1 NO Quota not supported\r\n",
				false
			],
		];
	}

	#[DataProvider('quotaRootProvider')]
	public function testGetQuotaRoot(string $serverResponse, array|false $expected) {
		$imap = new ScriptedImap($serverResponse);

		$this->assertSame($expected, $imap->getQuotaRoot('INBOX'));
		$this->assertSame("TAG1 GETQUOTAROOT \"INBOX\"\r\n", $imap->sentCommand());
	}
}
