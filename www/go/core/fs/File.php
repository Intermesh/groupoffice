<?php
namespace go\core\fs;

use Exception;
use go\core\ErrorHandler;
use go\core\http\Response;
use go\core\util\StringUtil;
use Throwable;


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
	public function exists(): bool
	{
		return is_file($this->path);
	}


	/**
	 * Get a temporary file
	 * 
	 * @param string $extension
	 * @return static
	 */
	public static function tempFile(string $extension): File
	{
		 return go()->getTmpFolder()->getFile(uniqid(time()) . '.' . $extension);
	}


  /**
   * Get the parent folder object
   *
   * @return ?Folder Parent folder object
   */
	public function getFolder(): ?Folder
	{
		return $this->getParent();
	}


  /**
   * Check if the file or folder is writable for the webserver user.
   *
   * @return bool
   * @throws Exception
   */
	public function isWritable(): bool
	{
		try {
			if ($this->exists()) {
				return is_writable($this->path);
			} else {
				return $this->getFolder()->isWritable();
			}
		} catch(Throwable $e) {
			// open basedir restriction may lead here
			return false;
		}
	}

	/**
	 * Delete the file
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function delete(): bool
	{
		self::checkDeleteAllowed($this);

		if (!file_exists($this->path)) {
			return true;
		}else{		
			return unlink($this->path);
		}
	}

	/**
	 * Get the extension of a filename
	 *
	 * @return string
	 */
	public function getExtension(): string
	{
		
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
	public function getNameWithoutExtension(): string
	{
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
   * @return self
   */
	public function appendNumberToNameIfExists(): self
	{
		$dir = $this->getFolder()->getPath();
		$origName = $this->getNameWithoutExtension();
		$extension = $this->getExtension();
		$x = 1;
		while ($this->exists()) {
			$this->path = $dir . '/' . $origName . ' (' . $x . ').' . $extension;
			$x++;
		}
		return $this;
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
	 * @param int|null $flags [optional] <p>
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
	 * @throws Exception
	 */
	public function putContents($data, int $flags = 0, $context = null) {
		
		$this->create();
		
		return file_put_contents($this->path, $data, $flags, $context);
	}

  /**
   * Reads entire file into a string
   * @link https://php.net/manual/en/function.file-get-contents.php
   *
   * @param int $offset [optional] <p>
   * The offset where the reading starts.
   * </p>
   * @param int|null $maxlen [optional] <p>
   * Maximum length of data read. The default is to read until end
   * of file is reached.
   * </p>
   * @return string|false The function returns the read data or false on failure.
   * @since 4.3
   * @since 5.0
   */
	public function getContents(int $offset = 0, int $maxlen = null) {
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
	public function getMimeType(): string
	{
		return $this->getContentType();
	}

	/**
	 * Find and replace text in a text file
	 *
	 * @param string $search
	 * @param string $replace
	 * @return string|false The function returns the replaced data or false on failure.
	 * @throws Exception
	 */
	public function replace(string $search, string $replace) {
		$contents = $this->getContents();
		$replaced = str_replace($search, $replace, $contents);
		if($replaced === $contents) {
			return $replaced;
		}
		if($this->putContents($replaced)) {
			return $replaced;
		}
		return false;
	}

	/**
	 * Returns the mime type for the file.
	 * If it can't detect it it will return application/octet-stream
	 *
	 * @return string
	 */
	public function getContentType(): string
	{
		
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
	 * @param bool $inline
	 * @throws Exception
	 */
	public function output(bool $sendHeaders = true, bool $useCache = true, array $headers = [], bool $inline = false) {
		$r = Response::get();
	
		if($sendHeaders) {
			foreach($headers as $name => $value) {
				$r->setHeader($name, $value);
			}

			if(!$r->hasHeader('Content-Type')) {
				$r->setHeader('Content-Type', $this->getContentType());
			}

			//Don't set content length for zip files because gzip of apache will corrupt the download. http://www.heath-whyte.info/david/computers/corrupted-zip-file-downloads-with-php
			if($this->getExtension() != 'zip') {
				$r->setHeader('Content-Length', $this->getSize());
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
					$r->setETag($this->getEtag());
				}
				$r->abortIfCached();
			} else{
				$r->setHeader('Cache-Control','no-cache, must-revalidate, post-check=0, pre-check=0'); //prevent caching
				$r->setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT'); //resolves problem with IE GET requests
				$r->setHeader('Pragma', 'no-cache');
				$r->removeHeader('Last-Modified');
			}
		}

		if (isset($_SERVER['HTTP_RANGE'])){
			$this->rangeDownload();
			return;
		} else {
			Response::get()->setHeader("Accept-Ranges", "bytes");
		}
		
		if(ob_get_contents() != '') {			
			throw new Exception("Could not output file because output has already been sent. Turn off output buffering to find out where output has been started.");
		}

		while (ob_get_level()) ob_end_clean();

		ini_set('zlib.output_compression', 0);

		$r->sendHeaders();

		$handle = $this->open('rb');

		if (!is_resource($handle))
			throw new Exception("Could not read file");

		while (!feof($handle)) {
			echo fread($handle, 1024);
			flush();
		}
	}

	private function rangeDownload() {

		$size   = $this->getSize();
		$length = $size;           // Content length
		$start  = 0;               // Start byte
		$end    = $size - 1;       // End byte

		// multipart/byteranges
		// http://www.w3.org/Protocols/rfc2616/rfc2616-sec19.html#sec19.2
		if (isset($_SERVER['HTTP_RANGE'])) {


			// Extract the range string
			list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
			// Make sure the client hasn't sent us a multibyte range
			if (strpos($range, ',') !== false) {

				// (?) Should this be issued here, or should the first
				// range be used? Or should the header be ignored and
				// we output the whole content?
				Response::get()->setStatus(416);
				Response::get()->setHeader("Content-Range", "bytes $start-$end/$size");
				// (?) Echo some info to the client?
				Response::get()->sendHeaders();
				exit;
			}
			// If the range starts with an '-' we start from the beginning
			// If not, we forward the file pointer
			// And make sure to get the end byte if specified
			if ($range[0] == '-') {
				// The n-number of the last bytes is requested
				$c_start = $size - substr($range, 1);
				$c_end   = $end;
			}	else {
				$range  = explode('-', $range);
				$c_start = $range[0];
				$c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
			}

			/* Check the range and make sure it's treated according to the specs.
			 * http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
			 */
			// End bytes can not be larger than $end.
			$c_end = ($c_end > $end) ? $end : $c_end;
			// Validate the requested range and return an error if it's not correct.
			if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {

				Response::get()->setStatus(416);
				Response::get()->setHeader("Content-Range,", "bytes $start-$end/$size");
				Response::get()->sendHeaders();
				exit;
			}

			$start  = $c_start;
			$end    = $c_end;
			$length = $end - $start + 1; // Calculate new content length

			Response::get()->setStatus(206);
			Response::get()->sendHeaders();
		}
		// Notify the client the byte range we'll be outputting
		Response::get()->setHeader("Content-Range", "bytes $start-$end/$size");
		Response::get()->setHeader("Content-Length", "$length");

		Response::get()->sendHeaders();

		// Start buffered download
		$buffer = 1024 * 8;
		$fp = $this->open('rb');
		if($start > 0) {
			fseek($fp, $start);
		}

		while(!feof($fp) && ($p = ftell($fp)) <= $end) {

			if ($p + $buffer > $end) {

				// In case we're only outputtin a chunk, make sure we don't
				// read past the length
				$buffer = $end - $p + 1;
			}
			set_time_limit(0); // Reset time limit for big files
			echo fread($fp, $buffer);
			flush(); // Free up memory. Otherwise large files will trigger PHP's memory limit.
		}

		fclose($fp);

	}


	/**
	 * Open file pointer
	 *
	 * See php fopen function
	 *
	 * @param string $mode
	 * @return resource
	 * @throws Exception
	 */
	public function open(string $mode){
		
		$this->create();
		
		return fopen($this->getPath(), $mode);
	}

  /**
   * Move a file to new location
   *
   * @param File $destination The file may not exist yet.
   * @return boolean
   * @throws Exception
   */
	public function move(File $destination): bool
	{

		if ($destination->exists()) {
			ErrorHandler::log("File " . $destination->getPath() . " exists in move");
			throw new Exception("File " . $destination->getName() . " exists in move!");
		}

		if($destination->getPath() == $this->getPath()) {
			return true;
		}

		try {
			$success = rename($this->path, $destination->getPath());
		} catch(Exception $e) {
			//renaming across partitions doesn't work
			/** @noinspection PhpConditionAlreadyCheckedInspection */
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
	 * @return bool <b>File</b> on success or <b>FALSE</b> on failure.
	 */
	public function link(File $targetLink): bool
	{
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

	public function getEtag(): string
	{
		return '"'.filemtime($this->path).'-'.fileinode($this->path).'"';
	}

	/**
	 * Pull 40-char sha1 hex from the binary data
	 *
	 * @return string|false
	 */
	public function getSha1Hash()
	{
		return sha1_file($this->path);
	}

	/**
	 * Compare this file with an other file.
	 *
	 * @param File $file
	 * @return bool True if the file is different, false if file is the same.
	 */
	public function equals(File $file): bool
	{
		/** @noinspection PhpPossiblePolymorphicInvocationInspection */
		return $this->md5Hash() == $file->md5Hash();
	}

	/**
	 * Create the file
	 *
	 * @param boolean $createPath Create the folders for this file also?
	 * @param int|null $time The touch time. If time is not supplied, the current system time is used.
	 * @param int|null $atime If present, the access time of the given filename is set to the value of atime. Otherwise, it is set to the value passed to the time parameter. If neither are present, the current system time is used.
	 * @return self|bool $successful
	 * @throws Exception
	 */
	public function touch(bool $createPath = false, int $time = null, int $atime = null) {
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

	/**
	 * @throws Exception
	 */
	private function create() {
		if(!$this->exists()) {
			$this->touch(true);
		}
	}
}
