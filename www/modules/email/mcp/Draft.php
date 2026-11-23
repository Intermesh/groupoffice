<?php

namespace GO\Email\Mcp;

use GO\Email\Model\ImapMessage;
use go\core\fs\Blob;
use go\core\mail\AddressList;
use go\core\mail\Message;
use go\modules\business\mcp\tool\ToolError;

/**
 * Save a draft in the Drafts folder of an e-mail account
 *
 * It's never sent: the user reviews and sends it in GroupOffice. The sender is the account's own address, never one
 * chosen by the model.
 */
class Draft extends EmailTool
{
	/**
	 * Maximum total size of the attachments
	 */
	const MAX_ATTACHMENTS_SIZE = 26214400;

	public function getName(): string
	{
		return 'email_draft';
	}

	public function getTitle(): string
	{
		return 'Write an e-mail draft';
	}

	public function getDescription(): string
	{
		return 'Save an e-mail as a draft in the Drafts folder of an account. It is not sent: the user checks and sends '
			. 'it in GroupOffice. For a reply, give "inReplyTo" with the mailbox and uid of the message. Attach files with '
			. 'the blobIds of create_blob.';
	}

	public function getInputSchema(): array
	{
		$addresses = ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'Addresses like "john@example.com" or "John Doe <john@example.com>"'];
		return [
			'type' => 'object',
			'properties' => [
				'accountId' => ['type' => 'string', 'description' => 'ID of the e-mail account, from email_mailboxes'],
				'to' => $addresses,
				'cc' => $addresses,
				'bcc' => $addresses,
				'subject' => ['type' => 'string'],
				'text' => ['type' => 'string', 'description' => 'The message as plain text'],
				'inReplyTo' => [
					'type' => 'object',
					'description' => 'The message this is a reply to, in the same account',
					'properties' => [
						'mailbox' => ['type' => 'string'],
						'uid' => ['type' => 'integer']
					],
					'required' => ['mailbox', 'uid'],
					'additionalProperties' => false
				],
				'attachments' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'blobIds of create_blob']
			],
			'required' => ['accountId', 'text'],
			'additionalProperties' => false
		];
	}

	public function isReadOnly(): bool
	{
		return false;
	}

	/**
	 * It only adds a draft
	 */
	public function isDestructive(): bool
	{
		return false;
	}

	public function call(array $arguments): array
	{
		$account = $this->account($arguments['accountId'] ?? null, true);
		if (empty($account->drafts)) {
			throw new ToolError('E-mail account ' . $account->id . ' has no Drafts folder. The user can set one in the account settings.');
		}
		$alias = $account->getDefaultAlias();
		if (!$alias) {
			throw new ToolError('E-mail account ' . $account->id . ' has no sender address');
		}

		$message = new Message();
		$message->setFrom($alias->email, $alias->name);
		$message->setTo(...self::addresses($arguments['to'] ?? [], 'to'));
		$message->setCc(...self::addresses($arguments['cc'] ?? [], 'cc'));
		$message->setBcc(...self::addresses($arguments['bcc'] ?? [], 'bcc'));

		$subject = $arguments['subject'] ?? '';
		if (!is_string($subject) || mb_strlen($subject) > 500 || preg_match('/[\x00-\x1F\x7F]/', $subject)) {
			throw new ToolError('"subject" must be a single line of text');
		}

		$text = $arguments['text'] ?? null;
		if (!is_string($text)) {
			throw new ToolError('"text" must be the message as plain text');
		}
		$message->setBody($text, 'text/plain');

		if (isset($arguments['inReplyTo'])) {
			$subject = $this->reply($message, $account, $arguments['inReplyTo'], $subject);
		}
		$message->setSubject($subject);

		$this->attach($message, $arguments['attachments'] ?? []);

		try {
			$imap = $account->openImapConnection($account->drafts);
			$uid = $imap->get_uidnext();
			if (!$imap->append_message($account->drafts, $message->toString(), "\\Seen \\Draft")) {
				throw new ToolError('The draft could not be saved: ' . $imap->last_error());
			}
		} catch (ToolError $e) {
			throw $e;
		} catch (\Exception $e) {
			throw new ToolError('The draft could not be saved: ' . $e->getMessage());
		}

		return [
			'accountId' => (string) $account->id,
			'mailbox' => $account->drafts,
			'uid' => $uid ? (int) $uid : null,
			'subject' => $subject,
			'sent' => false,
			'note' => 'Saved as a draft. The user can check and send it in GroupOffice.'
		];
	}

	/**
	 * Parse addresses of the model. Only valid addresses on one line.
	 *
	 * @return \go\core\mail\Address[]
	 * @throws ToolError
	 */
	private static function addresses(mixed $value, string $name): array
	{
		if (!is_array($value) || !array_is_list($value) || count($value) > 100) {
			throw new ToolError('"' . $name . '" must be a list of e-mail addresses');
		}
		$addresses = [];
		foreach ($value as $address) {
			if (!is_string($address) || preg_match('/[\x00-\x1F\x7F]/', $address)) {
				throw new ToolError('"' . $name . '" must be a list of e-mail addresses');
			}
			try {
				$list = (new AddressList($address, true))->toArray();
			} catch (\Exception $e) {
				$list = [];
			}
			if (count($list) !== 1 || !filter_var($list[0]->getEmail(), FILTER_VALIDATE_EMAIL)) {
				throw new ToolError('Invalid e-mail address in "' . $name . '": ' . $address);
			}
			$addresses[] = $list[0];
		}
		return $addresses;
	}

	/**
	 * Make the draft a reply: the In-Reply-To and References headers, and "Re: " before the subject
	 *
	 * @return string The subject
	 * @throws ToolError
	 */
	private function reply(Message $message, \GO\Email\Model\Account $account, mixed $inReplyTo, string $subject): string
	{
		if (!is_array($inReplyTo) || !isset($inReplyTo['uid']) || !(is_int($inReplyTo['uid']) || ctype_digit((string) $inReplyTo['uid']))) {
			throw new ToolError('"inReplyTo" must have the mailbox and uid of a message from email_query');
		}
		$mailbox = self::mailbox($inReplyTo['mailbox'] ?? null);

		$original = ImapMessage::model()->findByUid($account, $mailbox, (int) $inReplyTo['uid']);
		if (!$original) {
			throw new ToolError('The message to reply to wasn\'t found');
		}

		$messageId = trim((string) $original->message_id);
		if ($messageId !== '' && !preg_match('/[\x00-\x1F\x7F]/', $messageId)) {
			$message->setInReplyTo($messageId);
			$message->setReferences($messageId);
		}

		if ($subject === '') {
			$subject = (string) $original->subject;
			if (!preg_match('/^re:/i', $subject)) {
				$subject = 'Re: ' . $subject;
			}
		}
		return $subject;
	}

	/**
	 * @throws ToolError
	 */
	private function attach(Message $message, mixed $blobIds): void
	{
		if (!is_array($blobIds) || !array_is_list($blobIds) || count($blobIds) > 20) {
			throw new ToolError('"attachments" must be a list of at most 20 blobIds');
		}
		$size = 0;
		foreach ($blobIds as $blobId) {
			$blob = is_string($blobId) && Blob::isValidId($blobId) ? Blob::findById($blobId) : null;
			if (!$blob) {
				throw new ToolError('Unknown blobId in "attachments": ' . (is_string($blobId) ? $blobId : ''));
			}
			$size += (int) $blob->size;
			if ($size > self::MAX_ATTACHMENTS_SIZE) {
				throw new ToolError('The attachments are larger than 25 MB together');
			}
			$message->addBlob($blob);
		}
	}
}
