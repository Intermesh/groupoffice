<?php

namespace go\core\cache;

use Exception;
use go\core\App;
use go\core\ErrorHandler;
use go\core\fs\File;

/**
 * Cache implementation that uses serialized objects in files on disk.
 * The cache is persistent across requests.
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Disk implements CacheInterface {


	private $folder;
	
	private $cache;

	public function __construct() {		
		$this->folder = App::get()->getDataFolder()->getFolder('cache2');
		$this->folder->create();
	}	
	/**
	 * Can't use framework settings because it uses the QueryBuilder which uses cache and results in infinte loop.
	 */
	private function getDataFolder() {
		$sql = "SELECT value FROM core_setting s inner join core_module m on s.moduleId = m.id where m.name='core' AND m.package='community' and s.name='dataPath';";
		$pdo = App::get()->getDbConnection()->getPDO();
		$stmt = $pdo->query($sql);
		return new \go\core\fs\Folder($stmt->fetch(\PDO::FETCH_COLUMN, 0));
	}

	/**
	 * Store any value in the cache
	 * 
	 * @param string $key
	 * @param mixed $value Will be serialized
	 * @param boolean $persist Cache must be available in next requests. Use false of it's just for this script run.
	 * @param int $ttl Time to live in seconds
	 */
	public function set($key, $value, $persist = true, $ttl = 0) {

		//go()->debug("CACHE: ". $key);
		//don't set false values because unserialize returns false on failure.
		if ($key === false) {
			return true;
		}
		
		$key = str_replace('\\', '-', $key);

		if($persist) {
			$file = $this->folder->getFile($key);
			if($ttl) {
				$data = $value;
			} else{
				$data = ['e' => time() + $ttl, "v" => $value];
			}
			if(!$file->putContents(serialize($data))) {
				throw new \Exception("Could not write to cache!");
			}
		}
		
		$this->cache[$key] = $value;
	}

	/**
	 * Get a value from the cache
	 *
	 * Make sure to do a strict check on null to check if it existed. $value === null.
	 * 
	 * @param string $key 
	 * @return mixed null if it doesn't exist
	 */
	public function get($key) {
		
		$key = str_replace('\\', '-', $key);

		if(isset($this->cache[$key])) {
			return $this->cache[$key];
		}
		
		$file = $this->folder->getFile($key);

		if (!$file->exists()) {
			return null;
		} 
		
		$serialized = $file->getContents();

		try {
			$v = unserialize($serialized);
			if(is_array($v) && isset($v['e'])) {
				if($v['expires'] < time()) {
					$this->delete($key);
					return null;
				} else{
					$v = $v['v'];
				}
			}
			$this->cache[$key] = $v;
		}
		catch(\Exception $e) {
			ErrorHandler::log("Could not unserialize cache from file " . $key.' data: '.var_export($serialized, true));
			$this->delete($key);
			return null;
		}

		return $this->cache[$key];
	}

	/**
	 * Delete a value from the cache
	 * 
	 * @param string $key 
	 */
	public function delete($key) {
		$key = File::stripInvalidChars($key, '-');
		
		unset($this->cache[$key]);

		$file = $this->folder->getFile($key);
		$file->delete();
	}

	private $flushOnDestruct = false;

	/**
	 * Flush all values 
	 * 
	 * @param bool $onDestruct Delay flush until current script run ends by 
	 * default so cached values can still be used. For example cached record 
	 * relations will function until the script ends.
	 * 
	 * @return bool
	 */
	public function flush($onDestruct = true) {
		
//		throw new \Exception("Flush?");
		if ($onDestruct) {
			$this->flushOnDestruct = true;
			return true;
		}
		
		go()->debug("Flushing cache");
		
		$this->cache = [];
	
		$this->folder->delete();
		$this->folder->create();
		$this->folder->chmod(0777);
		
		$this->flushOnDestruct = false;
		
		return true;
	}

	public function __destruct() {
		if ($this->flushOnDestruct) {
			$this->flush(false);
		}
	}

	public static function isSupported() {
		$folder = App::get()->getSettings()->getDataFolder();
		
		if(!$folder->isWritable()) {
			throw new Exception("diskcache folder is not writable!");
		}
		
		if(!$folder->exists()) {
			$folder->create();
			$folder->chmod(0777);
		}
		
		return true;
	}

}
