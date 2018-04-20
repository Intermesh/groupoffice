<?php

use GO\Base\Observable;
use go\core\App;
use go\core\Environment;
use go\core\module\model\Module;
use go\core\util\Lock;

require('../vendor/autoload.php');

App::get();

$lock = new Lock("upgrade");
if (!$lock->lock()) {
	exit("Upgrade is already in progress");
}
header("Content-Type: text/plain; charset=utf8");

GO()->getCache()->flush(false);

try {
	
	if (!GO()->getDatabase()->hasTable("core_module")) {
		//todo: verify this is a valid 6.2 database
		require(Environment::get()->getInstallFolder() . '/install/62to63.php');
	}

//don't be strict
	GO()->getDbConnection()->query("SET sql_mode=''");

	

	function upgrade() {
		echo "Upgrading Group-Office\n";


		$u = [];

		$modules = Module::find()->all();

		$root = Environment::get()->getInstallFolder();

		$modulesById = [];
		/* @var $module Module */
		foreach ($modules as $module) {
			$modulesById[$module->id] = $module;

			if ($module->package == null) {
				
				
				//old not refactored yet
				$upgradefile = $root->getFile('modules/' . $module->name . '/install/updates.php');
				if (!$upgradefile->exists()) {
					$upgradefile = $root->getFile('modules/' . $module->name . '/install/updates.inc.php');
				}
			} else {
				$upgradefile = $module->module()->getFolder()->getFile('install/updates.php');
			}

			if (!$upgradefile->exists()) {
				continue;
			}

			$updates = array();
			require($upgradefile);

			//put the updates in an extra array dimension so we know to which module
			//they belong too.
			foreach ($updates as $timestamp => $updatequeries) {
				$u["$timestamp"][$module->id] = $updatequeries;
			}
		}

		ksort($u);



		$counts = array();

		$aModuleWasUpgradedToNewBackend = false;

		foreach ($u as $timestamp => $updateQuerySet) {

			foreach ($updateQuerySet as $moduleId => $queries) {

				//echo "Getting updates for ".$module."\n";
				$module = $modulesById[$moduleId];

				if (!is_array($queries)) {
					exit("Invalid queries in module: " . $module->name);
				}


				if (!isset($counts[$moduleId]))
					$counts[$moduleId] = 0;
				
//			/	echo $module->name ." installed version ".$module->version ." new version: ".$counts[$moduleId] ."\n";

				

				foreach ($queries as $query) {
					$counts[$moduleId]++;
					if ($counts[$moduleId] <= $module->version) {
						continue;
					}
					
					if (is_callable($query)) {
						echo "Running callable function\n";
						call_user_func($query);
					} else if (substr($query, 0, 7) == 'script:') {
						$updateScript = $root->getFile('modules/' . $module->name . '/install/updatescripts/' . substr($query, 7));

						if ($updateScript->exists()) {
							die($updateScript . ' not found!');
						}

						//if(!$quiet)
						echo 'Running ' . $updateScript . "\n";
						call_user_func(function() use ($updateScript) {
							require_once($updateScript);
						});
						
					} else {
						echo 'Excuting query: ' . $query . "\n";
						flush();
						try {
							if (!empty($query))
								GO()->getDbConnection()->query($query);
						} catch (PDOException $e) {
							//var_dump($e);		


							$errorsOccurred = true;

							echo $e->getMessage() . "\n";
							echo "Query: " . $query . "\n";
							echo "Package: " . ($module->package ?? "legacy") . "\n";
							echo "Module: " . $module->name . "\n";
							echo "Module installed version: " . $module->version . "\n";
							echo "Module source version: " . $counts[$moduleId] . "\n";

							if ($e->getCode() == 42000 || $e->getCode() == '42S21' || $e->getCode() == '42S01' || $e->getCode() == '42S22') {
								//duplicate and drop errors. Ignore those on updates
							} else {
								die();
							}
						}
					}
				


					echo ($module->package ?? "legacy") . "/" . $module->name . ' updated from '. $module->version .' to ' . $counts[$moduleId] . "\n";


					//$moduleModel = GO\Base\Model\Module::model()->findByName($module);
					//refetch module to see if package was updated
					if (!$module->package) {
						$module = Module::findById($moduleId);
						var_dump($module->package);
						$newBackendUpgrade = $module->package != null;
						if ($newBackendUpgrade) {
							$module->version = $counts[$moduleId] = 0;
							$aModuleWasUpgradedToNewBackend = true;

							echo "\n\n\nREFACTORED MODULE\n\n\n";
						} else
						{
							$module->version = $counts[$moduleId];
						}
					} else
					{
						$module->version = $counts[$moduleId];
					}
					
					//exit();

					if(!$module->save()) {
						throw new \Exception("Failed to save module");
					}
				}

			}
			
		}

		return !$aModuleWasUpgradedToNewBackend;
	}

	if (!upgrade()) {
		echo "A module was refactored. Rerunning...\n";
		upgrade();
	}


	echo "Flusing cache\n";
	GO::clearCache(); //legacy
	App::get()->getCache()->flush(false);
	App::get()->getDataFolder()->getFolder('clientscripts')->delete();


	echo "Rebuilding listeners\n";
	Observable::cacheListeners();

	echo "Done!\n";
} catch (\Exception $e) {
	echo (string) $e;
}
