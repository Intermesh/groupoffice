<?php
namespace go\core\cron;

use Exception;
use go\core\fs\Blob;
use go\core\util\DateTime;
use go\core\model\CronJob;
use function GO;

/**
 * This cron job cleans up garbage
 * 
 * At the moment this is just unused BLOB's
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
	}
}

