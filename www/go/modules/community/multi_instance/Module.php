<?php
namespace go\modules\community\multi_instance;

use go\core\Installer;
use go\modules\community\multi_instance\model\Instance;

class Module extends \go\core\Module {
	
	public function getAuthor() {
		return "Intermesh BV";
	}

	protected function afterInstall(\go\core\model\Module $model) {
		
		$cron = new \go\core\model\CronJobSchedule();
		$cron->moduleId = $model->id;
		$cron->name = "InstanceCron";
		$cron->expression = "* * * * *";
		$cron->description = "Cron for instances";
		
		if(!$cron->save()) {
			throw new \Exception("Failed to save cron job: " . var_export($cron->getValidationErrors(), true));
		}
		
		
		$cron = new \go\core\model\CronJobSchedule();
		$cron->moduleId = $model->id;
		$cron->name = "DeactivateTrials";
		$cron->expression = "0 10 * * *";
		$cron->description = "Deactivate trials";
		
		if(!$cron->save()) {
			throw new \Exception("Failed to save cron job: " . var_export($cron->getValidationErrors(), true));
		}
		
		return parent::afterInstall($model);
	}

	public function defineListeners()
	{
		parent::defineListeners();

		GO()->getInstaller()->on(Installer::EVENT_UPGRADE, static::class, 'upgradeInstances');
	}

	public static function upgradeInstances() {

		echo "\nUpgrading all instances\n";
		echo "-------------------------------\n\n";

		foreach(Instance::find() as $instance) {
			echo "Upgrading instance: " . $instance->hostname . ": ";
			flush();
			$success = $instance->upgrade();

			echo $success ? "SUCCESS" : "FAILED";

			echo "\n";
			
		}
	}
}
