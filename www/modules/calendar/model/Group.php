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
 * The Group model
 *
 * @package GO.modules.Calendar
 * @version $Id: Group.php 7607 2011-09-28 10:30:15Z <<USERNAME>> $
 * @copyright Copyright Intermesh BV.
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 *
 * @property int $id
 * @property int $user_id
 * @property String $name
 * @property String $fields
 * @property int $show_not_as_busy
 */

namespace GO\Calendar\Model;


class Group extends \GO\Base\Db\ActiveRecord {

	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return Group
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	/**
	 * Returns the table name
	 */
	public function tableName() {
		return 'cal_groups';
	}
	
	protected function init() {
		$this->columns['name']['unique']=true;
		return parent::init();
	}

	/**
	 * Here you can define the relations of this model with other models.
	 * See the parent class for a more detailed description of the relations.
	 */
	public function relations() {

		return array(
				'admins' => array('type' => self::MANY_MANY, 'model' => 'GO\Base\Model\User', 'field' => 'group_id', 'linkModel' => 'GO\Calendar\Model\GroupAdmin'),
				'calendars' => array('type' => self::HAS_MANY, 'model' => 'GO\Calendar\Model\Calendar', 'field' => 'group_id'),
		);
	}

}
