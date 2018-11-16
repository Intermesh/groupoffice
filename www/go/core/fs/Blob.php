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
	

	/**
	 * Get all table columns referencing the core_blob.id column.
	 * 
	 * @return array [['table'=>'foo', 'column' => 'blobId']]
	 */
	public static function getReferences() {
		
		$refs = GO()->getCache()->get("blob-refs");
		if(!$refs) {
			$dbName = GO()->getDatabase()->getName();
			GO()->getDbConnection()->query("USE information_schema");
			//somehow bindvalue didn't work here
			$sql = "SELECT `TABLE_NAME` as `table`, `COLUMN_NAME` as `column` FROM `KEY_COLUMN_USAGE` where constraint_schema=" . GO()->getDbConnection()->getPDO()->quote($dbName) . " and referenced_table_name='core_blob' and referenced_column_name = 'id'";
			$stmt = GO()->getDbConnection()->getPDO()->query($sql);
			$refs = $stmt->fetchAll(\PDO::FETCH_ASSOC);		
			GO()->getDbConnection()->query("USE `" . $dbName . "`");			
			
			GO()->getCache()->set("blob-refs", $refs);			
		}		
		
		return $refs;
	}
	
	/**
	 * Set the blob stale if it's not used in any of the referencing tables.
	 * 
	 * @return bool true if blob is stale
	 */
	public function setStaleIfUnused() {
		$refs = $this->getReferences();	
		
		$exists = false;
		foreach($refs as $ref) {
			$exists = (new Query)
							->selectSingleValue($ref['column'])
							->from($ref['table'])
							->where($ref['column'], '=', $this->id)
							->single();
			
			if($exists) {
				break;
			}
		}
		
		$this->staleAt = $exists ? null : new DateTime();
		
		if(!$this->save()) {
			throw new \Exception("Couldn't save blob");
		}
		return isset($this->staleAt);
	}
	
	/**
	 * Create from temporary file
	 * 
	 * @param \go\core\fs\File $file
	 * @return \self
	 */
	public static function fromTmp(File $file) {
		$hash = bin2hex(sha1_file($file->getPath(), true));
		$blob = self::findById($hash);
		if (empty($blob)) {
			$blob = new self();
			$blob->id = $hash;
			$blob->size = $file->getSize();
			$blob->staleAt = new DateTime("+1 hour");
		}
		$blob->name = $file->getName();
		$blob->tmpFile = $file->getPath();
		$blob->type = $file->getContentType();
		$blob->modifiedAt = $file->getModifiedAt();
		return $blob;
	}
	
	/**
	 * Create from string
	 * 
	 * @param string $string
	 * @return \self
	 */
	public static function fromString($string) {
		$hash = bin2hex(sha1($string, true));
		$blob = self::findById($hash);
		if (empty($blob)) {
			$blob = new self();
			$blob->id = $hash;
			$blob->size = mb_strlen($string, '8bit');
			$blob->strContent = $string;
			$blob->staleAt = new DateTime("+1 hour");
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

	/**
	 * Return file system path of blob data
	 * 
	 * @return string
	 */
	public function path() {
		$dir = substr($this->id,0,2) . '/' .substr($this->id,2,2). '/';
		return GO()->getDataFolder()->getPath() . '/data/'.$dir.$this->id;
	}
	
	/**
	 * Get blob data as file system file object
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
	
	/**
	 * Parse blob id's inserted as images in HTML content.
	 * 
	 * @param string $html
	 * @return string[] Array of blob ID's
	 */
	public static function parseFromHtml($html) {
		if(!preg_match_all('/<img .*?cid="(.*?)".*?>/i', $html, $matches)) {
			return [];
		}
		
		return array_unique($matches[1]);
	}

}
