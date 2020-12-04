<?php
namespace go\core\cli\controller;

use go\core\cache\None;
use go\core\Controller;
use go\core\db\Query;
use go\core\db\Table;
use go\core\fs\Blob;
use go\core\model\CronJobSchedule;
use go\core\model\Module;
use go\core\util\DateTime;
use function GO;


class System extends Controller {
	
	/**
	 * docker-compose exec --user www-data groupoffice-master php /usr/local/share/groupoffice/cli.php core/System/runCron --module=ldapauthenticatior --package=community --name=Sync
	 */
	public function runCron($name, $module = "core", $package = "core") {

		$module = Module::findByName($package, $module);

		$schedule = new CronJobSchedule();
		$schedule->moduleId =$module->id;
		$schedule->name = $name;
		$schedule->expression = "* * * * *";

		$cls = $schedule->getCronClass();
		
		$o = new $cls;
		$o->run($schedule);
	}

	/**
	 * docker-compose exec --user www-data groupoffice-master php /usr/local/share/groupoffice/cli.php core/System/upgrade
	 */
	public function upgrade() {

		go()->setCache(new None());

		go()->getInstaller()->isValidDb();
		go()->setCache(new \go\core\cache\None());	
		Table::destroyInstances();
		\GO::session()->runAsRoot();	
		date_default_timezone_set("UTC");
		go()->getInstaller()->upgrade();
		
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
