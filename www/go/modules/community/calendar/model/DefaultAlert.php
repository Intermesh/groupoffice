<?php

namespace go\modules\community\calendar\model;

use go\core\orm\Mapping;

/**
 * Default alert for event in the calendar that have $useDefaultAlerts set to true
 *
 * @property int $fk PK of the calendar this alert is a default for
 */
class DefaultAlert extends Alert {

	protected static function defineMapping(): Mapping
	{
		return (new Mapping(static::class))->addTable('calendar_default_alert', "dalert");
	}

}