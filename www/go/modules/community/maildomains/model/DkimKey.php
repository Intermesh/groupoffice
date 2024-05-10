<?php

namespace go\modules\community\maildomains\model;

use go\core\orm\Mapping;
use go\core\orm\Property;

final class DkimKey extends Property
{
	public int $id;

	public int $domainId;

	public string $selector;

	public string $txt;

	public int $status;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("community_maildomains_dkim_key");
	}


}