<?php
namespace go\core\cli\controller;

use Exception;
use go\core\Controller;
use go\core\db\Column;
use go\core\db\Utils;
use go\core\event\EventEmitterTrait;
use go\core\exception\Forbidden;
use go\core\exception\NotFound;
use go\core\fs\Blob;
use go\core\fs\File;
use go\core\jmap\Entity;
use go\core\jmap\Response;
use go\core\jmap\Router;
use go\core\model\Acl;
use go\core\model\Alert;
use go\core\http\Client;
use go\core\model\Alert as CoreAlert;
use go\core\model\CronJobSchedule;
use go\core\model\Module;
use Faker;
use go\core\model\User;
use go\core\orm\EntityType;
use go\core\orm\exception\SaveException;
use go\core\util\DateTime;
use go\core\util\JSON;
use go\core\util\PdfRenderer;
use go\modules\business\license\model\License;
use go\modules\community\history\Module as HistoryModule;
use JsonException;
use function GO;

class System extends Controller {

	use EventEmitterTrait;

	const EVENT_CLEANUP = 'cleanup';
	/**
	 * @var File[]|\go\core\fs\Folder[]
	 */
	private $installSqls;

	protected function authenticate()
	{
		// no auth because on upgrade it might fail and it's not needed on CLI anyway
	}


	/**
	 *
	 * docker-compose exec --user www-data groupoffice ./www/cli.php  core/System/addPDFFont --file=/root/Downloads/Lato/Lato-Regular.ttf
	 * @param string $file
	 * @return void
	 */
	public function addPDFFont($params) {

		$f = new File($params['file']);
		if(!$f->exists()) {
			throw new NotFound($f->getPath());
		}

		// convert TTF font to TCPDF format and store it on the fonts folder
		$result = PdfRenderer::addTTFFont($params['file']);


		var_dump($result);
	}


	/**
	 * @throws Exception
	 * @throws JsonException
	 */
	public function jmap() {
		stream_set_blocking(STDIN, 0);
		$data = stream_get_contents(STDIN);
		$requests = JSON::decode($data, true);

		Response::get()->jsonOptions = JSON_PRETTY_PRINT;

		$router = new Router();
		$router->run($requests);
	}

	/**
	 * docker-compose exec --user www-data groupoffice ./www/cli.php  core/System/deleteGroup --id=29
	 */
	public function deleteGroup($params) {
		$json = <<<JSON
[
  [
    "Group/set", {
      "destroy": [{$params['id']}]
    },
    "call-1"
  ]
]
JSON;

		$requests = JSON::decode($json, true);

		Response::get()->jsonOptions = JSON_PRETTY_PRINT;

		$router = new Router();
		$router->run($requests);

	}

	/**
	 * docker-compose exec --user www-data groupoffice ./www/cli.php  core/System/deleteUser --id=1
	 */
	public function deleteUser($params) {
		$json = <<<JSON
[
  [
    "User/set", {
      "destroy": [{$params['id']}]
    },
    "call-1"
  ]
]
JSON;

		$requests = JSON::decode($json, true);

		Response::get()->jsonOptions = JSON_PRETTY_PRINT;

		$router = new Router();
		$router->run($requests);
	}

	/**
	 * @throws NotFound
	 */
	public function resetSyncState($params) {
		if(!isset($params['entity'])) {
			EntityType::resetAllSyncState();
		} else{
			$et = EntityType::findByName($params['entity']);
			if(!$et) {
				throw new NotFound("Entity '{$params['entity']}' not found");
			}
			$et->resetSyncState();
		}

		echo "Reset done!\n";
	}

	/**
	 * docker-compose exec --user www-data groupoffice ./www/cli.php core/System/runCron --module=ldapauthenticator --package=community --name=Sync
	 *
	 * docker-compose exec --user www-data groupoffice ./www/cli.php core/System/runCron --module=contracts --package=business --name=CreateInvoices
	 *
	 * docker-compose exec --user www-data groupoffice ./www/cli.php core/System/runCron --module=core --package=core --name=GarbageCollection
	 * @throws NotFound
	 */
	public function runCron($params) {

        $name = $params['name'];
        $module = $params['module'] ?? 'core';
		$package = $params['package'] ?? 'core';

		$mod = Module::findByName($package, $module);
		if(!$mod) {
			throw new NotFound("Module '$package/$module' not found");
		}

		$schedule = new CronJobSchedule();
		$schedule->moduleId =$mod->id;
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
//WHy was this needed? It made 6.5 upgrad fail.
//		Observable::cacheListeners();
//		Listeners::get()->init();

		go()->getInstaller()->isValidDb();
		go()->getDatabase()->clearCache();
		\GO::session()->runAsRoot();	
		date_default_timezone_set("UTC");
		go()->getInstaller()->upgrade();

		$this->clearCache();

		echo "Done!\n";
	}


	public function clearCache() {
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

//		if(Module::isInstalled("legacy", "files")) {
//			$this->cleanupEmptyFolders();
//		}

		$this->cleanupAcls();

		$this->fireEvent(self::EVENT_CLEANUP);

		$this->reportUnknownTables();

	}

	private function cleanupEmptyFolders() {
		echo "Removing empty folders\n";
		$fc = new \GO\Files\Controller\FolderController();
		$fc->actionRemoveEmpty();
	}

	private function cleanupAcls() {
		echo "Cleaning up unused ACL's\n";
		CoreAlert::$enabled = false;

		// Speed things up.
		Entity::$trackChanges = false;

		\go\modules\community\history\Module::$enabled = false;
		Acl::delete(Acl::findStale());
		Acl::$lastDeleteStmt->rowCount();


		echo "Delete " . Acl::$lastDeleteStmt->rowCount() ." unused ACL's\n";

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

	public function checkLicense() {
		$key = go()->getSettings()->license;

		if(empty($key)) {
			echo "No license key installed\n";
		}

		echo "Key: " . $key ."\n\n";

		$data = License::getLicenseData();

		print_r($data);

		echo "----\n";
	}


	public function setLicense($params) {
		if(!isset($params['key'])) {
			throw new \InvalidArgumentException("Parameter 'key' is required");
		}

		go()->getSettings()->license = $params['key'];
		go()->getSettings()->save();

		$this->checkLicense();
	}


	/**
	 * Generates demo data
	 *
	 * @return void
	 * @throws Forbidden
	 * @example
	 * ```
	 * docker-compose exec --user www-data groupoffice ./www/cli.php core/System/demo
	 *
	 * docker-compose exec --user www-data groupoffice-finance ./www/cli.php core/System/demo --package=business --module=catalog
	 * ```
	 */
	public function demo($params = []) {

		$faker = Faker\Factory::create();

		Entity::$trackChanges = false;
		HistoryModule::$enabled = false;
		Alert::$enabled = false;

		$modules = Module::find();

		if(isset($params['package'])) {
			$modules->andWhere('package', '=', $params['package']);
		}

		if(isset($params['module'])) {
			$modules->andWhere('name', '=', $params['module']);
		}

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


	public function alert($params) {
		$user = User::find()->where('username', '=', $params['username'])->single();

		/* @var \go\core\model\User $user */

		$alert = $user->createAlert(new DateTime());

		if(!$alert->save()) {
			throw new SaveException($alert);
		}
	}



	/**
	 * docker-compose exec --user www-data groupoffice ./www/cli.php  core/System/checkBlobs --delete
	 *
	 * @return void
	 * @throws Exception
	 */
	public function checkBlobs() {
		Blob::removeMissingFromFilesystem(!empty($params['delete']));
	}

	/**
	 * Make keys unsigned
	 *
	 * docker-compose exec --user www-data groupoffice ./www/cli.php  core/System/convertInts
	 *
	 * @return void
	 */
	public function convertInts() {

		go()->getDbConnection()->exec("SET foreign_key_checks = 0;");

		$this->installSqls = go()->getEnvironment()->getInstallFolder()->find([
			'regex' => '/^install\.sql$/'
		], false);

//		array_map(function($file) {
//			echo $file->getPath() ."\n";
//		},$installSqls);


		foreach(go()->getDatabase()->getTables() as $table) {

			//skip old framework with short prefix
			if(explode("_", $table->getName())[0] != "core") {
				continue;
			}

			foreach($table->getColumns() as $column) {
				if($column->autoIncrement) {

					$this->convertAlterCol($column);

					$refs = $table->getReferences($column->name);

					foreach($refs as $ref) {
						$refTable = go()->getDatabase()->getTable($ref['table']);
						$refCol = $refTable->getColumn($ref['column']);
						$this->convertAlterCol($refCol);
					}


				}
			}
		}
		go()->getDbConnection()->exec("SET foreign_key_checks = 1;");
	}

	private function convertAlterCol(Column $column) {
		if($column->unsigned) {
			return;
		}
		$column->unsigned = true;
		$sql = "alter table `" . $column->getTable()->getName() . "` modify `" . $column->name . "` ".
			str_replace("11", "10", $column->getCreateSQL() ) . ";\n";
		try {
			echo $sql;
			$this->replaceInSQL($column);
			go()->getDbConnection()->exec($sql);
		} catch(Exception $e) {
			echo $e ."\n\n";
		}
	}

	private function replaceInSQL(Column $column) {

		$count = 0;

		$search = "/\b". preg_quote($column->name) ."(`?\s+)INT[^\s]*/i";
		$replace = $column->name."$1INT(10) UNSIGNED";

		echo "\n\n======\n\n";
		echo $search."\n";
		echo $replace."\n";

		foreach($this->installSqls as $file) {
			$contents = $file->getContents();

			$tableSearch  = '/create table[^\n]+`?' . preg_quote($column->getTable()->getName()).'`?[\s\n]*\((.*);/Usi';
			$contents = preg_replace_callback($tableSearch, function($matches) use ($column, &$count, $file, $search, $replace) {

				return preg_replace(
					$search,
					$replace, $matches[0] ,-1, $count
				);
			}, $contents);

			if($count > 1) {
				throw new Exception($count. " Could not update ".$column->getTable()->getName().".". $column->name ." in ". $file->getPath());
			}

			if($count == 1) {

				$file->putContents($contents);

				echo $column->getTable()->getName().".". $column->name ." replaced in " . $file->getPath() ."\n";

				break;
			}




		}
		if($count != 1) {
			throw new Exception($count. " Could not update ".$column->getTable()->getName().".". $column->name);
		}
	}
}
