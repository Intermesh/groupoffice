<?php
/*
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */
 
/**
 * The Settings model
 *
 * @package GO.modules.Tasks
 * @version $Id: Settings.php 7607 2011-09-20 10:06:28Z <<USERNAME>> $
 * @copyright Copyright Intermesh BV.
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 *
 * @property int $user_id
 * @property int $reminder_days
 * @property String $reminder_time
 * @property boolean $remind
 * @property int $default_tasklist_id
 */


namespace GO\Tasks\Model;


class Settings extends \GO\Base\Model\AbstractUserDefaultModel {

	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return Settings
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}

	public function tableName() {
		return 'ta_settings';
	}

	public function relations() {
		return array(
			'tasklist' => array('type' => self::BELONGS_TO, 'model' => 'GO\Tasks\Model\Tasklist', 'field' => 'default_tasklist_id', 'delete' => false)
			);
	}
		
	public function primaryKey() {
		return 'user_id';
	}
}
