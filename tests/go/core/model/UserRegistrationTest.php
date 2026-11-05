<?php

namespace go\core\model;

use PHPUnit\Framework\TestCase;

class UserRegistrationTest extends TestCase
{
	private array $userIds = [];

	protected function tearDown(): void
	{
		foreach ($this->userIds as $id) {
			User::delete(['id' => $id]);
		}
	}

	private function hostileInput(): array
	{
		$name = uniqid('reg');

		return [
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz',
			'recoveryEmail' => $name . '-recovery@example.com',
			'language' => 'en',
			'timezone' => 'Europe/Amsterdam',

			// things a client must never be able to choose
			'groups' => [Group::ID_ADMINS],
			'enabled' => false,
			'id' => 1,
			'loginCount' => 99,
			'forcePasswordChange' => false,
			'archive' => true,
		];
	}

	public function testFilterKeepsOnlyProfileProperties()
	{
		$filtered = User::filterRegistrationValues($this->hostileInput());

		$this->assertEqualsCanonicalizing(User::REGISTRATION_PROPERTIES, array_keys($filtered));
		foreach (['groups', 'enabled', 'id', 'loginCount', 'forcePasswordChange', 'archive'] as $forbidden) {
			$this->assertArrayNotHasKey($forbidden, $filtered);
		}
	}

	public function testFilterOnEmptyInput()
	{
		$this->assertSame([], User::filterRegistrationValues([]));
	}

	/**
	 * Same steps as the "register" action in api/auth.php
	 */
	public function testRegisteredUserCannotChooseGroups()
	{
		$input = $this->hostileInput();

		$user = new User();
		$user->setValues(User::filterRegistrationValues($input));
		$this->assertTrue($user->save(), var_export($user->getValidationErrors(), true));
		$this->userIds[] = $user->id;

		$fresh = User::findById($user->id);

		$this->assertFalse($fresh->isAdmin(), 'registered user must not become admin');
		$this->assertTrue($fresh->enabled);
		$this->assertNotEquals(User::ID_SUPER_ADMIN, $fresh->id);
		$this->assertSame(0, (int) $fresh->loginCount);
		$this->assertSame($input['email'], $fresh->email);
		$this->assertSame($input['timezone'], $fresh->timezone);
		$this->assertNotContains((string) Group::ID_ADMINS, array_map('strval', $fresh->groups));
	}
}
