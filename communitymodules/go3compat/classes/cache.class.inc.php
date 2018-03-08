<?php
class cache extends db{
	

	/**
	 * Update a cache
	 *
	 * @param Array $cache Associative array of record fields
	 *
	 * @access public
	 * @return bool True on success
	 */

	function save($user_id, $key, $content)
	{		
		$cache['user_id']=$user_id;
		$cache['key']=$key;
		$cache['content']=$content;
		$cache['mtime']=time();
		

		return $this->replace_row('go_cache', $cache);
	}
	
	function cleanup()
	{
		return $this->query("DELETE FROM go_cache WHERE mtime<?", 'i', Date::date_add(time(), -7));
	}
	
	function delete_cache($user_id, $key)
	{
		return $this->query("DELETE FROM go_cache WHERE user_id=? AND `key`=?", 'is', array($user_id, $key));
	}
	
	/**
	 * Gets a cache record
	 *
	 * @param Int $cache_id ID of the cache
	 *
	 * @access public
	 * @return Array Record properties
	 */

	function get_cache($user_id, $key)
	{
		$this->query("SELECT * FROM go_cache WHERE user_id=? AND `key`=?", 'is', array($user_id, $key));
		if($this->next_record())
		{
			return $this->f('content');
		}else
		{
			return false;
		}
	}	
}
?>