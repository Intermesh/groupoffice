<?php

namespace go\modules\legacy\files;

use go\core\auth\TemporaryState;
use go\core\model\User;
use GO\Base\Exception\NotFound;
use GO\Files\Controller\FolderController;
use PHPUnit\Framework\TestCase;

class FolderListTest extends TestCase
{
	/**
	 * Listing a folder that doesn't exist must give a "not found" error instead of a PHP error about reading a
	 * property of false (#38403)
	 */
	public function testListMissingFolderThrowsNotFound(): void
	{
		$authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		\GO::session()->setCurrentUser(User::ID_SUPER_ADMIN);

		try {
			$controller = new FolderController();
			$method = new \ReflectionMethod($controller, 'actionList');
			$method->setAccessible(true);

			$this->expectException(NotFound::class);
			$method->invoke($controller, ['folder_id' => 2147483647]);
		} finally {
			go()->setAuthState($authState);
		}
	}
}
