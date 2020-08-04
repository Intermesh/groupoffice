<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: LinkedEmail.php 7607 2011-09-01 15:38:01Z <<USERNAME>> $
 * @copyright Copyright Intermesh
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */

/**
 * E-mail message attachment model
 * 
 * @var string $name Filename of the attachment 
 * @var string $number Unique structure number. Eg. 1.1
 * @var string $content_id If it's an inline image it can have a content ID. The body can inlude an image tag with this content ID.
 * @var string $mime MIME content type
 * @var int $index Index number of the attachment
 * @var int $size Size in bytes
 * @var string $encoding Content encoding
 * @var string $disposition Can be attachment or inline.
 */

namespace GO\Email\Model;


class MessageAttachment extends \GO\Base\Model{
	public $name="";
	public $number=0;
	public $content_id="";
	public $mime="application/octet-stream";
	public $index=0;
	public $size=0;
	public $encoding="";
	public $disposition="";
	
	public $_tmp_file;
	
	/**
	 * Returns the static model of the specified AR class.
	 * Every child of this class must override it.
	 * 
	 * @return MessageAttachment the static model class
	 */
	public static function model($className=__CLASS__)
	{		
		return parent::model($className);
	}
	
	/**
	 * Create a new instance for an ComposerMessage for example.
	 * 
	 * @param \GO\Base\Fs\File $file The temporary file
	 * @return \MessageAttachment 
	 */
	public function createFromTempFile(\GO\Base\Fs\File $file){
		//		$a['name'] = $file->name();
		$a = new MessageAttachment();
		$a->name=$file->name();
		$a->mime= $file->mimeType();
		
		$a->setTempFile($file);
		$a->size=$file->size();
		
		return $a;
	}
	/**
	 * Get the temporary file for this attachment
	 * 
	 * @return StringHelper Relative to \GO::config()->tmp_dir 
	 */
	public function getTempFile(){
		return isset($this->_tmp_file) ? $this->_tmp_file : false;
	}
	
	/**
	 * 
	 * @param \GO\Base\Fs\Folder $targetFolder
	 * @param string $filename Optional
	 * @return type
	 */
	public function saveToFile(\GO\Base\Fs\Folder $targetFolder, $filename=null){
		
		$file = new \GO\Base\Fs\File(\GO::config()->tmpdir . $this->getTempFile());
		return $file->copy($targetFolder, $filename);
	}
	
	/**
	 * Set the temporary file 
	 * 
	 * @param \GO\Base\Fs\File $file
	 * @throws Exception 
	 */
	public function setTempFile(\GO\Base\Fs\File $file){
		if(!$file->isTempFile())
			throw new \Exception("File $file->name is not a temporary file");
		
		$this->_tmp_file = $file->stripTempPath();
	}
	
	/**
	 * Check if the tempfile is available
	 * 
	 * @return boolean 
	 */
	public function hasTempFile(){
		if(empty($this->_tmp_file))
			return false;
		else {
			return file_exists(\GO::config()->tmpdir.$this->_tmp_file);
		}
	}
	
	public function getData() {		
		if(empty($this->_tmp_file))
			return null;
		else {
			return file_get_contents(\GO::config()->tmpdir.$this->_tmp_file);
		}
	}
	
	
	/**
	 * Get the download URL
	 * @return StringHelper 
	 */
	public function getUrl(){
		if($this->getExtension()=='dat'){			
			return \GO::url('email/message/tnefAttachmentFromTempFile', array('tmp_file'=>$this->getTempFile()));
		}else
		{		
			return \GO::url('core/downloadTempFile', array('path'=>$this->getTempFile(), "cache" => "1"));
		}		
	}
	
	/**
	 * Check if the attachment is inline
	 * @return boolean 
	 */
	public function isInline(){
		
		//these don't work because you won't get temporary files when sending a message.
		//return !empty($this->content_id) && $this->disposition!='attachment';
		//return $this->disposition=='inline';
		
		return !empty($this->content_id) || $this->disposition=='inline';
	}
	
	/**
	 * Get all attributes. Useful to output to the client through JSON.
	 * 
	 * @return array 
	 */
	public function getAttributes(){
		return array(
				"url"=>$this->getUrl(),
				"name"=>$this->name,
				"number"=>$this->number,
				"content_id"=>$this->content_id,
				"mime"=>$this->mime,
				"tmp_file"=>$this->getTempFile(),
				"index"=>$this->index,
				"size"=>$this->size,
				"human_size"=>$this->getHumanSize(),
				"extension"=>$this->getExtension(),
				"encoding"=>$this->encoding,
				"disposition"=>$this->disposition,
			  "isInvite" => $this->isVcalendar()
		);
	}	
	
	/**
	 * Estimates base64 decoded data size by multiplying with 3/4. Padding can't
	 * be used because we don't have the data.
	 * 
	 * @return int
	 */
	public function getEstimatedSize(){
		switch($this->encoding){
			case 'base64':
				return ceil($this->size*0.75);
				break;
			default:
				return $this->size;
				break;
		}
	}
	
	/**
	 * Get the size formatted. eg. 128 kb
	 * @return StringHelper 
	 */
	public function getHumanSize(){
		return \GO\Base\Util\Number::formatSize($this->getEstimatedSize());
	}
	
	/**
	 * Get the file extension
	 * 
	 * @return StringHelper
	 */
	public function getExtension(){
		$file = new \GO\Base\Fs\File($this->name);
		return strtolower($file->extension());
	}
	
	
	public function isVcalendar(){
		return $this->mime=='text/calendar' || $this->getExtension() == 'ics';
	}
}
