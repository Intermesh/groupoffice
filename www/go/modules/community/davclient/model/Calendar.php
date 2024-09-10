<?php

namespace go\modules\community\davclient\model;

use go\core\orm\Mapping;
use go\core\orm\Property;
use go\core\util\DateTime;

/**
 * Each calendar is a syncable collection in a DAV account
 * it has a reference to a calendar in Group Office were the data is stored.
 */
class Calendar extends Property
{
	protected $davaccountId;
	/** @var string uri to the calendar on the server, also key of map */
	public $uri;

	public $calendarId;

	/** @var string if server has different ctag we need to fetch all etag to find out what changed. */
	public $ctag;
	public $synctoken;
	private $model;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("davclient_calendar");
	}

	public function addModel($calendar) {
		$this->calendarId = $calendar->id;
		$this->model = $calendar;
	}
}