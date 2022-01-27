<?php

/**
 * Group-Office
 * 
 * Copyright Intermesh BV. 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @license AGPL/Proprietary http://www.group-office.com/LICENSE.TXT
 * @link http://www.group-office.com
 * @package GO.modules.caldav.model
 * @version $Id: DavTask.php 22174 2017-03-21 09:04:12Z mdhart $
 * @copyright Copyright Intermesh BV.
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */

namespace GO\Caldav\Model;

use GO;
use GO\Base\Db\ActiveRecord;
use GO\Base\Util\StringHelper;
use GO\Calendar\Model\Calendar;



/**
 * The DavTask model
 *
 * @package GO.modules.caldav.model
 * @property StringHelper $uri
 * @property StringHelper $data
 * @property int $mtime
 * @property int $id
 */
class DavTask extends ActiveRecord {

	/**
	 * ID of the calendar that uses the tasklist of this task
	 * @var int
	 */
	public $tasklistId;

	public function tableName() {
		return 'dav_tasks';
	}

	public function afterSave($wasNew) {
		if (!$this->isModified()) {
			return true;
		} elseif ($wasNew) {
			$this->addChange(1);
		} else {
			$this->addChange(2);
		}
		return true;
	}

	public function afterDelete() {
		$this->addChange(3);
		return parent::afterDelete();
	}

	/**
	 * Adds a change record to the dav_calendar_changes table.
	 *
	 * @param int $operation 1 = add, 2 = modify, 3 = delete.
	 * @return void
	 */
	protected function addChange($operation) {
		
		return;

////		$tasklist = \GO\Tasks\Model\Tasklist::model()->findByPk($this->tasklistId);
//
//		$calendars = Calendar::model()->findByAttribute('tasklist_id', $this->tasklistId);
//
//		foreach($calendars as $calendar) {
//			$version = $tasklist->version+$calendar->version;
//
//
//			$objectUri = $this->uri;
//			$stmt = GO::getDbConnection()->prepare('INSERT INTO dav_calendar_changes (uri, synctoken, calendarid, operation) VALUES (?, ?, ?, ?)');
//			$stmt->execute([
//					$objectUri,
//					$version,
//					$calendar->id,
//					$operation
//
//			]);
//		}
//		Tasklist::versionUp($calendar->tasklist_id);
	}

}
