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
 * Simple key value store that caches on disk.
 * 
 * @version $Id: File.class.inc.php 7607 2011-06-15 09:17:42Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Merijn Schering <mschering@intermesh.nl> 
 * @package GO.base.cache
 */

namespace GO\Base\Cache;


class Apcu implements CacheInterface{
	
	private $_prefix;
	
	public function __construct() {
		$this->_prefix=\GO::config()->db_name.'-';
	}

	/**
	 * Store any value in the cache
	 * @param StringHelper $key
	 * @param mixed $value Will be serialized
	 * @param int $ttl Seconds to live
	 */
	public function set($key, $value, $ttl=0){
		return apcu_store ($this->_prefix.$key , $value, $ttl );
	}
	
	/**
	 * Get a value from the cache
	 * 
	 * @param StringHelper $key
	 * @return boolean 
	 */
	public function get($key){
		
		return apcu_fetch($this->_prefix.$key);
	}
	
	/**
	 * Delete a value from the cache
	 * 
	 * @param StringHelper $key 
	 */
	public function delete($key){
		apcu_delete($this->_prefix.$key);
	}
	/**
	 * Flush all values 
	 */
	public function flush(){
		apcu_clear_cache();
	}
	
	public function supported(){
		return function_exists("apcu_store");
	}
	

}
