<?php
namespace go\modules\legacy\core;

use go\core\auth\TemporaryState;
use go\core\model\User;
use GO\Base\Controller\AbstractController;
use GO\Base\Exception\AccessDenied;
use PHPUnit\Framework\TestCase;

class AbstractControllerAclTestController extends AbstractController
{
	public bool $aclIgnoredInAction = false;

	protected function allowGuests()
	{
		return ['*'];
	}

	protected function ignoreAclPermissions()
	{
		return ['*'];
	}

	protected function actionSucceed($params)
	{
		$this->aclIgnoredInAction = \GO::$ignoreAclPermissions;
		return ['success' => true];
	}

	protected function actionFail($params)
	{
		$this->aclIgnoredInAction = \GO::$ignoreAclPermissions;
		throw new AccessDenied();
	}
}

/**
 * Controllers can switch ACL checks off for their actions with ignoreAclPermissions(). They must be switched on
 * again after the action, also when it fails, otherwise the rest of the request runs without permission checks.
 */
class AbstractControllerAclTest extends TestCase
{
	private $authState;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		\GO::session()->setCurrentUser(User::ID_SUPER_ADMIN);
		\GO::$ignoreAclPermissions = false;
	}

	protected function tearDown(): void
	{
		\GO::$ignoreAclPermissions = false;
		go()->setAuthState($this->authState);
	}

	private function runAction(string $action): AbstractControllerAclTestController
	{
		$controller = new AbstractControllerAclTestController();

		// The CLI error view prints $response['exception'] in debug mode, which isn't set for AccessDenied
		$debug = \GO::config()->debug;
		\GO::config()->debug = false;
		ob_start();
		try {
			$controller->run($action, [], false);
		} finally {
			ob_end_clean();
			\GO::config()->debug = $debug;
		}
		return $controller;
	}

	public function testAclIsRestoredAfterAction()
	{
		$controller = $this->runAction('succeed');

		$this->assertTrue($controller->aclIgnoredInAction);
		$this->assertFalse(\GO::$ignoreAclPermissions);
	}

	public function testAclIsRestoredWhenActionFails()
	{
		$controller = $this->runAction('fail');

		$this->assertTrue($controller->aclIgnoredInAction);
		$this->assertFalse(\GO::$ignoreAclPermissions);
	}
}
