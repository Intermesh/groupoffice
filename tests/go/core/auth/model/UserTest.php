<?php
namespace go\core\model;

use go\core\model\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase {
	public function testProps() {
		$user = User::find()->single();
		
		$props = $user->toArray();
		
		$this->assertArrayHasKey('username', $props);
		
		
	}
}
