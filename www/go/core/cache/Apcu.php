<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace go\core\cache;


use Exception;

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

	private $apcuEnabled = false;

	/**
	 * Keep values in memory as long as the request lives. Disabled for SSE.
	 * @var bool
	 */
	public $keepInMemory = true;
	
	public function __construct() {
		$this->prefix = go()->getConfig()['db_name'];
		$this->apcuEnabled = apcu_enabled();
	}


	/**
	 * @return Disk
	 */
	private function getDiskCache(): Disk
	{
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
	 * @return void
	 * @throws Exception
	 */
	public function set(string $key, $value, bool $persist = true, int $ttl = 0) {

		$this->getDiskCache()->set($key, $value, $persist, $ttl);
		if($persist) {
			apcu_store($this->prefix . '-' .$key, $value, $ttl);
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
	 * Never trust that this value will persist. APCu has misses or is cleared on apache restart. Always
	 * expect this to fail sometimes.
	 * 
	 * @param string $key 
	 * @return mixed null if it doesn't exist
	 */
	public function get(string $key) {

		if(!$this->apcuEnabled) {
			return $this->getDiskCache()->get($key);
		}

		if($this->keepInMemory && isset($this->cache[$key])) {
			return $this->cache[$key];
		}
		$success = false;
		
		$value = apcu_fetch($this->prefix . '-' .$key, $success);
		
		if(!$success) {
			// if acpu fails fallback on disk cache
			return $this->getDiskCache()->get($key);
		}

		if($this->keepInMemory) {
			$this->cache[$key] = $value;
		}
		return $value;
	}

	/**
	 * Delete a value from the cache
	 * 
	 * @param string $key 
	 */
	public function delete(string $key) {
		unset($this->cache[$key]);
		apcu_delete($this->prefix . '-' . $key);
		$this->getDiskCache()->delete($key);
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
		$this->cache = [];
		apcu_clear_cache();

		$this->getDiskCache()->flush(false);
	}

	/**
	 * @throws Exception
	 */
	public function __destruct() {
		if ($this->flushOnDestruct) {
			$this->flush(false);
		}
	}

	public static function isSupported(): bool
	{
		return extension_loaded('apcu');
	}
}
