<?php
/*
 * Copyright Intermesh BV.
 *
 * This file is part of Reminder-Office. You should have received a copy of the
 * Reminder-Office license along with Reminder-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 */

/**
 * 
 * The Reminder model
 * 
 * The Group-Office core will check for these reminders automatically.
 * 
 * @version $Id: Reminder.php 7607 2011-08-04 13:41:42Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package GO.base.model
 * @property string $text
 * @property boolean $manual
 * @property int $snooze_time
 * @property int $vtime This time will be displayed when showing the reminder
 * @property int $time This is the time the reminder will be displayed
 * @property string $name
 * @property int $user_id
 * @property int $model_type_id
 * @property int $model_id
 * @property int $id
 */

namespace GO\Base\Model;


class Reminder extends \GO\Base\Db\ActiveRecord {

	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return Reminder 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	protected function init() {

		$this->columns['text']['gotype']='html';
		$this->columns['time']['gotype']='unixtimestamp';
		$this->columns['vtime']['gotype']='unixtimestamp';
		
		parent::init();
	}
	
	public function relations() {
		
		return array('users' => array('type'=>self::MANY_MANY, 'model'=>'GO\Base\Model\User', 'field'=>'reminder_id', 'linkModel' => 'GO\Base\Model\ReminderUser'));
	}
	
	/**
	 * Create a new reminder
	 *
	 * @param StringHelper $name The text that will appear in the reminder
	 * @param int $time Unix timestamp
	 * @param StringHelper $model_name Optional model name and model id so that the reminder links to the dialog.
	 * @param int $model_id
	 * @param int $vtime The time that will be displayed in the reminder
	 * 
	 * @return Reminder 
	 */
	public static function newInstance($name, $time, $model_name='', $model_id=0, $vtime=null){
		$r = new Reminder();
		$r->name=	\GO\Base\Util\StringHelper::cut_string($name, 100);
		$r->time=$time;
		$r->vtime=$vtime;
		$r->model_type_id=\GO::getModel($model_name)->modelTypeId();
		$r->model_id=$model_id;
		$r->save();
		
		return $r;
	}
  
	public function tableName() {
		return 'go_reminders';
	}
	
	/**
	 * Add a user to the ACL with a permission level.
	 *  
	 * @param int $userId
	 * @param int $time Unix timestamp. Defaults to reminder time.
	 * @return bool True on success
	 */
	public function setForUser($userId, $time=0) {
		
		$usersReminder = $this->hasUser($userId);
		
		if(!$time)
			$time=$this->time;
		
		if($usersReminder){
			$usersReminder->time=$time;			
		}else
		{		
			$usersReminder = new ReminderUser();
			$usersReminder->reminder_id=$this->id;
			$usersReminder->user_id = $userId;
			$usersReminder->time = $time;
		}
		
		return $usersReminder->save();

	}
	
	/**
	 * Returns the links table model if the reminder has the user
	 * 
	 * @param int $userId
	 * @return ReminderUser 
	 */
	public function hasUser($userId){
		return ReminderUser::model()->findByPk(array(
				'reminder_id'=>$this->id,				
				'user_id'=>$userId
						));
	}
	
	public function hasUsers(){
		
		$params = \GO\Base\Db\FindParams::newInstance()
						->select('count(*) AS count')
						->single();
		
		$params->getCriteria()->addModel(ReminderUser::model())->addCondition('reminder_id', $this->id);
		
		$record = ReminderUser::model()->find($params);
		
		return $record->count > 0;
	}
	
	
	/**
	 * Remove a user from the reminder
	 * 
	 * @param int $userId
	 * @return bool 
	 */
	public function removeUser($userId) {

		$model = $this->hasUser($userId);
		if ($model) {
			if (!$model->delete()) {
				return false;
			}
		}

		$this->fireEvent('dismiss', array($this, $userId));

		//delete the reminder if it doesn't have users anymore.
		if (!$this->hasUsers()) {
			$this->delete();
		}
			
		return true;		
	}
	
	
	public function defaultAttributes() {
		return array(
			'snooze_time'=>7200,
			'time' => time()
		);
	}
	
	/**
	 * Get the model to which this reminder belongs. A reminder can belong to an Event or Task for example.
	 * 
	 * @return \GO\Base\Db\ActiveRecord|boolean 
	 */
	public function getRelatedModel(){
		
		if(!$this->model_type_id || !$this->model_id)
			return false;
		
		$modelType = ModelType::model()->findByPk($this->model_type_id);
		
		$model = \GO::getModel($modelType->model_name)->findByPk($this->model_id);
		
		return $model;		
	}
	
	
	public function findByModel($modelName, $id){
		$model_type_id = \GO::getModel($modelName)->modelTypeId();		
		
		return $this->find(\GO\Base\Db\FindParams::newInstance()
						->criteria(\GO\Base\Db\FindCriteria::newInstance()
										->addModel(Reminder::model())
										->addCondition('model_type_id', $model_type_id)
										->addCondition('model_id', $id)));
	}
	
	
//	public function getUsers($findParams=false){
//		$stmt = User::model()->find(\GO\Base\Db\FindParams::newInstance()
//						->mergeWith($findParams)
//						->order(array('first_name','last_name'),array('ASC','ASC'))
//						->criteria(\GO\Base\Db\FindCriteria::model()->addModel(ReminderUser::model(),'r')->addCondition('reminder_id',$this->id,'=','r'))
//						->join(ReminderUser::model()->tableName(), 
//										\GO\Base\Db\FindCriteria::model()->addModel(ReminderUser::model())
//														->addCondition('id','r.user_id','=','r',true,true)
//														
//											)
//						
//						);
//		
//		return $stmt;
//	}
  	
}
