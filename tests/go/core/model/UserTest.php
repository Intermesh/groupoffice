<?php

namespace go\core;

use GO\Base\Model\Module;
use go\core\auth\Authenticate;
use go\core\model\Token;
use go\core\model\User;
use go\core\util\ClassFinder;
use go\core\validate\ErrorCode;

class UserTest extends \PHPUnit\Framework\TestCase
{
	public function testProps() {
		$user = User::find()->single();

		$props = $user->toArray();

		$this->assertArrayHasKey('username', $props);
	}

	public function testCreateFail() {
		$user = new User();
		$user->username = 'test1';
		//pass should not be valid by haveibeenpwnd
		$user->setPassword('test1test1');
		$user->displayName = 'Test user 1';
		$user->email = $user->recoveryEmail = 'test1@intermesh.localhost';

		$success = $user->save();

		$this->assertNotEmpty($user->getValidationErrors());
		$this->assertEquals(false, $success);

		$e = $user->getValidationError('password');

		$this->assertEquals(ErrorCode::INVALID_INPUT, $e['code']);
	}

	public function testCreate()
	{
		$user = new User();
		$user->username = 'test1';
		$user->setPassword('sdfTgs-Dfgg-$#ddg');
		$user->displayName = 'Test user 1';
		$user->email = $user->recoveryEmail = 'test1@intermesh.localhost';

		$success = $user->save();

		$this->assertEmpty($user->getValidationErrors());
		$this->assertEquals(true, $success);

		$user = new User();
		$user->username = 'test2';
		$user->setPassword('sdfTgs-Dfgg-$#ddg');
		$user->displayName = 'Test user 2';
		$user->email = $user->recoveryEmail = 'test2@intermesh.localhost';

		$success = $user->save();

		$this->assertEquals(true, $success);

		$user = new User();
		$user->username = 'test2';
		$user->setPassword('sdfTgs-Dfgg-$#ddg');
		$user->displayName = 'Test user 2';
		$user->email = $user->recoveryEmail = 'test2unique@intermesh.localhost';

		$success = $user->save();

		$this->assertEquals(false, $success);


		$validationError = $user->getValidationError('username');

		$this->assertIsArray($validationError);

		$this->assertEquals(ErrorCode::UNIQUE, $validationError['code']);
	}

	public function testAdminToArray() {
		$admin = User::findById(1);
		$array = $admin->toArray();

		$this->assertEquals(true, is_array($array));
	}

	public function testIsAdmin() {
		$admin = User::findById(1);

		$this->assertEquals(true, $admin->getIsAdmin());

		$user = User::find()->where(['username' => 'test1'])->single();

		$this->assertEquals(false, $user->getIsAdmin());
	}

	public function testAuth() {
		$auth = new Authenticate();
		$user = $auth->passwordLogin("test1", 'sdfTgs-Dfgg-$#ddg');

		$this->assertEquals(false, !$user);

		$token = new Token();
		$token->userId = $user->id;
		$token->addPassedAuthenticator($auth->getUsedPasswordAuthenticator());

		$success = $token->save();

		$this->assertEquals(true, $success);

		$this->assertEmpty($token->getPendingAuthenticators());

		$loginCount = $token->getUser()->loginCount;

		$success = $token->setAuthenticated();
		$this->assertEquals(true, $success);

		$this->assertEquals($loginCount + 1, $token->getUser()->loginCount);
	}

	public function testDelete() {
		$success  = User::delete(['username' => ['test1', 'test2']]);
		$this->assertEquals(true, $success);
	}
}
