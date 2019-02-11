<?php
namespace go\core;

use Exception;
use GO;
use go\core\auth\Password;
use go\core\model;
use go\core\module\Base;

class Module extends Base {
	public function getAuthor() {
		return "Intermesh BV";
	}
	
	protected function afterInstall(model\Module $model) {
		
		$cron = new model\CronJobSchedule();
		$cron->moduleId = $model->id;
		$cron->name = "GarbageCollection";
		$cron->expression = "0 * * * *";
		$cron->description = "Garbage collection";
		
		if(!$cron->save()) {
			throw new Exception("Failed to save cron job: " . var_export($cron->getValidationErrors(), true));
		}
		
		GO()->getSettings()->setDefaultGroups([model\Group::ID_EVERYONE]);
		
		if(!Password::register()) {
			return false;
		}
		
		
		return parent::afterInstall($model);
	}
	
	public static function getName() {
		return "core";
	}
	
	public static function getPackage() {
		return "core";
	}
	
	public function getSettings() {		
		return model\Settings::get();
	}
}
