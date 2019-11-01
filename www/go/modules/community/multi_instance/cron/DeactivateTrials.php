<?php
namespace go\modules\community\multi_instance\cron;

use go\core\orm\Query;
use go\core\util\DateTime;
use go\modules\community\multi_instance\model\Instance;
use go\core\model\CronJob;

class DeactivateTrials extends CronJob {
	
	public function run() {
		$expiredTrials = Instance::find()		
						->selectSingleValue('id')
						->where('isTrial', '=', true)
						->andWhere('enabled', '=', true)
						->andWhere('createdAt', '<', new DateTime("-30 days"))
						->all();

		if(empty($expiredTrials)) {
			return true;
		}

		Instance::delete((new Query())->where('id', 'IN', $expiredTrials));
	}
}

