<?php
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

//new framework
\go\modules\core\core\model\CronJobSchedule::runNext();


GO()->debug("Running cron for legacy framework");

//old framework
function findNextCron(){
	$currentTime = new \GO\Base\Util\Date\DateTime();

		$findParams = \GO\Base\Db\FindParams::newInstance()
			->single()
			->criteria(\GO\Base\Db\FindCriteria::newInstance()
				->addCondition('nextrun', $currentTime->getTimestamp(),'<')
				->addCondition('active',true)
			);
		
		return \GO\Base\Cron\CronJob::model()->find($findParams);
}

$jobAvailable = false;
\GO::debug('CRONJOB START (PID:'.getmypid().')');
while($cronToHandle = findNextCron()){
	$jobAvailable = true;
	\GO::debug('CRONJOB FOUND');
	$cronToHandle->run();
}

if(!$jobAvailable)
	\GO::debug('NO CRONJOB FOUND');

\GO::debug('CRONJOB STOP (PID:'.getmypid().')');
\GO::config()->save_setting('cron_last_run', time());





