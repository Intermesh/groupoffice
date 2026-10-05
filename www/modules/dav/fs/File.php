<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: FS_File.class.inc.php 7942 2011-08-22 14:25:46Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

namespace GO\Dav\Fs;

use GO\Base\Model\Acl;
use Sabre;
use Sabre\DAV\Exception\Forbidden;

class File extends \Sabre\DAV\FS\File {

	protected $folder;
	protected $write_permission;
	protected $relpath;

	public function __construct($path) {

		$this->relpath = \go\core\util\StringUtil::normalize($path);
		$path = \GO::config()->file_storage_path . $this->relpath ;

		parent::__construct($path);
	}

	/**
	 * @param bool $delete : toggle between delete or write mode
	 * @throws Forbidden
	 */
	public function checkWritePermission(?bool $delete = false): void
	{
		$fsFile = new \GO\Base\Fs\File($this->path);

		$this->folder = \GO\Files\Model\Folder::model()->findByPath($fsFile->parent()->stripFileStoragePath());
		$requiredPermissionLevel = $delete ? Acl::DELETE_PERMISSION : Acl::READ_PERMISSION;
		$permissionString = $delete ? "delete" : "write";
		if (!Acl::hasPermission($this->folder->getPermissionLevel(), $requiredPermissionLevel)) {
			throw new Sabre\DAV\Exception\Forbidden("DAV: User " . \GO::user()->username . " doesn't have " .
				$permissionString . " permission for file '" . $this->relpath . '"');
		}
	}

	/**
	 * Updates the data
	 *
	 * @param resource $data
	 * @return void
	 */
	public function put($data) {
		
		\GO::debug("DAVFile:put( ".$this->relpath.")");
		$this->checkWritePermission();
		
		$file = $this->getFile();
		$file->putContents($data);
	}

    public function lock($lock_id = "") {

			\GO::debug("DAVFile:lock( ".$lock_id.")");
			$file = $this->getFile();
			$file->lock($lock_id);
			$file->save(true);
    }

    public function unlock()
    {
		\GO::debug("DAVFile:unlock( )");
        $file = $this->getFile();
        $file->locked_user_id =0;
				$file->lock_id = "";
        $file->save(true);
    }

	/**
	 * Renames the node
	 *
	 * @param string $name The new name
	 * @return void
	 * @throws Forbidden
	 */
	public function setName($name): void
	{
		
		\GO::debug("DAVFile::setName($name)");
		$this->checkWritePermission();

		parent::setName($name);
		
		$file = $this->getFile();
		$file->name=$name;
		$file->save();
		
		$this->relpath = $file->path;
		$this->path = \GO::config()->file_storage_path.$this->relpath;
	}

	public function getServerPath() {
		return $this->path;
	}

	/**
	 * Move sthe node
	 *
	 * @return void
	 */
	public function move($newPath): void
	{
		$this->checkWritePermission();

		\GO::debug('DAVFile::move(' . $this->path . ' -> ' . $newPath . ')');
		
		$destFsFolder = new \GO\Base\Fs\Folder(dirname($newPath));		
		$destFolder = \GO\Files\Model\Folder::model()->findByPath($destFsFolder->stripFileStoragePath());
		
		$file = $this->getFile();
		$file->folder_id=$destFolder->id;
		$file->name = \GO\Base\Fs\File::utf8Basename($newPath);
		$file->save();
		
		$this->relpath = $file->path;
		$this->path = \GO::config()->file_storage_path.$this->relpath;
	}
	
	private $file;
	public function getFile(): bool|\GO\Files\Model\File
	{
		if(!isset($this->file)) {
			$this->file = \GO\Files\Model\File::model()->findByPath($this->relpath);
		}
		
		return $this->file;
}

	/**
	 * Returns the data
	 *
	 * @return string
	 */
	public function get() {
		$file = $this->getFile();
		$file->open();
		return fopen($this->path, 'r');
	}

	/**
	 * Delete the current file
	 *
	 *
	 *
	 *
	 *
	 *
	 *
	 *
	 *
	 *
	 *
	 *
	 *
	 *
	 *
	 *
	 *
	 *
	 *
	 *
	 *
	 *
	 *
	 *
	 *
	 *
	 *
	 *
	 * @return void
	 */
	public function delete(): void
	{
		$this->checkWritePermission(true);
		$file = $this->getFile();
		if ($file) {
			$file->delete();
		}
	}

	/**
	 * Returns the size of the node, in bytes
	 *
	 * @return int
	 */
	public function getSize() {

		return filesize($this->path);
	}

	/**
	 * Returns the mime-type for a file
	 *
	 * If null is returned, we'll assume application/octet-stream
	 *
	 * @return mixed
	 */
	public function getContentType() {
		
		$fsFile = new \GO\Base\Fs\File($this->path);

		return $fsFile->mimeType();	

	}

}

