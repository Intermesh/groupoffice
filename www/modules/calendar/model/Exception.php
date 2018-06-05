<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: Exception.php 7607 2011-09-16 11:24:50Z <<USERNAME>> $
 * @copyright Copyright Intermesh
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */

/**
 * The Exception model
 * 
 * @property int $id
 * @property int $event_id
 * @property int $time The time of the day of the exception. Use getStartTime() for the acurate time.
 * @property int $exception_event_id
 */

namespace GO\Calendar\Model;


class Exception extends \GO\Base\Db\ActiveRecord {

	

	protected function init() {
		parent::init();

		$this->columns['time']['required'] = true;
	}

	/**
	 * Enable this function if you want this model to check the acl's automatically.
	 */
	// public function aclField(){
	//	 return 'acl_id';	
	// }

	/**
	 * Returns the table name
	 */
	public function tableName() {
		return 'cal_exceptions';
	}

	/**
	 * Here you can define the relations of this model with other models.
	 * See the parent class for a more detailed description of the relations.
	 */
	public function relations() {
		return array(
				'event' => array('type' => self::BELONGS_TO, 'model' => 'GO\Calendar\Model\Event', 'field' => 'exception_event_id'),
				'mainevent' => array('type' => self::BELONGS_TO, 'model' => 'GO\Calendar\Model\Event', 'field' => 'event_id')
		);
	}

	protected function afterSave($wasNew) {

		if ($this->mainevent){
			$this->mainevent->touch();
			$this->mainevent->setReminder();
		}

		return parent::afterSave($wasNew);
	}
	
	/**
	 * Get's the start time of the exception based on the actual master event
	 * start time. The time entry just contains the date.
	 * 
	 * @return int 
	 */
	public function getStartTime(){
		return \GO\Base\Util\Date::clear_time($this->time, date('H',$this->mainevent->start_time), date('i',$this->mainevent->start_time));
	}

}
