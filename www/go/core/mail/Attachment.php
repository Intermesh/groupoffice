<?php

namespace go\core\mail;

use Exception;
use go\core\fs\Blob;
use go\core\fs\File;

/*
 * The attachment class for e-mail messages
 *
 * @author Merijn Schering <mschering@intermesh.nl>
 * @copyright Intermesh BV
 */
class Attachment
{

	/**
	 * @var string
	 */
	private $data;

	/**
	 * @var File
	 */
	private $file;
	/**
	 * @var string
	 */
	private $contentType;

	private $filename;

	private $id;

	private $inline = false;

	/**
	 * Provide Blob. Extracts path from blob and returns attachment
	 *
	 * @param Blob $blob
	 * @return Attachment
	 * @throws Exception
	 */
	public static function fromBlob(Blob $blob): Attachment
	{
		$a = self::fromPath($blob->path(), $blob->type);
		$a->setFilename($blob->name);
		return $a;
	}

	/**
	 * Create attachment from file
	 *
	 * @param string $path
	 * @param string|null $contentType Set the content type. If not given it will detect it from the file. eg. application/pdf
	 * @return Attachment
	 */
	public static function fromPath(string $path, ?string $contentType = null): Attachment
	{
		$file = new File($path);

		$a = new self();
		$a->file = $file;
		$a->contentType = $contentType ?? $file->getContentType();
		$a->filename = $file->getName();

		return $a;
	}

	/**
	 * Create attachment from string
	 *
	 * @param string $data The file data
	 * @param string $filename Filename
	 * @param string $contentType The content type. eg. application/pdf
	 * @return Attachment
	 */
	public static function fromString(string $data, string $filename, string $contentType = 'application/octet-stream'): Attachment
	{
		$a = new self();
		$a->data = $data;
		$a->contentType = $contentType;
		$a->filename = $filename;

		return $a;
	}

	public function setFilename(string $filename): Attachment
	{
		$this->filename = $filename;
		return $this;
	}

	public function getFilename()
	{
		return $this->filename;
	}


	/**
	 * The attachment content ID
	 * See also {@see setInline()}.
	 *
	 * @return string|null
	 * @throws Exception
	 */

	public function getId(): ?string
	{
		if(!isset($this->id)) {
			$this->id = bin2hex(random_bytes(16));
		}
		return $this->id;
	}


	/**
	 * The attachment content ID.
	 * See also {@see setInline()}.
	 *
	 * @param string $id
	 * @return Attachment
	 */
	public function setId(string $id): Attachment
	{
		$this->id = $id;
		return $this;
	}


	/**
	 * Set the file to inline. The ID of this attachment should occur in an image tag with src="cid:$id".
	 * See also {@see setId()}.
	 *
	 * @param bool $inline
	 * @return $this
	 */

	public function setInline(bool $inline): Attachment
	{
		$this->inline = $inline;
		return $this;
	}

	public function getInline() : bool {
		return $this->inline;
	}

	public function getContentType() : string {
		return $this->contentType;
	}

	public function setContentType(string $contentType) : Attachment {
		$this->contentType = $contentType;
		return $this;
	}


	public function isFile() :bool {
		return $this->file !== null;
	}

	/**
	 * @return resource
	 */
	public function getStream() {
		if($this->file) {
			return $this->file->open("r");
		} else {
			$stream = fopen('php://memory','r+');
			fwrite($stream, $this->data);
			rewind($stream);
			return $stream;
		}
	}

	public function getFile(): File
	{
		if ($this->file) {
			return $this->file;
		} else {
			$this->file = File::tempFile();
			$this->file->putContents($this->data);
			$this->data = null;
			return $this->file;
		}
	}

	public function getString() :string {
		if($this->file) {
			return $this->file->getContents();
		} else{
			return $this->data ?? "";
		}
	}

}