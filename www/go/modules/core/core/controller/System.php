<?php
namespace go\modules\core\core\controller;

use go\core\cli\Controller;
use go\core\db\Query;
use go\core\fs\Blob;
use go\core\util\DateTime;
use function GO;


class System extends Controller {
	
	public function garbageCollect() {
		$blobs = Blob::find()->where('staleAt', '<=', new DateTime())->execute();

		foreach($blobs as $blob)
		{
			if(!$blob->delete()) {
				throw new \Exception("Could not delete blob!");
			}
		}
		echo "Deleted ". $blobs->rowCount() . " stale blobs\n";
	}
	
	public function checkAllBlobs() {
		$blobs = Blob::find()->execute();
		
		echo "Processing: ".$blobs->rowCount() ." blobs\n";
		$staleCount = 0;
		foreach($blobs as $blob) {
			if($blob->setStaleIfUnused()) {
				echo 'D';
				$staleCount++;
			}else
			{
				echo '.';
			}
		}
		
		echo "\n\nFound " . $staleCount ." stale blobs\n";
	}
}
