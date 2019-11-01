<?php
namespace go\modules\community\multi_instance\cron;

use go\core\util\DateTime;
use go\modules\community\multi_instance\model\Instance;
use go\core\model\CronJob;

class DeactivateTrials extends CronJob {
	
	public function run() {
		$expiredTrials = Instance::find()
						->selectSingleValue('id')
						->where('isTrial', '=', true)
						->andWhere('enabled', '=', true)
						->andWhere('createdAt', '<', new DateTime("-30 days"));

		Instance::delete(['id' => $expiredTrials]);
	}
}

