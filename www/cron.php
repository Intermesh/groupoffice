#!/usr/bin/env php
<?php
/**
 * CRON script
 * Runs scheduled tasks for the system like garbage collection, sending of newsletters etc. *
 */
use GO\Base\Cron\CronJob;
use GO\Base\Db\FindCriteria;
use GO\Base\Db\FindParams;
use GO\Base\Util\Date\DateTime as DateTimeAlias;
use go\core\App;
use go\core\cli\State;
use go\core\model\CronJobSchedule;

if(!empty($argv[1])) {
	define('GO_CONFIG_FILE', $argv[1]);
}

require('GO.php');

App::get()->setAuthState(new State());
GO::session()->runAsRoot();

//new framework
CronJobSchedule::runNext();

//old framework
/**
 * @return mixed
 * @throws Exception
 */
function findNextCron(){
	$currentTime = new DateTimeAlias();

		$findParams = FindParams::newInstance()
			->single()
			->criteria(FindCriteria::newInstance()
				->addCondition('nextrun', $currentTime->getTimestamp(),'<')
				->addCondition('active',true)
			);
		
		return CronJob::model()->find($findParams);
}

$jobAvailable = false;
while($cronToHandle = findNextCron()){
	$jobAvailable = true;
	GO::debug('CRONJOB FOUND');
	$cronToHandle->run();
}

GO::config()->save_setting('cron_last_run', time());
