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
use Sabre;

class File extends \Sabre\DAV\FS\File {

	protected $folder;
	protected $write_permission;
	protected $relpath;

	public function __construct($path) {

		$this->relpath = \go\core\util\StringUtil::normalize($path);
		$path = \GO::config()->file_storage_path . $this->relpath ;

		parent::__construct($path);
	}

	public function checkWritePermission($delete=false) {

		$fsFile = new \GO\Base\Fs\File($this->path);

		$this->folder = \GO\Files\Model\Folder::model()->findByPath($fsFile->parent()->stripFileStoragePath());
		if (!\GO\Base\Model\Acl::hasPermission($this->folder->getPermissionLevel(), \GO\Base\Model\Acl::WRITE_PERMISSION)){
			throw new Sabre\DAV\Exception\Forbidden("DAV: User ".\GO::user()->username." doesn't have write permission for file '".$this->relpath.'"');
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
		
//		$file = \GO\Files\Model\File::model()->findByPath($this->relpath);
//		$file->saveVersion();
//		$file->putContents($data);
		

		$file = \GO\Files\Model\File::model()->findByPath($this->relpath);
		$file->putContents($data);

//		file_put_contents($this->path, $data);
//		\GO\Files\Model\File::model()->findByPath($this->relpath);

		//\GO::debug('ADDED FILE WITH WEBDAV -> FILE_ID: ' . $file_id);
	}

	/**
	 * Renames the node
	 *
	 * @param StringHelper $name The new name
	 * @return void
	 */
	public function setName($name) {
		
		\GO::debug("DAVFile::setName($name)");
		$this->checkWritePermission();

		parent::setName($name);
		
		$file = \GO\Files\Model\File::model()->findByPath($this->relpath);
		$file->name=$name;
		$file->save();
		
		$this->relpath = $file->path;
		$this->path = \GO::config()->file_storage_path.$this->relpath;
	}

	public function getServerPath() {
		return $this->path;
	}

	/**
	 * Movesthe node
	 *
	 * @param StringHelper $name The new name
	 * @return void
	 */
	public function move($newPath) {
		$this->checkWritePermission();

		\GO::debug('DAVFile::move(' . $this->path . ' -> ' . $newPath . ')');
		
		$destFsFolder = new \GO\Base\Fs\Folder(dirname($newPath));		
		$destFolder = \GO\Files\Model\Folder::model()->findByPath($destFsFolder->stripFileStoragePath());
		
		$file = \GO\Files\Model\File::model()->findByPath($this->relpath);
		$file->folder_id=$destFolder->id;
		$file->name = \GO\Base\Fs\File::utf8Basename($newPath);
		$file->save();
		
		$this->relpath = $file->path;
		$this->path = \GO::config()->file_storage_path.$this->relpath;
	}

	/**
	 * Returns the data
	 *
	 * @return StringHelper
	 */
	public function get() {
		$file = \GO\Files\Model\File::model()->findByPath($this->relpath);
		$file->open();
		return fopen($this->path, 'r');
	}

	/**
	 * Delete the current file
	 *
	 * @return void
	 */
	public function delete() {
		$this->checkWritePermission(true);
		$file = \GO\Files\Model\File::model()->findByPath($this->relpath);
		if($file) {
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

