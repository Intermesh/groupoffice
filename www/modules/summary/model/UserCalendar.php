<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @property int $user_id
 * @property int $calendar_id
 */


namespace GO\Summary\Model;


class UserCalendar extends \GO\Base\Db\ActiveRecord {
	
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
		
	public function tableName(){
		return 'su_visible_calendars';
	}
	
	public function primaryKey() {
    return array('user_id','calendar_id');
  }
}
