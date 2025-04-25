<?php

namespace go\modules\community\email\model;

use go\core\data\Model;

class EmailBodyPart extends Model {

	const defaultProps = ["partId", "blobId", "size", "name", "type", "charset",
		"disposition", "cid", "language", "location", "subParts"];

	/** @var string|null part id in body structure null if multipart/* */
	public $partId = null;

	/** @var string sha1 hash of decoded value */
	public $blobId = null;

	/** @var int size in bytes of decoded value or null if multipart */
	public $size = 0;

	/** @var EmailHeader[] raw values of mime part headers */
	private $_headers;

	/** @var string filename from `Content-Disposition` header or `Content-Type` name param */
	public $name = null;

	/** @var string value of `Content-Type` header field */
	public $type;

	/** @var string (only for `text/*`) Content-Type */
	public $charset;

	/** @var string `Content-Disposition` header eg. "inline", "attachment" */
	public $disposition;

	/** @var string `Content-id` header CFWS and <> removed for html `cid:` */
	public $cid = null;

	/** @var string[] `Content-Language` header */
	public $language;

	/** @var string URI in `Content-Location` header */
	public $location;

	/** @var EmailBodyPart[] if type `multipart/*` body parts of each child */
	public $subParts = [];

	/** @var string encoding type for transport '7/8bit', 'quoted-printable', 'base64' */
	public $encoding = '8bit';

	/** @var string the boundary string for multipart types */
	private $boundary;

	private $content;

	/** @var int uniqueness for boundary */
	protected static $makeUnique = 0;

	public function __construct($config = []) {
		foreach ($config as $key => $val) {
			if($key === 'subParts') {
				foreach($val as $subPart) {
					$this->subParts[] = new self($subPart);
				}
			} else {
				$this->$key = $val;
				if($this->isMultipart()) {
					$this->boundary = $this->genBoundery();
				}
			}
		}
	}


	public static function isPrintable($str) {
		return (strcspn($str, self::qpKeys) == strlen($str));
	}

	public function encoding() {
		return $this->encoding;
	}
	public function boundary() {
		return $this->boundary;
	}

	public function readHeaders($headers) {

		if (isset($headers['content-type'])) {
			$h = explode(';', $headers['content-type']);
			$type = array_shift($h);
			foreach ($h as $param) {
				$pos = strpos($param, 'filename=');
				if ($pos !== false) {
					$this->name = trim(trim(substr($param, $pos + 9)), '"');
				}
			}
		}
		if (isset($headers['content-disposition'])) {
			$h = explode(';', $headers['content-disposition']);
			$this->disposition = array_shift($h);
			foreach ($h as $param) {
				$pos = strpos($param, 'filename=');
				if ($pos !== false) {
					$this->name = trim(trim(substr($param, $pos + 9)), '"');
				}
			}
		}
		if (isset($headers['content-id'])) {
			$this->cid = $headers['content-id'];
		}
		if (isset($headers['content-language'])) {
			$this->cid = $headers['content-language'];
		}
		if (isset($headers['content-location'])) {
			$this->cid = $headers['content-location'];
		}
		if($this->cid) {
			$this->cid = trim($this->cid, '<>');
		}
	}

	public function getBlobId() {
		return null;
		//return 'mail.'.$this->owner->id.'-'.$this->partId;
	}

	public function setContent($content) {
		if (!is_string($content) && !is_resource($content)) {
			throw new \InvalidArgumentException(sprintf(
				'Content must be string or resource; received "%s"', is_object($content) ? get_class($content) : gettype($content)
			));
		}
		$this->content = $content;
	}

	public function isInlineMedia() {
		return $this->isA('image') || $this->isA('audio') || $this->isA('video');
	}

	public function isInline() {
		return $this->disposition !== "attachment" &&
			// Must be one of the allowed body types
			( $this->type === "text/plain" ||
				$this->type === "text/html" ||
				$this->isInlineMedia() );
	}

	/**  relative, mixed and alternative */
	public function isMultipart() {
		return $this->isA('multipart');
	}

	public function subType() {
		return substr($this->type, strpos($this->type, '/') + 1);
	}

	public function isA($str) {
		return strncmp($this->type, $str . '/', strlen($str) + 1) === 0;
	}

	private function getEncodedStream($EOL) {
		switch ($this->encoding) {
			case 'quoted-printable':
			case 'base64':
				if (array_key_exists($this->encoding, $this->filters)) {
					stream_filter_remove($this->filters[$this->encoding]);
				}
				$filter = stream_filter_append(
					$this->content, 'convert.' . $this->encoding . '-encode', STREAM_FILTER_READ, ['line-length' => 76, 'line-break-chars' => $EOL]
				);
				$this->filters[$this->encoding] = $filter;
				if (!is_resource($filter)) {
					throw new \RuntimeException('Failed to append ' . $this->encoding . ' filter');
				}
				break;
			default:
		}
		return $this->content;
	}

	public function getContent($EOL = "\r\n") {
		if (is_resource($this->content)) {
			$encodedStream = $this->getEncodedStream($EOL);
			$encodedStreamContents = stream_get_contents($encodedStream);
			$streamMetaData = stream_get_meta_data($encodedStream);
			if (isset($streamMetaData['seekable']) && $streamMetaData['seekable']) {
				rewind($encodedStream);
			}
			return $encodedStreamContents;
		}
		switch ($this->encoding) {
			case 'quoted-printable':
			case 'base64':
				return $this->encode($this->content, $EOL);
			default: // 8bit + 7bit
				return $this->content;
		}
	}

	private function encode($str, $EOL) {
		return iconv_mime_encode('', $str, [
			'input-charset' => $this->charset,
			'output-charset' => 'UTF-8',
			'line-length' => 72,
			'line-break-chars' => "\n",
			'scheme' => strtoupper(substr($this->encoding, 0, 1))
		]);
	}

	private function headersArray($encoding, $EOL) {
		$headers = [];
		$contentType = $this->type;
		if ($this->charset) {
			$contentType .= '; charset=' . $this->charset;
		}
		if ($this->boundary) {
			$contentType .= ';' . $EOL . " boundary=\"" . $this->boundary . '"';
		}
		$headers[] = ['Content-Type', $contentType];

		if ($encoding && $encoding !== '7bit') {
			$headers[] = ['Content-Transfer-Encoding', $encoding];
		}
		if ($this->cid) {
			$headers[] = ['Content-ID', $this->cid];
		}
		if ($this->disposition) {
			$disposition = $this->disposition;
			if ($this->name) {
				$disposition .= '; filename="' . $this->name . '"';
			}
			$headers[] = ['Content-Disposition', $disposition];
		}
//    if ($this->description) { // IN RFC but not in JMAP
//       $headers[] = ['Content-Description', $this->description];
//    }
		if ($this->location) {
			$headers[] = ['Content-Location', $this->location];
		}
		if ($this->language) {
			$headers[] = ['Content-Language', $this->language];
		}
		return $headers;
	}

	public function buildHeaders($encoding, $EOL = "\r\n") {
		$res = '';
		foreach ($this->headersArray($encoding, $EOL) as $header) {
			$res .= $header[0] . ': ' . $header[1] . $EOL;
		}
		return $res;
	}

}
