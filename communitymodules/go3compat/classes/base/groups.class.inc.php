<?php
/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @version $Id: groups.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package go.basic
 */

/**
 * This class is used to manage user groups
 * 
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: groups.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @copyright Copyright Intermesh
 * @package go.basic
 * @since Group-Office 2.05
 * 
 * @uses base_groups
 */


class GO_GROUPS extends db
{
	function groupnames_to_ids($groupnames)
	{

		$groupids = array();
		foreach($groupnames as $groupname)
		{
			if($group = $this->get_group_by_name($groupname))
			{
				$groupids[]=$group['id'];
			}
		}
		return $groupids;
	}

	/**
	 * Delete's a group
	 *
	 * @param	int			$group_id	The group ID to delete
	 * @access public
	 * @return bool		True on success
	 */
	function delete_group($group_id)
	{
		if($this->clear_group($group_id))
		{
			global $GO_SECURITY;
			if($GLOBALS['GO_SECURITY']->delete_group($group_id))
			{
				return $this->query("DELETE FROM go_groups WHERE id='".$this->escape($group_id)."'");
			}
		}
		return false;
	}

	/**
	 * Removes all go_users from a group
	 *
	 * @param	int			$group_id	The group ID to reset
	 * @access public
	 * @return bool		True on success
	 */
	function clear_group($group_id)
	{
		return $this->query("DELETE FROM go_users_groups WHERE group_id='".$this->escape($group_id)."'");
	}

	/**
	 * Add's a user to a group
	 *
	 * @param	int			$user_id	The user ID to add
	 * @param	int			$group_id	The group ID to add the user to
	 * @access public
	 * @return bool		True on success
	 */
	function add_user_to_group($user_id, $group_id)
	{
		if ( $user_id )
		{
			return $this->query("INSERT IGNORE INTO go_users_groups (user_id,group_id)".
	 			 " VALUES ($user_id, $group_id)");
		}
		return false;
	}

	/**
	 * Delete's a user to a group
	 *
	 * @param	int			$user_id	The user ID to delete
	 * @param	int			$group_id	The group ID to remove the user from
	 * @access public
	 * @return bool		True on success
	 */

	function delete_user_from_group($user_id, $group_id)
	{
		return $this->query("DELETE FROM go_users_groups WHERE".
			" user_id='".$this->escape($user_id)."' AND group_id='".$this->escape($group_id)."'");
	}

	/**
	 * Get a group's properties in an array
	 *
	 * @param	int			$group_id	The group ID to query
	 * @access public
	 * @return mixed		Array with properties or false
	 */
	function get_group($group_id)
	{
		$this->query("SELECT * FROM go_groups WHERE id='".$this->escape($group_id)."'");

		if($this->next_record())
		return $this->record;
		else
		return false;
	}

	/**
	 * Set the name of a group
	 *
	 * @param	string	$name			The new name of the group
	 * @param	int			$group_id	The group ID to query
	 * @access public
	 * @return mixed		Array with properties or false
	 */
	function update_group($group_id, $name,$admin_only=-1)
	{
		$group['id']=$group_id;
		$group['name']=$name;
		if($admin_only>-1)
			$group['admin_only']=$admin_only;

		return $this->update_row('go_groups', 'id', $group);
	}

	/**
	 * Get a group's properties in an array
	 *
	 * @param	int			$group_id	The group ID to query
	 * @access public
	 * @return mixed		Array with properties or false
	 */
	function get_group_by_name($name)
	{
		$this->query("SELECT * FROM go_groups WHERE name='".$this->escape($name)."'");
		if ($this->next_record())
		{
			return $this->record;
		}else
		{
			return false;
		}
	}

	/**
	 * Add's a group
	 *
	 * @param	int			$user_id	The owner user ID
	 * @param	string	$name			The name of the new group
	 * @access public
	 * @return int			The new group ID or false;
	 */
	function add_group($user_id, $name, $admin_only=0, $acl_id=0)
	{
		GLOBAL $GO_SECURITY;

		$group['id'] = $this->nextid("go_groups");		
		$group['user_id']=$user_id;
		$group['name']=$name;
		$group['acl_id']=$acl_id ? $acl_id : $GLOBALS['GO_SECURITY']->get_new_acl('group', $group['user_id']);
		$group['admin_only']=$admin_only;
			
		if($this->insert_row('go_groups', $group))
		{
			return $group['id'];
		}else
		{
			return false;		
		}
	}

	/**
	 * Check's if a user owns a group
	 *
	 * @param	int			$user_id	The user ID
	 * @param	int			$group_id	The group ID
	 * @access public
	 * @return bool

	 function user_owns_group($user_id, $group_id)
	 {
	 $this->query("SELECT user_id FROM go_groups WHERE user_id='$user_id' AND".
	 " id='$group_id'");
	 if ($this->num_rows() > 0)
	 {
	 return true;
	 }else
	 {
	 return false;
	 }
	 }*/

	/**
	 * Check's if a user is a member of a group
	 *
	 * @param	int			$user_id	The user ID
	 * @param	int			$group_id	The group ID
	 * @access public
	 * @return bool
	 */
	function is_in_group($user_id, $group_id)
	{
		$sql = "SELECT user_id FROM go_users_groups WHERE".
      " user_id='".$this->escape($user_id)."' AND group_id='".$this->escape($group_id)."'";
		$this->query($sql);

		return ($this->num_rows() > 0);
	}

	/**
	 * Get's all members of a group
	 *
	 * @param	int			$group_id	The group
	 * @param	string	$sort			The field to sort on
	 * @param	string	$direction	The sort direction (ASC/DESC)
	 * @access public
	 * @return int			Number of go_users in the group
	 */

	//$query, $field, $user_id=0, $start=0, $offset=0, $sort="name", $sort_direction='ASC'
	function get_users_in_group($group_id, $start = 0, $offset = 0, $sort="name", $direction = "ASC", $query='')
	{
		if ($sort == 'name' || $sort == 'go_users.name')
		{
			if(!isset($_SESSION['GO_SESSION']['sort_name']) ||  $_SESSION['GO_SESSION']['sort_name'] == 'first_name')
			{
				$sort = 'first_name '.$direction.', last_name';
			}else
			{
				$sort = 'last_name '.$direction.', first_name';
			}
		}

		$sql = "SELECT SQL_CALC_FOUND_ROWS go_users.id, go_users.email, go_users.first_name, go_users.middle_name , go_users.last_name, go_users.username FROM".
			" go_users INNER JOIN go_users_groups ON (go_users.id = go_users_groups.user_id)".
			" WHERE go_users_groups.group_id='".$this->escape($group_id)."' ";

		if(!empty($query))
		{
			$keywords = explode(' ', $query);

			if(count($keywords)>1)
			{
				foreach($keywords as $keyword)
				{
					$sql_keywords[] = "go_users.last_name LIKE '%".$this->escape($keyword)."%'";
				}

				$sql .= 'AND ('.implode(' AND ', $sql_keywords).') ';
			}else {
				$sql .= "AND CONCAT(go_users.first_name,go_users.middle_name,go_users.last_name) LIKE '%".$this->escape($query)."%' ";
			}
		}

		$sql .="ORDER BY ".$sort." ".$direction;

		
		if ($offset != 0)
		{
			$sql .= " LIMIT ".intval($start).",".intval($offset);
		}
		$this->query($sql);
			
		return $this->found_rows();
	}

	/**
	 * Check's if a user is allowed to view the group.
	 *	The user must be owner of member to see it.
	 *
	 * @param	int			$user_id	The user ID
	 * @param	int			$group_id	The group ID
	 * @access public
	 * @return bool

	 function group_is_visible($user_id, $group_id)
	 {
	 if ($this->user_owns_group($user_id, $group_id)
	 || $this->is_in_group($user_id, $group_id))
	 return true;
	 else
	 return false;
	 }*/

	function get_authorized_groups($user_id=0, $start = 0, $offset = 0, $sort="name", $direction = "ASC", $query='')
	{
		$sql = "SELECT g.*,u.username, u.first_name, u.middle_name, u.last_name FROM go_groups g ".
  	"INNER JOIN go_users u ON g.user_id=u.id ";

		if($user_id > 0)
		{
			$sql .= "INNER JOIN go_acl a ON (g.acl_id = a.acl_id AND (a.user_id=".$this->escape($user_id)." OR a.group_id IN (".implode(',',$GLOBALS['GO_SECURITY']->get_user_group_ids($user_id))."))) ";
		}
		
		if(!empty($query)){
			$sql .= ' AND name LIKE "'.$query.'" ';
		}

		$sql .= 'GROUP BY g.id ORDER BY '.$sort.' '.$direction;
		$this->query($sql);

		$count = $this->num_rows();

		if ($offset != 0)
		{
			$sql .= " LIMIT ".intval($start).",".intval($offset);
			$this->query($sql);
		}

		return $count;
	}

  /**
   * Get Groups from a given user
   */
  function get_groupnames_by_user($user_id)
  {
    $sql = "SELECT go_groups.name FROM go_groups INNER JOIN go_users_groups ON go_groups.id=go_users_groups.group_id ".
            "AND go_users_groups.user_id='".$this->escape($user_id)."';";
    
    $this->query($sql);
    
    $return = array();
    
    while($this->next_record())
      $return[] = $this->record['name'];
    
    return $return;
  }
  
  
  
	/**
	 * Get's all go_groups. If a user ID is specified it returns only the go_groups
	 *	that user is a member of.
	 *
	 * @access public
	 * @return int	Number of go_groups
	 */
	function get_groups($user_id=0, $start = 0, $offset = 0, $sort="name", $direction = "ASC", $query='')
	{
		$sql = "SELECT go_groups.*,go_users.username, go_users.first_name, go_users.middle_name, go_users.last_name FROM go_groups ".
  	"INNER JOIN go_users ON go_groups.user_id=go_users.id ";

		if($user_id > 0)
		{
			global $GO_CONFIG;
			
			$sql .= "INNER JOIN go_users_groups ON go_groups.id=go_users_groups.group_id ".
							"AND go_users_groups.user_id='".$this->escape($user_id)."' ".
							"AND go_groups.id!=".$GLOBALS['GO_CONFIG']->group_everyone." ".
							"AND go_groups.admin_only!='1'";
		}
		
		
		if(!empty($query)){
			$sql .= ' AND go_groups.name LIKE "'.$query.'" ';
		}

		$sql .= 'ORDER BY '.$sort.' '.$direction;

		$this->query($sql);

		$count = $this->num_rows();

		if ($offset != 0)
		{
			$sql .= " LIMIT ".intval($start).",".intval($offset);
			$this->query($sql);
		}

		return $count;
	}

	/**
	 * Get's all authorised go_groups for a user. User's can only see go_groups if they
	 *	are owner or member of the group
	 *
	 * @param	int	$user_id
	 * @access public
	 * @return int	Number of go_groups

	 function get_authorised_groups($user_id)
	 {
	 $sql = "SELECT go_groups.* FROM go_groups, go_users_groups".
	 " WHERE ((groups.user_id='$user_id')".
	 " OR (go_users_groups.user_id='$user_id'".
	 " AND go_users_groups.group_id=groups.id))".
	 " GROUP BY go_groups.id ORDER BY go_groups.id ASC";
	 $this->query($sql);
	 return $this->num_rows();
	 } */

	/**
	 * Search for a visible user for another user.
	 *
	 * @param	string	$query	The keyword to search on
	 *	@param	string	$field	The database field to search on
	 * @param	int			$user_id	The user_id to search for (Permissions)
	 * @param	int			$start	The first record to return
	 * @param	int			$offset	The number of records to return
	 * @access public
	 * @return int			The number of records returned
	 */
	function search($query, $field, $user_id, $start=0, $offset=0)
	{
		$sql = "SELECT go_users.* FROM go_users, go_users_groups INNER ".
			"JOIN go_acl ON go_users.go_acl_id= go_acl.go_acl_id WHERE ".
			"((go_acl.group_id = go_users_groups.group_id ".
			"AND go_users_groups.user_id = ".$this->escape($user_id).") OR (".
			"go_acl.user_id = ".$this->escape($user_id)." )) AND $field ".
			"LIKE '".$this->escape($query)."' ".
			"GROUP BY go_users.id ORDER BY name ASC";

		if ($offset != 0)	$sql .= " LIMIT ".intval($start).",".intval($offset);

		$this->query($sql);
		return $this->num_rows();
	}

	/**
	 * Called when a user is deleted
	 *
	 * @param	int			$user_id	The user ID that is about to be deleted
	 * @access private
	 * @return bool		True on success
	 */

	function delete_user($user_id)
	{					
		$sql = "DELETE FROM go_users_groups WHERE user_id='".$this->escape($user_id)."'";
		$this->query($sql);
		$sql = "SELECT id FROM go_groups WHERE user_id='".$this->escape($user_id)."'";
		$this->query($sql);
		$del = new GO_GROUPS();
		while ($this->next_record())
		{
			$del->delete_group($this->f("id"));
		}
	}
}
