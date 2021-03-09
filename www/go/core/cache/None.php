<?php

namespace go\core\cache;

use go\core\cache\CacheInterface;

/**
 * Cache implementation that uses serialized objects in files on disk.
 * The cache is persistent accross requests.
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class None implements CacheInterface {

	private $cache = [];
	
	public function __construct() {
		go()->warn("Not using cache. This will be slow!");
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
		$this->cache[$key] = $value;
		return true;
	}

	/**
	 * Get a value from the cache
	 * 
	 * @param string $key
	 * @return mixed Stored value or NULL if not found 
	 */
	public function get($key) {
		if (isset($this->cache[$key])) {
			return $this->cache[$key];
		}
		return null;
	}

	/**
	 * Delete a value from the cache
	 * 
	 * @param string $key 
	 */
	public function delete($key) {
		unset($this->cache[$key]);
		return true;
	}

	/**
	 * Flush all values 
	 * 
	 * @return boolean
	 */
	public function flush() {
		$this->cache = [];
		return true;
	}

	public static function isSupported() {
		return true;
	}

}
