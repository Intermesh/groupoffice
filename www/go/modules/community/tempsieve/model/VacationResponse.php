<?php
/**
 * @see https://www.rfc-editor.org/rfc/rfc8621#section-8 RFC-8621, section 8
 */
namespace go\modules\community\tempsieve\Model;

use go\core\jmap\Entity;
use go\core\util\DateTime;

final class VacationResponse extends Entity
{
	public string $id;
	public bool $isEnabled;
	public ?DateTime $fromDate;
	public ?DateTime $toDate;

	public ?string $subject;

	public ?string $textBody;

	public ?string $htmlBody;

}