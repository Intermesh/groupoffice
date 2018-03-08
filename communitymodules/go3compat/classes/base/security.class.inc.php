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
 * @version $Id: security.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package go.basic
 */

/**
 * This file is used to manage access control lists (ACL).
 *
 * ACL's can be used to secure items in Group-Office like addressbooks, calendars etc.
 *
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: security.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @copyright Copyright Intermesh
 * @package go.basic
 *
 * @uses db
 */

class GO_SECURITY extends db {

	const READ_PERMISSION=10;
	const CREATE_PERMISSION=20;
	const WRITE_PERMISSION=30;
	const DELETE_PERMISSION=40;
	const MANAGE_PERMISSION=50;


/**
 * The user_id of the current logged in user
 *
 * @var     int
 * @access  public
 */
	var $user_id = 0;

	/**
	 * True if admin user
	 *
	 * @var     int
	 * @access  private
	 */
	var $is_admin;


	var $http_authenticated_session=false;


	/**
	 * Constructor. Initialises base class of the security class family
	 *
	 * @access public
	 * @return void
	 */
	function __construct() {
		global $GO_CONFIG;
		parent::__construct();

		if (isset($_SESSION['GO_SESSION']['user_id']) &&
				$_SESSION['GO_SESSION']['user_id'] > 0) {
			$this->user_id=$_SESSION['GO_SESSION']['user_id'];

			$this->http_authenticated_session=!empty($_SESSION['GO_SESSION']['http_authenticated_user']);

		}
	}

	/**
	 * All URL's from the ExtJS requests contain an extra parameter called
	 * "auth_token". This token can be compared with the stored session variable
	 * to prevent Cross-site request forgeries:
	 * http://www.owasp.org/index.php/Cross-Site_Request_Forgery_(CSRF)
	 */

	function check_token(){
		global $GO_CONFIG;

		if(!$GLOBALS['GO_CONFIG']->disable_security_token_check && $_REQUEST['security_token']!=$_SESSION['GO_SESSION']['security_token']){
			$this->logout();
			go_log(LOG_ERR, 'Fatal error: Security token mismatch. Possible cross site request forgery attack!');
			die('Fatal error: Security token mismatch. Possible cross site request forgery attack!');
		}
	}

	/**
	 * Set's a user as logged in. This does NOT log a user in. $GO_AUTH->login()
	 * does that.
	 *
	 * @param	int	$user_id	The ID of the logged in user.
	 * @access public
	 * @return void
	 */
	function logged_in( $user=null ) {
		global $GO_CONFIG;

		if(isset($user)) {

			require_once($GLOBALS['GO_CONFIG']->class_path.'base/users.class.inc.php');
			$GO_USERS = new GO_USERS();

			$GO_USERS->update_session($user, !empty($_POST['login_language']));
			$this->user_id = $user['id'];
		}else {
			if(empty($this->user_id)) {

				if(!empty($_COOKIE['GO_UN']) && !empty($_COOKIE['GO_PW'])) {

					require_once($GLOBALS['GO_CONFIG']->class_path.'cryptastic.class.inc.php');
					$c = new cryptastic();

					$username = $c->decrypt($_COOKIE['GO_UN']);
					$password = $c->decrypt($_COOKIE['GO_PW']);

					//decryption might fail if mcrypt is not installed
					if(!$username){
						$username = $_COOKIE['GO_UN'];
						$password = $_COOKIE['GO_PW'];
					}

					require_once($GLOBALS['GO_CONFIG']->class_path.'base/auth.class.inc.php');
					$GO_AUTH = new GO_AUTH();

					$res =  $GO_AUTH->login($username, $password);

					if(!$res){
						//$this->logout();
						SetCookie("GO_UN","",time()-3600,"/","",!empty($_SERVER['HTTPS']),true);
						SetCookie("GO_PW","",time()-3600,"/","",!empty($_SERVER['HTTPS']),true);
						unset($_COOKIE['GO_UN'],$_COOKIE['GO_PW']);
					}
					return $res;
				}elseif(!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW']) && empty($_SESSION['PHP_AUTH_USER_FAILED']) && !defined("PHP_AUTH_USER_FAILED")) {
					$username = $_SERVER['PHP_AUTH_USER'];
					$password = $_SERVER['PHP_AUTH_PW'];

					require_once($GLOBALS['GO_CONFIG']->class_path.'base/auth.class.inc.php');
					$GO_AUTH = new GO_AUTH();

					if($GO_AUTH->login($username, $password)) {
						go_debug('Logged in using http authentication');
						$this->http_authenticated_session=$_SESSION['GO_SESSION']['http_authenticated_user']=true;
						return true;
					}else
					{
						go_debug('PHP_AUTH_USER is set but http authentication failed');
						//if PHP_AUTH_USER fails we must remember that because otherwise it will endlessly loop.
						$_SESSION['PHP_AUTH_USER_FAILED']=true;
						define("PHP_AUTH_USER_FAILED", true);
					}
				}

				return false;
			}

			return ($this->user_id > 0);
		}
	}

	/**
	 * Log the current user out.
	 *
	 * @access public
	 * @return void
	 */
	function logout() {
		global $GO_CONFIG;

		$username = isset($_SESSION['GO_SESSION']['username']) ? $_SESSION['GO_SESSION']['username'] : 'notloggedin';
		//go_log(LOG_DEBUG, 'LOGOUT Username: '.$username.'; IP: '.$_SERVER['REMOTE_ADDR']);
		go_infolog("LOGOUT for user: \"".$username."\" from IP: ".$_SERVER['REMOTE_ADDR']);

		if(!empty($this->user_id)){
			require_once($GLOBALS['GO_CONFIG']->class_path.'filesystem.class.inc');
			$fs = new filesystem();

			$length = -strlen($this->user_id)-1;

			//go_debug(substr($GLOBALS['GO_CONFIG']->tmpdir,$length));

			if(substr($GLOBALS['GO_CONFIG']->tmpdir,$length)==$this->user_id.'/' && is_dir($GLOBALS['GO_CONFIG']->tmpdir)){
				$fs->delete($GLOBALS['GO_CONFIG']->tmpdir);
			}
		}


		SetCookie("GO_UN","",time()-3600,"/","",!empty($_SERVER['HTTPS']),true);
		SetCookie("GO_PW","",time()-3600,"/","",!empty($_SERVER['HTTPS']),true);

		$old_session = $_SESSION;

		unset($_SESSION, $_COOKIE['GO_UN'], $_COOKIE['GO_PW']);

		
    session_regenerate_id();
    @session_destroy();
		$this->user_id = 0;

		global $GO_MODULES;
		if(isset($GO_MODULES)) {
			$GLOBALS['GO_MODULES']->load_modules();
		}

		global $GO_EVENTS;
		$GLOBALS['GO_EVENTS']->fire_event('logout', $old_session);
	}

	/**
	 * Checks if a user is logged in. if not it attempts to log in
	 * based on stored cookies. On failure it redirects the user to the login page.
	 *
	 * @param	bool	$admin	Check for administrator privileges too.
	 * @access public
	 * @return void
	 */
	function authenticate($module='') {
		global $GO_MODULES, $GO_CONFIG;

		if (!$this->logged_in()) {
			return 'NOTLOGGEDIN';
		}

		if($module!='' && (
				empty($GLOBALS['GO_MODULES']->modules[$module])
				||
				(!$GLOBALS['GO_MODULES']->modules[$module]['write_permission'] &&
				!$GLOBALS['GO_MODULES']->modules[$module]['read_permission'])
		)) {
			return 'UNAUTHORIZED';
		}
		return 'AUTHORIZED';
	}

	function json_authenticate($module='') {
		header('Content-Type: text/html; charset=UTF-8');

		$authenticated = $this->authenticate($module);

		if($authenticated!='AUTHORIZED') {
			echo $authenticated;
			exit();
		}else {
			return true;
		}
	}

	function html_authenticate() {
		$authenticated = $this->authenticate();
		if($authenticated!='AUTHORIZED') {
			global $GO_CONFIG;
			
			$_SESSION['GO_SESSION']['after_login_url']=$_SERVER['REQUEST_URI'];

			header('Location: '.$GLOBALS['GO_CONFIG']->host);

			exit();
		}
	}


	/**
	 * Creates and returns a new Access Control List to secure an object
	 *
	 * @param	string	$description	Description of the ACL
	 * @param	int			$user_id	The owner of the ACL and the one who can modify it
	 *									default is the current logged in user.
	 * @access public
	 * @return int			The ID of the new Access Control List
	 */
	function get_new_acl($description='', $user_id=-1) {
		global $GO_CONFIG;

		if ($user_id == -1) {
			$user_id = $this->user_id;
		}
		//$ai['id'] = $this->nextid("go_acl_items");
		$ai['description']=$description;
		$ai['user_id']=$user_id;

		$this->insert_row('go_acl_items', $ai);
    $id = $this->insert_id();
		$this->add_group_to_acl($GLOBALS['GO_CONFIG']->group_root, $id,GO_SECURITY::MANAGE_PERMISSION);
		$this->add_user_to_acl($user_id, $id,GO_SECURITY::MANAGE_PERMISSION);
		return $id;
	}

	/**
	 * Checks if a user is allowed to manage the Access Control List
	 *
	 * @param	int			$user_id	The owner of the ACL and the one who can modify it
	 * @param	int			$acl_id	The ID of the Access Control List
	 * @access public
	 * @return bool
	 */
	function has_permission_to_manage_acl($user_id, $acl_id) {
		//return ($this->user_owns_acl($user_id, $acl_id) || $this->has_admin_permission($user_id));
		return $this->has_permission($user_id, $acl_id)==GO_SECURITY::MANAGE_PERMISSION;
	}

	/**
	 * Checks if a user owns the Access Control List
	 *
	 * @param	int			$user_id	The owner of the ACL and the one who can modify it
	 * @param	int			$acl_id	The ID of the Access Control List
	 * @access public
	 * @return bool
	 */
	function user_owns_acl($user_id, $acl_id) {
		$this->query("SELECT user_id FROM go_acl_items WHERE id='".$this->escape($acl_id)."'");
		if ($this->next_record()) {
			if ($user_id == $this->f('user_id')) {
				return true;
			}elseif($this->f('user_id') == '0') {
				return $this->has_admin_permission($user_id);
			}
		}
		return false;
	}

	/**
	 * Change ownership of an ACL
	 *
	 * @param	int			$acl_id	The ID of the Access Control List
	 * @param	int			$user_id	The owner of the ACL and the one who can modify it
	 * @access public
	 * @return bool
	 */
	function chown_acl($acl_id, $user_id) {
		$sql = "UPDATE go_acl_items SET user_id='".$this->escape($user_id)."' WHERE id='".$this->escape($acl_id)."'";
		$this->query($sql);

		//if(!$this->user_in_acl($user_id, $acl_id)) {
			$this->add_user_to_acl($user_id, $acl_id, GO_SECURITY::MANAGE_PERMISSION);
		//}

		return true;
	}

	/**
	 * Deletes an Access Control List
	 *
	 * @param	int			$acl_id	The ID of the Access Control List
	 * @access public
	 * @return bool		True on succces
	 */
	function delete_acl($acl_id) {
		if($this->query("DELETE FROM go_acl WHERE acl_id='".$this->escape($acl_id)."'")) {
			return $this->query("DELETE FROM go_acl_items WHERE id='".$this->escape($acl_id)."'");
		}
		return false;
	}

	function get_acl($acl_id){
		$this->query("SELECT * FROM go_acl WHERE acl_id='".$this->escape($acl_id)."'");
		return $this->next_record();
	}

	/**
	 * Adds a user to an Access Control List
	 *
	 * @param	int			$user_id	The user_id to add to the ACL
	 * @param	int			$acl_id		The ID of the Access Control List
	 * @access public
	 * @return bool		True on success
	 */
	function add_user_to_acl($user_id,$acl_id, $level=1) {
		if($acl_id<1 || $user_id<1)
			return false;

		return $this->query("REPLACE INTO go_acl (acl_id,user_id,level) ".
				"VALUES ('".$this->escape($acl_id)."','".$this->escape($user_id)."','".$this->escape($level)."')");
	}

	/**
	 * Deletes a user from an Access Control List
	 *
	 * @param	int			$user_id	The user_id to delete from the ACL
	 * @param	int			$acl_id		The ID of the Access Control List
	 * @access public
	 * @return bool		True on success
	 */
	function delete_user_from_acl($user_id, $acl_id) {
		$sql = "DELETE FROM go_acl WHERE user_id='".$this->escape($user_id)."' AND acl_id='".$this->escape($acl_id)."'";
		return $this->query($sql);
	}

	/**
	 * Add's a user group to an Access Control List
	 *
	 * @param	int			$group_id	The group_id to add to the ACL
	 * @param	int			$acl_id		The ID of the Access Control List
	 * @access public
	 * @return bool		True on success
	 */
	function add_group_to_acl($group_id,$acl_id, $level=1) {
		if($acl_id<1 || $group_id<1)
			return false;

		return $this->query("REPLACE INTO go_acl (acl_id,group_id,level) ".
				"VALUES ('".$this->escape($acl_id)."','".$this->escape($group_id)."','".$this->escape($level)."')");
	}

	/**
	 * Deletes a user group from an Access Control List
	 *
	 * @param	int			$group_id	The group_id to add to the ACL
	 * @param	int			$acl_id		The ID of the Access Control List
	 * @access public
	 * @return bool		True on success
	 */
	function delete_group_from_acl($group_id, $acl_id, $force_group_root=false) {
		global $GO_CONFIG;
		if($force_group_root || $group_id != $GLOBALS['GO_CONFIG']->group_root) {
			$sql = "DELETE FROM go_acl WHERE group_id='".$this->escape($group_id)."' AND acl_id='".$this->escape($acl_id)."'";
			return $this->query($sql);
		}
	}

	function get_global_read_only_acl(){
		global $GO_CONFIG;

		$acl_id = $GLOBALS['GO_CONFIG']->get_setting('global_read_only_acl');
		if(!$acl_id){
			$acl_id = $this->get_new_acl('global', 1);

			$this->set_read_only_acl_permissions($acl_id);

			$GLOBALS['GO_CONFIG']->save_setting('global_read_only_acl', $acl_id);
		}

		return $acl_id;
	}

	function set_read_only_acl_permissions($acl_id=false){
		global $GO_CONFIG;

		if(!$acl_id)
			$acl_id = $GLOBALS['GO_CONFIG']->get_setting('global_read_only_acl');
		
		$this->delete_group_from_acl($GLOBALS['GO_CONFIG']->group_root, $acl_id, true);
		$this->add_group_to_acl($GLOBALS['GO_CONFIG']->group_everyone, $acl_id);
		$this->delete_user_from_acl(1, $acl_id);
	}

	/**
	 * Remove all users and user groups from an ACL
	 *
	 * @param	int			$acl_id		The ID of the Access Control List
	 * @access public
	 * @return bool		True on success
	 */
	function clear_acl($acl_id) {
		global $GO_CONFIG;

		if($this->query("DELETE FROM go_acl WHERE acl_id='".$this->escape($acl_id)."'")) {
			return $this->add_group_to_acl($GLOBALS['GO_CONFIG']->group_root, $acl_id);
		}
	}

	/**
	 * Set's the owner of an access control list
	 *
	 * @param	int			$acl_id		The ID of the Access Control List
	 * @param	int			$user_id	The user ID of the new owner
	 * @access public
	 * @return bool		True on success
	 */
	function set_acl_owner($acl_id, $user_id) {
		return $this->query("UPDATE go_acl_items SET user_id='".$this->escape($user_id)."' WHERE id='".$this->escape($acl_id)."'");
	}

	/**
	 * Checks if a user is in the special admins group
	 *
	 * @param	int			$user_id	The user ID
	 * @access public
	 * @return bool		True on success
	 */
	function has_admin_permission($user_id) {
		global $GO_CONFIG;

		require_once($GLOBALS['GO_CONFIG']->class_path.'base/groups.class.inc.php');
		$GO_GROUPS = new GO_GROUPS();

		if(!isset($this->is_admin))
			$this->is_admin = $GO_GROUPS->is_in_group($user_id, $GLOBALS['GO_CONFIG']->group_root);

		return $this->is_admin;
	}

	/**
	 * Get's all groups from an ACL
	 *
	 * @param	int			$acl_id	The ACL ID
	 * @access public
	 * @return int			Number of groups in the acl
	 */
	function get_groups_in_acl($acl_id) {
		global $GO_CONFIG, $auth_sources;

		$sql = "SELECT g.*,a.level FROM go_groups g INNER JOIN go_acl a ON".
				" a.group_id=g.id WHERE a.acl_id='".$this->escape($acl_id)."'".
				" ORDER BY g.name";
		$this->query($sql);
		return $this->num_rows();
	}

	/**
	 * Get all groups that are connected to a given acl.
	 *
	 * This function fetches all groups that have permissions for the given acl,
	 * and returns an array of IDs.
	 *
	 * @access public
	 *
	 * @param Integer $acl_id is the ID whose groups should be fetched.
	 *
	 * @return Array of the group IDs.
	 */
	function get_group_ids_from_acl( $acl_id ) {
		trigger_error(
				'get_group_ids_from_acl() is an abstract method.',
				E_USER_ERROR );
		return false;
	}

	/**
	 * Get's all users from an ACL
	 *
	 * @param	int			$acl_id	The ACL ID
	 * @access public
	 * @return int			Number of users in the acl
	 */
	function get_users_in_acl($acl_id, $level=0) {
		$sql = "SELECT u.id, u.first_name, u.middle_name, u.last_name, a.level ".
				"FROM go_acl a INNER JOIN go_users u ON u.id=a.user_id WHERE ".
				"a.acl_id='".$this->escape($acl_id)."'";

		if($level>0){
			$sql .= " AND a.level=".$this->escape($level);
		}

		$this->query($sql);
		return $this->num_rows();
	}

	/**
	 * Get's all authorized users from an ACL
	 *
	 * @param	int			$acl_id	The ACL ID
	 * @param	int			$level The minimum permission level
	 * 
	 * @access public
	 * @return Array			The user id's
	 */
	function get_authorized_users_in_acl($acl_id, $level=0) {
		$users=array();
		$sql = "SELECT user_id FROM go_acl WHERE acl_id='".$this->escape($acl_id)."' AND user_id!=0";

		if($level>0){
			$sql .= " AND level>=".$this->escape($level);
		}

		$this->query($sql);
		while($this->next_record()) {
			$users[] =$this->f('user_id');
		}

		$sql = "SELECT go_users_groups.user_id FROM go_users_groups INNER JOIN go_acl ON ".
				"go_acl.group_id=go_users_groups.group_id WHERE go_acl.acl_id=".intval($acl_id)." AND go_users_groups.user_id!=0";

		if($level>0){
			$sql .= " AND level>=".$this->escape($level);
		}

		$this->query($sql);
		while($this->next_record()) {
			if(!in_array($this->f('user_id'), $users)) {
				$users[] =$this->f('user_id');
			}
		}
		return $users;
	}

	/**
	 * Checks presence of a user in an ACL
	 *
	 * @param	int			$user_id	The user ID
	 * @param	int			$acl_id	The ACL ID
	 * @access public
	 * @return int			True if the user is in the ACL
	 */
	function user_in_acl($user_id, $acl_id) {
		$sql = "SELECT level FROM go_acl WHERE acl_id='".$this->escape($acl_id)."' AND".
				" user_id='$user_id'";
		$this->query($sql);
		if ($this->next_record()) {
			return $this->f('level');
		}
		return false;
	}

	/**
	 * Checks presence of a group in an ACL
	 *
	 * @param	int			$group_id	The group ID
	 * @param	int			$acl_id	The ACL ID
	 * @access public
	 * @return int			True if the group is in the ACL
	 */
	function group_in_acl($group_id, $acl_id) {
		$sql = "SELECT level FROM go_acl WHERE acl_id='".$this->escape($acl_id)."' AND group_id='".$this->escape($group_id)."'";
		$this->query($sql);
		if ($this->next_record()) {
			return $this->f('level');
		}
		return false;
	}

	/**
	 * Get's an ACL id based on the desciption. Use carefully.
	 *
	 * @param	string			$description	The description of an ACL
	 * @access public
	 * @return int			True if the group is in the ACL
	 */
	function get_acl_id($description) {
		$sql = "SELECT id FROM go_acl_items WHERE description='".$this->escape($description)."'";
		$this->query($sql);
		if ($this->next_record()) {
			return $this->f('id');
		}
		return false;
	}

	/**
	 * Checks if an ACL exists in acl_items. Use carefully!
	 *
	 * Returns:
	 *	false if the acl does not exist
	 *	true if the acl does exist in acl_items
	 *
	 * @param int $acl_id
	 * @access public
	 * @return bool
	 */
	function acl_exists( $acl_id ) {
		$sql = "SELECT * FROM go_acl_items WHERE id='".$this->escape($acl_id)."'";
		$this->query($sql);
		if ( $this->num_rows() != 0 ) {
			return true;
		}
		#    $sql = "SELECT * FROM acl WHERE acl_id='$acl_id'";
		#    $this->query($sql);
		#    if ( $this->num_rows() != 0 ) {
		#      $retval += 2;
		#    }
		return false;
	}

	/**
	 * Copy the user and group permissions of one acl to another
	 *
	 * @param	int			$sAcl	The source ACL to copy
	 * @param	int			$dAcl	The destination ACL to copy to.
	 * @access public
	 * @return void
	 */
	function copy_acl($sAcl, $dAcl=0, $level=0) {
		global $GO_CONFIG;

		if($dAcl > 0) {
			$this->clear_acl($dAcl);
		}else {
			$dAcl = $this->get_new_acl();
		}

		$sql = "SELECT * FROM go_acl WHERE acl_id='$sAcl' AND level>=".intval($level);

		$security = new GO_SECURITY();
		$this->query($sql);
		while($this->next_record()) {
			if ($this->f("group_id") != 0 && $this->f('group_id') != $GLOBALS['GO_CONFIG']->group_root && !$security->group_in_acl($this->f("group_id"), $dAcl)) {
				$security->add_group_to_acl($this->f("group_id"), $dAcl, $this->f('level'));
			}

			if ($this->f("user_id") != 0 && !$security->user_in_acl($this->f("user_id"), $dAcl))// && ($security->user_is_visible($this->f("user_id")) || $this->f("user_id") == $this->user_id))
			{
				$security->add_user_to_acl($this->f("user_id"), $dAcl, $this->f('level'));
			}
		}
		return $dAcl;
	}

	/**
	 * Checks if a user is visible to the current logged in user
	 *
	 * @param	int			$user_id	The user ID to check
	 * @access public
	 * @return int			True if the user is visible
	 */

	function user_is_visible($user_id) {
		if ($this->user_id == $user_id)
			return true;

		$sql = "SELECT acl_id FROM go_users WHERE id='".$this->escape($user_id)."'";
		$this->query($sql);
		$this->next_record();
		return $this->has_permission($this->user_id, $this->f("acl_id"));
	}


	/**
	 * Called when a user is deleted
	 *
	 * @param	int			$user_id	The user ID that is about to be deleted
	 * @access private
	 * @return bool		True on success
	 */

	function delete_user($user_id) {
		/*$sql = "DELETE FROM acl WHERE user_id='$user_id'";
		return $this->query($sql);*/
	}

	/**
	 * Called when a group is deleted
	 *
	 * @param	int			$group_id	The group ID that is about to be deleted
	 * @access private
	 * @return bool	 True on success
	 */
	function delete_group($group_id) {
		$sql = "DELETE FROM go_acl WHERE group_id='".$this->escape($group_id)."'";
		return $this->query($sql);
	}



	/**
	 * Checks if a user has permission for a ACL
	 * @deprecated
	 * @param	int			$user_id	The user that needs authentication
	 * @param	int			$acl_id	The ACL to check
	 * @param bool 		$groups_only only check user groups and no individual access
	 * @access private
	 * @return Int	 permission level on success, false otherwise
	 */

	function has_permission($user_id, $acl_id, $groups_only=false) {
		global $GO_CONFIG;

		if ($user_id > 0 && $acl_id > 0) {
			$sql = "SELECT a.acl_id, a.level FROM go_acl a ".
				"LEFT JOIN go_users_groups ug ON a.group_id=ug.group_id ".
				"WHERE a.acl_id=".intval($acl_id)." AND ".
				"(ug.user_id=".intval($user_id);
			
			if(!$groups_only)
				$sql .= " OR a.user_id=".intval($user_id).") ORDER BY a.level DESC";
			else
				$sql .= ")";

		$this->query($sql);
			if($this->next_record()) {
				return $this->f('level');
			}
		}
		return false;
	}
	
	function hasPermission($acl_id, $groups_only=false, $user_id=0) {
		global $GO_CONFIG;
		
		if($user_id==0)
			$user_id=$this->user_id;

		if ($user_id > 0 && $acl_id > 0) {
			$sql = "SELECT a.acl_id, a.level FROM go_acl a ".
				"LEFT JOIN go_users_groups ug ON a.group_id=ug.group_id ".
				"WHERE a.acl_id=".intval($acl_id)." AND ".
				"(ug.user_id=".intval($user_id);
			
			if(!$groups_only)
				$sql .= " OR a.user_id=".intval($user_id).") ORDER BY a.level DESC";
			else
				$sql .= ")";

		$this->query($sql);
			if($this->next_record()) {
				return $this->f('level');
			}
		}
		return false;
	}



	function get_user_group_ids($user_id=0) {
		if ($user_id == 0 || $user_id == $this->user_id) {
			if (!isset($_SESSION['GO_SESSION']['user_groups'])) {
				$_SESSION['GO_SESSION']['user_groups'] = array();

				$this->query("SELECT group_id FROM go_users_groups WHERE user_id=?", 'i', $user_id);
				while ($r = $this->next_record()) {
					if(!empty($r['group_id']))
						$_SESSION['GO_SESSION']['user_groups'][] = $r['group_id'];
				}
			}

			return $_SESSION['GO_SESSION']['user_groups'];
		} else {
			$ids = array();
			$this->query("SELECT group_id FROM go_users_groups WHERE user_id=?", 'i', $user_id);
			while ($r = $this->next_record()) {
				if(!empty($r['group_id']))
					$ids[] = $r['group_id'];
			}
			return $ids;
		}
	}
}
