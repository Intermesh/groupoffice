<?php

namespace go\core\mail;

use PHPUnit\Framework\TestCase;

class MailerTest extends TestCase
{
	protected function tearDown(): void
	{
		Mailer::$sendingDisabled = null;
	}

	/**
	 * The MCP server disables sending, so an AI assistant can't send e-mail, also not through modules that send mail when
	 * something is saved
	 */
	public function testSendingCanBeDisabled(): void
	{
		Mailer::$sendingDisabled = 'No e-mail now';
		$message = go()->getMailer()->compose()
			->setFrom('from@example.com')
			->setTo('to@example.com')
			->setSubject('Test')
			->setBody('Test');

		try {
			$message->send();
			$this->fail('The message was sent');
		} catch (\Exception $e) {
			$this->assertSame('No e-mail now', $e->getMessage());
		}

		try {
			go()->getMailer()->sendMime("Subject: Test\r\n\r\nTest");
			$this->fail('The MIME message was sent');
		} catch (\Exception $e) {
			$this->assertSame('No e-mail now', $e->getMessage());
		}

		// Rendering a message, like an e-mail draft, still works
		$this->assertStringContainsString('Subject: Test', $message->toString());
	}
}
