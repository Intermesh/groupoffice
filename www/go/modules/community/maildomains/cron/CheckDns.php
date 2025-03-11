<?php

namespace go\modules\community\maildomains\cron;

use go\core\model\CronJob;
use go\core\model\CronJobSchedule;
use go\modules\community\maildomains\model;
use go\modules\community\maildomains\util;

/**
 * docker compose exec --user www-data groupoffice-develop ./www/cli.php core/System/runCron --module=maildomains --package=community --name=CheckDns
 */
final class CheckDns extends CronJob
{
	/**
	 *
	 *
	 * @throws \Exception
	 */
	public function run(CronJobSchedule $schedule)
	{
		$mailDomains = model\Domain::find()->where(['active' => 1])->all();
		foreach($mailDomains as $d) {
			$d->checkDns();
			$d->save();
		}
	}
}