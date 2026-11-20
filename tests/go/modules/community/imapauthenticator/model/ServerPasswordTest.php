<?php

namespace go\modules\community\imapauthenticator\model;

use go\modules\community\imapauthenticator\Module;
use PHPUnit\Framework\TestCase;

/**
 * The SMTP password of an IMAP server profile is write only. It must not be sent to the browser.
 */
class ServerPasswordTest extends TestCase
{
	protected function setUp(): void
	{
		if (!go()->getModule('community', 'imapauthenticator')) {
			$this->assertNotFalse(Module::get()->install(), 'Could not install imapauthenticator module');
		}
	}

	public function testSmtpPasswordIsNotReadable(): void
	{
		$this->assertNotContains('smtpPassword', Server::getReadableProperties());

		$server = new Server();
		$server->setValues(['imapHostname' => 'imap.example.com', 'smtpPassword' => 'smtpSecret']);

		$this->assertStringNotContainsString('smtpSecret', json_encode($server->toArray()));
	}

	public function testSmtpPasswordCanBeUsedOnTheServer(): void
	{
		$server = new Server();
		$server->setValues(['smtpPassword' => 'smtpSecret']);

		$this->assertSame('smtpSecret', $server->decryptSmtpPassword());
	}
}
