<?php

namespace GO\Tasks;

use go\core\model\User;
use go\core\orm\Mapping;
use go\core\orm\Property;

class TasksModule extends \GO\Base\Module {
	
	public static function defineListeners() {

		User::on(Property::EVENT_MAPPING, static::class, 'onMap');
	}
	
	public static function initListeners() {
		
		\GO\Base\Model\User::model()->addListener('delete', "GO\Tasks\TasksModule", "deleteUser"); // TODO: remove and put relation in the database

	}

	public static function onMap(Mapping $mapping) {
		$mapping->addHasOne('taskSettings', \GO\Tasks\Model\UserSettings::class, ['id' => 'user_id']);
	}
	
	public function autoInstall() {
		return true;
	}
	
	public static function submitSettings($settingsController, &$params, &$response, $user) {
		
		$settings = Model\Settings::model()->getDefault($user);		
		if($settings->remind = isset($params['remind'])) {
			$settings->reminder_days = $params['reminder_days'];
			$settings->reminder_time = $params['reminder_time'];
		}
		
		$settings->default_tasklist_id=$params['default_tasklist_id'];

		$settings->save();
		
		return parent::submitSettings($settingsController, $params, $response, $user);
	}
	
	public static function loadSettings($settingsController, &$params, &$response, $user) {
		
		$settings = Model\Settings::model()->getDefault($user);
		$response['data']=array_merge($response['data'], $settings->getAttributes());
		
		$tasklist = $settings->tasklist;
		
		if($tasklist) {
			$response['data']['default_tasklist_id']=$tasklist->id;
			$response['remoteComboTexts']['default_tasklist_id']=$tasklist->name;
		}
				
		//$response = Controller\Task::reminderSecondsToForm($response);
		
		return parent::loadSettings($settingsController, $params, $response, $user);
	}
	
	public static function deleteUser($user) {
		
		Model\PortletTasklist::model()->deleteByAttribute('user_id', $user->id);
	}
	
}
