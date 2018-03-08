<?php
namespace go\core;

class AppTest extends \PHPUnit\Framework\TestCase {
	public function testSettings() {
		App::get()->getSettings()->language = 'en';
		$success = App::get()->getSettings()->save();
		
		$this->assertEquals(true, $success);
	}
}
