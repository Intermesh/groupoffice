<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: tickets.class.inc.php 9131 2010-10-01 10:03:59Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

namespace GO\Dav\Fs;
use Sabre;

class Directory extends \Sabre\DAV\FS\Directory{

	protected $_folder;
	protected $relpath;

	public function __construct($path, $overrideName = null) {

		$path = rtrim($path, '/');

		$this->relpath = \go\core\util\StringUtil::normalize($path);
		$path = \GO::config()->file_storage_path . $this->relpath;
		
		//		if(!$this->_getFolder()->checkPermissionLevel(\GO\Base\Model\Acl::READ_PERMISSION)){
//			\GO::debug("DAV: User ".\GO::user()->username." doesn't have write permission for ".$this->relpath);
//			throw new Sabre\DAV\Exception\Forbidden ("DAV: User ".\GO::user()->username." doesn't have write permission for folder '".$this->relpath.'"');
//		}
		parent::__construct($path, $overrideName);
	}

	/**
	 *
	 * @return \GO\Files\Model\Folder 
	 */
	private function _getFolder() {
		if (!isset($this->_folder)) {

			$this->_folder = \GO\Files\Model\Folder::model()->findByPath($this->relpath);

			if (!$this->_folder) {
				throw new Sabre\DAV\Exception\NotFound('Folder not found: ' . $this->relpath);
			}
		}
		return $this->_folder;
	}

	/**
	 * Reading a folder, its files and its sub folders takes the read permission.
	 *
	 * Folders can have permissions of their own, so this can't be left to the folders above this one. The listing of
	 * the children hides the folders that can't be read, but they must not be reachable by name either.
	 *
	 * A node is checked once. It can be called from several places: when the node is handed out by its parent, and when
	 * its own children are requested. A directory that the root creates directly was not handed out by a parent.
	 *
	 * @throws Sabre\DAV\Exception\Forbidden
	 * @throws Sabre\DAV\Exception\NotFound
	 */
	private function checkReadPermission(): void
	{
		if ($this->readPermissionChecked) {
			return;
		}

		if (!$this->_getFolder()->checkPermissionLevel(\GO\Base\Model\Acl::READ_PERMISSION)) {
			throw new Sabre\DAV\Exception\Forbidden("DAV: User " . \GO::user()->username . " doesn't have read permission for folder '" . $this->relpath . "'");
		}

		$this->readPermissionChecked = true;
	}

	private bool $readPermissionChecked = false;

	/**
	 * Creates a new file in the directory
	 *
	 * data is a readable stream resource
	 *
	 * @param string $name Name of the file
	 * @param resource $data Initial payload
	 * @return void
	 */
	public function createFile($name, $data = null) {
		
		\GO::debug("FSD::createFile($name)");

		$folder = $this->_getFolder();

		if (!$folder->checkPermissionLevel(\GO\Base\Model\Acl::WRITE_PERMISSION))
			throw new Sabre\DAV\Exception\Forbidden();

		$newFile = new \GO\Base\Fs\File($this->path . '/' . $name);
		if($newFile->exists())
			throw new \Exception("File already exists!");
		

		
		$tmpFile = \GO\Base\Fs\File::tempFile();
		$tmpFile->putContents($data);
		
		if(!\GO\Files\Model\File::checkQuota($tmpFile->size())){
			$tmpFile->delete();
			throw new Sabre\DAV\Exception\InsufficientStorage();
		}
		
//		$newFile->putContents($data);
		
		$tmpFile->move($folder->fsFolder, $name);
		

		$folder->addFile($name);
	}

	/**
	 * Renames the node
	 *
	 * @param string $name The new name
	 * @return void
	 */
	public function setName($name) {

		$folder = $this->_getFolder();

		if (!$folder->checkPermissionLevel(\GO\Base\Model\Acl::WRITE_PERMISSION))
			throw new Sabre\DAV\Exception\Forbidden();
		
		$folder->name = $name;
		$folder->save();

		$this->relpath = $folder->path;
		$this->path = \GO::config()->file_storage_path.$this->relpath;
	}

	public function getServerPath() {
		return $this->path;
	}

	/**
	 * Moves the node
	 *
	 * @param string $name The new name
	 * @return void
	 */
	public function move($newPath) {

		\GO::debug("FSD::move($newPath)");

		if (!is_dir(dirname($newPath)))
			throw new \Exception('Invalid move!');

		$folder = $this->_getFolder();

		if (!$folder->checkPermissionLevel(\GO\Base\Model\Acl::WRITE_PERMISSION))
			throw new Sabre\DAV\Exception\Forbidden();
	
		$destFsFolder = new \GO\Base\Fs\Folder($newPath);		
		
		//\GO::debug("Dest folder: ".$destFsFolder->stripFileStoragePath());
		
		$destFolder = \GO\Files\Model\Folder::model()->findByPath($destFsFolder->parent()->stripFileStoragePath());
		
		$folder->parent_id=$destFolder->id;
		$folder->name = $destFsFolder->name();
		if(!$folder->save()) {
			throw new \Exception("Could not save folder ".$folder->id." ".var_export($folder->getValidationErrors(), true));
		}

		$this->relpath = $folder->path;
		$this->path = \GO::config()->file_storage_path.$this->relpath;
		
	}

	/**
	 * Creates a new subdirectory
	 *
	 * @param string $name
	 * @return void
	 */
	public function createDirectory($name) {

		\GO::debug("FSD:createDirectory($this->relpath.'/'.$name)");
		
		$folder = $this->_getFolder();

		if (!$folder->checkPermissionLevel(\GO\Base\Model\Acl::WRITE_PERMISSION))
			throw new Sabre\DAV\Exception\Forbidden();

		$folder->addFolder($name);
	}

	/**
	 * Returns a specific child node, referenced by its name
	 *
	 * @param string $name
	 * @throws Sabre\DAV\Exception\NotFound
	 * @return Sabre\DAV\INode
	 */
	public function getChild($name) {

		// Sabre calls this with the names from the URL. It doesn't use the listing, which only has what can be read.
		$this->checkReadPermission();

		return $this->createChild($name, true);
	}

	/**
	 * @param string $name
	 * @param bool $checkRead A folder can have permissions of its own. Check them for a name that came from the outside.
	 *  Not needed for the items of the listing: that already only has the folders that can be read and files are
	 *  readable when the folder they are in is.
	 * @return Directory|File
	 * @throws Sabre\DAV\Exception\NotFound
	 */
	private function createChild(string $name, bool $checkRead): Sabre\DAV\INode
	{
		$path = $this->path . '/' .  \go\core\util\StringUtil::normalize($name);

		\GO::debug("FSD:getChild($path)");

		if (is_dir($path)) {
			$directory = new Directory($this->relpath . '/' . $name);

			if ($checkRead) {
				try {
					$directory->checkReadPermission();
				} catch (Sabre\DAV\Exception\Forbidden $e) {
					// same as a folder that doesn't exist, like the listing does
					throw new Sabre\DAV\Exception\NotFound('File with name ' . $path . ' could not be located');
				}
			}

			return $directory;
		} else if (file_exists($path)) {
			return new File($this->relpath . '/' . $name);
		} else {
			throw new Sabre\DAV\Exception\NotFound('File with name ' . $path . ' could not be located');
		}
	}

	/**
	 * Checks is a child-node exists.
	 *
	 * It is generally a good idea to try and override this. Usually it can be optimized.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function childExists($name) {
		
		

		$path = $this->path . '/' . $name;

		\GO::debug("FSD:childExists($path)");

		try {
			// Same rules as getChild(): a node that can't be read doesn't exist for this user.
			$this->getChild($name);

			return true;
		} catch (Sabre\DAV\Exception\NotFound | Sabre\DAV\Exception\Forbidden $e) {

			return false;
		}
	}

	/**
	 * Returns an array with all the child nodes
	 *
	 * @return Sabre\DAV\INode[]
	 */
	public function getChildren() {

		\GO::debug('FSD::getChildren ('.$this->relpath.')');
		$nodes = array();
		//foreach(scandir($this->path) as $node) if($node!='.' && $node!='..') $nodes[] = $this->getChild($node);

		$f = $this->_getFolder();

		if (!$f) {
			throw new Sabre\DAV\Exception\NotFound("Folder not found in database");
		}

		// outside of the try below, which hides errors
		$this->checkReadPermission();

		try {
			$stmt = $f->getSubFolders();

			\GO::debug('Subfolders: '.$stmt->rowCount());

			while ($folder = $stmt->fetch()) {
				// UTF8 must be form D for mac os
				$folder->checkNormalization();
				$nodes[] = $this->createChild($folder->name, false);
			}

			$stmt = $f->files();

			\GO::debug('Files: '.$stmt->rowCount());

			while ($file = $stmt->fetch()) {
				// UTF8 must be form D for mac os
				$file->checkNormalization();
				$nodes[] = $this->createChild($file->name, false);
			}
		}
		catch(\Exception $e) {
			\GO::debug("Failed to list children: " . $e->getMessage());
		}

		return $nodes;
	}

	/**
	 * Deletes all files in this directory, and then itself
	 *
	 * @return void
	 */
	public function delete() {
		
		\GO::debug('FSD::delete('.$this->relpath.')');

		$folder = $this->_getFolder();
		
		if (!$folder->checkPermissionLevel(\GO\Base\Model\Acl::DELETE_PERMISSION))
			throw new Sabre\DAV\Exception\Forbidden();


		$folder->delete();
	}

	/**
	 * Returns available diskspace information
	 *
	 * @return array
	 */
	public function getQuotaInfo() {		
	
		$user = go()->getAuthState()->getUser();
		$free = $user->getStorageFreeSpace();
		
		return array(
				$user->getStorageQuota() - $free,
				$free
		);		
	}

}
