<?php

namespace go\modules\legacy\email;

use go\core\event\Listeners;
use go\core\model\Acl;
use GO\Email\EmailModule;
use GO\Email\Mcp\Query;
use GO\Email\Model\Account;
use go\modules\business\mcp\model\McpKey;
use go\modules\business\mcp\model\McpKeyEntity;
use go\modules\business\mcp\Server;
use go\modules\business\mcp\tool\ToolError;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../fixtures/SharedAccountTrait.php';
require_once __DIR__ . '/../../../business/mcp/McpFixtureTrait.php';

/**
 * The e-mail tools of the MCP server only use e-mail accounts that are picked for the key and that the user may use
 *
 * The shared account points to a closed port, so a call that gets past its checks fails on the IMAP connection.
 */
class EmailToolsTest extends TestCase
{
	use SharedAccountTrait;
	use \go\modules\business\mcp\McpFixtureTrait;

	public static function setUpBeforeClass(): void
	{
		if (!class_exists(Server::class)) {
			self::markTestSkipped('The MCP module is required');
		}
		self::setUpMcp();

		// EmailModule only adds the e-mail tools with a license, which the tests don't have. Listeners added at runtime
		// last until the listeners are loaded again.
		Server::on(Server::EVENT_TOOL_PROVIDERS, EmailModule::class, 'onMcpToolProviders');
	}

	public static function tearDownAfterClass(): void
	{
		Listeners::get()->clear();
		if (class_exists(Server::class)) {
			self::tearDownMcp();
		}
	}

	protected function setUp(): void
	{
		$this->setUpSharedAccount(Acl::LEVEL_READ);
		$this->actAs($this->other->id);
	}

	protected function tearDown(): void
	{
		if (isset($this->authState)) {
			$this->tearDownSharedAccount();
		}
	}

	/**
	 * A key of the other user, not saved
	 */
	private function key(array $items): McpKey
	{
		$key = new McpKey();
		$key->userId = (string) $this->other->id;
		$key->name = 'test';
		$entities = [];
		foreach ($items as [$accountId, $mayWrite]) {
			$entity = new McpKeyEntity($key);
			$entity->entityTypeId = (string) Account::entityType()->getId();
			$entity->entityId = (string) $accountId;
			$entity->mayWrite = $mayWrite;
			$entities[] = $entity;
		}
		$key->entities = $entities;
		return $key;
	}

	private function toolNames(McpKey $key): array
	{
		$response = (new Server($key))->handle(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/list', 'params' => []], []);
		return array_column($response['body']['result']['tools'], 'name');
	}

	private function call(McpKey $key, string $tool, array $arguments): array
	{
		$response = (new Server($key))->handle(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/call', 'params' => ['name' => $tool, 'arguments' => $arguments]], []);
		return $response['body']['result'];
	}

	public function testToolsOnlyForPickedAccounts(): void
	{
		$names = $this->toolNames($this->key([]));
		$this->assertNotContains('email_query', $names);

		$names = $this->toolNames($this->key([[$this->account->id, false]]));
		$this->assertContains('email_mailboxes', $names);
		$this->assertContains('email_query', $names);
		$this->assertContains('email_get', $names);
		$this->assertContains('email_attachment', $names, 'reading attachments only needs read access');
		$this->assertNotContains('email_draft', $names, 'drafts need write access');

		$names = $this->toolNames($this->key([[$this->account->id, true]]));
		$this->assertContains('email_draft', $names);
		$this->assertContains('create_blob', $names, 'for attachments of drafts');
		$this->assertNotContains('jmap_write', $names, 'an e-mail account gives no write access to JMAP entities');
	}

	public function testAccountIsNoJmapEntity(): void
	{
		$key = $this->key([[$this->account->id, true]]);
		$this->assertNull($key->findEntity('Account'));
		$this->assertNotContains((string) Account::entityType()->getId(), array_map(fn($e) => (string) $e->entityTypeId, $key->getAllEntities()));
	}

	/**
	 * E-mail accounts hold passwords, so they can't be picked as a whole entity type, also not by a key that picked it
	 * before
	 */
	public function testAccountsCanNotBePickedAsEntityType(): void
	{
		$this->assertFalse(McpKey::isPickable(Account::entityType()));

		$key = $this->key([]);
		$entity = new McpKeyEntity($key);
		$entity->entityTypeId = (string) Account::entityType()->getId();
		$entity->mayWrite = true;
		$key->entities = [$entity];
		$this->assertNull($key->findEntity('Account'));
		$this->assertSame([], array_filter($key->getAllEntities(), fn($e) => (string) $e->entityTypeId === (string) Account::entityType()->getId()));
		$this->assertFalse($key->save(), 'a new key with the entity type');
	}

	public function testAccountThatIsNotPickedIsRefused(): void
	{
		$result = $this->call($this->key([[$this->account->id + 1000, false]]), 'email_query', ['accountId' => (string) $this->account->id]);
		$this->assertTrue($result['isError']);
		$this->assertStringContainsString("isn't allowed for this key", $result['structuredContent']['error']);
	}

	public function testAttachmentOfAccountThatIsNotPickedIsRefused(): void
	{
		$result = $this->call($this->key([[$this->account->id + 1000, false]]), 'email_attachment', [
			'accountId' => (string) $this->account->id, 'mailbox' => 'INBOX', 'uid' => 1, 'number' => '2'
		]);
		$this->assertTrue($result['isError']);
		$this->assertStringContainsString("isn't allowed for this key", $result['structuredContent']['error']);
	}

	public function testAttachmentNumberIsValidated(): void
	{
		$key = $this->key([[$this->account->id, false]]);
		foreach (['', '2/../1', '2 BODY[]', '1.', 2] as $number) {
			$result = $this->call($key, 'email_attachment', [
				'accountId' => (string) $this->account->id, 'mailbox' => 'INBOX', 'uid' => 1, 'number' => $number
			]);
			$this->assertTrue($result['isError'], var_export($number, true));
			$this->assertStringContainsString('"number" must be', $result['structuredContent']['error']);
		}
	}

	public function testDraftNeedsCreatePermission(): void
	{
		// Picked with write access, but the user may only read the account
		$result = $this->call($this->key([[$this->account->id, true]]), 'email_draft', ['accountId' => (string) $this->account->id, 'text' => 'Hi']);
		$this->assertTrue($result['isError']);
		$this->assertStringContainsString("don't have access", $result['structuredContent']['error']);
	}

	public function testDraftNeedsWriteAccessOnTheKey(): void
	{
		$key = $this->key([[$this->account->id, false]]);
		$key->entities[] = (function () use ($key) {
			// Another account with write access, so email_draft is listed
			$entity = new McpKeyEntity($key);
			$entity->entityTypeId = (string) Account::entityType()->getId();
			$entity->entityId = (string) ($this->account->id + 1000);
			$entity->mayWrite = true;
			return $entity;
		})();
		$result = $this->call($key, 'email_draft', ['accountId' => (string) $this->account->id, 'text' => 'Hi']);
		$this->assertTrue($result['isError']);
		$this->assertStringContainsString('may only read', $result['structuredContent']['error']);
	}

	public function testAccountTheUserLostAccessToIsRefused(): void
	{
		$key = $this->key([[$this->account->id, false]]);

		$this->actAs(\go\core\model\User::ID_SUPER_ADMIN);
		$acl = Acl::findById($this->account->acl_id);
		$acl->removeGroup($this->other->getPersonalGroup()->id);
		$this->assertTrue($acl->save());
		$this->actAs($this->other->id);

		$result = $this->call($key, 'email_query', ['accountId' => (string) $this->account->id]);
		$this->assertTrue($result['isError']);
		$this->assertStringContainsString("don't have access", $result['structuredContent']['error']);
	}

	public function testPickingIsValidated(): void
	{
		$key = $this->key([[$this->account->id, true]]);
		$this->assertFalse($key->save(), 'write access needs create permission on the account');

		$key = $this->key([[$this->account->id, false]]);
		$this->assertTrue($key->save(), var_export($key->getValidationErrors(), true));
		try {
			// Write access added later is checked too
			$key = McpKey::findById($key->id);
			$key->setValues(['entities' => [['entityTypeId' => (string) Account::entityType()->getId(), 'entityId' => (string) $this->account->id, 'mayWrite' => true]]]);
			$this->assertFalse($key->save(), 'write access needs create permission on the account');

			$this->actAs(\go\core\model\User::ID_SUPER_ADMIN);
			$acl = Acl::findById($this->account->acl_id);
			$acl->removeGroup($this->other->getPersonalGroup()->id);
			$this->assertTrue($acl->save());
			$this->actAs($this->other->id);

			// Access that was revoked later doesn't block saving the key: the tools refuse the account
			$key = McpKey::findById($key->id);
			$key->name = 'renamed';
			$this->assertTrue($key->save(), var_export($key->getValidationErrors(), true));

			// Also when the dialog or an OAuth consent sends the same items again
			$row = ['entityTypeId' => (string) Account::entityType()->getId(), 'entityId' => (string) $this->account->id, 'mayWrite' => false];
			$key = McpKey::findById($key->id);
			$key->setValues(['entities' => [$row]]);
			$this->assertTrue($key->save(), var_export($key->getValidationErrors(), true));

			$other = $this->key([[$this->account->id + 1000, false]]);
			$this->assertFalse($other->save(), 'an account that doesn\'t exist');
		} finally {
			McpKey::delete(['id' => $key->id]);
		}
	}

	public function testPickerListsReadableAccounts(): void
	{
		$accounts = (new \GO\Email\Mcp\EmailToolProvider())->getPickableItems();
		$this->assertSame([(string) $this->account->id], array_column($accounts, 'entityId'));
		$this->assertSame($this->owner->email, $accounts[0]['name']);

		$outsider = $this->createUser('emoutsider');
		try {
			$this->actAs($outsider->id);
			$this->assertSame([], (new \GO\Email\Mcp\EmailToolProvider())->getPickableItems(), 'a user without access to the account');
		} finally {
			$this->actAs(\go\core\model\User::ID_SUPER_ADMIN);
			\go\core\model\User::delete(['id' => $outsider->id]);
		}
	}

	public function testSearchIsEscaped(): void
	{
		$this->assertSame('SUBJECT "say \\"hi\\" \\\\ bye" SINCE 1-Nov-2026 UNSEEN', Query::buildSearch(['subject' => 'say "hi" \\ bye', 'after' => '2026-11-01', 'unreadOnly' => true]));
		$this->assertSame('ALL', Query::buildSearch([]));

		$this->expectException(ToolError::class);
		Query::buildSearch(['subject' => "x\r\nA1 DELETE INBOX"]);
	}
}
