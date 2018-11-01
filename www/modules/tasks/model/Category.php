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
 * The Category model
 *
 * @package GO.modules.Tasks
 * @version $Id: Category.php 7607 2011-09-20 10:09:35Z <<USERNAME>> $
 * @copyright Copyright Intermesh BV.
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 *
 * @property int $id
 * @property String $name
 * @property int $user_id
 */


namespace GO\Tasks\Model;


class Category extends \GO\Base\Db\ActiveRecord {

	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return Category 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}

	public function tableName() {
		return 'ta_categories';
	}
	
	protected function init() {
		$this->columns['name']['unique']=array("user_id");
		return parent::init();
	}

	public function relations() {
		return array(
				'tasks' => array('type' => self::HAS_MANY, 'model' => 'GO\Tasks\Model\Task', 'field' => 'category_id'),
				);
	}
}
