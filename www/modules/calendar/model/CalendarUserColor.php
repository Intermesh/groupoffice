<?php
/**
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @package GO.modules.Calendar
 * @version $Id: CalendarUserColors.php 7607 2012-06-27 15:56:46Z wsmits $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits wsmits@intermesh.nl
 */
 
/**
 * The CalendarUserColors model
 *
 * @package GO.modules.Calendar
 * @version $Id: CalendarUserColor.php 7607 2012-06-27 15:56:46Z wsmits $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits wsmits@intermesh.nl
 *
 * @property int $user_id
 * @property int $calendar_id
 * @property String $color
 */


namespace GO\Calendar\Model;


class CalendarUserColor extends \GO\Base\Db\ActiveRecord{

	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return CalendarUserColors
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}

	public function primaryKey() {
		return array('calendar_id','user_id');
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
		 return 'cal_calendar_user_colors';
	 }

	/**
	 * Here you can define the relations of this model with other models.
	 * See the parent class for a more detailed description of the relations.
	 */
	 public function relations() {
		 return array(
				 
		 );
	 }
}
