<?php

namespace go\core\mail;

use Exception;
use go\core\fs\Blob;
use go\core\fs\File;

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

	public static function fromPath(string $path, ?string $contentType = null): Attachment
	{
		$file = new File($path);

		$a = new self();
		$a->file = $file;
		$a->contentType = $contentType ?? $file->getContentType();
		$a->filename = $file->getName();

		return $a;
	}

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


	public function getId(): ?string
	{
		if(!isset($this->id)) {
			$this->id = bin2hex(random_bytes(16));
		}
		return $this->id;
	}

	public function setId(string $id): Attachment
	{
		$this->id = $id;
		return $this;
	}

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