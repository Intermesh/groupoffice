<?php

namespace go\core;

use go\core\auth\TemporaryState;
use go\core\model\User;
use PHPUnit\Framework\TestCase;

class InstallerTest extends TestCase
{
	private $authState;
	private $databaseVersion;
	private array $userIds = [];

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->databaseVersion = go()->getSettings()->databaseVersion;
	}

	protected function tearDown(): void
	{
		// Only changed in memory, never saved
		go()->getSettings()->databaseVersion = $this->databaseVersion;
		go()->setAuthState($this->authState);

		foreach ($this->userIds as $id) {
			User::delete(['id' => $id]);
		}
	}

	private function newUser(): User
	{
		$name = uniqid('inst');
		$user = new User();
		$user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($user->save(), var_export($user->getValidationErrors(), true));
		$this->userIds[] = $user->id;

		return $user;
	}

	private function setUpToDate(bool $upToDate): void
	{
		go()->getSettings()->databaseVersion = $upToDate ? go()->getVersion() : '0.0.1';
	}

	public function testUpToDateUpgradeIsDeniedForAnonymous(): void
	{
		$this->setUpToDate(true);
		go()->setAuthState(new TemporaryState());
		$this->assertFalse(go()->getInstaller()->isUpgradeAllowed());
	}

	public function testUpToDateUpgradeIsDeniedForNonAdmin(): void
	{
		$this->setUpToDate(true);
		go()->setAuthState((new TemporaryState())->setUser($this->newUser()));
		$this->assertFalse(go()->getInstaller()->isUpgradeAllowed());
	}

	public function testUpToDateUpgradeIsAllowedForAdmin(): void
	{
		$this->setUpToDate(true);
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		$this->assertTrue(go()->getInstaller()->isUpgradeAllowed());
	}

	public function testPendingUpgradeIsAllowedForAnonymous(): void
	{
		$this->setUpToDate(false);
		go()->setAuthState(new TemporaryState());
		$this->assertTrue(go()->getInstaller()->isUpgradeAllowed());
	}
}
