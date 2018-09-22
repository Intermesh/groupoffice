<?php

namespace go\core\fs;

use go\core\orm;

class Blob extends orm\Entity {

	public $id;
	public $type; // content-type
	public $name;
	public $size; // in bytes
//	public $fsModifiedAt; // timestamp
//	public $fsCreatedAt; // ts
	public $modified;
	public $createdAt; // blob is created when uploaded for the first time
	private $tmpFile;
	private $strContent;
	
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
		return parent::defineMapping()->addTable('core_blob', 'b');
	}
	
	/**
	 * @return \go\core\fs\MetaData
	 */
	public function getMetaData() {
		return new MetaData($this);
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
	
	/**
	 * Get a blob URL
	 * 
	 * @param string $blobId
	 * @return string
	 */
	public static function url($blobId) {
		return GO()->getSettings()->URL . 'download.php?blob=' . $blobId;
	}

}
