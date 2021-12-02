<?php

namespace go\core\imap;

use go\core\data\Model;

/**
 * Abstract Part class
 * 
 * Base class for a single and multipart of a MIME message
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
abstract class Part extends Model {

	/**
	 * IMAP part number.
	 * 
	 * eg. "1" in a single part or "1.1" in multipart messages
	 * 
	 * @var string 
	 */
	public $partNumber;
	
	/**
	 * Type of the part. 
	 * 
	 * eg. "text", "image", "multipart"
	 * 
	 * @var string 
	 */
	public $type = 'multipart';
	
	/**
	 * Content ID
	 * 
	 * @var string 
	 */
	public $id;
	
	/**
	 * Subtype
	 * 
	 * eg. "plain", "html", "jpeg", "mixed"
	 * 
	 * @var string 
	 */
	public $subtype;
	
	/**
	 * Array of extra parameters
	 * 
	 * @var array 
	 */
	public $params;
	
	
	public $description;
	
	/**
	 * Encoding type
	 * 
	 * eg. base64 or quoted-printable
	 * 
	 * @var string 
	 */
	public $encoding;
	
	/**
	 * Size in bytes
	 * 
	 * @var int 
	 */
	public $size;
	
	public $lines;
	
	public $md5;
	
	/**
	 * Disposition
	 * 
	 * eg.
	 * 
	 * ['attachment' => ['filename' => 'Doc.pdf']]
	 * 
	 * @var array 
	 */
	public $disposition;
	
	public $language;
	
	public $location;

	/**
	 * The message this part belongs to
	 * 
	 * @var Message 
	 */
	public $message;

	/**
	 * Increments the part number. 
	 * 
	 * eg. 1.1 becomes 1.2
	 * 
	 * @param string $partNumber
	 * @param string
	 */
	protected function incrementPartNumber($partNumber) {
		if (!strstr($partNumber, '.')) {
			$partNumber++;
		} else {
			$parts = explode('.', $partNumber);
			$parts[(count($parts) - 1)] ++;
			$partNumber = implode('.', $parts);
		}
		return (string) $partNumber;
	}

	/**
	 * Get the data of this part
	 * 
	 * @param boolean $peek Don't mark message as read
	 * @param \go\core\imap\Streamer $streamer
	 * @param string|boolean Returns boolean if streamer is given and operation was successful
	 */
	public function getData($peek = true, Streamer $streamer = null) {
		return $this->message->fetchPartData($this->partNumber, $peek, $streamer);
	}
	
	/**
	 * Get the filename
	 * 
	 * Uses the part name or content disposition
	 * 
	 * @return boolean
	 */
	public function getFilename(){
		
		if(isset($this->disposition) && is_array($this->disposition)){
			$dispositionType = key($this->disposition);
			
			
			if($dispositionType && !empty($this->disposition[$dispositionType]['filename'])){
				$decoded = Utils::mimeHeaderDecode($this->disposition[$dispositionType]['filename']);
				return $decoded;
			}
		} 
			
		if(!empty($this->params['name'])){
			return Utils::mimeHeaderDecode($this->params['name']);
		}
//		We had mails that had content description
//		else if(isset($this->description)){
//			return $this->description.'.txt';
//		}
	
		return null;
	}
	
	public function getContentType() {
		return $this->type.'/'.$this->subtype;
	}
	
	/**
	 * Stream data to a file pointer
	 * 
	 * @param $filePointer If none is given the browser output will be used
	 */
	public function output($filePointer = null){
		
		
		if(!isset($filePointer)){
			
			$sendHeaders = true;
		
			$filePointer = fopen("php://output",'w');
		}else
		{
			$sendHeaders = false;
		}
		
		if(!is_resource($filePointer)){
			throw new Exception("Invalid file pointer given");
		}
		
		if($sendHeaders){
			header('Content-Type: '.$this->type.'/'.$this->subtype);
			header('Content-Disposition: inline; filename='.$this->getFilename());
		}		
		
		$streamer = new Streamer($filePointer, $this->encoding);
		
		$this->getData(true, $streamer);
	}	
	
	public function toArray(array $attributes = null): array
	{
		return parent::toArray($attributes);
	}
}
