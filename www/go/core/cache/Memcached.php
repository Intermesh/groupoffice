<?php
namespace go\core\cache;

use go\core\exception\ConfigurationException;


/**
 * Cache implementation that uses serialized objects in files on disk.
 * The cache is persistent across requests.
 *
 * @example config
 *
 * ```
 * $config['cache'] = \go\core\cache\Memcached::class;
 * $config['cacheMemcachedHost'] = 'memcached';
 * $config['cacheEntities'] = true;
 * $config['cacheMemcachedPort'] = 11211;
 * ```
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Memcached implements CacheInterface {


	private $prefix;
	private $cache;

	/**
	 * @var \Memcached
	 */
	private $mem;
	
	public function __construct() {
		$this->prefix = go()->getConfig()['db_name'];
		$this->mem = new \Memcached();

		if(!isset(go()->getConfig()['cacheMemcachedHost'])) {
			throw new ConfigurationException("'cacheMemcachedHost' is required in config.php");
		}

		$this->mem->addServer(go()->getConfig()['cacheMemcachedHost'], go()->getConfig()['cacheMemcachedPort'] ?? 11211);
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

		//don't set false values because unserialize returns false on failure.
		if ($key === false) {
			return true;
		}


		if($persist) {
			$this->mem->add($this->prefix . '-' .$key, $value, $ttl);
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

		if(isset($this->cache[$key])) {
			return $this->cache[$key];
		}

		$value = $this->mem->get($this->prefix . '-' .$key);
		
		if($this->mem->getResultCode() == \Memcached::RES_NOTFOUND) {
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
		unset($this->cache[$key]);
		$this->mem->delete($this->prefix . '-' . $key);
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
		$this->cache = [];

		$this->mem->flush();
	}

	public function __destruct() {
		if ($this->flushOnDestruct) {
			$this->flush(false);
		}
	}

	public static function isSupported() {
		return extension_loaded('memcached');
	}
}
