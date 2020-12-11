<?php
namespace go\modules\community\multi_instance\cron;

use go\core\ErrorHandler;
use go\core\model\CronJob;

class InstanceCron extends CronJob {
	
	public function run() {

		//The server manager calls cron via HTTP because it doesn't know the document root when running
		//multiple versions of GO.v It passes ?exec=1 to make it run on the command line.
		$c = new \go\core\http\Client();
		foreach(\go\modules\community\multi_instance\model\Instance::find() as $instance) {		
			go()->debug("Running cron for ". $instance->getConfigFile()->getPath());
			$result = $c->get("http://" . $instance->hostname . '/cron.php?exec=1');
			if($result['status'] != 200) {
				ErrorHandler::log("Failed to run cron on instance " . $instance->hostname);
			}
			//exec("php " . \go\core\Environment::get()->getInstallFolder()->getFile('cron.php')->getPath() . ' ' . $instance->getConfigFile()->getPath());
		}
	}
}
