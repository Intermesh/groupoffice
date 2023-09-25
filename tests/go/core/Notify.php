<?php
namespace go\core;

class Notify extends \PHPUnit\Framework\TestCase {
	public function testMail() {


		go()->getSettings()->systemEmail = 'admin@intermesh.localhost';
		go()->getSettings()->smtpHost = 'mailserver';
		go()->getSettings()->smtpPort = 25;
		go()->getSettings()->smtpEncryption = null;
		go()->getSettings()->save();



		$ctrl = new \go\core\controller\Notify();
		$response = $ctrl->mail([
			"subject" => "Test message",
			"body" =>  "This is the body",
			"to" => ["admin@intermesh.localhost" => "Admin"]
		]);

		$this->assertEquals(true, $response['success']);

		$response = $ctrl->mail([
			"subject" => "Test message",
			"body" =>  "This is the body",
			"to" => "'System Admin' <admin@intermesh.localhost>"
		]);

		$this->assertEquals(true, $response['success']);

	}
}