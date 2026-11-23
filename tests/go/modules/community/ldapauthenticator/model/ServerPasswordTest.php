<?php

namespace go\modules\community\ldapauthenticator\model;

use go\modules\community\ldapauthenticator\Module;
use PHPUnit\Framework\TestCase;

/**
 * The bind and SMTP passwords of an LDAP server profile are write only. They must not be sent to the browser.
 */
class ServerPasswordTest extends TestCase
{
	protected function setUp(): void
	{
		if (!go()->getModule('community', 'ldapauthenticator')) {
			$this->assertNotFalse(Module::get()->install(), 'Could not install ldapauthenticator module');
		}
	}

	public function testPasswordsAreNotReadable(): void
	{
		$this->assertNotContains('password', Server::getReadableProperties());
		$this->assertNotContains('smtpPassword', Server::getReadableProperties());

		$server = new Server();
		$server->setValues(['hostname' => 'ldap.example.com', 'password' => 'bindSecret', 'smtpPassword' => 'smtpSecret']);

		$json = json_encode($server->toArray());
		$this->assertStringNotContainsString('bindSecret', $json);
		$this->assertStringNotContainsString('smtpSecret', $json);
	}

	public function testPasswordsCanBeUsedOnTheServer(): void
	{
		$server = new Server();
		$server->setValues(['password' => 'bindSecret', 'smtpPassword' => 'smtpSecret']);

		$this->assertSame('bindSecret', $server->decryptPassword());
		$this->assertSame('smtpSecret', $server->readSmtpPassword());
	}
}
