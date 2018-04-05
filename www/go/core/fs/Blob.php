<?php

namespace go\core\fs;

use go\core\orm;

class Blob extends orm\Entity {

	public $id;
	public $type; // content-type
	public $name;
	public $size; // in bytes
	public $modified; // timestamp
	public $createdAt;
	private $tmpFile;
	private $strContent;
	
	static function fromTmp($path) {
		$hash = bin2hex(sha1_file($path, true));
		$blob = self::findById($hash);
		if (empty($blob)) {
			$blob = new self();
			$blob->id = $hash;
			$blob->size = filesize($path);
			$blob->tmpFile = $path;
		}
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

	protected function internalSave() {
		if(!$this->isNew()) {
			return true;
		}
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
		return parent::internalSave();
	}

	public function path() {
		$filePath = substr_replace(substr_replace($this->id,'/',2,0),'/',5,0); // 1st-2 and 2nd-2 chars = dir and subdir
		return GO()->getDataFolder()->getPath() . '/data/' . $filePath;
	}

}
