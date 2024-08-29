<?php

namespace go\modules\community\maildomains\cron;

use go\core\model\CronJob;
use go\core\model\CronJobSchedule;
use go\modules\community\maildomains\model;
use GO\Base\Util\Number;
final class CalculateUsage extends CronJob
{
	/**
	 * @param CronJobSchedule $schedule
	 * @return void
	 * @throws \Exception
	 */
	public function run(CronJobSchedule $schedule)
	{
		$mbs = model\Mailbox::find()->where(['active' => 1])->select(["maildir", "username", "domainId"])->all();

		foreach ($mbs as $mb) {
			$mb->cacheUsage();
			go()->debug('Folder size for ' . $mb->getMaildirFolder()->getPath() . ' is '. Number::formatSize($mb->usage));
		}
	}
}