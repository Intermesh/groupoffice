<?php

namespace go\modules\community\calendar\model;

use go\core\orm\Mapping;

class DefaultAlertWT extends Alert {
	// Omit for the default alerts (with or without time)
	protected $calendarId;

	protected static function defineMapping(): Mapping
	{
		return (new Mapping(static::class))->addTable('calendar_default_alert_with_time', "alertwt");
	}

}