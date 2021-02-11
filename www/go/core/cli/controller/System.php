<?php
namespace go\core\cli\controller;

use go\core\cache\None;
use go\core\Controller;
use go\core\db\Query;
use go\core\db\Table;
use go\core\db\Utils;
use go\core\fs\Blob;
use go\core\fs\File;
use go\core\util\DateTime;
use function GO;


class System extends Controller {
	
	/**
	 * docker-compose exec --user www-data groupoffice-master php /usr/local/share/groupoffice/cli.php core/System/runCron --module=ldapauthenticatior --package=community --name=Sync
	 */
	public function runCron($name, $module = "core", $package = "core") {
		$cls = $package == "core" ?
			"go\\core\\cron\\".$name : 
			"go\\modules\\" . $package ."\\".$module."\\cron\\".$name;
		
		$o = new $cls;
		$o->run();
	}

	/**
	 * docker-compose exec --user www-data groupoffice-master php ./www/cli.php core/System/upgrade
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


	/**
	 *  docker-compose exec --user www-data groupoffice-64 php ./www/cli.php core/System/cleanup
	 */
	public function cleanup() {

		echo "Cleaning up....\n";
		Utils::runSQLFile(new File(__DIR__ . '/cleanup.sql'), true);

		$unknown = $this->getUnknownTables();

		if(count($unknown)) {
			echo "Some unknown tables where found. Please consider removing these:\n\n";

			foreach ($unknown as $table) {
				echo "DROP TABLE `" . $table->getName() . "`;\n";
			}

			echo "\n\n---\n\n";
		}

	}

	private function getUnknownTables() {
		$sqls = go()->getEnvironment()->getInstallFolder()->find('/.*\.sql/', false, true);
		$installSql = "";
		foreach($sqls as $s) {
			$installSql .= $s->getContents() ."\n\n";
		}
		$unknown = [];

		foreach(go()->getDatabase()->getTables() as $table) {
			if(strstr($table->getName(), 'core_customfields_multiselect') === false && strstr($installSql, $table->getName()) === false) {
				$unknown[] = $table;
			}
		}

		return $unknown;
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
