<?php
namespace go\modules\community\multi_instance\cron;

use go\core\model\CronJob;

class InstanceCron extends CronJob {
	
	public function run(\go\core\model\CronJobSchedule $schedule) {
		foreach(\go\modules\community\multi_instance\model\Instance::find() as $instance) {		
			go()->debug("Running cron for ". $instance->getConfigFile()->getPath());
			exec("php " . \go\core\Environment::get()->getInstallFolder()->getFile('cron.php')->getPath() . ' ' . $instance->getConfigFile()->getPath());
		}
	}
}
