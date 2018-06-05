<?php

namespace GO\Base\Fs;


class MemoryFile extends File{
	
	private $_data;
	
	public function __construct($filename, $data) {
		
		$this->_data = $data;
		
		
		return parent::__construct($filename);
	}
	
	public function getContents() {
		return $this->_data;
	}
	public function contents() {
		return $this->_data;
	}
	
	public function putContents($data, $flags = null, $context = null) {
		if($flags = FILE_APPEND){
			$this->_data .= $data;
		}else
		{
			$this->_data = $data;
		}
	}
	
	public function size() {
		return strlen($this->_data);
	}
	
	public function mtime() {
		return time();
	}
	
	public function ctime() {
		return time();
	}
	
	public function exists() {
		return true;
	}
	
	public function move(Base $destinationFolder, $newFileName = false, $isUploadedFile = false, $appendNumberToNameIfDestinationExists = false) {
		throw Exception("move not implemented for memory file");
	}
	
	public function copy(Folder $destinationFolder, $newFileName = false) {
		throw Exception("copy not implemented for memory file");
	}
	public function parent() {
		return false;
	}
	
	public function child($filename) {
		throw Exception("child not possible for memory file");
	}
	
	public function createChild($filename, $isFile = true) {
		throw Exception("createChild not possible for memory file");
	}
	
	
		/**
	 * Check if the file or folder is writable for the webserver user.
	 * 
	 * @return boolean 
	 */
	public function isWritable(){
		return true;
	}
		
	/**
	 * Change owner
	 * @param StringHelper $user
	 * @return boolean 
	 */
	public function chown($user){
		return false;
	}
	
	/**
	 * Change group
	 * 
	 * @param StringHelper $group
	 * @return boolean 
	 */
	public function chgrp($group){
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
	public function chmod($permissionsMode){
		return false;
	}
	
	/**
	 * Delete the file
	 * 
	 * @return boolean 
	 */
	public function delete(){
		return false;
	}
	
	public function isFolder() {
		return false;
	}
	
	/**
	 * Check if this object is a file.
	 * 
	 * @return boolean 
	 */
	public function isFile(){
		return true;
	}
	
	public function rename($name){
		$this->path=$name;
	}
	
	public function appendNumberToNameIfExists() {
		return $this->path;
	}
	
	public function output() {
		echo $this->_data;
	}
	
	public function setDefaultPermissions() {
		
	}
	
	public function md5Hash(){
		return md5($this->_data);
	}
}
