<?php
namespace go\core\fs;

use Exception;
use go\core\App;
use Throwable;

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
	public function exists(): bool
	{
		return is_dir($this->path) || is_link($this->path);
	}

	/**
	 * Get a temporary folder
	 * 
	 * @return static
	 */
	public static function tempFolder(): Folder
	{
		return go()->getTmpFolder()->getFolder(uniqid(time()));
	}

	/**
	 * Check if folder is empty
	 *
	 * @return bool
	 */
	public function isEmpty(): bool
	{

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
	public function getChildren(bool $includeFiles = true, bool $includeFolders = true): array
	{
		if (!$dir = opendir($this->path)) {
			return []; // Return an empty array to prevent crashing foreach() loops
		}

		$children = [];
		while (false !== ($item = readdir($dir))) {
			
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
	public function getFiles(): array
	{
		return $this->getChildren(true, false);
	}
	
	/**
	 * Get all subfolders in this folder.
	 * 
	 * @return Folder[]
	 */
	public function getFolders(): array
	{
		return $this->getChildren(false, true);
	}

	/**
	 * Get a file in this folder
	 *
	 * This function does not check if the folder exists.
	 *
	 * @param string $relativePath
	 * @return File
	 */
	public function getFile(string $relativePath): File
	{
		$childPath = $this->path . '/' . $relativePath;
		return new File($childPath);
	}

	/**
	 * Get a subfolder
	 *
	 * This function does not check if the folder exists.
	 *
	 * @param string $relativePath
	 * @return Folder
	 */
	public function getFolder(string $relativePath): Folder
	{
		$childPath = $this->path . '/' . $relativePath;
		return new Folder($childPath);
	}	
	

	
	
	/**
	 * Check if the file or folder is writable for the webserver user.
	 *
	 * @return boolean
	 */
	public function isWritable(): bool
	{
		try {
			if ($this->exists()) {
				return is_writable($this->path);
			} else {
				return $this->getParent()->isWritable();
			}
		} catch(Throwable $e) {

			// open basedir restriction may lead here
			return false;
		}
	}

	/**
	 * Delete the folder
	 *
	 * @return boolean
	 * @throws Exception
	 */
	public function delete(): bool
	{
		self::checkDeleteAllowed($this);

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
	 * @throws Exception
	 */
	public function move(Folder $destinationFolder): bool
	{
		if (!$this->exists()) {
			throw new Exception("Folder '" . $this->getPath() . "' does not exist");
		}

		if ($destinationFolder->exists()) {
			throw new Exception("Destination folder already exists!");
		}

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
		} catch(Exception $e) {
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
	 * @throws Exception
	 */
	public function copy(Folder $destinationFolder): Folder
	{
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
	 * @return self
	 * @throws Exception
	 */
	public function create(int $permissionsMode = 0777): Folder
	{
		if (is_dir($this->path)) {
			return $this;
		}

		try{
			if (mkdir($this->path, $permissionsMode, true)) {
				//needed for concurrency problems
				clearstatcache();
				return $this;
			} 
		}
		catch(Exception $e){
			App::get()->debug($e->getMessage());
			if(is_dir($this->path)){
				//perhaps this was a race condition
				return $this;
			}
		}
		throw new Exception("Could not create folder " . $this->path);		
	}

	/**
	 * Create a symbolic link in this folder
	 *
	 * @param Folder $target
	 * @param string|null $linkName optional link name. If omitted the name will be the same as the target folder name
	 * @return File
	 * @throws Exception
	 */
	public function createLink(Folder $target, string|null $linkName = null): File
	{
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
	 * @return string
	 */
	public function appendNumberToNameIfExists(): string
	{
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
	 * @return int | null
	 */
	public function calculateSize() : ?int {
		$cmd = 'du -sbL "' . $this->path . '" 2>/dev/null';

		$io = popen($cmd, 'r');

		if ($io) {
			$size = fgets($io, 4096);
			if($size === false) {
				return false;
			}
			$size = preg_replace('/[\t\s]+/', ' ', trim($size));
			return substr($size, 0, strpos($size, ' '));
		} else {
			return null;
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
	 *  exclude: Exclude if name matches this regex.
	 *
	 *
	 * @param boolean $findFolders
	 * @param boolean $findFiles
	 * @return File[]|Folder[]
	 * @throws Exception
	 */
	public function find($config = [], bool $findFolders = true, bool $findFiles = true): array
	{
		$result = [];

		if(!is_array($config)) {
			$config = ['regex' => $config];
		}

		foreach($this->getChildren($findFiles, true) as $child) {

			if(!empty($config['exclude']) && preg_match($config['exclude'], $child->getName())) {
				continue;
			}

			$isFolder = $child->isFolder();

			if($isFolder) {
				//Do exists check for broken links
				if($child->isLink() && !$child->getLinkTarget()) {
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
