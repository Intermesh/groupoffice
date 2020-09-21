<?php


namespace GO\Files\Controller;

use Exception;
use GO;
use GO\Base\Exception\AccessDenied;
use go\core\fs\Blob;
use GO\Files\Model\Folder;

class FolderController extends \GO\Base\Controller\AbstractModelController {

	protected $model = 'GO\Files\Model\Folder';
	
	

	protected function allowGuests() {
		if($this->isCli())
			return array('syncfilesystem');
		else
			return parent::allowGuests();
	}
  
  protected function allowWithoutModuleAccess() {
    return ['images'];
  }
	
	protected function actionGetURL($path){
		
		if (substr($path,0,1)=='/')
			$path = substr($path,1);
		if (substr($path,-1,1)=='/')
			$path = substr($path,0,-1);
		
		$folderModel = Folder::model()->findByPath($path,true);
		
		return array('success'=>true,'url'=>  \GO\Base\Util\Http::addParamsToUrl($folderModel->getExternalURL(),array('GOSID'=>session_id(), 'security_token'=>\GO::session()->values['security_token'])));
	}
	
	protected function actionCache($params){
		\GO\Files\Model\SharedRootFolder::model()->rebuildCache(\GO::user()->id);
	}

	protected function actionSyncFilesystem($params){	
		
		if(!$this->isCli() && !\GO::user()->isAdmin())
			throw new \GO\Base\Exception\AccessDenied();
		
		$oldAllowDeletes = \GO\Base\Fs\File::setAllowDeletes(false);

		\GO::$disableModelCache=true; //for less memory usage
		ini_set('max_execution_time', '0');

		\GO::session()->runAsRoot();

		if(isset($params['path'])){
			$folders = array($params['path']);
		}else
		{
			$folders = array('users','projects2','addressbook','notes','tickets');


			$billingFolder = new \GO\Base\Fs\Folder(\GO::config()->file_storage_path.'billing');
			if($billingFolder->exists()){
				$bFolders = $billingFolder->ls();

				foreach($bFolders as $folder){		
						if($folder->isFolder() && $folder->name()!='notifications'){
							$folders[]='billing/'.$folder->name();
						}
				}		
			}
		}

		echo "<pre>";
		foreach($folders as $name){
			echo "Syncing ".$name."\n";
			try{
				$folder = Folder::model()->findByPath($name, true);
				
				if(!$folder)
					throw new \Exception("Could not find or create folder");
				
				$folder->syncFilesystem(true);
				
				
			}
			catch(\Exception $e){
				if (PHP_SAPI != 'cli')
					echo "<span style='color:red;'>".$e->getMessage()."</span>\n";
				else
					echo $e->getMessage()."\n";
			}
		}

		echo "Done\n";


		if(!isset($params['path'])){
			\GO\Base\Fs\File::setAllowDeletes($oldAllowDeletes);
			$folders = array('email', 'billing/notifications');

			foreach($folders as $name){

				echo "Deleting ".$name."\n";
				Folder::$deleteInDatabaseOnly=true;
				\GO\Files\Model\File::$deleteInDatabaseOnly=true;
				try{
					$folder = Folder::model()->findByPath($name);
					if($folder)
							$folder->delete();
				}
				catch(\Exception $e){
					if (PHP_SAPI != 'cli')
						echo "<span style='color:red;'>".$e->getMessage()."</span>\n";
					else
						echo $e->getMessage()."\n";
				}
			}
		}	
			
		

	
		
		
	}
	
	public function actionDeleteInvalid(){
		$folders = array('email', 'billing/notifications');

		foreach($folders as $name){

			echo "Deleting ".$name."\n";
			Folder::$deleteInDatabaseOnly=true;
			\GO\Files\Model\File::$deleteInDatabaseOnly=true;
			try{
				$folder = Folder::model()->findByPath($name);
				if($folder)
						$folder->delete();
			}
			catch(\Exception $e){
				if (PHP_SAPI != 'cli')
					echo "<span style='color:red;'>".$e->getMessage()."</span>\n";
				else
					echo $e->getMessage()."\n";
			}
		}
			
			
		$findParams = \GO\Base\Db\FindParams::newInstance();

		$findParams->getCriteria()->addCondition('parent_id', null,'IS');

		$stmt = Folder::model()->find($findParams);

		foreach($stmt as $folder){

			if(!$folder->fsFolder->exists()){

				echo "Deleting ".$folder->path."\n";
				$folder->delete();
			}

		}	
	}

	private function _getExpandFolderIds($params){
		$expandFolderIds=array();
		if(!empty($params['expand_folder_id']) && $params['expand_folder_id']!='shared') {
			$expandFolderIds=  Folder::model()->getFolderIdsInPath($params['expand_folder_id']);
		}
		return $expandFolderIds;
	}

	private function _buildSharedTree($expandFolderIds){
		
		
		\GO\Files\Model\SharedRootFolder::model()->rebuildCache(\GO::user()->id);
		
		$response=array();
		
		
		$findParams = \GO\Base\Db\FindParams::newInstance()
						->joinRelation('sharedRootFolders')
						->ignoreAcl()
						->order('name','ASC')
						->limit(500);
		
		$findParams->getCriteria()
					->addCondition('user_id', \GO::user()->id,'=','sharedRootFolders');
		
		
		
		$shares = Folder::model()->find($findParams);
		foreach($shares as $folder){
			$node = $this->_folderToNode($folder, $expandFolderIds, false);
			$node['path'] = $folder->path;
			$response[]=$node;
		}

		return $response;

	}


	protected function actionTree($params) {

		//refresh forces sync with db
		if(!empty($params['sync_folder_id'])){
			if($params['sync_folder_id']=="shared"){
				\GO\Files\Model\SharedRootFolder::model()->rebuildCache(\GO::user()->id, true);
			}else
			{
				if(empty(GO::config()->files_disable_filesystem_sync)){
					$syncFolder = Folder::model()->findByPk($params['sync_folder_id']);
					if($syncFolder)
						$syncFolder->syncFilesystem();
				}
			}
		}

		$response = array();

		$expandFolderIds = $this->_getExpandFolderIds($params);

		$showFiles = isset($params['showFiles']);

		switch ($params['node']) {
			case 'shared':
				$response=$this->_buildSharedTree($expandFolderIds);
				break;
			case 'root':
				if (!empty($params['root_folder_id'])) {
					$folder = Folder::model()->findByPk($params['root_folder_id']);
//					$folder->checkFsSync();
					$node = $this->_folderToNode($folder, $expandFolderIds, true, $showFiles);
					$response[] = $node;
				} else {
					$folder = Folder::model()->findHomeFolder(\GO::user());

//					$folder->checkFsSync();

					$node = $this->_folderToNode($folder, $expandFolderIds, true, $showFiles);
					$node['text'] = \GO::t("Personal", "files");
					$node['iconCls'] = 'ic-home';
					$node['path'] = $folder->path;
					$response[] = $node;


					$node = array(
							'text' => \GO::t("Shared", "files"),
							'id' => 'shared',
							'readonly' => true,
							'draggable' => false,
							'allowDrop' => false,
							'parent_id'=>0,
							'iconCls' => 'ic-folder-shared',
							'path'=>"shared"							
					);
					
					//expand shares for non admins only. Admin may see too many folders.
					if(!\GO::user()->isAdmin()){
						$node['expanded']=true;
						$node['children']=$this->_buildSharedTree($expandFolderIds);
					}
					
					$response[] = $node;

					if (GO::config()->files_show_addressbooks && GO::modules()->addressbook) {
						$contactsFolder = Folder::model()->findByPath('addressbook');

						if ($contactsFolder) {
							$node = $this->_folderToNode($contactsFolder, $expandFolderIds, false, $showFiles);
							$node['path'] = $contactsFolder->path;
							$node['text'] = \GO::t("Address book", "addressbook");
							$response[] = $node;
						}
					}


					if (GO::config()->files_show_projects && GO::modules()->projects) {
						$projectsFolder =  Folder::model()->findByPath('projects');

						if ($projectsFolder) {
							$node = $this->_folderToNode($projectsFolder, $expandFolderIds, false, $showFiles);
							$node['path'] = $projectsFolder->path;
							$node['text'] = \GO::t("projects", "projects");
							$response[] = $node;
						}
					}
					

					if (GO::config()->files_show_projects && GO::modules()->projects2) {
						$projectsFolder = Folder::model()->findByPath('projects2');

						if ($projectsFolder) {
							$node = $this->_folderToNode($projectsFolder, $expandFolderIds, false, $showFiles);
								$node['path'] = $projectsFolder->path;
							$node['text'] = \GO::t("Projects", "projects2");
							$response[] = $node;
						}
					}
					
					if(\GO::user()->isAdmin()){
						$logFolder = Folder::model()->findByPath('log', true);
//						$logFolder->syncFilesystem();
						
						$node = $this->_folderToNode($logFolder, $expandFolderIds, false, $showFiles);
						$node['path'] = $logFolder->path;
						$node['text']=\GO::t("Log files");
						
						$response[]=$node;
					}
				}



				break;

			default:
				$folder = Folder::model()->findByPk($params['node']);
				if(!$folder)
					return false;
				
//				$folder->checkFsSync();

				$stmt = $folder->getSubFolders(\GO\Base\Db\FindParams::newInstance()
							->order(new \go\core\db\Expression('name COLLATE utf8mb4_unicode_ci ASC')));

				while ($subfolder = $stmt->fetch()) {
					$response[] = $this->_folderToNode($subfolder, $expandFolderIds, false, $showFiles);
					
				}

				if ($showFiles) {
						$response = array_merge($response, $this->_addFileNodes($folder));
					}

				break;
		}

		return $response;
	}

	private function _folderToNode($folder, $expandFolderIds=array(), $withChildren=true, $withFiles = false) {
		$expanded = $withChildren || in_array($folder->id, $expandFolderIds);
		$node = array(
				'text' => $folder->name,
				'id' => $folder->id,
				'draggable' => false,
				'iconCls' => !$folder->acl_id || $folder->readonly ? 'ic-folder' : 'ic-folder-shared',
				'expanded' => $expanded,
				'parent_id'=>$folder->parent_id,
				'path'=>$folder->path
		);

		if ($expanded) {
			$node['children'] = array();
			$stmt = $folder->getSubFolders(\GO\Base\Db\FindParams::newInstance()
							->limit(300)//not so nice hardcoded limit
							->order(new \go\core\db\Expression('name COLLATE utf8mb4_unicode_ci ASC')));
			while ($subfolder = $stmt->fetch()) {
				$node['children'][] = $this->_folderToNode($subfolder, $expandFolderIds, false, $withFiles);
			}

			if ($withFiles) {
				$node['children'] = array_merge($node['children'], $this->_addFileNodes($folder));
			}
		} else {
			if (!$folder->hasChildren()) {
				//it doesn't habe any subfolders so instruct the client about this
				//so it can present the node as a leaf.
				$node['children'] = array();
				$node['expanded'] = true;

				if ($withFiles) {
					$node['children'] = array_merge($node['children'], $this->_addFileNodes($folder));
				}
			}
		}

		return $node;
	}

	private function _addFileNodes($folder) {
		$stmt = $folder->files();

		$files = array();
		while($file = $stmt->fetch()) {
			$fileNode = array(
				'text' => $file->name,
				'name' => $file->name,
				'id' => $file->id,
				'size' => $file->size,
				'extension' => $file->extension,
				'draggable' => false,
				'leaf' => true,
				'path'=> $folder->path . '/' . $file->name,
				'iconCls' => 'filetype-' . strtolower($file->extension),
				'checked' => false
			);

			$files[] = $fileNode;
			\GO::debug($file);
		}
		return $files;
	}

	protected function beforeSubmit(&$response, &$model, &$params) {

		if(isset($params['share']) && !$model->readonly && !$model->isSomeonesHomeFolder() && $model->checkPermissionLevel(\GO\Base\Model\Acl::MANAGE_PERMISSION)){
			if ($params['share']==1 && $model->acl_id == 0) {
				$model->visible = 1;
				if(GO::modules()->isInstalled('hidesharedprojectfs')) {
					$parentId = ($model->getIsNew()) ? $params['parent_id'] : $model->parent_id;
					$parent = Folder::model()->findByPk($parentId);
					if(!empty($parent)) { 
						while($parent = $parent->parent) {
							if($parent->parent_id == 0 && in_array($parent->name, array('projects2', 'addressbook'))) {
								$model->visible = 0;
							}
						}
					}
				}
				
//				$acl = new \GO\Base\Model\Acl();
//				$acl->description = $model->tableName() . '.' . $model->aclField();
//				$acl->user_id = \GO::user() ? \GO::user()->id : 1;
//				$acl->save();
				$shared_folder = $model;
				while(!$shared_folder->isSomeonesHomeFolder() && $shared_folder->parent_id!=0) {
					$shared_folder = $shared_folder->parent;
				}
				$acl = $model->setNewAcl($shared_folder->user_id);
				$userGroup = \GO\Base\Model\Group::model()->findSingleByAttribute('isUserGroupFor', \GO::user()->id);
				if($userGroup) {
					$acl->addGroup($userGroup->id, \GO\Base\Model\Acl::MANAGE_PERMISSION);						
				}
				$acl->save(); // again
				
				//for enabling the acl permissions panel
				$response['acl_id']=$model->acl_id;
			}

			if ($params['share']==0 && $model->acl_id > 0) {
				$model->acl->delete();
				$model->acl_id = $response['acl_id'] = 0;
			}
		}

		if(!empty($params['name']) && \GO::config()->convert_utf8_filenames_to_ascii)
			$params['name']=\GO\Base\Util\StringHelper::utf8ToASCII ($params['name']);

		return parent::beforeSubmit($response, $model, $params);
	}

	protected function afterSubmit(&$response, &$model, &$params, $modifiedAttributes) {

		//output the new path of the file if we changed the name.
		if (isset($modifiedAttributes['name']))
			$response['new_path'] = $model->path;

		$notifyRecursive = !empty($params['notifyRecursive']) && $params['notifyRecursive']=='true' ? true : false;

		if(isset($params['notify'])){
			if ($params['notify']==1)
				$model->addNotifyUser(\GO::user()->id,$notifyRecursive);

			if ($params['notify']==0)
				$model->removeNotifyUser(\GO::user()->id,$notifyRecursive);
		}

		parent::afterSubmit($response, $model, $params, $modifiedAttributes);
	}

	protected function afterLoad(&$response, &$model, &$params) {

		$response['data']['path'] = $model->path;
		$response['data']['notify'] = $model->hasNotifyUser(\GO::user()->id);
		$response['data']['is_someones_home_dir'] = $model->isSomeonesHomeFolder();
		$response['data']['username'] = !empty($model->user) ? $model->user->name : '';
		$response['data']['musername'] = !empty($model->mUser) ? $model->mUser->name : '';
		
		$response['data']['url']=$model->externalUrl;

		return parent::afterLoad($response, $model, $params);
	}

	protected function afterDisplay(&$response, &$model, &$params) {
		$response['data']['path'] = $model->path;
		$response['data']['type'] = \GO::t("Folder", "files");
		$response['data']['notify'] = $model->hasNotifyUser(\GO::user()->id);
		$response['data']['url']=$model->externalUrl;

		return parent::afterDisplay($response, $model, $params);
	}

	protected function actionPaste($params) {

		$response['success'] = true;

		if (!isset($params['overwrite']))
			$params['overwrite'] = 'ask'; //can be ask, yes, no


		if (isset($params['ids']) && $params['overwrite'] == 'ask')
			\GO::session()->values['files']['pasteIds'] = $this->_splitFolderAndFileIds(json_decode($params['ids'], true));

		$destinationFolder = Folder::model()->findByPk($params['destination_folder_id']);

		if (!$destinationFolder->checkPermissionLevel(\GO\Base\Model\Acl::WRITE_PERMISSION))
			throw new \GO\Base\Exception\AccessDenied();

		while ($file_id = array_shift(\GO::session()->values['files']['pasteIds']['files'])) {
			$file = \GO\Files\Model\File::model()->findByPk($file_id);

			$newFileName=$file->name;

			$existingFile = $destinationFolder->hasFile($file->name);

			//if it's a copy-paste in the same folder then append a number.
			if($existingFile && $existingFile->id==$file->id){
				if($params['paste_mode'] == 'cut')
					continue;
				else
				{
					$fsFile = $existingFile->fsFile;
					$fsFile->appendNumberToNameIfExists();
					$newFileName = $fsFile->name();
					$existingFile=false;
				}
			}

			if ($existingFile) {
				switch ($params['overwrite']) {
					case 'ask':
						array_unshift(\GO::session()->values['files']['pasteIds']['files'], $file_id);
						$response['fileExists'] = $file->name;
						return $response;
						break;

					case 'yestoall':
					case 'yes':
						$existingFile->delete();

						if ($params['overwrite'] == 'yes')
							$params['overwrite'] = 'ask';
						break;

					case 'notoall':
					case 'no':
						if ($params['overwrite'] == 'no')
							$params['overwrite'] = 'ask';
						continue 2;
				}
			}

			if ($params['paste_mode'] == 'cut') {
				if (!$file->move($destinationFolder))
					throw new \Exception("Could not move " . $file->name);
			}else {
				if (!$file->copy($destinationFolder,$newFileName))
					throw new \Exception("Could not copy " . $file->name);
			}
		}

		while ($folder_id = array_shift(\GO::session()->values['files']['pasteIds']['folders'])) {
			$folder = Folder::model()->findByPk($folder_id);
			
			if($params['paste_mode']=='copy' && $folder->parent_id==$destinationFolder->id){
				//pasting in the same folder. Append (1).
				$fsFolder = $folder->fsFolder;
				$fsFolder->appendNumberToNameIfExists();
				$folderName=$fsFolder->name();				
			}  else {
				$folderName = $folder->name;
			}

			$existingFolder = $destinationFolder->hasFolder($folderName);
			if ($existingFolder) {
				switch ($params['overwrite']) {
					case 'ask':
						array_unshift(\GO::session()->values['files']['pasteIds']['folders'], $folder_id);
						$response['fileExists'] = $folderName;
						return $response;
						break;

					case 'yestoall':
					case 'yes':
						//$existingFolder->delete();

						if ($params['overwrite'] == 'yes')
							$params['overwrite'] = 'ask';
						break;

					case 'notoall':
					case 'no':
						if ($params['overwrite'] == 'no')
							$params['overwrite'] = 'ask';

						continue 2;
				}
			}

			if ($params['paste_mode'] == 'cut') {
				if (!$folder->move($destinationFolder))
					throw new \Exception("Could not move " . $folder->name);
			}else {
				if (!$folder->copy($destinationFolder, $folderName))
					throw new \Exception("Could not copy " . $folder->name);
			}
		}

		return $response;
	}

	private function _splitFolderAndFileIds($ids) {
		$fileIds = array();
		$folderIds = array();


		foreach ($ids as $typeId) {
			if (substr($typeId, 0, 1) == 'd') {
				$folderIds[] = substr($typeId, 2);
			} else {
				$fileIds[] = substr($typeId, 2);
			}
		}

		return array('files' => $fileIds, 'folders' => $folderIds);
	}

	private function _listShares($params) {

		//$store = \GO\Base\Data\Store::newInstance(\GO\Files\Model\Folder::model());
//
//      //set sort aliases
//      $store->getColumnModel()->formatColumn('type', '$model->type',array(),'name');
//      $store->getColumnModel()->formatColumn('size', '"-"',array(),'name');
//
//      $store->getColumnModel()->setFormatRecordFunction(array($this, 'formatListRecord'));
//      $findParams = $store->getDefaultParams($params);
//      $stmt = \GO\Files\Model\Folder::model()->findShares($findParams);
//      $store->setStatement($stmt);
//
//      $response = $store->getData();
		
		
//		$fp = \GO\Base\Db\FindParams::newInstance()->limit(100);
		
		//$fp = \GO\Base\Db\FindParams::newInstance()->calcFoundRows();
		
		$cm = new \GO\Base\Data\ColumnModel('GO\Files\Model\Folder');
		$cm->setFormatRecordFunction(array($this, 'formatListRecord'));
		
		
		$findParams = \GO\Base\Db\FindParams::newInstance()
						->joinRelation('sharedRootFolders')
						->ignoreAcl()
						->order(new \go\core\db\Expression('name COLLATE utf8mb4_unicode_ci ASC'));
		
		$findParams->getCriteria()
					->addCondition('user_id', \GO::user()->id,'=','sharedRootFolders');
		
		
		$store = new \GO\Base\Data\DbStore('GO\Files\Model\Folder',$cm, $params, $findParams);
		$response = $store->getData();
		$response['permission_level']=\GO\Base\Model\Acl::READ_PERMISSION;
//		$response['results']=array();
//		$shares =\GO\Files\Model\Folder::model()->getTopLevelShares($fp);
//		foreach($shares as $folder){
//			$record=$folder->getAttributes("html");
//			$record = $this->formatListRecord($record, $folder, false);
//			$response['results'][]=$record;
//		}
//		$response['total']=$shares->foundRows;
		return $response;
	}

	private $_listFolderPermissionLevel;

	protected function actionList($params) {

		if (!empty($params['query']))
				return $this->_searchFiles($params);
            
		
		
		//get the folder that contains the files and folders to list.
		//This will check permissions too.
		if(empty($params['folder_id'])) {
			$folder = Folder::model()->findHomeFolder (GO::user());
		}else
		{			
			if ($params['folder_id'] == 'shared') {
				return $this->_listShares($params);
			}
			$folder = Folder::model()->findByPk($params['folder_id']);
		}
		
		if(!$folder)
			throw new \Exception('No Folder found with id '.$params['folder_id']);
	
		
		
		// if it is the users folder tha get the shared folders
		if($folder->name == 'users' && $folder->parent_id == 0) {
			return $this->_listShares($params);
		}
		
		
		$user = $folder->quotaUser;
		$this->_listFolderPermissionLevel=$folder->permissionLevel;

		$response['permission_level']=$folder->permissionLevel;//$folder->readonly ? \GO\Base\Model\Acl::READ_PERMISSION : $folder->permissionLevel;

		if(empty($params['skip_fs_sync']) && empty(GO::config()->files_disable_filesystem_sync))
			$folder->checkFsSync();

		//useful information for the view.
		$response['path'] = $folder->path;

		//Show this page in thumbnails or list
		$folderPreference = \GO\Files\Model\FolderPreference::model()->findByPk(array('user_id'=>\GO::user()->id,'folder_id'=>$folder->id));
		if($folderPreference)
			$response['thumbs']=$folderPreference->thumbs;
		else
			$response['thumbs']=0;

		$response['parent_id'] = $folder->parent_id;

		//locked state
		$response['lock_state']=!empty($folder->apply_state);
		$response['cm_state']=isset($folder->cm_state) && !empty($folder->apply_state) ? $folder->cm_state : "";
		$response['may_apply_state']=\GO\Base\Model\Acl::hasPermission($folder->getPermissionLevel(), \GO\Base\Model\Acl::MANAGE_PERMISSION);

//      if($response["lock_state"]){
//          $state = json_decode($response["cm_state"]);
//
//          if(isset($state->sort)){
//              $params['sort']=$state->sort->field;
//              $params['dir']=$state->sort->direction;
//          }
//      }

		
		

		$store = \GO\Base\Data\Store::newInstance(Folder::model());

		//set sort aliases
		$store->getColumnModel()->formatColumn('type', '',array(),'name');
		$store->getColumnModel()->formatColumn('size', '"-"',array(),'name');
		$store->getColumnModel()->formatColumn('locked_user_id', '"0"');


		//handle delete request for both files and folder
		$this->_processDeletes($params, $store);
		
		$store->getColumnModel()->setFormatRecordFunction(array($this, 'formatListRecord'));

		$findParams = $store->getDefaultParams($params);
		
		//sorting on custom fields doesn't work for folders
		//TODO
		if(!isset($params['sort']) || substr($params['sort'],0,13)=='customFields.' || $params['sort'] == 'name') {
			$findParams->order(new \go\core\db\Expression('name COLLATE utf8mb4_unicode_ci ' . (!isset($params['dir']) || $params['dir'] == 'ASC' ? 'ASC' : 'DESC')));
		}

		
		$findParamsArray = $findParams->getParams();
		if(!isset($findParamsArray['start']))
			$findParamsArray['start']=0;

		if(!isset($findParamsArray['limit']))
			$findParamsArray['limit']=0;

		//$stmt = $folder->folders($findParams);

		$stmt = $folder->getSubFolders($findParams);

		$store->setStatement($stmt);

		$response = array_merge($response, $store->getData());

		//add files to the listing if it fits
		$folderPages = floor($stmt->foundRows / $findParamsArray['limit']);
		$foldersOnLastPage = $stmt->foundRows - ($folderPages * $findParamsArray['limit']);

		//$isOnLastPageofFolders = $stmt->foundRows < ($findParams['limit'] + $findParams['start']);

		if (count($response['results'])) {
			$fileStart = $findParamsArray['start'] - $folderPages * $findParamsArray['limit'];
			$fileLimit = $findParamsArray['limit'] - $foldersOnLastPage;
		} else {
			$fileStart = $findParamsArray['start'] - $stmt->foundRows;
			$fileLimit = $findParamsArray['limit'];
		}

		if ($fileStart >= 0) {

			$store->resetResults();

			$store->getColumnModel()->formatColumn('size', '"-"',array(),'size');
			$store->getColumnModel()->formatColumn('type', '',array(),'extension');
			$store->getColumnModel()->formatColumn('locked', '$model->isLocked()');
			$store->getColumnModel()->formatColumn('locked_user_id', '$model->locked_user_id');
			$store->getColumnModel()->formatColumn('folder_id', '$model->folder_id');

			$findParams = $store->getDefaultParams($params)
							->limit($fileLimit)
							->start($fileStart);
			
			// Handle the files filter
			if(!empty($params['files_filter'])){
				$extensions= explode(',',$params['files_filter']);
				$findParams->getCriteria()->addInCondition('extension', $extensions);
			}
			
			if(!isset($params['sort']) || $params['sort'] == 'name') {
				$findParams->order(new \go\core\db\Expression('name COLLATE utf8mb4_unicode_ci ' . (!isset($params['dir']) || $params['dir'] == 'ASC' ? 'ASC' : 'DESC')));
			}

			$stmt = $folder->files($findParams);
			$store->setStatement($stmt);

			$filesResponse = $store->getData();

			$response['total']+=$filesResponse['total'];
			$response['results'] = array_merge($response['results'], $filesResponse['results']);
		} else {
			$record = $folder->files(\GO\Base\Db\FindParams::newInstance()->single()->select('count(*) as total'));
			$response['total']+=$record->total;
		}
		if(empty($user)) {
			$user = \GO::user();
		}
		
		$response['owner_id'] = $user->id;
		$response['disk_usage']=round($user->disk_usage/1024/1024,2);
		$response['disk_quota']=$user->disk_quota;

		return $response;
	}
	
	/**
	 * Process deletes, separate function because it needs to be called from different places.
	 * 
	 * @param array $params
	 * @param type $store
	 */
	private function _processDeletes($params, $store=false){
		if(!$store){
			$store = \GO\Base\Data\Store::newInstance(Folder::model());
		}
		
		//handle delete request for both files and folder
		if (isset($params['delete_keys'])) {

			$ids = $this->_splitFolderAndFileIds(json_decode($params['delete_keys'], true));

			$params['delete_keys'] = json_encode($ids['folders']);
			$store->processDeleteActions($params, "GO\Files\Model\Folder");

			$params['delete_keys'] = json_encode($ids['files']);
			$store->processDeleteActions($params, "GO\Files\Model\File");
		}
	}

	private function _searchFiles($params) {
		
			//handle delete request for both files and folder
			$this->_processDeletes($params);
		
			$response['success'] = true;

			$queryStr = !empty($params['query']) ? '%'.$params['query'].'%' : '%';
			$limit = !empty($params['limit']) ? $params['limit'] : 30;
			$start = !empty($params['start']) ? $params['start'] : 0;

			$aclJoinCriteria = \GO\Base\Db\FindCriteria::newInstance()->addRawCondition('a.aclId', 'sc.aclId', '=', false);

			$aclWhereCriteria = \GO\Base\Db\FindCriteria::newInstance()
					->addInCondition("groupId", \GO\Base\Model\User::getGroupIds(\GO::user()->id), "a", false);

			$findParams = \GO\Base\Db\FindParams::newInstance()
					->select('t.*')
					->ignoreAcl()
					->joinCustomFields()
					->joinModel(array(
						'model'=>'GO\Base\Model\SearchCacheRecord',
						'localTableAlias'=>'t',
						'localField'=>'id',
						'foreignField'=>'entityId',
						'tableAlias'=>'sc'
					))
					->join(\GO\Base\Model\AclUsersGroups::model()->tableName(), $aclJoinCriteria, 'a', 'INNER')->debugSql()
					->criteria(
						\GO\Base\Db\FindCriteria::newInstance()
							->addCondition('entityTypeId', \GO::getModel('GO\Files\Model\File')->modelTypeId(),  '=', 'sc', true)
							->mergeWith(
								\GO\Base\Db\FindCriteria::newInstance()
									->addCondition('name', $queryStr, 'LIKE', 'sc', false)
									->addCondition('keywords', $queryStr, 'LIKE', 'sc', false)
							)
							->mergeWith($aclWhereCriteria)
					);
			
			if(isset($params['sort'])){
				
				if($params['sort'] == 'name') {
					 $findParams->order(new \go\core\db\Expression('t.name COLLATE utf8mb4_unicode_ci ' . (!isset($params['dir']) || $params['dir'] == 'ASC' ? 'ASC' : 'DESC')));
				}else
				{				
					$params['sort'] = str_replace('customFields', 'cf', $params['sort']);
					$findParams->order($params['sort'], $params['dir']);
				}
			}
			
			$filesStmt = \GO\Files\Model\File::model()->find($findParams);

			$response['total'] = $filesStmt->rowCount();

			$filesStmt = \GO\Files\Model\File::model()->find(
				$findParams
					->start($start)
					->limit($limit)
			);

			$response['results'] = array();
			$response['cm_state'] = '';
			$response['may_apply_state'] = false;
			$response['lock_state'] = false;
			$response['permission_level'] = 0;

			foreach ($filesStmt as $searchFileModel) {
				$record = $searchFileModel->getAttributes();
				$record['customFields'] = $searchFileModel->getCustomFields();
				$record = $this->formatListRecord($record, $searchFileModel);
				$record['name'] = $searchFileModel->path;
				$response['results'][] = $record;
			}

			return $response;
	}
        
	public function formatListRecord($record, $model) {

		$record['path'] = $model->path;

		if ($model instanceof Folder) {
			$record['type_id'] = 'd:' . $model->id;
			$record['type'] = \GO::t("Folder", "files");
			$record['size'] = '-';
			$record['extension'] = 'folder';
			$record['readonly']=$model->readonly;
		} else {
			$record['type_id'] = 'f:' . $model->id;
			$record['type'] = \GO\Base\Fs\File::getFileTypeDescription($model->extension);
			$record['extension'] = strtolower($model->extension);
			$record['size']=$model->size;
			$record['permission_level']=$this->_listFolderPermissionLevel;
			$record['unlock_allowed']=$model->unlockAllowed();

			if(empty($_REQUEST['noHandler'])){ // Added this line because the json_decode function cannot handle javascript. When noHandler is set to true, this line will be skipped
				$record['handler']='startjs:function(){'.$model->getDefaultHandler()->getHandler($model).'}:endjs';
			}
		}
		$record['thumb_url'] = $model->thumbURL;

		return $record;
	}

	private function _checkExistingModelFolder($model, $folder, $mustExist=false) {

		\GO::debug("Check existing model folder ".$model->className()."(ID:".$model->id." Folder ID: ".$folder->id." ACL ID: ".$model->findAclId().")");

		if(!$folder->fsFolder->exists())
		{
			//throw new \Exception("Fs folder doesn't exist! ".$folder->fsFolder->path());
			\GO::debug("Deleting it because filesystem folder doesn't exist");
			$folder->readonly = 1; //makes sure acl is not deleted
			$folder->delete(true);
			if($mustExist)
				return $this->_createNewModelFolder($model);
			else
				return 0;
		}

		//todo test this:
//      if(!isset($model->acl_id) && empty($params['mustExist'])){
//          //if this model is not a container like an addressbook but a contact
//          //then delete the folder if it's empty.
//          $ls = $folder->fsFolder->ls();
//          if(!count($ls) && $folder->fsFolder->mtime()<time()-60){
//              $folder->delete();
//              $response['files_folder_id']=$model->files_folder_id=0;
//              $model->save();
//              return $response['files_folder_id'];
//          }
//      }



		$currentPath = $folder->path;
		$newPath = \go\core\util\StringUtil::normalize(rtrim($model->buildFilesPath(),'.'));
		

		if(!$newPath)
			return false;

		if(\GO::router()->getControllerAction()=='checkdatabase'){
			//Always ensure folder exists on check database
			$destinationFolder = Folder::model()->findByPath(
							dirname($newPath), true, array('acl_id'=>$model->findAclId(),'readonly'=>1));
		}

		if ($currentPath != $newPath) {

			\GO::debug("Moving folder ".$currentPath." to ".$newPath);

			//model has a new path. We must move the current folder
			$destinationFolder = Folder::model()->findByPath(
							dirname($newPath), true, array('acl_id'=>$model->findAclId(),'readonly'=>1));


			//sometimes the folder must be moved into a folder with the same. name
			//for example:
			//projects/Name must be moved into projects/Name/Name
			//then we temporarily move it to a temp name
			if($destinationFolder->id==$folder->id || $destinationFolder->fsFolder->isSubFolderOf($folder->fsFolder)){
				\GO::debug("Destination folder is the same!");
				$folder->name=uniqid();
				$folder->systemSave=true;
				$folder->save(true);

				\GO::debug("Moved folder to temp:".$folder->fsFolder->path());

				\GO::modelCache()->remove("GO\Files\Model\Folder");

				$destinationFolder = Folder::model()->findByPath(
							dirname($newPath), true);
				

				\GO::debug("Now moving to:".$destinationFolder->fsFolder->path());

			}

			if($destinationFolder->id==$folder->id){
				throw new \Exception("Same ID's!");
			}

			$fsFolder = new \GO\Base\Fs\Folder($newPath);
//          $fsFolder->appendNumberToNameIfExists();

			if(($existingFolder = $destinationFolder->hasFolder($fsFolder->name()))){
				\GO::debug("Merging into existing folder.".$folder->path.' ('.$folder->id.') -> '.$existingFolder->path.' ('.$existingFolder->id.')');
				//if (!empty($model->acl_id))
				$existingFolder->acl_id = $model->findAclId();
				$existingFolder->visible = 0;
				$existingFolder->readonly = 1;
				$existingFolder->save(true);

				$folder->systemSave = true;

				$existingFolder->moveContentsFrom($folder, true);

				//delete empty folder.
				$folder->readonly = 1; //makes sure acl is not deleted
				$folder->delete(true);

				return $existingFolder->id;

			}else
			{
//              if ($model->acl_id>0)
//                  $folder->acl_id = $model->acl_id;
//              else
//                  $folder->acl_id=0;
				$folder->acl_id = $model->findAclId();

				$folder->name = $fsFolder->name();
				$folder->parent_id = $destinationFolder->id;
				$folder->systemSave = true;
				$folder->visible = 0;
				$folder->readonly = 1;
				if($folder->isModified())
					if(!$folder->save(true)){
						throw new \Exception(var_export($folder->getValidationErrors(), true));
					}
			}
		}else
		{
			\GO::debug("No change needed");
//          if ($model->acl_id>0)
//              $folder->acl_id = $model->acl_id;
//          else
//              $folder->acl_id=0;
			$folder->acl_id = $model->findAclId();
			$folder->systemSave = true;
			$folder->visible = 0;
			$folder->readonly = 1;
			if($folder->isModified())
				$folder->save(true);
		}

		return $folder->id;
	}

	private function _createNewModelFolder(\GO\Base\Db\ActiveRecord $model) {

		GO::debug("Create new model folder ".$model->className()."(ID:".$model->id.")");
		$filesPath = \go\core\util\StringUtil::normalize(rtrim($model->buildFilesPath(),'.'));
		$folder = Folder::model()->findByPath($filesPath,true, array('readonly'=>1));
		
		if(!$folder){
			throw new \Exception("Failed to create folder ".$filesPath);
		}
//      if (!empty($model->acl_id))
//          $folder->acl_id = $model->acl_id;

		$folder->acl_id=$model->findAclId();
		
		$folder->visible = 0;
		$folder->readonly = 1;
		$folder->systemSave = true;
		$folder->save(true);
		
		return $folder->id;
	}
	
	
	protected function checkEntityFolder($params) {
		$cls = $params['model'];
		
		$entityType = \go\core\orm\EntityType::findByName($params['model']);
		$cls = $entityType->getClassName();
		
		$entity = $cls::findById($params['id']);
		
		$folder = Folder::model()->findForEntity($entity);
		return [
				"success" => true,
				"files_folder_id" => $folder->id
		];
	}

	/**
	 * check if a model folder exists
	 *
	 * @param type $params
	 * @return type
	 */
	protected function actionCheckModelFolder($params) {
		
		$cls = $params['model'];
		$entityType = \go\core\orm\EntityType::findByName($params['model']);
		if(!empty($entityType)) {
			$cls = $entityType->getClassName();
		}
		
		if(strpos($params['model'], '\\') === false && is_a($cls, '\\go\\core\\orm\\Entity', true)) {
			return $this->checkEntityFolder($params);
		}
		
		$obj = new $cls(false);
		$model = $obj->findByPk($params['id'],false, true);

		$response['success'] = true;
		$response['files_folder_id'] = $this->checkModelFolder($model, true, !empty($params['mustExist']));
		return $response;
	}

	public function checkModelFolder(\GO\Base\Db\ActiveRecord $model, $saveModel=false, $mustExist=false) {
		$oldAllowDeletes = \GO\Base\Fs\File::setAllowDeletes(false);
	
		$newFolder = false;
		$folder = false;
		if ($model->files_folder_id > 0){
			
			GO::debug('Model has files_folder_id '.$model->files_folder_id);

			$folder = Folder::model()->findByPk($model->files_folder_id, false, true);
			
			//record has an ID but the folder is missing from the database. Attempt to create new one.
			$mustExist = true;
		}

		if ($folder) {
			
			GO::debug('Folder exists in database');
					
			$model->files_folder_id = $this->_checkExistingModelFolder($model, $folder, $mustExist);

			if ($saveModel && $model->isModified())
				$model->save(true);
		}elseif ($model->alwaysCreateFilesFolder() || $mustExist) {
			
			GO::debug('Folder does not exist in database. Will create it.');
		
			//this model has an acl_id. So we should create a shared folder with this acl.
			//this folder should always exist.
			//only new models that have it's own acl field should always have a folder.
			//otherwise it will be created when first accessed.
			$model->files_folder_id = $this->_createNewModelFolder($model);
			
			$newFolder = true;

			if ($saveModel && $model->isModified())
				$model->save(true);
		}

		if(empty($model->files_folder_id))
			$model->files_folder_id=0;

		 \GO\Base\Fs\File::setAllowDeletes($oldAllowDeletes);
		 
		 if($model->files_folder_id) {
			$this->fireEvent('checkmodelfolder', array($model, $folder, $newFolder));
		 }

		return $model->files_folder_id;
	}

	protected function actionProcessUploadQueue($params) {

		$response['success'] = true;

		if (!isset($params['overwrite']))
			$params['overwrite'] = 'ask'; //can be ask, yes, no

		$destinationFolder = Folder::model()->findByPk($params['destination_folder_id']);

		if (!$destinationFolder->checkPermissionLevel(\GO\Base\Model\Acl::CREATE_PERMISSION))
			throw new \GO\Base\Exception\AccessDenied();

		if(isset($params['blobs'])) {
			$paths = json_decode($params['blobs']);
		} else if(isset(\GO::session()->values['files']['uploadqueue'])) {
			$paths = \GO::session()->values['files']['uploadqueue'];
		} else {
			throw new \Exception("Nothing to process");
		}

		$this->processPaths($paths, $destinationFolder, $params['overwrite'], $response);

		return $response;
	}

	private function processPaths($paths, Folder $currentFolder, $overwrite, &$response) {

		$removeBlobs = [];

		while ($tmpfile = array_shift($paths)){
			if(!is_string($tmpfile)) {
				// its a json object with blob data
				$blob = $tmpfile;

				$tmpfile = Blob::buildPath($blob->blobId);
			} else{
				unset($blob);
			}

			$destinationFolder = $currentFolder;
			if (is_dir($tmpfile)) {
				$folder = new \GO\Base\Fs\Folder($tmpfile);
				if ($folder->exists()) {
					$folder->move($destinationFolder->fsFolder, false, true);
					$destinationFolder->addFileSystemFolder($folder);
				}
			} else {
				$file = new \GO\Base\Fs\File($tmpfile);
				$filename = $file->name();
				if(isset($blob)) {
					if(isset($blob->subfolder)) {
						while($fname = array_shift($blob->subfolder)){
							if($f = $destinationFolder->hasFolder($fname)) {
								$destinationFolder = $f;
							} else {
								$destinationFolder = $destinationFolder->addFolder($fname);
							}
						}
					}
					$filename = $blob->name;
					$removeBlob = $this->removeBlob($blob->blobId);
				}else{
					$removeBlob = false;
				}

				if ($file->exists()) {

					$existingFile = $destinationFolder->hasFile($filename);
					if ($existingFile) {
						switch ($overwrite) {
							case 'ask':
								array_unshift($paths, $tmpfile);
								$response['fileExists'] = $filename;
								continue 2;
							case 'yes':
								$params['overwrite'] = 'ask';
							case 'yestoall':
								//we dont want overwrite file in no case
								if(!$removeBlob) {
									$newFile = GO\Base\Fs\File::tempFile();
									$file = $file->copy($newFile->parent(), $newFile->name());
								}
								$existingFile->replace($file);
								if($removeBlob) {
									$removeBlobs[] = $removeBlob->id;
								}
								break;
							case 'no':
								$params['overwrite'] = 'ask';
							case 'notoall':
								continue 2;
						}
					} else {
						if(!$removeBlob || $this->blobIsNeededAgain($removeBlob->id, $paths)) {
							$newFile = GO\Base\Fs\File::tempFile();
							$file = $file->copy($newFile->parent(), $newFile->name());
						}
						$destinationFolder->addFileSystemFile($file, false, $filename);
						if($removeBlob) {
							$removeBlobs[] = $removeBlob->id;
						}
					}
					$response['success'] = true;
				}
			}
		}

		if(count($removeBlobs)) {
			Blob::delete(['id' => $removeBlobs]);
		}
	}

	/**
	 * Check if user uploaded the same blob more than once so the blob must be copied
	 *
	 * @param $id
	 * @param $blobs
	 */
	private function blobIsNeededAgain($id, $blobs) {
		foreach($blobs as $blob) {
			if($blob->id == $id) {
				return true;
			}
		}
		return false;
	}

	private function removeBlob($blobId) {
		$blob = Blob::findById($blobId);
		if(!$blobId) {
			throw new \Exception("Blob not found");
		}
		return isset($blob->staleAt) ? $blob : false;

	}

	protected function actionCompress($params) {

		ini_set('max_execution_time', 600);
		ini_set('memory_limit', '512M');
		//So other actions can run simultanuously
		GO::session()->closeWriting();
		
		$sources = json_decode($params['compress_sources'], true);

		$workingFolder = Folder::model()->findByPk($params['working_folder_id']);
		$destinationFolder = Folder::model()->findByPk($params['destination_folder_id']);
		$archiveFile = new \GO\Base\Fs\File(\GO::config()->file_storage_path.$destinationFolder->path . '/' . $params['archive_name'] . '.zip');

		if(!$destinationFolder->checkPermissionLevel(\GO\Base\Model\Acl::WRITE_PERMISSION)){
			throw new AccessDenied();
		}

		if($archiveFile->exists())
			throw new \Exception(sprintf(\GO::t("Filename %s already exists", "files"), $archiveFile->stripFileStoragePath()));
		
		$sourceObjects = array();
		for($i=0;$i<count($sources);$i++){

			$file = \GO\Files\Model\File::model()->findByPath($sources[$i]);
			if(!$file) {
				throw new NotFound();
			}

			if(!$file->getPermissionLevel()) {
				throw new AccessDenied();
			}

			$path = \GO::config()->file_storage_path.$sources[$i];			
			$sourceObjects[]=\GO\Base\Fs\Base::createFromPath($path);
		}

		if(\GO\Base\Fs\Zip::create($archiveFile, $workingFolder->fsFolder, $sourceObjects)){
			\GO\Files\Model\File::importFromFilesystem($archiveFile);
			$response['success']=true;
		}  else {
			throw new \Exception("ZIP creation failed");
		}


		return $response;
	}
	
	/**
	 * Compress the selected files and return as download
	 * 
	 * @param array $params
	 * @return boolean
	 * @throws \Exception
	 */
	protected function actionCompressAndDownload($params) {

		if(!isset($params['archive_name']))
			Throw new \Exception('No name for the archive given');
		
		ini_set('max_execution_time', 600);
		ini_set('memory_limit', '512M');
		GO::session()->closeWriting();
		
		$sources = json_decode($params['sources'], true);
		
		$workingFolder = false;
		
		// Read the sources and create objects from them
		$sourceObjects = array();
		
		// The total filesize in bytes
		$totalFileSize = 0;
		
		// The maximum filesize that is allowed to zip (Default is 256MB)
		$maxFilesize = GO::config()->zip_max_file_size;
		
		for($i=0;$i<count($sources);$i++){

			$file = \GO\Files\Model\File::model()->findByPath($sources[$i]);
			if(!$file) {
				throw new NotFound();
			}

			if(!$file->getPermissionLevel()) {
				throw new AccessDenied();
			}

			$path = \GO::config()->file_storage_path.$sources[$i];
			
			$sourceFile = \GO\Base\Fs\Base::createFromPath($path);
			
			// Increase the total filesize
			$totalFileSize += $sourceFile->size();
			
			if($totalFileSize >= $maxFilesize){
				throw new \Exception(sprintf(
					\GO::t("The total size of the files that are selected to be zipped is too big. (Only %s is allowed.)"), 
					\GO\Base\Util\Number::formatSize($maxFilesize,2)
				));
			}
			
			// Set the workingFolder
			if(!$workingFolder){
				$workingFolder = $sourceFile->parent();
			}
			
			$sourceObjects[]= $sourceFile;
		}
		
		// Create the zipped temp file object
		$archiveFile = \GO\Base\Fs\File::tempFile($params['archive_name'],'zip');
		if($archiveFile->exists())
			throw new \Exception(sprintf(\GO::t("Filename %s already exists", "files"), $archiveFile->stripFileStoragePath()));
		
		// Create the zipfile
		if(\GO\Base\Fs\Zip::create($archiveFile, $workingFolder, $sourceObjects)){
			
			// Output download headers
//			\GO\Base\Util\Http::outputDownloadHeaders($archiveFile,false,true);
//			$archiveFile->output();
			$response['archive'] = $archiveFile->stripTempPath();
			$response['success'] = true;
		} else {
			throw new \Exception("ZIP creation failed");
		}
		
		return $response;
	}


	protected function actionDecompress($params){
		
		//So other actions can run simultanuously
		GO::session()->closeWriting();
		
		
		if (!\GO\Base\Util\Common::isWindows())
			putenv('LANG=en_US.UTF-8');

		$sources = json_decode($params['decompress_sources'], true);


		$workingFolder = Folder::model()->findByPk($params['working_folder_id']);
		
		if(!$workingFolder->checkPermissionLevel(\GO\Base\Model\Acl::WRITE_PERMISSION)){
			throw new \GO\Base\Exception\AccessDenied("No permission to write in the target folder.");
		}

		$workingPath = \GO::config()->file_storage_path.$workingFolder->path;
		chdir($workingPath);


		while ($filePath = array_shift($sources)) {
			$file = new \GO\Base\Fs\File(\GO::config()->file_storage_path.$filePath);
			switch(strtolower($file->extension())) {
				case 'zip':					
					
					$folder = \GO\Base\Fs\Folder::tempFolder(uniqid());
					
					if(class_exists("\ZipArchive")){
						$zip = new \ZipArchive;
						$zip->open($file->path());
						$zip->extractTo($folder->path());									
						$this->_convertZipEncoding($folder);
					}else
					{
						chdir($folder->path());					
						$cmd = \GO::config()->cmd_unzip.' -n '.escapeshellarg($file->path());
						exec($cmd, $output, $ret);
						if($ret!=0)
						{
							throw new \Exception("Could not decompress\n".implode("\n",$output));
						}
					}
					
					$items = $folder->ls();
					
					foreach($items as $item){
						$item->move(new \GO\Base\Fs\Folder($workingPath));
					}
					
					$folder->delete();
					
					break;
				case 'gz':
				case 'tgz':
					$cmd = \GO::config()->cmd_tar.' zxf '.escapeshellarg($file->path());
					exec($cmd, $output, $ret);

					if($ret!=0)
					{
						throw new \Exception("Could not decompress\n".implode("\n",$output));
					}
					break;

				case 'tar':
					$cmd = \GO::config()->cmd_tar.' xf '.escapeshellarg($file->path());
					
					exec($cmd, $output, $ret);

					if($ret!=0)
					{
						throw new \Exception("Could not decompress\n".implode("\n",$output));
					}
					break;
			}
		}
		
		$workingFolder->syncFilesystem(true);

		return array('success'=>true);

	}
	
	private function _convertZipEncoding(\GO\Base\Fs\Folder $folder, $charset='CP850'){
		$items = $folder->ls();
		
		foreach($items as $item){
			
			if(!\GO\Base\Util\StringHelper::isUtf8($item->name()))
				$item->rename(\GO\Base\Util\StringHelper::clean_utf8($item->name(), $charset));

			if($item->isFolder()){
				$this->_convertZipEncoding($item, $charset);
			}
		}
	}


	/**
	 * The savemailas module can send attachments along to be stored as files with
	 * a note, task, event etc.
	 *
	 * @param type $response
	 * @param type $model
	 * @param type $params
	 */
	public function processAttachments(&$response, &$model, &$params){
		//Does this belong in the controller?
		if (!empty($params['tmp_files'])) {
			$tmp_files = json_decode($params['tmp_files'], true);

			if(count($tmp_files)){
				$folder_id = $this->checkModelFolder($model, true, true);

				$folder = Folder::model()->findByPk($folder_id);

				while ($tmp_file = array_shift($tmp_files)) {
					if (!empty($tmp_file['tmp_file'])) {

						$file = new \GO\Base\Fs\File(\GO::config()->tmpdir.$tmp_file['tmp_file']);
						$file->move(new \GO\Base\Fs\Folder(\GO::config()->file_storage_path . $folder->path));

						$folder->addFile($file->name());
					}
				}
			}
		}
	}


	protected function actionImages($params){
		if(isset($params["id"])){
			$currentFile = \GO\Files\Model\File::model()->findByPk($params["id"]);
		}else
		{
			$currentFile = \GO\Files\Model\File::model()->findByPath($params["path"]);
		}

		$folder = $currentFile->folder();

		$thumbParams = json_decode($params['thumbParams'], true);

		$response["success"]=true;
		$response['images']=array();
		$response['index']=$index=0;

		if(!isset($params["sort"]))
			$params["sort"]="name";

		if(!isset($params["dir"]))
			$params["dir"]="ASC";

		$findParams = \GO\Base\Db\FindParams::newInstance()
						->order($params["sort"], $params["dir"]);

		$stmt = $folder->files($findParams);
		while($file = $stmt->fetch()){
			if($file->isImage()){
				if($file->id == $currentFile->id)
					$response['index']=$index;

				$index++;

				$response['images'][]=array(
					"name"=>$file->name,
					"download_path"=>$file->downloadUrl,
					"src"=>$file->getThumbUrl($thumbParams)
				);
			}
		}

		return $response;
	}
	
	
	/**
	 * Delete a single not. Must be a POST request
	 *
	 * @param int $id
	 * @throws Exception
	 * @throws \GO\Base\Exception\NotFound
	 */
	protected function actionDelete($id) {

		if (!GO::request()->isPost() && !GO::environment()->isCli()) {
			throw new Exception('Delete must be a POST request');
		}

		$model = Folder::model()->findByPk($id);
		if (!$model)
			throw new \GO\Base\Exception\NotFound();

		$model->delete();

		echo $this->render('delete', array('model' => $model));
	}
}
