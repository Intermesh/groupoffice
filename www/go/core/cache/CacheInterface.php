<?php

namespace go\core\cache;

/**
 * Key value cache implementation interface. The cache is persistent accross 
 * requests.
 *
 * 
 * The app instance of the cache is available by calling:
 * 
 * ````````````````````````````````````````````````````````````````````````````
 * \go\core\App::get()->getCache();
 * ````````````````````````````````````````````````````````````````````````````
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
interface CacheInterface {

	/**
	 * Store any value in the cache
	 *
	 * @param string $key
	 * @param mixed $value Will be serialized
	 * @param boolean $persist Cache must be available in next requests. Use false of it's just for this script run.
	 * @param int $ttl Time to live in seconds
	 */
	public function set(string $key, $value, bool $persist = true, int $ttl = 0);

	/**
	 * Get a value from the cache
	 *
	 * Make sure to do a strict check on null to check if it existed. $value === null.
	 *
	 * Never trust that this value will persist. some caches have misses or are cleared on apache restart. Always
	 * expect this to fail sometimes.
	 * 
	 * @param string $key
	 * @return mixed Stored value or NULL if not found  
	 */
	public function get(string $key);

	/**
	 * Delete a value from the cache
	 * 
	 * @param string $key 
	 */
	public function delete(string $key);

	/**
	 * Flush all values 
	 *
	 * @param bool $onDestruct
	 */
	public function flush(bool $onDestruct = true);

	/**
	 * Returns true if this system supports this cache driver
	 * 
	 * @return boolean
	 */
	public static function isSupported(): bool;
}
