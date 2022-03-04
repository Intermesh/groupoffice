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

use GO\Base\Fs\Folder;
use GO\Base\Fs\File;

class MessageAttachment extends \GO\Base\Model
{
	public $name = "";
	public $number = 0;
	public $content_id = "";
	public $mime = "application/octet-stream";
	public $index = 0;
	public $size = 0;
	public $encoding = "";
	public $disposition = "";
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
	 * @return MessageAttachment
	 */
	public function createFromTempFile(\GO\Base\Fs\File $file): MessageAttachment
	{
		$a = new MessageAttachment();
		$a->name = $file->name();
		$a->mime = $file->mimeType();

		$a->setTempFile($file);
		$a->size = $file->size();

		return $a;
	}


	/**
	 * Get the temporary file for this attachment
	 * 
	 * @return maxed path  Relative to \GO::config()->tmp_dir
	 */
	public function getTempFile()
	{
		return isset($this->_tmp_file) ? $this->_tmp_file : false;
	}
	
	/**
	 * 
	 * @param Folder $targetFolder
	 * @param string|null $filename Optional
	 * @return File
	 */
	public function saveToFile(Folder $targetFolder, ?string $filename=null): File
	{
		$file = new File(\GO::config()->tmpdir . $this->getTempFile());
		return $file->copy($targetFolder, $filename);
	}
	
	/**
	 * Set the temporary file 
	 * 
	 * @param File $file
	 * @throws \Exception
	 */
	public function setTempFile(File $file){
		if(!$file->isTempFile()) {
			throw new \Exception("File $file->name is not a temporary file");
		}
		$this->_tmp_file = $file->stripTempPath();
	}
	
	/**
	 * Check if the tempfile is available
	 * 
	 * @return bool
	 */
	public function hasTempFile(): bool
	{
		if(empty($this->_tmp_file)) {
			return false;
		}
		return file_exists(\GO::config()->tmpdir.$this->_tmp_file);
	}
	
	public function getData()
	{
		if(empty($this->_tmp_file)) {
			return null;
		}
		return file_get_contents(\GO::config()->tmpdir.$this->_tmp_file);
	}
	
	
	/**
	 * Get the download URL
	 * @return string
	 */
	public function getUrl(): string
	{
		if ($this->getExtension()=='dat') {
			return \GO::url('email/message/tnefAttachmentFromTempFile', array('tmp_file' => $this->getTempFile()));
		}
		return \GO::url('core/downloadTempFile', array('path'=>$this->getTempFile(), "cache" => "1"));
	}
	
	/**
	 * Check if the attachment is inline
	 * @return bool
	 */
	public function isInline(): bool
	{
		return !empty($this->content_id) || $this->disposition=='inline';
	}
	
	/**
	 * Get all attributes. Useful to output to the client through JSON.
	 * 
	 * @return array 
	 */
	public function getAttributes(): array
	{
		return array(
			"url" => $this->getUrl(),
			"name" => $this->name,
			"number" => $this->number,
			"content_id" => $this->content_id,
			"mime" => $this->mime,
			"tmp_file" => $this->getTempFile(),
			"index" => $this->index,
			"size" => $this->size,
			"human_size" => $this->getHumanSize(),
			"extension" => $this->getExtension(),
			"encoding" => $this->encoding,
			"disposition" => $this->disposition,
			"isInvite" => $this->isVcalendar()
		);
	}

	/**
	 * Estimates base64 decoded data size by multiplying with 3/4. Padding can't
	 * be used because we don't have the data.
	 *
	 * @return float
	 */
	public function getEstimatedSize(): float
	{
		if ($this->encoding === 'base64') {
			return ceil($this->size * 0.75);
		}
		return $this->size;
	}

	/**
	 * Get the size formatted. eg. 128 kb
	 * @return string
	 */
	public function getHumanSize(): string
	{
		return \GO\Base\Util\Number::formatSize($this->getEstimatedSize());
	}

	/**
	 * Get the file extension
	 *
	 * @return string
	 */
	public function getExtension(): string
	{
		$file = new \GO\Base\Fs\File($this->name);
		return strtolower($file->extension());
	}


	public function isVcalendar(): bool
	{
		return $this->mime == 'text/calendar' || $this->getExtension() == 'ics';
	}
}
