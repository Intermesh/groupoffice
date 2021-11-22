<?php

namespace go\core\fs;

use Exception;
use go\core\App;
use go\core\db\Table;
use go\core\exception\ConfigurationException;
use go\core\orm\Query;
use go\core\orm;
use go\core\util\DateTime;

use ReflectionException;
use function GO;

/**
 * Blob entity
 * 
 * Group Office has a BLOB system to store files. When uploading a file a unique 
 * hash is calculated for the file to identify it. So when the same file is 
 * stored more than once in Group Office it will only be saved to disk once. You 
 * don’t have to worry about uploading or downloading the data. Because this has 
 * already been implemented for you.
 * 
 * In the database you must store the BLOB id in a BINARY (40) type column.
 * 
 * Warning
 * It’s very important that a foreign key constraint is defined for the BLOB id 
 * when it’s used in a table. Because the garbage collection mechanism uses 
 * these keys to determine if a BLOB is stale and to be cleaned up. In other 
 * words if you don’t do this your BLOB data will be removed automatically.
 * 
 * A blob can be downloaded with download.php?blob=HASH. It can be uploaded via
 * upload.php with HTTP.
 * 
 * @link https://groupoffice-developer.readthedocs.io/en/latest/blob.html
 */
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
	private $removeFile = true;
	private $strContent;


	/**
	 * Get all table columns referencing the core_blob.id column.
	 *
	 * It uses the 'information_schema' to read all foreign key relations.
	 * So it's important that every blob is saved in a column with a 'RESTRICT'
	 * foreign key relation to core_blob.id. For example:
	 *
	 * ```
	 * ALTER TABLE `addressbook_contact`
	 *    ADD CONSTRAINT `addressbook_contact_ibfk_2` FOREIGN KEY (`photoBlobId`) REFERENCES `core_blob` (`id`);
	 * ```
	 * @link https://groupoffice-developer.readthedocs.io/en/latest/blob.html
	 * @return array [['table'=>'foo', 'column' => 'blobId']]
	 * @throws ConfigurationException
	 */
	public static function getReferences() {
		
		$refs = go()->getCache()->get("blob-refs");
		if($refs === null) {
			$dbName = go()->getDatabase()->getName();
			go()->getDbConnection()->exec("USE information_schema");
			
			try {
				//somehow bindvalue didn't work here
				$sql = "SELECT `TABLE_NAME` as `table`, `COLUMN_NAME` as `column` FROM `KEY_COLUMN_USAGE` where table_schema=" . go()->getDbConnection()->getPDO()->quote($dbName) . " and referenced_table_name='core_blob' and referenced_column_name = 'id'";
				$stmt = go()->getDbConnection()->query($sql);
				$refs = $stmt->fetchAll(\PDO::FETCH_ASSOC);		
			}
			finally{
				go()->getDbConnection()->exec("USE `" . $dbName . "`");		
			}	
			
			go()->getCache()->set("blob-refs", $refs);			
		}		
		
		return $refs;
	}

	/**
	 * Check if this blob is used in a database table
	 *
	 * It uses foreign key relations to check this.
	 *
	 * @return boolean
	 * @throws Exception
	 */
	public function isUsed() {

		//TODO: logo must be referenced somewhere. maybe core_settings was a bad idea because it's not relational.
		if($this->id == go()->getSettings()->logoId) {
			return true;
		}

		$refs = $this->getReferences();	
		
		$exists = false;
		foreach($refs as $ref) {
			$exists = (new Query)
							->selectSingleValue($ref['column'])
							->from($ref['table'])
							->where($ref['column'], '=', $this->id)
							->single();
			
			if($exists) {
				return true;
			}
		}
		
		return false;
	}

	/**
	 * Set the blob stale if it's not used in any of the referencing tables.
	 *
	 * @return bool true if blob is stale
	 * @throws Exception
	 */
	public function setStaleIfUnused() {		
		$this->staleAt = $this->isUsed() ? null : new DateTime();
		
		if(!$this->save()) {
			throw new Exception("Couldn't save blob");
		}
		return isset($this->staleAt);
	}

	/**
	 * Create from file.
	 *
	 * The Blob needs to be save after calling this function.
	 *
	 * @param File $file
	 * @return self
	 * @throws ReflectionException
	 */
	public static function fromFile(File $file, $removeFile = false) {
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

		if(strlen($blob->type) > 127) {
			go()->warn("Invalid content type given: " . $blob->type);

			$blob->type = 'application/octet-stream';
		}

		$blob->modifiedAt = $file->getModifiedAt();
		$blob->removeFile = $removeFile;
		return $blob;
	}

	/**
	 * Create from temporary file.
	 *
	 * The Blob needs to be save after calling this function. This will remove the temporary file!
	 *
	 * @param File $file
	 * @return self
	 * @throws ReflectionException
	 */
	public static function fromTmp(File $file) {
		return self::fromFile($file, true);
	}

	/**
	 * Create from string
	 *
	 * @param string $string
	 * @return self
	 * @throws ReflectionException
	 */
	public static function fromString($string) {
		$hash = bin2hex(sha1($string, true));
		$blob = self::findById($hash);
		if (empty($blob)) {
			$blob = new self();
			$blob->id = $hash;
			$blob->size = mb_strlen($string, '8bit');
			$blob->strContent = $string;
			//$blob->staleAt = new DateTime("+1 hour");
		}
		return $blob;
	}
	
	protected static function defineMapping() {
		return parent::defineMapping()->addTable('core_blob', 'b');
	}

	/**
	 * @return MetaData
	 * @throws ReflectionException
	 */
	public function getMetaData() {
		return new MetaData($this);
	}

	protected function insertTableRecord(Table $table, array $record)
	{
		$stmt = go()->getDbConnection()->insertIgnore($table->getName(), $record);
		if (!$stmt->execute()) {
			throw new Exception("Could not execute insert query");
		}
	}

	protected function internalSave() {
		if (!is_dir(dirname($this->path()))) {
			mkdir(dirname($this->path()), 0775, true);
		}
		if (!file_exists($this->path())) { 
			if (!empty($this->tmpFile)) {

				$tempFile = new File($this->tmpFile);

				if($this->removeFile) {
					$tempFile->move(new File($this->path()));
				} else{
					$tempFile->copy(new File($this->path()));					
				}
			} else if (!empty($this->strContent)) {
				file_put_contents($this->path(), $this->strContent);
			}
		}
		
		return parent::internalSave();
	}

	/**
	 * Checks if blob is in use. If it's used it will not delete but return true.
	 * It will remove the file on disk.
	 *
	 * @return boolean
	 * @throws Exception
	 */
	protected static function internalDelete(Query $query) {

		$new = [];
		$paths = [];

		foreach(Blob::find()->mergeWith($query) as $blob) {;
			if(!$blob->isUsed()) {
				$new[] = $blob->id;
				$paths[] = $blob->path();
			} else if(isset($blob->staleAt)) {
				$blob->staleAt = null;
				$blob->save();
			}
		}

		if(empty($new)) {
			return true;
		}

		$query->clearWhere()->andWhere(['id' => $new]);
		
		if(parent::internalDelete($query)) {

			foreach($paths as $path) {
				if(is_file($path)) {
					unlink($path);
				}
			}
			return true;
		}	
		
		return false;
	}
	
	// private $deleteHard = false;
	
	// /**
	//  * Delete without checking isUsed()
	//  * 
	//  * It will throw an PDOException if you call this when it's in use.
	//  * 
	//  * @return true
	//  */
	// public function hardDelete() {
	// 	$this->deleteHard = true;
	// 	return $this->delete();
	// }

	/**
	 * Return file system path of blob data
	 *
	 * @return string
	 * @throws ConfigurationException
	 */
	public function path() {
		return self::buildPath($this->id);
	}

	static function buildPath($id) {
		$dir = substr($id,0,2) . DIRECTORY_SEPARATOR .substr($id,2,2). DIRECTORY_SEPARATOR;
		return go()->getDataFolder()->getPath() . DIRECTORY_SEPARATOR . 'data'. DIRECTORY_SEPARATOR . $dir . $id;
	}

	/**
	 * Get blob data as file system file object
	 *
	 * @return File
	 * @throws ConfigurationException
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
		return go()->getSettings()->URL . 'api/download.php?blob=' . $blobId;
	}
	
	/**
	 * Parse blob id's inserted as images in HTML content.
	 * 
	 * @param string $html
	 * @return string[] Array of blob ID's
	 */
	public static function parseFromHtml($html) {
//		if(!preg_match_all('/<img [^>]*src="[^>]*\?blob=([^>"]*)"[^>]*>/i', $html, $matches)) {
//			return [];
//		}

		$matches = [];

		if(preg_match_all('/"http[^>]*\?blob=([^>"]*)"[^>]*>/i', $html, $urlMatches)) {
			$matches = $urlMatches[1];
		}

		if(preg_match_all('/data-blob-id="([^"]*)"/', $html, $dataBlobIdMatches)){
			$matches = array_merge($matches, $dataBlobIdMatches[1]);
		}

		return array_unique($matches);
	}
	
	/**
	 * Find image tags with a blobId download URL in "src" and replace them with a 
	 * new "src" attribute.
	 * 
	 * Useful when attaching inline images for example:
	 * 
	 * ````
	 * $blobIds = \go\core\fs\Blob::parseFromHtml($body);
	 * foreach($blobIds as $blobId) {
	 * 	$blob = \go\core\fs\Blob::findById($blobId);
	 * 	
	 * 	$img = \Swift_EmbeddedFile::fromPath($blob->getFile()->getPath());
	 * 	$img->setContentType($blob->type);
	 * 	$contentId = $this->embed($img);
	 * 	$body = \go\core\fs\Blob::replaceSrcInHtml($body, $blobId, $contentId);
	 * }
	 * 
	 * @param string $html The HTML subject
	 * @param string $blobId The blob ID to find and replace
	 * @param string $newSrc The new "src" attribute for the blob
	 * @return string Replaced HTML
	 */
	public static function replaceSrcInHtml($html, $blobId, $src) {		
//		$replaced =  preg_replace('/(<img [^>]*src=")[^>]*blob='.$blobId.'("[^>]*>)/i', '$1'.$src.'$2', $html);

		$replaced = preg_replace_callback('/<img [^>]*' . $blobId . '[^>]*>/i', function($matches) use ($src) {
			return preg_replace('/src="[^"]*"/i', 'src="' .$src .'"', $matches[0]);
		}, $html);

		return $replaced;
	}
	
	/**
	 * Output for download
	 */
	public function output($inline = false) {
		$disp = $inline ? 'inline' : 'attachment';

		$this->getFile()->output(true, true, [
			'ETag' => $this->id,
			'Content-Type' => $this->type, 
			"Expires" => (new DateTime("1 year"))->format("D, j M Y H:i:s"),
			'Content-Disposition' => $disp . ';filename="' . $this->name . '"'
					]);
	}

	protected static function atypicalApiProperties()
	{
		return array_merge(parent::atypicalApiProperties(), ['file']);
	}
}
