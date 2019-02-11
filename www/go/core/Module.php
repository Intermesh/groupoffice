<?php
namespace go\core;

use go\core\module\Base;

class Module extends Base {
	public function getAuthor() {
		return "Intermesh BV";
	}
	
	protected function afterInstall(\go\modules\core\modules\model\Module $model) {
		
		$cron = new \go\core\model\CronJobSchedule();
		$cron->moduleId = $model->id;
		$cron->name = "GarbageCollection";
		$cron->expression = "0 * * * *";
		$cron->description = "Garbage collection";
		
		if(!$cron->save()) {
			throw new \Exception("Failed to save cron job: " . var_export($cron->getValidationErrors(), true));
		}
		
		return parent::afterInstall($model);
	}
	
	public static function getName() {
		return "core";
	}
	
	public static function getPackage() {
		return "core";
	}
	
	public function getSettings(): \go\core\Settings {
		if($this->getPackage()) {
			
		}
		return \go\core\model\Settings::get();
	}
}
