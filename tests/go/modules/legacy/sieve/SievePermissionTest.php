<?php

namespace go\modules\legacy\sieve;

use GO\Sieve\Controller\SieveController;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../email/fixtures/SharedAccountTrait.php';

/**
 * E-mail filters (Sieve) and the out of office reply of an account may only be read and changed by users with manage
 * permission on the account. Users who may use a shared account can't redirect its mail.
 */
class SievePermissionTest extends TestCase
{
	use \go\modules\legacy\email\SharedAccountTrait;

	protected function setUp(): void
	{
		if(!\GO::modules()->isInstalled('sieve')) {
			$this->markTestSkipped('The sieve module is required');
		}
		// "Use account"
		$this->setUpSharedAccount(\go\core\model\Acl::LEVEL_CREATE);
	}

	protected function tearDown(): void
	{
		$this->tearDownSharedAccount();
	}

	public static function controllers(): array
	{
		$controllers = [
			'sieve' => [SieveController::class]
		];
		if(class_exists(\GO\SieveEditor\Controller\FiltersetController::class)) {
			$controllers['sieveeditor'] = [\GO\SieveEditor\Controller\FiltersetController::class];
		}
		return $controllers;
	}

	/**
	 * @dataProvider controllers
	 */
	public function testUserWithoutManagePermissionCannotConnect(string $controller): void
	{
		$this->actAs($this->other->id);
		$this->assertDenied(new $controller, '_sieveConnect', $this->account->id);
	}

	/**
	 * @dataProvider controllers
	 */
	public function testOwnerCanConnect(string $controller): void
	{
		$this->actAs($this->owner->id);
		$this->assertAllowed(new $controller, '_sieveConnect', $this->account->id);
	}

	public function testUserWithoutManagePermissionCannotListRules(): void
	{
		$this->actAs($this->other->id);
		$this->assertDenied(new SieveController(), 'actionRules', ['account_id' => $this->account->id, 'script_name' => 'default', 'delete_keys' => '[0]']);
	}
}
