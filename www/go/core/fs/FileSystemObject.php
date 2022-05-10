<?php

namespace go\core\fs;

use Exception;
use GO\Base\Html\Error;
use go\core\App;
use go\core\ErrorHandler;
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

	/**
	 * @var bool
	 */
	private static $allowRootFolderDelete = false;
	protected $path;


	/**
	 * Disallow these chars for files and folders.
	 */
	const INVALID_CHARS = '/[\/:*\?"<>|\\\]/';

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
	 * Allow deletes in the root folder of the data folder
	 *
	 * @param bool $allow
	 * @return void
	 */
	public static function allowRootFolderDelete(bool $allow = true) {
		self::$allowRootFolderDelete = $allow;
	}


	/**
	 * Check if delete is allowed on give file or folder
	 *
	 * @param FileSystemObject $fso
	 * @return void
	 * @throws Exception
	 */
	public static function checkDeleteAllowed(self $fso) {

		if(!self::$allowRootFolderDelete && $fso->getParent() == go()->getDataFolder()) {
			if(go()->getDebugger()->getRequestId() !== 'phpunit') {
				ErrorHandler::log(go()->getDebugger()->getRequestId() . ' tried to delete in root: ' . $fso->getPath());
				ErrorHandler::logBacktrace();
			}
			throw new Exception(go()->getDebugger()->getRequestId().' tried to delete in root: ' . $fso->getPath());
		}
	}
	/**
	 * Get the parent folder object
	 *
	 * @return Folder|null Parent folder object
	 */
	public function getParent() : ?Folder {

		$parentPath = dirname($this->path);
		if ($parentPath == $this->path) {
			return null;
		}

		return new Folder($parentPath);
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
	public function getModifiedAt(): DateTime
	{
		return new DateTime('@' . filemtime($this->path));
	}

	/**
	 * Return the creation unix timestamp
	 *
	 * @return DateTime
	 * @throws Exception
	 */
	public function getCreatedAt(): DateTime
	{
		return new DateTime('@' . filectime($this->path));
	}

	/**
	 * Filesize in bytes
	 *
	 * @return int Filesize in bytes
	 */
	public function getSize(): int
	{
		return filesize($this->path);
	}

	/**
	 * Get the name of this file or folder
	 *
	 * @return string
	 */
	public function getName() : string {

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
	public function exists(): bool
	{
		return file_exists($this->path);
	}

	/**
	 * Check if the file or folder is writable for the webserver user.
	 *
	 * @return boolean
	 */
	public function isWritable(): bool
	{
		return is_writable($this->path);
	}

	/**
	 * Check if the file or folder is readable for the webserver user.
	 *
	 * @return boolean
	 */
	public function isReadable(): bool
	{
		return is_readable($this->path);
	}

	/**
	 * Change owner
	 * @param string $user
	 * @return boolean
	 */
	public function chown(string $user): bool
	{
		return chown($this->path, $user);
	}

	/**
	 * Change group
	 *
	 * @param string $group
	 * @return boolean
	 */
	public function chgrp(string $group): bool
	{
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
	public function chmod(int $permissionsMode): bool
	{
		return chmod($this->path, $permissionsMode);
	}

	/**
	 * Delete the file
	 *
	 * @return boolean
	 */
	public function delete(): bool
	{
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
	private function checkPathInput(string $path) : bool {
		$path = '/' . str_replace('\\', '/', $path);
		return strpos($path, '/../') === false;
	}

//	/**
//	 * Gets the filename from a path string and works with UTF8 characters
//	 *
//	 * @param string $path
//	 * @return string
//	 */
//	public static function utf8Basename(string $path): string
//	{
//		if (!function_exists('mb_substr')) {
//			return basename($path);
//		}
//		//$path = trim($path);
//		if (substr($path, -1, 1) == '/') {
//			$path = substr($path, 0, -1);
//		}
//		if (empty($path)) {
//			return '';
//		}
//		$pos = mb_strrpos($path, '/');
//		if ($pos === false) {
//			return $path;
//		} else {
//			return mb_substr($path, $pos + 1);
//		}
//	}

	/**
	 * Remove unwanted characters from a string so it can safely be used as a filename.
	 *
	 * @param string $filename
	 * @param string $replace
	 * @return string
	 */
	public static function stripInvalidChars(string $filename,string $replace = '') : string {
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
	public function isLink(): bool
	{
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
	public function isFolder(): bool
	{
		return is_a($this, Folder::class); //works with non existing files
	}

	/**
	 * Check if this object is a file.
	 *
	 * @return boolean
	 */
	public function isFile(): bool
	{
		return is_a($this, File::class); //works with non existing files
	}

	/**
	 * Rename a file or folder
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function rename(string $name): bool
	{
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
	 * Check if the given folder is a parent of this folder.
	 *
	 * @param Folder $parent
	 * @return boolean
	 */
	public function isDescendantOf(Folder $parent): bool
	{
		return strpos($this->getPath(), $parent->getPath() . '/') === 0;
	}
	
	/**
	 * Check if this is in the temp folder
	 * 
	 * @return boolean
	 */
	public function isTemporary(): bool
	{
		return $this->isDescendantOf(App::get()->getTmpFolder());
	}

	/**
	 * Gets the relative path from a given parent folder
	 *
	 * @param Folder $fromFolder
	 * @return string
	 * @throws Exception
	 */
	public function getRelativePath(Folder $fromFolder) :string {
		if (!$this->isDescendantOf($fromFolder)) {
			throw new Exception("The given folder is not an ancestor of this folder or file: " . $fromFolder .' '.$this->path);
		} else {
			return substr($this->getPath(), strlen($fromFolder->getPath()) + 1);
		}
	}
}
