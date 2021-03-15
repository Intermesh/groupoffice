<?php
namespace go\core\fs;

use Exception;
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
	 * Check if the file or folder exists
	 * @return boolean
	 */
	public function exists() {
		return is_file($this->path);
	}


	/**
	 * Get a temporary file
	 * 
	 * @param string $extension
	 * @return static
	 */
	public static function tempFile($extension) {
		 return go()->getTmpFolder()->getFile(uniqid(time()) . '.' . $extension);
	}


  /**
   * Get the parent folder object
   *
   * @return Folder Parent folder object
   * @throws Exception
   */
	public function getFolder() {
		$parentPath = dirname($this->path);		
		return new Folder($parentPath);
	}


  /**
   * Check if the file or folder is writable for the webserver user.
   *
   * @return bool
   * @throws Exception
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
	 * @return bool
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
   * @return string
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
	 * @return string
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
   * @return string  New filepath
   * @throws Exception
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
   * Write a string to a file
   * @link https://php.net/manual/en/function.file-put-contents.php
   * @param mixed $data <p>
   * The data to write. Can be either a string, an
   * array or a stream resource.
   * </p>
   * <p>
   * If data is a stream resource, the
   * remaining buffer of that stream will be copied to the specified file.
   * This is similar with using stream_copy_to_stream.
   * </p>
   * <p>
   * You can also specify the data parameter as a single
   * dimension array. This is equivalent to
   * file_put_contents($filename, implode('', $array)).
   * </p>
   * @param int $flags [optional] <p>
   * The value of flags can be any combination of
   * the following flags (with some restrictions), joined with the binary OR
   * (|) operator.
   * </p>
   * <p>
   * <table>
   * Available flags
   * <tr valign="top">
   * <td>Flag</td>
   * <td>Description</td>
   * </tr>
   * <tr valign="top">
   * <td>
   * FILE_USE_INCLUDE_PATH
   * </td>
   * <td>
   * Search for filename in the include directory.
   * See include_path for more
   * information.
   * </td>
   * </tr>
   * <tr valign="top">
   * <td>
   * FILE_APPEND
   * </td>
   * <td>
   * If file filename already exists, append
   * the data to the file instead of overwriting it. Mutually
   * exclusive with LOCK_EX since appends are atomic and thus there
   * is no reason to lock.
   * </td>
   * </tr>
   * <tr valign="top">
   * <td>
   * LOCK_EX
   * </td>
   * <td>
   * Acquire an exclusive lock on the file while proceeding to the
   * writing. Mutually exclusive with FILE_APPEND.
   * @since 5.1
   * </td>
   * </tr>
   * </table>
   * </p>
   * @param resource $context [optional] <p>
   * A valid context resource created with
   * stream_context_create.
   * </p>
   * @return int|false The function returns the number of bytes that were written to the file, or
   * false on failure.
   * @since 5.0
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
   * Reads entire file into a string
   * @link https://php.net/manual/en/function.file-get-contents.php
   *
   * @param int $offset [optional] <p>
   * The offset where the reading starts.
   * </p>
   * @param int $maxlen [optional] <p>
   * Maximum length of data read. The default is to read until end
   * of file is reached.
   * </p>
   * @return string|false The function returns the read data or false on failure.
   * @since 4.3
   * @since 5.0
   */
	public function getContents($offset = 0, $maxlen = null) {		
		if(isset($maxlen)) {
			return file_get_contents($this->getPath(), false, null, $offset, $maxlen);	
		} else{
			return file_get_contents($this->getPath(), false, null, $offset);
		}
	}

	/**
	 * Alias for getContentType()
	 * 
	 * @see getContentType()
	 */
	public function getMimeType() {
		return $this->getContentType();
	}
	
	/**
	 * Find and replace text in a text file
	 * 
	 * @param string $search
	 * @param string $replace
	 * @return bool
	 */
	public function replace($search, $replace) {
		$contents = $this->getContents();
		$replaced = str_replace($search, $replace, $contents);
		if($replaced === $contents) {
			return true;
		}
		return $this->putContents($replaced);		
	}

	/**
	 * Returns the mime type for the file.
	 * If it can't detect it it will return application/octet-stream
	 *
	 * @return string
	 */
	public function getContentType() {
		
		//sometimes these fail and are important to always be right
		switch($this->getExtension()) {
			case 'css': 	return 'text/css';
			case 'js': 		return 'application/javascript';
			case 'json': 	return 'application/json';
			case 'csv': 	return 'text/csv';
			//case 'vcf':		return 'text/vcard';
			//case 'ics':		return 'text/calendar';
			case 'eml':		return 'message/rfc822';
			default: 		return mime_content_type($this->getPath());
		}		
		
	}

  /**
   * Send download headers and output the contents of this file to standard out (browser).
   * @param boolean $sendHeaders
   * @param boolean $useCache
   * @param array $headers key value array of http headers to send
   * @throws Exception
   */
	public function output($sendHeaders = true, $useCache = true, array $headers = [], $inline = false) {		
		$r = \go\core\http\Response::get();
	
		if($sendHeaders) {
			foreach($headers as $name => $value) {
				$r->setHeader($name, $value);
			}

			if(!$r->hasHeader('Content-Type')) {
				$r->setHeader('Content-Type', $this->getContentType());
			}

			if(!$r->hasHeader('Content-Disposition')) {
				$disp = $inline ? 'inline' : 'attachment';
				$r->setHeader('Content-Disposition', $disp . '; filename="' . $this->getName() . '"');
			}
			if(!$r->hasHeader('Content-Transfer-Encoding')) {
				$r->setHeader('Content-Transfer-Encoding', 'binary');
			}

			if ($useCache) {
				if(!$r->hasHeader('Cache-Control')) {
					$r->setHeader('Cache-Control', 'PRIVATE');
				}
				$r->removeHeader('Pragma');

				if(!$r->hasHeader('Last-Modified')) {
					$r->setModifiedAt($this->getModifiedAt());
				}
				if(!$r->hasHeader('Etag')) {
					$r->setETag($this->getMd5Hash());
				}
				$r->abortIfCached();
			}
		}		
		
		if(ob_get_contents() != '') {			
			throw new Exception("Could not output file because output has already been sent. Turn off output buffering to find out where output has been started.");
		}

		while (ob_get_level()) ob_end_clean();

		ini_set('zlib.output_compression', 0);

		$r->sendHeaders();

		$handle = $this->open('rb');

		if (!is_resource($handle))
			throw new \Exception("Could not read file");

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
   * @throws Exception
   */
	public function move(File $destination) {

		if ($destination->exists()) {
			throw new Exception("File exists in move!");
		}

		if($destination->getPath() == $this->getPath()) {
			return true;
		}
;
		try {
			$success = rename($this->path, $destination->getPath());
		} catch(Exception $e) {
			//renaming across partitions doesn't work
			$success = $destination->exists() || ($this->copy($destination) != false);
			if($success) {
				$this->delete();
			}
		}

		if ($success) {
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
   * @param self $destinationFile
   * @return self|bool
   * @throws Exception
   */
	public function copy(File $destinationFile) {
		
		if($destinationFile->exists()) {
			throw new Exception("The destination '".$destinationFile->getPath()."' already exists!");
		}
		
		$destinationFile->getFolder()->create();
	
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
   * @throws Exception
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
   * @return false|string
   */
	public function getMd5Hash() {
		return md5_file($this->path);
	}

	/**
	 * Pull 40-char sha1 hex from the binary data
	 *
	 * @return string
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
	 * @param int $time The touch time. If time is not supplied, the current system time is used.
	 * @param int $atime If present, the access time of the given filename is set to the value of atime. Otherwise, it is set to the value passed to the time parameter. If neither are present, the current system time is used.
	 * @return self|bool $successful
	 * @throws Exception
	 */
	public function touch($createPath = false, $time = null, $atime = null) {
		if ($createPath){
			$this->getFolder()->create();
		}

		if(isset($time)) {
			if(isset($atime)) {
				$success = touch($this->getPath(), $time, $atime);
			} else{
				$success = touch($this->getPath(), $time);
			}
		}else{
			$success = touch($this->getPath());
		}

		if ($success) {
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
