<?php

namespace go\core\fs;

use go\core\orm;

class Blob extends orm\Entity {

	public $id;
	public $contentType; // content-type
	public $name;
	public $size; // in bytes
//	public $fsModifiedAt; // timestamp
//	public $fsCreatedAt; // ts
	public $modified;
	public $createdAt; // blob is created when uploaded for the first time
	private $tmpFile;
	private $strContent;
	public $metaData;
	private $fetchMetaData = true;
	
	static function fromTmp($path) {
		$hash = bin2hex(sha1_file($path, true));
		$blob = self::findById($hash);
		if (empty($blob)) {
			$blob = new self();
			$blob->id = $hash;
			$blob->size = filesize($path);
		}
		$blob->tmpFile = $path;
		return $blob;
	}
	
	static function fromString($string) {
		$hash = bin2hex(sha1($string, true));
		$blob = self::findById($hash);
		if (empty($blob)) {
			$blob = new self();
			$blob->id = $hash;
			$blob->size = mb_strlen($string, '8bit');
			$blob->strContent = $string;
		}
		return $blob;
	}
	
	protected static function defineMapping() {
		return parent::defineMapping()->addTable('core_blob', 'b')
			->addRelation('metaData', MetaData::class, ['id' => 'blobId'], false);
	}
	
	public function disableMetaData(){
		$this->fetchMetaData = false;
	}
	
	protected function internalValidate() {
		if($this->isNew() && $this->fetchMetaData) {
			$this->parseMetaData();
		}
		return parent::internalValidate();
	}

	protected function internalSave() {
		if (!is_dir(dirname($this->path()))) {
			mkdir(dirname($this->path()), 0775, true);
		}
		if (!file_exists($this->path())) { 
			if (!empty($this->tmpFile)) {
				rename($this->tmpFile, $this->path());
			} else if (!empty($this->strContent)) {
				file_put_contents($this->path(), $this->strContent);
			}
		}
		if(!$this->isNew()) {
			return true;
		}
		return parent::internalSave();
	}
	
	private function parseMetaData() {
		$metaData = new MetaData();
		if($metaData->extract($this) !== false) {
			\GO::debug($metaData->toArray());
			$this->metaData = $metaData;
		}
	}
	
	private function rangeDownload($httpRange) {

		list(, $range) = explode('=', $httpRange, 2);
		if (strpos($range, ',') !== false) { //no support for multi-range (yet)
			return false;
		}
		if ($range[0] == '-') {
			$start = $this->size - substr($range, 1);
		}else{
			list($start, $end)  = explode('-', $range);
			$end = (isset($end) && is_numeric($end)) ? $end : $this->size - 1;
		}
		$end = min($end, $this->size-1);
		if ($start > $end) {
			header('HTTP/1.1 416 Requested Range Not Satisfiable');
			header("Content-Range: bytes $start-$end/$this->size");
		}

		header('HTTP/1.1 206 Partial Content');
		header("Content-Range: bytes $start-$end/$this->size");
		header('Content-Length: '.($end - $start + 1));

		$buffer = 1024 * 8;
		$fp = fopen($this->path(), 'rb');
		
		fseek($fp, $start);
		while(!feof($fp) && ($p = ftell($fp)) <= $end) {
			if ($p + $buffer > $end) {
					$buffer = $end - $p + 1;
			  }
			echo fread($fp, $buffer);
			flush();
		}
		fclose($fp);
		return true;
	}
	
	public function download() {
		set_time_limit(0);
		header('Content-Type: '.$this->contentType);
		header("Accept-Ranges: bytes");
		if(isset($_SERVER['HTTP_RANGE'])){
			$this->rangeDownload($_SERVER['HTTP_RANGE']);
		} else {
			header('Content-Disposition: attachment; filename="' . $this->name . '"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . $this->size);
			readfile($this->path());
		}
	}
	
	protected function internalDelete() {
		if(parent::internalDelete()) {
			if(is_file($this->path())) {
				unlink($this->path());
			}
			return true;
		}
		return false;
	}

	public function path() {
		$dir = substr($this->id,0,2) . '/' .substr($this->id,2,2). '/';
		return GO()->getDataFolder()->getPath() . '/data/'.$dir.$this->id;
	}

}
