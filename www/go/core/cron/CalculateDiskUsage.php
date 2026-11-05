<?php

namespace go\core\cron;

use go\core\db\DbException;
use go\core\exception\Forbidden;
use go\core\fs\Folder;
use go\core\model\CronJob;
use go\core\model\CronJobSchedule;
use Exception;
use go\core\model\Module;
use go\modules\community\serverclient\model\MailDomain;

final class CalculateDiskUsage extends CronJob
{

	/**
	 * @throws DbException
	 * @throws Forbidden
	 * @throws Exception
	 */
	public function run(CronJobSchedule $schedule): void
	{
		$conn = go()->getDbConnection();
		$stmt = $conn->query('SHOW TABLE STATUS FROM `' . go()->getConfig()['db_name'] . '`');
		$database_usage = 0;
		while ($r = $stmt->fetch()) {
			$database_usage += $r['Data_length'] + $r['Index_length'];
		}
		go()->getSettings()->databaseUsage = $database_usage;

		$conn->disconnect(); //

		$fileStorageFolder = new Folder(go()->getConfig()['file_storage_path']);
		$fileStorageSize = $fileStorageFolder->calculateSize();
		go()->getSettings()->fileStorageUsage = $fileStorageSize;


		if (Module::isInstalled("community", "serverclient", true)) {
			$domains = \go\modules\community\serverclient\Module::getDomains();
			if (!empty($domains)) {
				$d = new MailDomain("");
				$usage = $d->getUsage($domains);
				go()->getSettings()->mailboxUsage = $usage;
			}
		} elseif (Module::isInstalled("community", "maildomains", true)) {
			$result = go()->getDbConnection()->query("SELECT SUM('bytes') as 'usage' FROM `community_maildomains_mailbox`")->fetch();
			$usage = $result ? $result['usage'] : 0;
			go()->getSettings()->mailboxUsage = $usage;
		} elseif (Module::isInstalled("community", "postfixadmin", true)) {
			// TODO: Are we still supporting this?
			$result = go()->getDbConnection()->query("SELECT SUM('usage') as 'usage' FROM `pa_mailboxes`")->fetch();
			$usage = $result ? $result['usage'] : 0;
			go()->getSettings()->mailboxUsage = $usage;
		}

		go()->getSettings()->save();
	}
}