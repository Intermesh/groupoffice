<?php
namespace test;

use go\core\App;
use PHPUnit\Framework\TestCase;


class AppTest extends TestCase {
	public function testSettings() {
		$app = App::get();
		
		$this->assertInstanceOf(App::class, $app);
	}
}
