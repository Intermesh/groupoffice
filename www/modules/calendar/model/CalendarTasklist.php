<?php
/*
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */
namespace GO\Calendar\Model;

/**
 * The CalendarTasklist model
 *
 * @package GO.modules.Calendar
 * @copyright Copyright Intermesh BV.
 *
 * @property int $calendar_id
 * @property int $tasklist_id
 */
class CalendarTasklist extends \GO\Base\Db\ActiveRecord{

	public function primaryKey() {
		return ['calendar_id','tasklist_id'];
	}

	 public function tableName() {
		 return 'cal_visible_tasklists';
	 }
}
