<?php
namespace go\modules\community\multi_instance\cron;

use go\core\util\DateTime;
use go\modules\community\multi_instance\model\Instance;
use go\core\model\CronJob;

class DeactivateTrials extends CronJob {
	
	public function run() {
		$expiredTrials = Instance::find()
						->where('isTrial', '=', true)
						->andWhere('enabled', '=', true)
						->andWhere('createdAt', '<', new DateTime("-30 days"));
		
		foreach($expiredTrials as $trial) {
//			$trial->enabled = false;
//			$trial->isTrial = false;
//			if(!$trial->save()) {
//				throw new \Exception("Could not deactivate trial");
//			}
			$trial->delete();
		}
	}
}

