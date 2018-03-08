<?php
namespace GO\Dropbox\Model;

use GO;
use GO\Dropbox\Model\DropboxUser;
use GO\Dropbox\Model\Settings;
use GO\Files\Model\File;
use GO\Files\Model\Folder;
use Kunnu\Dropbox\Authentication\DropboxAuthHelper;
use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;

/**
 * DropboxClient
 */
abstract class DropboxClient {

	const DBX_ROOT = '/';
	
	private static $dropboxApp;
	private static $dropboxService;
	private static $dropboxAuthHelper;
	private static $dbxUser;
	
	/**
	 * Set the dropbox user
	 * @param DropboxUser $dbxUser
	 * 
	 * @return DropboxUser
	 */
	public static function setDropboxUser(DropboxUser $dbxUser){
		self::reset();
		self::$dbxUser = $dbxUser;

		return self::$dbxUser;
	}
	
	
	public static function reset(){
		self::$dbxUser = null;
		self::$dropboxApp = null;
		self::$dropboxService = null;
		self::$dropboxAuthHelper = null;		
	}
	
	/**
	 * 
	 * @return DropboxApp
	 */
	public static function getDropboxApp(){
				
		// Find an accessToken of the logged in user
		$accessToken = null;
		
		$dbxUser = self::getDropboxUser();
				
		if(!empty($dbxUser->access_token)){
			$accessToken = $dbxUser->access_token;
		}
		
		if(empty(self::$dropboxApp)){
			// Get client id and secret from the settings object
			$settings = Settings::load();
		
			self::$dropboxApp = new DropboxApp($settings->app_key, $settings->app_secret, $accessToken);
		}
		
		return self::$dropboxApp;
	}
	
	/**
	 * 
	 * @return DropboxUser
	 */
	public static function getDropboxUser(){
		
		if(!self::$dbxUser){
			self::$dbxUser = DropboxUser::model()->findByPk(GO::user()->id);
		}
		
		if(!self::$dbxUser){
			self::$dbxUser = new DropboxUser();
		}
		
		return self::$dbxUser;		
	}
	
	/**
	 * 
	 * @param DropboxApp $app
	 * @return Dropbox
	 */
	public static function getDropboxService($app){
		
		if(empty(self::$dropboxService)){
			self::$dropboxService = new Dropbox($app);
		}
		
		return self::$dropboxService;
	}
	
	/**
	 * 
	 * @param Dropbox $app
	 * @return DropboxAuthHelper
	 */
	public static function getAuthHelper($service){
		
		if(empty(self::$dropboxAuthHelper)){
			self::$dropboxAuthHelper = $service->getAuthHelper();
		}
		
		return self::$dropboxAuthHelper;
	}
	
	
	public static function syncDropboxToGO(){
		
		GO::session()->runAsRoot();
		GO::setMaxExecutionTime(0);
		
		$app = DropboxClient::getDropboxApp();		
		$service = DropboxClient::getDropboxService($app);
		$dbxUser = DropboxClient::getDropboxUser();
		
		\GO::debug("Dropbox: Sync to GO for user: ".$dbxUser->user_id);
		
		$localFolder = $dbxUser->getDropboxFolder();
		
		$dbxCursor = $service->listFolderLatestCursor(self::DBX_ROOT);
		
		if(empty($dbxUser->delta_cursor) || $dbxCursor > $dbxUser->delta_cursor){
			// Updates on dropbox
			$modifiedItems = $service->listFolderContinue($dbxUser->delta_cursor?$dbxUser->delta_cursor:$dbxCursor)->getItems();
			
			foreach($modifiedItems as $modifiedItem){
				
				$class = get_class($modifiedItem);
				$dbxPath = $modifiedItem->getPathDisplay();
				$goPath = $localFolder->getFullPath().$modifiedItem->getPathDisplay();
				
				switch($class){
					
					case 'Kunnu\Dropbox\Models\DeletedMetadata':
						// The file or folder is deleted on dropbox
						\GO::debug("Dropbox: Delete file from Group-Office " . $dbxPath . " -> " . $goPath);
						$file = File::model()->findByPath($goPath, false);
						if($file) {
							$file->delete();
						} else {
							$folder = Folder::model()->findByPath($goPath, false, array(), false);
							if($folder) {
								$folder->delete();
							} else {
								\GO::debug("Dropbox: Could not find path for delete file on Group-Office " . $goPath);
							}
						}
						break;
					
					case 'Kunnu\Dropbox\Models\FolderMetadata':
						// This is a folder on dropbox
						\GO::debug("Dropbox: Create folder on Group-Office " . $dbxPath . " -> " . $goPath);
						$folder = Folder::model()->findByPath($goPath, true);
						
						break;
					
					case 'Kunnu\Dropbox\Models\FileMetadata':
						// This is a file on dropbox
						\GO::debug("Dropbox: Create file on Group-Office " . $dbxPath . " -> " . $goPath);
						$folder = Folder::model()->findByPath(dirname($goPath), true, array(), false);
						$name = \GO\Base\Fs\File::utf8Basename($goPath);
						$path = $folder->fsFolder->createChild($name)->path();
						
						$fileMeta = $service->download($dbxPath, $path);

						touch($path, strtotime($fileMeta->modified));						
						break;
				}
			}
			
			$localFolder->syncFilesystem(true,false);
			
			// Sync success, save cursor
			$dbxUser->delta_cursor = $dbxCursor;
			$dbxUser->save();
		}		
		
	}
	
	
	public static function syncGOToDropbox(){

		GO::session()->runAsRoot();
		GO::setMaxExecutionTime(0);
		
		$app = DropboxClient::getDropboxApp();
		$service = DropboxClient::getDropboxService($app);
		$dbxUser = DropboxClient::getDropboxUser();
		
		\GO::debug("Dropbox: Sync to Dropbox for user: ".$dbxUser->user_id);
		
		$localFolder = $dbxUser->getDropboxFolder();
		
		$goSnapshot = self::getGroupOfficeSnapShot($localFolder);
		$dbxSnapshot = self::getDropboxSnapshot($service);
		
		foreach ($goSnapshot as $path => $props) {
			
			if(!isset($dbxSnapshot[$path]) || $dbxSnapshot[$path]['mtime'] < $props['mtime']) {
				
				if (is_file(GO::config()->file_storage_path . $props['path'])) {
					// if it's a file then upload it
					\GO::debug('Dropbox: Upload file: '.$props['path']);
					
					$localPath = GO::config()->file_storage_path . $props['path'];
					$fileMeta = $service->upload($localPath, $path, ['autorename' => true]);
					if (!isset($fileMeta)){
						throw new \Exception("Failed to create file '".$path."' on Dropbox");
					}
				} elseif(!isset($dbxSnapshot[$path])) {
					// Create the folder
					\GO::debug('Dropbox: Create folder '.$path);
					
					$folderMeta = $service->createFolder($path);
					if (!isset($folderMeta)){
						throw new \Exception("Failed to create folder '".$path."' on Dropbox");
					}
				}
			}
		}

		//reverse sort for deleting so that deeper files are deleted first.
		krsort($dbxSnapshot);
		
		foreach ($dbxSnapshot as $path => $props) {
			if (!isset($dbxSnapshot[$path])) {
				\GO::debug('Dropbox: Deleting from dropbox: '.$path);
				$deletedMeta = $service->delete($path);
				
				if (!isset($deletedMeta)){
					throw new \Exception("Failed to delete '".$path."' on Dropbox");
				}
			}
		}

		//get delta again so we won't process our own changes next sync
		$dbxCursor = $service->listFolderLatestCursor(self::DBX_ROOT);
//		// Sync success, save cursor
		$dbxUser->delta_cursor = $dbxCursor;
		$dbxUser->save();
	}
	
	protected static function getGroupOfficeSnapShot(\GO\Files\Model\Folder $folder, $sort = true) {

		$snapshot = array();
		
		$stmt = $folder->files();
		foreach ($stmt as $file) {

			// Strip local folder path
			$shortPath = str_replace(strtolower($folder->getFullPath()),'',strtolower($file->path));
			
			$snapshot[$shortPath] = array('mtime' => $file->mtime, 'path' => $file->path);
		}

		$stmt = $folder->folders();
		foreach ($stmt as $childFolder) {
			$shortPath = str_replace(strtolower($folder->getFullPath()),'',strtolower($childFolder->path));
			$snapshot[strtolower($shortPath)] = array('mtime' => $childFolder->mtime, 'path' => $childFolder->path);
			$snapshot = array_merge($snapshot, self::getGroupOfficeSnapShot($childFolder, false));
		}

		if($sort){
			ksort($snapshot);
		}

		return $snapshot;
	}
	
	protected static function getDropboxSnapshot($service, $dropboxPath = self::DBX_ROOT, $sort = true) {

		$snapshot = array();

		$listFolderContents = $service->listFolder($dropboxPath);
		$items = $listFolderContents->getItems();

		foreach($items->all() as $item){
			
			$class = get_class($item);
			
			switch($class){
				
				case 'Kunnu\Dropbox\Models\FileMetadata':
					$snapshot[strtolower($item->getPathLower())] = array('mtime' => $item->getServerModified(), 'path' => $item->getPathDisplay());
					break;
				
				case 'Kunnu\Dropbox\Models\FolderMetadata':
					$snapshot[strtolower($item->getPathLower())] = array('mtime' => null, 'path' => $item->getPathDisplay());
					$snapshot = array_merge($snapshot, self::getDropboxSnapshot($service, $item->getPathDisplay(), false));
					break;
			}
			
		}

		if($sort){
			ksort($snapshot);
		}

		return $snapshot;
	}
}
