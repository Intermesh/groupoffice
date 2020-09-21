<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: Folder.php 7607 2011-09-01 15:44:36Z <<USERNAME>> $
 * @copyright Copyright Intermesh
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */

namespace GO\Files\Model;

use GO;

/**
 * The Folder model
 *
 * Top level folders with parent_id=0 are readable to everyone with access to
 * the files module automatically. This is done in the validate() function of this model.
 *
 * A shared folder has an acl_id set. When the system checks permissions it will
 * recursively search up the tree until it finds a folder that has an acl_id.
 *
 * @property int $user_id
 * @property int $id
 * @property int $parent_id
 * @property StringHelper $name
 * @property StringHelper $path Relative path from \GO::config()->file_storage_path
 * @property boolean $visible When this folder is shared it only shows up in the tree when visible is set to true
 * @property int $acl_id
 * @property StringHelper $comments
 * @property boolean $thumbs Show this folder in thumbnails
 * @property int $ctime
 * @property int $mtime
 * @property int $muser_id
 * @property int $quota_user_id The filesize will be substracted from this user its quota
 * @property boolean $readonly Means this folder is readonly even to the administrator! eg. Home folders may never be edited.
 * @property StringHelper $cm_state The stored state of the column model whebn apply state is true
 * @property boolean $apply_state Apply the configured state of the column model to everybody.
 * @property \GO\Base\Fs\Folder $fsFolder
 * @property int $acl_write
 */
class Folder extends \GO\Base\Db\ActiveRecord {

	private $_path;
	
	public $recursiveApplyCustomFieldCategories = false;

	//prevents acl id's to be generated automatically by the activerecord.
	public $isJoinedAclField = true;

	public static $trimOnSave = false;

	public function aclOverwrite() {
		return false;
	}

	/**
	 *
	 * @var boolean Set to true by a system save so the readonly flag won't take effect in beforeSave
	 */
	public $systemSave = false;

	public static $deleteInDatabaseOnly = false;

	protected function init() {
		$this->columns['name']['required'] = true;
		return parent::init();
	}

	public function customfieldsModel() {
		return "GO\Files\Customfields\Model\Folder";
	}

	protected function getCacheAttributes() {

		//	Otherwise it would break 3.7 to 4.X upgrade
		if (\GO::router()->getControllerRoute()=='maintenance/upgrade') {
			return false;
		}

		$path = $this->path;

		//Don't cache tickets files because there are permissions issues. Everyone has read access to the types but may not see other peoples files.
		if(strpos($path, 'tickets/')===0){
			return false;
		}

		return array('name'=>$this->name, 'description'=>$path);
	}

	/**
	 * Enable this function if you want this model to check the acl's automatically.
	 */
	public function aclField() {
		return 'acl_id';
	}

	public function findAclId() {
		//folder may have an acl ID if they don't have one we must recurse up the tree
		//to find the acl.
		if ($this->acl_id > 0){
			return $this->acl_id;
		}elseif($this->parent)
			return $this->parent->findAclId();
		else
			return false;
	}

	public function hasLinks() {
		return true;
	}
	
	/**
	 * New records derrive the customfield settings from their parent
	 * @see customfields/CategoryController::actionSetAnabled
	 */
	public function deriveCustomfieldSettings(Folder $from) {
		
		if(!\GO::modules()->isInstalled('customfields')) {
			return true;
		}
		
		$success = true;
		//cleanup
		$enabledCategories = \GO\Customfields\Model\EnabledCategory::model()->findByAttributes(array(
			'model_name'=>'GO\Files\model\File',
			'model_id'=>$this->id
		));
		$enabledCategories->callOnEach('delete');
		//find
		$disableCategories = \GO\Customfields\Model\DisableCategories::model()->findByPk(array(
			'model_id'=>$this->id,
			'model_name'=>'GO\Files\model\File'
		));
		
		if(!\GO\Customfields\Model\DisableCategories::isEnabled('GO\Files\model\File', $from->id)) {
			
			if(!empty($disableCategories)) { //clean me when $from is not active
				
				$success = $disableCategories->delete();
			}
			return $success;
		} 
		
		if(empty($disableCategories)) {
			$disableCategories = new \GO\Customfields\Model\DisableCategories();
			$disableCategories->model_name='GO\Files\model\File';
			$disableCategories->model_id=$this->id;
			$success = $disableCategories->save() && $success;
		}
		$parentCategories = \GO\Customfields\Model\EnabledCategory::model()->findByAttributes(array(
			'model_name'=>'GO\Files\model\File',
			'model_id'=>$from->id
		));
		//create
		foreach($parentCategories as $category){
			$enabled = $category->duplicate(array('model_id'=>$this->id));
			$success = $enabled->save() && $success;
		}
		return $success;
	}
	
	public function recursivlyApplyCustomfieldSettings() {
		
		if(!\GO::modules()->isInstalled('customfields')) {
			return true;
		}
		
		$ids = $this->getSubFolderIds();
		foreach($ids as $subfolder_id) {
			if($subfolder_id==$this->id) {
				continue; //skip
			}
			$subfolder = Folder::model()->findByPk($subfolder_id);
			$subfolder->deriveCustomfieldSettings($this);
			$subfolder->recursiveApplyCustomFieldCategories = true;
			$subfolder->save();
		}
	}

	/**
	 * Returns the table name
	 */
	public function tableName() {
		return 'fs_folders';
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
				'parent' => array('type' => self::BELONGS_TO, 'model' => 'GO\Files\Model\Folder', 'field' => 'parent_id'),
				'quotaUser' => array('type' => self::BELONGS_TO, 'model' => 'GO\Base\Model\User', 'field' => 'quota_user_id'),
				'folders' => array('type' => self::HAS_MANY, 'model' => 'GO\Files\Model\Folder', 'field' => 'parent_id', 'delete' => true, 'findParams'=>  \GO\Base\Db\FindParams::newInstance()->order('name','ASC')),
				'files' => array('type' => self::HAS_MANY, 'model' => 'GO\Files\Model\File', 'field' => 'folder_id', 'delete' => true),
				'notifyUsers'=>array('type' => self::HAS_MANY, 'model' => 'GO\Files\Model\FolderNotification', 'field' => 'folder_id', 'delete' => true),
				'preferences'=>array('type' => self::HAS_MANY, 'model' => 'GO\Files\Model\FolderPreference', 'field' => 'folder_id', 'delete' => true),
				'sharedRootFolders'=>array('type' => self::HAS_MANY, 'model' => 'GO\Files\Model\SharedRootFolder', 'field' => 'folder_id', 'delete' => true),
		);
	}

	protected function getLocalizedName() {
		return \GO::t('folder', 'files');
	}

	/**
	 * This getter recursively builds the folder path.
	 * @return StringHelper
	 */
	protected function getPath($forceResolve=false) {

		if($forceResolve || !isset($this->_path)){
			$this->_path = $this->name;
			$currentFolder = $this;


			$ids=array();

			if(!empty($this->id))
				$ids[]=$this->id;

			while ($currentFolder = $currentFolder->parent) {

				if(in_array($currentFolder->id, $ids))
					throw new \Exception("Infinite folder loop detected in ".$this->_path." ".implode(",", $ids));
				else
					$ids[]=$currentFolder->id;

				$this->_path = $currentFolder->name . '/' . $this->_path;
			}
		}
		return $this->_path;
	}

	public function getFullPath() {
		$currentFolder = $this;
		
		$ids=array();
		if(!empty($this->id))
			$ids[]=$this->id;
			
		$fullPath = $this->name;
		while ($currentFolder = $currentFolder->parent) {

			if(in_array($currentFolder->id, $ids))
				throw new \Exception("Infinite folder loop detected in ".$this->_path." ".implode(",", $ids));
			else
				$ids[]=$currentFolder->id;

			$fullPath = $currentFolder->name . '/' . $fullPath;

		}

		
		return $fullPath;
	}
	
	/**
	 * Get a URL to show the folder directy in the files module.
	 *
	 * @return StringHelper
	 */
	public function getExternalURL(){
		return \GO::createExternalUrl("files", "showFolder", array($this->id));
	}

	public function getFolderIdsInPath($folder_id){
		$ids=array();
		$currentFolder = Folder::model()->findByPk($folder_id);

		if(!$currentFolder)
			return $ids;

		while ($currentFolder = $currentFolder->parent) {
			$ids[] = $currentFolder->id;
		}
		return $ids;
	}

	protected function getFsFolder() {
		return new \GO\Base\Fs\Folder(\GO::config()->file_storage_path . $this->path);
	}


	private function _checkParentId(){
		if($this->isModified("parent_id") && !empty($this->id)){
			$currentFolder=$this;

			while ($currentFolder = $currentFolder->parent) {
				if($currentFolder->id==$this->id){
					$this->setValidationError ("parent_id", "Can not move folder into this folder because it's a child");
					break;
				}
			}

			//throw new \Exception("test");
		}
	}

	public function validate() {

		$this->_checkParentId();

		if($this->parent_id==0 && $this->acl_id==0){
			//top level folders are readonly to everyone.
			$this->readonly=1;
			$this->acl_id=\GO::modules()->files->acl_id;
		}
		return parent::validate();
	}

	/**
	 *
	 * @return \GO\Base\Fs\Folder
	 */
	private function _getOldFsFolder(){

		if($this->isNew)
			return $this->fsFolder;

		$filename = $this->isModified('name') ? $this->getOldAttributeValue('name') : $this->name;
		if($this->isModified('parent_id')){
			//file will be moved so we need the old folder path.
			$oldFolderId = $this->getOldAttributeValue('parent_id');
			$oldFolder = Folder::model()->findByPk($oldFolderId, false, true);
			if($oldFolder){
				$oldRelPath = $oldFolder->path;
				$oldPath = \GO::config()->file_storage_path . $oldRelPath . '/' . $filename;
			}else
			{
				return false;
			}

		}else{
			
			$parentPath = $this->parent ? $this->parent->path.'/' : '';
			
			$oldPath = \GO::config()->file_storage_path . $parentPath.$filename;
		}
		return new \GO\Base\Fs\Folder($oldPath);
	}

	protected function beforeSave() {


		//check permissions on the filesystem
		if($this->isNew){
			
			if(!$this->fsFolder->firstExistingParent()->isWritable()){
				throw new \Exception("Folder ".$this->fsFolder->firstExistingParent()->stripFileStoragePath()." (Creating ".$this->name.") is read only on the filesystem. Please check the file system permissions (hint: chown -R www-data:www-data /home/groupoffice)");
			}
		}else
		{
			if($this->isModified('name') || $this->isModified('parent_id')){			
				if($this->_getOldFsFolder() && $this->_getOldFsFolder()->exists() && !$this->_getOldFsFolder()->isWritable())
					throw new \Exception("Folder ".$this->path." is read only on the filesystem. Please check the file system permissions (hint: chown -R www-data:www-data /home/groupoffice)");
			}
		}

		if(!$this->systemSave && !$this->isNew && $this->readonly){
			if($this->isModified('name') || $this->isModified('folder_id'))
				return false;
		}

		if($this->parent){
			$existingFolder = $this->parent->hasFolder($this->name);
			if($existingFolder && $existingFolder->id!=$this->id)
				throw new \Exception(\GO::t('folderExists','files').': '.$this->path);
		}
		
		if($this->isNew && empty($this->quota_user_id)){
			$shared_folder = $this;
			while(!$shared_folder->isSomeonesHomeFolder() && $shared_folder->parent_id!=0) {
				$shared_folder = $shared_folder->parent;
			}
			$this->quota_user_id = $shared_folder->user_id;
		}

		return parent::beforeSave();
	}

	public function setAttribute($name, $value, $format = false) {

		//so that path gets resolved again
		if($name=='parent_id')
			$this->_path=null;

		return parent::setAttribute($name, $value, $format);
	}

	protected function afterSave($wasNew) {

		if ($wasNew) {

			$this->fsFolder->create();


			//sync parent timestamp
			if($this->parent){
				$this->parent->mtime=$this->parent->fsFolder->mtime();
				$this->parent->save(true);

				$this->notifyUsers(
					$this->parent->id,
					FolderNotificationMessage::ADD_FOLDER,
					$this->name,
					$this->parent->getPath()
				);
				
				$this->deriveCustomfieldSettings($this->parent);
			}
			
		} else {

			$this->_path=null;

			if(!$this->fsFolder->exists()){

				if($this->isModified('parent_id')){
					Folder::model()->clearFolderCache();
					//file will be moved so we need the old folder path.
					$oldFolderId = $this->getOldAttributeValue('parent_id');
					$oldFolder = Folder::model()->findByPk($oldFolderId, false, true);
					$oldRelPath = $oldFolder->path;

					$oldName = $this->isModified('name') ? $this->getOldAttributeValue('name') : $this->name;

					$oldPath = \GO::config()->file_storage_path . $oldRelPath . '/' . $oldName;

					$fsFolder = new \GO\Base\Fs\Folder($oldPath);

					$newRelPath = $this->getPath(true);

					$newFsFolder = new \GO\Base\Fs\Folder(\GO::config()->file_storage_path . dirname($newRelPath));

					if (!$fsFolder->move($newFsFolder))
						throw new \Exception("Could not rename folder on the filesystem");

					$this->notifyUsers(
						array(
						    $this->id,
						    $oldFolder->id,
						    $this->parent->id
						),
						FolderNotificationMessage::MOVE_FOLDER,
						$oldRelPath . '/' . $oldName,
						$newRelPath
					);
				}

				//if the filesystem folder is missing check if we need to move it when the name or parent folder changes.
				if($this->isModified('name')){

					Folder::model()->clearFolderCache();

					\GO::debug("Renaming from ".$this->getOldAttributeValue('name')." to ".$this->name);

					$oldFsFolder = new \GO\Base\Fs\Folder(dirname($this->fsFolder->path()).'/'.$this->getOldAttributeValue('name'));

					$oldFsFolder->rename($this->name);

					$this->notifyUsers(
						array(
                            $this->id,
                            $this->parent->id
                        ),
						FolderNotificationMessage::RENAME_FOLDER,
						$this->parent->path . '/' . $this->getOldAttributeValue('name'),
						$this->parent->path . '/' . $this->name
					);
				}
			}
			
			if($this->recursiveApplyCustomFieldCategories == 'true') {
				$this->recursivlyApplyCustomfieldSettings();
			}
		}

		//sync parent timestamp

		if($this->isModified('parent_id')){
			if($this->parent){

				$oldFolderId = $this->getOldAttributeValue('parent_id');
				$oldFolder = \GO\Files\Model\Folder::model()->findByPk($oldFolderId, false, true);

					//so it won't sync the filesystem
				if($oldFolder){

					GO::debug('touching old parent');
					$oldFolder->touch();
				}

				GO::debug('touching parent');
				$this->parent->touch();
			}
		}

		return parent::afterSave($wasNew);
	}
	
	public function getPermissionLevel() {
		
		$level = parent::getPermissionLevel();
		if($level == \GO\Base\Model\Acl::WRITE_PERMISSION && $this->acl->description != 'fs_folders.acl_id') {
			$level = \GO\Base\Model\Acl::DELETE_PERMISSION;
		}
		
		return $level;		
	}


	public function delete($ignoreAcl = false) {

		if(!$ignoreAcl && $this->readonly){
			throw new \Exception(\GO::t('dontDeleteSystemFolder','files'));
		}

		return parent::delete($ignoreAcl);
	}

	protected function afterDelete() {

		\GO::debug("after delete ".$this->path." ".$this->fsFolder->path());

		if(!Folder::$deleteInDatabaseOnly)
			$this->fsFolder->delete();

		//Read only flag is set for addressbooks, tasklists etc. They share the same acl so deleting it would make addressbooks inaccessible.
		if(!$this->readonly){
			//normally this is done automatically. But we overide $this->joinAclfield to prevent acl management.
			$acl = \GO\Base\Model\Acl::model()->findByPk($this->{$this->aclField()});
			if($acl)
				$acl->delete();
		}

		$this->notifyUsers(
			array($this->id, $this->parent->id),
			FolderNotificationMessage::DELETE_FOLDER,
			$this->getPath()
		);
		return parent::afterDelete();
	}


	private $_folderCache=array();

	public function save($ignoreAcl = false) {
		if(!$this->isModified() && (empty($this->getCustomfieldsRecord()) || !$this->getCustomfieldsRecord()->isModified())) { // this will make it possible to set the "notify" in a folder see afterSubmit
			return true;
		}
		return parent::save($ignoreAcl);
	}
	
	public function clearFolderCache(){
		$this->_folderCache=array();
	}
	/**
	 * Find a folder by path relative to \GO::config()->file_storage_path
	 *
	 * @param String $relpath
	 * @param boolean $autoCreate True to auto create the folders. ACL's will be ignored.
	 * @return Folder
	 */
	public function findByPath($relpath, $autoCreate=false, $autoCreateAttributes=array(), $caseSensitive=true, $returnLastFound = false) {


		$oldIgnoreAcl = \GO::$ignoreAclPermissions;
		\GO::$ignoreAclPermissions=true;

		$folder=false;
		if (substr($relpath, -1) == '/') {
			$relpath = substr($relpath, 0, -1);
		}

		$parts = explode('/', $relpath);
		$parent_id = 0;

		foreach($parts as $index=>$folderName){
			$lastFolder = $folder;

			$cacheKey = $parent_id.'/'.$folderName;

			if(!isset($this->_folderCache[$cacheKey])){

				$col = $caseSensitive ? 'BINARY t.name' : 't.name';

				$findParams = \GO\Base\Db\FindParams::newInstance();
				$findParams->getCriteria()
								->addCondition('parent_id', $parent_id)
								->addBindParameter(':name', $folderName)
								->addRawCondition($col, ':name'); //use utf8_bin for case sensivitiy and special characters.

				$folder = $this->findSingle($findParams);
				if (!$folder) {
					if (!$autoCreate) {
						if($returnLastFound) {
							return $lastFolder;
						} else{
							return false;
						}
					}

					$folder = new Folder();
					$folder->setAttributes($autoCreateAttributes);
					$folder->name = $folderName;
					$folder->parent_id = $parent_id;
					if(!$folder->save()){
						throw new \Exception('Could not create folder: '.implode('<br>',$folder->getValidationErrors()));
					}
				}elseif(!empty($autoCreateAttributes))
				{
					//should not apply it to existing folders. this leads to unexpected results.
	//				$folder->setAttributes($autoCreateAttributes);
	//				$folder->save();
				}
				if(!GO::$disableModelCache){
					$this->_folderCache[$cacheKey]=$folder;
				}
			}else
			{
				$folder = $this->_folderCache[$cacheKey];
			}

			$parent_id = $folder->id;
		}

		\GO::$ignoreAclPermissions=$oldIgnoreAcl;

		return $folder;
	}
	/**
	 * Return the home folder of a user.
	 *
	 * @param \GO\Base\Model\User $user
	 */
	public function findHomeFolder($user){

		$folder = Folder::model()->findByPath('users/'.$user->username, true);

		if(empty($folder->acl_id)){
				$folder->setNewAcl($user->id);
		}

		$folder->user_id=$user->id;
		$folder->visible=1;
		$folder->readonly=1;
		//\GO::$ignoreAclPermissions=true;
		$folder->save();
		//\GO::$ignoreAclPermissions=false;

		return $folder;
	}

	/**
	 * Check if this folder is the home folder of a user.
	 *
	 * @return boolean
	 */
	public function isSomeonesHomeFolder(){
		if($this->isNew)
			return false;

		return $this->parent && $this->parent->name=='users' && $this->parent->parent_id==0;
	}


	/**
	 * Add a file to this folder. The file must already be present on the filesystem.
	 *
	 * @param String $name
	 * @return File
	 */
	public function addFile($name) {
		$file = new File();

		$file->folder_id = $this->id;
		$file->name = $name;


		if($file->save())
			return $file;
		else
			return false;
	}

	/**
	 * Add a filesystem file to this folder. The file will be moved to this folder
	 * and added to the database.
	 *
	 * @param \GO\Base\Fs\File $file
	 * @param boolean $appendNumberToNameIfExists Set if a number needs to be added to the name if the file already exists.
	 * @return File
	 */
	public function addFilesystemFile(\GO\Base\Fs\File $file, $appendNumberToNameIfExists=false){

		if(!File::checkQuota($file->size()))
			throw new \GO\Base\Exception\InsufficientDiskspace();

		$file->move($this->fsFolder, false, false, $appendNumberToNameIfExists);
		$file->setDefaultPermissions();
		return $this->addFile($file->name());
	}

	/**
	 * Add a filesystem file to this folder. The file will be moved to this folder
	 * and added to the database.
	 *
	 * @param \GO\Base\Fs\File $file
	 * @return File
	 */
	public function addFilesystemFolder(\GO\Base\Fs\Folder $folder){
		$folder->move($this->fsFolder);
		return $this->addFolder($folder->name(), true);
	}

	/**
	 * Add an uploaded file
	 *
	 * @param array $filesArrayItem Item from the $_FILES array
	 * @return boolean
	 */
	public function addUploadedFile($filesArrayItem){

		$fsFile = new \GO\Base\Fs\File($filesArrayItem['tmp_name']);
		$fsFile->move($this->fsFolder, $filesArrayItem['name'], true, true);

		return $this->addFile($fsFile->name());
	}

	/**
	 * Add a subfolder.
	 *
	 * @param String $name
	 * @return Folder
	 */
	public function addFolder($name, $syncFileSystem=false, $syncOnNextAccess=false){
		$folder = new Folder();
		$folder->parent_id = $this->id;
		$folder->name = $name;

		//file manager will compare database timestamp with filesystem when it's accessed.
		if($syncOnNextAccess)
			$folder->mtime=1;

		$folder->save();

		if($syncFileSystem)
			$folder->syncFilesystem();

		return $folder;
	}

	/**
	 * Adds missing files and folders from the filesystem to the database and
	 * removes files and folders from the database that are not on the filesystem.
	 *
	 * @param boolean $recurseAll
	 * @param boolean $recurseOneLevel
	 */
	public function syncFilesystem($recurseAll=false, $recurseOneLevel=true) {

		if(\GO::config()->debug)
			\GO::debug("syncFilesystem ".$this->path);
		
		if(\GO::environment()->isCli()){
			echo $this->path."\n";
		}

		$oldIgnoreAcl = \GO::setIgnoreAclPermissions(true);

		$oldCache = \GO::$disableModelCache;

		GO::$disableModelCache=$recurseAll;

//		if(class_exists("GO\Filesearch\FilesearchModule"))
//			\GO\Filesearch\FilesearchModule::$disableIndexing=true;

		if($this->fsFolder->exists()){
			$items = $this->fsFolder->ls();

			foreach ($items as $item) {
				try{
				//\GO::debug("FS SYNC: Adding fs ".$item->name()." to database");
					if ($item->isFile()) {
						$file = $this->hasFile($item->name());
						if (!$file){
							$this->addFile($item->name());
						}else
						{
							//this will update timestamp and size of file
							if($file->mtime != $file->fsFile->mtime()){
								$file->save();
							}
						}

					}else
					{

						$willSync = $recurseOneLevel || $recurseAll;

						$folder = $this->hasFolder($item->name());
						if(!$folder)
							$folder = $this->addFolder($item->name(), false, !$willSync);

						if($willSync)
							$folder->syncFilesystem($recurseAll, false);
					}
				}
				catch(\Exception $e){
					echo "<span style='color:red;'>".$e->getMessage()."</span>\n";
				}
			}
		}else
		{
			$this->fsFolder->create();
		}


		//make sure no filesystem items are deleted. Sometimes folders are stored as files somehow.
		$oldFileDeleteInDatabaseOnly = File::$deleteInDatabaseOnly;
		$oldFolderDeleteInDatabaseOnly = Folder::$deleteInDatabaseOnly;
		
		File::$deleteInDatabaseOnly=true;
		Folder::$deleteInDatabaseOnly=true;

		$stmt= $this->folders();
		while($folder = $stmt->fetch()){
			try{
				if(!$folder->fsFolder->exists() || $folder->fsFolder->isFile())
					$folder->delete(true);
			}catch(\Exception $e){
				echo "<span style='color:red;'>".$e->getMessage()."</span>\n";
			}
		}

		$stmt= $this->files();
		while($file = $stmt->fetch()){
			try{
				if(!$file->fsFile->exists() || $file->fsFile->isFolder())
					$file->delete(true);
			}catch(\Exception $e){
				echo "<span style='color:red;'>".$e->getMessage()."</span>\n";
			}
		}

		$this->mtime=$this->fsFolder->mtime();
		$this->save(true);

		\GO::$disableModelCache=$oldCache;

		\GO::setIgnoreAclPermissions($oldIgnoreAcl);
		
		File::$deleteInDatabaseOnly=$oldFileDeleteInDatabaseOnly;
		Folder::$deleteInDatabaseOnly=$oldFolderDeleteInDatabaseOnly;
	}

	/**
	 * Compares the database timestamp with the filesystem timestamp and syncs the
	 * folder if necessary.
	 */
	public function checkFsSync(){

		if(!$this->fsFolder->exists())
			throw new \Exception("Folder ".$this->path." doesn't exist on the filesystem! Please run a database check.");

		\GO::debug('checkFsSync '.$this->path.' : '.$this->mtime.' < '.$this->fsFolder->mtime());

		if($this->mtime < $this->fsFolder->mtime()){
			\GO::debug("Filesystem folder ".$this->path." is not in sync with database. Will sync now.");
			$this->syncFilesystem ();
			$this->mtime=$this->fsFolder->mtime();
			$this->save(true);
		}
	}

	/**
	 * Add a user that will be notified by e-mail when something changes in the
	 * folder.
	 *
	 * @param int $user_id
	 * @param boolean $recursively If true, apply this to all subfolders.
	 */
	public function addNotifyUser($user_id,$recursively=false){
		if(!$this->hasNotifyUser($user_id)){
			$m = new FolderNotification();
			$m->folder_id = $this->id;
			$m->user_id = $user_id;
			$m->save();
		}
		if ($recursively) {
			$childFolderStmt = Folder::model()->findByAttribute('parent_id',$this->id);
			while ($childFolder = $childFolderStmt->fetch())
				$childFolder->addNotifyUser($user_id,true);
		}
  }

	/**
	 * Remove a user that will be notified by e-mail when something changes in the
	 * folder.
	 *
	 * @param int $user_id
	 * @param boolean $recursively If true, apply this to all subfolders.
	 */
	public function removeNotifyUser($user_id, $recursively=false){
		$model = FolderNotification::model()->findByPk(array('user_id'=>$user_id, 'folder_id'=>$this->pk));
		if($model)
			$model->delete();

		if ($recursively) {
			$childFolderStmt = Folder::model()->findByAttribute('parent_id',$this->id);
			while ($childFolder = $childFolderStmt->fetch())
				$childFolder->removeNotifyUser($user_id,true);
		}
	}

    /**
    * Check if a user receives notifications about changes in the folder.
    *
    * @param type $user_id
    * @return FolderNotification or false
    */
    public function hasNotifyUser($user_id){
        return FolderNotification::model()->findByPk(
            array('user_id'=>$user_id, 'folder_id'=>$this->pk)
        ) !== false;
    }

    /**
    *
    * @param int|array $folder_id
    * @param type $type
    * @param type $arg1
    * @param type $arg2
    */
    public function notifyUsers($folder_id, $type, $arg1, $arg2 = '') {
        FolderNotification::model()->storeNotification($folder_id, $type, $arg1, $arg2);
    }


	/**
	 * Check if this folder has a file by filename and return the model.
	 *
	 * @param String $filename
	 * @return File
	 */
	public function hasFile($filename, $caseSensitive=true){
		$col = $caseSensitive ? 'BINARY t.name' : 't.name';
		$findParams = \GO\Base\Db\FindParams::newInstance()
						->single();
		$findParams->getCriteria()
							->addBindParameter(':name', $filename)
							->addRawCondition($col, ':name'); //use utf8_bin for case sensivitiy and special characters.

		return $this->files($findParams);
	}

	/**
	 * Check if this folder has a file by filename and return the model.
	 *
	 * @param String $filename
	 * @return Folder
	 */
	public function hasFolder($filename){

		$findParams = \GO\Base\Db\FindParams::newInstance()
						->single();
		$findParams->getCriteria()
							->addBindParameter(':name', $filename)
							->addRawCondition('BINARY t.name', ':name'); //use utf8_bin for case sensivitiy and special characters.

		return $this->folders($findParams);
	}


	/**
	 * Checks if a filename exists and renames it.
	 *
	 * @access public
	 * @return StringHelper  New filename
	 */
	public function appendNumberToNameIfExists()
	{
		$origName= $this->name;
		$x=1;
		while($this->parent->hasFolder($this->name))
		{
			$this->name=$origName.' ('.$x.')';
			$x++;
		}
		return $this->name;
	}
	
	/**
	 * When this folder is copied or moved to a different home folder, the quota
	 * of all the folder inside this folder and the subfolder need to be
	 * substracted from the old homedir user when moved and added to the new 
	 */
	private function _recalculateQuota() {
		
		$oldUser = $this->quotaUser;
		
		//set quota user
		$shared_folder = $this;
		while(!$shared_folder->isSomeonesHomeFolder() && $shared_folder->parent_id!=0) {
			$shared_folder = $shared_folder->parent;
		}
		$this->quota_user_id = $shared_folder->user_id;
		
		//check if changed
		if($this->quota_user_id == $this->getOldAttributeValue('quota_user_id'))
			return;
		
		$newUser = $this->quotaUser;
		
		$folderIds = array($this->id);
		$recur = function($folders, $quota_user_id) use (&$folderIds, &$recur) {
			while($folder = $folders->fetch()){
				$folder->quota_user_id = $quota_user_id;
				$folderIds[] = $folder->id;
				$folder->save();
				$recur($folder->folders(), $quota_user_id); // <- recursion implied
			}
		};
		$recur($this->folders(), $this->quota_user_id);
		
		//sum size of the files in this folder
		$criteria = \GO\Base\Db\FindCriteria::newInstance()->addInCondition('folder_id', $folderIds);
		$fp = \GO\Base\Db\FindParams::newInstance()->select('SUM(size) as total')->criteria($criteria);
		$totalQuotaMoved = File::model()->findSingle($fp)->total;
		
		// add and substract quota
		if($oldUser) {
			$oldUser->calculatedDiskUsage(0 - $totalQuotaMoved)->save(true);
		}

		$newUser->calculatedDiskUsage($totalQuotaMoved)->save(true);
	}

	/**
	 * Move a folder to another folder
	 *
	 * @param Folder $destinationFolder
	 * @return boolean
	 */
	public function move($destinationFolder){

		$this->parent_id=$destinationFolder->id;
		$this->_recalculateQuota();
		return $this->save();
	}
	
	protected function beforeDuplicate(&$duplicate) {
		
		if(!empty($duplicate->acl_id)) {
			$oldAcl = \GO\Base\Model\Acl::model()->findByPk($duplicate->acl_id);
			$duplicate->setNewAcl();
			$newAcl = \GO\Base\Model\Acl::model()->findByPk($duplicate->acl_id);
			$oldAcl->copyPermissions($newAcl);			
		}
		
		return parent::beforeDuplicate($duplicate);
	}

	/**
	 * Copy a folder to another folder
	 *
	 * @param Folder $destinationFolder
	 * @return boolean
	 */
	public function copy($destinationFolder, $newName=false){

		if(\GO::config()->debug)
			\GO::debug("Copy folder ".$this->path." to ".$destinationFolder->path);

		if(!$newName)
			$newName=$this->name;

		$existing = $destinationFolder->hasFolder($newName);
		if(!$existing){
			$copy = $this->duplicate(array("parent_id"=>$destinationFolder->id,'name'=>$newName, 'quota_user_id'=>null),true, true);
			//$copy->parent_id=$destinationFolder->id;
			if(!$copy)
				return false;

			$destinationFsFolder = $copy->fsFolder->parent();
//			$copy->fsFolder->delete();
			try{ // $copy directory already made but copy() might throw an exception when coping in the source folder
				if(!$this->fsFolder->copy($destinationFsFolder, $newName)) {
					return false;
				}
			} catch(\Exception $e) {
				$copy->delete();
				throw $e;
			}
		}else
		{
			$copy = $existing;
			//if folder exist then merge the folder.
		}

		$stmt = $this->folders();
		while($folder = $stmt->fetch()){
			if(!$folder->copy($copy))
				return false;
		}

		$stmt = $this->files();
		while($file = $stmt->fetch()){
			if(!$file->copy($copy))
				return false;
		}

		return true;
	}

	protected function getThumbURL() {

		$params = array(
				'src'=>$this->path,
				'foldericon'=> $this->acl_id ? 'folder_public.png' : 'folder.png',
				'lw'=>100,
				'ph'=>100,
				'zc'=>1,
				'filemtime'=>$this->mtime
				);

		return \GO::url('core/thumb', $params);
	}

	/**
	 * Get all the subfolders of this folder. This function checks permissions in a
	 * special way. When folder have acl_id=0 they inherit permissions of the parent folder.
	 *
	 * @return \GO\Base\Db\ActiveStatement
	 */
	public function getSubFolders($findParams=false, $noGrouping=false){
			if(!$findParams)
				$findParams=\GO\Base\Db\FindParams::newInstance();

			$findParams->ignoreAcl(); //We'll build a special acl check for folders that inherit permissions here.

			//$findParams->debugSql();

			$aclJoinCriteria = \GO\Base\Db\FindCriteria::newInstance()
							->addRawCondition('a.acl_id', 't.acl_id','=', false);

			$aclWhereCriteria = \GO\Base\Db\FindCriteria::newInstance()
							//->addRawCondition('a.acl_id', 'NULL','IS', false)
							->addCondition('acl_id', 0,'=','t',false)
							->addCondition('user_id', \GO::user()->id,'=','a', false)
							->addInCondition("group_id", \GO\Base\Model\User::getGroupIds(\GO::user()->id),"a", false);

			$findParams->join(\GO\Base\Model\AclUsersGroups::model()->tableName(), $aclJoinCriteria, 'a', 'LEFT');

			$findParams->criteria(\GO\Base\Db\FindCriteria::newInstance()
									->addModel(Folder::model())
									->addCondition('parent_id', $this->id)
									->mergeWith($aclWhereCriteria));

			if(!$noGrouping)
				$findParams->group(array('t.id'));

			return Folder::model()->find($findParams);
	}
	
	/**
	 * Get an array of ids of all the folder inside this one
	 * @return array
	 */
	public function getSubFolderIds() {
		$ids = array();
		$ids[] = $this->id;
		$walkSubfolders = function($folder) use(&$ids, &$walkSubfolders) {
			foreach($folder->folders() as $sfolder) {

				if(empty($folder->acl_id) || $folder->getPermissionLevel()) {
					$walkSubfolders($sfolder);
					$ids[] = $sfolder->id;
				}
			}
			
		};
		$walkSubfolders($this);
		return $ids;
	}

	/**
	 * Checks if this folder has child folders and checks permissions too.
	 * @return boolean
	 */
	public function hasChildren(){
		return $this->getSubFolders(\GO\Base\Db\FindParams::newInstance()->single(), true);
	}

	/**
	 * Check if this folder has subfolders without checking permissions.
	 *
	 * @return boolean
	 */
	public function hasFolderChildren(){
		$folder = Folder::model()->findSingleByAttribute('parent_id', $this->id);

		return $folder!=false;
	}

	/**
	 * Check if this folder has files.
	 *
	 * @return boolean
	 */
	public function hasFileChildren(){
		$file = File::model()->findSingleByAttribute('folder_id', $this->id);

		return $file!=false;
	}

	/**
	 * Move all the files and folders from a given source folder into this folder.
	 *
	 * @param Folder $sourceFolder
	 */
	public function moveContentsFrom(Folder $sourceFolder, $mergeFolders=false){

		//make sure database is in sync with filesystem.
		$sourceFolder->syncFilesystem(true);

		$stmt = $sourceFolder->folders();
		while($subfolder = $stmt->fetch()){
			\GO::debug("MOVE ".$subfolder->name);
			$subfolder->systemSave=true;
			if(!$mergeFolders){
				$subfolder->parent_id=$this->id;
				$subfolder->appendNumberToNameIfExists();
				if(!$subfolder->save(true)){
					throw new \Exception("Could not save folder ".$subfolder->name." ".implode("\n", $subfolder->getValidationErrors()));
				}
			}else
			{
				if(($existingFolder = $this->hasFolder($subfolder->name))){
					$existingFolder->moveContentsFrom($subfolder, true);
					if(!$subfolder->delete(true)){
						throw new \Exception("Could not delete folder ".$subfolder->name);
					}
				}else
				{
					$subfolder->parent_id=$this->id;
					if(!$subfolder->save(true)){
						throw new \Exception("Could not save folder ".$subfolder->name." ".implode("\n", $subfolder->getValidationErrors()));
					}
				}
			}
		}

		$stmt = $sourceFolder->files();
		while($file = $stmt->fetch()){
			\GO::debug("MOVE ".$file->name);
			$file->folder_id=$this->id;
			$file->appendNumberToNameIfExists();
			if(!$file->save()){
				throw new \Exception("Could not save file ".$file->name." ".implode("\n", $file->getValidationErrors()));
			}
		}
	}

	public function copyContentsFrom(Folder $sourceFolder, $mergeFolders=false){
		//make sure database is in sync with filesystem.
		$sourceFolder->syncFilesystem(true);


		$stmt = $sourceFolder->folders();
		while($subfolder = $stmt->fetch()){

			$subfolder->systemSave=true;
			if(!$mergeFolders){
				$subfolder->copy($this);
			}else
			{
				if(($existingFolder = $this->hasFolder($subfolder->name))){
					$existingFolder->copyContentsFrom($subfolder, true);
				}else
				{
					$subfolder->copy($this);
				}
			}
		}

		$stmt = $sourceFolder->files();
		while($file = $stmt->fetch()){
			$file->copy($this, false, true);
		}
	}

	/**
	 *
	 * @param StringHelper $name
	 * @return Folder
	 */
	public function getTopLevelShare($folderName){

		\GO::debug("getTopLevelShare($folderName)");

		if(!isset($this->_folderCache['Shared/'.$folderName])){
			$findParams = \GO\Base\Db\FindParams::newInstance();

			$findParams->joinRelation('sharedRootFolders')							
				->order('name','ASC')
				->single();

			$findParams->getCriteria()
						->addCondition('user_id', \GO::user()->id,'=','sharedRootFolders')
						->addBindParameter(':name', $folderName)
						->addRawCondition('BINARY t.name', ':name'); //use utf8_bin for case sensivitiy and special characters.

			$folder=$this->find($findParams);
			
			if($folder && !$folder->checkPermissionLevel(\GO\Base\Model\Acl::READ_PERMISSION)) {
				$folder = false;
			}

			$this->_folderCache['Shared/'.$folderName]=$folder;

			//for findByPath
			if($folder){
				if(!GO::$disableModelCache){
					$this->_folderCache[$folder->parent_id.'/'.$folderName]=$folder;
				}
			}

		}

		return $folder;
	}

	/**
	 *
	 * @param \GO\Base\Db\FindParams $findParams
	 * @return \GO\Base\Db\ActiveStatement
	 */
	public function getTopLevelShares($findParams=false){
		if(!$findParams)
			$findParams = new \GO\Base\Db\FindParams();

		$findParams->joinRelation('sharedRootFolders')
			//->ignoreAcl()
			->order('name','ASC')
			->limit(500);

		$findParams->getCriteria()
					->addCondition('user_id', \GO::user()->id,'=','sharedRootFolders');

		return $this->find($findParams);
	}

	/**
	 * Empty the folder
	 */
	public function removeChildren(){
		$stmt = $this->folders();
		while($subfolder = $stmt->fetch()){
			$subfolder->delete();
		}

		$stmt = $this->files();
		while($file = $stmt->fetch()){
			$file->delete();
		}
	}
}
