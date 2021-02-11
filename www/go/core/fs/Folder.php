<?php
namespace go\core\fs;

use Exception;

/**
 * A folder object
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Folder extends FileSystemObject {
	
	
//	/**
//	 * Create a new file object. Filesystem file is not created automatically.
//	 *
//	 * @param string $relativePath
//	 * @return File
//	 */
//	public function getFolder($relativePath) {		
//		
//		$childPath = $this->path . '/' . $relativePath;
//
//		$file = new File($childPath);
//		
//		if($file->exists()) {
//			throw new \Exception("File '$relativePath' already exists");
//		}
//		
//		return $file;
//	}
//
//	/**
//	 * Create a new folder object. Filesystem folder is not created automatically.
//	 *
//	 * @param string $relativePath
//	 * @return Folder
//	 */
//	public function getFolder($relativePath) {
//		$childPath = $this->path . '/' . $relativePath;
//
//		$folder = new Folder($childPath);
//		$folder->createMode = $this->createMode;
//		
//		if($folder->exists()) {
//			throw new \Exception("Folder '$relativePath' already exists");
//		}
//		
//		return $folder;
//	}


	/**
	 * Check if the file or folder exists
	 * @return boolean
	 */
	public function exists() {
		return is_dir($this->path) || is_link($this->path);
	}

	/**
	 * Get a temporary folder
	 * 
	 * @return static
	 */
	public static function tempFolder() {
		return go()->getTmpFolder()->getFolder(uniqid(time()));
	}

	/**
	 * Check if folder is empty
	 *
	 * @return bool
	 */
	public function isEmpty() {

		$handle = opendir($this->getPath());
		while (false !== ($entry = readdir($handle))) {
			if ($entry != "." && $entry != "..") {
				closedir($handle);
				return FALSE;
			}
		}
		closedir($handle);
		return TRUE;
	}

	/**
	 * Get folder directory listing.
	 *
	 * @param boolean $includeFiles
	 * @param boolean $includeFolders
	 * 
	 * @return File[]|Folder[]|array[]
	 */
	public function getChildren($includeFiles = true, $includeFolders = true) {
		if (!$dir = opendir($this->path)) {
			return []; // Return an empty array to prevent crashing foreach() loops
		}

		$children = [];
		while ($item = readdir($dir)) {
			
			$folderPath = $this->path . '/' . $item;
			
			if ($item !== "." && $item !== "..") {
				
				$isFile = is_file($folderPath);
				
				if ($isFile && $includeFiles) {
					$children[] = new File($folderPath);
				} else if (!$isFile && $includeFolders){
					$children[] = new Folder($folderPath);
				}		
			}
		}

		closedir($dir);

		return $children;
	}
	
	/**
	 * Get all files in this folder
	 * 
	 * @return File[]
	 */
	public function getFiles() {
		return $this->getChildren(true, false);
	}
	
	/**
	 * Get all subfolders in this folder.
	 * 
	 * @return Folder[]
	 */
	public function getFolders() {
		return $this->getChildren(false, true);
	}
	
	/**
	 * Get a file in this folder
	 * 
	 * This function does not check if the folder exists.
	 * 
	 * @return File
	 */
	public function getFile($relativePath) {
		$childPath = $this->path . '/' . $relativePath;
		$file = new File($childPath);
		
		return $file;
	}
	
	/**
	 * Get a subfolder
	 * 
	 * This function does not check if the folder exists.
	 * 
	 * @return Folder
	 */
	public function getFolder($relativePath) {
		$childPath = $this->path . '/' . $relativePath;		
		$folder = new Folder($childPath);
		
		return $folder;
	}	
	
	/**
	 * Get the parent folder object
	 *
	 * @return Folder Parent folder object
	 */
	public function getParent() {

		$parentPath = dirname($this->path);
		if ($parentPath == $this->path) {
			return false;
		}

		return new Folder($parentPath);
	}
	
	
	/**
	 * Check if the file or folder is writable for the webserver user.
	 *
	 * @return boolean
	 */
	public function isWritable() {
		
		if($this->exists()) {
			return is_writable($this->path);
		}else
		{
			return $this->getParent()->isWritable();
		}
	}

	/**
	 * Delete the folder
	 *
	 * @return boolean
	 */
	public function delete() {
		if (!$this->exists()){
			return true;
		}

		//just delete symlink and not contents of linked folder!
		if (is_link($this->path)) {
			return unlink($this->path);
		}

		$items = $this->getChildren(true);

		foreach ($items as $item) {
			if (!$item->delete()) {
				return false;
			}
		}

		return !is_dir($this->path) || rmdir($this->path);
	}
	/**
	 * Move the folder to a new location.
	 *
	 * Note, it's not moved into this folder but moved as this name into it's parent
	 *
	 * @param Folder $destinationFolder This folder may not exist
	 */
	public function move(Folder $destinationFolder) {
		if (!$this->exists()) {
			throw new Exception("Folder '" . $this->getPath() . "' does not exist");
		}

		if ($destinationFolder->exists()) {
			throw new Exception("Destination folder already exists!");
		}

//		if(is_link($this->path)){
//			$link = new File($this->path);
//			return $link->move(new File($destinationFolder->path()));
//		}

		if($destinationFolder->isDescendantOf($this)){
			throw new Exception("Can't move this folder into a child folder!");
		}

		$newPath = $destinationFolder->getPath();

		//do nothing if path is the same.
		if ($newPath === $this->getPath()) {
			return true;
		}
		
		$success = false;
		try{
			$success = rename($this->getPath(), $newPath);
		} catch(\Exception $e) {
			//rename fails accross partitions. Ignore and retry with copy delete.
			go()->warn("Rename failed. Falling back on copy, delete");
		}

		if (!$success) { // Notice suppressed by @
			//	throw new Exception("Rename failed");
			// If renaming is throwing an error then do it the old way.
			// This is done because of problems when moving items across partitions.
			// See https://bugs.php.net/bug.php?id=50676 for more info about this.
			// If rename fails then try the old method
			$movedFolder = new Folder($newPath);
			$movedFolder->create();

			$ls = $this->getChildren(true);
			foreach ($ls as $fsObject) {
				if ($fsObject->isFile()) {
					$fsObject->move($movedFolder->getFile($fsObject->getName()));
				} else {
					$fsObject->move($movedFolder->getFolder($fsObject->getName()));
				}
			}

			$this->delete();

			$newPath = $movedFolder->getPath();
		}

		$this->path = $newPath;

		return true;
	}

	/**
	 * Copy a folder to a new location
	 *
	 * @param Folder $destinationFolder This folder may not exist
	 * @return Folder
	 */
	public function copy(Folder $destinationFolder) {
		
		if($destinationFolder->isDescendantOf($this)){
			throw new Exception("Can't copy this folder into a child folder!");
		}

		if ($destinationFolder->exists()) {
			throw new Exception("Destination folder '".$destinationFolder->getPath()."' already exists!");
		}

		if (!$destinationFolder->create()) {
			throw new Exception("Could not create " . $destinationFolder->getPath());
		}

		$ls = $this->getChildren();
		foreach ($ls as $fsObject) {
			if ($fsObject->isFolder()) {
				$fsObject->copy($destinationFolder->getFolder($fsObject->getName()));
			} else {
				$fsObject->copy($destinationFolder->getFile($fsObject->getName()));
			}
		}

		return $destinationFolder;
	}

	/**
	 * Create the folder
	 *
	 * @param int $permissionsMode 
	 * 
	 * Use umask() to change defaults.
	 * 
	 * Note that mode is not automatically
	 * assumed to be an octal value, so strings (such as "g+w") will
	 * not work properly. To ensure the expected operation,
	 * you need to prefix mode with a zero (0):
	 * 
	 *
	 * @return self|boolean
	 */
	public function create($permissionsMode = 0777) {

		if (is_dir($this->path)) {
			return $this;
		}

		try{
//			var_dump($permissionsMode);
			if (mkdir($this->path, $permissionsMode, true)) {
				//needed for concurrency problems
				clearstatcache();

				return $this;
			} 
		}
		catch(\Exception $e){
			\go\core\App::get()->debug($e->getMessage());
		}
		throw new Exception("Could not create folder " . $this->path);		
	}

	/**
	 * Create a symbolic link in this folder
	 *
	 * @param Folder $target
	 * @param string $linkName optional link name. If omitted the name will be the same as the target folder name
	 * @return File
	 * @throws Exception
	 */
	public function createLink(Folder $target, $linkName = null) {

		if (!isset($linkName)) {
			$linkName = $target->getName();
		}

		$link = $this->getFile($linkName);
		if ($link->exists()) {
			throw new Exception("Path " . $link->getPath() . " already exists");
		}

		if (symlink($target->getPath(), $link->getPath())) {
			return $link;
		} else {
			throw new Exception("Failed to create link " . $link->getPath() . " to " . $target->getPath());
		}
	}

	/**
	 * Checks if a filename exists and renames it.
	 *
	 * @param string $filepath The complete path to the file
	 * @param string  New filepath
	 */
	public function appendNumberToNameIfExists() {
		$origPath = $this->path;
		$x = 1;
		while ($this->exists()) {
			$this->path = $origPath . ' (' . $x . ')';
			$x++;
		}
		return $this->path;
	}

	/**
	 * Calculate size of the directory in bytes.
	 *
	 * @return int/false
	 */
	public function calculateSize() {
		$cmd = 'du -sb "' . $this->path . '" 2>/dev/null';

		$io = popen($cmd, 'r');

		if ($io) {
			$size = fgets($io, 4096);
			if($size === false) {
				return false;
			}
			$size = preg_replace('/[\t\s]+/', ' ', trim($size));
			$size = substr($size, 0, strpos($size, ' '));

			return $size;
		} else {
			return false;
		}
	}
	
	/**
	 * Find files and folder matching a regular expression pattern
	 * 
	 * @param array|string $config If string is given then it's used as $config['regex'].
	 *  $config is an array that can have:
	 *  regex: then name must match this with preg_match(); eg '/.*\.sql/'
	 *  older: Return files if the file wasn't modified after the given DateTime object
	 *  newer: Return files if the file was modified after the given DateTime object
	 *  empty: Return empty folders
	 *
	 *
	 * @param boolean $findFolders
	 * @param boolean $findFiles
	 * @return File[]|Folder[]
	 */
	public function find($config = [], $findFolders = true, $findFiles = true) {
		$result = [];

		if(!is_array($config)) {
			$config = ['regex' => $config];
		}

		foreach($this->getChildren($findFiles, true) as $child) {
			$isFolder = $child->isFolder();

			if($isFolder) {
				//Do exists check for broken links
				if(!$child->exists()) {
					continue;
				}

				if(
					$findFolders &&
					(empty($config['empty']) || $child->isEmpty()) &&
					(!isset($config['regex']) || preg_match($config['regex'], $child->getName()))
				){
					$result[] = $child;
				}

				$result = array_merge($result, $child->find($config, $findFolders, $findFiles));

			} else {
				$m = $child->getModifiedAt();
				if($findFiles &&
					(!isset($config['regex']) || preg_match($config['regex'], $child->getName())) &&
					(!isset($config['older']) || $m < $config['older'])  &&
					(!isset($config['newer']) || $m > $config['newer'])
				) {
					$result[] = $child;
				}

			}


		}
		
		return $result;
	}


}
