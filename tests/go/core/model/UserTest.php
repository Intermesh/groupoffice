<?php

namespace go\core;

use GO\Base\Model\Module;
use go\core\model\User;
use go\core\util\ClassFinder;

class UserTest extends \PHPUnit\Framework\TestCase
{
	public function testCreate()
	{
		$user = new User();
		$user->username = 'test1';
		$user->setPassword('test1test1');
		$user->displayName = 'Test user 1';
		$user->email = $user->recoveryEmail = 'test1@intermesh.localhost';

		$success = $user->save();

		$this->assertEquals(true, $success);


		$user = new User();
		$user->username = 'test2';
		$user->setPassword('test1test1');
		$user->displayName = 'Test user 2';
		$user->email = $user->recoveryEmail = 'test2@intermesh.localhost';

		$success = $user->save();

		$this->assertEquals(true, $success);


		$user = new User();
		$user->username = 'test2';
		$user->setPassword('test1test1');
		$user->displayName = 'Test user 2';
		$user->email = $user->recoveryEmail = 'test2@intermesh.localhost';

		$success = $user->save();

		$this->assertEquals(false, $success);
	}


	public function testDelete() {
		$success  = User::delete(['username' => ['test1', 'test2']]);
		$this->assertEquals(true, $success);
	}



}
