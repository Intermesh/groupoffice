<?php
namespace go\modules\community\multi_instance\cron;

use go\modules\core\core\model\CronJob;

class InstanceCron extends CronJob {
	
	public function run() {
		foreach(\go\modules\community\multi_instance\model\Instance::find() as $instance) {		
			GO()->debug("Running cron for ". $instance->getConfigFile()->getPath());
			exec("php " . \go\core\Environment::get()->getInstallFolder()->getFile('cron.php')->getPath() . ' ' . $instance->getConfigFile()->getPath());
		}
	}
}
