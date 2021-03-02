<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: File.php 7607 2011-09-01 15:40:20Z <<USERNAME>> $
 * @copyright Copyright Intermesh
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */

namespace GO\Files\Model;

use GO;
use go\core\exception\NotFound;

/**
 * The File model
 *
 * @property int $id
 * @property int $folder_id
 * @property String $name

 * @property int $locked_user_id
 * @property int $status_id
 * @property int $ctime
 * @property int $mtime
 * @property int $muser_id
 * @property int $size
 * @property int $user_id
 * @property String $comments
 * @property String $extension
 * @property int $expire_time
 * @property String $random_code
 * @property String $content_expire_date
 *
 * @property String $thumbURL
 *
 * @property String $downloadUrl
 *
 * @property String $path
 * @property \GO\Base\Fs\File $fsFile
 * @property Folder $folder
 * @property \GO\Base\Model\User $lockedByUser
 *
 * @property boolean $delete_when_expired
 */
class File extends \GO\Base\Db\ActiveRecord implements \GO\Base\Mail\SwiftAttachableInterface {

	use \go\core\orm\CustomFieldsTrait;

	public static $deleteInDatabaseOnly=false;

	private $_permissionLevel;

	public static $trimOnSave = false;

	/**
	 * Returns a static model of itself
	 *
	 * @param String $className
	 * @return File
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}
	
	/**
	 * Enable this function if you want this model to check the acl's automatically.
	 */
	public function aclField() {
		return 'folder.acl_id';
	}

	/**
	 * Returns the table name
	 */
	public function tableName() {
		return 'fs_files';
	}

	protected function getLocalizedName() {
		return \GO::t("File", "files");
	}

	public function customfieldsModel() {
		return "GO\Files\Customfields\Model\File";
	}

	public function hasLinks() {
		return true;
	}

	protected function getCacheAttributes() {

		$path = $this->path;

		//Don't cache tickets files because there are permissions issues. Everyone has read access to the types but may not see other peoples files.
		if(strpos($path, 'tickets/')===0){
			return false;
		}

		return array('name'=>$this->name, 'description'=>$path);
	}

	public function getLogMessage($action){
		return $this->path;
	}
	/**
	 * Here you can define the relations of this model with other models.
	 * See the parent class for a more detailed description of the relations.
	 */
	public function relations() {
		return array(
				'lockedByUser' => array('type' => self::BELONGS_TO, 'model' => 'GO\Base\Model\User', 'field' => 'locked_user_id'),
				'folder' => array('type' => self::BELONGS_TO, 'model' => 'GO\Files\Model\Folder', 'field' => 'folder_id'),
				'versions' => array('type'=>self::HAS_MANY, 'model'=>'GO\Files\Model\Version', 'field'=>'file_id', 'delete'=>true),
		);
	}

	public function getPermissionLevel(){

		if(\GO::$ignoreAclPermissions)
			return \GO\Base\Model\Acl::MANAGE_PERMISSION;

		if(!$this->aclField())
			return -1;

		if(!\GO::user())
			return false;

		//if($this->isNew && !$this->joinAclField){
		if(empty($this->{$this->aclField()}) && !$this->getIsJoinedAclField()){
			//the new model has it's own ACL but it's not created yet.
			//In this case we will check the module permissions.
			$module = $this->getModule();
			if($module=='base'){
				return \GO::user()->isAdmin() ? \GO\Base\Model\Acl::MANAGE_PERMISSION : false;
			}else
				return \GO::modules()->$module->permissionLevel;

		}else
		{
			if(!isset($this->_permissionLevel)){

				$acl_id = $this->findAclId();
				if(!$acl_id){
					throw new \Exception("Could not find ACL for ".$this->className()." with pk: ".$this->pk);
				}

				$this->_permissionLevel=\GO\Base\Model\Acl::getUserPermissionLevel($acl_id);// model()->findByPk($acl_id)->getUserPermissionLevel();
			}
			return $this->_permissionLevel;
		}

	}

	protected function init() {
		$this->columns['expire_time']['gotype'] = 'unixdate';
		$this->columns['content_expire_date']['gotype'] = 'unixdate';
		$this->columns['name']['required']=true;
		parent::init();
	}

	/**
	 * Check if a file is locked by another user.
	 *
	 * @return boolean
	 */
	public function isLocked(){
		return !empty($this->locked_user_id) && (!\GO::user() || $this->locked_user_id!=\GO::user()->id);
	}

	public function unlockAllowed(){
		return ($this->locked_user_id==\GO::user()->id || \GO::user()->isAdmin()) && $this->checkPermissionLevel(\GO\Base\Model\Acl::WRITE_PERMISSION);
	}

	public function getJsonData() {
			$data =  array(
					'id' => $this->id,
					'name' => $this->path,
					'ctime' => \GO\Base\Util\Date::get_timestamp($this->ctime),
					'mtime' => \GO\Base\Util\Date::get_timestamp($this->mtime),
					'extension' => $this->extension,
					'size' => $this->size,
					'user_id' => $this->user_id,
//					'type' => $this->type,
					'folder_id' => $this->folder_id,
					'type_id' => 'f:'.$this->id,
					'path' => $this->path,
					'locked' => $this->isLocked(),
					'locked_user_id' => $this->locked_user_id,
					'unlock_allowed' => $this->unlockAllowed(),
					'expire_time' => $this->expire_time > 0 ? \GO\Base\Util\Date::get_timestamp($this->expire_time,false) : '',
					'thumbs' => 0,
					'thumb_url' => $this->getThumbURL()
				);

			if(method_exists($this, "getCustomFields")) {
				$data['customFields'] = $this->getCustomFields()->toArray();
			}
			return $data;
	}

	/**
	 *
	 * @return \GO\Base\Fs\File
	 */
	private function _getOldFsFile(){

		if($this->isNew)
			return $this->fsFile;

		$filename = $this->isModified('name') ? $this->getOldAttributeValue('name') : $this->name;
		if($this->isModified('folder_id')){
			//file will be moved so we need the old folder path.
			$oldFolderId = $this->getOldAttributeValue('folder_id');
			$oldFolder = Folder::model()->findByPk($oldFolderId);
			$oldRelPath = $oldFolder->path;
			$oldPath = \GO::config()->file_storage_path . $oldRelPath . '/' . $filename;

		}else{
			$oldPath = \GO::config()->file_storage_path . $this->folder->path.'/'.$filename;
		}
		return new \GO\Base\Fs\File($oldPath);
	}

	protected function beforeDelete() {

		//blocked database check. We check this in the controller now.
		if($this->isLocked() && !\GO::user()->isAdmin())
			throw new \Exception(\GO::t("File is locked", "files").': '.$this->path);

		return parent::beforeDelete();
	}

	/**
	 * Check the disk and user quota
	 * @param integer $newBytes amount of bytes that are added when check succeeds
	 * @return boolean true if the check passed and the file may be added
	 */
	public static function checkQuota($newBytes) {
		$enoughQuota = true;
		$userQuota = \GO::user()->getDiskQuota();
		
		if ($userQuota) {
			$enoughQuota = \GO::user()->disk_usage + $newBytes <= $userQuota;
		}
		if ($enoughQuota && \GO::config()->quota > 0) {
			$currentQuota = \GO::config()->get_setting('file_storage_usage');
			$enoughQuota = $currentQuota + $newBytes <= (\GO::config()->quota * 1024);
		}
		
		return $enoughQuota;
	}

	public function checkNormalization() {
		if(!\go\core\util\StringUtil::isNormalized($this->name)) {
			\GO::debug("Normalizing file $this->id to Unicode Form C");
			
			$name = \go\core\util\StringUtil::normalize($this->name);		
			
			if($this->getFsFile()->exists()) {
				$this->getFsFile()->rename($name);
			}
			
			if(!$this->getIsNew()) {
				 go()->getDbConnection()->update('fs_files',['name' => $name], ['id' => $this->id])->execute();
			}
			$this->name = $name;
		}
	}

	protected function beforeSave() {
		
		//Normalize UTF-8. ONly form D works on MacOS webdav!
		$this->checkNormalization();

		//check permissions on the filesystem
		if($this->isNew){
			
			if(is_null($this->folder->fsFolder)){
				throw new \Exception("Folder ".$this->folder->path." cannot be found on disk, please check this path manually.");
			}
			
			if(!$this->folder->fsFolder->isWritable()){
				throw new \Exception("Folder ".$this->folder->path." is read only on the filesystem. Please check the file system permissions (hint: chown -R www-data:www-data /home/groupoffice)");
			}
		}else
		{
			if($this->isModified('name') || $this->isModified('folder_id')){
				if($this->_getOldFsFile()->exists() && !$this->_getOldFsFile()->isWritable())
					throw new \Exception("File ".$this->_getOldFsFile()->path ()." is read only on the filesystem. Please check the file system permissions (hint: chown -R www-data:www-data /home/groupoffice)");
			}
		}
		
		if($this->isNew || $this->isModified('name')){
			$existingFile = $this->folder->hasFile($this->name);
			if($existingFile && $existingFile->id!=$this->id)
				throw new \Exception(sprintf(\GO::t("Filename %s already exists", "files"), $this->path));
		}

		if(!$this->isNew){

			if($this->isModified('name')){
				//rename filesystem file.
				//throw new \Exception($this->getOldAttributeValue('name'));
				$oldFsFile = $this->_getOldFsFile();
				if($oldFsFile->exists())
					$oldFsFile->rename($this->name);

				$this->notifyUsers(
					$this->folder_id,
					FolderNotificationMessage::RENAME_FILE,
					$this->folder->path . '/' . $this->getOldAttributeValue('name'),
					$this->folder->path . '/' . $this->name
				);
			}

			if($this->isModified('folder_id')){
				if(!isset($oldFsFile))
					$oldFsFile = $this->_getOldFsFile();

				if (!$oldFsFile->move(new \GO\Base\Fs\Folder(\GO::config()->file_storage_path . dirname($this->path))))
					throw new \Exception("Could not rename folder on the filesystem");

				//get old folder objekt
                                $oldFolderId = $this->getOldAttributeValue('folder_id');
				$oldFolder = Folder::model()->findByPk($oldFolderId);

				$this->notifyUsers(
					array(
					    $this->getOldAttributeValue('folder_id'),
					    $this->folder_id
					),
					FolderNotificationMessage::MOVE_FILE,
					$oldFolder->path . '/' . $this->name,
					$this->path
				);
			}
		}

		if($this->isModified('locked_user_id')){
			$old_locked_user_id = $this->getOldAttributeValue('locked_user_id');
			if(!empty($old_locked_user_id) && $old_locked_user_id != \GO::user()->id && !\GO::user()->isAdmin())
				throw new \GO\Files\Exception\FileLocked();
		}
		
		
		$this->extension = $this->fsFile->extension();
		//make sure extension is not too long
		$this->cutAttributeLength("extension");

		$this->size = $this->fsFile->size();
		//$this->ctime = $this->fsFile->ctime();
		$this->mtime = $this->fsFile->mtime();

		

		return parent::beforeSave();
	}

	protected function getPath() {
		return $this->folder ? $this->folder->path . '/' . $this->name : $this->name;
	}
	
	public function getVirtualPath() {
		return $this->folder->getVirtualPath() . '/' . $this->name;
	}

	protected function getFsFile() {
		return new \GO\Base\Fs\File(\GO::config()->file_storage_path . $this->path);
	}

	private function _addQuota(){

		if($this->isModified('size') || $this->isNew) {
			$sizeDiff = (int) $this->fsFile->size() - (int) $this->getOldAttributeValue('size');

//			GO::debug("Adding quota: $sizeDiff for ".$this->folder->quotaUser->getName());
			if($this->folder->quotaUser){
				$this->folder->quotaUser->calculatedDiskUsage($sizeDiff)->save(true); //user quota
			}
			if(GO::config()->quota>0) {
				GO::config()->save_setting("file_storage_usage", GO::config()->get_setting('file_storage_usage')+$sizeDiff); //system quota
			}
		}

	}
	
	private function _removeQuota(){
		if(\GO::config()->quota>0){
			\GO::debug("Removing quota: $this->size");
			\GO::config()->save_setting("file_storage_usage", \GO::config()->get_setting('file_storage_usage')-$this->size);
		}

		if($this->folder->quotaUser){
			$this->folder->quotaUser->calculatedDiskUsage (0-$this->size)->save(true);
		}

	}
	
	public function checkPermissionLevel($level) {
			
//			var_dump($this->acl->description);
		
		//If this folder belongs to a contact or project etc. then we only need write permission to delete it.
		if($level == \GO\Base\Model\Acl::DELETE_PERMISSION && $this->folder->acl->usedIn != 'fs_folders.acl_id') {
			$level = \GO\Base\Model\Acl::WRITE_PERMISSION;
		}
		
		return parent::checkPermissionLevel($level);
	}
	
	private function getOldFolder() {
		return Folder::model()->findByPk($this->getOldAttributeValue('folder_id'));
	}
	
	
	public function checkOldPermissionLevel($level) {
		//If this folder belongs to a contact or project etc. then we only need write permission to delete it.
		if($level == \GO\Base\Model\Acl::DELETE_PERMISSION && $this->getOldFolder()->acl->usedIn != 'fs_folders.acl_id') {
			$level = \GO\Base\Model\Acl::WRITE_PERMISSION;
		}
		return parent::checkOldPermissionLevel($level);
	}

	protected function afterSave($wasNew) {
		$this->_addQuota();

		if ($wasNew) {
			$this->notifyUsers(
				$this->folder_id,
				FolderNotificationMessage::ADD_FILE,
                $this->name,
				$this->folder->path
			);
		} else {
			if ($this->isModified() && !$this->isModified('name') && !$this->isModified('folder_id')) {
				$this->notifyUsers(
					$this->folder_id,
					FolderNotificationMessage::UPDATE_FILE,
					$this->path
				);
			}
		}


		//touch the timestamp so it won't sync with the filesystem
		if($this->isModified('folder_id')){

			GO::debug("touching parent");
			$this->folder->touch();

			$oldParent = \GO\Files\Model\Folder::model()->findByPk($this->getOldAttributeValue('folder_id'));

			if($oldParent){
				GO::debug("touching old parent");
				$oldParent->touch();
			}
		}


		return parent::afterSave($wasNew);
	}
	
	public $customVersionPath = null;
	public function getVersionStoragePath() {
		if($this->customVersionPath!==null) {
			return $this->customVersionPath;
		}
		return 'versioning/'.$this->id;
	}

	protected function afterDelete() {

		$this->_removeQuota();

		if(!File::$deleteInDatabaseOnly)
			$this->fsFile->delete();

		$versioningFolder = new \GO\Base\Fs\Folder(\GO::config()->file_storage_path.$this->getVersionStoragePath());
		$versioningFolder->delete();

		$this->notifyUsers(
            $this->folder_id,
			FolderNotificationMessage::DELETE_FILE,
			$this->path
		);

		return parent::afterDelete();
	}

	/**
	 * The link that can be send in an e-mail as download link.
	 *
	 * @return StringHelper
	 */
	public function getEmailDownloadURL($html=true, $newExpireTime=false, $deleteWhenExpired=false) {

		if($newExpireTime && $this->expire_time < $newExpireTime){
			if($this->expire_time <= time() || empty($this->random_code)) {
				$this->random_code=\GO\Base\Util\StringHelper::randomPassword(11,'a-z,A-Z,0-9');
			}
			$this->expire_time = $newExpireTime;
			$this->delete_when_expired = $deleteWhenExpired;
			$this->save();
		}

		if (!empty($this->expire_time) && !empty($this->random_code)) {
			return \GO::url('files/file/download', array('id'=>$this->id,'inline'=>'false','random_code'=>$this->random_code), false, $html, false);
		}
	}


	/**
	 * The link to download the file.
	 * This function does not check the file download expire time and the random code
	 *
	 * @return StringHelper
	 */
	public function getDownloadURL($downloadAttachment=true, $relative=false) {
		return \GO::url('files/file/download', array('id'=>$this->id, 'inline'=>$downloadAttachment?'false':'true'), $relative);
	}


	public function getThumbURL($urlParams=array("lw"=>480, "ph"=>270, "zc"=>0)) {

		$urlParams['filemtime']=$this->mtime;
		$urlParams['src']=$this->path;

		if($this->extension=='svg'){
			return $this->getDownloadURL(false, true);
		}else
		{
			return \GO::url('core/thumb', $urlParams);
		}
	}

	/**
	 * Move a file to another folder
	 *
	 * @param Folder $destinationFolder
	 * @return boolean
	 */
	public function move($destinationFolder,$appendNumberToNameIfExists=false){

		$this->folder_id=$destinationFolder->id;
		if($appendNumberToNameIfExists)
			$this->appendNumberToNameIfExists();
		return $this->save();
	}

	/**
	 * Just the let someone kow the file was opened
	 */
	public function open() {
		$this->log('open');
	}

	/**
	 * Adds some extra info to the loggin of files
	 * @param StringHelper $action the action to log
	 * @param boolean $save unused
	 * @return boolean if save was successful
	 */
	protected function log($action, $save=true, $modifiedCustomfieldAttrs = false) {
		$log = parent::log($action, false, $modifiedCustomfieldAttrs);
		if(empty($log))
			return false;
		
		if($log === true) {
			return true;
		}
		
		if($log->action=='update') {
			$log->action = 'propedit';
			if($log->object->isModified('folder_id'))
				$log->action='moved';
			if($log->object->isModified('name')) {
				$log->action='renamed';
				$log->message = $log->object->getOldAttributeValue('name') . ' > ' . $log->message;
			}
		}
		return $save ? $log->save() : $log;
	}

	/**
	 * Copy a file to another folder.
	 *
	 * @param Folder $destinationFolder
	 * @param StringHelper $newFileName. Leave blank to use the same name.
	 * @return File
	 */
	public function copy($destinationFolder, $newFileName=false, $appendNumberToNameIfExists=false){

		$copy = $this->duplicate(array('folder_id'=>$destinationFolder->id), false, true);

		if($newFileName)
			$copy->name=$newFileName;

		if($appendNumberToNameIfExists)
			$copy->appendNumberToNameIfExists();

		$this->fsFile->copy($copy->fsFile->parent(), $copy->name);

		$copy->save(true);

		return $copy;
	}

	/**
	 * Import a filesystem file into the database.
	 *
	 * @param \GO\Base\Fs\File $fsFile
	 * @return File
	 */
	public static function importFromFilesystem(\GO\Base\Fs\File $fsFile){

		$folderPath = str_replace(\GO::config()->file_storage_path,"",$fsFile->parent()->path());

		$folder = Folder::model()->findByPath($folderPath, true);
		if(($file = $folder->hasFile($fsFile->name()))) {
			return $file;
		}
		return $folder->addFile($fsFile->name());
	}

	/**
	 * Replace filesystem file with given file.
	 *
	 * @param \GO\Base\Fs\File $fsFile
	 */
	public function replace(\GO\Base\Fs\File $fsFile, $isUploadedFile=false){

		if($this->isLocked())
			throw new \GO\Files\Exception\FileLocked();
//		for safety allow replace action
//		if(!File::checkQuota($fsFile->size()-$this->size))
//			throw new \GO\Base\Exception\InsufficientDiskSpace();
		if(!$this->isNew)
			$this->log('edit');
		$this->saveVersion();

		if(!$fsFile->move($this->folder->fsFolder,$this->name, $isUploadedFile)){		
			return false;
		}
		$fsFile->setDefaultPermissions();

		$old = $this->mtime;
		$this->mtime = $this->fsFile->mtime();
		$this->save();
		
		$this->fireEvent('replace', array($this, $isUploadedFile));
		
		return true;
	}

	public function putContents($data){
//		for safety allow replace actions
//		if(!File::checkQuota(strlen($data)))
//			throw new \GO\Base\Exception\InsufficientDiskSpace();

		$this->fsFile->putContents($data);
		$old = $this->mtime;
		$this->mtime = $this->fsFile->mtime();
		$this->save();
	}

	/**
	 * Copy current file to the versioning system.
	 */
	public function saveVersion(){

		$this->version++;
		
		$this->fireEvent('saveversion', array($this));
		
		if(\GO::config()->max_file_versions > -1){
			$version = new Version();
			$version->file_id = $this->id;
			$version->size_bytes = $this->size;
			$version->save();
		}
	}

	/**
	 * Find the file model by relative path.
	 *
	 * @param StringHelper $relpath Relative path from \GO::config()->file_storage_path
	 * @return File
	 */
	public function findByPath($relpath){
		$folder = Folder::model()->findByPath(dirname($relpath),false,array());
		if(!$folder)
			return false;
		else
		{
			return $folder->hasFile(\GO\Base\Fs\File::utf8Basename($relpath));
		}
	}

	/**
	 * Check if the file is an image.
	 *
	 * @return boolean
	 */
	public function isImage(){
		switch(strtolower($this->extension)){
			case 'ico':
			case 'jpg':
			case 'jpeg':
			case 'png':
			case 'gif':
			case 'bmp':
			case 'xmind':
			case 'svg':

				return true;
			default:
				return false;
		}
	}



	/**
	 * Checks if a filename exists and renames it.
	 *
	 * @param	StringHelper $filepath The complete path to the file
	 * @access public
	 * @return StringHelper  New filename
	 */
	public function appendNumberToNameIfExists()
	{
		$dir = $this->folder->path;
		$origName = $this->fsFile->nameWithoutExtension();
		$extension = $this->fsFile->extension();
		$x=1;
		$newName=$this->name;
		while($this->folder->hasFile($newName))
		{
			$newName=$origName.' ('.$x.').'.$extension;
			$x++;
		}
		$this->name=$newName;
		return $this->name;
	}

	/**
	 *
	 * @param type $folder_id
	 * @param type $type
	 * @param type $arg1
	 * @param type $arg2
	 */
	public function notifyUsers($folder_id, $type, $arg1, $arg2 = '') {
		FolderNotification::model()->storeNotification($folder_id, $type, $arg1, $arg2);
	}



	public function findRecent($start=false,$limit=false){
		$storeParams = \GO\Base\Db\FindParams::newInstance()->ignoreAcl();

		$joinSearchCacheCriteria = \GO\Base\Db\FindCriteria::newInstance()
					->addRawCondition('`t`.`id`', '`sc`.`entityId`')
					->addCondition('entityTypeId', $this->modelTypeId(),'=','sc');

		$storeParams->join(\GO\Base\Model\SearchCacheRecord::model()->tableName(), $joinSearchCacheCriteria, 'sc', 'INNER');


		$aclJoinCriteria = \GO\Base\Db\FindCriteria::newInstance()
							->addRawCondition('a.aclId', 'sc.aclId','=', false);

		$aclWhereCriteria = \GO\Base\Db\FindCriteria::newInstance()
						->addInCondition("groupId", \GO\Base\Model\User::getGroupIds(\GO::user()->id),"a", false);

		$storeParams->join(\GO\Base\Model\AclUsersGroups::model()->tableName(), $aclJoinCriteria, 'a', 'INNER');

		$storeParams->criteria(\GO\Base\Db\FindCriteria::newInstance()
								->addModel(Folder::model())
								->mergeWith($aclWhereCriteria));

		$storeParams->group(array('t.id'))->order('mtime','DESC');

		$storeParams->getCriteria()->addCondition('mtime', \GO\Base\Util\Date::date_add(\GO\Base\Util\Date::clear_time(time()),-7),'>');

		if ($start!==false)
			$storeParams->start($start);
		if ($limit!==false)
			$storeParams->limit($limit);

		return $this->find($storeParams);
	}

	public function getHandlers(){
		$handlers=array();
		$classes = \GO\Files\FilesModule::getAllFileHandlers();
		foreach($classes as $class){

			$fileHandler = new $class;
			if($fileHandler->fileIsSupported($this)){
				$handlers[]= $fileHandler;
			}
		}

		return $handlers;
	}


	public static $defaultHandlers;
	/**
	 *
	 * @return \GO\Files\Filehandler\FilehandlerInterface
	 */
	public function getDefaultHandler(){

		$ex = strtolower($this->extension);

		if(!isset(self::$defaultHandlers[$ex])){
			$fh = FileHandler::model()->findByPk(
						array('extension'=>$ex, 'user_id'=>\GO::user()->id));

			if($fh && class_exists($fh->cls)){
				self::$defaultHandlers[$ex]=new $fh->cls;
			}else{
				$classes = \GO\Files\FilesModule::getAllFileHandlers();
				foreach($classes as $class){

//					$fileHandler = new $class->name;
					$fileHandler = new $class();
					if($fileHandler->isDefault($this)){
						self::$defaultHandlers[$ex]= $fileHandler;
						break;
					}
				}

				if(!isset(self::$defaultHandlers[$ex]))
					self::$defaultHandlers[$ex]=new \GO\Files\Filehandler\Download();
			}
		}

		return self::$defaultHandlers[$ex];


	}

	/**
	 * Returns this file as swift attachment
	 * 
	 * @param string $altName
	 * @return Swift_Attachment
	 */
	public function getAttachment($altName = null) {
	
		$fullPath = $this->getFsFile()->path();
		
		$attachment = \Swift_Attachment::fromPath($fullPath);
		
		if($altName !== null){
			$attachment->setFilename($altName);
		}
		
		return $attachment;
	}

	/**
	 * @param GO\Base\Fs\File $outputFile
	 * @param string $format
	 * @throws NotFound
	 */
	public function convertTo(\GO\Base\Fs\File $outputFile, $format = 'pdf')
	{
		$converterModule = go()->getModule('business', 'fileconverter');
		if (!$converterModule) {
			throw new NotFound('Converter module is not available');
		}

		$service = \go\modules\business\fileconverter\Module::getAvailableService();
		$service->convert($this->fsFile, $outputFile, $format);
	}
}
