<?php

namespace go\modules\legacy\email;

use GO\Email\Controller\MessageController;
use GO\Email\Model\Account;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/fixtures/SharedAccountTrait.php';

/**
 * Sending or saving a draft as an alias of a shared e-mail account requires "Use account" on that account. Users who may
 * only read it, also "delegated" users, can't send as the account.
 */
class SendPermissionTest extends TestCase
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

	private function messageParams(): array
	{
		return [
			'alias_id' => $this->account->getDefaultAlias()->id,
			'to' => 'someone@example.com',
			'cc' => '',
			'bcc' => '',
			'subject' => 'Sent as the owner',
			'content_type' => 'plain',
			'plainbody' => 'Hello',
			'htmlbody' => ''
		];
	}

	public static function actions(): array
	{
		return [
			'send' => ['actionSend'],
			'save draft' => ['actionSave']
		];
	}

	/**
	 * @dataProvider actions
	 */
	public function testDelegatedUserCannotSendAsSharedAccount(string $action): void
	{
		$this->actAs($this->other->id);
		$this->assertDenied(new MessageController(), $action, $this->messageParams());
	}

	/**
	 * @dataProvider actions
	 */
	public function testUserWithUseAccountCanSend(string $action): void
	{
		$acl = \go\core\model\Acl::findById($this->account->acl_id);
		$acl->addGroup($this->other->getPersonalGroup()->id, \go\core\model\Acl::LEVEL_CREATE);
		$this->assertTrue($acl->save());

		$this->actAs($this->other->id);
		$this->assertAllowed(new MessageController(), $action, $this->messageParams());
	}

	public function testDelegatedUserCannotSendNotificationAsSharedAccount(): void
	{
		$this->actAs($this->other->id);
		$this->assertDenied(new MessageController(), 'actionNotification', [
			'account_id' => $this->account->id,
			'message_to' => $this->owner->email,
			'notification_to' => 'someone@example.com',
			'subject' => 'Any subject'
		]);
	}
}
