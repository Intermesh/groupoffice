<?php
namespace go\core\controller;

use go\core\Controller;
use go\core\db\Query;
use go\core\fs\Blob;
use go\core\util\DateTime;
use function GO;


class System extends Controller {
	
	public function runCron($name, $module = "core", $package = "core") {
		$cls = $package == "core" ?
			"go\\core\\cron\\".$name : 
			"go\\modules\\" . $package ."\\".$module."\\cron\\".$name;
		
		$o = new $cls;
		$o->run();
	}
	
	// public function checkAllBlobs() {
	// 	$blobs = Blob::find()->execute();
		
	// 	echo "Processing: ".$blobs->rowCount() ." blobs\n";
	// 	$staleCount = 0;
	// 	foreach($blobs as $blob) {
	// 		if($blob->setStaleIfUnused()) {
	// 			echo 'D';
	// 			$staleCount++;
	// 		}else
	// 		{
	// 			echo '.';
	// 		}
	// 	}
		
	// 	echo "\n\nFound " . $staleCount ." stale blobs\n";
	// }
}
