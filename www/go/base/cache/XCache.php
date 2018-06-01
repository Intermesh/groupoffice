<?php
namespace GO\Base\Cache;

use GO;

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
class XCache implements CacheInterface{
	
	private $_prefix;
	
	public function __construct() {		
		$this->_prefix=GO::config()->db_name.'-';
	}

	/**
	 * Store any value in the cache
	 * @param StringHelper $key
	 * @param mixed $value Will be serialized
	 * @param int $ttl Seconds to live
	 */
	public function set($key, $value, $ttl=0){
		return xcache_set ($this->_prefix.$key , serialize($value), $ttl );
	}
	
	/**
	 * Get a value from the cache
	 * 
	 * @param StringHelper $key
	 * @return boolean 
	 */
	public function get($key){
		
		$v = xcache_get($this->_prefix.$key);
		
		if($v){
			return unserialize($v);
		}  else {
			
			return null;
		}
	}
	
	/**
	 * Delete a value from the cache
	 * 
	 * @param StringHelper $key 
	 */
	public function delete($key){
		xcache_unset($this->_prefix.$key);
	}
	/**
	 * Flush all values 
	 */
	public function flush(){
		xcache_unset_by_prefix($this->_prefix);
	}
	
	
	public function supported(){
		return function_exists("xcache_get");
	}

}
