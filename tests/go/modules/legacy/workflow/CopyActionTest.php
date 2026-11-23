<?php
namespace go\modules\legacy\workflow;

use GO\Workflow\Action\Copy;
use GO\Workflow\Action\HistoryPdfInCopy;
use GO\Workflow\Model\Model;
use GO\Workflow\Model\Step;
use GO\Workflow\Model\StepHistory;
use go\core\auth\TemporaryState;
use go\core\model\User;
use PHPUnit\Framework\TestCase;

/**
 * The copy actions ignore the ACL while copying the file. When that fails the ACL may not stay ignored for the rest of
 * the request.
 */
class CopyActionTest extends TestCase
{
	private $authState;
	private bool $ignoreAcl;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->ignoreAcl = \GO::$ignoreAclPermissions;
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
		\GO::session()->setCurrentUser(User::ID_SUPER_ADMIN);
		\GO::$ignoreAclPermissions = false;
	}

	protected function tearDown(): void
	{
		\GO::$ignoreAclPermissions = $this->ignoreAcl;
		go()->setAuthState($this->authState);
	}

	public static function actions(): array
	{
		return [[Copy::class], [HistoryPdfInCopy::class]];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('actions')]
	public function testAclIsNotIgnoredAfterAFailedCopy(string $actionClass): void
	{
		$home = \GO\Files\Model\Folder::model()->findHomeFolder(\GO\Base\Model\User::model()->findByPk(User::ID_SUPER_ADMIN));

		$step = $this->createStub(Step::class);
		$step->method('__get')->willReturnMap([
			['copy_to_folder', $home->path],
			['keep_original_copy', true],
		]);
		// fails while the ACL is ignored
		$step->method('isApproved')->willThrowException(new \Exception('Approval failed'));

		$history = $this->createStub(StepHistory::class);
		$history->method('__get')->willReturnMap([
			['step', $step],
			['process_model_id', 0],
		]);

		// an unsaved PDF file in the folder
		$file = new \GO\Files\Model\File();
		$file->folder_id = $home->id;
		$file->name = 'workflowtest.pdf';

		$workflowModel = $this->createStub(Model::class);
		$workflowModel->method('getRelatedModel')->willReturn($file);

		try {
			(new $actionClass())->run($history, $workflowModel, []);
			$this->fail('The copy action succeeded');
		} catch (\Exception $e) {
			$this->assertSame('Approval failed', $e->getMessage());
		}

		$this->assertFalse(\GO::$ignoreAclPermissions, 'The ACL is still ignored after the copy action failed');
	}
}
