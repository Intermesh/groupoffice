<?php


namespace GO\Files\Cron;


class RecalculateDiskUsage extends \GO\Base\Cron\AbstractCron {

	public function enableUserAndGroupSupport() {
		return false;
	}

	public function getLabel() {
		return "Recalculate user quota";
	}

	public function getDescription() {
		return "";
	}

	/**
	 * The code that needs to be called when the cron is running
	 * 
	 * If $this->enableUserAndGroupSupport() returns TRUE then the run function 
	 * will be called for each $user. (The $user parameter will be given)
	 * 
	 * If $this->enableUserAndGroupSupport() returns FALSE then the 
	 * $user parameter is null and the run function will be called only once.
	 * 
	 * @param \GO\Base\Cron\CronJob $cronJob
	 */
	public function run(\GO\Base\Cron\CronJob $cronJob) {
		$controller = new \GO\Files\Controller\FileController();
		$controller->run('RecalculateDiskUsage');
	}

}
