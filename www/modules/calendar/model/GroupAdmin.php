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
 * The GroupAdmin model
 *
 * @package GO.modules.calendar.model
 * @property int $user_id
 * @property int $group_id
 */


namespace GO\Calendar\Model;


class GroupAdmin extends \GO\Base\Db\ActiveRecord{

	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return GroupAdmin
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	public function primaryKey() {
		return array('group_id','user_id');
	}
	
	/**
	 * Returns the table name
	 */
	 public function tableName() {
		 return 'cal_group_admins';
	 }

	/**
	 * Here you can define the relations of this model with other models.
	 * See the parent class for a more detailed description of the relations.
	 */
	 public function relations() {
		 return array(
			 'group' => array('type'=>self::BELONGS_TO, 'model'=>'GO\Calendar\Model\Group', 'field'=>'group_id')
		 );
	 }
	 
	 protected function afterSave($wasNew) {
		 
		 $stmt = $this->group->calendars;
		 
		 foreach($stmt as $calendar){
       $group = \go\core\model\Group::find()->where(['isUserGroupFor' => $this->user_id])->single();
			 $calendar->acl->addGroup($group->id, \GO\Base\Model\Acl::DELETE_PERMISSION);
		 }
		 
		 return parent::afterSave($wasNew);
	 }
}
