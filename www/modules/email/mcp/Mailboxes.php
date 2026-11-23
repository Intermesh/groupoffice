<?php

namespace GO\Email\Mcp;

use go\modules\business\mcp\tool\ToolError;

/**
 * The picked e-mail accounts with their folders
 */
class Mailboxes extends EmailTool
{
	public function getName(): string
	{
		return 'email_mailboxes';
	}

	public function getTitle(): string
	{
		return 'E-mail accounts and folders';
	}

	public function getDescription(): string
	{
		return 'List the e-mail accounts you may use, with their folders and unread counts. Call this first for the '
			. 'accountId and folder names of email_query and email_get.';
	}

	public function getInputSchema(): array
	{
		return [
			'type' => 'object',
			'properties' => [
				'accountId' => ['type' => 'string', 'description' => 'Only this account. Default: all accounts.']
			],
			'additionalProperties' => false
		];
	}

	public function call(array $arguments): array
	{
		$accounts = isset($arguments['accountId']) ? [$this->account($arguments['accountId'])] : $this->accounts();

		$result = [];
		foreach ($accounts as $account) {
			$special = array_filter([
				'drafts' => $account->drafts,
				'sent' => $account->sent,
				'trash' => $account->trash
			]);

			try {
				$folders = [];
				foreach ($account->getAllMailboxes(false, true) as $mailbox) {
					if ($mailbox->noselect) {
						continue;
					}
					$folder = ['name' => $mailbox->name, 'unread' => (int) $mailbox->unseen];
					$role = array_search($mailbox->name, $special, true);
					if ($role !== false) {
						$folder['role'] = $role;
					}
					$folders[] = $folder;
				}
			} catch (\Exception $e) {
				throw new ToolError('Could not read the folders of e-mail account ' . $account->id . ': ' . $e->getMessage());
			}

			$result[] = [
				'accountId' => (string) $account->id,
				'email' => EmailToolProvider::accountName($account),
				'folders' => $folders
			];
		}

		return ['accounts' => $result];
	}
}
