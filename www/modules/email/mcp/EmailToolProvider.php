<?php

namespace GO\Email\Mcp;

use GO\Base\Db\FindParams;
use GO\Base\Model\Acl;
use GO\Email\Model\Account;
use go\core\orm\EntityType;
use go\modules\business\mcp\model\McpKey;
use go\modules\business\mcp\ToolProvider;

/**
 * E-mail tools for AI assistants, until e-mail has a JMAP API
 *
 * The user picks single e-mail accounts for a key. Assistants can search and read mail, and write drafts in accounts
 * picked with write access. They can't send: a person always sends the draft.
 *
 * Only used when the MCP module is installed, see {@see \GO\Email\EmailModule::onMcpToolProviders()}.
 */
class EmailToolProvider implements ToolProvider
{
	public function getEntityType(): EntityType
	{
		return Account::entityType();
	}

	public function getPickableItems(): array
	{
		$items = [];
		$accounts = Account::model()->find(FindParams::newInstance()->permissionLevel(Acl::READ_PERMISSION));
		foreach ($accounts as $account) {
			$items[] = ['entityId' => (string) $account->id, 'name' => self::accountName($account)];
		}
		return $items;
	}

	public function mayPick(string $entityId, bool $mayWrite): bool
	{
		if (!ctype_digit($entityId)) {
			return false;
		}
		$account = Account::model()->findByPk($entityId, false, true);
		return $account && $account->checkPermissionLevel($mayWrite ? Acl::CREATE_PERMISSION : Acl::READ_PERMISSION);
	}

	public function getTools(McpKey $key, array $items): array
	{
		$tools = [
			new Mailboxes($key),
			new Query($key),
			new Get($key),
			new Attachment($key)
		];
		foreach ($items as $item) {
			if ($item->mayWrite) {
				$tools[] = new Draft($key);
				break;
			}
		}
		return $tools;
	}

	/**
	 * The e-mail address of an account, for the picker and the model
	 */
	public static function accountName(Account $account): string
	{
		$alias = $account->getDefaultAlias();
		return $alias ? $alias->email : $account->username;
	}
}
