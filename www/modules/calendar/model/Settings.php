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
 * @package GO.modules.calendar.model
 * @version $Id: example.php 7607 20120101Z <<USERNAME>> $
 * @copyright Copyright Intermesh BV.
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */
 
/**
 * The Settings model
 *
 * @package GO.modules.calendar.model
 * @property int $calendar_id
 * @property string $background
 * @property int $reminder
 * @property int $user_id
 */


namespace GO\Calendar\Model;


class Settings extends \GO\Base\Model\AbstractUserDefaultModel{
	
	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return Settings 
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * Returns the table name
	 */
	public function tableName() {
		return 'cal_settings';
	}
	
	public function primaryKey() {
		return 'user_id';
	}
	
	public function relations() {
		return array(
				'calendar' => array('type'=>self::BELONGS_TO, 'model'=>'GO\Calendar\Model\Calendar', 'field'=>'calendar_id')
		);
	}

	protected function beforeValidate()
	{
		if(!$this->calendar || $this->calendar->user_id != $this->user_id) {
			$this->setValidationError('calendar_id', "Invalid default calendar, You must be owner.");
		}
		return parent::beforeValidate();
	}

}