<?php

namespace go\core\fs;

use Exception;
use go\core\db\Table;
use go\core\jmap\Request;
use go\core\orm\exception\SaveException;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\orm;
use go\core\util\DateTime;

use go\core\webclient\Extjs3;
use PDO;
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

	private $hardLink = false;

	private $strContent;


	protected function init()
	{
		parent::init();

		if($this->isNew()) {
			$this->staleAt = new DateTime("+1 hour");
		}
	}


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
	 */
	public static function getReferences(): array
	{
		return Table::getInstance("core_blob")->getReferences();
	}

	/**
	 * Check if this blob is used in a database table
	 *
	 * It uses foreign key relations to check this.
	 *
	 * @return boolean
	 */
	public function isUsed(): bool
	{
		//TODO: logo must be referenced somewhere. maybe core_settings was a bad idea because it's not relational.
		if($this->id == go()->getSettings()->logoId) {
			return true;
		}

		$refs = $this->getReferences();	

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
	 * Finds blobs that have no references anymore and are older than one hour
	 *
	 * @return self[]|Query
	 */
	public static function findStale() {
		$refs = Blob::getReferences();

		foreach($refs as $ref) {
			$q = 	(new Query())
				->select($ref['column'].' as blobId')
				->from($ref['table'])
				->where($ref['column'], '!=', null);

			if(!isset($refsQuery)) {
				$refsQuery = $q;
			} else {
				$refsQuery->union($q);
			}
		}

		return static::find()
			->join($refsQuery, 'refs', 'b.id = refs.blobId', 'left')
			->where('refs.blobId is null')
			->andWhere('staleAt < now()');
	}



	/**
	 * Create from file.
	 *
	 * The Blob needs to be save after calling this function.
	 *
	 * @param File $file
	 * @param bool $removeFile
	 * @return self
	 * @throws Exception
	 */
	public static function fromFile(File $file, bool $hardLink = false): Blob
	{
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

		if(strlen($blob->type) > 127) {
			go()->warn("Invalid content type given: " . $blob->type);

			$blob->type = 'application/octet-stream';
		}

		$blob->modifiedAt = $file->getModifiedAt();
		$blob->hardLink = $hardLink;

		return $blob;
	}

	/**
	 * Create from temporary file.
	 *
	 * The Blob needs to be save after calling this function. This will remove the temporary file!
	 *
	 * @param File $file
	 * @return self
	 * @throws Exception
	 */
	public static function fromTmp(File $file): Blob
	{
		$blob = self::fromFile($file);
		$blob->removeFile = true;
		return $blob;
	}

	/**
	 * Create from string
	 *
	 * @example
	 * ```
	 * $blob = Blob::fromString(json_encode($jsonArray, JSON_PRETTY_PRINT));
	 * $blob->name = $params['entity'] . '.json';
	 * $blob->type = 'json';
	 * $success = $blob->save();
	 * ```
	 *
	 * @param string $string
	 * @return self
	 */
	public static function fromString(string $string): Blob
	{
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
	
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()->addTable('core_blob', 'b');
	}

	/**
	 * @return MetaData
	 */
	public function getMetaData(): MetaData
	{
		return new MetaData($this);
	}

	protected function insertTableRecord(Table $table, array $record)
	{
		$stmt = go()->getDbConnection()->insertIgnore($table->getName(), $record);
		if (!$stmt->execute()) {
			throw new Exception("Could not execute insert query");
		}
	}

	protected function internalSave(): bool
	{
		if (!is_dir(dirname($this->path()))) {
			mkdir(dirname($this->path()), 0775, true);
		}
		if (!file_exists($this->path())) { 
			if (!empty($this->tmpFile)) {

				$tempFile = new File($this->tmpFile);

				if($this->removeFile) {
					$tempFile->move(new File($this->path()));
				} else if($this->hardLink) {
					$tempFile->link(new File($this->path()));
				} else {
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
	 * @param Query $query
	 * @return boolean
	 * @throws SaveException
	 * @throws Exception
	 */
	protected static function internalDelete(Query $query): bool
	{
		$ids = [];
		$paths = [];

		$blobs = Blob::find()->mergeWith($query);

		foreach($blobs as $blob) {
			if($blob->id != go()->getSettings()->logoId) {
				$ids[] = $blob->id;
				$paths[] = $blob->path();
			}
		}

		if(empty($ids)) {
			go()->debug("No blobs to delete");
			return true;
		}

		//for performance use Id's gathered above
		$justIds = (new Query)->where(['id' => $ids]);
		if(parent::internalDelete($justIds)) {

			foreach($paths as $path) {
				if(is_file($path)) {
					unlink($path);

					go()->debug("GC unlink blob: " . $path);
				}
			}
			return true;
		}	
		
		return false;
	}

	/**
	 * Return file system path of blob data
	 *
	 * @return string
	 * @throws Exception
	 */
	public function path(): string
	{
		return self::buildPath($this->id);
	}

	/**
	 * @throws Exception
	 */
	static function buildPath($id): string
	{
		$dir = substr($id,0,2) . DIRECTORY_SEPARATOR .substr($id,2,2). DIRECTORY_SEPARATOR;
		return go()->getDataFolder()->getPath() . DIRECTORY_SEPARATOR . 'data'. DIRECTORY_SEPARATOR . $dir . $id;
	}

	/**
	 * Get blob data as file system file object
	 *
	 * @return File
	 * @throws Exception
	 */
	public function getFile(): File
	{
		return new File($this->path());
	}
	
	/**
	 * Get a blob URL
	 * 
	 * @param string $blobId
	 * @return string
	 */
	public static function url(string $blobId, $relative = false): string
	{
		return ($relative ? Extjs3::get()->getRelativeUrl() : go()->getSettings()->URL) . 'api/download.php?blob=' . $blobId;
	}

	/**
	 * Parse blob id's inserted as images in HTML content.
	 *
	 * @param ?string $html
	 * @param bool $checkIfExists Verify if the blob exists in the database
	 * @return string[] Array of blob ID's
	 */
	public static function parseFromHtml(?string $html, bool $checkIfExists = false): array
	{
//		if(!preg_match_all('/<img [^>]*src="[^>]*\?blob=([^>"]*)"[^>]*>/i', $html, $matches)) {
//			return [];
//		}

		if(empty($html)) {
			return [];
		}

		$matches = [];

		if(preg_match_all('/blob=([^>&"]*)"[^>]*>/i', $html, $urlMatches)) {
			$matches = $urlMatches[1];
		}

		if(preg_match_all('/data-blob-id="([^"]*)"/', $html, $dataBlobIdMatches)){
			$matches = array_merge($matches, $dataBlobIdMatches[1]);
		}

		$matches =  array_unique($matches);

		if($checkIfExists) {
			$matches = array_filter($matches, function($blobId) {
				return Blob::exists($blobId);
			});
		}
		return $matches;
	}

	public static function parseTmpFilesFromHtml(?string &$html) {
		if(empty($html)) {
			return [];
		}

		$blobs = [];

		if(preg_match_all('/<img [^>]*src="(.*core\/downloadTempFile[^>]+path=([^"&]+)[^"]*)"/', $html, $matches)) {
			for($i = 0; $i < count($matches[0]); $i ++) {
				$file =go()->getTmpFolder()->getFile(go()->getUserId().'/'.urldecode($matches[2][$i]));
				if($file->exists()) {
					$blob = self::fromFile($file, true);
					$blob->save();
					$html = str_replace($matches[1][$i], self::url($blob->id), $html);
					$blobs[] = $blob->id;
				}
			}
		}

		return $blobs;

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
	 *  $blob = \go\core\fs\Blob::findById($blobId);
	 *
	 *  $img = \Swift_EmbeddedFile::fromPath($blob->getFile()->getPath());
	 *  $img->setContentType($blob->type);
	 *  $contentId = $this->embed($img);
	 *  $body = \go\core\fs\Blob::replaceSrcInHtml($body, $blobId, $contentId);
	 * }
	 *
	 * @param string $html The HTML subject
	 * @param string $blobId The blob ID to find and replace
	 * @param string $src
	 * @return string Replaced HTML
	 */
	public static function replaceSrcInHtml(string $html, string $blobId, string $src): string
	{
//		$replaced =  preg_replace('/(<img [^>]*src=")[^>]*blob='.$blobId.'("[^>]*>)/i', '$1'.$src.'$2', $html);

		return preg_replace_callback('/<img [^>]*' . $blobId . '[^>]*>/i', function($matches) use ($src) {
			return preg_replace('/src="[^"]*"/i', 'src="' .$src .'"', $matches[0]);
		}, $html);
	}

	/**
	 * Output for download
	 * @throws Exception
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

	public static function atypicalApiProperties(): array
	{
		return array_merge(parent::atypicalApiProperties(), ['file']);
	}


	/**
	 * Iterates all blobs on disk and checks if they are in the database
	 *
	 * @param bool $delete
	 * @return void
	 * @throws Exception
	 */
	public static function removeMissingFromFilesystem(bool $delete = false) {
		$folder = go()->getDataFolder()->getFolder("data");

		foreach($folder->getFolders() as $level1) {
			foreach($level1->getFolders() as $level2) {
				foreach($level2->getFiles() as $file) {
					$blobId = $file->getName();

					echo $blobId . ": ";

					$blob = static::findById($blobId);

					echo $blob ? "found" : "NOT FOUND";

					if(!$blob && $delete) {
						$file->delete();
					}

					echo "\n";
				}
			}
		}
	}
}
