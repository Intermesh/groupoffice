<?php
namespace go\core\cli\controller;

use go\core\Controller;
use go\core\db\Query;
use go\core\db\Table;
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

	public function upgrade() {
		GO()->getInstaller()->isValidDb();
		GO()->setCache(new \go\core\cache\None());	
		Table::destroyInstances();
		\GO::session()->runAsRoot();	
		GO()->getInstaller()->upgrade();
		
		echo "Done!\n";
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
