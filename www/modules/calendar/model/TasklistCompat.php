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
 * Added for the CalendarTasklistController
 *
 * @property int $id
 * @property int $name
 */
class TasklistCompat extends \GO\Base\Db\ActiveRecord {

	/**
	 * Returns the table name
	 */
	public function tableName() {
		return 'tasks_tasklist';
	}

	public function aclField()
	{
		return 'aclId';
	}

}
