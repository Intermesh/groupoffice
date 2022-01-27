<?php
namespace go\core\fs;

use go\core\util\DateTime;
use Exception;

/**
 * A file which only exists in memory. Just a variable with data.
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class MemoryFile extends File {
	
	private $data;
	
	public function __construct($filename, $data) {
		
		$this->data = $data;
		
		
		parent::__construct($filename);
	}
	
	public function getContents(int $offset = 0, int $maxlen = null) {
		return $this->data;
	}
	public function contents() {
		return $this->data;
	}
	
	public function putContents($data, int $flags = null, $context = null) {
		if($flags === FILE_APPEND){
			$this->data .= $data;
		}else
		{
			$this->data = $data;
		}
	}
	
	public function getSize(): int
	{
		return strlen($this->data);
	}
	
	public function getModifiedAt(): DateTime
	{
		return new DateTime();
	}
	
	public function getCreatedAt(): DateTime
	{
		return new DateTime();
	}
	
	public function exists(): bool
	{
		return true;
	}
	
	public function move(File $destination) :bool {
		throw new Exception("move not implemented for memory file");
	}
	
	public function copy(File $destinationFile) {
		throw new Exception("copy not implemented for memory file");
	}

		/**
	 * Check if the file or folder is writable for the webserver user.
	 * 
	 * @return boolean 
	 */
	public function isWritable(): bool
	{
		return true;
	}
		
	/**
	 * Change owner
	 * @param string $user
	 * @return boolean 
	 */
	public function chown(string $user): bool
	{
		return false;
	}
	
	/**
	 * Change group
	 * 
	 * @param string $group
	 * @return boolean 
	 */
	public function chgrp(string $group): bool
	{
		return false;
	}
	
	/**
	 *
	 * @param int $permissionsMode <p>
	 * Note that mode is not automatically
	 * assumed to be an octal value, so strings (such as "g+w") will
	 * not work properly. To ensure the expected operation,
	 * you need to prefix mode with a zero (0):
	 * </p>
	 * 
	 * @return boolean 
	 */
	public function chmod(int $permissionsMode): bool
	{
		return false;
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
	
	public function isFolder(): bool
	{
		return false;
	}
	
	/**
	 * Check if this object is a file.
	 * 
	 * @return boolean 
	 */
	public function isFile(): bool
	{
		return true;
	}
	
	public function setName(string $name) {
		$this->path = $name;
	}
	
	public function appendNumberToNameIfExists():string {
		return $this->path;
	}
	
	public function output(bool $sendHeaders = true, bool $useCache = true, array $headers = [], bool $inline = true) {
		echo $this->data;
	}
	
	
	public function getMd5Hash(){
		return md5($this->data);
	}
}
