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

	const ENCODING_7BIT = '7bit';
	const ENCODING_8BIT = '8bit';
	const ENCODING_BASE64 = 'base64';
	const ENCODING_BINARY = 'binary';
	const ENCODING_QUOTED_PRINTABLE = 'quoted-printable';

	private ?string $data = null;
	private ?File $file = null;
	private ?string $contentType = null;
	private ?string $filename = null;
	private ?string $id = null;
	private bool $inline = false;

	private string $encoding = self::ENCODING_BASE64;

	/**
	 * Provide Blob. Extracts path from blob and returns attachment
	 *
	 * @param Blob $blob
	 * @return Attachment
	 * @throws Exception
	 */
	public static function fromBlob(Blob $blob, string $encoding = self::ENCODING_BASE64): Attachment
	{
		$a = self::fromPath($blob->path(), $blob->type);
		$a->setFilename($blob->name);
		$a->encoding = $encoding;
		return $a;
	}

	/**
	 * Create attachment from file
	 *
	 * @param string $path
	 * @param string|null $contentType Set the content type. If not given it will detect it from the file. eg. application/pdf
	 * @return Attachment
	 */
	public static function fromPath(string $path, ?string $contentType = null, string $encoding = self::ENCODING_BASE64): Attachment
	{
		$file = new File($path);

		$a = new self();
		$a->file = $file;
		$a->contentType = $contentType ?? $file->getContentType();
		$a->filename = $file->getName();
		$a->encoding = $encoding;

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
	public static function fromString(string $data, string $filename, string $contentType = 'application/octet-stream', string $encoding = self::ENCODING_BASE64): Attachment
	{
		$a = new self();
		$a->data = $data;
		$a->contentType = $contentType;
		$a->filename = $filename;
		$a->encoding = $encoding;

		return $a;
	}

	public function setFilename(?string $filename): Attachment
	{
		$this->filename = $filename;
		return $this;
	}

	public function getFilename(): ?string
	{
		return $this->filename;
	}

	public function getEncoding(): ?string
	{
		return $this->encoding;
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
		if(isset($this->file)) {
			return $this->file->getContents();
		} else{
			return $this->data ?? "";
		}
	}

}