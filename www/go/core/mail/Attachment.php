<?php

namespace go\core\mail;

use Exception;
use go\core\fs\Blob;
use go\core\fs\File;

class Attachment
{

	private $stream;
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
		return self::fromPath($blob->path(), $blob->type);
	}

	public static function fromPath(string $path, ?string $contentType = null): Attachment
	{
		$file = new File($path);

		$a = new self();
		$a->stream = $file->open('r+');
		$a->contentType = $contentType ?? $file->getContentType();
		$a->filename = $file->getName();

		return $a;
	}

	public static function fromString(string $data, string $filename, string $contentType = 'application/octet-stream'): Attachment
	{
		$stream = fopen('php://memory','r+');
		fwrite($stream, $data);
		rewind($stream);

		$a = new self();
		$a->stream = $stream;
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

	/**
	 * @return resource
	 */
	public function getStream() {
		return $this->stream;
	}

}