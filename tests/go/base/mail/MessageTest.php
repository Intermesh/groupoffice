<?php
namespace go\base\mail;


use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase {
	public function testLoadMime() {

		$mime = file_get_contents(dirname(__DIR__,3) . '/static/invite.eml');
		$message = new \GO\Base\Mail\Message();
		$message->loadMimeMessage($mime);

		$this->assertEquals("Invitation: test", $message->getSubject());

//		echo $message->toString();


	}

	public function testLoadMime2() {

		$mime = file_get_contents(dirname(__DIR__,3) . '/static/ios-invite.eml');
		$message = new \GO\Base\Mail\Message();
		$message->loadMimeMessage($mime);

		$this->assertEquals("E727B9C5-4451-48A6-ABDD-BEFC26C23460@intermesh.localhost", $message->getId());

//		echo $message->toString();

	}

	public function testMimeWords() {
		$subject = "=?UTF-8?Q?Vergeet_u_niet_de_watermeterstand_door_te_geven?_|_Klantnumme?=
 =?UTF-8?Q?r_12345?=";

		$decoded = \go\core\imap\Utils::mimeHeaderDecode($subject);

		$this->assertEquals("Vergeet u niet de watermeterstand door te geven? | Klantnummer 12345", $decoded);
	}
}
