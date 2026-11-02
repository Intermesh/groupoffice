<?php
namespace go\modules\legacy\core;

use go\core\auth\TemporaryState;
use go\core\model\User;
use GO\Base\Controller\AbstractController;
use GO\Base\Exception\AccessDenied;
use GO\Base\Model\SavedExport;
use GO\Base\Model\Template;
use GO\Core\Controller\AdvancedSearchController;
use GO\Core\Controller\CoreController;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Legacy controllers that take the model class from the request may only use models without an ACL for admins,
 * because those models pass every permission check in ActiveRecord.
 */
class RequestedModelPermissionTest extends TestCase
{
	private $authState;
	private bool $ignoreAcl;
	private bool $disableModelCache;

	private User $user;

	/** @var SavedExport[] */
	private array $exports = [];

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->ignoreAcl = \GO::$ignoreAclPermissions;
		$this->disableModelCache = \GO::$disableModelCache;

		// Models remember the permission level of the user they were first used by. The users change in this test.
		\GO::$disableModelCache = true;

		$name = uniqid('requestedmodel');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), var_export($this->user->getValidationErrors(), true));

		$this->actAs(User::findById(User::ID_SUPER_ADMIN));

		// a model without an ACL
		for ($i = 0; $i < 2; $i++) {
			$export = new SavedExport();
			$export->name = 'test ' . $i;
			$export->class_name = 'test';
			$export->view = 'Csv';
			$export->export_columns = 'id';
			$this->assertTrue($export->save(true));
			$this->exports[] = $export;
		}
	}

	protected function tearDown(): void
	{
		go()->setAuthState($this->authState);
		\GO::session()->setCurrentUser(User::ID_SUPER_ADMIN);

		foreach ($this->exports as $export) {
			if (SavedExport::model()->findByPk($export->id, false, true)) {
				$export->delete(true);
			}
		}
		User::delete(['id' => $this->user->id]);

		\GO::$ignoreAclPermissions = $this->ignoreAcl;
		\GO::$disableModelCache = $this->disableModelCache;
	}

	private function actAs(User $user): void
	{
		go()->setAuthState(new TemporaryState($user->id));
		\GO::session()->setCurrentUser($user->id);
	}

	/**
	 * Merge the second export into the first and delete it
	 *
	 * @throws AccessDenied
	 */
	private function merge(): void
	{
		$method = new ReflectionMethod(AdvancedSearchController::class, 'actionMerge');
		$method->invoke(new AdvancedSearchController(), [
			'model_name' => SavedExport::class,
			'target_model_id' => $this->exports[0]->id,
			'merge_models' => json_encode([$this->exports[1]->id]),
			'delete_merge_models' => 1
		]);
	}

	private function exportExists(SavedExport $export): bool
	{
		return (bool) SavedExport::model()->findByPk($export->id, false, true, true);
	}

	public function testUserCannotMergeModelWithoutAcl()
	{
		$this->actAs($this->user);

		try {
			$this->merge();
			$this->fail('A user merged and deleted a model without an ACL');
		} catch (AccessDenied $e) {
			$this->assertTrue(true);
		}

		$this->assertTrue($this->exportExists($this->exports[1]));
	}

	public function testAdminCanMergeModelWithoutAcl()
	{
		$this->actAs(User::findById(User::ID_SUPER_ADMIN));

		$this->merge();

		$this->assertFalse($this->exportExists($this->exports[1]));
	}

	public function testUserCannotUnlinkModelWithoutAcl()
	{
		$this->actAs($this->user);

		$method = new ReflectionMethod(CoreController::class, 'actionUnlink');

		$this->expectException(AccessDenied::class);
		$method->invoke(new CoreController(), [
			'model_name1' => SavedExport::class,
			'id1' => $this->exports[0]->id,
			'model_name2' => SavedExport::class,
			'id2' => $this->exports[1]->id
		]);
	}

	public function testUserCanUseModelWithAcl()
	{
		$this->actAs($this->user);

		$method = new ReflectionMethod(AbstractController::class, 'getRequestedModel');

		$this->assertInstanceOf(Template::class, $method->invoke(new CoreController(), Template::class));
	}

	public function testClassThatIsNotAModelIsRefused()
	{
		$this->actAs($this->user);

		$method = new ReflectionMethod(AbstractController::class, 'getRequestedModel');

		$this->expectException(AccessDenied::class);
		$method->invoke(new CoreController(), \ArrayObject::class);
	}
}
