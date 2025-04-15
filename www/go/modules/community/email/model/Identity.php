<?php

namespace go\modules\community\email\model;


use go\core\acl\model\AclItemEntity;

class Identity extends AclItemEntity {

	/** @var string The 'From' name when composing new mail */
	public ?string $name;

	/** @var string read-only the 'From' email when composing new mail */
	public ?string $email;

	/** @var string The 'Reply-to' header for new mail */
	protected ?string $replyTo;

	/** @var string The 'Bcc' header for new mail */
	protected ?string $bcc;

	/** @var string Signate for new plain-text mail */
	public ?string $textSignature;

	/** @var string signature to use for new html mail to insert in <body> */
	public ?string $htmlSignature;


	/** @return EmailAddress[]|null */
	public function getBcc() {
		return json_decode($this->bcc);
	}

	/** @return EmailAddress[]|null */
	public function getReplyTo() {
		return json_decode($this->replyTo);
	}

	public function setBcc($value) {
		$this->bcc = json_encode($value);
	}

	public function setReplyTo($value) {
		$this->replyTo = json_encode($value);
	}

	protected static function aclEntityClass(): string
	{
		return EmailAccount::class;
	}

	protected static function aclEntityKeys(): array
	{
		return ['accountId'=>'id'];
	}
}
