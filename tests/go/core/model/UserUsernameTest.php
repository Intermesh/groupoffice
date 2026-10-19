<?php

namespace go\core\model;

use go\core\validate\ErrorCode;
use PHPUnit\Framework\TestCase;

class UserUsernameTest extends TestCase
{
	private array $userIds = [];

	protected function tearDown(): void
	{
		foreach ($this->userIds as $id) {
			User::delete(['id' => $id]);
		}
	}

	public static function validNames(): array
	{
		return [
			['john'],
			['John.Smith'],
			['john_smith-2'],
			['john@example.com'],
			['A'],
			['0'],
			['a.b-c_d@e.f'],
		];
	}

	/**
	 * @dataProvider validNames
	 */
	public function testValidNames(string $name)
	{
		$this->assertSame(1, preg_match(User::USERNAME_REGEX, $name), "'$name' should be valid");
	}

	public static function invalidNames(): array
	{
		return [
			// the old pattern accepted all of these: one valid character was enough
			['a b'],
			['john smith'],
			['<script>alert(1)</script>x'],
			["john'; drop table x; --"],
			['john/../../etc'],
			['john\\admin'],
			['a%00b'],
			['jöhn'],
			['john+tag@example.com'],
			['"john"'],
			// and these never matched or never should
			[''],
			[' '],
			['!!!'],
			// a final new line is not part of a name
			["john\n"],
			["\njohn"],
			["jo\nhn"],
			["john\r\n"],
			["john\0"],
		];
	}

	/**
	 * @dataProvider invalidNames
	 */
	public function testInvalidNames(string $name)
	{
		$this->assertSame(0, preg_match(User::USERNAME_REGEX, $name), "'" . addcslashes($name, "\0..\37") . "' should be invalid");
	}

	private function newUser(string $username): User
	{
		$user = new User();
		$user->setValues([
			'username' => $username,
			'displayName' => 'Username test',
			'email' => uniqid('un') . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);

		return $user;
	}

	public function testUserWithInvalidCharactersCannotBeSaved()
	{
		$user = $this->newUser('bad name<b>' . uniqid());

		$this->assertFalse($user->save());

		$error = $user->getValidationError('username');
		$this->assertNotNull($error);
		$this->assertSame(ErrorCode::MALFORMED, $error['code']);
	}

	public function testUserWithValidNameCanBeSaved()
	{
		$user = $this->newUser(uniqid('un.') . '-ok_1@example.com');

		$this->assertTrue($user->save(), var_export($user->getValidationErrors(), true));
		$this->userIds[] = $user->id;
	}

	public function testRenamingToInvalidNameIsRefused()
	{
		$user = $this->newUser(uniqid('un'));
		$this->assertTrue($user->save());
		$this->userIds[] = $user->id;

		$user = User::findById($user->id);
		$user->username = 'new name';

		$this->assertFalse($user->save());
		$this->assertNotNull($user->getValidationError('username'));
	}

	public function testExistingUsersKeepWorkingUntilTheyAreRenamed()
	{
		// Users that were created while the check was weak may have a name that is not allowed now. The name is only
		// checked when it's changed, so changing something else about them still works.
		$user = $this->newUser(uniqid('un'));
		$this->assertTrue($user->save());
		$this->userIds[] = $user->id;
		go()->getDbConnection()->exec("update core_user set username = 'old style name' where id = " . (int) $user->id);

		$user = User::findById($user->id);
		$user->displayName = 'Changed';

		$this->assertTrue($user->save(), var_export($user->getValidationErrors(), true));
	}
}
