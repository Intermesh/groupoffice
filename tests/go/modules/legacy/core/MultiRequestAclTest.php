<?php

namespace go\modules\legacy\core;

use GO\Core\Controller\CoreController;
use GO\Files\Controller\AclLeakTestController;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/fixtures/AclLeakTestController.php';

/**
 * core/multiRequest runs several requests in one. A request must not run with the permissions that a previous one left
 * behind.
 */
class MultiRequestAclTest extends TestCase
{
	private bool $ignoreAcl;

	protected function setUp(): void
	{
		$this->ignoreAcl = \GO::$ignoreAclPermissions;
		\GO::$ignoreAclPermissions = false;
		AclLeakTestController::$ignoredInNextRequest = null;
	}

	protected function tearDown(): void
	{
		\GO::$ignoreAclPermissions = $this->ignoreAcl;
	}

	public function testPermissionsAreRestoredBetweenRequests(): void
	{
		$controller = new CoreController();
		$method = new \ReflectionMethod($controller, 'actionMultiRequest');
		$method->setAccessible(true);

		ob_start();
		try {
			$method->invoke($controller, [
				'requests' => json_encode([
					'leak' => ['r' => 'files/aclLeakTest/leak'],
					'record' => ['r' => 'files/aclLeakTest/record']
				])
			]);
		} finally {
			$output = ob_get_clean();
		}
		$ignoredAfterwards = \GO::$ignoreAclPermissions;

		$this->assertFalse(AclLeakTestController::$ignoredInNextRequest, $output);
		$this->assertFalse($ignoredAfterwards);
	}
}
