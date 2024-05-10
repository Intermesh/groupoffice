<?php

namespace go\modules\community\maildomains\cron;

use go\core\model\CronJob;
use go\core\model\CronJobSchedule;
use go\modules\community\maildomains\model;

final class CheckDns extends CronJob
{
	public function run(CronJobSchedule $schedule)
	{
		$mbs = model\Mailbox::find()->where(['active' => 1])->select(["domain", "dmarc", "mx", "spf", "dmarcStatus", "mxStatus", "spfStatus"])->all();
	}
}