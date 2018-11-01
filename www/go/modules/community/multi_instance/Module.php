<?php
namespace go\modules\community\multi_instance;

class Module extends \go\core\module\Base {
	
	public function getAuthor() {
		return "Intermesh BV";
	}

	protected function afterInstall(\go\modules\core\modules\model\Module $model) {
		
		$cron = new \go\modules\core\core\model\CronJobSchedule();
		$cron->moduleId = $model->id;
		$cron->name = "InstanceCron";
		$cron->expression = "* * * * *";
		$cron->description = "Cron for instances";
		
		if(!$cron->save()) {
			throw new \Exception("Failed to save cron job: " . var_export($cron->getValidationErrors(), true));
		}
		
		return parent::afterInstall($model);
	}
}
