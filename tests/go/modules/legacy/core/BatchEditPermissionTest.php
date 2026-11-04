<?php
namespace go\modules\legacy\core;

use go\core\auth\TemporaryState;
use go\core\model\User;
use GO\Base\Exception\AccessDenied;
use GO\Base\Model\Acl;
use GO\Base\Model\AclUsersGroups;
use GO\Core\Controller\BatchEditController;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Models without an ACL pass every permission check in ActiveRecord, so batch edit may only change them for admins.
 */
class BatchEditPermissionTest extends TestCase
{
	private $authState;
	private bool $ignoreAcl;
	private bool $disableModelCache;

	private User $user;
	private Acl $acl;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->ignoreAcl = \GO::$ignoreAclPermissions;
		$this->disableModelCache = \GO::$disableModelCache;

		// Models remember the permission level of the user they were first used by. The users change in this test.
		\GO::$disableModelCache = true;

		$name = uniqid('batchedit');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), var_export($this->user->getValidationErrors(), true));

		$this->actAs(User::findById(User::ID_SUPER_ADMIN));
		\GO::$ignoreAclPermissions = true;

		$this->acl = new Acl();
		$this->acl->usedIn = 'test';
		$this->acl->ownedBy = User::ID_SUPER_ADMIN;
		$this->acl->save();
		$this->acl->addGroup($this->user->getPersonalGroup()->id, Acl::READ_PERMISSION);

		\GO::$ignoreAclPermissions = false;
	}

	protected function tearDown(): void
	{
		go()->setAuthState($this->authState);
		\GO::session()->setCurrentUser(User::ID_SUPER_ADMIN);
		\GO::$ignoreAclPermissions = true;

		$this->acl->delete();
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
	 * Give the user manage permission on the ACL with batch edit
	 *
	 * @throws AccessDenied
	 */
	private function raiseLevel(): void
	{
		$method = new ReflectionMethod(BatchEditController::class, 'actionSubmit');
		$method->invoke(new BatchEditController(), [
			'model_name' => AclUsersGroups::class,
			'keys' => json_encode([['aclId' => $this->acl->id, 'groupId' => $this->user->getPersonalGroup()->id]]),
			'data' => json_encode([['name' => 'level', 'value' => Acl::MANAGE_PERMISSION, 'mergeable' => false, 'replace' => true]])
		]);
	}

	private function getLevel(): int
	{
		return (int) go()->getDbConnection()
			->selectSingleValue('level')
			->from('core_acl_group')
			->where(['aclId' => $this->acl->id, 'groupId' => $this->user->getPersonalGroup()->id])
			->single();
	}

	public function testUserCannotEditModelWithoutAcl()
	{
		$this->actAs($this->user);

		try {
			$this->raiseLevel();
			$this->fail('A user changed a model without an ACL');
		} catch (AccessDenied $e) {
			$this->assertTrue(true);
		}

		$this->assertEquals(Acl::READ_PERMISSION, $this->getLevel());
	}

	public function testAdminCanEditModelWithoutAcl()
	{
		$this->actAs(User::findById(User::ID_SUPER_ADMIN));

		$this->raiseLevel();

		$this->assertEquals(Acl::MANAGE_PERMISSION, $this->getLevel());
	}

	public function testClassThatIsNotAModelIsRefused()
	{
		$this->actAs($this->user);

		$method = new ReflectionMethod(BatchEditController::class, 'actionAttributesStore');

		$this->expectException(AccessDenied::class);
		$method->invoke(new BatchEditController(), ['model_name' => \ArrayObject::class]);
	}
}
