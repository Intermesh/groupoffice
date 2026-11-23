<?php

namespace GO\Email\Mcp;

use GO\Base\Model\Acl;
use GO\Email\Model\Account;
use go\modules\business\mcp\tool\Tool;
use go\modules\business\mcp\tool\ToolError;

/**
 * Base of the e-mail tools: every call checks that the account is picked for the key and that the user may use it now
 */
abstract class EmailTool extends Tool
{
	/**
	 * Find an account the tool may use
	 *
	 * @param mixed $accountId From the arguments of the model
	 * @param bool $forDraft Whether a draft is written: needs write access on the key and create permission on the account
	 * @throws ToolError
	 */
	protected function account(mixed $accountId, bool $forDraft = false): Account
	{
		if (!is_scalar($accountId) || !ctype_digit((string) $accountId)) {
			throw new ToolError('"accountId" must be the ID of an e-mail account from email_mailboxes');
		}

		$item = $this->key->findItem(Account::entityType()->getId(), (string) $accountId);
		if (!$item) {
			throw new ToolError('E-mail account ' . $accountId . ' isn\'t allowed for this key. Call email_mailboxes for the accounts you may use.');
		}
		if ($forDraft && !$item->mayWrite) {
			throw new ToolError('This key may only read e-mail account ' . $accountId . ', not write drafts');
		}

		// The user may have lost access after the account was picked
		$account = Account::model()->findByPk($accountId, false, true);
		if (!$account || !$account->checkPermissionLevel($forDraft ? Acl::CREATE_PERMISSION : Acl::READ_PERMISSION)) {
			throw new ToolError('You don\'t have access to e-mail account ' . $accountId);
		}

		return $account;
	}

	/**
	 * The accounts picked for the key that the user may still read
	 *
	 * @return Account[]
	 */
	protected function accounts(): array
	{
		$accounts = [];
		foreach ($this->key->getItems(Account::entityType()->getId()) as $item) {
			$account = Account::model()->findByPk($item->entityId, false, true);
			if ($account && $account->checkPermissionLevel(Acl::READ_PERMISSION)) {
				$accounts[] = $account;
			}
		}
		return $accounts;
	}

	/**
	 * A mailbox name from the arguments of the model
	 *
	 * Control characters are refused: a line break would start another IMAP command.
	 *
	 * @throws ToolError
	 */
	protected static function mailbox(mixed $mailbox): string
	{
		if ($mailbox === null || $mailbox === '') {
			return 'INBOX';
		}
		if (!is_string($mailbox) || strlen($mailbox) > 500 || preg_match('/[\x00-\x1F\x7F]/', $mailbox)) {
			throw new ToolError('"mailbox" must be a folder name from email_mailboxes');
		}
		return $mailbox;
	}

	/**
	 * Schema of a reference to a message
	 */
	protected static function messageRefSchema(): array
	{
		return [
			'accountId' => ['type' => 'string', 'description' => 'ID of the e-mail account'],
			'mailbox' => ['type' => 'string', 'description' => 'Folder name, like "INBOX"'],
			'uid' => ['type' => 'integer', 'description' => 'UID of the message in the folder']
		];
	}
}
