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
 * @version $Id: DavEvent.php 21378 2016-05-04 11:34:26Z mschering $
 * @copyright Copyright Intermesh BV.
 */

namespace GO\Caldav\Model;

use GO\Calendar\Model\Calendar;

/**
 * The DavEvent model
 *
 * @package GO.modules.caldav.model
 * @property StringHelper $uri
 * @property StringHelper $data
 * @property int $mtime
 * @property int $id
 */
class DavEvent extends \GO\Base\Db\ActiveRecord {

	public $calendarId;

	public function tableName() {
		return 'dav_events';
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

		$calendar = Calendar::model()->findByPk($this->calendarId);
		$version = $calendar->version;
//		if ($calendar->tasklist_id) {
//			$tasklist = \GO\Tasks\Model\Tasklist::model()->findByPk($calendar->tasklist_id);
//			$version += $tasklist->version;
//		}

		$objectUri = $this->uri;
		$stmt = \GO::getDbConnection()->prepare('INSERT INTO dav_calendar_changes (uri, synctoken, calendarid, operation) VALUES (?, ?, ?, ?)');
		$stmt->execute([
				$objectUri,
				$version,
				$this->calendarId,
				$operation
		]);
		Calendar::versionUp($this->calendarId);
	}

}
