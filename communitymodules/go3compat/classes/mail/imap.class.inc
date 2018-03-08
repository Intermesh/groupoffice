<?php
require_once($GLOBALS['GO_CONFIG']->class_path.'mail/imap_base.class.inc.php');
class imap extends imap_bodystruct {

	var $handle=false;

	var $ssl=false;
	var $server='';
	var $port=143;
	var $username='';
	var $password='';

	var $starttls=false;

	var $auth='plain';

	var $selected_mailbox=false;

	var $touched_folders =array();

	var $delimiter=false;

	var $errors=array();

	var $sort_count = false;
	
	public function __construct(){
		
	}

	public function __destruct() {
		$this->disconnect();
	}

	public function last_error(){
		$count=count($this->errors);
		//go_debug($this->errors);
		if($count)
			return $this->errors[$count-1];
		else
			return false;
	}

	public function clear_errors(){
		//$this->errors=array();
	}


	/**
	 * Connects to the IMAP server and authenticates the user
	 *
	 * @param <type> $server
	 * @param <type> $port
	 * @param <type> $username
	 * @param <type> $password
	 * @param <type> $ssl
	 * @param <type> $starttls
	 * @return <type>
	 */

	public function connect($server, $port, $username, $password, $ssl=false, $starttls=false, $auth='plain') {

		go_debug("imap::connect($server, $port, $username, ***, $ssl, $starttls)");

		//cache DNS in session. Seems to be faster with gmail somehow.
		if(empty($_SESSION['GO_SESSION']['imap'][$server]))
		{
			$_SESSION['GO_SESSION']['imap'][$server]=gethostbyname($server);
		}
		
		$this->ssl = $ssl;
		$this->starttls = $starttls;
		$this->auth = strtolower($auth);

		$this->server=$_SESSION['GO_SESSION']['imap'][$server];
		$this->port=$port;
		$this->username=$username;
		$this->password=$password;

		if ($this->ssl) {
			$this->server = 'ssl://'.$this->server;
		}

		$this->handle = @fsockopen($this->server, $this->port, $errorno, $errorstr, 30);
		if (!is_resource($this->handle)) {
			throw new Exception('Failed to open socket #'.$errorno.'. '.$errorstr);
		}

		return $this->authenticate($username, $password);
	}



	/**
	 * Disconnect from the IMAP server
	 *
	 * @return <type>
	 */

	public function disconnect() {
		if (is_resource($this->handle)) {
			$command = "LOGOUT\r\n";
			$this->send_command($command);
			$this->state = 'disconnected';
			$result = $this->get_response();
			//go_debug($this->commands);

			//go_debug($this->responses);

			fclose($this->handle);

			$this->handle=false;
			$this->selected_mailbox=false;

			return true;
		}else {
			return false;
		}
	}

	/**
	 * Handles authentication. You can optionally set
	 * $this->starttls or $this->auth to CRAM-MD5
	 *
	 * @param <type> $username
	 * @param <type> $pass
	 * @return <type>
	 */

	private function authenticate($username, $pass) {

		if ($this->starttls) {

			$command = "STARTTLS\r\n";
			$this->send_command($command);
			$response = $this->get_response();
			if (!empty($response)) {
				$end = array_pop($response);
				if (substr($end, 0, strlen('A'.$this->command_count.' OK')) == 'A'.$this->command_count.' OK') {
					stream_socket_enable_crypto($this->handle, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
				}
			}
		}
		switch (strtolower($this->auth)) {
			case 'cram-md5':
				$this->banner = fgets($this->handle, 1024);
				$cram1 = 'A'.$this->command_number().' AUTHENTICATE CRAM-MD5'."\r\n";
				fputs ($this->handle, $cram1);
				$this->commands[trim($cram1)] = getmicrotime();
				$response = fgets($this->handle, 1024);
				$this->responses[] = $response;
				$challenge = base64_decode(substr(trim($response), 1));
				$pass .= str_repeat(chr(0x00), (64-strlen($pass)));
				$ipad = str_repeat(chr(0x36), 64);
				$opad = str_repeat(chr(0x5c), 64);
				$digest = bin2hex(pack("H*", md5(($pass ^ $opad).pack("H*", md5(($pass ^ $ipad).$challenge)))));
				$challenge_response = base64_encode($username.' '.$digest);
				$this->commands[trim($challenge_response)] = getmicrotime();
				fputs($this->handle, $challenge_response."\r\n");
				break;
			default:
				$login = 'A'.$this->command_number().' LOGIN "'.$this->_escape($username).'" "'.$this->_escape($pass). "\"\r\n";
				$this->commands[trim(str_replace($pass, 'xxxx', $login))] = getmicrotime();
				fputs($this->handle, $login);
				break;
		}
		$res = $this->get_response();

		$authed = false;
		if (is_array($res) && !empty($res)) {
			$response = array_pop($res);

			//Sometimes an extra empty line comes along
			if(!$response && count($res)==2)
				$response = array_pop($res);

			$this->short_responses[$response] = getmicrotime();
			if (!$this->auth) {
				if (isset($res[1])) {
					$this->banner = $res[1];
				}
				if (isset($res[0])) {
					$this->banner = $res[0];
				}
			}	
			if (stristr($response, 'A'.$this->command_count.' OK')) {
				$authed = true;
				$this->state = 'authed';
			}else
			{
				throw new Exception($response);
				//$this->errors[]=$response;
			}
		}
		return $authed;
	}
	
	private function _escape($str){
		return str_replace(array('\\','"'), array('\\\\','\"'), $str);
	}

	/**
	 * Get's the capabilities of the IMAP server. Useful to determine if the
	 * IMAP server supports server side sorting.
	 *
	 * @return <type>
	 */

	public function get_capability() {
		if (isset($_SESSION['GO_IMAP'][$this->server]['imap_capability'])) {
			$this->capability=$_SESSION['GO_IMAP'][$this->server]['imap_capability'];
		}else {
			$command = "CAPABILITY\r\n";
			$this->send_command($command);
			$response = $this->get_response();
			$this->capability = $_SESSION['GO_IMAP'][$this->server]['imap_capability'] = implode(' ', $response);
		}

		go_debug('IMAP capability: '.$this->capability);

		return $this->capability;
	}


	public function get_acl($mailbox){

		$mailbox = $this->utf7_encode($this->_escape( $mailbox));
		$this->clean($mailbox, 'mailbox');

		$command = "GETACL $mailbox\r\n";
		$this->send_command($command);
		$response = $this->get_response(false, true);
		
		$ret = array();

		foreach($response as $line)
		{
			if($line[0]=='*' && $line[1]=='ACL' && count($line)>3){
				for($i=3,$max=count($line);$i<$max;$i+=2){
					$ret[]=array('identifier'=>$line[$i],'permissions'=>$line[$i+1]);
				}
			}
		}
		
		return $ret;
	}

	public function set_acl($mailbox, $identifier, $permissions){

		$mailbox = $this->utf7_encode($this->_escape( $mailbox));
		$this->clean($mailbox, 'mailbox');

		$command = "SETACL $mailbox $identifier $permissions\r\n";
		$this->send_command($command);

		$response = $this->get_response();
		return $this->check_response($response);
	}

	public function delete_acl($mailbox, $identifier){
		$command = "DELETEACL $mailbox $identifier\r\n";
		$this->send_command($command);
		$response = $this->get_response();
		return $this->check_response($response);
	}

	/**
		* Get the delimiter that is used to delimit Mailbox names
		*
		* @access public
		* @return mixed The delimiter or false on failure
		*/

	public function get_mailbox_delimiter() {
		if(!$this->delimiter){
			if(isset($_SESSION['GO_SESSION']['imap_delimiter'][$this->server])){
				$this->delimiter=$_SESSION['GO_SESSION']['imap_delimiter'][$this->server];
			}else
			{
				$this->get_folders();
			}
		}
		return $this->delimiter;
	}

	private function set_mailbox_delimiter($delimiter) {
		$this->delimiter=$_SESSION['GO_SESSION']['imap_delimiter'][$this->server]=$delimiter;
	}


	/**
	 * Get's the mailboxes
	 *
	 * @param <type> $namespace
	 * @param <type> $subscribed
	 * @return <type>
	 */

	public function get_folders($namespace='', $subscribed=false, $pattern='*') {

		$this->get_capability();

		if ($subscribed) {
			$imap_command = 'LSUB';
		}
		else {
			$imap_command = 'LIST';
		}
		$excluded = array();
		$parents = array();
		$delim = false;

		$command = $imap_command.' "'.$namespace."\" \"$pattern\"\r\n";
		$this->send_command($command);
		$result = $this->get_response(false, true);
		$folders = array();
		foreach ($result as $vals) {
			if (!isset($vals[0])) {
				continue;
			}
			if ($vals[0] == 'A'.$this->command_count) {
				continue;
			}
			$flags = false;
			$count = count($vals);
			$folder = $this->utf7_decode($vals[($count - 1)]);
			$flag = false;
			$delim_flag = false;
			$parent = '';
			$folder_parts = array();
			$no_select = false;
			$can_have_kids = false;
			$has_kids = false;
			$marked = false;
			$hidden = false;

			foreach ($vals as $v) {
				if ($v == '(') {
					$flag = true;
				}
				elseif ($v == ')') {
					$flag = false;
					$delim_flag = true;
				}
				else {
					if ($flag) {
						$flags .= ' '.$v;
					}
					if ($delim_flag && !$delim) {
						$delim = $v;
						$delim_flag = false;
					}
				}
			}

			if (!$this->delimiter) {
				$this->set_mailbox_delimiter($delim);
			}

			if (stristr($flags, 'marked')) {
				$marked = true;
			}
			if (!stristr($flags, 'noinferiors')) {
				$can_have_kids = true;
			}
			if (($folder == $namespace && $namespace) || stristr($flags, 'haschildren')) {
				$has_kids = true;
			}
			if ($folder != 'INBOX' && $folder != $namespace && stristr($flags, 'noselect')) {
				$no_select = true;
			}

			if (!isset($folders[$folder]) && $folder) {
				$folders[$folder] = array(
								'delimiter' => $delim,
								'name' => $folder,
								'marked' => $marked,
								'noselect' => $no_select,
								'can_have_children' => $can_have_kids,
								'has_children' => $has_kids
				);
			}
		}



		

		//sometimes shared folders like "Other user.shared" are in the folder list
		//but there's no "Other user" parent folder. We create a dummy folder here.

		foreach($folders as $name=>$folder){
			$pos = strrpos($name, $delim);

			if($pos){
				$parent = substr($name,0,$pos);
				if(!isset($folders[$parent]))
				{
					$folders[$parent]=array(
								'delimiter' => $delim,
								'name' => $parent,
								'marked' => true,
								'noselect' => true,
								'can_have_children' => true,
								'has_children' => true);
				}
			}

			$last_folder = $name;
		}

		//go_debug($folders);

		ksort($folders);

		return $folders;
	}


	/**
	 * Before getting message a mailbox must be selected
	 *
	 * @param <type> $mailbox_name
	 * @return <type>
	 *
	 */

	public function select_mailbox($mailbox_name='INBOX') {

		//go_debug($this->selected_mailbox);

		if($this->selected_mailbox && $this->selected_mailbox['name']==$mailbox_name)
			return true;

		if(!in_array($mailbox_name, $this->touched_folders))
			$this->touched_folders[]=$mailbox_name;


		$box = $this->utf7_encode($this->_escape( $mailbox_name));
		$this->clean($box, 'mailbox');

		$command = "SELECT \"$box\"\r\n";

		$this->send_command($command);
		$res = $this->get_response(false, true);
		$status = $this->check_response($res, true);
		$uidvalidity = 0;
		$exists = 0;
		$uidnext = 0;
		$flags = array();
		$pflags = array();
		foreach ($res as $vals) {
			if (in_array('UIDNEXT', $vals)) {
				foreach ($vals as $i => $v) {
					if (intval($v) && isset($vals[($i - 1)]) && $vals[($i - 1)] == 'UIDNEXT') {
						$uidnext = $v;
					}
				}
			}
			if (in_array('UIDVALIDITY', $vals)) {
				foreach ($vals as $i => $v) {
					if (intval($v) && isset($vals[($i - 1)]) && $vals[($i - 1)] == 'UIDVALIDITY') {
						$uidvalidity = $v;
					}
				}
			}
			if (in_array('PERMANENTFLAGS', $vals)) {
				$collect_flags = false;
				foreach ($vals as $i => $v) {
					if ($v == ')') {
						$collect_flags = false;
					}
					if ($collect_flags) {
						$pflags[] = $v;
					}
					if ($v == '(') {
						$collect_flags = true;
					}
				}
			}
			if (in_array('FLAGS', $vals)) {
				$collect_flags = false;
				foreach ($vals as $i => $v) {
					if ($v == ')') {
						$collect_flags = false;
					}
					if ($collect_flags) {
						$flags[] = $v;
					}
					if ($v == '(') {
						$collect_flags = true;
					}
				}
			}
			if (in_array('EXISTS', $vals)) {
				foreach ($vals as $i => $v) {
					if (intval($v) && isset($vals[($i + 1)]) && $vals[($i + 1)] == 'EXISTS') {
						$exists = $v;
					}
				}
			}
		}

		$mailbox=false;
		if ($status) {
			$mailbox['name']=$mailbox_name;
			$mailbox['uidnext'] = $uidnext;
			$mailbox['uidvalidity'] = $uidvalidity;
			$mailbox['messages'] = $exists;
			$mailbox['flags'] = $flags;
			$mailbox['permanentflags'] = $pflags;

			$this->selected_mailbox=$mailbox;
		}
		return $mailbox;
	}

	/**
	 * Get's the number and UID's of unseen messages of a mailbox
	 *
	 * @param <type> $folder
	 * @return <type>
	 */

	public function get_unseen($mailbox=false) {

		if($mailbox){
			if(!$this->select_mailbox($mailbox)){
				return false;
			}
		}

		#some servers don't seem to support brackets
		#$command = "UID SEARCH (UNSEEN) ALL\r\n";
		
		$command = "UID SEARCH UNSEEN ALL\r\n";
		
		$this->send_command($command);
		$res = $this->get_response(false, true);
		$status = $this->check_response($res, true);
		$unseen = 0;
		$uids = array();
		if ($status) {
			array_pop($res);
			foreach ($res as $vals) {
				foreach ($vals as $v) {

					if (is_numeric($v)) {
						$unseen++;
						$uids[] = $v;
					}
				}
			}
		}

		$this->selected_mailbox['unseen']=$unseen;

		//go_debug($unseen);
		return array('count'=>$unseen, 'uids'=>$uids);
	}


	/**
	 * Returns a sorted list of mailbox UID's
	 *
	 * @param <type> $sort
	 * @param <type> $reverse
	 * @param <type> $filter
	 * @return <type>
	 */
	public function sort_mailbox($sort='ARRIVAL', $reverse=false, $filter='ALL') {

		if(empty($filter)){
			$filter = 'ALL';
		}
		
		if(!$this->selected_mailbox)
			throw new Exception('No mailbox selected');

		$this->get_capability();

		if (($sort == 'THREAD_R' || $sort == 'THREAD_O')) {
			if ($sort == 'THREAD_O') {
				if (stristr($this->capability, 'ORDEREDSUBJECT')) {
					$ret =  $this->thread_sort($sort, $filter);
					$this->sort_count = $ret['total'];
					return $ret;
				}
				else {
					$uids=$this->server_side_sort('ARRIVAL', false, $filter);
					$this->sort_count = count($uids);
					return $uids;
				}
			}
			if ($sort == 'THREAD_R') {
				if (stristr($this->capability, 'THREAD')) {
					$ret = $this->thread_sort($sort, $filter);
					$this->sort_count = $ret['total'];
					return $ret;
				}
				else {
					$uids=$this->server_side_sort('ARRIVAL', false, $filter);
					$this->sort_count = count($uids);
					return $uids;
				}
			}
		}
		elseif (stristr($this->capability, 'SORT')) {
			$uids=$this->server_side_sort($sort, $reverse, $filter);
			$this->sort_count = count($uids);
			return $uids;
		}
		else {
			$uids=$this->client_side_sort($sort, $reverse, $filter);
			$this->sort_count = count($uids);
			return $uids;
		}
	}

	private function server_side_sort($sort, $reverse, $filter) {
		go_debug("server_side_sort($sort, $reverse, $filter)");

		$this->clean($sort, 'keyword');
		//$this->clean($filter, 'keyword');
		$command = 'UID SORT ('.$sort.') US-ASCII '.$filter."\r\n";
		$this->send_command($command);
		/*if ($this->disable_sort_speedup) {
			$speedup = false;
		}
		else {*/
		$speedup = true;
		//}
		$res = $this->get_response(false, true, 8192, $speedup);
		$status = $this->check_response($res, true);
		$uids = array();
		foreach ($res as $vals) {
			if ($vals[0] == '*' && strtoupper($vals[1]) == 'SORT') {
				array_shift($vals);
				array_shift($vals);
				$uids = array_merge($uids, $vals);
			}
			else {
				if (preg_match("/^(\d)+$/", $vals[0])) {
					$uids = array_merge($uids, $vals);
				}
			}
		}
		unset($res);
		if ($reverse) {
			$uids = array_reverse($uids);
		}
		return $status ? $uids : false;
	}

	/**
	 * Function is only used if server doesn't support remote SORT
	 *
	 * @param <type> $terms
	 * @param <type> $sort
	 * @param <type> $reverse
	 * @return <type>
	 */
	public function search($terms) {
		//$this->clean($this->search_charset, 'charset');
		$this->clean($terms, 'search_str');

		/*
		 * Sending charset along doesn't work on iMailserver.
		 * Without seems to work on different servers.
		 */
		$charset = '';
		//$charset =  'CHARSET UTF-8 ';
		
		$command = 'UID SEARCH '.$charset.$terms."\r\n";
		$this->send_command($command);
		$result = $this->get_response(false, true);
		$status = $this->check_response($result, true);
		$res = array();
		if ($status) {
			array_pop($result);
			foreach ($result as $vals) {
				foreach ($vals as $v) {
					if (preg_match("/^\d+$/", $v)) {
						$res[] = $v;
					}
				}
			}
		}
		return $res;
	}


	/* use the FETCH command to manually sort the mailbox */
	private function client_side_sort($sort, $reverse, $filter='ALL') {
		
		go_debug("imap::client_side_sort($sort, $reverse, $filter)");

		$uid_string='1:*';
		if(!empty($filter) && $filter !='ALL'){
			$uids = $this->search($filter);
			if(!count($uids)){
				return array();
			}else
			{
				$uid_string=implode(',', $uids);
			}
		}

		$this->clean($sort, 'keyword');
		$command1 = 'UID FETCH '.$uid_string.' ';
		switch ($sort) {
//			Doesn't work on some servers. Use internal date for these.
//			case 'DATE':
//			case 'R_DATE':
//				$command2 = "BODY.PEEK[HEADER.FIELDS (DATE)]";
//				$key = "BODY[HEADER.FIELDS";
//				break;
//			case 'SIZE':
			case 'R_SIZE':
				$command2 = "RFC822.SIZE";
				$key = "RFC822.SIZE";
				break;
			case 'ARRIVAL':
				$command2 = "INTERNALDATE";
				$key = "INTERNALDATE";
				break;
			case 'R_ARRIVAL':
				$command2 = "INTERNALDATE";
				$key = "INTERNALDATE";
				break;
			case 'FROM':
			case 'R_FROM':
				$command2 = "BODY.PEEK[HEADER.FIELDS (FROM)]";
				$key = "BODY[HEADER.FIELDS";
				break;
			case 'SUBJECT':
			case 'R_SUBJECT':
				$command2 = "BODY.PEEK[HEADER.FIELDS (SUBJECT)]";
				$key = "BODY[HEADER.FIELDS";
				break;
			default:
				$command2 = "INTERNALDATE";
				$key = "INTERNALDATE";
				break;
		}
		$command = $command1.'('.$command2.")\r\n";
		
		$this->send_command($command);
		$res = $this->get_response(false, true);
		$status = $this->check_response($res, true);
		$uids = array();
		$sort_keys = array();
		foreach ($res as $vals) {
			if (!isset($vals[0]) || $vals[0] != '*') {
				continue;
			}
			$uid = 0;
			$sort_key = 0;
			$body = false;
			foreach ($vals as $i => $v) {
				if ($body) {
					if ($v == ']' && isset($vals[$i + 1])) {
						if ($command2 == "BODY.PEEK[HEADER.FIELDS (DATE)]") {
							$sort_key = strtotime(trim(substr($vals[$i + 1], 5)));
						}
						else {
							$sort_key = $vals[$i + 1];
						}
						$body = false;
					}
				}
				if (strtoupper($v) == 'UID') {
					if (isset($vals[($i + 1)])) {
						$uid = $vals[$i + 1];
						$uids[] = $uid;
					}
				}
				if ($key == strtoupper($v)) {
					if (substr($key, 0, 4) == 'BODY') {
						$body = 1;
					}
					elseif (isset($vals[($i + 1)])) {
						if ($key == "INTERNALDATE") {
							$sort_key = strtotime($vals[$i + 1]);
						}
						else {
							$sort_key = $vals[$i + 1];
						}
					}
				}
			}
			if ($sort_key && $uid) {
				$sort_keys[$uid] = $sort_key;
			}
		}

		if (count($sort_keys) != count($uids)) {
			//echo 'BUG: Client side sort array mismatch';
			//exit;
		}
		unset($res);
		natcasesort($sort_keys);
		$uids = array_keys($sort_keys);
		if ($reverse) {
			$uids = array_reverse($uids);
		}
		return $status ? $uids : false;
	}
	/* use the THREAD extension to get the sorted UID list and thread data */
	private function thread_sort($sort ,$filter) {
		$this->clean($filter, 'keyword');
		if (substr($sort, 7) == 'R') {
			$method = 'REFERENCES';
		}
		else {
			$method = 'ORDEREDSUBJECT';
		}
		$command = 'UID THREAD '.$method.' US-ASCII '.$filter."\r\n";
		$this->send_command($command);
		$res = $this->get_response();
		$status = $this->check_response($res);
		$uid_string = '';
		foreach ($res as $val) {
			if (strtoupper(substr($val, 0, 8)) == '* THREAD') {
				$uid_string .= ' '.substr($val, 8);
			}
		}
		unset($res);
		$uids = array();
		$thread_data = array();
		$uid_string = str_replace(array(' )', ' ) ', ')', ' (', ' ( ', '( '), array(')', ')', ')', '(', '(', '('), $uid_string);
		$branches = array();
		$level = 0;
		$thread = 0;
		$last_id = 0;
		$offset = 0;
		$parents = array();
		while($uid_string) {
			switch ($uid_string{0}) {
				case ' ':
					$level++;
					$offset++;
					$parents[$level] = $last_id;
					$uid_string = substr($uid_string, 1);
					break;
				case '(':
					$level++;
					if ($level == 2) {
						$parents[$level] = $thread;
					}
					$uid_string = substr($uid_string, 1);
					break;
				case ')':
					$uid_string = substr($uid_string, 1);
					if ($offset) {
						$level -= $offset;
						$offset = 0;
					}
					$level--;
					break;
				default:
					if (preg_match("/^(\d+)/", $uid_string, $matches)) {
						if ($level == 1) {
							$thread = $matches[1];
							$parents = array(1 => 0);
						}
						if (!isset($parents[$level])) {
							if (isset($parents[$level - 1])) {
								$parents[$level] = $parents[$level - 1];
							}
							else {
								$parents[$level] = 0;
							}
						}
						$thread_data[$thread][$matches[1]] = array('parent' => $parents[$level], 'level' => $level, 'thread' => $thread);
						$parents[$level] = $thread;
						$last_id = $matches[1];
						$uid_string = substr($uid_string, strlen($matches[1]));
					}
					else {
						echo 'BUG'.$uid_string."\r\n";
						;
						$uid_string = substr($uid_string, 1);
					}
			}
		}
		$thread_data = array_reverse($thread_data);
		$new_thread_data = array();
		$threads = array();
		foreach ($thread_data as $vals) {
			foreach ($vals as $i => $v) {
				$uids[] = $i;
				if ($v['parent'] && isset($new_thread_data[$v['parent']])) {
					if (isset($new_thread_data[$v['thread']]['reply_count'])) {
						$new_thread_data[$v['thread']]['reply_count']++;
					}
					else {
						$new_thread_data[$v['thread']]['reply_count'] = 1;
					}
				}
				else {
					$threads[] = $i;
				}
				$new_thread_data[$i] = $v;
			}
		}
		return array('uids' => $uids, 'total' => count($uids), 'thread_data' => $new_thread_data,
						'sort' => $sort, 'filter' => $filter, 'timestamp' => time(), 'threads' => $threads);

	}


	/**
	 * Get's message headers of a single message:
	 *
	 * $message=array(
					'to'=>'',
					'cc'=>'',
					'bcc'=>'',
					'from'=>'',
					'subject'=>'',
					'uid'=>'',
					'size'=>'',
					'internal_date'=>'',
					'date'=>'',
					'udate'=>'',
					'internal_udate'=>'',
					'x-priority'=>3,
					'reply-to'=>'',
					'content-type'=>'',
					'disposition-notification-to'=>'',
					'content-transfer-encoding'=>'',
					'charset'=>'',
					'seen'=>0,
					'flagged'=>0,
					'answered'=>0,
					'forwarded'=>0
				);
	 *
	 * @param <type> $uid
	 * @return <type>
	 */

	public function get_message_header($uid, $full_data=false){
		$headers = $this->get_message_headers(array($uid), $full_data);
		if(isset($headers[$uid])){
			return $headers[$uid];
		}else
		{
			return false;
		}
	}



	/**
	 * Get's message headers from an UID range:
	 *
	 * $message=array(
					'to'=>'',
					'cc'=>'',
					'bcc'=>'',
					'from'=>'',
					'subject'=>'',
					'uid'=>'',
					'size'=>'',
					'internal_date'=>'',
					'date'=>'',
					'udate'=>'',
					'internal_udate'=>'',
					'x-priority'=>3,
					'reply-to'=>'',
					'content-type'=>'',
					'disposition-notification-to'=>'',
					'content-transfer-encoding'=>'',
				 'charset'=>'',
					'seen'=>0,
					'flagged'=>0,
					'answered'=>0,
					'forwarded'=>0
				);
	 *
	 * @param <type> $uids
	 * @return <type>
	 */
	public function get_message_headers($uids, $full_data=false) {

		$sorted_string = implode(',', $uids);
		$this->clean($sorted_string, 'uid_list');
		$command = 'UID FETCH '.$sorted_string.' (FLAGS INTERNALDATE RFC822.SIZE BODY.PEEK[HEADER.FIELDS (SUBJECT FROM '.
						"DATE CONTENT-TYPE X-PRIORITY TO CC";

		if($full_data)
			$command .= " BCC REPLY-TO DISPOSITION-NOTIFICATION-TO CONTENT-TRANSFER-ENCODING MESSAGE-ID";

		$command .= ")])\r\n";
		
		$this->send_command($command);
		$res = $this->get_response(false, true);

		$status = $this->check_response($res, true);
		$tags = array('UID' => 'uid', 'FLAGS' => 'flags', 'RFC822.SIZE' => 'size', 'INTERNALDATE' => 'internal_date');
		$junk = array('SUBJECT', 'FROM', 'CONTENT-TYPE', 'TO', 'CC','BCC', '(', ')', ']', 'X-PRIORITY', 'DATE','REPLY-TO','DISPOSITION-NOTIFICATION-TO','CONTENT-TRANSFER-ENCODING', 'MESSAGE-ID');
		//$flds = array('uid','flags','size','internal_date','answered','seen','','reply-to', 'content-type','x-priority','disposition-notification-to');
		$headers = array();
		foreach ($res as $n => $vals) {
			if (isset($vals[0]) && $vals[0] == '*') {
				$message=array(
					'to'=>'',
					'cc'=>'',
					'bcc'=>'',
					'from'=>'',
					'subject'=>'',
					'uid'=>'',
					'size'=>'',
					'internal_date'=>'',
					'date'=>'',
					'udate'=>'',
					'internal_udate'=>'',
					'x-priority'=>3,
					'reply-to'=>'',
					'message-id'=>'',
					'content-type'=>'',
					'content-type-attributes'=>array(),
					'disposition-notification-to'=>'',
					'content-transfer-encoding'=>'',
					'charset'=>'',
					'seen'=>0,
					'flagged'=>0,
					'answered'=>0,
					'forwarded'=>0
				);

				$count = count($vals);
				for ($i=0;$i<$count;$i++) {
					if ($vals[$i] == 'BODY[HEADER.FIELDS') {
						$i++;
						while(isset($vals[$i]) && in_array($vals[$i], $junk)) {
							$i++;
						}

						$header = str_replace("\r\n", "\n", $vals[$i]);
						$header = preg_replace("/\n\s/", " ", $header);

						$lines = explode("\n", $header);

						foreach ($lines as $line) {
							if(!empty($line)) {
								$header = trim(strtolower(substr($line, 0, strpos($line, ':'))));

								if (!$header && !empty($last_header)) {
									$message[$last_header] .= "\n".trim($line);
								}else {
									if(isset($message[$header])){
										$message[$header] = trim(substr($line, (strpos($line, ':') + 1)));
										$last_header = $header;
									}
								}
							}
						}
					}
					elseif (isset($tags[strtoupper($vals[$i])])) {
						if (isset($vals[($i + 1)])) {
							if ($tags[strtoupper($vals[$i])] == 'flags' && $vals[$i + 1] == '(') {
								$n = 2;
								while (isset($vals[$i + $n]) && $vals[$i + $n] != ')') {
									$prop = strtolower(substr($vals[$i + $n],1));

									if(isset($message[$prop]))
										$message[$prop]=true;

									$n++;
								}
								$i += $n;
							}
							else {
								$prop = $tags[strtoupper($vals[$i])];
								
								if(isset($message[$prop]))
										$message[$prop] = trim($vals[($i + 1)]);
								$i++;
							}
						}
					}
				}
				if ($message['uid']) {
					if(isset($message['content-type'])) {
						$message['content-type']=strtolower($message['content-type']);
						if (strpos($message['content-type'], 'charset=')!==false) {
							if (preg_match("/charset\=([^\s]+)/", $message['content-type'], $matches)) {
								$message['charset'] = trim(str_replace(array('"', "'", ';'), '', $matches[1]));
							}							
						}
						if(preg_match("/([^\/]*\/[^;]*)(.*)/", $message['content-type'], $matches)){
							$message['content-type']=$matches[1];
							$atts = trim($matches[2], ' ;');							
							$atts=explode(';', $atts);
							
							for($i=0;$i<count($atts);$i++){
								$keyvalue=explode('=', $atts[$i]);
								if(isset($keyvalue[1]) && $keyvalue[0]!='boundary')
									$message['content-type-attributes'][trim($keyvalue[0])]=trim($keyvalue[1],' "');
							}
							
							//$message['content-type-attributes']=$atts;
						}
					}

					//sometimes headers contain some extra stuff between ()
					$message['date']=preg_replace('/\([^\)]*\)/','', $message['date']);

					$message['udate']=strtotime($message['date']);
					$message['internal_udate']=strtotime($message['internal_date']);
					if(empty($message['udate']))
						$message['udate']=$message['internal_udate'];

					$message['subject']=$this->mime_header_decode($message['subject']);
					$message['from']=$this->mime_header_decode($message['from']);
					$message['to']=$this->mime_header_decode($message['to']);
					$message['reply-to']=$this->mime_header_decode($message['reply-to']);
					if(isset($message['cc']))
						$message['cc']=$this->mime_header_decode($message['cc']);
					$headers[$message['uid']] = $message;
				}
			}
		}
		$final_headers = array();
		foreach ($uids as $v) {
			if (isset($headers[$v])) {
				$final_headers[$v] = $headers[$v];
			}
		}

		//go_debug($final_headers);
		return $final_headers;
	}


	/**
		* Check if the given mailbox root is valid and return it with the correct delimiter
		*
		* @param $mbroot The Mailbox root. (eg. INBOX/)
		* @access public
		* @return mixed Mailbox root with delimiter or false on failure
		*/

	function check_mbroot($mbroot) {
		$mbroot = trim($mbroot);

		$list = $this->get_folders('', false,'%');
		//go_debug($list);
		if (is_array($list)) {
			while ($folder = array_shift($list)) {
				if (!$this->delimiter && strlen($folder['delimiter']) > 0) {
					$this->set_mailbox_delimiter($folder['delimiter']);

					if (substr($mbroot, -1) == $this->delimiter) {
						$mbroot = substr($mbroot, 0, -1);
					}
				}

				if ($folder['name'] == $mbroot) {
					return $mbroot.$this->delimiter;
				}
			}
		}
		return false;
	}


	/**
	 * Get's an array with two keys. usage and limit in bytes.
	 *
	 * @return <type>
	 */
	public function get_quota() {
		$command = "GETQUOTAROOT \"INBOX\"\r\n";

		$this->send_command($command);
		$res = $this->get_response();
		$status = $this->check_response($res);
		if($status){
			foreach($res as $response){
				if(strpos($response, 'STORAGE')!==false){
					$parts = explode(" ", $response);
					$storage_part = array_search("STORAGE", $parts);
					if ($storage_part>0){						
						return array(
							'usage'=>intval($parts[$storage_part+1]),
							'limit'=>intval($parts[$storage_part+2]));
					}
				}
			}
		}
		return false;
	}

	/**
	 * Get the structure of a message
	 *
	 * @param <type> $uid
	 * @return <type>
	 */
	public function get_message_structure($uid) {
		$this->clean($uid, 'uid');
		$part_num = 1;
		$struct = array();
		$command = "UID FETCH $uid BODYSTRUCTURE\r\n";
		$this->send_command($command);
		$result = $this->get_response(false, true);

		while (isset($result[0][0]) && isset($result[0][1]) && $result[0][0] == '*' && strtoupper($result[0][1]) == 'OK') {
			array_shift($result);
		}
		$status = $this->check_response($result, true);
		$response = array();
		if (!isset($result[0][4])) {
			$status = false;
		}
		if ($status) {
			if (strtoupper($result[0][4]) == 'UID') {
				$response = array_slice($result[0], 7, -1);
			}
			else {
				$response = array_slice($result[0], 5, -1);
			}
			$response = $this->split_toplevel_result($response);
			if (count($response) > 1) {
				$struct = $this->parse_multi_part($response, 1, 1);
			}
			else {
				$struct[1] = $this->parse_single_part($response);
			}
		}

		return $struct;
	}

	/**
	 * Find's the first message part in a structure returned from
	 * get_message_structure that matches the parameters given.
	 *
	 * Useful to find the first text/plain or text/html for example to find the
	 * message body.
	 *
	 * @param <type> $struct
	 * @param <type> $imap_id
	 * @param <type> $type
	 * @param <type> $subtype
	 * @return <type>
	 */

	public function find_message_parts($struct, $imap_id, $type='text', $subtype=false, $parts=array()) {
		if (!is_array($struct) || empty($struct)) {
			return $parts;
		}
		foreach ($struct as $id => $vals) {
			if ($imap_id && $id == $imap_id) {
				$vals['imap_id'] = $id;
				$parts[] = $vals;
			}
			elseif (!$imap_id && isset($vals['type']) && $vals['type'] == $type) {
				if ($subtype) {
					if ($subtype == $vals['subtype']) {
						$vals['imap_id'] = $id;
						$parts[] = $vals;
					}
				}
				else {
					$vals['imap_id'] = $id;
					$parts[] = $vals;
				}
			}
			if (empty($res) && isset($vals['subs'])) {
				$this->find_message_parts($vals['subs'], $imap_id, $type, $subtype, $parts);				
			}
		}
		return $parts;
	}




	public function has_alternative_body($struct){

		//go_debug($struct);

		if (!is_array($struct) || empty($struct)) {
			return false;
		}

		if(isset($struct['type']) && $struct['type']=='message' && strtolower($struct['subtype'])=='alternative'){
			return true;
		}

		foreach ($struct as $id => $vals) {
			if(isset($vals['type']) && $vals['type']=='message' && strtolower($struct['subtype'])=='alternative'){
				return true;
			}elseif (isset($vals['subs']) && (!isset($vals['subtype']) || $vals['subtype']!='rfc822')){
				if($this->has_alternative_body($vals['subs'])){
					return true;
				}
			}
		}

		return false;
	}


	/**
	 * Find's the first message part in a structure returned from
	 * get_message_structure that matches the parameters given.
	 *
	 * Useful to find the first text/plain or text/html for example to find the
	 * message body.
	 *
	 * @param <type> $struct
	 * @param <type> $imap_id
	 * @param <type> $type
	 * @param <type> $subtype
	 * @return <type>
	 */

	public function find_body_parts($struct, $type='text', $subtype='html', &$parts=false) {
		if (!is_array($struct) || empty($struct)) {
			return $parts;
		}

		if(!$parts)
		{
			$parts=array('text_found'=>false, 'parts'=>array());
		}
		foreach ($struct as $id => $vals) {

			//go_debug($vals);
			if(is_array($vals)){
				if (isset($vals['type'])){

					$vals['imap_id'] = $id;
					//go_debug($vals);

					if ($vals['type'] == $type && $subtype == $vals['subtype'] && $vals['disposition']!='attachment') {

						$parts['text_found']=true;
						$parts['parts'][] = $vals;

					}elseif($vals['type']=='image' && in_array($vals['subtype'], array('jpg','jpeg','gif','png','bmp')) && $vals['disposition']=='inline' && empty($vals['id']))
					{
						//an inline image without ID. We'll display in the part order. Apple
						//mail sends mail like this.
						$parts['parts'][]=$vals;
					}
				}
			
				//don't decent into message/RFC822 files. Sometimes they come nested in the body from the IMAP server.
				if (isset($vals['subs']) && (!isset($vals['subtype']) || $vals['subtype']!='rfc822')){

					$text_found_at_this_level = $parts['text_found'];
					$this->find_body_parts($vals['subs'], $type, $subtype, $parts);

					//

					/*
					 * If we found body parts for example 1.1 and 1.2 doesn't include a text
					 * attachment with number 2 like in this sample structure
					 *
					 * array (
	  1 =>
	  array (
		'subs' =>
		array (
		  '1.1' =>
		  array (
			'type' => 'text',
			'subtype' => 'plain',
			'charset' => 'iso-8859-1',
			'format' => 'flowed',
			'id' => false,
			'description' => false,
			'encoding' => '8bit',
			'size' => '279',
			'lines' => '15',
			'md5' => false,
			'disposition' => false,
			'language' => false,
			'location' => false,
			'name' => false,
			'filename' => false,
		  ),
		  '1.2' =>
		  array (
			'subs' =>
			array (
			  '1.2.1' =>
			  array (
				'type' => 'text',
				'subtype' => 'html',
				'charset' => 'iso-8859-1',
				'id' => false,
				'description' => false,
				'encoding' => '7bit',
				'size' => '1028',
				'lines' => '28',
				'md5' => false,
				'disposition' => false,
				'language' => false,
				'location' => false,
				'name' => false,
				'filename' => false,
			  ),
			  '1.2.2' =>
			  array (
				'type' => 'image',
				'subtype' => 'jpeg',
				'name' => 'gass-sign.jpg',
				'id' => '<part1.04070803.02030505@gassinstallasjon.no>',
				'description' => false,
				'encoding' => 'base64',
				'size' => '19818',
				'md5' => false,
				'disposition' => 'inline',
				'language' => '(',
				'location' => 'filename',
				'filename' => false,
				'charset' => false,
				'lines' => false,
			  ),
			  'type' => 'message',
			  'subtype' => 'related',
			),
		  ),
		  'type' => 'message',
		  'subtype' => 'alternative',
		),
	  ),
	  2 =>
	  array (
		'type' => 'text',
		'subtype' => 'plain',
		'name' => 'gass-skriver.txt',
		'charset' => 'us-ascii',
		'id' => false,
		'description' => false,
		'encoding' => 'base64',
		'size' => '32',
		'lines' => '0',
		'md5' => false,
		'disposition' => 'inline',
		'language' => '(',
		'location' => 'filename',
		'filename' => false,
	  ),
	  'type' => 'message',
	  'subtype' => 'mixed',
	)
					 */
					if(!$text_found_at_this_level && $parts['text_found'])
						break;
				}
			}
		}
		return $parts;
	}

	/**
	 * Find all attachment parts from a structure returned by get_message_structure
	 *
	 * @param <type> $struct
	 * @param <type> $skip_ids Skip thise ID's
	 * @param <type> $attachments
	 * @return <type>
	 */

	public function find_message_attachments($struct, $skip_ids=array(), $attachments=array()) {
		if (!is_array($struct) || empty($struct)) {
			return $attachments;
		}

		foreach ($struct as $id => $vals) {
			if(!is_array($vals) || in_array($id, $skip_ids))
				continue;

			if(isset($vals['type'])){
				$vals['imap_id'] = $id;
				
				//sometimes NIL is returned from Dovecot?!?
				if($vals['id']=='NIL')
					$vals['id']='';

				$attachments[]=$vals;
			}elseif(isset($vals['subs'])) {
				$attachments = $this->find_message_attachments($vals['subs'],$skip_ids,	$attachments);
			}
		}
		return $attachments;
	}

	/**
	 * Decodes a message part.
	 *
	 * @param <type> $str
	 * @param <type> $encoding Can be base64 or quoted-printable
	 * @param <type> $charset If this is given then the part will be converted to UTF-8 and illegal characters will be stripped.
	 * @return <type>
	 */

	public function decode_message_part($str, $encoding, $charset=false) {
		
		switch(strtolower($encoding)) {
			case 'base64':
				$str = base64_decode($str);
				break;
			case 'quoted-printable':
				$str =  quoted_printable_decode($str);
				break;
		}

		if($charset){

			//some clients don't send the charset.
			if($charset=='us-ascii')
				$charset = 'windows-1252';
			
			$str = String::clean_utf8($str, $charset);
			if($charset != 'utf-8') {
				$str = str_replace($charset, 'utf-8', $str);
			}
		}
		return $str;
	}

	/**
	 * Get's a message part and returned in binary form or UTF-8 charset.
	 * 
	 * @param <type> $uid
	 * @param <type> $part_no
	 * @param <type> $encoding
	 * @param <type> $charset
	 * @param <type> $peek
	 * @return <type>
	 */

	public function get_message_part_decoded($uid, $part_no, $encoding, $charset=false, $peek=false, $max=false) {
		go_debug("get_message_part_decoded($uid, $part_no, $encoding, $charset)");
		return $this->decode_message_part(
						$this->get_message_part($uid, $part_no, $peek, $max),
						$encoding,
						$charset
		);
	}


	/**
	 * Get the full body of a message part. Obtain the partnumbers with get_message_structure.
	 *
	 * @param <type> $uid
	 * @param <type> $message_part omit if you want the full message
	 * @param <type> $raw
	 * @param <type> $max
	 * @return <type>
	 */
	public function get_message_part($uid, $message_part=0, $peek=false, $max=false) {
		$this->clean($uid, 'uid');

		$peek_str = $peek ? '.PEEK' : '';

		if (empty($message_part)) {
			$command = "UID FETCH $uid BODY".$peek_str."[]\r\n";
		}
		else {
			//$this->clean($message_part, 'msg_part');
			$command = "UID FETCH $uid BODY".$peek_str."[$message_part]\r\n";
		}
		$this->send_command($command);

		$result = $this->get_response($max, true);
		$status = $this->check_response($result, true);
		$res = '';
		foreach ($result as $vals) {
			if ($vals[0] != '*') {
				continue;
			}
			$search = true;
			foreach ($vals as $v) {
				if ($v != ']' && !$search) {
					$res = trim(preg_replace("/\s*\)$/", '', $v));
					break 2;
				}
				if (stristr(strtoupper($v), 'BODY')) {
					$search = false;
				}
			}
		}
		return $res;
	}

	/**
	 * Start getting a message part for reading it line by line
	 *
	 * @param <type> $uid
	 * @param <type> $message_part
	 * @return <type>
	 */
	public function get_message_part_start($uid, $message_part=0) {
		$this->clean($uid, 'uid');
		if (empty($message_part)) {
			$command = "UID FETCH $uid BODY[]\r\n";
		}
		else {
			//$this->clean($message_part, 'msg_part');
			$command = "UID FETCH $uid BODY[$message_part]\r\n";
		}
		$this->send_command($command);
		$result = fgets($this->handle);

		//go_debug($result);
		$size = false;
		if (preg_match("/\{(\d+)\}\r\n/", $result, $matches)) {
			$size = $matches[1];
		}

		$this->last_line=fgets($this->handle);
		return $size;
	}
	/**
	 * Read message part line. get_message_part_start must be called first
	 *
	 * @return <type>
	 */
	public function get_message_part_line() {
		if (!$this->last_line) {
			$res = false;
		}else
		{
			$res = $this->last_line;
			$this->last_line = fgets($this->handle,1024);

			if ($this->check_response(array($this->last_line))) {
				$this->last_line = false;

				if(substr(rtrim($res),-1, 1)==')')
					$res = substr(rtrim($res),0,-1);
			}
		}

		return $res;
	}

	public function save_to_file($uid, $path, $imap_part_id=-1, $encoding=''){

		$fp = fopen($path, 'w+');

		if(!$fp)
			return false;

		/*
		 * Somehow fetching a message with an empty message part which should fetch it
		 * all doesn't work. (http://tools.ietf.org/html/rfc3501#section-6.4.5)
		 *
		 * That's why I first fetch the header and then the text.
		 */
		if($imap_part_id==-1){
			$header = $this->get_message_part($uid, 'HEADER')."\r\n\r\n";

			if(!fputs($fp, $header))
				return false;
			
			$imap_part_id='TEXT';
		}

		$size = $this->get_message_part_start($uid,$imap_part_id);

		while($line = $this->get_message_part_line()){
			
			switch(strtolower($encoding)) {
				case 'base64':
					$line=base64_decode($line);
					break;
				case 'quoted-printable':
					$line= quoted_printable_decode($line);
					break;
			}
			
			if(!fputs($fp, $line))
				return false;
		}

		fclose($fp);

		return true;
	}

	/**
	 * Set or clear flags of an UID range. Flags can be:
	 * 
	 * \Seen
	 * \Answered
	 * \Flagged
	 * \Deleted
	 * $Forwarded
	 *
	 * @param <type> $uids
	 * @param <type> $flags
	 * @param <type> $clear
	 * @return <type>
	 */
	public function set_message_flag($uids, $flags, $clear=false) {
		$status=false;
		$uid_strings = array();
		if (!empty($uids)) {
			if (count($uids) > 500) {
				while (count($uids) > 500) {
					$uid_strings[] = implode(',', array_splice($uids, 0, 500));
				}
				if (count($uids)) {
					$uid_strings[] = implode(',', $uids);
				}
			}
			else {
				$uid_strings[] = implode(',', $uids);
			}
		}
		else {
			$uid_strings[] = $uid_str;
		}
		foreach ($uid_strings as $uid_string) {
			if ($uid_string) {
				$this->clean($uid_string, 'uid_list');
			}

			if($clear) {
				$command = "UID STORE $uid_string -FLAGS ($flags)\r\n";
			}else {
				$command = "UID STORE $uid_string +FLAGS ($flags)\r\n";
			}
			$this->send_command($command);
			$res = $this->get_response();
			$status = $this->check_response($res);
			if (!$status) {
				return $status;
			}
		}
		return $status;
	}

	/**
	 * Copy a message from the currently selected mailbox to another mailbox
	 *
	 * @param <type> $uids
	 * @param <type> $mailbox
	 * @return <type>
	 */
	public function copy($uids, $mailbox) {
		$this->clean($mailbox, 'mailbox');

		$uid_string = implode(',',$uids);

		$command = "UID COPY $uid_string \"".$this->utf7_encode($mailbox)."\"\r\n";
		$this->send_command($command);
		$res = $this->get_response();
		return $this->check_response($res);
	}

	/**
	 * Move a message from the currently selected mailbox to another mailbox
	 *
	 * @param <type> $uids
	 * @param <type> $mailbox
	 * @param <type> $expunge
	 * @return <type>
	 */
	public function move($uids, $mailbox, $expunge=true) {

		if(!in_array($mailbox, $this->touched_folders)) {
			$this->touched_folders[]=$mailbox;
		}

		if(!$this->copy($uids, $mailbox))
			return false;

		return $this->delete($uids, $expunge);
	}

	/**
	 * Delete messages from the currently selected mailbox
	 * 
	 * @param <type> $uids
	 * @param <type> $expunge
	 * @return <type>
	 */
	public function delete($uids, $expunge=true) {
		$status = $this->set_message_flag($uids, '\Deleted \Seen');
		if(!$status)
			return false;

		return !$expunge || $this->expunge();
	}

	/**
	 * Expunge the mailbox. It will remove all the messages marked with the
	 *  \Deleted flag.
	 *
	 * @return <type>
	 */
	public function expunge() {
		$this->send_command("EXPUNGE\r\n");
		$res = $this->get_response();
		return $this->check_response($res);
	}

	private function addslashes($mailbox){
		return $this->_escape( $mailbox);
	}

	/**
	 * Removes a mailbox
	 * 
	 * @param <type> $mailbox
	 * @return <type>
	 */
	public function delete_folder($mailbox) {
		$this->clean($mailbox, 'mailbox');

		$this->unsubscribe($mailbox);

		$command = 'DELETE "'.$this->addslashes($this->utf7_encode($mailbox))."\"\r\n";
		$this->send_command($command);
		$result = $this->get_response(false);
		return $this->check_response($result, false);		
	}

	public function get_folder_tree($mailbox) {
		$this->clean($mailbox, 'mailbox');
		$delim = $this->get_mailbox_delimiter();
		return $this->get_folders($mailbox.$delim,true);
	}

	/**
	 * Rename a mailbox
	 *
	 * @param <type> $mailbox
	 * @param <type> $new_mailbox
	 * @return <type>
	 */
	public function rename_folder($mailbox, $new_mailbox) {
		$this->clean($mailbox, 'mailbox');
		$this->clean($new_mailbox, 'mailbox');

		$delim = $this->get_mailbox_delimiter();

		$children = $this->get_folders($mailbox.$delim);

		//go_debug($children);
		//throw new Exception('test');

		$command = 'RENAME "'.$this->addslashes($this->utf7_encode($mailbox)).'" "'.
						$this->addslashes($this->utf7_encode($new_mailbox)).'"'."\r\n";
		$this->send_command($command);
		$result = $this->get_response(false);
		
		$status = $this->check_response($result, false);

		if($status && $this->unsubscribe($mailbox) && $this->subscribe($new_mailbox)){			

			foreach($children as $old_child) {
				if($old_child['name']!=$mailbox){
				 $old_child = $old_child['name'];
				 $pos = strpos($old_child, $mailbox);
				 $new_child = substr_replace($old_child, $new_mailbox, $pos, strlen($mailbox));

				 $this->unsubscribe($old_child);
				 $this->subscribe($new_child);
				}
			}
			return true;
		}else
		{
			return false;
		}		
	}

	/**
	 * Create a new mailbox
	 *
	 * @param <type> $mailbox
	 * @param <type> $subscribe
	 * @return <type>
	 */
	public function create_folder($mailbox, $subscribe=true) {
		$this->clean($mailbox, 'mailbox');

		$command = 'CREATE "'.$this->addslashes($this->utf7_encode($mailbox)).'"'."\r\n";

		$this->send_command($command);
		$result = $this->get_response(false);

		$status = $this->check_response($result, false);

		if(!$status)
			return false;

		return !$subscribe || $this->subscribe($mailbox);
	}
	
	
	/**
	 * Subscribe to a mailbox
	 * 
	 * @param <type> $mailbox
	 * @return <type>
	 */
	public function subscribe($mailbox){
		$command = 'SUBSCRIBE "'.$this->addslashes($this->utf7_encode($mailbox)).'"'."\r\n";
		$this->send_command($command);
		$result = $this->get_response(false, true);
		return $this->check_response($result, true);
	}

	/**
	 * Unsubscribe a mailbox
	 *
	 * @param <type> $mailbox
	 * @return <type>
	 */
	public function unsubscribe($mailbox){
		$command = 'UNSUBSCRIBE "'.$this->addslashes($this->utf7_encode($mailbox)).'"'."\r\n";
		$this->send_command($command);
		$result = $this->get_response(false, true);
		return $this->check_response($result, true);
	}

	/**
	 * Get the next UID for the selected mailbox
	 *
	 * @return StringHelper the next UID on the IMAP server
	 */

	public function get_uidnext(){

		if(empty($this->selected_mailbox['uidnext'])){			
			$command = 'STATUS "'.$this->addslashes($this->utf7_encode($this->selected_mailbox['name'])).'" (UIDNEXT)'."\r\n";
			$this->send_command($command);
			$result = $this->get_response(false, true);
			
			$vals = array_shift($result);
			if($vals){
				foreach ($vals as $i => $v) {
					if (intval($v) && isset($vals[($i - 1)]) && $vals[($i - 1)] == 'UIDNEXT') {
						$this->selected_mailbox['uidnext'] = $v;
					}
				}
			}
		}

		return $this->selected_mailbox['uidnext'];		
	}

	/**
	 * End's a line by line append operation
	 *
	 * @return <type>
	 */
	public function append_end() {
		$result = $this->get_response(false, true);
		return  $this->check_response($result, true);
		/*if($status){
			return !empty($this->selected_mailbox['uidnext']) ? $this->selected_mailbox['uidnext'] : true;
		}*/
	}

	/**
	 * Feed data when after append_start is called to start an append operation
	 *
	 * @param <type> $string
	 * @return <type>
	 */
	public function append_feed($string) {
		return fwrite($this->handle, $string."\r\n");
	}

	/**
	 * Start an append operation. Data can be fed line by line with append_feed
	 * after this function is called.
	 *
	 * @param <type> $mailbox
	 * @param <type> $size
	 * @param <type> $flags
	 * @return <type>
	 */
	public function append_start($mailbox, $size, $flags = "") {
		//Select mailbox first so we can predict the UID.
		$this->select_mailbox($mailbox);

		$this->clean($mailbox, 'mailbox');
		$this->clean($size, 'uid');
		$command = 'APPEND "'.$this->utf7_encode($mailbox).'" ('.$flags.') {'.$size."}\r\n";
		$this->send_command($command);
		$result = fgets($this->handle);
		if (substr($result, 0, 1) == '+') {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Append a message to a mailbox
	 * 
	 * @param <type> $mailbox
	 * @param <type> $data
	 * @param <type> $flags See set_message_flag
	 * @return <type>
	 */
	public function append_message($mailbox, $data, $flags=""){

		go_debug("imap::append_message($mailbox, data, $flags);");

		if(!$this->append_start($mailbox, strlen($data), $flags))
			return false;

		if(!$this->append_feed($data))
			return false;

		return $this->append_end();
	}


	/**
	 * Extract uuencoded attachment from a text/plain body. Some mail clients
	 * embed attachments in the text body. This function will take them out and
	 * retrn them in an array.
	 *
	 * @param <type> $body
	 * @return <type>
	 *
	 */
	public function extract_uuencoded_attachments(&$body)
	{
		$body = str_replace("\r", '', $body);
		$regex = "/(begin ([0-7]{3}) (.+))\n(.+)\nend/Us";

		preg_match_all($regex, $body, $matches);

    $attachments = array();

    for ($i = 0; $i < count($matches[3]); $i++) {
			$boundary	= $matches[1][$i];
			$fileperm	= $matches[2][$i];
			$filename	= $matches[3][$i];

			$size = strlen($matches[4][$i]);

			$mime = File::get_mime($matches[3][$i]);
			$ct = explode('/', $mime);
			$attachments[]=array(
				'boundary'=>$matches[1][$i],
				'permissions'=>$matches[2][$i],
				'name'=>$matches[3][$i],
				'data'=>$matches[4][$i],
				'disposition'=>'ATTACHMENT',
				'encoding'=>'',
				'type'=>$ct[0],
				'subtype'=>$ct[1],
				'size'=>$size,
				'human_size'=>Number::format_size($size)
			);
    }

    //remove it from the body.
    $body = preg_replace($regex, "", $body);
    //go_debug($body);

    return $attachments;
	}
}
