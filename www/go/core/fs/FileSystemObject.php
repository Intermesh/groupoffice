<?php

namespace go\core\fs;

use Exception;
use go\core\App;
use go\core\util\DateTime;
use InvalidArgumentException;

/**
 * Base class for files and folders on the filesystem
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
abstract class FileSystemObject {

	protected $path;


	/**
	 * Disallow these chars for files and folders.
	 */
	const INVALID_CHARS = '/[\/:\*\?"<>|\\\]/';

	/**
	 * Constructor of a file or folder
	 *
	 * @param string $path The absolute path must be suplied
	 * @throws InvalidArgumentException
	 */
	public function __construct(string $path) {

		$path = rtrim($path, '/');

		if (empty($path)) {
			throw new InvalidArgumentException("Path may not be empty in Base");
		}

		if (!$this->checkPathInput($path)) {
			throw new InvalidArgumentException("The supplied path '$path' was invalid");
		}

		$this->path = $path;
	}


	/**
	 * Return absolute filesystem path
	 *
	 * @return string
	 */
	public function getPath() : string{
		return $this->path;
	}

	/**
	 * Return the modification unix timestamp
	 *
	 * @return DateTime
	 * @throws Exception
	 */
	public function getModifiedAt() {
		return new DateTime('@' . filemtime($this->path));
	}

	/**
	 * Return the creation unix timestamp
	 *
	 * @return DateTime 
	 */
	public function getCreatedAt() {
		return new DateTime('@' . filectime($this->path));
	}

	/**
	 * Filesize in bytes
	 *
	 * @return int Filesize in bytes
	 */
	public function getSize() {
		return filesize($this->path);
	}

	/**
	 * Get the name of this file or folder
	 *
	 * @param string
	 */
	public function getName() {

		if (!function_exists('mb_substr')) {
			return basename($this->path);
		}

		if (empty($this->path)) {
			return '';
		}
		$pos = mb_strrpos($this->path, '/');
		if ($pos === false) {
			return $this->path;
		} else {
			return mb_substr($this->path, $pos + 1);
		}
	}

	/**
	 * Check if the file or folder exists
	 * @return boolean
	 */
	public function exists() {
		return file_exists($this->path);
	}

	/**
	 * Check if the file or folder is writable for the webserver user.
	 *
	 * @return boolean
	 */
	public function isWritable() {
		return is_writable($this->path);
	}

	/**
	 * Check if the file or folder is readable for the webserver user.
	 *
	 * @return boolean
	 */
	public function isReadable() {
		return is_readable($this->path);
	}

	/**
	 * Change owner
	 * @param string $user
	 * @return boolean
	 */
	public function chown($user) {
		return chown($this->path, $user);
	}

	/**
	 * Change group
	 *
	 * @param string $group
	 * @return boolean
	 */
	public function chgrp($group) {
		return chgrp($this->path, $group);
	}

	/**
	 * Change permissions
	 * 
	 * You should use umask() to control default permissions
	 * 
	 * @param int $permissionsMode 
	 * Note that mode is not automatically
	 * assumed to be an octal value, so strings (such as "g+w") will
	 * not work properly. To ensure the expected operation,
	 * you need to prefix mode with a zero (0):
	 * 
	 *
	 * @return boolean
	 */
	public function chmod($permissionsMode) {
		return chmod($this->path, $permissionsMode);
	}

	/**
	 * Delete the file
	 *
	 * @return boolean
	 */
	public function delete() {
		return false;
	}

	public function __toString() {
		return $this->path;
	}

	/**
	 * Checks if a path send as a request parameter is valid.
	 *
	 * @param string $path
	 * @return boolean
	 */
	private function checkPathInput($path) {
		$path = '/' . str_replace('\\', '/', $path);
		return strpos($path, '/../') === false;
	}

	/**
	 * Get's the filename from a path string and works with UTF8 characters
	 *
	 * @param string $path
	 * @param string
	 */
	public static function utf8Basename($path) {
		if (!function_exists('mb_substr')) {
			return basename($path);
		}
		//$path = trim($path);
		if (substr($path, -1, 1) == '/') {
			$path = substr($path, 0, -1);
		}
		if (empty($path)) {
			return '';
		}
		$pos = mb_strrpos($path, '/');
		if ($pos === false) {
			return $path;
		} else {
			return mb_substr($path, $pos + 1);
		}
	}

	/**
	 * Remove unwanted characters from a string so it can safely be used as a filename.
	 *
	 * @param string $filename
	 * @param string
	 */
	public static function stripInvalidChars($filename, $replace = '') {
		$filename = trim(preg_replace(self::INVALID_CHARS, $replace, $filename));

		//IE likes to change a double white space to a single space
		//We must do this ourselves so the filenames will match.
		$filename = preg_replace('/\s+/', ' ', $filename);

		//strip dots from start
		$filename = ltrim($filename, '.');

		if (empty($filename)) {
			$filename = 'unnamed';
		}


		if (strlen($filename) > 255) {
			$filename = trim(substr($filename, 0, 255));
		}

		return $filename;
	}

	/**
	 * Check if this folder is a symbolic link
	 *
	 * @return bool Returns the canonicalized absolute pathname on success. The resulting path will have no symbolic link, /./ or /../ components. Trailing delimiters, such as \ and /, are also removed.
	 *  realpath() returns false on failure, e.g. if the file does not exist.
	 */
	public function isLink() {
		return is_link($this->path);
	}

	/**
	 * expands all symbolic links and resolves references to /./, /../ and extra / characters in the input path and returns the canonicalized absolute pathname.
	 *
	 * @return string|bool false on
	 */
	public function getLinkTarget() {
		return realpath($this->path);
	}


	/**
	 * Check if this object is a folder.
	 *
	 * @return boolean
	 */
	public function isFolder() {
//		return is_dir($this->path);
		return is_a($this, Folder::class); //works with non existing files
	}

	/**
	 * Check if this object is a file.
	 *
	 * @return boolean
	 */
	public function isFile() {
//		return is_file($this->path);
		return is_a($this, File::class); //works with non existing files
	}

	/**
	 * Rename a file or folder
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function rename($name) {		
				
		$oldPath = $this->path;
		$newPath = dirname($this->path) . '/' . $name;

		if (!$this->exists() || rename($oldPath, $newPath)) {
			$this->path = $newPath;
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check if the given folder is a subfolder of this folder.
	 *
	 * @param Folder $parent
	 * @return boolean
	 */
	public function isDescendantOf(Folder $parent) {
		return strpos($this->getPath(), $parent->getPath() . '/') === 0;
	}
	
	/**
	 * Check if this is in the temp folder
	 * 
	 * @return boolean
	 */
	public function isTemporary() {
		return $this->isDescendantOf(App::get()->getConfig()->getTempFolder());
	}

	/**
	 * Get's the relative path from a given parent folder
	 * 
	 * @param Folder $fromFolder
	 * @param string|boolean
	 */
	public function getRelativePath(Folder $fromFolder) {
		if (!$this->isDescendantOf($fromFolder)) {
			throw new Exception("The given folder is not an ancestor of this folder or file: " . $fromFolder .' '.$this->path);
		} else {
			return substr($this->getPath(), strlen($fromFolder->getPath()) + 1);
		}
	}
}
