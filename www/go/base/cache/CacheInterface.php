<?php
/*
 * 
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 */

/**
 * Abstract class for simple key value store.
 * 
 * @version $Id: File.class.inc.php 7607 2011-06-15 09:17:42Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Merijn Schering <mschering@intermesh.nl> 
 * @package GO.base.cache
 */


namespace GO\Base\Cache;


interface CacheInterface{
	/**
	 * Store any value in the cache
	 * @param StringHelper $key
	 * @param mixed $value Will be serialized
	 * @param int $ttl Seconds to live
	 */
	public function set($key, $value, $ttl=0);
	
	/**
	 * Get a value from the cache
	 * 
	 * @param StringHelper $key
	 * @return boolean 
	 */
	public function get($key);
	
	/**
	 * Delete a value from the cache
	 * 
	 * @param StringHelper $key 
	 */
	public function delete($key);
	
	/**
	 * Flush all values 
	 */
	public function flush();
	
	/**
	 * Returns true if this system supports this cache driver
	 * 
	 * @return boolean
	 */
	public function supported();
}
