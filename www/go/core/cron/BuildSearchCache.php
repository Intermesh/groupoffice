<?php
namespace go\core\cron;

use Error;
use ErrorException;
use Exception;
use go\core\ErrorHandler;
use go\core\fs\Blob;
use go\core\model\CronJobSchedule;
use go\core\model\OauthAccessToken;
use go\core\util\DateTime;
use go\core\model\CronJob;
use function GO;
use go\core\db\Query;
use go\core\event\EventEmitterTrait;
use go\core\orm\EntityType;
use GO\Base\Db\ActiveRecord;
use go\core\model\Token;
use go\core\jmap\Entity;
use go\core\orm\Query as GoQuery;
use Throwable;

/**
 * This cron job cleans up garbage
 *
 * It should run once a day and it cleans:
 *
 * - BLOB storage
 * - core_change sync changelog
 *
 */
class BuildSearchCache extends CronJob {

	public function run(CronJobSchedule $schedule)
	{
		$schedule->enabled = false;
		if(!$schedule->save()) {
			throw new Exception($schedule->getValidationErrorsAsString());
		}

		ob_start();
		$c = new \GO\Core\Controller\MaintenanceController();
		$c->run("buildSearchCache", ['reset' => true]);
		$output = ob_get_clean();

		go()->getMailer()->compose()
			->setSubject("Build search cache complete")
			->setBody($output)
			->setTo(go()->getSettings()->systemEmail)
			->send();
	}
}