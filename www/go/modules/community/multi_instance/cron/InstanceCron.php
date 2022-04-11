<?php
namespace go\modules\community\multi_instance\cron;

use go\core\ErrorHandler;
use go\core\http\Client;
use go\core\model\CronJob;
use go\core\model\CronJobSchedule;
use go\modules\community\multi_instance\model\Instance;
use Throwable;

class InstanceCron extends CronJob {
	
	public function run(CronJobSchedule $schedule) {
		//The server manager calls cron via HTTP because it doesn't know the document root when running
		//multiple versions of GO.v It passes ?exec=1 to make it run on the command line.
		$c = new Client();
		$c->setOption(CURLOPT_CONNECTTIMEOUT, 360);
		$c->setOption(CURLOPT_TIMEOUT, 360);

		foreach(Instance::find() as $instance) {
			try {
				if (!$instance->isInstalled()) {
					go()->debug("NOT INSTALLED: " . $instance->hostname);
					continue;
				}

				go()->debug("Running cron for " . $instance->getConfigFile()->getPath());

				$result = $c->get("http://" . $instance->hostname . '/cron.php?exec=1');
				if ($result['status'] != 200) {
					ErrorHandler::log("Failed to run cron on instance " . $instance->hostname . ". HTTP Status: " . $result['status']);
				}

			} catch(Throwable $e)  {
				ErrorHandler::log("Failed to run cron on instance " . $instance->hostname . ". Error: " . $e->getMessage() );
			}
		}
	}
}
