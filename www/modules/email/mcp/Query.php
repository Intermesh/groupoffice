<?php

namespace GO\Email\Mcp;

use GO\Base\Mail\Imap;
use GO\Email\Model\ImapMessage;
use go\modules\business\mcp\tool\ToolError;

/**
 * Search the messages of a folder
 *
 * The IMAP search is built here from separate fields. The model never writes IMAP search syntax.
 */
class Query extends EmailTool
{
	const MAX_LIMIT = 25;

	public function getName(): string
	{
		return 'email_query';
	}

	public function getTitle(): string
	{
		return 'Search e-mail';
	}

	public function getDescription(): string
	{
		return 'Search the messages in a folder of an e-mail account, newest first. All given fields must match. Returns '
			. 'the sender, recipients, subject and date of each message, and its accountId, mailbox and uid for email_get.';
	}

	public function getInputSchema(): array
	{
		return [
			'type' => 'object',
			'properties' => [
				'accountId' => ['type' => 'string', 'description' => 'ID of the e-mail account, from email_mailboxes'],
				'mailbox' => ['type' => 'string', 'description' => 'Folder name. Default "INBOX".'],
				'from' => ['type' => 'string', 'description' => 'Text in the sender'],
				'to' => ['type' => 'string', 'description' => 'Text in the recipients'],
				'subject' => ['type' => 'string', 'description' => 'Text in the subject'],
				'text' => ['type' => 'string', 'description' => 'Text anywhere in the message'],
				'after' => ['type' => 'string', 'description' => 'Received on or after this date, "YYYY-MM-DD"'],
				'before' => ['type' => 'string', 'description' => 'Received before this date, "YYYY-MM-DD"'],
				'unreadOnly' => ['type' => 'boolean', 'description' => 'Only unread messages'],
				'position' => ['type' => 'integer', 'minimum' => 0, 'description' => 'Skip this many results'],
				'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => self::MAX_LIMIT, 'description' => 'Default 10']
			],
			'required' => ['accountId'],
			'additionalProperties' => false
		];
	}

	public function call(array $arguments): array
	{
		$account = $this->account($arguments['accountId'] ?? null);
		$mailbox = self::mailbox($arguments['mailbox'] ?? null);
		$search = self::buildSearch($arguments);

		$position = max(0, (int) ($arguments['position'] ?? 0));
		$limit = min(self::MAX_LIMIT, max(1, (int) ($arguments['limit'] ?? 10)));

		try {
			$messages = ImapMessage::model()->find($account, $mailbox, $position, $limit, Imap::SORT_DATE, true, $search);
			$total = $account->getImapConnection()->sort_count;
		} catch (\Exception $e) {
			throw new ToolError('Could not search folder "' . $mailbox . '": ' . $e->getMessage());
		}

		$list = [];
		foreach ($messages as $message) {
			$list[] = [
				'accountId' => (string) $account->id,
				'mailbox' => $mailbox,
				'uid' => (int) $message->uid,
				'from' => (string) $message->from,
				'to' => (string) $message->to,
				'subject' => (string) $message->subject,
				'date' => $message->udate ? date('c', (int) $message->udate) : null,
				'unread' => !$message->seen,
				'hasAttachments' => !empty($message->has_attachments)
			];
		}

		return ['total' => (int) $total, 'position' => $position, 'messages' => $list];
	}

	/**
	 * The IMAP search criteria of the arguments
	 *
	 * @throws ToolError
	 */
	public static function buildSearch(array $arguments): string
	{
		$criteria = [];
		foreach (['from' => 'FROM', 'to' => 'TO', 'subject' => 'SUBJECT', 'text' => 'TEXT'] as $name => $key) {
			if (isset($arguments[$name]) && $arguments[$name] !== '') {
				$criteria[] = $key . ' ' . self::quote($arguments[$name], $name);
			}
		}
		foreach (['after' => 'SINCE', 'before' => 'BEFORE'] as $name => $key) {
			if (isset($arguments[$name]) && $arguments[$name] !== '') {
				$date = is_string($arguments[$name]) ? \DateTime::createFromFormat('!Y-m-d', $arguments[$name]) : false;
				if (!$date || $date->format('Y-m-d') !== $arguments[$name]) {
					throw new ToolError('"' . $name . '" must be a date like "2026-11-16"');
				}
				$criteria[] = $key . ' ' . $date->format('j-M-Y');
			}
		}
		if (!empty($arguments['unreadOnly'])) {
			$criteria[] = 'UNSEEN';
		}

		return empty($criteria) ? 'ALL' : implode(' ', $criteria);
	}

	/**
	 * An IMAP quoted string (RFC 3501). Control characters are refused: a line break would start another IMAP command.
	 *
	 * @throws ToolError
	 */
	private static function quote(mixed $value, string $name): string
	{
		if (!is_string($value) || strlen($value) > 200 || preg_match('/[\x00-\x1F\x7F]/', $value)) {
			throw new ToolError('"' . $name . '" must be a single line of text');
		}
		return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"';
	}
}
