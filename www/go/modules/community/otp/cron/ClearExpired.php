<?php

namespace go\modules\community\otp\cron;

use go\core\db\Query;
use go\core\model\CronJob;
use go\core\model\CronJobSchedule;
final class ClearExpired extends CronJob
{
	public function run(CronJobSchedule $schedule): void
	{
		$dt = new \DateTime();

		go()->getDbConnection()->delete('otp_secret',
			(new Query())->where('expiresAt',  '<=', $dt)
		)->execute();
	}
}