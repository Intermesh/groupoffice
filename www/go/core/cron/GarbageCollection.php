<?php
namespace go\core\cron;

use Exception;
use go\core\fs\Blob;
use go\core\util\DateTime;
use go\core\model\CronJob;
use function GO;
use go\core\db\Query;

/**
 * This cron job cleans up garbage
 * 
 * It should run once a day and it cleans:
 * 
 * - BLOB storage
 * - core_change sync changelog
 * 
 */
class GarbageCollection extends CronJob {
	
	public function run() {
		$blobs = Blob::find()->where('staleAt', '<=', new DateTime())->execute();

		foreach($blobs as $blob)
		{
			if(!$blob->delete()) {
				throw new Exception("Could not delete blob!");
			}
		}		
			
		GO()->debug("Deleted ". $blobs->rowCount() . " stale blobs");

		$date = new DateTime();
		$date->modify('-' .GO()->getSettings()->syncChangesMaxAge.' days');

		GO()->getDbConnection()->delete('core_change', (new Query)->where('createdAt', '<', $date))->execute();
	}
}

