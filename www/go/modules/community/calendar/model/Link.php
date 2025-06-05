<?php

namespace go\modules\community\calendar\model;

use go\core\orm\Mapping;
use go\core\orm\Property;
class Link extends Property {

	protected ?int $id;
	protected int $eventId;

	public string $rel = 'enclosure';

	public string $title;

	public string $contentType;

	public int $size;

	public string $blobId;

	// other optional values are not implemented as it is now only used for attachments
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()->addTable('calendar_event_link', "ro");
	}
}