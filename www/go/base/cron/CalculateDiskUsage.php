<?php

namespace GO\Base\Cron;


use go\core\ErrorHandler;
use go\modules\community\serverclient\model\MailDomain;
use go\modules\community\serverclient\Module;

class CalculateDiskUsage extends AbstractCron {
	
	/**
	 * Return true or false to enable the selection for users and groups for 
	 * this cronjob.
	 * 
	 * CAUTION: This will give the run() function a different behaviour. 
	 *					Please see the documentation of the run() function 
	 *					to see what is different.
	 */
	public function enableUserAndGroupSupport(){
		return false;
	}
	
	/**
	 * Get the unique name of the Cronjob
	 * 
	 * @return String
	 */
	public function getLabel(){
		return "Calculate disk usage";
	}
	
	/**
	 * Get the unique name of the Cronjob
	 * 
	 * @return String
	 */
	public function getDescription(){
		return "Calculate total disk usage to display in the about dialog.";
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
	 * @param CronJob $cronJob
	 */
	public function run(CronJob $cronJob){
		$stmt =\GO::getDbConnection()->query("SHOW TABLE STATUS FROM `".\GO::config()->db_name."`;");

		$database_usage=0;
		while($r=$stmt->fetch()){
			$database_usage+=$r['Data_length'];
			$database_usage+=$r['Index_length'];
		}
		
		\GO::config()->save_setting('database_usage', $database_usage);
		
		$folder = new \GO\Base\Fs\Folder(\GO::config()->file_storage_path);
		//disconnect mysql as it can take a long time to calculate
		go()->getDbConnection()->disconnect();

		$size = $folder->calculateSize();
		\GO::config()->save_setting('file_storage_usage', $size);

		if(\GO::modules()->serverclient) {
			$domains = Module::getDomains();
			if(!empty($domains)) {
				$maildomain = new MailDomain("");

				try {
					$usage = $maildomain->getUsage($domains);
					\GO::config()->save_setting('mailbox_usage', $usage);
				} catch(\Exception $e) {
					ErrorHandler::logException($e);
				}
			}
		} else if(\GO::modules()->postfixadmin){
			
			$findParams = \GO\Base\Db\FindParams::newInstance()
							->select('sum(`usage`) AS `usage`')
							->ignoreAcl()
							->single();
			
			$result = \GO\Postfixadmin\Model\Mailbox::model()->find($findParams);

			\GO::config()->save_setting('mailbox_usage', $result->usage*1024);
		} else {
			\GO::config()->save_setting('mailbox_usage', 0);
    }

	}
	
}
