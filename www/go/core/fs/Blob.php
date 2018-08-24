<?php

namespace go\core\fs;

use go\core\db\Query;
use go\core\orm;
use go\core\util\DateTime;
use function GO;

class Blob extends orm\Entity {

	/**
	 * The 20 character blob hash
	 * 
	 * @var string 
	 */
	public $id;
	
	/**
	 * Content type
	 * 
	 * @var string eg. text/plain
	 */
	public $type;
	
	/**
	 * File name of the hash (first upload)
	 * 
	 * @var string 
	 */
	public $name;
	
	/**
	 * Blob size in bytes
	 * @var int
	 */
	public $size;
	
	/**
	 * Modified at
	 * 
	 * @var DateTime
	 */
	public $modifiedAt;
	
	/**
	 * Creation time
	 * 
	 * blob is created when uploaded for the first time
	 * 
	 * @var DateTime
	 */
	public $createdAt; 
	
	/**
	 * Blob can be deleted after this date
	 * 
	 * @var DateTime
	 */
	public $staleAt;
	
	private $tmpFile;
	private $strContent;
	
	private static $refs;
	
	private static function getReferences() {
		
		if(isset(self::$refs)) {
			return self::$refs;
		}
		$dbName = GO()->getDatabase()->getName();
		GO()->getDbConnection()->query("USE information_schema");
		//somehow bindvalue didn't work here
		$sql = "SELECT `TABLE_NAME` as `table`, `COLUMN_NAME` as `column` FROM `KEY_COLUMN_USAGE` where constraint_schema=" . GO()->getDbConnection()->getPDO()->quote($dbName) . " and referenced_table_name='core_blob' and referenced_column_name = 'id'";
		$stmt = GO()->getDbConnection()->getPDO()->query($sql);
		self::$refs = $stmt->fetchAll(\PDO::FETCH_ASSOC);		
		GO()->getDbConnection()->query("USE `" . $dbName . "`");
		
		return self::$refs;
	}
	
	/**
	 * 
	 */
	public function setStaleIfUnused() {
		$records = $this->getReferences();	
		
		foreach($records as $record) {
			$exists = (new Query)
							->selectSingleValue($record['column'])
							->from($record['table'])
							->where($record['column'], '=', $this->id)
							->single();
			
			if($exists) {
				return false;
			}
		}
		
		$this->staleAt = new DateTime();
		if(!$this->save()) {
			throw new \Exception("Couldn't save blob");
		}
		return true;
	}
	
	public static function fromTmp(File $file) {
		$hash = bin2hex(sha1_file($file->getPath(), true));
		$blob = self::findById($hash);
		if (empty($blob)) {
			$blob = new self();
			$blob->id = $hash;
			$blob->size = $file->getSize();
		}
		$blob->name = $file->getName();
		$blob->tmpFile = $file->getPath();
		$blob->type = $file->getContentType();
		$blob->modified = $file->getModifiedAt()->format("U");
		return $blob;
	}
	
	public static function fromString($string) {
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
	 * @return MetaData
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
	 * 
	 * @return File
	 */
	public function getFile() {
		return new File($this->path());
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
