<?php
namespace go\modules\legacy\billing;

use GO\Base\Exception\AccessDenied;
use GO\Base\Exception\NotFound;
use GO\Base\Model\Acl;
use GO\Billing\Controller\OrderController;
use GO\Billing\Controller\SyncContractsController;
use go\core\auth\TemporaryState;
use go\core\model\User;
use go\modules\business\ModuleTablesTrait;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../business/fixtures/ModuleTablesTrait.php';

/**
 * The contract sync and the batch status change find orders without the ACL, so they check the permission on the book.
 */
class BookPermissionTest extends TestCase
{
	use ModuleTablesTrait;

	private $authState;
	private bool $ignoreAcl;
	private bool $disableModelCache;
	private User $user;
	private Acl $acl;
	private int $bookId;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->ignoreAcl = \GO::$ignoreAclPermissions;
		$this->disableModelCache = \GO::$disableModelCache;
		// Models remember the permission level of the user they were first used by. The levels change in these tests.
		\GO::$disableModelCache = true;

		$this->createTables(go()->getEnvironment()->getInstallPath() . '/promodules/billing/install/install.sql');

		$name = uniqid('bookuser');
		$this->user = new User();
		$this->user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($this->user->save(), $this->user->getValidationErrorsAsString());

		$this->actAs(User::ID_SUPER_ADMIN);
		\GO::$ignoreAclPermissions = true;
		$this->acl = new Acl();
		$this->acl->usedIn = 'test';
		$this->acl->ownedBy = User::ID_SUPER_ADMIN;
		$this->acl->save();
		\GO::$ignoreAclPermissions = false;

		go()->getDbConnection()->insert('bs_books', [
			'user_id' => User::ID_SUPER_ADMIN,
			'name' => 'Book permission test',
			'acl_id' => $this->acl->id
		])->execute();
		$this->bookId = (int) go()->getDbConnection()->getPDO()->lastInsertId();
	}

	protected function tearDown(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		\GO::$ignoreAclPermissions = true;
		go()->getDbConnection()->delete('bs_books', ['id' => $this->bookId])->execute();
		$this->acl->delete();
		User::delete(['id' => $this->user->id]);
		$this->uninstallModuleTables();

		\GO::$ignoreAclPermissions = $this->ignoreAcl;
		\GO::$disableModelCache = $this->disableModelCache;
		go()->setAuthState($this->authState);
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
		\GO::session()->setCurrentUser($userId);
	}

	private function grant(int $level): void
	{
		$this->acl->addGroup($this->user->getPersonalGroup()->id, $level);
		$this->actAs($this->user->id);
	}

	private function syncContracts($bookId): void
	{
		$_REQUEST['book_id'] = $bookId;
		try {
			// The constructor sends a header
			$controller = (new \ReflectionClass(SyncContractsController::class))->newInstanceWithoutConstructor();
			(new \ReflectionMethod(SyncContractsController::class, 'getStatFindParams'))->invoke($controller);
		} finally {
			unset($_REQUEST['book_id']);
		}
	}

	private function batchStatus($bookId): void
	{
		(new \ReflectionMethod(OrderController::class, 'actionBatchStatus'))->invoke(new OrderController(), [
			'book_id' => $bookId,
			'from_status_id' => 1,
			'to_status_id' => 0,
			'start' => 1
		]);
	}

	public function testSyncContractsNeedsReadPermission(): void
	{
		$this->actAs($this->user->id);
		$this->expectException(AccessDenied::class);
		$this->syncContracts($this->bookId);
	}

	public function testSyncContractsWithReadPermission(): void
	{
		$this->grant(Acl::READ_PERMISSION);
		$this->syncContracts($this->bookId);
		$this->assertTrue(true);
	}

	public function testSyncContractsRefusesListOfBooks(): void
	{
		$this->grant(Acl::READ_PERMISSION);
		$this->expectException(NotFound::class);
		$this->syncContracts([$this->bookId]);
	}

	public function testBatchStatusNeedsWritePermission(): void
	{
		$this->grant(Acl::READ_PERMISSION);
		$this->expectException(AccessDenied::class);
		$this->batchStatus($this->bookId);
	}

	public function testBatchStatusWithWritePermission(): void
	{
		$this->grant(Acl::WRITE_PERMISSION);
		try {
			$this->batchStatus($this->bookId);
		} catch (AccessDenied $e) {
			$this->fail('Write permission on the book was refused');
		} catch (\Exception $e) {
			// The book has no orders
			$this->assertEquals(\GO::t("There are no orders to process", "billing"), $e->getMessage());
		}
	}
}
