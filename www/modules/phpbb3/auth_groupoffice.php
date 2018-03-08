<?php
/**
 * Autologin function
 *
 * @return array containing the user row or empty if no auto login should take place
 */
function login_groupoffice(&$username, &$password,  $ip = '', $browser = '', $forwarded_for = '')
{
	global $db;
	
	if (!$password)
	{
		return array(
			'status'	=> LOGIN_ERROR_PASSWORD,
			'error_msg'	=> 'NO_PASSWORD_SUPPLIED',
			'user_row'	=> array('user_id' => ANONYMOUS),
		);
	}

	if (!$username)
	{
		return array(
			'status'	=> LOGIN_ERROR_USERNAME,
			'error_msg'	=> 'LOGIN_ERROR_USERNAME',
			'user_row'	=> array('user_id' => ANONYMOUS),
		);
	}
	$gorow = user_row_groupoffice($username, $password);

	if($gorow)
	{
		$sql = 'SELECT user_id, username, user_password, user_passchg, user_email, user_type
			FROM ' . USERS_TABLE . "
			WHERE username_clean = '" . $db->sql_escape(utf8_clean_string($username)) . "'";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($row)
		{
			// User inactive...
			if ($row['user_type'] == USER_INACTIVE || $row['user_type'] == USER_IGNORE)
			{
				return array(
					'status'		=> LOGIN_ERROR_ACTIVE,
					'error_msg'		=> 'ACTIVE_ERROR',
					'user_row'		=> $row,
				);
			}

			// Successful login...
			return array(
				'status'		=> LOGIN_SUCCESS,
				'error_msg'		=> false,
				'user_row'		=> $row,
			);
		}

		// this is the user's first login so create an empty profile
		return array(
			'status'		=> LOGIN_SUCCESS_CREATE_PROFILE,
			'error_msg'		=> false,
			'user_row'		=> $gorow,
		);

	}else
	{
//		return array(
//				'status'	=> LOGIN_ERROR_USERNAME,
//				'error_msg'	=> 'LOGIN_ERROR_USERNAME',
//				'user_row'	=> array('user_id' => ANONYMOUS),
//		);
		//fallback to regular Phpbb db auth.
		require_once(dirname(__FILE__).'/auth_db.php');
		return login_db($username, $password, $ip, $browser, $forwarded_for);
	}
}

function groupoffice_unserializesession($data) {
	$vars = preg_split('/([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff^|]*)\|/',
									$data, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
	for ($i = 0; isset($vars[$i]); $i++)
		$result[$vars[$i++]] = unserialize($vars[$i]);
	return $result;
}


function autologin_groupoffice()
{
	$user_id=false;
	
	if(isset($_REQUEST['goauth']))
	{
		$file = base64_decode($_REQUEST['goauth']);

		//$_SESSION['groupoffice_to_phpbb_session_file']=$file;

		$user_id = intval(file_get_contents($file));
	}elseif(isset($_COOKIE['groupoffice'])){
		$fname = session_save_path() . "/sess_" . $_COOKIE['groupoffice'];
		if (file_exists($fname)) {
			$data = file_get_contents($fname);
			$data = groupoffice_unserializesession($data);
			
			if(isset($data['GO_SESSION']['user_id']))
				$user_id=$data['GO_SESSION']['user_id'];
		}
	}
		//unlink($file);
			
	if($user_id){
		$gorow = user_row_groupoffice('', '', $user_id);

		if($gorow)
		{
			global $db;
		
			$sql = 'SELECT * FROM ' . USERS_TABLE . "
			WHERE username_clean = '" . $db->sql_escape(utf8_clean_string($gorow['username'])) . "'";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			

			if ($row)
			{
				return ($row['user_type'] == USER_INACTIVE || $row['user_type'] == USER_IGNORE) ? array() : $row;
			}
			
			if (!function_exists('user_add'))
			{
				global $phpbb_root_path, $phpEx;
	
				include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
			}
	
			// create the user if he does not exist yet
			user_add($gorow);
	
			$sql = 'SELECT *
				FROM ' . USERS_TABLE . "
				WHERE username_clean = '" . $db->sql_escape(utf8_clean_string($gorow['username'])) . "'";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
	
			if ($row)
			{
				return $row;
			}
		}
		
	}
	return array();
}

function get_godb()
{
	global $config;
	
	$godb = new dbal_mysqli();
	$godb->sql_connect($config['groupoffice_server'], $config['groupoffice_user'], $config['groupoffice_pass'], $config['groupoffice_database']);
	return $godb;
}


/**
 * This function generates an array which can be passed to the user_add function in order to create a user
 */
function user_row_groupoffice($username, $password, $user_id=false)
{
	global $db, $config, $user;
	
	// first retrieve default group id
	$sql = 'SELECT group_id
		FROM ' . GROUPS_TABLE . "
		WHERE group_name = '" . $db->sql_escape('REGISTERED') . "'
			AND group_type = " . GROUP_SPECIAL;
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if (!$row)
	{
		trigger_error('NO_GROUP');
	}

	
	$godb=get_godb();
	

	$sql = "SELECT username,email,id,password FROM core_user WHERE enabled=1 AND ";

	if($user_id)	
	{
		$sql .= "id=".$user_id;		
	}else
	{
		$sql .= "username= '" . $db->sql_escape(utf8_clean_string($username)) . "'";
	}

	$result = $godb->sql_query($sql);
	$gorow = $godb->sql_fetchrow($result);
	$godb->sql_freeresult($result);

	if(!$user_id){
		$pw = crypt($password, $gorow['password']);		
		if($gorow['password']!=$pw)
			return false;
	}

	//var_dump($gorow);

	if(!$gorow)
	{
		return false;
	}

//	$adpos = strpos($gorow['username'],'@');
//	if($adpos){
//		$gorow['username']=substr($gorow['username'],0,$adpos);
//
//		//check for a duplicate user in GO.
//		$sql = "SELECT id FROM core_user WHERE username='".$db->sql_escape(utf8_clean_string($gorow['username']))."'";
//		$result = $godb->sql_query($sql);
//		if($godb->sql_fetchrow($result)){
//			//duplicate found append the id
//			$gorow['username'].=$gorow['id'];
//		}
//	}

	// generate user account data
	return array(
		'username'		=> $gorow['username'],
		'user_password'	=> phpbb_hash($password),
		'user_email'	=> $gorow['email'],
		'group_id'		=> (int) $row['group_id'],
		'user_type'		=> USER_NORMAL,
		'user_ip'		=> $user->ip,
	);
}

function acp_groupoffice(&$new)
{
	global $user;

	$tpl = '

	<dl>
		<dt><label for="groupoffice_server">Group-Office DB server:</label><br /><span></span></dt>
		<dd><input type="text" id="groupoffice_server" size="40" name="config[groupoffice_server]" value="' . $new['groupoffice_server'] . '" /></dd>
	</dl>
	<dl>
		<dt><label for="groupoffice_server">Group-Office DB name:</label><br /><span></span></dt>
		<dd><input type="text" id="groupoffice_server" size="40" name="config[groupoffice_database]" value="' . $new['groupoffice_database'] . '" /></dd>
	</dl>
	<dl>
		<dt><label for="groupoffice_server">Group-Office DB user:</label><br /><span></span></dt>
		<dd><input type="text" id="groupoffice_server" size="40" name="config[groupoffice_user]" value="' . $new['groupoffice_user'] . '" /></dd>
	</dl>
	<dl>
		<dt><label for="groupoffice_server">Group-Office DB password:</label><br /><span></span></dt>
		<dd><input type="password" id="groupoffice_server" size="40" name="config[groupoffice_pass]" value="' . $new['groupoffice_pass'] . '" /></dd>
	</dl>
	<dl>
		<dt><label for="groupoffice_server">Group-Office DB port:</label><br /><span></span></dt>
		<dd><input type="text" id="groupoffice_server" size="40" name="config[groupoffice_port]" value="' . $new['groupoffice_port'] . '" /></dd>
	</dl>
		';

	// These are fields required in the config table
	return array(
		'tpl'		=> $tpl,
		'config'	=> array('groupoffice_server', 'groupoffice_database', 'groupoffice_user', 'groupoffice_pass', 'groupoffice_port')
	);
}

/**
* The session validation function checks whether the user is still logged in
*
* @return boolean true if the given user is authenticated or false if the session should be closed
*/
function validate_session_groupoffice(&$userrecord)
{
	//if goauth is passed then a new session must be created
	return !isset($_REQUEST['goauth']);

}



?>
