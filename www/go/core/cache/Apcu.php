<?php
namespace go\core\cache;


/**
 * Cache implementation that uses serialized objects in files on disk.
 * The cache is persistent across requests.
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Apcu implements CacheInterface {


	private $prefix;
	private $cache;

	private $disk;
	
	public function __construct() {
		$this->prefix = go()->getConfig()['core']['db']['name'];
	}


	/**
	 * @return Disk
	 */
	private function getDiskCache() {
		if(!isset($this->disk)) {
			$this->disk = new Disk();
		}

		return $this->disk;
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

		if(PHP_SAPI === 'cli') {
			return $this->getDiskCache()->set($key, $value, $persist, $ttl);
		}

		//don't set false values because unserialize returns false on failure.
		if ($key === false) {
			return true;
		}


		if($persist) {
			apcu_store($this->prefix . '-' .$key, $value, $ttl);
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

		if(PHP_SAPI === 'cli') {
			return $this->getDiskCache()->get($key);
		}

		if(isset($this->cache[$key])) {
			return $this->cache[$key];
		}
		$success = false;
		
		$value = apcu_fetch($this->prefix . '-' .$key, $success);
		
		if(!$success) {
			return null;
		}
		
		$this->cache[$key] = $value;
		return $this->cache[$key];		
	}

	/**
	 * Delete a value from the cache
	 * 
	 * @param string $key 
	 */
	public function delete($key) {
		
		if(PHP_SAPI === 'cli') {
			return $this->getDiskCache()->delete($key);
		}

		unset($this->cache[$key]);
		apcu_delete($this->prefix . '-' . $key);
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
			
			$this->getDiskCache()->flush(true);

			$this->flushOnDestruct = true;
			return true;
		}
		$this->cache = [];
//	var_dump(apcu_cache_info());
		apcu_clear_cache();		

		$this->getDiskCache()->flush(false);
	}

	public function __destruct() {
		if ($this->flushOnDestruct) {
			$this->flush(false);
		}
	}

	public static function isSupported() {
		return extension_loaded('apcu');
	}
}
