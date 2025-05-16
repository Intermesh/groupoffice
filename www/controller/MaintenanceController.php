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
use GO\Base\Exception\AccessDenied;
use go\core\App;
use go\core\auth\TemporaryState;
use go\core\db\Table;
use go\core\db\Utils;
use go\core\orm\EntityType;
use go\core\orm\PrincipalTrait;
use go\core\orm\SearchableTrait;
use go\modules\community\history\Module;
use PDOException;
use ReflectionClass;
use go\core\util\ClassFinder;
use go\core\orm\Entity;
use ReflectionMethod;

class MaintenanceController extends AbstractController {
	
	protected function allowGuests() {
		return array('upgrade','checkdatabase','servermanagerreport','test','downloadfromshop', 'removeduplicates','buildsearchcache');
	}
	
	//don't check token in this controller
	protected function checkSecurityToken(){}
	
	protected function init() {
		GO::$disableModelCache=true; //for less memory usage
		GO::setMaxExecutionTime(0); //allow long runs
		GO::setMemoryLimit(256);
		ini_set('display_errors','on');		
	}
	
	public function actionTestCache($params){
		
		GO::cache()->set('test','test');
	}
	
	protected function actionCollectGarbage() {
		$cg = new \go\core\fs\GarbageCollector;
		$cg->execute();
	}

//	protected function ignoreAclPermissions() {
//		return array('*');
//	}
	
	protected function actionGetNewAcl($params){

    if(!empty($params['source_acl_id'])) {
      $sourceAcl = \GO\Base\Model\Acl::model()->findByPk($params['source_acl_id']);
      $params['user_id'] = $sourceAcl->ownedBy;
      $params['description'] = $sourceAcl->usedIn;
    }
		$acl = new \GO\Base\Model\Acl();
		$acl->ownedBy=isset($params['user_id']) ? $params['user_id'] : GO::user()->id;
		$acl->usedIn=$params['description'];
		$acl->save();

		if(!empty($sourceAcl)) {
		  $sourceAcl->copyPermissions($acl);
    }
		
		echo $acl->id;
	}
	
	protected function actionCopyAcl($params){
		$acl = \GO\Base\Model\Acl::model()->findByPk($params['id']);
		$copy = $acl->duplicate();
		$acl->copyPermissions($copy);
		
		echo $copy->id;
	}
	
	protected function actionRemoveDuplicates($params){
				
		if(!GO::user()->isAdmin())
			throw new \GO\Base\Exception\AccessDenied();
		
		GO::session()->runAsRoot();

		
		\GO\Base\Fs\File::setAllowDeletes(false);
		//VERY IMPORTANT:
		\GO\Files\Model\Folder::$deleteInDatabaseOnly=true;
		\GO\Files\Model\File::$deleteInDatabaseOnly=true;
		
		$this->lockAction();
		
		GO::session()->closeWriting(); //close writing otherwise concurrent requests are blocked.
		
		$checkModels = array(
//				"GO\Calendar\Model\Event"=>array('name', 'start_time', 'end_time', 'calendar_id', 'rrule'),
//				"GO\Tasks\Model\Task"=>array('name', 'start_time', 'due_time', 'tasklist_id', 'rrule', 'user_id'),
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

				$stmt1 = GO::getModel($modelName)->find($findParams);

				echo '<table border="1">';
				echo '<tr><td>ID</th><th>'.implode('</th><th>',$checkFields).'</th></tr>';

				$count = 0;

				while($dupModel = $stmt1->fetch()){
					
					$select = 't.id';
					
					if(GO::getModel($modelName)->hasFiles()){
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

					$stmt = GO::getModel($modelName)->find($findParams);

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
								if(empty($params['ignore_links']) && $model->hasLinks() && $model->countLinks()){
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
				echo '<br /><br /><a href="'. GO::url('maintenance/removeDuplicates', array('delete'=>true, 'model'=>$modelName)).'">Click here to delete the newest duplicates marked in red for model '.$modelName.'.</a>';
				
			}
		}
		
		if(empty($params['model'])) {
			echo '<br /><br /><a href="'. GO::url('maintenance/removeDuplicates', array('delete'=>true)).'">Click here to delete the newest duplicates marked in red.</a>';

			echo '<br /><br /><a href="'. GO::url('maintenance/removeDuplicates', array('delete'=>true, 'ignore_links' => true)).'">Click here to delete the newest duplicates marked in red also when they have links.</a>';
		} else {
			echo '<br /><br /><a href="'. GO::url('maintenance/removeDuplicates').'">Show all models.</a>';
		}
	}

	protected function actionBuildPrincipals($params) {

		GO::session()->closeWriting();

		if(!$this->isCli() && !GO::user()->isAdmin() && GO::router()->getControllerAction()!='upgrade')
			throw new \GO\Base\Exception\AccessDenied();

		go()->setAuthState(new TemporaryState(1));
		\go\core\jmap\Entity::$trackChanges = false;
		Module::$enabled = false;
		if(!$this->lockAction()) {
			exit("Already running!");
		}
		//speed things up
		go()->getDbConnection()->exec("SET unique_checks=0; SET foreign_key_checks=0");


		if(!$this->isCli()){
			echo '<pre>';
		}
//		if(!empty($params['reset'])) {
//			echo "Resetting cache!\n";
//			go()->getDbConnection()->exec("TRUNCATE core_principal");
//		}

		echo "Checking principal cache\n\n";
		echo ".: Record cached, E: Error while occurred, S: Record skipped (probably normal)\n"
			.    "==============================================================================\n\n";

		$classFinder = new ClassFinder();
		$entities = $classFinder->findByTrait(PrincipalTrait::class);

		foreach ($entities as $cls) {
			$cls::rebuildPrincipalForEntity();
			echo "\nDone\n\n";
		}

		go()->getDbConnection()->exec("SET unique_checks=1; SET foreign_key_checks=1");

		echo "Resettings JMAP sync state\n";
		go()->rebuildCache();

		echo "\n\nAll done!\n\n";

		if(!$this->isCli()){
			echo '</pre>';
		}
	}

	/**
	 * Calls buildSearchIndex on each Module class.
	 *
	 * You can give a model classname to only build up the searchcache for that model type:
	 * EG: ?r=maintenance/buildSearchCache&modelName=GO\Savemailas\Model\LinkedEmail
	 *
	 * @param array $paramns
	 * @throws AccessDenied
	 * @throws Exception
	 */
	protected function actionBuildSearchCache(array $params) {
		
		if(!$this->isCli() && !GO::user()->isAdmin() && GO::router()->getControllerAction()!='upgrade') {
			throw new \GO\Base\Exception\AccessDenied();
		}

		GO::setIgnoreAclPermissions(true);
		GO::session()->runAsRoot();

		go()->setAuthState(new TemporaryState(1));

		\go\core\jmap\Entity::$trackChanges = false;
		Module::$enabled = false;

		if(!$this->lockAction()) {
			exit("Already running!");
		}

		if(!$this->isCli()){
			echo '<pre>';
		}

		//speed things up
		go()->getDbConnection()->exec("SET unique_checks=0; SET foreign_key_checks=0");
		
		if(!empty($params['reset'])) {
			echo "Resetting cache!\n";
			//change mtime's so they will be updated
			if(!empty($params['modelName'])){
				$entityTypeId = $params['modelName']::entityType()->getId();
				go()->getDbConnection()->exec("update core_search set `rebuild` = true WHERE entityTypeId=" . $entityTypeId);
			} else {
				go()->getDbConnection()->exec("update core_search set `rebuild` = true");
			}
		}
		
		echo "Checking search cache\n\n";
		echo ".: Record cached, E: Error while occurred, S: Record skipped (probably normal)\n"
		.    "==============================================================================\n\n";
		
		GO::session()->closeWriting(); //close writing otherwise concurrent requests are blocked.

		
		$response = array();

		if(!empty($params['modelName'])){;
			$m = $params['modelName'];

			if(is_a($m, Entity::class, true)){
				$cls = new ReflectionClass($m);
				$mthd = $cls->getMethod('rebuildSearchForEntity');
				$mthd->invoke($cls->newInstance(), $m);
//				SearchableTrait::rebuildSearchForEntity($params['modelName']);
			}else {
				$models = array(new ReflectionClass($params['modelName']));

				foreach ($models as $model) {
					if ($model->isSubclassOf("GO\Base\Db\ActiveRecord") && !$model->isAbstract()) {
						GO::getModel($model->getName())->rebuildSearchCache();
					}
				}
			}
		} else {
			GO::modules()->callModuleMethod('buildSearchCache', array(&$response));
			go()->rebuildSearch();
		}

		go()->getDbConnection()->exec("SET unique_checks=1; SET foreign_key_checks=1");

		echo "Resettings JMAP sync state\n";
		go()->rebuildCache();

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
		

		if(!$this->isCli() && !GO::user()->isAdmin())
			throw new \GO\Base\Exception\AccessDenied();		 
		
		GO::setIgnoreAclPermissions(true);
		GO::session()->runAsRoot();	
		
		//$this->run("upgrade",$params);		
		
		$this->lockAction();

		\go\core\jmap\Entity::$trackChanges = false;
		Module::$enabled = false;

		\GO\Base\Db\ActiveRecord::$updateSearch = false;
		
		$response = array();
		
		$oldAllowDeletes = \GO\Base\Fs\File::setAllowDeletes(false);

		if(!$this->isCli()){
				echo '<pre>';
		}

		go()->getInstaller()->fixCollations();
		
				
		if(!empty($params['module'])){
			if($params['module']=='base' || $params['module']=='core'){
				$this->_checkCoreModels();
			}else {
				if (empty($params['package']) || $params['package'] == 'legacy') {

					$class = 'GO\\' . ucfirst($params['module']) . '\\' . ucfirst($params['module']) . 'Module';
					if (class_exists($class)) {
						$module = $class::get();
						$module->checkDatabase($response);
					}
				} else {
					$class = 'go\\modules\\' . $params['package'] . '\\' . $params['module'] . '\\Module';
					$module = $class::get();
					$module->checkDatabase($response);
				}
			}
		}else
		{
			$this->_checkCoreModels();
			GO::modules()->callModuleMethod('checkDatabase', array(&$response));
		}


//		$cf = new ClassFinder();
//		$entities = $cf->findByParent(Entity::class);
//
//		foreach($entities as $entity) {
//			echo "Checking ". $entity."\n";
//			$entity::check();
//		}


		echo "Resettings JMAP sync state\n";
		go()->rebuildCache();
		
		echo "All Done!\n";
		
		if(!$this->isCli()){
				echo '</pre>';
		}
        
		\GO\Base\Fs\File::setAllowDeletes($oldAllowDeletes);
		
		return $response;
	}
	
	private function _checkCoreModels(){
		
		$sql = "delete from core_acl where id = 0;";
		GO::getDbConnection()->query($sql);

		EntityType::checkDatabase();
		
		$classes= GO::findClasses('model');
		foreach($classes as $model){
			if($model->isSubclassOf('GO\Base\Db\ActiveRecord') && !$model->isAbstract()){

				$m = GO::getModel($model->getName());

				if($m->hasColumn('user_id')) {
					//correct missing user_id values
					$stmt = go()->getDbConnection()->updateIgnore(
						$m->tableName(),
						['user_id' => 1],
						(new \go\core\orm\Query())
							->where("user_id not in (select id from core_user)"));
					$stmt->execute();
					if($stmt->rowCount()) {
						echo "Changed " . $stmt->rowCount() . " missing user id's into the admin user\n";
					}
				}

				if($m->hasColumn('acl_id')) {
					//correct missing user_id values
					$stmt = go()->getDbConnection()->update(
						$m->tableName(),
						['acl_id' => 0],
						(new \go\core\orm\Query())
							->where("acl_id not in (select id from core_acl)"));

					$stmt->execute();

					if($stmt->rowCount()) {
						echo "Set " . $stmt->rowCount() . " missing ACL id's to zero\n";
					}
				}
		
				echo "Processing ".$model->getName()."\n";
				flush();

				$m = GO::getModel($model->getName());

				if($m->checkDatabaseSupported()){		
//					$stmt = $m->find(array(
//							'ignoreAcl'=>true
//					));
//
//					$stmt->callOnEach('checkDatabase');


					//to avoid memory errors
					$start = 0;

					//per thousands to keep memory low
					$stmt = $m->find(array(
						'ignoreAcl'=>true,
						'start' => $start,
						'limit' => 1000
					));

					while($stmt->rowCount()) {
						$stmt->callOnEach('checkDatabase', true);

						$stmt = $m->find(array(
							'ignoreAcl'=>true,
							'start' => $start+=1000,
							'limit' => 1000
						));
					}

					unset($stmt);
					
				}
			}
		}
	}
	
	
	public static function ob_upgrade_log($buffer)
	{
		global $logFile;

		file_put_contents($logFile, $buffer, FILE_APPEND);
		return $buffer;
	}
	
	protected function actionUpgrade($params) {
		echo "Please run install/upgrade.php";
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
		
		$commonLangFolder = new \GO\Base\Fs\Folder(GO::config()->root_path.'language/');
		$commonLangFolderContentArr = $commonLangFolder->ls();
		$moduleModelArr = GO::modules()->getAllModules();
		
		echo "<h1>Translate tool</h1>";
				
		echo '<p><a href="'. GO::url("maintenance/zipLanguage",array("lang"=>$lang2code)).'">Download zip file for '.$lang2code.'</a></p>';
		
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
	 * @return string Html string containing useful information for the user.
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
	 * @return string Output string, possibly containing warnings for the user.
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
					$outputString .= '<i><font color="red">Could not compare '.str_replace(GO::config()->root_path, '', $langFile->path()).', because it has no translation contents!</font></i><br />';
				}
			} else {
				$outputString .= '<i><font color="red">Could not compare with '.str_replace(GO::config()->root_path, '', $langFile->path()).', because it cannot be made UTF-8!</font></i><br />';
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
		
		$languages = array_keys(GO::language()->getLanguages());
		
		$commonLangFolder = new \GO\Base\Fs\Folder(GO::config()->root_path.'language/');
		$folders = $commonLangFolder->ls();
		
		$modules = GO::modules()->getAllModules();
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
		
		if(!$this->isCli() && !GO::user()->isAdmin())
			throw new \GO\Base\Exception\AccessDenied();
		
		$files = $this->_getAllLanguageFiles();
		
		foreach($files as $file){
			
			echo "Processing: ".$file->path()."\n";
		
			$data = $file->contents();

			$entries = explode("\$l", $data);

			//to find duplicate keys we'll reverse the lines because the last definition is used.
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
		$commonLangFolder = new \GO\Base\Fs\Folder(GO::config()->root_path.'language/');
		if($commonLangFolder->exists()){
			$commonLangFolderContentArr = $commonLangFolder->ls();
			$moduleModelArr = GO::modules()->getAllModules();

			foreach ($commonLangFolderContentArr as $commonLangFolder) {
				if (get_class($commonLangFolder)=='GO\Base\Fs\Folder') {
					$commonLangFileArr = $commonLangFolder->ls();
					foreach ($commonLangFileArr as $commonLangFile)
						if (get_class($commonLangFile)=='GO\Base\Fs\File' && $commonLangFile->name()==$langCode.'.php') {
							$fileNames[] = str_replace(GO::config()->root_path,'',$commonLangFile->path());
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
						$fileNames[] = str_replace(GO::config()->root_path,'',$modLangFile->path());
				}
			}
		}
		
		$tmpFile = \GO\Base\Fs\File::tempFile($langCode.'-'.str_replace('.','-', GO::config()->version), 'zip');
		
		//exec zip
		$cmdString = GO::config()->cmd_zip.' '.$tmpFile->path().' '.implode(" ", $fileNames);
		exec($cmdString,$outputArr, $retVal);
		
		if($retVal>0)
			trigger_error("Creating ZIP file failed! ".implode("<br />", $outputArr), E_USER_ERROR);
		
		\GO\Base\Util\Http::outputDownloadHeaders($tmpFile);
		$tmpFile->output();
		$tmpFile->delete();
	}
	
	protected function actionCheckDefaultModels(){
		
		if(!$this->isCli() && !GO::user()->isAdmin())
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
		
		GO::getDbConnection()->query("DELETE FROM go_state WHERE name!='summary-active-portlets' AND user_id=".intval($params['user_id']));

		return array('success'=>true);
	}
	
	
	protected function actionRemoveEmptyStuff($params){
		
		if(!$this->isCli() && !GO::user()->isAdmin())
			throw new \GO\Base\Exception\AccessDenied();
		
		GO::session()->closeWriting();

		GO::setIgnoreAclPermissions(true);
		
		if(!$this->isCli())			
			echo '<pre>';
		
	
		
		if(GO::modules()->isInstalled("calendar")){
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

		
	}	
	
	
	protected function actionConvertToInnoDB(){
		GO::getDbConnection()->query("SET sql_mode = '';");
		
		$stmt = GO::getDbConnection()->query("SHOW TABLES");
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
