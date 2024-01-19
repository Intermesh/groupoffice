<?php

namespace go\modules\community\calendar\model;

use go\core\orm\Mapping;

class DefaultAlertWT extends DefaultAlert {

	protected static function defineMapping(): Mapping
	{
		return (new Mapping(static::class))->addTable('calendar_default_alert_with_time', "alertwt");
	}

}