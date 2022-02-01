<?php
namespace go\core\cli\controller;

use Exception;
use GO\Base\Observable;
use go\core\cache\None;
use go\core\Controller;
use go\core\db\Table;
use go\core\db\Utils;
use go\core\event\EventEmitterTrait;
use go\core\exception\Forbidden;
use go\core\exception\NotFound;
use go\core\fs\File;
use go\core\jmap\Entity;
use go\core\model\Alert;
use go\core\http\Client;
use go\core\model\CronJobSchedule;
use go\core\event\Listeners;
use go\core\model\Module;
use Faker;


use go\core\orm\EntityType;
use go\modules\community\history\Module as HistoryModule;
use function GO;

class System extends Controller {

	use EventEmitterTrait;

	const EVENT_CLEANUP = 'cleanup';


	/**
	 * @throws NotFound
	 */
	public function resetSyncState(string $entity = null) {
		if(!isset($entity)) {
			EntityType::resetAllSyncState();
		} else{
			$et = EntityType::findByName($entity);
			if(!$et) {
				throw new NotFound("Entity '$entity' not found");
			}
			$et->resetSyncState();
		}

		echo "Reset done!\n";
	}

	/**
	 * docker-compose exec --user www-data groupoffice ./www/cli.php core/System/runCron --module=ldapauthenticatior --package=community --name=Sync
	 *
	 * docker-compose exec --user www-data groupoffice ./www/cli.php core/System/runCron --module=contracts --package=business --name=CreateInvoices
	 */
	public function runCron($name, $module = "core", $package = "core") {

		$module = Module::findByName($package, $module);

		$schedule = new CronJobSchedule();
		$schedule->moduleId =$module->id;
		$schedule->name = $name;
		$schedule->expression = "* * * * *";
		$schedule->description = "Temporary CLI job " . uniqid();

		$cls = $schedule->getCronClass();

		try {
			$o = new $cls;
			$o->run($schedule);
		} finally {
			CronJobSchedule::delete($schedule->primaryKeyValues());
		}

	}

	/**
	 * docker-compose exec --user www-data groupoffice-master php ./www/cli.php core/System/upgrade
	 * @throws Exception
	 */
	public function upgrade() {

		Observable::cacheListeners();
		Listeners::get()->init();

		go()->getInstaller()->isValidDb();
		Table::destroyInstances();
		\GO::session()->runAsRoot();	
		date_default_timezone_set("UTC");
		go()->getInstaller()->upgrade();

		try {
			$http = new Client();
			$http->setOption(CURLOPT_SSL_VERIFYHOST, false);
			$http->setOption(CURLOPT_SSL_VERIFYPEER, false);

			$response = $http->get(go()->getSettings()->URL . '/install/clearcache.php');
			if($response['status'] != 200) {
				echo "Failed to clear cache. Please run: '" .go()->getSettings()->URL . "install/' in the browser.\n";
			} else{
				echo "Cache cleared via webserver\n";
			}
		} catch(Exception $e) {
			echo "Failed to clear cache. Please run: '" .go()->getSettings()->URL . "install/' in the browser.\n";
		}

		echo "Done!\n";
	}


	/**
	 *  docker-compose exec --user www-data groupoffice php ./www/cli.php core/System/cleanup
	 */
	public function cleanup() {

		echo "This script will delete unused data from your database.\n".
		 "Please confirm with 'y' that you have made a BACKUP and you wish to continue [y/N].\n";
		$confirm = trim(fgets(STDIN));     // Read the input
		if($confirm != "y") {
			echo "Aborted. $confirm\n";
			exit();
		}

		echo "Cleaning up....\n";
		Utils::runSQLFile(new File(__DIR__ . '/cleanup.sql'), true);

		$this->cleanupAcls();

		$this->fireEvent(self::EVENT_CLEANUP);

		$this->reportUnknownTables();

	}

	private function cleanupAcls() {

		// for memory problems
		go()->getDebugger()->disabled = false;

		echo "Cleaning up unused ACL's\n";

//		go()->getDatabase()->getTable('core_acl')->backup();
//		go()->getDatabase()->getTable('core_acl_group')->backup();

		go()->getDbConnection()->exec("update core_acl set usedIn = null, entityTypeId = null, entityId = null");
		go()->getDbConnection()->exec("update core_acl set usedIn = 'core_entity.defaultAclId' where id in (select defaultAclId from core_entity)");


		echo "Checking database\n";

		$modules = Module::find();

		foreach($modules as $module) {
			if(!$module->isAvailable()) {
				continue;
			}
			echo "Checking module ". ($modules->package ?? "legacy") . "/" .$module->name ."\n";
			$module->module()->checkAcls();
		}

		echo "\n\n";

		//hack for folders which are skipped in the checkDatabase
		go()->getDbConnection()->exec(
			"update core_acl a inner join fs_folders f on f.acl_id = a.id set usedIn = 'fs_folders.acl_id', entityTypeId = ". \GO\Files\Model\Folder::entityType()->getId() .
			", entityId = f.id where usedIn is null"
		);

	//	$deleteCount = go()->getDbConnection()->exec("delete from core_acl where usedIn is null");

		//echo "Delete " . $deleteCount ." unused ACL's\n";

	}

	private function reportUnknownTables(){
		$unknown = $this->findUnknownTables();

		if(count($unknown)) {
			echo "Some unknown tables where found. Please consider removing these:\n\n";

			foreach ($unknown as $table) {
				echo "DROP TABLE `" . $table->getName() . "`;\n";
			}

			echo "\n\n---\n\n";
		}
	}

	/**
	 * Finds tables not present in any of the install.sql files.
	 *
	 * @return array
	 */
	private function findUnknownTables() {
		$sqls = go()->getEnvironment()->getInstallFolder()->find('/.*\.sql/', false, true);
		$installSql = "";
		foreach($sqls as $s) {
			$installSql .= $s->getContents() ."\n\n";
		}
		$unknown = [];

		foreach(go()->getDatabase()->getTables() as $table) {
			//Custom fields create tables for multiselect
			if(strstr($table->getName(), 'core_customfields_multiselect') === false && strstr($installSql, $table->getName()) === false) {
				$unknown[] = $table;
			}
		}

		return $unknown;
	}


	/**
	 * Generates demo data
	 *
	 * @return void
	 * @throws Forbidden
	 * @example
	 * ```
	 * docker-compose exec --user www-data groupoffice-tasks ./www/cli.php core/System/demo
	 * ```
	 */
	public function demo() {

		$faker = Faker\Factory::create();

		Entity::$trackChanges = false;
		HistoryModule::$enabled = false;
		Alert::$enabled = false;

		$modules = Module::find();
//		$modules = [Module::findByName("community", "tasks")];

		foreach($modules as $module) {
			if(!$module->isAvailable()) {
				continue;
			}
			echo "Creating demo for module ". ($module->package ?? "legacy") . "/" .$module->name ."\n";
			$module->module()->demo($faker);

			echo "\n\nDone\n\n";
		}

		go()->getSettings()->demoDataAsked = true;
		go()->getSettings()->save();

		// for resyncing
		go()->rebuildCache();

		Entity::$trackChanges = true;
		HistoryModule::$enabled = true;
		Alert::$enabled = true;

		echo "\n\nAll done!\n\n";
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
