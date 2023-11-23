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
	/**
	 * Keep values in memory as long as the request lives. Disabled for SSE.
	 * @var bool
	 */
	private $keepInMemory = true;

	public function disableMemory():void {
		$this->cache = [];
		$this->keepInMemory = false;
	}

	/**
	 * @throws Exception
	 */
	public function __construct() {
		$this->folder = go()->getDataFolder()->getFolder('cache/disk');
		$this->folder->create();
	}

	/**
	 * Store any value in the cache
	 *
	 * @param string $key
	 * @param mixed $value Will be serialized
	 * @param boolean $persist Cache must be available in next requests. Use false of it's just for this script run.
	 * @param int $ttl Time to live in seconds
	 * @throws Exception
	 */
	public function set(string $key, $value, bool $persist = true, int $ttl = 0) {

		$key = str_replace('/', '-', $key);

		if($persist) {
			$file = $this->folder->getFile($key);
			if(!$ttl) {
				$data = $value;
			} else{
				$data = ['e' => time() + $ttl, "v" => $value];
			}
			if(!$file->putContents(serialize($data))) {
				throw new Exception("Could not write to cache!");
			}
		}

		if($this->keepInMemory) {
			$this->cache[$key] = $value;
		}
	}

	/**
	 * Get a value from the cache
	 *
	 * Make sure to do a strict check on null to check if it existed. $value === null.
	 * 
	 * @param string $key 
	 * @return mixed null if it doesn't exist
	 */
	public function get(string $key) {

		$key = str_replace('/', '-', $key);

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
				if($v['e'] < time()) {
					$this->delete($key);
					return null;
				} else{
					$v = $v['v'];
				}
			}
			if($this->keepInMemory) {
				$this->cache[$key] = $v;
			}
			return $v;
		}
		catch(Exception $e) {
			ErrorHandler::log("Could not unserialize cache from file " . $key.' error: '. $e->getMessage());
			$this->delete($key);
			return null;
		}


	}

	/**
	 * Delete a value from the cache
	 * 
	 * @param string $key 
	 */
	public function delete(string $key) {
		$key = File::stripInvalidChars($key, '-');
		
		unset($this->cache[$key]);

		$file = $this->folder->getFile($key);
		if($file->exists()) {
			$file->delete();
		}
	}

	private $flushOnDestruct = false;

	/**
	 * Flush all values
	 *
	 * @param bool $onDestruct Delay flush until current script run ends by
	 * default so cached values can still be used. For example cached record
	 * relations will function until the script ends.
	 *
	 * @throws Exception
	 */
	public function flush(bool $onDestruct = true) {

		if ($onDestruct) {
			$this->flushOnDestruct = true;
			return;
		}
		
		go()->debug("Flushing cache");
		
		$this->cache = [];

		//in case previous attempt failed
		$checkExisting = go()->getDataFolder()->getFolder('cache/disktrash');
		if($checkExisting->exists()) {
			$checkExisting->delete();
		}
	
		$f = clone $this->folder;
		if($f->rename('disktrash')) {
			$f->delete();
		}

		$this->folder->create();
		$this->folder->chmod(0777);
		
		$this->flushOnDestruct = false;
	}

	public function __destruct() {
		if ($this->flushOnDestruct) {
			$this->flush(false);
		}
	}

	/**
	 * @throws Exception
	 */
	public static function isSupported(): bool
	{
		$folder = go()->getDataFolder()->getFolder('cache/disktrash');
		
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
