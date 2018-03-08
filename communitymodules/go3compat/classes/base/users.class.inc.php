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
 * @version $Id: users.class.inc.php 19784 2016-01-26 13:56:16Z michaelhart86 $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package go.basic
 */


/**
 * This class is used to manage users in Group-Office.
 * 
 * @copyright Copyright Intermesh
 * @version $Id: users.class.inc.php 19784 2016-01-26 13:56:16Z michaelhart86 $
 * @author Merijn Schering <mschering@intermesh.nl>
 
 * @package go.basic
 * @since Group-Office 2.05
 * 
 * @uses db
 */
class GO_USERS extends db
{	
/**
	 * Updates the session data corresponding to the user_id.
	 * 
	 * @access public
	 * 
	 * @param int $user_id
	 * 
	 * @return bool
	 */
	function update_session( $userdata , $update_language=false) {
		global $GO_LANGUAGE, $GO_CONFIG;

		if(!is_array($userdata)){
			$userdata = $this->get_user($userdata);

			if(!$userdata)
				return false;
		}

		$middle_name = $userdata['middle_name'] == '' ? '' : $userdata['middle_name'].' ';

		if($update_language && $GLOBALS['GO_LANGUAGE']->language != $userdata['language'])
		{
			$userdata['language'] = $up_user['language'] = $GLOBALS['GO_LANGUAGE']->language;
			$up_user['id']=$userdata['id'];

			$this->update_row('go_users', 'id', $up_user);
		}else
		{
			$GLOBALS['GO_LANGUAGE']->set_language($userdata['language']);
		}

		$_SESSION['GO_SESSION']['user_id'] = $userdata['id'];

		$_SESSION['GO_SESSION']['username'] = $userdata['username'];
		$_SESSION['GO_SESSION']['name'] = trim($userdata['first_name'].' '.$middle_name.$userdata['last_name']);
//		$_SESSION['GO_SESSION']['company'] = $userdata['company'];
//		$_SESSION['GO_SESSION']['function'] = $userdata['function'];
//		$_SESSION['GO_SESSION']['department'] = $userdata['department'];
//
//		$_SESSION['GO_SESSION']['first_name'] = $userdata['first_name'];
//		$_SESSION['GO_SESSION']['middle_name'] = $userdata['middle_name'];
//		$_SESSION['GO_SESSION']['last_name'] = $userdata['last_name'];
//		$_SESSION['GO_SESSION']['country'] = $userdata['country'];
		$_SESSION['GO_SESSION']['email'] = $userdata['email'];
//		$_SESSION['GO_SESSION']['work_phone'] = $userdata['work_phone'];
//		$_SESSION['GO_SESSION']['home_phone'] = $userdata['home_phone'];

		$_SESSION['GO_SESSION']['thousands_separator'] = $userdata['thousands_separator'];
		if($_SESSION['GO_SESSION']['thousands_separator']=='')
			$_SESSION['GO_SESSION']['thousands_separator']=' ';
		$_SESSION['GO_SESSION']['decimal_separator'] = $userdata['decimal_separator'];
		$_SESSION['GO_SESSION']['date_format'] = Date::get_dateformat($userdata['date_format'], $userdata['date_separator']);
		$_SESSION['GO_SESSION']['date_separator'] = $userdata['date_separator'];
		$_SESSION['GO_SESSION']['time_format'] = $userdata['time_format'];
		$_SESSION['GO_SESSION']['currency'] = $userdata['currency'];
		$_SESSION['GO_SESSION']['lastlogin'] = isset ($userdata['lastlogin']) ? $userdata['lastlogin'] : time();
		$_SESSION['GO_SESSION']['max_rows_list'] = $userdata['max_rows_list'];
		$_SESSION['GO_SESSION']['timezone'] = $userdata['timezone'];
		$_SESSION['GO_SESSION']['start_module'] = isset ($userdata['start_module']) ? $userdata['start_module'] : 'summary';

		//$_SESSION['GO_SESSION']['language'] = $userdata['language'];

		$theme_changed = !isset($_SESSION['GO_SESSION']['theme']) || $userdata['theme'] != $_SESSION['GO_SESSION']['theme'];

		$_SESSION['GO_SESSION']['theme'] = $userdata['theme'];
		$_SESSION['GO_SESSION']['mute_sound'] = $userdata['mute_sound'];
    $_SESSION['GO_SESSION']['mute_reminder_sound'] = $userdata['mute_reminder_sound'];
    $_SESSION['GO_SESSION']['mute_new_mail_sound'] = $userdata['mute_new_mail_sound'];
		$_SESSION['GO_SESSION']['popup_reminders'] = $userdata['popup_reminders'];
    $_SESSION['GO_SESSION']['show_smilies'] = $userdata['show_smilies'];
		$_SESSION['GO_SESSION']['first_weekday'] = $userdata['first_weekday'];
		$_SESSION['GO_SESSION']['sort_name'] = !empty($userdata['sort_name']) ? $userdata['sort_name'] : 'last_name';

		$_SESSION['GO_SESSION']['list_separator'] = $userdata['list_separator'];
		$_SESSION['GO_SESSION']['text_separator'] = $userdata['text_separator'];

		

		if($theme_changed){
			require_once($GLOBALS['GO_CONFIG']->class_path.'base/theme.class.inc.php');
			$GO_THEME = new GO_THEME();
			$GO_THEME->set_theme();
		}


		return true;
	}

	/**
   * This function returns an array of the fields that can be used as search
   * criterias for users.
   * 
   * @access public 
   * @param void 
   * @return array
   */
	function get_search_fields() {
		
		global $lang;

		$searchfields[] = array( '',  $lang['common']['SearchAll'] );
		$searchfields[] = array( 'first_name',  $lang['common']['firstName'] );
		$searchfields[] = array( 'last_name',   $lang['common']['lastName'] );
		$searchfields[] = array( 'email',	    $lang['common']['email'] );
		$searchfields[] = array( 'company',	    $lang['common']['company'] );
		$searchfields[] = array( 'department',  $lang['common']['department'] );
		$searchfields[] = array( 'function',    $lang['common']['function'] );
		$searchfields[] = array( 'address',	    $lang['common']['address'] );
		$searchfields[] = array( 'city',	    $lang['common']['city'] );
		$searchfields[] = array( 'zip',	    $lang['common']['zip'] );
		$searchfields[] = array( 'state',	    $lang['common']['state'] );
		$searchfields[] = array( 'country',	    $lang['common']['country'] );
		$searchfields[] = array( 'work_address',$lang['common']['workAddress'] );
		$searchfields[] = array( 'work_cip',    $lang['common']['workZip'] );
		$searchfields[] = array( 'work_city',   $lang['common']['workCity'] );
		$searchfields[] = array( 'work_state',  $lang['common']['workState'] );
		$searchfields[] = array( 'work_country',$lang['common']['workCountry'] );
		return $searchfields;
	}

	/**
   * This function searches for users with the given search field.
   * 
   * @access public
   * 
   * @param StringHelper $query
   * @param StringHelper $field
   * @param int $user_id
   * @param int $start
   * @param int $offset
   * 
   * @return array
   */
	
	function search($query, $field, $user_id=0, $start=0, $offset=0, $sort="name", $sort_direction='ASC', $search_operator='LIKE')
	{
		global $GO_MODULES;		
		
		if($sort == 'name')
		{
			if(!isset($_SESSION['GO_SESSION']['sort_name']) || $_SESSION['GO_SESSION']['sort_name'] == 'last_name')
			{
				$sort = 'last_name '.$sort_direction.', first_name ';
			}else
			{
				$sort = 'first_name '.$sort_direction.', last_name ';
			}
		}
		
		$where=false;

		$sql = "SELECT";

		if($offset>0){
			$sql .= ' SQL_CALC_FOUND_ROWS';
		}

		$sql .= " u.id,u.username,u.first_name,u.middle_name,u.last_name,u.logins,u.lastlogin,u.ctime,u.email,u.enabled";

		if($user_id > 0)
		{
			
			$sql .=" FROM go_users u INNER JOIN go_acl a ON (u.acl_id = a.acl_id AND (a.user_id=".intval($user_id)." OR a.group_id IN (".implode(',',$GLOBALS['GO_SECURITY']->get_user_group_ids($user_id))."))) ";
			
		}else
		{		
			$sql .= " FROM go_users u ";
		}
		
		if($query!='')
		{
			$sql .= $where ? " AND " : " WHERE ";
			
			if(!is_array($field))
			{
				$fields=array();
				if($field == '')
				{
					$fields[]='u.username';
					$fields[]='name';
					$fields[]='email';
					
					
				}else {
					$fields[]=$field;
				}
			}else {
				$fields=$field;
			}
			
			foreach($fields as $field)
			{
				if(count($fields)>1)
				{
					if(isset($first))
					{
						$sql .= ' OR ';
					}else
					{
						$first = true;
						$sql .= '(';
					}				
				}
				
				if($field=='name')
				{
					$sql .= "CONCAT(first_name,middle_name,last_name) $search_operator '".$this->escape(str_replace(' ','%', $query))."' ";
				}else
				{
					$sql .= "$field $search_operator '".$this->escape($query)."' ";
				}
			}
			if(count($fields)>1)
			{
				$sql .= ')';
			}
		}	

	 	$sql .= " GROUP BY u.id ORDER BY $sort $sort_direction";
		
		if ($offset != 0)
		{
			$sql .= " LIMIT ".intval($start).",".intval($offset);			
		}
		$this->query($sql);

		go_debug($sql);

		return $offset > 0 ? $this->found_rows() : $this->num_rows();
	}
	
	function get_linked_users($user_id, $link_id)
	{
		global $GO_CONFIG;
		require_once($GLOBALS['GO_CONFIG']->class_path.'base/links.class.inc.php');
		$GO_LINKS = new GO_LINKS();

		$links = $GO_LINKS->get_links($link_id, 8);
		
		if(count($links))
		{
			$sql = "SELECT go_users.* FROM go_users  INNER JOIN go_acl ON go_users.acl_id = go_acl.acl_id ".
				"LEFT JOIN go_users_groups ON go_acl.group_id = go_users_groups.group_id WHERE (go_acl.user_id=".intval($user_id)." ".
				"OR go_users_groups.user_id=".intval($user_id).") AND link_id IN (".implode(',',$links).") ORDER BY last_name ASC, first_name ASC";
			
			$this->query($sql);
			return $this->num_rows();
		}
		return 0;
	}

	/**
	 * Fetch all users from the user management backend.
	 * 
	 * This function retrieves all users from the database and returns their
	 * number. After that you are able to process each user via next_record.
	 * 
	 * @access public
	 * 
	 * @param StringHelper $sort The field to sort on
	 * @param StringHelper $direction The sort direction
	 * @param int $start Return results starting from this row
	 * @param int $offset Return this number of rows
	 * 
	 * @return int The number of users
	 */

	function get_users($sort="name",$direction="ASC", $start=0, $offset=0)
	{
		if ($sort == 'name')
		{
			if(!isset($_SESSION['GO_SESSION']['sort_name']) ||  $_SESSION['GO_SESSION']['sort_name'] == 'first_name')
			{
				$sort = 'first_name '.$direction.', last_name';
			}else
			{
				$sort = 'last_name '.$direction.', first_name';
			}
			//      $sort = 'first_name '.$direction.', last_name';
		}
		$count=0;
		$this->query("SELECT id FROM go_users");
		if ($this->next_record())
		{
			$count = $this->num_rows();
		}

		if ($count > 0)
		{
			$sql = "SELECT * FROM go_users ORDER BY ".$sort." ".$direction;

			if ($offset != 0)
			{
				$sql .= " LIMIT ".intval($start).",".intval($offset);
			}
			$this->query($sql);
		}
		return $count;
	}

	/**
	 * This function retrieves all users that are visible to a user.
	 * 
	 * This function fetches all users that should be visible to the given
	 * user. next_record() can be used to iterate over the result set.
	 * 
	 * @access public
	 * 
	 * @param StringHelper $sort The field to sort on
	 * @param StringHelper $direction The sort direction
	 * @param int $start Return results starting from this row
	 * @param int $offset Return this number of rows
	 * 
	 * @return int The number of users
	 */
	function get_authorized_users($user_id, $sort="name",$direction="ASC")
	{
		if ($sort == 'go_users.name' || $sort=='name')
		{
			if($_SESSION['GO_SESSION']['sort_name'] == 'first_name')
			{
				$sort = 'go_users.first_name '.$direction.', go_users.last_name';
			}else
			{
				$sort = 'go_users.last_name '.$direction.', go_users.first_name';
			}
			//      $sort = 'users.first_name '.$direction.', go_users.last_name';
		}
		$sql = "SELECT DISTINCT go_users.* FROM go_users ".
		"INNER JOIN go_acl ON go_users.acl_id= go_acl.acl_id ".
		"LEFT JOIN go_users_groups ON (go_acl.group_id = go_users_groups.group_id) ".
		"WHERE go_users_groups.user_id=".intval($user_id)." OR ".
		"go_acl.user_id = ".$this->escape($user_id)." ORDER BY ".$sort." ".$direction;

		$this->query($sql);
		return $this->num_rows();
	}

	/**
	 * This function searches for a user by his email address.
	 * 
	 * This function retrieves all userdata based on the users email address.
	 * 
	 * @access public
	 * 
	 * @param StringHelper $email The e-mail address of a user
	 * 
	 * @return array
	 */
	function get_user_by_email($email)
	{
		$email = String::get_email_from_string($email);
		$sql = "SELECT * FROM go_users WHERE email LIKE '".$this->escape($email)."'";
		$this->query($sql);
		
		//return false if there is more then one result
		if($this->num_rows()!=1)
		{
			return false;
		}elseif ($this->next_record(DB_ASSOC))
		{
			return $this->record;
		}
		
	}
	/**
	 * This function returns all userdata based on the user's name.
	 * 
	 * @access public
	 * 
	 * @param int $user_id The user to check access for
	 * @param StringHelper $username
	 * 
	 * @return array The user profile
	 */
	function get_authorized_user_by_email($user_id, $email)
	{
		$sql = "SELECT DISTINCT go_users.* FROM go_users ".
		"INNER JOIN go_acl ON go_users.acl_id= go_acl.acl_id ".
		"LEFT JOIN go_users_groups ON (go_acl.group_id = go_users_groups.group_id) ".
		"WHERE (go_users_groups.user_id=".intval($user_id)." OR ".
		"go_acl.user_id = ".$this->escape($user_id).") AND email='".$this->escape($email)."'";
		$this->query($sql);
		if ($this->next_record(DB_ASSOC))
		{
			return $this->record;
		}
		return false;
	}

	/**
	 * This function checks if the password the user supplied is valid.
	 * 
	 * @access public
	 * 
	 * @param StringHelper $password
	 * 
	 * @return bool
	 
	function check_password($password)
	{
		$this->query("SELECT id FROM go_users WHERE password='".md5($password).
		"' AND id='".$_SESSION['GO_SESSION']['user_id']."'");
		if ($this->num_rows() > 0)
		{
			return true;
		}
		return false;
	}*/

	public function get_user_realname($user_id){
		
		$user_id = intval($user_id);

		if(!isset($this->cached_realnames[$user_id])){
			$sql = "SELECT first_name, middle_name, last_name FROM go_users WHERE id=".$user_id;
			$this->query($sql);
			$record = $this->next_record();
			
			$this->cached_realnames[$user_id]='';
			if($record)
				$this->cached_realnames[$user_id]=String::format_name($record);
			else
				$this->cached_realnames[$user_id]='Removed ID: '.$user_id;
		}

		return $this->cached_realnames[$user_id];
	}

	/**
	 * This function searches for a user by his ID andreturns all userdata based on the users ID.
	 * 
	 * @access public	
	 * @param int $user_id 
	 * @return array
	 */
	function get_user($user_id)
	{
		$sql = "SELECT * FROM go_users WHERE id='".$this->escape($user_id)."'";
		$this->query( $sql );
		if ($this->next_record(DB_ASSOC))
		{
			if($this->record['date_separator']=='')
			{
				$this->record['date_separator']=' ';
			}			
			return $this->record;
		}		
		return false;
	}

	/**
	 * This function updates all userdata based on the given parameters.
	 * 
	 * @access public
	 *
	 * @return bool True on success
	 */

	function update_user(
	$user,
	$user_groups=null,
	$visible_user_groups=null,
	$modules_read=null,
	$modules_write=null)
	{
		global $GO_MODULES, $GO_SECURITY, $GO_CONFIG;

		require_once($GLOBALS['GO_CONFIG']->class_path.'base/groups.class.inc.php');
		$GO_GROUPS = new GO_GROUPS();

		if($this->update_profile($user))
		{
			//make sure we have user['acl_id']
			$user = $this->get_user($user['id']);
			
			if(isset($modules_read) && isset($modules_write)){
				$GLOBALS['GO_MODULES']->get_modules();
				while ($mod = $GLOBALS['GO_MODULES']->next_record())
				{
					$level = 0;
					if(in_array($mod['id'], $modules_write)){
						$level = GO_SECURITY::WRITE_PERMISSION;
					}elseif(in_array($mod['id'], $modules_read)){
						$level = GO_SECURITY::READ_PERMISSION;
					}

					if ($level)
					{
						if(!$GLOBALS['GO_SECURITY']->has_permission($user['id'], $mod['acl_id']))
						{
							$GLOBALS['GO_SECURITY']->add_user_to_acl($user['id'], $mod['acl_id'], $level);
						}
					} else {
						if($GLOBALS['GO_SECURITY']->user_in_acl($user['id'], $mod['acl_id']))
						{
							$GLOBALS['GO_SECURITY']->delete_user_from_acl($user['id'], $mod['acl_id']);
						}
					}
				}
			}

			


			$GO_GROUPS->get_groups();
			$groups2 = new GO_GROUPS();
			while($GO_GROUPS->next_record())
			{
				if(isset($user_groups))
				{
					$is_in_group = $groups2->is_in_group($user['id'], $GO_GROUPS->f('id'));
					$should_be_in_group = in_array($GO_GROUPS->f('id'), $user_groups);

					if ($is_in_group && !$should_be_in_group)
					{
						$groups2->delete_user_from_group($user['id'], $GO_GROUPS->f('id'));
					}

					if (!$is_in_group && $should_be_in_group)
					{
						$groups2->add_user_to_group($user['id'], $GO_GROUPS->f('id'));
					}
				}

				if(isset($visible_user_groups))
				{
					$group_is_visible = $GLOBALS['GO_SECURITY']->group_in_acl($GO_GROUPS->f('id'), $user['acl_id']);
					$group_should_be_visible = in_array($GO_GROUPS->f('id'), $visible_user_groups);

					if ($group_is_visible && !$group_should_be_visible)
					{
						$GLOBALS['GO_SECURITY']->delete_group_from_acl($GO_GROUPS->f('id'), $user['acl_id']);
					}

					if (!$group_is_visible  && $group_should_be_visible)
					{
						$GLOBALS['GO_SECURITY']->add_group_to_acl($GO_GROUPS->f('id'), $user['acl_id']);
					}
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * This function updates a the profile of a user.
	 * 
	 * Using an SQL update record, this function actualizes the profile of the
	 * given user.
	 * 
	 * 
	 * @access protected
	 * 
	 * @param Array $user is an array of all data that should be updated.
	 * 
	 * @return Boolean to indicate the success of the operation.
	 */
	function update_profile($user, $complete_profile=false)
	{
		global $GO_EVENTS, $GO_SECURITY;
		
		$user['mtime']=time();

		$params = array($user);
		
		$ret = false;
		if(!empty($user['password']))
		{
			$user['password']=crypt($user['password']);
			$user['password_type']='crypt';
		}
		
		if($this->update_row('go_users', 'id', $user))
		{
			if(isset($_SESSION['GO_SESSION']['user_id']) && $user['id'] == $_SESSION['GO_SESSION']['user_id'])
			{
				$ret = $this->update_session($user['id']);				
			}
			$ret = true;
		}
		
		$this->cache_user($user['id']);
		
		if($complete_profile)
		{
			$user=$this->get_user($user['id']);
			if(isset($params[0]['password']))
			{
				$user['password']=$params[0]['password'];
			}
			$params = array($user, $user['password']);
			$GLOBALS['GO_EVENTS']->fire_event('add_user', $params);
		}else
		{
			$GLOBALS['GO_EVENTS']->fire_event('update_user', $params);
		}
		
		return $ret;
	}
	/**
	 * This function updates the user's password.
	 * 
	 * @access public
	 * 
	 * @param int $user_id
	 * @param string $password
	 * 
	 * @return bool True on success
	 
	
	function update_password($user_id, $password)
	{
		global $GO_EVENTS;
		
		$sql = "UPDATE go_users SET password='".md5($password)."' WHERE id='$user_id'";
		if ($this->query($sql))
		{
			$GLOBALS['GO_EVENTS']->fire_event('change_user_password', array($user_id, $password));
			
			return true;
		}
		return false;
	} */
	/**
	 * This function returns all userdata based on the user's name.
	 * 
	 * @access public
	 * 
	 * @param StringHelper $username
	 * 
	 * @return array The user profile
	 */
	function get_user_by_username($username)
	{
		$sql = "SELECT * FROM go_users WHERE username='".$this->escape($username)."'";
		$this->query($sql);
		if ($this->next_record())
		{
			return $this->record;
		}
		return false;
	}
	/**
	 * This function checks, if there is already a user with the given email
	 * address.
	 * 
	 * @access public
	 * 
	 * @param StringHelper $email
	 * 
	 * @return bool True if exists
	 */
	function email_exists($email)
	{
		$sql = "SELECT id FROM go_users WHERE email='".$this->escape($email)."'";
		$this->query($sql);
		if ($this->num_rows() > 0)
		{
			return true;
		}
		return false;
	}
	
	/**
	 * Check if a string is valid to use for a username
	 *
	 * @param StringHelper $username
	 * @return bool true if valid
	 */
	function check_username($username)
	{
		return preg_match('/^[A-Za-z0-9_\-\.\@]*$/', $username);
	}

	/**
	 * This function adds a new user to the database.
	 * 
	 * @access public
	 * 
	 * @param StringHelper $user Array of all columns of table 'go_users'
	 * @param StringHelper $user_groups The user group id's the user will be member of
	 * @param StringHelper $visible_user_groups The user group id's where the user will be visible to
	 * @param StringHelper $modules_read The modules the user will have read permissions for
	 * @param StringHelper $modules_write The modules the user will have write permissions for
	 * @param StringHelper $acl	Some custom ACL id's the user will have access to (Be careful)

	 * 
	 * @return bool True on success
	 */

	function add_user(
	&$user,
	$user_groups=array(),
	$visible_user_groups=array(),
	$modules_read=array(),
	$modules_write=array(),
	$acl=array(),
	$send_invitation=false
	)
	{

		global $GO_CONFIG, $GO_LANGUAGE, $GO_SECURITY, $GO_MODULES, $GO_EVENTS, $lang;

		require_once($GLOBALS['GO_CONFIG']->class_path.'base/groups.class.inc.php');
		$GO_GROUPS = new GO_GROUPS();

		$GLOBALS['GO_LANGUAGE']->require_language_file('users');

		if(empty($user['username']) || empty($user['email']))
		{
			throw new Exception($lang['common']['missingField']);
		}

		// We check if we are able to add a new user. If we already have too much
		// of them we do not want new ones ;)
		if ( $this->max_users_reached() ) {
			throw new Exception($lang['users']['max_users_reached']);
		}

		if (!String::validate_email($user['email'])) {
			throw new Exception($lang['users']['error_email']);
		}


		if (!$this->check_username($user['username'])) {
			throw new Exception($lang['users']['error_username']);
		}

		// We check if a user with this email address already exists. Since the
		// email address is used as key for the acl_id, no two users may have the
		// same address. It also should not be possible to have multiple users
		// with the same name...

		if(!$GLOBALS['GO_CONFIG']->allow_duplicate_email)
		{
			$this->query( "SELECT email,username,id FROM go_users WHERE email='".$this->escape($user['email'])."' OR username='".$this->escape($user['username'])."'");
			if ($existing = $this->next_record()) {
				if($existing['email']==$user['email']){
					throw new Exception($lang['users']['error_email_exists']);

				}else{
					throw new Exception($lang['users']['error_username_exists']);
				}
			}
		}else
		{
			$this->query( "SELECT id FROM go_users WHERE username='".$this->escape($user['username'])."'");
			if ($existing = $this->next_record()) {
				throw new Exception($lang['users']['error_username_exists']);
			}
		}

		if(!isset($user['enabled']))
			$user['enabled']='1';		
		
		if(!isset($user['start_module']))
			$user['start_module']='summary';
		
		if(!isset($user['language']))
	 		$user['language'] = $GLOBALS['GO_LANGUAGE']->language;

	 		
		if(!isset($user['currency']))
	 		$user['currency'] = $GLOBALS['GO_CONFIG']->default_currency;
	 		
	 	if(!isset($user['decimal_separator']))
			$user['decimal_separator'] = $GLOBALS['GO_CONFIG']->default_decimal_separator;
			
		if(!isset($user['thousands_separator']))
			$user['thousands_separator'] = $GLOBALS['GO_CONFIG']->default_thousands_separator;
			
		if(!isset($user['time_format']))
			$user['time_format'] = $GLOBALS['GO_CONFIG']->default_time_format;
			
		if(!isset($user['date_format']))
			$user['date_format'] = $GLOBALS['GO_CONFIG']->default_date_format;
			
		if(!isset($user['date_separator']))
			$user['date_separator'] = $GLOBALS['GO_CONFIG']->default_date_separator;
		
		if(!isset($user['first_weekday']))
			$user['first_weekday'] = $GLOBALS['GO_CONFIG']->default_first_weekday;
			
		if(!isset($user['timezone']))
			$user['timezone'] = $GLOBALS['GO_CONFIG']->default_timezone;
		
		if(!isset($user['theme']))
			$user['theme'] = $GLOBALS['GO_CONFIG']->theme;
			
		if(!isset($user['max_rows_list']))
			$user['max_rows_list'] = 20;
			
		if(!isset($user['sex']))			
			$user['sex'] = 'M';

		if(!isset($user['sort_name']))
			$user['sort_name'] = $GLOBALS['GO_CONFIG']->default_sort_name;



		if (empty($user['id'])){
			$user['id'] = $this->nextid("go_users");
		}
		
		
		// When the acl_id is already given, we do not have to create a new one,
		// but it may be neccessary to change the owner of the acl - this is
		// needed when the authentication framework "accidentially" creates the
		// acl id for this user (which happens in the case, when the user is
		// authenticated against an LDAP directory, where the id is generated
		// when the LDAP entry is converted to the $user entry, which is given
		// as parameter to this function).
		if ( isset( $user['acl_id'] ) ) {
			$GLOBALS['GO_SECURITY']->set_acl_owner( $user['acl_id'], $user['id'] );
		} else {
			$user['acl_id'] = $GLOBALS['GO_SECURITY']->get_new_acl( $user['email'] );
		}		
		
		$user['ctime'] = $user['mtime']=time();

		$random_password=false;
		$user['password_type']='crypt';
		if(empty($user['password'])){
			$user['password']=$this->random_password();
			$random_password=true;
		}	

		//random password is used by serverclient. It won't try to add an e-mail account
		//with a random password
		$GLOBALS['GO_EVENTS']->fire_event('before_add_user', array($user, $random_password));
		
		$unencrypted_password = $user['password'];
		if(!empty($user['password']))
		{
			$unencrypted_password = $user['password'];
			$user['password'] = crypt($user['password']);
		}
	
		if(isset($GLOBALS['GO_MODULES']->modules['files']))
		{
			require_once($GLOBALS['GO_MODULES']->modules['files']['class_path'].'files.class.inc.php');
			$files = new files();
			
			$usersdir = $files->resolve_path('users',true, 1);
			$admindir = $files->resolve_path('adminusers',true, 1);
		
			$files->mkdir($usersdir, $user['username'], $user['id'], $user['id'],true,'1','1');
			$folder = $files->mkdir($admindir, $user['username'], 1, 1,true,'1');
			if($folder)
				$user['files_folder_id']=$folder['id'];			
		}
		

		if ($user['id'] > 0 && $this->insert_row('go_users', $user))
		{
			
			$this->cache_user($user['id']);
			
			$GLOBALS['GO_SECURITY']->set_acl_owner( $user['acl_id'], $user['id'] );
			$GO_GROUPS->add_user_to_group( $user['id'], $GLOBALS['GO_CONFIG']->group_everyone);

			foreach($user_groups as $group_id)
			{
				if($group_id > 0 && $group_id != $GLOBALS['GO_CONFIG']->group_everyone && !$GO_GROUPS->is_in_group($user['id'], $group_id))
				{
					$GO_GROUPS->add_user_to_group($user['id'], $group_id);
				}
			}
			foreach($visible_user_groups as $group_id)
			{
				if($group_id > 0 && !$GLOBALS['GO_SECURITY']->group_in_acl($group_id, $user['acl_id']))
				{
					$GLOBALS['GO_SECURITY']->add_group_to_acl($group_id, $user['acl_id']);
				}
			}

			foreach($modules_read as $module_name)
			{
				$module = $GLOBALS['GO_MODULES']->get_module($module_name);
				if($module)
				{
					$GLOBALS['GO_SECURITY']->add_user_to_acl($user['id'], $module['acl_id'], GO_SECURITY::READ_PERMISSION);
				}
			}

			foreach($modules_write as $module_name)
			{
				$module = $GLOBALS['GO_MODULES']->get_module($module_name);
				if($module)
				{
					$GLOBALS['GO_SECURITY']->add_user_to_acl($user['id'], $module['acl_id'], GO_SECURITY::WRITE_PERMISSION);
				}
			}

			foreach($acl as $acl_id)
			{
				if(!$GLOBALS['GO_SECURITY']->user_in_acl($user['id'], $acl_id))
				{
					$GLOBALS['GO_SECURITY']->add_user_to_acl($user['id'], $acl_id);
				}
			}
			
			$user['password']=$unencrypted_password;

			//delay add user event because name must be supplied first.
			if(!empty($user['first_name']))
			{
				$GLOBALS['GO_EVENTS']->fire_event('add_user', array($user, $random_password));
			}

			if($send_invitation){
				require_once($GLOBALS['GO_CONFIG']->class_path.'mail/GoSwift.class.inc.php');
				require_once($GLOBALS['GO_MODULES']->modules['users']['class_path'].'users.class.inc.php');
				$users = new users();

				$email = $users->get_register_email();

				$swift = new GoSwift($user['email'], $email['register_email_subject']);
				foreach($user as $key=>$value){
					$email['register_email_body'] = str_replace('{'.$key.'}', $value, $email['register_email_body']);
				}

				$email['register_email_body']= str_replace('{url}', $GLOBALS['GO_CONFIG']->full_url, $email['register_email_body']);
				$email['register_email_body']= str_replace('{title}', $GLOBALS['GO_CONFIG']->title, $email['register_email_body']);
				$swift->set_body($email['register_email_body'],'plain');
				$swift->set_from($GLOBALS['GO_CONFIG']->webmaster_email, $GLOBALS['GO_CONFIG']->title);
				$swift->sendmail();
			}

			return $user['id'];
		} else {
			$GLOBALS['GO_SECURITY']->delete_acl( $user['id'] );
		}
	
		return false;
	}
	/**
	 * This function tells us if we exceeded the maximum number of users if set in
	 * config.php
	 * 
	 * @access public
	 * 
	 * @param void
	 * 
	 * @return bool
	 */
	function max_users_reached()
	{
		global $GO_CONFIG;

		if($this->get_users() < $GLOBALS['GO_CONFIG']->max_users || $GLOBALS['GO_CONFIG']->max_users == 0)
		{
			return false;
		}else
		{
			return true;
		}
	}
	/**
	 * This function deletes a user from the database.
	 * 
	 * @access public
	 * 
	 * @param int $user_id
	 * 
	 * @return bool
	 */
	function delete_user($user_id)
	{
		global $GO_CONFIG,$GO_SECURITY, $GO_EVENTS;

		require_once($GLOBALS['GO_CONFIG']->class_path.'base/groups.class.inc.php');
		$GO_GROUPS = new GO_GROUPS();

		if($user = $this->get_user($user_id))
		{
			$acl_id = $this->f("acl_id");
			$username = $this->f("username");
			$sql = "DELETE FROM go_users WHERE id='".$this->escape($user_id)."'";
			if ($this->query($sql))
			{
				$GO_GROUPS->delete_user($user_id);
				$GLOBALS['GO_SECURITY']->delete_acl($acl_id);
				$GLOBALS['GO_SECURITY']->delete_user($user_id);
				
				require_once($GLOBALS['GO_CONFIG']->class_path.'base/search.class.inc.php');
				$search = new search();
				
				$search->delete_search_result($user_id, 8);		

				$args=array($user);
				
				$GLOBALS['GO_EVENTS']->fire_event('user_delete', $args);

				$sql = "DELETE FROM go_acl WHERE user_id=".intval($user_id).";";
				$this->query($sql);

				return true;
			}
		}
		throw new Exception('An error has occured while deleting the user');
	}

	function increment_logins( $user_id ) {
		$sql =  "UPDATE go_users SET logins=logins+1, lastlogin='".time().
		"' WHERE id='$user_id'";
		$this->query( $sql );
	}
	
	/**
	 * This function generates a randomized password.
	 * 
	 * @access public
	 * 
	 * @param StringHelper $characters_allow
	 * @param StringHelper $characters_disallow
	 * @param int $password_length
	 * @param int $repeat
	 * 
	 * @return StringHelper
	 */
	function random_password( $characters_allow = 'a-z,1-9', $characters_disallow = 'i,o', $password_length = 0, $repeat = 0 ) {
		return String::random_password($characters_allow,$characters_disallow,$password_length,$repeat);
	}
	
/**
	 * When a global search action is performed this function will be called for each module
	 *
	 * @param int $last_sync_time The time this function was called last
	 */

	

	function cache_user($user_id)
	{
		global $GO_MODULES, $GO_CONFIG, $GO_LANGUAGE;
		
		require_once($GLOBALS['GO_CONFIG']->class_path.'/base/search.class.inc.php');
		$search = new search();

		require($GLOBALS['GO_LANGUAGE']->get_language_file('users'));

		$sql = "SELECT DISTINCT *  FROM go_users WHERE id=?";
		$this->query($sql, 'i', $user_id);
		$record = $this->next_record();
		if($record)
		{	
			$cache['id']=$this->f('id');
			$cache['user_id']=1;
			$cache['name'] = htmlspecialchars(String::format_name($this->f('last_name'),$this->f('first_name'),$this->f('middle_name')), ENT_QUOTES, 'utf-8');;
			$cache['link_type']=8;
			$cache['description']='';
			$cache['type']=$us_user;
			$cache['keywords']=$search->record_to_keywords($this->record).','.$cache['type'];
			$cache['mtime']=$this->f('mtime');
			$cache['module']='users';
			$cache['acl_id']=$GLOBALS['GO_MODULES']->modules['users']['acl_id'];
			
			$search->cache_search_result($cache);
		}
	}
}
