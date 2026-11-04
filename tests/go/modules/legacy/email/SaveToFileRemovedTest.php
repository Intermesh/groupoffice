<?php
namespace go\modules\legacy\email;

use go\core\auth\TemporaryState;
use go\core\model\User;
use GO\Base\Exception\NotFound;
use GO\Email\Controller\MessageController;
use PHPUnit\Framework\TestCase;

/**
 * email/message/saveToFile wrote a composed message to any path in the file storage that the client sent,
 * without checking permissions. Nothing used it, so it was removed.
 */
class SaveToFileRemovedTest extends TestCase
{
	private $authState;
	private string $target;

	protected function setUp(): void
	{
		if (!\GO::modules()->isInstalled('email')) {
			$this->markTestSkipped('The email module is not installed');
		}

		$this->authState = go()->getAuthState();
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		\GO::session()->setCurrentUser(User::ID_SUPER_ADMIN);

		$this->target = 'save-to-file-' . uniqid() . '.eml';
	}

	protected function tearDown(): void
	{
		if (!isset($this->target)) {
			return;
		}

		$file = new \GO\Base\Fs\File(\GO::config()->file_storage_path . $this->target);
		if ($file->exists()) {
			$file->delete();
		}

		go()->setAuthState($this->authState);
	}

	public function testSaveToFileActionDoesNotExist()
	{
		$controller = new MessageController();

		// run() only dispatches to action methods that exist. Check that first, because running the old action
		// renders its error and ends the process.
		$this->assertFalse(method_exists($controller, 'actionSaveToFile'), 'email/message/saveToFile can still be called');

		ob_start();
		try {
			$controller->run('saveToFile', [
				'save_to_path' => $this->target,
				'alias_id' => 1,
				'subject' => 'test',
				'htmlbody' => 'test',
				'content_type' => 'html'
			], false);
			$this->fail('email/message/saveToFile can still be called');
		} catch (NotFound $e) {
			$this->assertTrue(true);
		} finally {
			ob_end_clean();
		}

		$this->assertFileDoesNotExist(\GO::config()->file_storage_path . $this->target);
	}
}
