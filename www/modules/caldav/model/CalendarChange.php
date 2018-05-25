<?php

namespace GO\Caldav\Model;

/**
 * @property int $id AI PK
 * @property StringHelper $uri URI to the changed event
 * @property int $synctoken Number that will is incresed in the calendar (higher is newer)
 * @property int $calendarid Unique id of the calendar
 * @property int $operation Add, Update or Delete (One of the constants of this class)
 * 
 * A tracked change in the calendar
 * 
 * Add this to install & update:
 * DROP TABLE IF EXISTS `dav_calendar_changes`;
CREATE TABLE dav_calendar_changes (
    id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    uri VARBINARY(200) NOT NULL,
    synctoken INT(11) UNSIGNED NOT NULL,
    calendarid INT(11) UNSIGNED NOT NULL,
    operation TINYINT(1) NOT NULL,
    INDEX calendarid_synctoken (calendarid, synctoken)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
 * 
 */
class CalendarChange extends \GO\Base\Db\ActiveRecord {
	
	const ADD = 1;
	const MODIFY = 2;
	const DELETE = 3;
	
	public function tableName() {
		return 'dav_calendar_changes';
	}

}
