<?php

namespace go\modules\legacy\email;

use GO\Email\Controller\FolderController;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/fixtures/SharedAccountTrait.php';

/**
 * Sharing folders of an e-mail account on the IMAP server (IMAP ACL) requires manage permission on the account.
 */
class FolderAclPermissionTest extends TestCase
{
	use SharedAccountTrait;

	protected function setUp(): void
	{
		// "Use account"
		$this->setUpSharedAccount(\go\core\model\Acl::LEVEL_CREATE);
	}

	protected function tearDown(): void
	{
		$this->tearDownSharedAccount();
	}

	public static function actions(): array
	{
		return [
			'setAcl' => ['actionSetAcl', ['mailbox' => 'INBOX', 'identifier' => 'someone', 'read' => 1, 'admin' => 1]],
			'aclStore' => ['actionAclStore', ['mailbox' => 'INBOX', 'delete_keys' => '["someone"]']],
		];
	}

	/**
	 * @dataProvider actions
	 */
	public function testUserWithoutManagePermissionCannotShareFolders(string $action, array $params): void
	{
		$this->actAs($this->other->id);
		$params['account_id'] = $this->account->id;
		$this->assertDenied(new FolderController(), $action, $params);
	}

	/**
	 * @dataProvider actions
	 */
	public function testOwnerCanShareFolders(string $action, array $params): void
	{
		$this->actAs($this->owner->id);
		$params['account_id'] = $this->account->id;
		$this->assertAllowed(new FolderController(), $action, $params);
	}
}
