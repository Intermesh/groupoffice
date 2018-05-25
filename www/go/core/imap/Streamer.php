<?php
namespace go\core\imap;

/**
 * IMAP Data streamer
 * 
 * Can be used to stream attachments straight to the browser or a file in a 
 * memory efficient way.
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Streamer {

	private $filePointer;
	private $encoding;
	
	private $leftOver = "";

	/**
	 * Constructior
	 * 
	 * @param resource $filePointer Writable pointer to a stream
	 * @param string $encoding
	 */
	public function __construct($filePointer, $encoding = null) {
		$this->filePointer = $filePointer;
		$this->encoding = strtolower($encoding);
	}

	/**
	 * Stream data to the file pointer
	 * 
	 * @param string $data
	 */
	public function put($data) {

		switch ($this->encoding) {
			case 'base64':
				$data = $this->leftOver . $data;
				
				$this->leftOver = "";

				if (strlen($data) % 4 == 0) {

					if (!$this->filePointer) {
						$str .= base64_decode($data);
					} else {
						fputs($this->filePointer, base64_decode($data));
					}
				} else {

					$buffer = "";
					while (strlen($data) > 4) {
						$buffer .= substr($data, 0, 4);
						$data = substr($data, 4);
					}

					if (!$this->filePointer) {
						$str .= base64_decode($buffer);
					} else {
						fputs($this->filePointer, base64_decode($buffer));
					}

					if (strlen($data)) {
						$this->leftOver = $data;
					}
				}
				break;
			case 'quoted-printable':
				if (!$this->filePointer) {
					$str .= quoted_printable_decode($data);
				} else {
					fputs($this->filePointer, quoted_printable_decode($data));
				}
				break;
			default:
				if (!$this->filePointer) {
					$str .= $data;
				} else {
					fputs($this->filePointer, $data);
				}
				break;
		}
	}
	
	public function finish(){
		if (!empty($this->leftOver)) {
			fputs($this->filePointer, base64_decode($this->leftOver));			
		}		
//		fclose($this->filePointer);
	}
}
