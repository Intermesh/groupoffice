<?php
use GO\Base\Observable;
use go\core\App;
use go\core\Environment;
use go\modules\core\modules\model\Module;
use go\core\util\Lock;
use GO\Base\Db\ActiveRecord;
use go\core\jmap\Entity;

/**
 * 
 * @return int 62 for 6.2 db and 63 for 6.3 or higher.
 * @throws \Exception
 */
function isValidDb() {
	if(GO()->getDatabase()->hasTable("core_module")) {

		if(!GO()->getDatabase()->hasTable("core_user")) {
			throw new \Exception("Your database seems to be in an invalid state. Please restore a backup and make sure no tables from a partial upgrade process are left.");
		}

		return 63;
	}
	if (!GO()->getDatabase()->hasTable("go_settings")) {
		throw new \Exception("Your database does not seem to be a Group-Office database");
	}
	$mtime = (new \go\core\db\Query)
					->selectSingleValue('value')
					->from('go_settings')
					->where('name', '=', 'upgrade_mtime')
					->single();

	if($mtime < 20180511) {
		throw new \Exception("You're database is not on the latest 6.2 version. Please upgrade it to the latest 6.2 first and make sure the modules 'customfields' and 'search' are installed.");
	}
	
	if((new \go\core\db\Query)
					->selectSingleValue('count(*)')
					->from('go_modules')
					->where('id', 'in', ['customfields', 'search'])
					->andWhere('enabled', '=', true)
					->single() != 2) {
		throw new \Exception("You've got a 6.2 database but you must install / enable the modules 'customfields' and 'search' before upgrading.");
					}
					
	return 62;	
}

function checkLicenses($is62 = false) {	
	if($is62) {
		//disabled modules must be deleted too when upgrading from 6.2 to 6.3
		$modules = (new \go\core\db\Query)
					->select('id AS name, "legacy" AS package')
					->from('go_modules')
					->where('enabled', '=', true)
					->all();
	} else
	{
		$modules = (new \go\core\db\Query)
					->select('name, package')
					->from('core_module')
					->where('enabled', '=', true)
					->all();
	}
	
	$unavailable = [];
	foreach($modules as $module) {
		
		if(isset($module['package']) && $module['package'] != 'legacy') {
			
			//SKIP for now as there no encoded refactored moules yet.
			continue;
		}
		
		
		$moduleCls = "GO\\" . ucfirst($module['name']). "\\" . ucfirst($module['name']) . "Module";
		
		if(is_dir(__DIR__.'/../go/modules/core/'.$module['name']) || is_dir(__DIR__.'/../go/modules/community/'.$module['name'])) {
			continue;
		}
		
		if(!class_exists($moduleCls)) {
			$unavailable[] = $module['name'];
			continue;
		} 
			
		$mod = new $moduleCls();
		
		if(!$mod->isAvailable()) {
			$unavailable[] = $module['name'];
		}		
	}
	if(isset($_GET['ignore']) && count($unavailable)) {
		if($is62) {
			GO()->getDbConnection()->query("update go_modules set enabled=0 where id IN ('".implode("', '",$unavailable)."')");
		} else {
			GO()->getDbConnection()->query("update core_module set enabled=0 where name IN ('".implode("', '",$unavailable)."')");	
		}
	} elseif(count($unavailable)) {

		echo "The following modules are not available because they're missing on disk\n"
		. "or you've got an <b>invalid or missing license file</b>: \n"
		. "<ul style=\"font-size:1.5em\"><li>" . implode("</li><li>", $unavailable) . "</li></ul>"
		. "Please install the license file(s) and refresh this page or disable these modules.\n"
		. "If you continue the incompatible modules will be disabled.\n\n";
		return false;
	}
	
	return true;
	
}


try {
	
	require('../vendor/autoload.php');
	
	require("gotest.php");
	if(!systemIsOk()) {
		header("Location: test.php");
		exit();
	}

	

	
	require('header.php');
	
	echo "<section><div class=\"card\"><h2>Upgrading Group-Office</h2><pre>";
	
	App::get();

	ActiveRecord::$log_enabled = false;

	$lock = new Lock("upgrade");
	if (!$lock->lock()) {
		throw new \Exception("Upgrade is already in progress");
	}
	
	GO()->getCache()->flush(false);
	GO::cache()->flush();
	GO()->setCache(new \go\core\cache\None());
	$dbValid = isValidDb();
	
	//remove obsolete modules
	if($dbValid == 62) {
		GO()->getDbConnection()->query("delete from go_modules where id IN ('blacklist', 'servermanager', 'admin2userlogin', 'formprocessor', 'settings', 'sites', 'syncml', 'dropbox', 'timeregistration', 'projects', 'hoursapproval', 'webodf','imapauth','ldapauth', 'presidents','ab2users', 'backupmanager', 'calllog', 'emailportlet', 'gnupg', 'language', 'mailings', 'newfiles')");
	} else {
		GO()->getDbConnection()->query("delete from core_module where name IN ('blacklist', 'servermanager', 'admin2userlogin', 'formprocessor', 'settings', 'sites', 'syncml', 'dropbox', 'timeregistration', 'projects', 'hoursapproval', 'webodf','imapauth','ldapauth', 'presidents','ab2users', 'backupmanager', 'calllog', 'emailportlet', 'gnupg', 'language', 'mailings', 'newfiles')");
	}
	
	function upgrade() {
		$u = [];

		Entity::$trackChanges = false;

		$modules = Module::find()->all();

		$root = Environment::get()->getInstallFolder();

		$modulesById = [];
		/* @var $module Module */
		foreach ($modules as $module) {
			
			if(!$module->isAvailable()) {
				echo "Skipping module ".$module->name." because it's not available.\n";
				continue;
			}
			
			$modulesById[$module->id] = $module;

			if ($module->package == null) {
				
				
				//old not refactored yet
				$upgradefile = $root->getFile('modules/' . $module->name . '/install/updates.php');
				if (!$upgradefile->exists()) {
					$upgradefile = $root->getFile('modules/' . $module->name . '/install/updates.inc.php');
				}
			} else {
				$upgradefile = $module->module()->getFolder()->getFile('install/updates.php');
				$module->module()->registerEntities();
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

						if (!$updateScript->exists()) {
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
					
					flush();
					
					echo ($module->package ?? "legacy") . "/" . $module->name . ' updated from '. $module->version .' to ' . $counts[$moduleId] . "\n";


					//$moduleModel = GO\Base\Model\Module::model()->findByName($module);
					//refetch module to see if package was updated
					if (!$module->package) {
						$module = Module::findById($moduleId);
						$newBackendUpgrade = $module->package != null;
						if ($newBackendUpgrade) {
							$module->version = $counts[$moduleId] = 0;
							$aModuleWasUpgradedToNewBackend = true;
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
	
	if(checkLicenses($dbValid == 62)) {

		//don't be strict
		GO()->getDbConnection()->query("SET sql_mode=''");
	
		if ($dbValid == 62) {					
			require(Environment::get()->getInstallFolder() . '/install/62to63.php');
		}		

		try {
			GO::session()->runAsRoot();
		}
		catch(\Exception $e) {
			echo "\nWarning: could not run as root!\n\n";
			echo $e;
		}

		if (!upgrade()) {
			echo "\n\nA module was refactored. Rerunning...\n\n";
			upgrade();
		}


		echo "Rebuilding cache\n";

		//reset new cache
		$cls = GO()->getConfig()['general']['cache'];
		GO()->setCache(new $cls);


		GO()->rebuildCache();
		App::get()->getSettings()->databaseVersion = App::get()->getVersion();
		App::get()->getSettings()->save();

		echo "Done!\n";

		echo "</pre></div>";

		echo '<a class="button" href="../">Continue</a>';


		if(GO()->getDebugger()->enabled) {
			echo "<div style=\"clear:both;margin-bottom:20px;\"></div><div class=\"card\"><h2>Debugger output</h2><pre>" . implode("\n", GO()->getDebugger()->getEntries()) . "</pre></div>";
		}

		echo "</section>";

	} else {
		echo '<a class="button" href="?ignore=modules">Disable &amp; Continue</a>';
		echo "</pre></div></section>";
	}
} catch (\Exception $e) {
	echo "<b>Error:</b> ".$e->getMessage()."\n\n";;
	
	echo $e->getTraceAsString();
	
	echo "</pre></div></section>";
}

require('footer.php');