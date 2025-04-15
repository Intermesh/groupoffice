<?php
namespace go\modules\community\email\model;

use go\core\orm\Mapping;
use go\core\orm\Property;

class EmailAddress extends Property {

	protected ?int $fk;
	/**
	 * The display-name of the mailbox [@!RFC5322]. If this is a quoted-string:
	 *
	 * The surrounding DQUOTE characters are removed.
	 * Any quoted-pair is decoded.
	 * White space is unfolded, and then any leading and trailing white space is removed.
	 * If there is no display-name but there is a comment immediately following the addr-spec, the value of this SHOULD
	 * be used instead. Otherwise, this property is null.
	 * @var string|null
	 */
	public ?string $name;

	/** @var string The addr-spec of the mailbox [@!RFC5322] */
	public ?string $email;

	protected ?string $type; // from, to, cc, bcc

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()->addTable('email_address');
	}
}