<?php

namespace go\modules\community\davclient\cron;

use go\core\model\CronJob;
use go\core\model\CronJobSchedule;
use go\modules\community\davclient\model;

class RefreshDav extends CronJob
{

	public function enableUserAndGroupSupport()
	{
		return false;
	}

	public function getLabel()
	{
		return \go()->t('Fetch data from caldav servers', 'davclient', 'community');
	}

	public function getDescription()
	{
		return \go()->t('Auto refresh', 'davclient', 'community');
	}

	public function run(CronJobSchedule $schedule)
	{

		go()->log("Refreshing data from remote caldav servers..");

		$accounts = model\DavAccount::find()->all();
		foreach($accounts as $account){
			if($account->needsSync()) {
				go()->log("Refreshing ". $account->name.'...');
				$account->sync();
			}
		}

	}
}