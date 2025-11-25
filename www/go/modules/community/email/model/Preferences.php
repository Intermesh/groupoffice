<?php

namespace go\modules\community\email\model;

use go\core\orm\Mapping;
use go\core\orm\Property;


class Preferences extends Property
{

	/** Identity to use when composing a new email */
	public ?string $defaultIdentityId;

	/** Group messages into conversations? */
	public bool $enableConversations = false;

	/** Load remote images: always, knownOnly, ask */
	public ?string $loadExternalContent = 'ask';

	/**  content-type of new email message */
	public ?string $messageStructure = 'html'; // or 'plain'

	/** quote on Reply and Forward */
	public bool $quoteOriginal = true;

	/** Compose Reply in same structure as original */
	public bool $structureAsOriginal = false;

	/** Always show unread messages on top of the list */
	public bool $showNewOnTop = false;

	/** Show a preview line on the mailbox screen? */
	public bool $showPreview = true;

	/** Show avatars of senders? */
	public bool $showAvatar = true;

	/** If true, HTML messages will be converted to plain text before being shown, */
	public bool $viewTextOnly = false;

	/** Amount of seconds sending the message is delayed, so users can undo, 0 to send directly */
	public int $delaySentSeconds = 15; // unsigned

	/** If true, Ask to add the unknown recipient to the address book */
	public bool $saveUnknownRecipient = false;

	/** reply button will add all the recipients in the conversation., false for just the sender */
	public bool $defaultReplyAll = true;

	/** If true, client will ask to respond to read receipts. */
	public bool $showReadReceipts = false;

	protected static function defineMapping(): Mapping {
		return parent::defineMapping()->addTable("email_preferences", "emp");
	}

}