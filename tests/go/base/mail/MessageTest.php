<?php
namespace go\base\mail;


use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase {
	public function testLoadMime() {

		$mime = file_get_contents(dirname(__DIR__,3) . '/static/invite.eml');
		$message = new \GO\Base\Mail\Message();
		$message->loadMimeMessage($mime);

		$this->assertEquals("Invitation: test", $message->getSubject());




	}
}
