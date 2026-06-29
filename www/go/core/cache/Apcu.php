<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace go\core\cache;


use APCUIterator;
use Exception;
use go\core\Environment;
use go\core\http\Client;

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
	private $keepInMemory = true;

	public function disableMemory():void {
		$this->cache = [];
		$this->keepInMemory = false;
		$this->getDiskCache()->disableMemory();
	}

	public function freeMemory(array $preserveKeys = ['entity-types']):void {
		$this->cache = array_intersect_key($this->cache, array_flip($preserveKeys));
	}
	
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
			$this->disk->disableMemory();
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
	public function set(string $key, $value, bool $persist = true, int $ttl = 0):void
	{
		if($this->keepInMemory) {
			$this->cache[$key] = $value;
		}

		if(!$this->apcuEnabled) {
			$this->getDiskCache()->set($key, $value, $persist, $ttl);
			return;
		}
		if($persist) {
			apcu_store($this->prefix . '-' .$key, $value, $ttl);
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

		if($this->keepInMemory && isset($this->cache[$key])) {
			return $this->cache[$key];
		}

		if(!$this->apcuEnabled) {
			return $this->getDiskCache()->get($key);
		}

		$success = false;
		
		$value = apcu_fetch($this->prefix . '-' .$key, $success);
		
		if(!$success) {
			return null;
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
		//		apcu_clear_cache();
		if(apcu_enabled()) {
			apcu_delete(new APCUIterator('/^' . preg_quote($this->prefix, '/') . '-/'));
		} else if(Environment::get()->isCli() && go()->isInstalled() && !empty(go()->getSettings()->URL)) {
			$http = new Client();
			$http->setOption(CURLOPT_SSL_VERIFYHOST, false);
			$http->setOption(CURLOPT_SSL_VERIFYPEER, false);

			$http->get(go()->getSettings()->URL . '/install/clearcache.php');
		}

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
