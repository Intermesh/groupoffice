<?php


namespace GO\Files\Controller;

use GO\Base\Exception\NotFound;
use GO\Files\Model\File;
use go\core\fs\Blob;
use go\core\fs\File as GoFile;
use go\core\fs\Folder;
use GO\Email\Controller\MessageController;

class FileController extends \GO\Base\Controller\AbstractModelController {

	protected $model = 'GO\Files\Model\File';
	
	protected function allowGuests() {
		return array('download'); //permissions will be checked manually in that action
	}
	
	protected function ignoreAclPermissions() {
		return array('correctquotauser');
	}
	
	protected function actionTest($params) {

		$md = new \go\core\fs\MetaData($this);
		$tag = $md->extractID3(__DIR__.'/test.mp3');
		var_dump($tag->toArray());
	}
	protected function actionExif(){
		
		$md = new \go\core\fs\MetaData($this);
		$exif = $md->extractExif(__DIR__.'/test.jpg');
		
	}

	public function actionCreateBlob($ids) {
		$ids = explode(',', $ids);
		$blobs = [];
		foreach($ids as $id) {
			$file = File::model()->findByPk($id);

			$fsFile = new GOFile($file->fsFile->path());
			$blob = Blob::fromFile($fsFile);
			$blob->save();			

			$blobs[] = ['name' => $file->name, 'blobId' => $blob->id];
		}

		return array_merge(['success' => true, 'blobs' => $blobs], $blob->toArray());
	}
	
	
	protected function actionExpiredList($params){
				
		$store = \GO\Base\Data\Store::newInstance(\GO\Files\Model\File::model());
		$store->getColumnModel()->formatColumn('path', '$model->path', array(), array('first_name', 'last_name'));

//		$findParams = $store->getDefaultParams($params);
		
		$findParams = \GO\Base\Db\FindParams::newInstance()->ignoreAcl();


		$joinSearchCacheCriteria = \GO\Base\Db\FindCriteria::newInstance()
					->addRawCondition('`t`.`id`', '`sc`.`entityId`')
					->addCondition('entityTypeId', \GO\Files\Model\File::model()->modelTypeId(),'=','sc');

		$findParams->join(\GO\Base\Model\SearchCacheRecord::model()->tableName(), $joinSearchCacheCriteria, 'sc', 'INNER');


		$aclJoinCriteria = \GO\Base\Db\FindCriteria::newInstance()
							->addRawCondition('a.aclId', 'sc.aclId','=', false);

		$aclWhereCriteria = \GO\Base\Db\FindCriteria::newInstance()
						->addInCondition("groupId", \GO\Base\Model\User::getGroupIds(\GO::user()->id),"a", false);

		$findParams->join(\GO\Base\Model\AclUsersGroups::model()->tableName(), $aclJoinCriteria, 'a', 'INNER');

		$findParams->criteria(\GO\Base\Db\FindCriteria::newInstance()
								->addModel(\GO\Files\Model\Folder::model())
								->mergeWith($aclWhereCriteria));

		$findParams->group(array('t.id'))->order('mtime','DESC');
		
		$findParams->getCriteria()->addCondition('content_expire_date', time() ,'<');

		$store->setStatement (\GO\Files\Model\File::model()->find($findParams));
		
		$response = $store->getData();
		$response['total'] = $store->getTotal();
		
		return $response;
		
	}
	
	/**
	 * Will calculate the used diskspace per user
	 * If no ID is passed diskspace will be recalculated for all user
	 * @param integer $id id of the user to recalculate used space for
	 */
	protected function actionRecalculateDiskUsage($id=false) {
		
		\GO::session()->closeWriting();
		
		$users = array();
		if(!empty($id)) {
			$user = \GO\Base\Model\User::model()->findByPk($id);
			if(!empty($user)) {
				$users[] = $user;
			}
		} else {
			$users = \GO\Base\Model\User::model()->find();
		}
		
		foreach($users as $user) {
			if($user->calculatedDiskUsage()->save())
				echo $user->getName() . ' uses ' . $user->disk_usage. "<br>\n";
		}
	}
	
	protected function actionCorrectQuotaUser() {
		$time_start = microtime(true); 
		$count = 0;
		
		$userFolder = \GO\Files\Model\Folder::model()->findByPath('users');
		foreach($userFolder->folders() as $homeFolder) {
			$homeId = $homeFolder->user_id;
			
			$walkSubfolders = function($folder) use($homeId, &$walkSubfolders, &$count) {
				
				//echo $folder->path.' -> '.$homeId.'<br />';
				$folder->quota_user_id = $homeId;
				if(!$folder->save()) {
					throw new \Exception("Could not save folder: ".var_export($folder->getValidationErrors(), true));
				}

				foreach($folder->folders() as $subFolder) {
					$walkSubfolders($subFolder);					
					$count++;
				}
			};
			$walkSubfolders($homeFolder);
		}
		$time_end = microtime(true);
		$execution_time = ($time_end - $time_start);
		echo '<b>'.$count.' Folders updated in:</b> '.$execution_time.' Seconds';
	}
	
	protected function actionDisplay($params) {
		
		//custom fields send path as ID.
		if(!empty($params['id']) && !is_numeric($params['id'])){
			$file = \GO\Files\Model\File::model()->findByPath($params['id']);
			$params['id']=$file->id;
		}
		
		return parent::actionDisplay($params);
	}

	
	protected function afterDisplay(&$response, &$model, &$params) {

		$response['data']['path'] = $model->path;
		$response['data']['size'] = \GO\Base\Util\Number::formatSize($model->fsFile->size());
		$response['data']['extension'] = strtolower($model->fsFile->extension());
		$response['data']['type'] = \GO::t($response['data']['extension'], 'base', 'filetypes');
		
		$response['data']['locked_user_name']=$model->lockedByUser ? $model->lockedByUser->name : '';
		$response['data']['locked']=$model->isLocked();
		$response['data']['unlock_allowed']=$model->unlockAllowed();
		

		if (!empty($model->random_code) && time() < $model->expire_time) {
			$response['data']['expire_time'] = \GO\Base\Util\Date::get_timestamp(\GO\Base\Util\Date::date_add($model->expire_time, -1),false);
			$response['data']['download_link'] = $model->emailDownloadURL;
		} else {
			$response['data']['expire_time'] = "";
			$response['data']['download_link'] = "";
		}
		
		$response['data']['url']=\GO::url('files/file/download',array('id'=>$model->id), false, true);

		if ($model->fsFile->isImage())
			$response['data']['thumbnail_url'] = $model->thumbURL;
		else
			$response['data']['thumbnail_url'] = "";
		
		$response['data']['handler']='startjs:function(){'.$model->getDefaultHandler()->getHandler($model).'}:endjs';
		
		try{
			if(\GO::modules()->filesearch){
				$filesearch = \GO\Filesearch\Model\Filesearch::model()->findByPk($model->id);
//				if(!$filesearch){
//					$filesearch = \GO\Filesearch\Model\Filesearch::model()->createFromFile($model);
//				}
				if($filesearch){
					$response['data']=array_merge($filesearch->getAttributes('formatted'), $response['data']);
				

					if (!empty($params['query_params'])) {
						$qp = json_decode($params['query_params'], true);
						if (isset($qp['content_all'])){

							$c = new \GO\Filesearch\Controller\FilesearchController();

							$response['data']['text'] = $c->highlightSearchParams($qp, $response['data']['text']);
						}
					}
				}else
				{
					$response['data']['text'] = \GO::t("This file has not been indexed yet", "filesearch");
				}
			}
		}
		catch(\Exception $e){
			\GO::debug((string) $e);
			
			$response['data']['text'] = "Index out of date. Please rebuild it using the admin tools.";
		}

		return parent::afterDisplay($response, $model, $params);
	}

	protected function afterLoad(&$response, &$model, &$params) {

		$response['data']['path'] = $model->path;
		$response['data']['size'] = \GO\Base\Util\Number::formatSize($model->fsFile->size());
		$response['data']['extension'] = strtolower($model->fsFile->extension());
		$response['data']['type'] = \GO::t($response['data']['extension'], 'base', 'filetypes');
		
		$response['data']['name']=$model->fsFile->nameWithoutExtension();
		
		if (!empty($model->user))
			$response['data']['username']=$model->user->name;
		if (!empty($model->mUser))
			$response['data']['musername'] = $model->mUser->name;
		$response['data']['locked_user_name']=$model->lockedByUser ? $model->lockedByUser->name : '';
		
		
		
		$fh = \GO\Files\Model\FileHandler::model()->findByPk(
						array('extension'=>$model->extension, 'user_id'=>\GO::user()->id));
		if($fh){
			$fileHandler = new $fh->cls;
			
			$response['data']['handlerCls']=$fh->cls;
			$response['data']['handlerName']=$fileHandler->getName();
		}else
		{
			$response['data']['handlerCls']="";
			$response['data']['handlerName']="";
		}
		

		return parent::afterLoad($response, $model, $params);
	}
	
	protected function beforeSubmit(&$response, &$model, &$params) {
		
		if(isset($params['name'])){		
			$params['name'] = \GO\Base\Fs\File::stripInvalidChars($params['name']); // Strip invalid chars
			if(isset($params['extension'])) {
				$params['name'].='.'.$params['extension'];
				$model->extension = $params['extension'];
			} else if(!empty($model->fsFile->extension())) {
				$params['name'].='.'.$model->fsFile->extension();
			}
		}
		
		if(isset($params['lock'])){
			//GOTA sends lock parameter It does not know the user ID.
			$model->locked_user_id=empty($params['lock']) ? 0 : \GO::user()->id;
		}
		
		
		$fh = \GO\Files\Model\FileHandler::model()->findByPk(array(
			'extension' => strtolower($model->extension), 
			'user_id' => \GO::user()->id
		));
		
		if(!$fh)
			$fh = new \GO\Files\Model\FileHandler();
		
		$fh->extension=strtolower($model->extension);
		
		if(isset($params['handlerCls']))
			$fh->cls=$params['handlerCls'];
		
		if(empty($params['handlerCls']))
			$fh->delete();
		else
			$fh->save();
		
		return parent::beforeSubmit($response, $model, $params);
	}
	
	protected function actionHandlers($params){
		if(!empty($params['path'])){
			$folder = \GO\Files\Model\Folder::model()->findByPath(dirname($params['path']));
			$file = $folder->hasFile(\GO\Base\Fs\File::utf8Basename($params['path']));
		}else
		{
			$file = \GO\Files\Model\File::model()->findByPk($params['id'], false, true);
		}

		if(empty($params['all'])){
			$fileHandlers = array($file->getDefaultHandler());
		}else
		{
			$fileHandlers = $file->getHandlers();
		}
//	var_dump($fileHandlers);
		
		$store = new \GO\Base\Data\ArrayStore();
		
		foreach($fileHandlers as $fileHandler){	
			$store->addRecord(array(
					'name'=>$fileHandler->getName(),
					'handler'=>$fileHandler->getHandler($file),
					'iconCls'=>$fileHandler->getIconCls(),
					'cls'=>  get_class($fileHandler),
					'extension'=>$file->extension
			));	
		}	
		
		return $store->getData();		
	}
	
	protected function actionSaveHandler($params){
//		\GO::config()->save_setting('fh_'.$, $value)
		
		$fh = \GO\Files\Model\FileHandler::model()->findByPk(
						array('extension'=>strtolower($params['extension']), 'user_id'=>\GO::user()->id));
		
		if(!$fh)
			$fh = new \GO\Files\Model\FileHandler();
		
		$fh->extension=strtolower($params['extension']);
		$fh->cls=$params['cls'];
		return array('success'=>empty($params['cls']) ? $fh->delete() : $fh->save());
	}
	
	
	protected function actionOpen($params) {
		if(!empty($params['path'])) {
			$file = \GO\Files\Model\File::model()->findByPath($params['path']);
		} else
		{
			$file = \GO\Files\Model\File::model()->findByPath($params['id']);
		}

		if(!$file){
			throw new \Exception("File not found");
		}
		
		$response = [
				'success' => true,
				'file' => $file->getAttributes(),
				'handler' => 'startjs:function(){'.$file->getDefaultHandler()->getHandler($file).'}:endjs'
		];
		
		return $response;
	}
	

	protected function actionDownload($params) {
		\GO::session()->closeWriting();
		
		\GO::setMaxExecutionTime(0);
		
		if(isset($params['path'])){
			$folder = \GO\Files\Model\Folder::model()->findByPath(dirname($params['path']));
			if(!$folder) {
			  throw new NotFound($params['path']);
      }
			$file = $folder->hasFile(\GO\Base\Fs\File::utf8Basename($params['path']));
		}else
		{
			$file = \GO\Files\Model\File::model()->findByPk($params['id'], false, true);
		}
		
		if(!$file)
			throw new \GO\Base\Exception\NotFound();
		
		if(!empty($params['random_code'])){
			if($file->random_code!=$params['random_code'])
				throw new \GO\Base\Exception\NotFound();
			
			if(time()>$file->expire_time)
				throw new \Exception(\GO::t("Sorry, the download link for this file has expired", "files"));				
		} else {
			$public = substr($file->path,0,6)=='public';

			if (!$public) {
				if (!\GO::user() || !$file->checkPermissionLevel(\GO\Base\Model\Acl::READ_PERMISSION)) {
					throw new \GO\Base\Exception\AccessDenied();
				}
			}
		}

		
		// Show the file inside the browser or give it as a download
		$inline = true; // Defaults to show inside the browser
		if(isset($params['inline']) && ((bool) $params['inline'] === false) || $params['inline'] == 'false') {
			$inline = false;
		}

		\GO\Base\Util\Http::outputDownloadHeaders($file->fsFile, $inline, !empty($params['cache']));
		$file->open();
		
		$this->fireEvent('beforedownload', array(
				&$this,
				&$params,
				&$file
		));
		
		$file->fsFile->output();
	}

	/**
	 *
	 * @param type $params 
	 * @todo
	 */
	protected function actionCreateDownloadLink($params){
		
		$response=array();
		
		$file = \GO\Files\Model\File::model()->findByPk($params['id']);
		
		$url = $file->getEmailDownloadURL(true,\GO\Base\Util\Date::date_add($params['expire_time'],1),$params['delete_when_expired']);
		
		$response['url']=$url;
		$response['success']=true;
		
		return $response;
		
	}	
	
	/**
	 * This action will generate multiple Email Download link and return a JSON
	 * response with the generated links in the email subject
	 * @param array $params
	 * - string ids: json encode file ids to mail
	 * - timestamp expire_time: chosen email link expire time 
	 * - int template_id: id of used template
	 * - int alias_id: id of alias to mail from
	 * - string content_type : html | plain  
	 * @return StringHelper Json response
	 */
	protected function actionEmailDownloadLink($params){
		$msgController = new MessageController();
		$templateContent = $msgController->loadTemplate($params);
		$files = \GO\Files\Model\File::model()->findByAttribute('id', json_decode($params['ids']));
		
		$html=$params['content_type']=='html';
		$bodyindex = $html ? 'htmlbody' : 'plainbody';
		$lb = $html ? '<br />' : "\n";

		$text = $html ? \GO::t("Click on the link to download the file", "files") : \GO::t("Click the secured link below or copy it to your browser's address bar to download the file.", "files");
		$linktext = $html ? "<ul>" : $lb;
		
		foreach($files as $file) {
			$url = $file->getEmailDownloadURL($html,\GO\Base\Util\Date::date_add($params['expire_time'],1),$params['delete_when_expired']);
			$linktext .= $html ?  '<li><a href="'.$url.'">'.$file->name.'</a></li>'.$lb : $url.$lb;
		}
		$linktext .= $html ? "</ul>" : "\n";
		$text .= ' ('.\GO::t("possible until", "files").' '.\GO\Base\Util\Date::get_timestamp(\GO\Base\Util\Date::date_add($file->expire_time,-1), false).')'.$lb;
		$text .= $linktext;

		$params['body']= $text;

    $msgController = new MessageController();
    $response = $msgController->loadTemplate($params);

//		$response['data'][$bodyindex]=$text;
				
		$response['data']['subject'] = \GO::t("Download link", "files"); //.' '.$file->name;
		$response['success']=true;
		
		return $response;
	}
	
	
	public function actionRecent($params){
		
		$start = !empty($params['start']) ? $params['start'] : 0;
		$limit = !empty($params['limit']) ? $params['limit'] : 20;
		
		$store = \GO\Base\Data\Store::newInstance(\GO\Files\Model\File::model());

		$store->getColumnModel()->formatColumn('path', '$model->path', array(), array('first_name', 'last_name'));
		$store->getColumnModel()->formatColumn('weekday', '$fullDays[date("w", $model->mtime)]." ".\GO\Base\Util\Date::get_timestamp($model->mtime, false);', array('fullDays'=>\GO::t("full_days")),array('first_name', 'last_name'));
		
		$store->setStatement(\GO\Files\Model\File::model()->findRecent($start,$limit));

		$response = $store->getData();
		
		$store->setStatement(\GO\Files\Model\File::model()->findRecent());
		$response['total'] = $store->getTotal();
		
		return $response;
	}
	
	public function actionCleanup() {
		
		$cleanupRoot = \GO::config()->file_storage_path.'cleanup/';
		
		\GO\Files\Model\File::$deleteInDatabaseOnly = true;
						
		
		
		$findParams = \GO\Base\Db\FindParams::newInstance();
		
		
		$findCriteria = \GO\Base\Db\FindCriteria::newInstance()->addRawCondition("t.name REGEXP '^.+ \\\([0-9]+\\\)\\\..+'");
		$findParams->criteria($findCriteria);
		$stmt = \GO\Files\Model\File::model()->find($findParams);
		
		foreach ($stmt as $copySubfixFile) {
			
			
			$name = preg_replace('/(\w+) (\\([0-9]+\)).(\w+)/i', '${1}.$3', $copySubfixFile->name);
			
			$findParams = \GO\Base\Db\FindParams::newInstance();
			$findCriteria = \GO\Base\Db\FindCriteria::newInstance()->addCondition('name', $name)->addCondition('folder_id', $copySubfixFile->folder_id);
			$findParams->criteria($findCriteria);
			$stmt2 = \GO\Files\Model\File::model()->find($findParams);
			
			foreach ($stmt2 as $file) {
				
				if($file->fsFile->md5Hash() == $copySubfixFile->fsFile->md5Hash()) {
					echo $copySubfixFile->path . ' ## ' . $copySubfixFile->folder_id. "<br/>";
					
					$cleanupPath = $cleanupRoot.$copySubfixFile->folder->getFullPath(); //projects2/Projectnaam/bestand (1).jpg
					
					$folderTo = new \GO\Base\Fs\Folder($cleanupPath);
					$folderTo->create();
					
					if($folderTo->exists()) {
						if(!$copySubfixFile->fsFile->move($folderTo)) {
							throw new Exception('file move error from: '. $copySubfixFile->path . ' to '. $folderTo->getFullPath());
						}
						
						$copySubfixFile->delete();
					} else {
						throw new Exception('Folder do not exists: '.$cleanupPath);
					}
					
				}
			}
			
			
		}
	}
	
	
	
}

