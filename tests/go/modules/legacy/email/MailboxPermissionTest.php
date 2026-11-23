<?php

namespace go\modules\legacy\email;

use GO\Email\Controller\AccountController;
use GO\Email\Controller\FolderController;
use GO\Email\Controller\MessageController;
use GO\Email\Model\Account;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/fixtures/SharedAccountTrait.php';

/**
 * Users who may only read a shared e-mail account (or read and mark as read: "delegated") may not move, delete or
 * change its mail and folders. That requires "Use account".
 */
class MailboxPermissionTest extends TestCase
{
	use SharedAccountTrait;

	protected function setUp(): void
	{
		$this->setUpSharedAccount(Account::ACL_DELEGATED_PERMISSION);
	}

	protected function tearDown(): void
	{
		$this->tearDownSharedAccount();
	}

	public static function changingActions(): array
	{
		return [
			'moveOld' => [MessageController::class, 'actionMoveOld', ['mailbox' => 'INBOX', 'target_mailbox' => 'Trash', 'until_date' => '01-01-2030']],
			'moveToSpam' => [MessageController::class, 'actionMoveToSpam', ['from_mailbox_name' => 'INBOX', 'mail_uid' => '1']],
			'moveToInbox' => [MessageController::class, 'actionMoveToInbox', ['mail_uid' => '1']],
			'deleteAllAttachments' => [MessageController::class, 'actionDeleteAllAttachments', ['mailbox' => 'INBOX', 'uid' => '1']],
			'truncate' => [FolderController::class, 'actionTruncate', ['mailbox' => 'INBOX']],
			'subscribe' => [FolderController::class, 'actionSubscribe', ['mailboxs' => '"INBOX/sub"']],
			'unsubscribe' => [FolderController::class, 'actionUnsubscribe', ['mailboxs' => '"INBOX/sub"']],
			'createFolder' => [FolderController::class, 'actionCreate', ['parent' => 'INBOX', 'name' => 'new']],
			'renameFolder' => [FolderController::class, 'actionRename', ['mailbox' => 'INBOX/sub', 'name' => 'new']],
		];
	}

	/**
	 * @dataProvider changingActions
	 */
	public function testDelegatedUserCannotChangeMail(string $controller, string $action, array $params): void
	{
		$this->actAs($this->other->id);
		$params['account_id'] = $this->account->id;
		$this->assertDenied(new $controller, $action, $params);
	}

	/**
	 * @dataProvider changingActions
	 */
	public function testOwnerCanChangeMail(string $controller, string $action, array $params): void
	{
		$this->actAs($this->owner->id);
		$params['account_id'] = $this->account->id;
		$this->assertAllowed(new $controller, $action, $params);
	}

	public function testReadOnlyUserCannotMarkFolderAsRead(): void
	{
		$acl = \go\core\model\Acl::findById($this->account->acl_id);
		$acl->addGroup($this->other->getPersonalGroup()->id, \go\core\model\Acl::LEVEL_READ);
		$this->assertTrue($acl->save());

		$this->actAs($this->other->id);
		$this->assertDenied(new FolderController(), 'actionMarkAsRead', ['account_id' => $this->account->id, 'mailbox' => 'INBOX']);
	}

	public function testDelegatedUserCanMarkFolderAsRead(): void
	{
		$this->actAs($this->other->id);
		$this->assertAllowed(new FolderController(), 'actionMarkAsRead', ['account_id' => $this->account->id, 'mailbox' => 'INBOX']);
	}

	public function testDelegatedUserCannotMoveMailOutOfSharedAccount(): void
	{
		// the user's own account is the target
		$this->actAs(\go\core\model\User::ID_SUPER_ADMIN);
		$own = new Account();
		$own->user_id = $this->other->id;
		$own->host = '127.0.0.1';
		$own->port = 1;
		$own->imap_encryption = '';
		$own->smtp_encryption = '';
		$own->smtp_port = 1;
		$own->username = 'other';
		$own->password = 'secret';
		$own->smtp_host = '127.0.0.1';
		$own->checkImapConnectionOnSave = false;
		$this->assertTrue($own->save(true));
		$acl = \go\core\model\Acl::findById($own->acl_id);
		$acl->addGroup($this->other->getPersonalGroup()->id, \go\core\model\Acl::LEVEL_MANAGE);
		$this->assertTrue($acl->save());

		try {
			$this->actAs($this->other->id);
			$params = [
				'srcMessages' => json_encode([['accountId' => $this->account->id, 'mailboxPath' => 'INBOX', 'mailUid' => 1, 'seen' => true]]),
				'targetAccountId' => $own->id,
				'targetMailboxPath' => 'INBOX',
				'move' => 1
			];
			$this->assertDenied(new AccountController(), 'actionCopyMailTo', $params);

			// copying is allowed
			unset($params['move']);
			$this->assertAllowed(new AccountController(), 'actionCopyMailTo', $params);
		} finally {
			$this->actAs(\go\core\model\User::ID_SUPER_ADMIN);
			\GO::$ignoreAclPermissions = true;
			$own->delete(true);
			\GO::$ignoreAclPermissions = false;
		}
	}
}
