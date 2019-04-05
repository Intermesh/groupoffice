<?php
namespace go\core\fs;

use DateTime;
use Exception;
use go\core\App;
use go\core\fs\FileSystemObject;
use go\core\fs\Folder;
use go\core\util\StringUtil;


/**
 * A file object
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class File extends FileSystemObject {
	
	/**
	 * Get a temporary file
	 * 
	 * @param string $extension
	 * @return statuc
	 */
	public static function tempFile($extension) {
		 return GO()->getTmpFolder()->getFile(uniqid(time()) . '.' . $extension);
	}

	
	/**
	 * Get the parent folder object
	 *
	 * @return Folder Parent folder object
	 */
	public function getFolder() {
		$parentPath = dirname($this->path);		
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
			return $this->getFolder()->isWritable();
		}
	}
	
//	/**
//	 * Get the size formatted nicely like 1.5 MB
//	 *
//	 * @param int $decimals
//	 * @return string
//	 */
//	public function getHumanSize($decimals = 1) {
//		$size = $this->getSize();
//		if ($size == 0) {
//			return 0;
//		}
//
//		switch ($size) {
//			case ($size > 1073741824) :
//				$size = \go\core\util\Number::localize($size / 1073741824, $decimals);
//				$size .= " GB";
//				break;
//
//			case ($size > 1048576) :
//				$size = \go\core\util\Number::localize($size / 1048576, $decimals);
//				$size .= " MB";
//				break;
//
//			case ($size > 1024) :
//				$size = \go\core\util\Number::localize($size / 1024, $decimals);
//				$size .= " KB";
//				break;
//
//			default :
//				$size = \go\core\util\Number::localize($size, $decimals);
//				$size .= " bytes";
//				break;
//		}
//		return $size;
//	}

	/**
	 * Delete the file
	 *
	 * @return boolean
	 */
	public function delete() {
		if (!file_exists($this->path)) {
			return true;
		}else{		
			return unlink($this->path);
		}
	}


	/**
	 * Get the extension of a filename
	 *
	 * @param string $filename
	 * @param string
	 */
	public function getExtension() {
		
		$filename = $this->getName();
		
		$extension = '';

		$pos = strrpos($filename, '.');
		if ($pos) {
			$extension = substr($filename, $pos + 1);
		}
		//return trim(strtolower($extension)); // Does not work when extension on disk is in capital letters (.PDF, .XLSX)
		return trim($extension);
	}

	/**
	 * Get the file name with out extension
	 * @param string
	 */
	public function getNameWithoutExtension() {
		$filename = $this->getName();
		$pos = strrpos($filename, '.');
		
		if ($pos) {
			$filename = substr($filename, 0, $pos);
		}
		
		return $filename;
	}

	/**
	 * Checks if a filename exists and renames it.
	 *
	 * @param	StringUtil $filepath The complete path to the file
	 * @access public
	 * @param string  New filepath
	 */
	public function appendNumberToNameIfExists() {
		$dir = $this->getFolder()->getPath();
		$origName = $this->getNameWithoutExtension();
		$extension = $this->getExtension();
		$x = 1;
		while ($this->exists()) {
			$this->path = $dir . '/' . $origName . ' (' . $x . ').' . $extension;
			$x++;
		}
		return $this->path;
	}

	/**
	 * Put data in the file. (See php function file_put_contents())
	 *
	 * @param string $data
	 * @param type $flags
	 * @param type $context
	 * @return boolean
	 */
	public function putContents($data, $flags = null, $context = null) {
		
		$this->create();
		
		if (file_put_contents($this->path, $data, $flags, $context)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get the contents of this file.
	 *
	 * @param string
	 */
	public function getContents() {
		
		return file_get_contents($this->getPath());
	}

	public function getMimeType() {
		return mime_content_type($this->getPath());
	}

	/**
	 * Returns the mime type for the file.
	 * If it can't detect it it will return application/octet-stream
	 *
	 * @param string
	 */
	public function getContentType() {
		
		//sometimes these fail and are important to always be right
		switch($this->getExtension()) {
			case 'css':
				return 'text/css';
			case 'js':
				return 'application/javascript';				
			default:
				return mime_content_type($this->getPath());
		}		
		
	}

	/**
	 * Send download headers and output the contents of this file to standard out (browser).
	 * @param boolean $sendHeaders
	 * @param boolean $useCache
	 */
	public function output($sendHeaders = true, $useCache = true) {		
		$r = \go\core\http\Response::get();
	
		if($sendHeaders) {
			$r->setHeader('Content-Type', $this->getContentType());
			$r->setHeader('Content-Disposition', 'inline; filename="' . $this->getName() . '"');
			$r->setHeader('Content-Transfer-Encoding', 'binary');

			if ($useCache) {			
				$r->setModifiedAt(new DateTime('@'.$this->getModifiedAt()));
				$r->setETag($this->getMd5Hash());
				$r->abortIfCached();
			}				
		}		
		
		if(ob_get_contents() != '') {			
			throw new \Exception("Could not output file because output has already been sent. Turn off output buffering to find out where output has been started.");
		}

		$handle = fopen($this->getPath(), "rb");

		if (!is_resource($handle)) {
			throw new Exception("Could not read file");
		}
		
		$r->sendHeaders();

		while (!feof($handle)) {
			echo fread($handle, 1024);
			flush();
		}
	}
	
	/**
	 * Open file pointer
	 * 
	 * See php fopen function
	 * 
	 * @param string $mode
	 * @return resource
	 */
	public function open($mode){
		
		//$this->create();
		
		return fopen($this->getPath(), $mode);
	}

	/**
	 * Move a file to new location
	 *
	 * @param File $destination The file may not exist yet.
	 * @return boolean
	 */
	public function move(File $destination) {

		if ($destination->exists()) {
			throw new Exception("File exists in move!");
		}

		if (rename($this->path, $destination->getPath())) {
			$this->path = $destination->getPath();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Create a hard link
	 * 
	 * @link http://php.net/manual/en/function.link.php
	 * 
	 * @param File $targetLink The link name.
	 * 
	 * @return File|bool <b>File</b> on success or <b>FALSE</b> on failure.
	 */
	public function createLink(File $targetLink) {
		return link($this->getPath(), $targetLink->getPath());
	}

	/**
	 * Copy a file to another folder.
	 *
	 * @param File $destinationFile
	 * @return File
	 */
	public function copy(File $destinationFile) {
		
		if($destinationFile->exists()) {
			throw new \Exception("The destination '".$destinationFile->getPath()."' already exists!");
		}
	
		if (!copy($this->path, $destinationFile->getPath())) {
			return false;
		}else{
			return $destinationFile;
		}
	}

	/**
	 * Convert and clean the file to ensure it has valid UTF-8 data.
	 *
	 * @return boolean
	 */
	public function convertToUtf8() {

		if (!$this->isWritable()){
			return false;
		}

		$str = $this->getContents();
		if (!$str) {
			return false;
		}

		$enc = mb_detect_encoding($this->getContents(), "ASCII,JIS,UTF-8,ISO-8859-1,ISO-8859-15,EUC-JP,SJIS");
		if (!$enc) {
			$enc = 'UTF-8';
		}

		$bom = pack("CCC", 0xef, 0xbb, 0xbf);
		if (0 == strncmp($str, $bom, 3)) {
			//echo "BOM detected - file is UTF-8\n";
			$str = substr($str, 3);
		}

		return $this->putContents(StringUtil::cleanUtf8($str, $enc));
	}

	/**
	 * Get the md5 hash from this file
	 *
	 * @param string
	 */
	public function getMd5Hash() {
		return md5_file($this->path);
	}

	/**
	 * Pull 40-char sha1 hex from the binary data
	 *
	 * @param string
	 */
	public function getSha1Hash() {
		return sha1_file($this->path);
	}

	/**
	 * Compare this file with an other file.
	 *
	 * @param File $file
	 * @return bool True if the file is different, false if file is the same.
	 */
	public function equals(File $file) {
		if ($this->md5Hash() != $file->md5Hash()){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * Create the file
	 *
	 * @param boolean $createPath Create the folders for this file also?
	 * @return self|bool $successfull
	 */
	public function touch($createPath = false) {
		if ($createPath){
			$this->getFolder()->create();
		}

		if (touch($this->getPath())) {
			return $this;
		} else {
			return false;
		}
	}
	
	private function create() {
		if(!$this->exists()) {
			$this->touch(true);
		}
	}
}
