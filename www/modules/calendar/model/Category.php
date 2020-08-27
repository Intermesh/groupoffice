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
 * @package GO.modules.Calendar
 * @version $Id: Category.php 7607 2011-09-28 10:29:10Z <<USERNAME>> $
 * @copyright Copyright Intermesh BV.
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 *
 * @property int $id
 * @property String $name
 * @property String $color eg. "EBF1E2"
 * @property int $calendar_id
 * @property int $acl_id
 */


namespace GO\Calendar\Model;


class Category extends \GO\Base\Db\ActiveRecord{
    
      protected function getPermissionLevelForNewModel() {
  
            
            if(empty($this->calendar_id)) {
                return parent::getPermissionLevelForNewModel();
            }else
            {
                return $this->calendar->getPermissionLevel();
            }
        }

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

	public function getPermissionLevel()
	{
		//if you have manage permissions to the calendar you may delete categories created by others
		if(!empty($this->calendar_id)) {
			return $this->calendar->getPermissionLevel();
		} else{
			return parent::getPermissionLevel();
		}
	}

	protected function init() {
		$this->columns['name']['unique']=array("calendar_id");
		return parent::init();
	}
	
	public function getLogMessage($action) {		
		return $this->name;
	}

	public function aclField() {
		return 'acl_id';
	}

	/**
	 * Returns the table name
	 */
	 public function tableName() {
		 return 'cal_categories';
	 }

	/**
	 * Here you can define the relations of this model with other models.
	 * See the parent class for a more detailed description of the relations.
	 */
	public function relations() {
		return array(
			'calendar' => array('type' => self::BELONGS_TO, 'model' => 'GO\Calendar\Model\Calendar', 'field' => 'calendar_id')
		);
	}
	 	 
	 /**
	  * Find a category by name. It searches the global and calendar categories
	  * 
	  * @param int $calendar_id
	  * @param StringHelper $name
	  * @return Category
	  */
	 public function findByName($calendar_id, $name){
		 
		 $findParams = \GO\Base\Db\FindParams::newInstance()->single();
		 
		 $findParams->getCriteria()
						 ->addCondition('name', $name)
						 ->mergeWith(\GO\Base\Db\FindCriteria::newInstance()
							->addCondition('calendar_id', $calendar_id)
							->addCondition('calendar_id', 0,'=','t',false)
										 );
		 
		 return Category::model()->find($findParams);
	 }
	 
	 protected function afterSave($wasNew) {
		 
		 $c = new \GO\Base\Db\Connection();		 
		 $c->createStatement()->update(
						 Event::model()->tableName(), 
						 array('background'=>$this->color),
						 'category_id=:category_id',
						 array('category_id'=>$this->id));
		 
		 return parent::afterSave($wasNew);
	 }
	 
	 protected function beforeDelete() {
		 if (empty($this->calendar))
			 return true;		 
		 
		 if ($this->calendar->getPermissionLevel() >= \GO\Base\Model\Acl::DELETE_PERMISSION)
			 return true;
		 else
			 throw new \GO\Base\Exception\AccessDenied();
	 }
}
