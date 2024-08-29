<?php

namespace go\modules\community\maildomains\cron;

use go\core\model\CronJob;
use go\core\model\CronJobSchedule;
use go\modules\community\maildomains\model;
use go\modules\community\maildomains\util;

final class CheckDns extends CronJob
{
	/**
	 * @throws \Exception
	 */
	public function run(CronJobSchedule $schedule)
	{
		$ip = go()->getConfig()['serverclient_server_ip'] ?? '127.0.0.1';
		$mailDomains = model\Domain::find()->where(['active' => 1])->all();
		foreach($mailDomains as $d) {
			$d->checkDns();
			$d->save();
		}
	}
}