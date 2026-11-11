<?php

namespace go\modules\legacy\email;

use go\core\auth\TemporaryState;
use go\core\model\User;
use GO\Base\Exception\AccessDenied;
use GO\Email\Model\Account;

/**
 * An e-mail account of an owner that is shared with another user. The account points to a closed port, so actions that
 * get past their permission check fail when they connect to the IMAP or SMTP server.
 */
trait SharedAccountTrait
{
	private $authState;
	private bool $ignoreAcl;
	private bool $disableModelCache;
	private User $owner;
	private User $other;
	private Account $account;

	protected function setUpSharedAccount(int $otherLevel): void
	{
		if(!\GO::modules()->isInstalled('email')) {
			$this->markTestSkipped('The email module is required');
		}

		$this->authState = go()->getAuthState();
		$this->ignoreAcl = \GO::$ignoreAclPermissions;
		$this->disableModelCache = \GO::$disableModelCache;

		// Models remember the permission level of the user they were first used by. The users change in these tests.
		\GO::$disableModelCache = true;

		$this->owner = $this->createUser('emowner');
		$this->other = $this->createUser('emother');

		$this->actAs(User::ID_SUPER_ADMIN);
		$account = new Account();
		$account->user_id = $this->owner->id;
		$account->host = '127.0.0.1';
		$account->port = 1;
		$account->imap_encryption = '';
		$account->smtp_encryption = '';
		$account->username = 'owner';
		$account->password = 'secret';
		$account->smtp_host = '127.0.0.1';
		$account->smtp_port = 1;
		$account->checkImapConnectionOnSave = false;
		$this->assertTrue($account->save(true), var_export($account->getValidationErrors(), true));
		$account->addAlias($this->owner->email, 'Owner');
		$this->account = $account;

		$acl = \go\core\model\Acl::findById($account->acl_id);
		foreach($acl->groups as $group) {
			if($group->groupId != $this->owner->getPersonalGroup()->id) {
				$acl->removeGroup($group->groupId);
			}
		}
		$acl->addGroup($this->owner->getPersonalGroup()->id, \go\core\model\Acl::LEVEL_MANAGE);
		$acl->addGroup($this->other->getPersonalGroup()->id, $otherLevel);
		$this->assertTrue($acl->save());

		$this->actAs($this->other->id);
		$this->assertSame($otherLevel, Account::model()->findByPk($account->id)->getPermissionLevel());
	}

	protected function tearDownSharedAccount(): void
	{
		go()->setAuthState($this->authState);
		\GO::session()->setCurrentUser(User::ID_SUPER_ADMIN);
		\GO::$ignoreAclPermissions = true;

		if(isset($this->account)) {
			$this->account->delete(true);
		}
		if(isset($this->owner)) {
			User::delete(['id' => [$this->owner->id, $this->other->id]]);
		}

		\GO::$ignoreAclPermissions = $this->ignoreAcl;
		\GO::$disableModelCache = $this->disableModelCache;

		// Saving the first account registers its entity type, which rebuilds the entity type cache. Entities keep their
		// old entity type with a stale modseq, so later tests would get a JMAP state that doesn't change.
		\go\core\orm\Entity::clearCache();
	}

	private function createUser(string $prefix): User
	{
		$name = uniqid($prefix);
		$user = new User();
		$user->setValues([
			'username' => $name,
			'displayName' => $name,
			'email' => $name . '@example.com',
			'password' => 'Pa55word!!xyz'
		]);
		$this->assertTrue($user->save(), var_export($user->getValidationErrors(), true));
		return $user;
	}

	private function actAs(int $userId): void
	{
		go()->setAuthState(new TemporaryState($userId));
		\GO::session()->setCurrentUser($userId);
	}

	/**
	 * Run a protected controller action
	 */
	private function invoke(object $controller, string $action, array $args)
	{
		$method = new \ReflectionMethod($controller, $action);
		$method->setAccessible(true);
		ob_start();
		try {
			return $method->invokeArgs($controller, $args);
		} finally {
			ob_end_clean();
		}
	}

	/**
	 * Assert that the action is refused before it does anything
	 */
	private function assertDenied(object $controller, string $action, $params): void
	{
		try {
			$this->invoke($controller, $action, [$params]);
			$this->fail($action . ' was allowed');
		} catch (AccessDenied $e) {
			$this->assertTrue(true);
		} catch (\Throwable $e) {
			$this->fail($action . ' got past the permission check: ' . get_class($e) . ': ' . $e->getMessage());
		}
	}

	/**
	 * Assert that the action gets past the permission check. It fails later because the server can't be reached.
	 */
	private function assertAllowed(object $controller, string $action, $params): void
	{
		try {
			$this->invoke($controller, $action, [$params]);
		} catch (AccessDenied $e) {
			$this->fail($action . ' was denied');
		} catch (\Throwable $e) {
			// connection error
		}
		$this->assertTrue(true);
	}
}
