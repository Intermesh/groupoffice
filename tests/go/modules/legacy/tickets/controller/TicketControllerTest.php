<?php
namespace go\modules\legacy\tickets\controller;

use GO\Base\Exception\AccessDenied;
use GO\Base\Model\Acl;
use GO\Tickets\Controller\TicketController;
use go\core\auth\TemporaryState;
use go\core\model\User;
use go\modules\business\ModuleTablesTrait;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../business/fixtures/ModuleTablesTrait.php';

/**
 * The agents list shows the users with manage permission on an ACL. It may only be used for the ACL of a ticket type
 * the user can read.
 */
class TicketControllerTest extends TestCase
{
	use ModuleTablesTrait;

	private $authState;
	private bool $ignoreAcl;
	private bool $disableModelCache;
	private User $user;
	private User $agent;
	private Acl $typeAcl;
	private Acl $otherAcl;
	private int $typeId;

	protected function setUp(): void
	{
		$this->authState = go()->getAuthState();
		$this->ignoreAcl = \GO::$ignoreAclPermissions;
		$this->disableModelCache = \GO::$disableModelCache;
		// Models remember the permission level of the user they were first used by. The levels change in these tests.
		\GO::$disableModelCache = true;

		$this->createTables(go()->getEnvironment()->getInstallPath() . '/promodules/tickets/install/install.sql', ['ti_types']);

		$this->user = $this->createUser();
		$this->agent = $this->createUser();

		$this->actAs(User::ID_SUPER_ADMIN);
		\GO::$ignoreAclPermissions = true;
		$this->typeAcl = $this->createAcl();
		$this->typeAcl->addGroup($this->agent->getPersonalGroup()->id, Acl::MANAGE_PERMISSION);
		// An ACL of something else, for example a private calendar of the agent
		$this->otherAcl = $this->createAcl();
		$this->otherAcl->addGroup($this->agent->getPersonalGroup()->id, Acl::MANAGE_PERMISSION);
		\GO::$ignoreAclPermissions = false;

		go()->getDbConnection()->insert('ti_types', [
			'name' => 'Agents test',
			'user_id' => User::ID_SUPER_ADMIN,
			'acl_id' => $this->typeAcl->id
		])->execute();
		$this->typeId = (int) go()->getDbConnection()->getPDO()->lastInsertId();
	}

	protected function tearDown(): void
	{
		$this->actAs(User::ID_SUPER_ADMIN);
		\GO::$ignoreAclPermissions = true;
		go()->getDbConnection()->delete('ti_types', ['id' => $this->typeId])->execute();
		$this->typeAcl->delete();
		$this->otherAcl->delete();
		User::delete(['id' => [$this->user->id, $this->agent->id]]);
		$this->uninstallModuleTables();

		\GO::$ignoreAclPermissions = $this->ignoreAcl;
		\GO::$disableModelCache = $this->disableModelCache;
		go()->setAuthState($this->authState);
	}

	private function createUser(): User
	{
		$name = uniqid('agentuser');
		$user = new User();
		$user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($user->save(), $user->getValidationErrorsAsString());
		return $user;
	}

	private function createAcl(): Acl
	{
		$acl = new Acl();
		$acl->usedIn = 'test';
		$acl->ownedBy = User::ID_SUPER_ADMIN;
		$acl->save();
		return $acl;
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
		\GO::session()->setCurrentUser($userId);
	}

	private function agents($aclId): array
	{
		$method = new \ReflectionMethod(TicketController::class, 'actionAgents');
		$response = $method->invoke(new TicketController(), ['acl_id' => $aclId]);
		return array_map('intval', array_column($response['results'], 'id'));
	}

	public function testAgentsOfReadableType(): void
	{
		$this->typeAcl->addGroup($this->user->getPersonalGroup()->id, Acl::READ_PERMISSION);
		$this->actAs($this->user->id);
		$this->assertContains((int) $this->agent->id, $this->agents($this->typeAcl->id));
	}

	public function testAgentsOfUnreadableTypeAreRefused(): void
	{
		$this->actAs($this->user->id);
		$this->expectException(AccessDenied::class);
		$this->agents($this->typeAcl->id);
	}

	public function testAgentsOfOtherAclAreRefused(): void
	{
		$this->actAs($this->user->id);
		$this->expectException(AccessDenied::class);
		$this->agents($this->otherAcl->id);
	}

	public function testNoTypeHasNoAgents(): void
	{
		$this->actAs($this->user->id);
		$this->assertEquals([], $this->agents(''));
	}
}
