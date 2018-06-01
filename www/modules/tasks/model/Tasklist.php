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
 * The Tasklist model
 *
 * @package GO.modules.Tasks
 * @version $Id: Tasklist.php 7607 2011-09-20 10:07:07Z <<USERNAME>> $
 * @copyright Copyright Intermesh BV.
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 *
 * @property int $id
 * @property String $name
 * @property int $user_id
 * @property int $acl_id
 * @property int $files_folder_id
 */


namespace GO\Tasks\Model;


class Tasklist extends \GO\Base\Model\AbstractUserDefaultModel {

	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return Tasklist 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	static public function versionUp($id) {
		return \GO::$db->exec('UPDATE ta_tasklists SET version = version + 1 WHERE id = '.(int)$id);
	}
	
	public function settingsModelName() {
		return "GO\Tasks\Model\Settings";
	}
	
	public function settingsPkAttribute() {
		return 'default_tasklist_id';
	}

	public function tableName() {
		return 'ta_tasklists';
	}

	public function aclField() {
		return 'acl_id';
	}
	
	public function relations() {
		return array(
				'tasks' => array('type' => self::HAS_MANY, 'model' => 'GO\Tasks\Model\Task', 'field' => 'tasklist_id', 'delete' => true),
				);
	}
	
	public function hasFiles(){
		return true;
	}
	
	/**
	 * Remove all tasks
	 */
	public function truncate(){
			
		$tasks = $this->tasks;
		
		foreach($tasks as $task){
			$task->delete();
		}
	}
	
	/**
	 * 
	 * @param \GO\Base\Model\User $user
	 * @return Tasklist
	 */
	public function getDefault(\GO\Base\Model\User $user, &$createdNew=false) {
		$default = parent::getDefault($user, $createdNew);
	
		if($createdNew){
			$pt = new PortletTasklist();
			$pt->user_id=$user->id;
			$pt->tasklist_id=$default->id;
			$pt->save();
		}
	
		return $default;
	}
}
