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

//The server manager calls cron via HTTP because it doesn't know the document root when running
//multiple versions of GO.v It passes ?exec=1 to make it run on the command line.
if(!empty($_GET['exec'])) {
    $cmd = __FILE__ . " " . App::findConfigFile() . " &> /dev/null &";
    //echo $cmd . "\n";
	exec($cmd, $output, $result);
	if($result !==  0) {
	    throw new Exception("Failed to run CRON with command: " . $cmd );
    }
	exit();
}

App::get()->setAuthState(new State());
GO::session()->runAsRoot();

//for debugging
//go()->getDebugger()->output = true;

if(go()->getSettings()->databaseVersion != go()->getVersion()) {
    echo "Aborting CRON because an update is needed: " . go()->getSettings()->databaseVersion . " -> " . go()->getVersion() . "\n";
	exit();
}

$lock = new \go\core\util\Lock("cron");
if(!$lock->lock()) {
    \go\core\ErrorHandler::log("cron.php is locked (already running)");
    exit();
}


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
