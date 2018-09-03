<?php
/**
 * WARNING: This controller does not check authentication!
 * 
 * Controller with some maintenance functions
 */

namespace GO\Core\Controller;

use Exception;
use GO;
use GO\Base\Controller\AbstractController;
use GO\Base\Db\PDO;
use PDOException;
use ReflectionClass;

class MaintenanceController extends AbstractController {
	
	protected function allowGuests() {
		return array('upgrade','checkdatabase','servermanagerreport','test','downloadfromshop', 'removeduplicates','buildsearchcache');
	}
	
	//don't check token in this controller
	protected function checkSecurityToken(){}
	
	protected function init() {
		\GO::$disableModelCache=true; //for less memory usage
		\GO::setMaxExecutionTime(0); //allow long runs		
		GO::setMemoryLimit(256);
		ini_set('display_errors','on');		
	}
	
	public function actionTestCache($params){
		
		GO::cache()->set('test','test');
	}
	
	protected function actionDownloadFromShop($params){
		$this->requireCli();
		
		$proPackageName = 'groupoffice-pro';
		
		$this->checkRequiredParameters(array('shopuser','shoppass'), $params);
		
		$shopUrl = 'https://intermesh.group-office.com/';
		
		$packages = isset($params['packages']) ? explode(",", $params['packages']) : array('documents-4.0', 'billing-4.0', 'groupoffice-pro-4.0');
		
		$downloads=array();
		foreach($packages as $package_name){
			
			echo "\nGetting latest ".$package_name."\n";
		
			$packageUrl = $shopUrl.'?r=licenses/package/downloadPackageFile&package_name='.$package_name;
			

			$c = new \GO\Base\Util\HttpClient();
			if(!$c->groupofficeLogin($shopUrl, $params['shopuser'],$params['shoppass']))
				exit("Bad user name or password for shop");
			else
				echo "Shop login successful\n";

			$tmpDir = new \GO\Base\Fs\Folder(getcwd());
			if(!$tmpDir->isWritable())
				exit("Error: ".$tmpDir->path ()." is not writable!\n");

			$file = $tmpDir->createChild($package_name.'.tar.gz');
			echo "Downloading file from shop...\n";
			if(!$c->downloadFile($packageUrl, $file))		
				exit("Error: Failed to download file");
			
			$file->rename($c->getLastDownloadedFilename());
			
			
			$downloads[]=$file;
			
			
			if(!empty($params['replacefolder']))
				$params['unpack']=1;
			
			//echo "Filename: ".$c->getLastDownloadedFilename()."\n";

			echo "File saved in ".$file->path()."\n";

			if(!empty($params['unpack'])){
				echo "Unpacking ".$file->name()."\n";
				system('tar zxf '.$file->name());
			}

		}
		
		
		
		if(!empty($params['unpack'])){
			foreach($downloads as $download){
				if(strpos($download->name(), $proPackageName)!==false){
					$proDownload=$download;
					break;
				}
			}
			
			if(empty($proDownload)){
				exit("Error: Can't unpack. Group-Office Professional was not part of the downloads\n");
			}
			
			echo "Moving modules into pro package\n";
			
			$downloadFolder = str_replace('.tar.gz','', $proDownload->name());
			
			$newFolder = new \GO\Base\Fs\Folder(getcwd().'/'.$downloadFolder);
			if(!$newFolder->exists())
				exit("Download folder ".$newFolder->path()." does not exist.\n");
			
			foreach($downloads as $download){
				if(strpos($download->name(), $proPackageName)===false){
					
					$modPackageName = str_replace('.tar.gz','', $download->name());
					
					system('rm -Rf '.$modPackageName.'/professional');
					system('mv '.$modPackageName.'/* '.$downloadFolder.'/modules/');
					system('rm -Rf '.$modPackageName.'*');
					$proDownload->delete();
				}
			}			
		
			if(!empty($params['replacefolder'])){			

				$params['replacefolder']=realpath($params['replacefolder']);

				echo "Replacing: ".$params['replacefolder']."\n";

				$replaceFolder = new \GO\Base\Fs\Folder($params['replacefolder']);

				$origFolderName = $replaceFolder->name();

				$backupName = $origFolderName.'_bak_'.date('YmdGis');

				echo "Creating backup in ".$backupName."\n";


				if(!$replaceFolder->exists()){
					exit("Error: Folder ".$params['replacefolder']." does not exist!\n");
				}
				if(!$replaceFolder->rename($backupName))
					die("Failed to rename ".$replaceFolder->path()."\n");

//				$newFolder = new \GO\Base\Fs\Folder(getcwd().'/'.$downloadFolder);
				if(!$newFolder->rename($origFolderName))
					die("Failed to rename ".$newFolder->path()."\n");

				//there might be a config file or license file in the directory
				echo "Copying possible config and license files\n";
				system('cp '.$replaceFolder->path().'/config.php '.$replaceFolder->path().'/*license.txt '.$newFolder->path().'/');

			}
		}
		echo "All done\n";
		
	}
	
//	protected function ignoreAclPermissions() {
//		return array('*');
//	}
	
	protected function actionGetNewAcl($params){
		$acl = new \GO\Base\Model\Acl();
		$acl->user_id=isset($params['user_id']) ? $params['user_id'] : \GO::user()->id;
		$acl->description=$params['description'];
		$acl->save();
		
		echo $acl->id;
	}
	
	protected function actionCopyAcl($params){
		$acl = \GO\Base\Model\Acl::model()->findByPk($params['id']);
		$copy = $acl->duplicate();
		$acl->copyPermissions($copy);
		
		echo $copy->id;
	}
	
	protected function actionRemoveDuplicates($params){
				
		if(!\GO::modules()->tools)
			throw new \GO\Base\Exception\AccessDenied();
		
		\GO::session()->runAsRoot();

		
		\GO\Base\Fs\File::setAllowDeletes(false);
		//VERY IMPORTANT:
		\GO\Files\Model\Folder::$deleteInDatabaseOnly=true;
		\GO\Files\Model\File::$deleteInDatabaseOnly=true;
		
		$this->lockAction();
		
		\GO::session()->closeWriting(); //close writing otherwise concurrent requests are blocked.
		
		$checkModels = array(
				"GO\Calendar\Model\Event"=>array('name', 'start_time', 'end_time', 'calendar_id', 'rrule'),
				"GO\Tasks\Model\Task"=>array('name', 'start_time', 'due_time', 'tasklist_id', 'rrule', 'user_id'),
				"GO\Addressbook\Model\Contact"=>array('first_name', 'middle_name', 'last_name', 'addressbook_id', 'company_id', 'email'),
				"GO\Files\Model\Folder"=>array('name', 'parent_id'),
//				"GO\Calendar\Model\Participant"=>array('event_id', 'email'),
				//"GO\Billing\Model\Order"=>array('order_id','book_id','btime')
			);
		
		echo '<p style="color:red;"><font style="font-size:18px;" >Warning: This script only checks for duplicate items on the displayed columns!</font></p>';
		
		foreach($checkModels as $modelName=>$checkFields){
			
			if(empty($params['model']) || $modelName==$params['model']){

				echo '<h1>'.$modelName.'</h1>';

				$checkFieldsStr = 't.'.implode(', t.',$checkFields);
				$findParams = \GO\Base\Db\FindParams::newInstance()
								->ignoreAcl()
								->select('t.id, count(*) AS n, '.$checkFieldsStr)
								->group($checkFields)
								->having('n>1');

				$stmt1 = \GO::getModel($modelName)->find($findParams);

				echo '<table border="1">';
				echo '<tr><td>ID</th><th>'.implode('</th><th>',$checkFields).'</th></tr>';

				$count = 0;

				while($dupModel = $stmt1->fetch()){
					
					$select = 't.id';
					
					if(\GO::getModel($modelName)->hasFiles()){
						$select .= ', t.files_folder_id';
					}

					$findParams = \GO\Base\Db\FindParams::newInstance()
								->ignoreAcl()
								->select($select.', '.$checkFieldsStr)
								->order('id','ASC');

					$criteria=$findParams->getCriteria();

					foreach($checkFields as $field){
						$criteria->addCondition($field, $dupModel->getAttribute($field));
					}							

					$stmt = \GO::getModel($modelName)->find($findParams);

					$first = true;

					while($model = $stmt->fetch()){
						echo '<tr><td>';
						if(!$first)
							echo '<span style="color:red">';
						echo $model->id;
						if(!$first)
							echo '</span>';
						echo '</th>';				

						foreach($checkFields as $field)
						{
							echo '<td>'.$model->getAttribute($field,'html').'</td>';
						}

						echo '</tr>';

						if(!$first){							
							if(!empty($params['delete'])){

								if($model->hasLinks() && $model->countLinks()){
									echo '<tr><td colspan="99">Skipped delete because model has links</td></tr>';
								}elseif(($filesFolder = $model->getFilesFolder(false)) && ($filesFolder->hasFileChildren() || $filesFolder->hasFolderChildren())){
									echo '<tr><td colspan="99">Skipped delete because model has folder or files</td></tr>';
								}else{									
									$model->delete(true);
								}
							}

							$count++;
						}

						$first=false;
					}
				}	
					

				echo '</table>';

				echo '<p>Found '.$count.' duplicates</p>';
				echo '<br /><br /><a href="'.\GO::url('maintenance/removeDuplicates', array('delete'=>true, 'model'=>$modelName)).'">Click here to delete the newest duplicates marked in red for model '.$modelName.'.</a>';
				
			}
		}
		
		if(empty($params['model']))
			echo '<br /><br /><a href="'.\GO::url('maintenance/removeDuplicates', array('delete'=>true)).'">Click here to delete the newest duplicates marked in red.</a>';
		else
			echo '<br /><br /><a href="'.\GO::url('maintenance/removeDuplicates').'">Show all models.</a>';
	}
	
	/**
	 * Calls buildSearchIndex on each Module class.
	 * 
	 * You can give a model classname to only build up the searchcache for that model type:
	 * EG: ?r=maintenance/buildSearchCache&modelName=GO\Savemailas\Model\LinkedEmail
	 * 
	 * @return array 
	 */
	protected function actionBuildSearchCache($params) {
		
		if(!$this->isCli() && !\GO::modules()->tools && \GO::router()->getControllerAction()!='upgrade')
			throw new \GO\Base\Exception\AccessDenied();
		
		GO::setIgnoreAclPermissions(true);
		
		$this->lockAction();
		
		if(!$this->isCli()){
			echo '<pre>';
		}
		
		\GO::session()->closeWriting(); //close writing otherwise concurrent requests are blocked.
		
		$response = array();
		
//		if(empty($params['keepexisting']))
//			\GO::getDbConnection()->query('TRUNCATE TABLE go_search_cache');
//		
		//inserting is much faster without full text index. It's faster to add it again afterwards.
//		echo "Dropping full text search index\n";
//		try{
//			\GO::getDbConnection()->query("ALTER TABLE go_search_cache DROP INDEX ft_keywords");
//		}catch(\Exception $e){
//			echo $e->getMessage()."\n";
//		}
				
		if(!empty($params['modelName'])){
			$modelName = $params['modelName'];
			
			if(empty($params['keepexisting'])){
				$query = 'DELETE FROM go_search_cache WHERE model_name='.\GO::getDbConnection()->quote($modelName);
				\GO::getDbConnection()->query($query);
			}

			$models = array(new ReflectionClass($modelName));
		} else {
			if(empty($params['keepexisting']))
			\GO::getDbConnection()->query('TRUNCATE TABLE go_search_cache');
			
			$models=\GO::findClasses('model');
		}
		
		foreach($models as $model){
			if($model->isSubclassOf("GO\Base\Db\ActiveRecord") && !$model->isAbstract()){
				echo "Processing ".$model->getName()."\n";
				flush();
				$stmt = \GO::getModel($model->getName())->rebuildSearchCache();			
			}
		}
		
		if(empty($params['modelName'])){
			\GO::modules()->callModuleMethod('buildSearchCache', array(&$response));
		}
		
//		echo "Adding full text search index\n";
//		\GO::getDbConnection()->query("ALTER TABLE `go_search_cache` ADD FULLTEXT ft_keywords(`name` ,`keywords`);");
		
		echo "\n\nAll done!\n\n";
		
		if(!$this->isCli()){
			echo '</pre>';
		}
	}

	/**
	 * Calls checkDatabase on each Module class.
	 * @return array 
	 */
	protected function actionCheckDatabase($params) {
		

		if(!$this->isCli() && !\GO::modules()->tools)
			throw new \GO\Base\Exception\AccessDenied();
		
		 
		
		GO::setIgnoreAclPermissions(true);
		
		$this->run("upgrade",$params);		
		
		$this->lockAction();
		
		$response = array();
		
		$oldAllowDeletes = \GO\Base\Fs\File::setAllowDeletes(false);

		if(!$this->isCli()){
				echo '<pre>';
		}
		
	
				
		if(!empty($params['module'])){
			if($params['module']=='base'){
				$this->_checkCoreModels();
			}else
			{
				$class='GO\\'.ucfirst($params['module']).'\\'.ucfirst($params['module']).'Module';
				$module = new $class;
				$module->checkDatabase($response);
			}
		}else
		{
			$this->_checkCoreModels();
			\GO::modules()->callModuleMethod('checkDatabase', array(&$response));
		}
		
		echo "All Done!\n";
		
		if(!$this->isCli()){
				echo '</pre>';
		}
        
		\GO\Base\Fs\File::setAllowDeletes($oldAllowDeletes);
		
		return $response;
	}
	
	private function _checkCoreModels(){
		
		$sql = "delete from go_acl where acl_id = 0;";
		\GO::getDbConnection()->query($sql);
		
		//fix for invalid acl rows.
		$sql = "insert ignore into go_acl (acl_id,group_id) SELECT acl_id,group_id FROM `go_acl` WHERE user_id>0 && group_id>0;";
		\GO::getDbConnection()->query($sql);
		
		$sql = "insert ignore into go_acl (acl_id,user_id) SELECT acl_id,user_id FROM `go_acl` WHERE user_id>0 && group_id>0;";
		\GO::getDbConnection()->query($sql);		
		
		$sql = "delete from go_acl where user_id>0 and group_id>0;";
		\GO::getDbConnection()->query($sql);
		
		
		$sql = 'insert ignore into go_acl_items (select acl_id, "1", "missing", now() from go_acl);';
		\GO::getDbConnection()->query($sql);
		
		
		
		$classes=\GO::findClasses('model');
		foreach($classes as $model){
			if($model->isSubclassOf('GO\Base\Db\ActiveRecord') && !$model->isAbstract()){
		
				echo "Processing ".$model->getName()."\n";
				flush();

				$m = \GO::getModel($model->getName());

				if($m->checkDatabaseSupported()){		
					$stmt = $m->find(array(
							'ignoreAcl'=>true
					));
					
					$stmt->callOnEach('checkDatabase');
					
				}
			}
		}
	}
	
	private function _checkV3(){
		
		if(!\GO\Base\Db\Utils::tableExists('go_model_types')){
			
			$upgrade_mtime = \GO::config()->get_setting('upgrade_mtime');
			
			if($upgrade_mtime < 20111222)
				exit("Old version detected but it's older then ".\GO::config()->product_name." 3.7.41. You must upgrade to the latest 3.7 version first.");
			
			echo "Older version of ".\GO::config()->product_name." detected. Preparing database for 4.0 upgrade\n";
		
			$queries[]="ALTER TABLE `go_modules` ADD `enabled` BOOLEAN NOT NULL DEFAULT '1'";
			
			$queries[]="TRUNCATE TABLE `go_state`";
			$queries[]="delete from go_settings where name='version'";

			$queries[]="ALTER TABLE `go_users` ADD `mute_reminder_sound` ENUM( '0', '1' ) NOT NULL AFTER `mute_sound` ,
			ADD `mute_new_mail_sound` ENUM( '0', '1' ) NOT NULL AFTER `mute_reminder_sound`";

			$queries[]="ALTER TABLE `go_users` ADD `show_smilies` ENUM( '0', '1' ) NOT NULL DEFAULT '1' AFTER `mute_new_mail_sound`";
			$queries[]="ALTER TABLE `go_users` CHANGE `password` `password` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL";
			
			$queries[] = "delete from go_modules where id='log';";
			
			$queries[] = "ALTER TABLE `em_accounts` ADD INDEX(`acl_id`);";

			foreach($queries as $query){
				try {
					echo 'Excuting query: ' . $query . "\n";
					\GO::getDbConnection()->query($query);
				} catch (PDOException $e) {
					echo $e->getMessage() . "\n";
				}
			}
			
			echo "Done.\n";
			return true;
		}else
		{
			return false;
		}
		
	}
	
	public static function ob_upgrade_log($buffer)
	{
		global $logFile;

		file_put_contents($logFile, $buffer, FILE_APPEND);
		return $buffer;
	}
	
	protected function actionUpgrade($params) {
		
		
		
		if(!version_compare( phpversion(), "5.3", ">="))
			exit("You are running a PHP version older than 5.3. PHP 5.3 or greater is required to run Group-Office ".\GO::config()->version);
		
		$this->lockAction();
		
		$errorsOccurred = false;
		

		GO::setIgnoreAclPermissions(true);
		GO::session()->runAsRoot();
		
		\GO::clearCache();
		
		
		
		\GO\Base\Db\Columns::$forceLoad=true;
				
		//don't be strict in upgrade process
		\GO::getDbConnection()->query("SET sql_mode=''");
		
		
		
		$v3 = $this->_checkV3();
		
		$logDir = new \GO\Base\Fs\Folder(\GO::config()->file_storage_path.'log/upgrade/');
		$logDir->create();
		global $logFile;
		
		$logFile = $logDir->path().'/'.date('Ymd_Gi').'.log';
		touch ($logFile);

		if(!is_writable($logFile)){
			die('Fatal error: Could not write to log file');
		}

		
		
		
		ob_start("GO\Core\Controller\MaintenanceController::ob_upgrade_log");
		
		
		if(!$this->isCli()){
			echo '<pre>';
		}
		
		echo "Updating ".\GO::config()->product_name." database\n";
		
		//build an array of all update files. The queries are indexed by timestamp
		//so they will all be executed in the right order.
		$u = array();

		require(\GO::config()->root_path . 'install/updates.php');
		
		//put the updates in an extra array dimension so we know to which module
		//they belong too.
		foreach ($updates as $timestamp => $updatequeries) {
			$u["$timestamp"]['core'] = $updatequeries;
		}


		$modules = \GO::modules()->getAllModules();
			
		while ($module=array_shift($modules)) {
			
			if($module->isAvailable()){
				$updatesFile = $module->path . 'install/updates.php';
				if (!file_exists($updatesFile))
					$updatesFile = $module->path . 'install/updates.inc.php';

				if (file_exists($updatesFile)) {
					$updates = array();
					require($updatesFile);

					//put the updates in an extra array dimension so we know to which module
					//they belong too.
					foreach ($updates as $timestamp => $updatequeries) {
						$u["$timestamp"][$module->id] = $updatequeries;
					}
				}
			}
		}
		//sort the array by timestamp
		ksort($u);
//		
//		var_dump($u);
//		exit();
		
		$currentCoreVersion = \GO::config()->get_setting('version');
		if (!$currentCoreVersion)
			$currentCoreVersion = 0;
		
		$counts=array();
		
		foreach ($u as $timestamp => $updateQuerySet) {
			
			foreach ($updateQuerySet as $module => $queries) {
				
				//echo "Getting updates for ".$module."\n";
				
				if(!is_array($queries)){
					exit("Invalid queries in module: ".$module);
				}
				
				if($module=='core')
					$currentVersion=$currentCoreVersion;
				else
					$currentVersion = \GO::modules()->$module->version;
				
				if(!isset($counts[$module]))
					$counts[$module]=0;			
				
				foreach ($queries as $query) {
					$counts[$module]++;
					if ($counts[$module] > $currentVersion) {
						if (substr($query, 0, 7) == 'script:') {
							if ($module == 'core')
								$updateScript = \GO::config()->root_path . 'install/updatescripts/' . substr($query, 7);
							else
								$updateScript = \GO::modules()->$module->path . 'install/updatescripts/' . substr($query, 7);

							if (!file_exists($updateScript)) {
								die($updateScript . ' not found!');
							}
							
							//if(!$quiet)
							echo 'Running ' . $updateScript . "\n";
							flush();
							if (empty($params['test']))
								require_once($updateScript);
						}else {
							echo 'Excuting query: ' . $query . "\n";
							flush();
							if (empty($params['test'])) {
								try {
									if(!empty($query))
										\GO::getDbConnection()->query($query);
								} catch (PDOException $e) {
									//var_dump($e);		
									
									
									$errorsOccurred = true;
									
									echo $e->getMessage() . "\n";									
									echo "Query: ".$query;
//									if ($e->getCode() == 1091 || $e->getCode() == 1060) {
//										//duplicate and drop errors. Ignore those on updates
//									} else {
//										die();
//									}
								}
							}
						}

						if (empty($params['test'])) {
							if($module=='core')
								\GO::config()->save_setting('version', $counts[$module]);
							else{
								
								//echo $module.' updated to '.$counts[$module]."\n";
								
								$moduleModel = \GO::modules()->$module;
								
								$moduleModel->version=$counts[$module];
								$moduleModel->save();
							}
							ob_flush();
						}
					}
				}
			}
		}

		if (empty($params['test'])) {
			echo "Database updated to version " . \GO::config()->mtime, "\n";
			ob_flush();
			
			\GO::config()->save_setting('upgrade_mtime', \GO::config()->mtime);
		} else {
			echo "Ran in test mode\n";
		}
		
		echo "Removing cached javascripts...\n\n";	
		//in some exceptions alowed deletes is still on false here.
		\GO\Base\Fs\File::setAllowDeletes(true);
		\GO::clearCache();
		//rebuild listeners
		\GO\Base\Observable::cacheListeners();		
		if($v3){
			
//			if(\GO::modules()->isInstalled('projects') && \GO::modules()->isInstalled('files')){
//				echo "Renaming projects folder temporarily for new project paths\n";
//				$folder = \GO\Files\Model\Folder::model()->findByPath('projects');
//				if($folder){
//					$folder->name='oldprojects';
//					$folder->systemSave=true;
//					$folder->save();
//				}
//			}
			
			
//			echo "Checking database after version 3.7 upgrade.\n";
//			$this->actionCheckDatabase($params);
//			echo "Done\n\n";
//			ob_flush();
			
			$versioningFolder = new \GO\Base\Fs\Folder(\GO::config()->file_storage_path.'versioning');
			if($versioningFolder->exists())
				$versioningFolder->rename("versioning_backup_3_7");
			
			if(!$this->isCli()){
				echo '</pre>';
			}
				
			echo "Building search cache after version 3.7 upgrade.\n";
			ob_flush();
			$this->run("buildsearchcache",$params);
			ob_flush();
			
			if(!$this->isCli()){
				echo '<pre>';
			}
		}		
		
		
//		if(!$errorsOccurred) {
			echo "All Done!\n";		
//		}else
//		{
//			echo "All Done! Done but some errors occurred. Please try running this script again.\n";		
//		}
		
		
		
		if(!$this->isCli() && basename($_SERVER['PHP_SELF'])!='upgrade.php'){
			echo '</pre><br /><br />';
			
			if(!$errorsOccurred) {
				echo '<a href="'.\GO::config()->host.'">'.\GO::t('cmdContinue').'</a>';
			}else
			{
				echo '<a href="'.\GO::url('maintenance/upgrade').'">'.\GO::t('cmdContinue').'</a>';
			}
		}
		
		ob_end_flush();
		
		
		\GO\Base\Db\Columns::$forceLoad=false;
		//return $response;
	}
	
	
	public function actionServermanagerReport($params){
		$this->requireCli();
		$this->fireEvent('servermanagerReport');
	}
	

	
	/**
	 * Action to be called from browser address bar. It compares all the language
	 * fields of lang1 and lang2 in the current Group-Office installation, and
	 * echoes the fields that are in one language but not the other.
	 * @param type $params MUST contain $params['lang1'] AND $params['lang2']
	 */
	protected function actionCheckLanguage($params){
		
		
		header('Content-Type: text/html; charset=UTF-8');
		
		$lang1code = empty($params['lang1']) ? 'en' : $params['lang1'];
		$lang2code = empty($params['lang2']) ? 'nl' : $params['lang2'];
		
		$commonLangFolder = new \GO\Base\Fs\Folder(\GO::config()->root_path.'language/');
		$commonLangFolderContentArr = $commonLangFolder->ls();
		$moduleModelArr = \GO::modules()->getAllModules();
		
		echo "<h1>Translate tool</h1>";
				
		echo '<p><a href="'.\GO::url("maintenance/zipLanguage",array("lang"=>$lang2code)).'">Download zip file for '.$lang2code.'</a></p>';
		
		foreach ($commonLangFolderContentArr as $commonContentEl) {
			if (get_class($commonContentEl)=='GO\Base\Fs\Folder') {
				echo '<h3>'.$commonContentEl->path().'</h3>';
				echo $this->_compareLangFiles($commonContentEl->path().'/'.$lang1code.'.php', $commonContentEl->path().'/'.$lang2code.'.php');
				echo '<hr>';
				
			} else {
//				$commonContentEl = new \GO\Base\Fs\File();
//				$langFileContentString = $commonContentEl->getContents();
			}
		}
		
		foreach ($moduleModelArr as $moduleModel) {
			echo '<h3>'.$moduleModel->path.'</h3>';
			echo $this->_compareLangFiles($moduleModel->path.'language/'.$lang1code.'.php', $moduleModel->path.'language/'.$lang2code.'.php');
			echo '<hr>';
		}
	}
	
	/**
	 * Used in actionCheckLanguage. Compares the language contents of two language
	 * files, and echoes the fields that are in one file but not the other as Html.
	 * @param String $lang1Path Full path to first language file.
	 * @param String $lang2Path Full path to second language file.
	 * @return StringHelper Html string containing useful information for the user.
	 */
	private function _compareLangFiles($lang1Path,$lang2Path) {
		$outputHtml = '';
		$content1Arr = array();
		$content2Arr = array();
		
		$outputHtml .= $this->_langFieldsToArray($lang1Path,$content1Arr);
		$outputHtml .= $this->_langFieldsToArray($lang2Path,$content2Arr);				

		if(!empty($content1Arr) && !empty($content2Arr))
		{
			$outputHtml .= '<i>Missing in '.$lang2Path.':</i><br />'
							.$this->_getMissingFields($content1Arr, $content2Arr)
							.'<br />';
			$outputHtml .= '<i>Missing in '.$lang1Path.':</i><br />'
							.$this->_getMissingFields($content2Arr, $content1Arr)
							.'<br />';
		}
		
		return $outputHtml;
	}
	
	private function _replaceBOM($filePath){
		$origStr = file_get_contents($filePath);
		$str = str_replace("\xEF\xBB\xBF", '', $origStr);	
//		$str = str_replace("ï»¿", '', $str);	
		if($str!=$origStr){					
			file_put_contents($filePath, $str);
		}			
		
	}
	
	/**
	 * Used in actionCheckLanguage. Parse the file, putting its language fields
	 * into $contentArr.
	 * @param String $filePath The full path to the file.
	 * @param Array &$contentArr The array to put the language fields in.
	 * @return StringHelper Output string, possibly containing warnings for the user.
	 */
	private function _langFieldsToArray($filePath,&$contentArr) {
		$outputString = '';
		$langFile = new \GO\Base\Fs\File($filePath);
		
		
		
		if(!file_exists($langFile->path())) {
			$outputString .= '<i><font color="red">File not found: "'.$langFile->path().'"</font></i><br />';
		} else {
			$this->_replaceBOM($filePath);
			$encodingName = $langFile->detectEncoding($langFile->getContents());
			if ( $encodingName == 'UTF-8' || $encodingName == 'ASCII' || $langFile->convertToUtf8() ) {
				$lines = file($langFile->path());
				if (count($lines)) {
					foreach($lines as $line)
					{
						$first_equal = strpos($line,'=');
						if($first_equal != 0)
						{
							$key = str_replace('"','\'',trim(substr($line, 0, $first_equal)));
							$contentArr[$key] = trim(substr($line, $first_equal, strlen($line)-1));
						}
					}
				} else {
					$outputString .= '<i><font color="red">Could not compare '.str_replace(\GO::config()->root_path, '', $langFile->path()).', because it has no translation contents!</font></i><br />';
				}
			} else {
				$outputString .= '<i><font color="red">Could not compare with '.str_replace(\GO::config()->root_path, '', $langFile->path()).', because it cannot be made UTF-8!</font></i><br />';
			}
			
			//for displaying errors
			include($filePath);
			
			
		}
		return $outputString;
	}
	
	/**
	 * Used in actionCheckLanguage. Compares two arrays and returns as Html the
	 * fields that is in one but not the other.
	 * @param Array $array1
	 * @param Array $array2
	 * @return String 
	 */
	private function _getMissingFields($array1, $array2)
	{
		$outputString = '';
		$diffs = array_diff_key($array1, $array2);
		
		if(!empty($diffs))
		{
			foreach($diffs as $key=>$diff)
			{
				if(!strpos($diff, '{}'))
					$output[] = $key.$diff;
			}
			if(!empty($output))
			{
				foreach ($output as $out)
					$outputString .= htmlentities($out,ENT_QUOTES,'UTF-8').'<br />';
			}
		}
		return $outputString;
	}
	
	
	private function _getAllLanguageFiles(){
		
		$files=array();
		
		$languages = array_keys(\GO::language()->getLanguages());
		
		$commonLangFolder = new \GO\Base\Fs\Folder(\GO::config()->root_path.'language/');
		$folders = $commonLangFolder->ls();
		
		$modules = \GO::modules()->getAllModules();
		foreach($modules as $module){
			$folder = new \GO\Base\Fs\Folder($module->path.'language');
			if($folder->exists())
				$folders[]=$folder;
		}
		
		foreach($folders as $folder){
			foreach($languages as $language){
				if($file = $folder->child($language.'.php')){
					$files[]=$file;
				}
			}
		}
		
		return $files;
	
	}
	
	
	protected function actionRemoveOldLangKeys($params){
		
		if(!$this->isCli() && !GO::modules()->tools)
			throw new \GO\Base\Exception\AccessDenied();
		
		$files = $this->_getAllLanguageFiles();
		
		foreach($files as $file){
			
			echo "Processing: ".$file->path()."\n";
		
			$data = $file->contents();

			$entries = explode("\$l", $data);

			//to find duplicate keys we'll reverse the lines becuase the last definition is used.
			$entries = array_reverse($entries);

			$processedKeys = array();

			$newData=array();

			foreach($entries as $entry){

				if(preg_match('/^\[(\'|")([a-z_-]+)(\'|")\][^[]/i', $entry, $matches)){

					$key = $matches[2];


					if(!in_array($key, $processedKeys)){
						$newData[]=$entry;
						$processedKeys[]=$key;
					}  else {
						echo "Skipping duplicate key : ".$key."\n";
					}

				}else
				{
					$newData[] = $entry;
				}			
			}
			
			$newData = implode("\$l", array_reverse($newData));
			
//			echo $newData;
			
			if(eval(str_replace('<?php', '', $newData))===false)
				throw new Exception("Parse error in generated data for ".$file->path());
			
			$file->putContents($newData);
		}
	}
	
	/**
	 * Run from the browser's address bar. Collects all language files, and puts
	 * them in a zip file in the file storage path, respecting the folder
	 * structure. I.e., you can later unpack the file contents to the
	 * Group-Office path.
	 * @param type $params 
	 */
	protected function actionZipLanguage($params){
		if (!empty($params['lang'])) {
			$langCode = $params['lang'];
		} else {
			die('<font color="red"><i>The GET parameter lang is required for the zipLanguage action!</i></font>');
		}
		$fileNames = array();
		
		//gather file list in array
		$commonLangFolder = new \GO\Base\Fs\Folder(\GO::config()->root_path.'language/');
		if($commonLangFolder->exists()){
			$commonLangFolderContentArr = $commonLangFolder->ls();
			$moduleModelArr = \GO::modules()->getAllModules();

			foreach ($commonLangFolderContentArr as $commonLangFolder) {
				if (get_class($commonLangFolder)=='GO\Base\Fs\Folder') {
					$commonLangFileArr = $commonLangFolder->ls();
					foreach ($commonLangFileArr as $commonLangFile)
						if (get_class($commonLangFile)=='GO\Base\Fs\File' && $commonLangFile->name()==$langCode.'.php') {
							$fileNames[] = str_replace(\GO::config()->root_path,'',$commonLangFile->path());
						}
				}
			}
		}
		
		foreach ($moduleModelArr as $moduleModel) {
			$modLangFolder = new \GO\Base\Fs\Folder($moduleModel->path.'language/');
			if($modLangFolder->exists()){
				$modLangFiles = $modLangFolder->ls();
				foreach ($modLangFiles as $modLangFile) {
					if ($modLangFile->name()==$langCode.'.php')
						$fileNames[] = str_replace(\GO::config()->root_path,'',$modLangFile->path());
				}
			}
		}
		
		$tmpFile = \GO\Base\Fs\File::tempFile($langCode.'-'.str_replace('.','-', \GO::config()->version), 'zip');
		
		//exec zip
		$cmdString = \GO::config()->cmd_zip.' '.$tmpFile->path().' '.implode(" ", $fileNames);
		exec($cmdString,$outputArr, $retVal);
		
		if($retVal>0)
			trigger_error("Creating ZIP file failed! ".implode("<br />", $outputArr), E_USER_ERROR);
		
		\GO\Base\Util\Http::outputDownloadHeaders($tmpFile);
		$tmpFile->output();
		$tmpFile->delete();
	}
	
	protected function actionCheckDefaultModels(){
		
		if(!$this->isCli() && !GO::modules()->tools)
			throw new \GO\Base\Exception\AccessDenied();
		
		GO::session()->closeWriting();
		
		GO::setIgnoreAclPermissions(true);
		
		if(!$this->isCli())			
			echo '<pre>';
		
		$stmt = \GO\Base\Model\User::model()->find();
		
		foreach($stmt as $user){
			echo "Checking ".$user->username."\n";
			$user->checkDefaultModels();
		}
		
		echo "Done\n\n";
	}
	
	protected function actionResetState($params){
		
		\GO::getDbConnection()->query("DELETE FROM go_state WHERE name!='summary-active-portlets' AND user_id=".intval($params['user_id']));

		return array('success'=>true);
	}
	
	
	protected function actionRemoveEmptyStuff($params){
		
		if(!$this->isCli() && !GO::modules()->tools)
			throw new \GO\Base\Exception\AccessDenied();
		
		GO::session()->closeWriting();

		GO::setIgnoreAclPermissions(true);
		
		if(!$this->isCli())			
			echo '<pre>';
		
		if(\GO::modules()->isInstalled("addressbook")){
			echo "\n\nProcessing addressbook\n";
			flush();
			$stmt = \GO\Addressbook\Model\Addressbook::model()->find();
			while($addressbook = $stmt->fetch()){
				$contactStmt = $addressbook->contacts();
				$companiesStmt = $addressbook->companies();
				
				if(!$contactStmt->rowCount() && !$companiesStmt->rowCount()){
					echo "Removing ".$addressbook->name."\n";
					$addressbook->delete();
					flush();
				}
			}
		}
		
		if(\GO::modules()->isInstalled("calendar")){
			echo "\n\nProcessing calendar\n";
			flush();
			
			$stmt = \GO\Calendar\Model\Calendar::model()->find();
			while($calendar = $stmt->fetch()){
				$eventStmt = $calendar->events();
				
				if(!$eventStmt->rowCount()){
					echo "Removing ".$calendar->name."\n";
					$calendar->delete();
					flush();
				}
			}
		}
		
		if(\GO::modules()->isInstalled("tasks")){
			echo "\n\nProcessing tasks\n";
			flush();
			
			$stmt = \GO\Tasks\Model\Tasklist::model()->find();
			while($tasklist = $stmt->fetch()){
				$eventStmt = $tasklist->tasks();
				
				if(!$eventStmt->rowCount()){
					echo "Removing ".$tasklist->name."\n";
					$tasklist->delete();
					flush();
				}
			}
		}
		
		
		if(\GO::modules()->isInstalled("notes")){
			echo "\n\nProcessing notes\n";
			flush();
			
			$stmt = \GO\Notes\Model\Category::model()->find();
			while($cat = $stmt->fetch()){
				$eventStmt = $cat->notes();
				
				if(!$eventStmt->rowCount()){
					echo "Removing ".$cat->name."\n";
					$cat->delete();
					flush();
				}
			}
		}
		
	}	
	
	
	protected function actionConvertToInnoDB(){
		\GO::getDbConnection()->query("SET sql_mode = '';");
		
		$stmt = \GO::getDbConnection()->query("SHOW TABLES");
		$stmt->setFetchMode(PDO::FETCH_NUM);
		
		echo '<pre>';
		
		foreach($stmt as $record){
			
			if($record[0]!='fs_filesearch' && $record[0] != 'cms_files'){//filesearch requires fulltext index
				$sql = "ALTER TABLE `".$record[0]."` ENGINE=InnoDB;";
				echo $sql."\n";
				
				GO::getDbConnection()->query($sql);
			}
			
			
		}
	}
	
		
		
	protected function actionCheckVersion(){
		$rssUrl = "https://sourceforge.net/api/file/index/project-id/76359/mtime/desc/limit/20/rss";
		
		$httpClient = new  \GO\Base\Util\HttpClient();
		
		$response = $httpClient->request($rssUrl);
		
	
		$sXml = simplexml_load_string($response);	
		
		$firstItem = $sXml->channel->item[0];		
		
		$link = (string) $firstItem->link;
		
		preg_match('/-([0-9]\.[0-9]{1,2}\.[0-9]{1,2})\./', $link, $matches);
		
		$version = $matches[1];
			
		$ret = version_compare(GO::config()->version, $version);
		
		if($ret!== -1){
			echo "A new version ($version) is available at $link";
		}else
		{
			echo "Your running the latest version";
		}
		
	}
}