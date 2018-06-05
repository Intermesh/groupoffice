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
 * @version $Id: View.php 7607 2012-04-12 11:48:52Z <<USERNAME>> $
 * @copyright Copyright Intermesh BV.
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */
 
/**
 * The View model
 *
 * @package GO.modules.Calendar
 * @version $Id: View.php 7607 2012-04-12 11:48:52Z <<USERNAME>> $
 * @copyright Copyright Intermesh BV.
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 *
 * @property int $id
 * @property int $user_id
 * @property String $name
 * @property int $time_interval
 * @property int $acl_id
 * @property int $merge
 * @property int $owncolor
 */


namespace GO\Calendar\Model;


class View extends \GO\Base\Db\ActiveRecord{

	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return View
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	protected function init() {
		$this->columns['name']['unique']=true;
		$this->columns['name']['required']=true;
		return parent::init();
	}


	/**
	 * Enable this function if you want this model to check the acl's automatically.
	 */
	 public function aclField(){
		 return 'acl_id';	
	 }
	 
	 protected function getPermissionLevelForNewModel() {
		 return \GO\Base\Model\Acl::MANAGE_PERMISSION;
	 }
	 

	/**
	 * Returns the table name
	 */
	 public function tableName() {
		 return 'cal_views';
	 }
     
     public function getGroupCalendars()
     {
        $findParams = \GO\Base\Db\FindParams::newInstance()
                ->select('t.*')
                ->criteria(\GO\Base\Db\FindCriteria::newInstance()
				->addCondition('view_id', $this->id,'=', 'vgr'));	
        
        $findParams->joinModel(array(
            'model'=>'GO\Base\Model\User',
            'localField'=>'user_id',
            'tableAlias'=>'usr', 
            
        ));
        
        $findParams->joinModel(array(
            'model'=>'GO\Base\Model\UserGroup',
            'localField'=>'user_id',
            'foreignField'=>'userId',
            'tableAlias'=>'usg', 
            
        ));
        
		$findParams->joinModel(array(
            'model'=>'GO\Base\Model\Group',
            'localField'=>'groupId',
            'localTableAlias'=>'usg',
            'tableAlias'=>'grp', 
		));
        
        $findParams->joinModel(array(
            'model'=>'GO\Calendar\Model\ViewGroup',
            'localField'=>'id',
            'localTableAlias'=>'grp',
            'foreignField'=>'group_id',
            'tableAlias'=>'vgr', 
            'criteria'=> \GO\Base\Db\FindCriteria::newInstance()
				->addCondition('view_id', $this->id,'=', 'vgr')
		));
        
		
        return Calendar::model()->find($findParams);
     }

	/**
	 * Here you can define the relations of this model with other models.
	 * See the parent class for a more detailed description of the relations.
	 */
	 public function relations() {
		 return array(
            'calendars' => array(
                'type' => self::MANY_MANY, 
                'model' => 'GO\Calendar\Model\Calendar',
                'linkModel'=>'GO\Calendar\Model\ViewCalendar',
                'field'=>'view_id', 
                'linksTable' => 'cal_views_calendars', 
                'remoteField'=>'calendar_id'
            ),
           'groups' => array(
               'type' => self::MANY_MANY, 
               'model' => 'GO\Calendar\Model\Calendar',
               'linkModel'=>'GO\Calendar\Model\ViewGroup',
               'field'=>'view_id', 
               'linksTable' => 'cal_views_groups', 
               'remoteField'=>'group_id'
            )
         );
	 }
}
