<?php

namespace GO\Base\Mail;

use Exception;
use GO;
use GO\Base\Fs\File;
use GO\Base\Mail\Exception\ImapAuthenticationFailedException;
use go\core\util\DateTime;

class Imap extends ImapBodyStruct
{

	const SORT_NAME='NAME';
	const SORT_FROM='FROM';
	const SORT_TO='TO';
	const SORT_DATE='DATE';
	const SORT_ARRIVAL='ARRIVAL';
	const SORT_SUBJECT='SUBJECT';
	const SORT_SIZE='SIZE';

	/**
	 * @var resource|bool
	 */
	var $handle=false;

	var $ssl=false;
	var $server='';
	var $port=143;
	var $username='';
	var $password='';

	var $starttls=false;

	var $auth='plain';

	/**
	 * @var null|string
	 */
	private $token;

	var $selected_mailbox=false;

	var $delimiter=false;
	// Store all search result UIDs
	var $allUids = 0;

	var $sort_count = 0;

	var $gmail_server = false;

	var $permittedFlags = false;

	public $ignoreInvalidCertificates = false;

	public static $systemFlags = array(
		'Seen',
		'Answered',
		'Flagged',
		'Deleted',
		'Draft',
		'Recent'
	);

	public function __construct(){

	}

	public function __destruct()
	{
		$this->disconnect();
	}

	public function checkConnection() :bool
	{
		if (!is_resource($this->handle)){
			return $this->connect(
							$this->server,
							$this->port,
							$this->username,
							$this->password,
							$this->ssl,
							$this->starttls,
							$this->auth);
		}
		return true;
	}

	/**
	 * Connects to the IMAP server and authenticates the user
	 *
	 * @param string $server
	 * @param int $port
	 * @param string $username
	 * @param string $password
	 * @param bool $ssl
	 * @param bool $starttls
	 * @param string $auth
	 * @param string|null $token
	 * @return bool
	 * @throws ImapAuthenticationFailedException
	 */

	public function connect(string $server, int $port, string $username, string $password, bool $ssl=false, bool $starttls=false, string $auth='plain', ?string $token= null ) :bool
	{
		if (empty($password) && strtolower($auth) == 'plain') {
			throw new ImapAuthenticationFailedException('Authentication failed for user ' . $username . ' on IMAP server ' . $this->server);
		}
//		\GO::debug("imap::connect($server, $port, $username, ***, $ssl, $starttls, $auth, ***)");

		$this->ssl = $ssl;
//		$this->starttls = ($starttls && $auth !== 'azure');
		$this->starttls = $starttls;
		$this->auth = strtolower($auth);

		if ($token) {
			$this->token = $token;
		}

		$this->server = $server;
		$this->port = $port;
		$this->username = $username;
		$this->password = $password;

		$context_options = null;//array(); -> Must be an associative array of associative arrays in the format $arr['wrapper']['option'] = $value, or null
		if ($this->ignoreInvalidCertificates) {
			$context_options = array('ssl' => array(
				"verify_peer" => false,
				"verify_peer_name" => false
			));
		}

		$streamContext = stream_context_create($context_options);

		$errorno = 0;
		$errorstr = '';
		$remote = $this->ssl ? 'ssl://' : '';
		$remote .= $this->server . ":" . $this->port;

		$this->handle = stream_socket_client($remote, $errorno, $errorstr, 10, STREAM_CLIENT_CONNECT, $streamContext);
		if (!is_resource($this->handle)) {
			throw new \Exception('Failed to open socket #' . $errorno . '. ' . $errorstr);
		}
		stream_set_timeout($this->handle, 10);
//		$this->metadata = stream_get_meta_data($this->handle); // for debugging purposes
		$greeting = trim(fgets($this->handle, 8192));
//		$this->handleGreeting($greeting);
		if (self::$debug && $greeting) {
			go()->debug('S: ' . $greeting);
		}
		return $this->authenticate($username, $password);
	}

//	private function handleGreeting($greeting) {
//		//some imap servers like dovecot respond with the capability after login.
//		//Set this in the session so we don't need to do an extra capability command.
//		if(($startpos = strpos($greeting, 'CAPABILITY'))!==false){
//			\GO::debug("Use capability from login");
//			$endpos=  strpos($greeting, ']', $startpos);
//			if($endpos){
//				$capability = substr($greeting, $startpos, $endpos-$startpos);
//				\GO::session()->values['GO_IMAP'][$this->server]['imap_capability']=$capability;
//			}
//
//		}
//	}

	/**
	 * Disconnect from the IMAP server
	 *
	 * @return bool
	 */

	public function disconnect() :bool
	{
		if (is_resource($this->handle)) {
			$command = "LOGOUT\r\n";
			$this->send_command($command);
			$this->state = 'disconnected';
			$result = $this->get_response();
			$this->check_response($result);
			fclose($this->handle);

			foreach($this->errors as $error){
				error_log("IMAP error ". $this->username .'@' . $this->server .': ' . $error);
			}

			$this->handle=false;
			$this->selected_mailbox=false;

			return true;
		}
		return false;
	}

	protected $commands = [];
	protected $responses = [];
	protected $short_responses = [];

	private $state = "";
	private $capability;
	/**
	 * Handles authentication. You can optionally set
	 * $this->starttls or $this->auth to CRAM-MD5
	 *
	 * @param string $username
	 * @param string $pass
	 * @return bool
	 * @throws \Exception|ImapAuthenticationFailedException
	 */

	private function authenticate(string $username, string $pass): bool
	{
		if ($this->starttls) {
			$command = "STARTTLS\r\n";
			$this->send_command($command);
			$this->commands[trim($command)] = \GO\Base\Util\Date::getmicrotime();
			$response = $this->get_response();
			if (!empty($response)) {
				$end = array_pop($response);
				if (substr($end, 0, strlen('A' . $this->command_count . ' OK')) == 'A' . $this->command_count . ' OK') {
					if (!stream_socket_enable_crypto($this->handle, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
						throw new \Exception("Failed to enable TLS on socket");
					}
				} else {
					throw new \Exception("Failed to enable TLS: " . $end);
				}
			}
		}
		switch (strtolower($this->auth)) {
			case 'cram-md5':
				$this->banner = fgets($this->handle, 1024);
				$cram1 = 'A' . $this->command_number() . ' AUTHENTICATE CRAM-MD5' . "\r\n";
				fputs($this->handle, $cram1);
				$this->commands[trim($cram1)] = \GO\Base\Util\Date::getmicrotime();
				$response = fgets($this->handle, 1024);
				$this->responses[] = $response;
				$challenge = base64_decode(substr(trim($response), 1));
				$pass .= str_repeat(chr(0x00), (64 - strlen($pass)));
				$ipad = str_repeat(chr(0x36), 64);
				$opad = str_repeat(chr(0x5c), 64);
				$digest = bin2hex(pack("H*", md5(($pass ^ $opad) . pack("H*", md5(($pass ^ $ipad) . $challenge)))));
				$challenge_response = base64_encode($username . ' ' . $digest);
				$this->commands[trim($challenge_response)] = \GO\Base\Util\Date::getmicrotime();
				fputs($this->handle, $challenge_response . "\r\n");
				break;
			case 'googleoauth2':
			case 'azure':
				$str = base64_encode("user=$this->username\1auth=Bearer $this->token\1\1");
				$this->send_command("AUTHENTICATE XOAUTH2 " . $str . "\r\n");
				break;

			case 'xoauth2': // TODO: ???
				$login = 'A'.$this->command_number().' AUTHENTICATE XOAUTH2 ' . $pass . "\r\n";
				$this->commands[trim(str_replace($pass, 'xxxx', $login))] = \GO\Base\Util\Date::getmicrotime();
				fputs($this->handle, $login);
				break;

			default:
				$login = 'A' . $this->command_number() . ' LOGIN "' . $this->_escape($username) . '" "' . $this->_escape($pass) . "\"\r\n";
				$this->commands[trim(str_replace($pass, 'xxxx', $login))] = \GO\Base\Util\Date::getmicrotime();
				fputs($this->handle, $login);
				break;
		}
		$res = $this->get_response();

		$authed = false;
		if (is_array($res) && !empty($res)) {
			$response = array_pop($res);

			//Sometimes an extra empty line comes along
			if (!$response && count($res) == 2) {
				$response = array_pop($res);
			}

			$this->short_responses[$response] = \GO\Base\Util\Date::getmicrotime();
			if (!$this->auth) {
				if (isset($res[1])) {
					$this->banner = $res[1];
				}
				if (isset($res[0])) {
					$this->banner = $res[0];
				}
			}
			if (stristr($response, 'A' . $this->command_count . ' OK')) {
				$authed = true;
				$this->state = 'authed';

//				$this->handleGreeting($response);
			}else
			{
				throw new ImapAuthenticationFailedException('Authentication failed for user '.$username.' on IMAP server '.$this->server);
			}
		}
		return $authed;
	}

	private function _escape(string $str) :string
	{
		return str_replace(array('\\','"'), array('\\\\','\"'), $str);
	}
	private function _unescape(string $str) : string
	{
		return str_replace(array('\\\\','\"'), array('\\','"'), $str);
	}

	/**
	 * Gets the capabilities of the IMAP server. Useful to determine if the
	 * IMAP server supports server side sorting.
	 *
	 * @return string
 	 * @throws Exception
	 */

	public function get_capability() :string
	{
		//Cache capability in the session so this command is not used repeatedly
		$key = $this->server.':'.$this->username;
		if (isset(\GO::session()->values['GO_IMAP'][$key]['imap_capability'])) {
			$this->capability=\GO::session()->values['GO_IMAP'][$key]['imap_capability'];
		}else {
			if(!isset($this->capability)){
				$command = "CAPABILITY\r\n";
				$this->send_command($command);
				$response = $this->get_response();
				$this->capability = \GO::session()->values['GO_IMAP'][$key]['imap_capability'] = implode(' ', $response);
			}

		}
		return $this->capability;
	}

	/**
	 * Check if the IMAP server has a particular capability.
	 * eg. QUOTA, ACL, LIST-EXTENDED etc.
	 *
	 * @param string $str
	 * @return boolean
	 */
	public function has_capability($str) :bool
	{
		$has = stripos($this->get_capability(), $str)!==false;

		if(isset(\GO::session()->values['imap_disable_capabilites_'.$this->server])){
			if(!isset(\GO::config()->disable_imap_capabilities))
				\GO::config()->disable_imap_capabilities='';

			\GO::config()->disable_imap_capabilities.=" ".\GO::session()->values['imap_disable_capabilites_'.$this->server];
		}

		//We stumbled upon a dovecot server that crashed when sending a command
		//using LIST-EXTENDED. With this option we can workaround that issue.
		if($has && stripos(\GO::config()->disable_imap_capabilities, $str)!==false) {
			$has = false;
		}

		return $has;
	}


	public function get_acl($mailbox){

		$mailbox = $this->addslashes($this->utf7_encode($mailbox));
		$this->clean($mailbox, 'mailbox');

		$command = "GETACL \"$mailbox\"\r\n";
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

		$mailbox = $this->addslashes($this->utf7_encode($mailbox));
		$this->clean($mailbox, 'mailbox');

		$command = "SETACL \"$mailbox\" $identifier $permissions\r\n";
		//throw new \Exception($command);
		$this->send_command($command);

		$response = $this->get_response();

		return $this->check_response($response);
	}

	public function delete_acl($mailbox, $identifier){
		$mailbox = $this->addslashes($this->utf7_encode($mailbox));
		$this->clean($mailbox, 'mailbox');

		$command = "DELETEACL \"$mailbox\" $identifier\r\n";
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
			if(isset(\GO::session()->values['imap_delimiter'][$this->server])){
				$this->delimiter=\GO::session()->values['imap_delimiter'][$this->server];
			} else {
				$this->get_folders();
			}
		}
		return $this->delimiter;
	}

	private function set_mailbox_delimiter($delimiter) {
		$this->delimiter=\GO::session()->values['imap_delimiter'][$this->server]=$delimiter;
	}


	private $_subscribedFoldersCache;

	/**
	 * @param $mailboxName
	 * @param $flags
	 * @return bool
	 */
	private function _isSubscribed($mailboxName, $flags){

		if(strtoupper($mailboxName)=="INBOX"){
			return true;
		} else {
			if(!isset($this->_subscribedFoldersCache[$this->server.$this->username])){
				$this->_subscribedFoldersCache[$this->server.$this->username] = $this->list_folders(true, false, '', '*');
			}
			return isset($this->_subscribedFoldersCache[$this->server.$this->username][$mailboxName]);
		}
	}

	public function list_folders($listSubscribed=true, $withStatus=false, $namespace='', $pattern='*', $isRoot=false)
	{
		\GO::debug("list_folders($listSubscribed, $withStatus, $namespace, $pattern)");

		$listCmd = $listSubscribed ? 'LSUB' : 'LIST';

		$cmd = $listCmd.' "'.$this->addslashes($this->utf7_encode($namespace)).'" "'.$this->addslashes($this->utf7_encode($pattern)).'"';

		if($this->has_capability("LIST-EXTENDED") && !$listSubscribed) {
			$cmd .= ' RETURN (CHILDREN';

			if($withStatus){
				$cmd .= ' STATUS (MESSAGES UNSEEN)';
			}

			$cmd .= ')';
		}

//		\GO::debug($cmd);

		$cmd .= "\r\n";

		$this->send_command($cmd);
		$result = $this->get_response(false, true);

		if(!$this->check_response($result, true, false) && $this->has_capability("LIST-EXTENDED")){

			//some servers pretend to support list-extended but fail on the commands.
			//work around by disabling support and try again.
			\GO::session()->values['imap_disable_capabilites_'.$this->server]='LIST-EXTENDED';

			return $this->list_folders($listSubscribed, $withStatus, $namespace, $pattern, $isRoot);
		}
//		\GO::debug($result);

		$delim=false;

		$folders = array();
		foreach ($result as $vals) {
			if (!isset($vals[0])) {
				continue;
			}
			if ($vals[0] == 'A'.$this->command_count) {
				continue;
			}

			if($vals[1]==$listCmd){
				$flags = false;
				$folder = "";//$vals[($count - 1)];
				$flag = false;
				$delim_flag = false;
				$delim=false;
				$parent = '';
				$no_select = false;
				$can_have_kids = true;
				$has_no_kids=false;
				$has_kids = false;
				$marked = false;
				//$subscribed=$listSubscribed;

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
							$delim = $this->_unescape($v);
							$delim_flag = false;
						} elseif($delim) {
							$folder .= $v;
						}
					}
				}

				if(strtoupper($folder)=='INBOX')
					$folder='INBOX'; //fix lowercase or mixed case inbox strings

				if($folder=='dovecot')
					continue;

				if (!$this->delimiter) {
					$this->set_mailbox_delimiter($delim);
				}


				//in some case the mailserver return the mailbox twice when it has subfolders:
				//R: * LIST ( ) / Drafts
				//R: * LIST ( ) / Folder3
				//R: * LIST ( ) / Trash
				//R: * LIST ( ) / Sent
				//R: * LIST ( ) / Folder2
				//R: * LIST ( ) / INBOX
				//R: * LIST ( ) / INBOX/
				//R: * LIST ( ) / Test &- test/
				//R: * LIST ( ) / Test &- test

				//We trim the delimiter of the folder to fix that.
				$folder = trim($folder, $this->delimiter);

				if (stristr($flags, 'marked')) {
					$marked = true;
				}
				if (!stristr($flags, 'noinferiors')) {
					$can_have_kids = false;
				}
				if (stristr($flags, 'haschildren')) {
					$has_kids = true;
				}

				if (stristr($flags, 'hasnochildren')) {
					$has_no_kids = true;
				}

				$folder = $this->_unescape($folder);
				$decodedName = $this->utf7_decode($folder);
				$subscribed = $listSubscribed || $this->_isSubscribed($decodedName, $flags);

				$nonexistent = stristr($flags, 'NonExistent');

				if ($folder != 'INBOX' && (stristr($flags, 'noselect') || $nonexistent)) {
					$no_select = true;
				}

				if (!isset($folders[$decodedName]) && $decodedName) {

					$folders[$decodedName] = array(
									'delimiter' => $delim,
									'name' => $decodedName,
									'marked' => $marked,
									'noselect' => $no_select,
									'nonexistent' => $nonexistent,
									'noinferiors' => $can_have_kids,
									'haschildren' => $has_kids,
									'hasnochildren' => $has_no_kids,
									'subscribed'=>$subscribed
					);
				}
			} else {
				$lastProp=false;
				foreach ($vals as $v) {
					if ($v == '(') {
						$flag = true;
					}
					elseif ($v == ')') {
						break;
					}
					else {
						if($lastProp=='MESSAGES'){
							$folders[$decodedName]['messages']=intval($v);
						}elseif($lastProp=='UNSEEN'){
							$folders[$decodedName]['unseen']=intval($v);
						}
					}

					$lastProp=$v;
				}
			}
		}

		//sometimes shared folders like "Other user.shared" are in the folder list
		//but there's no "Other user" parent folder. We create a dummy folder here.
		if(!isset($folders['INBOX']) && $isRoot){
			$folders["INBOX"]=array(
						'delimiter' => $delim,
						'name' => 'INBOX',
						'marked' => true,
						'nonexistent'=>false,
						'noselect' => false,
						'haschildren'=>false,
						'hasnochildren'=>true,
						'noinferiors' => false,
						'subscribed'=>true);
		}

		if($withStatus){
			//no support for list status. Get the status for each folder
			//with seperate status calls
			foreach($folders as $name=>$folder){
				if(!isset($folders[$name]['unseen'])){
					if($folders[$name]['nonexistent'] || $folders[$name]['noselect']){
						$folders[$name]['messages']=0;
						$folders[$name]['unseen']=0;
					}else
					{
						$status = $this->get_status($folder["name"]);
						if(!$status) {
							go()->warn("Could not get status for folder '" . $folder['name'] . "'");
						} else{
							$folders[$name]['messages']=$status['messages'];
							$folders[$name]['unseen']=$status['unseen'];
						}

					}
				}
			}
		}

		\GO\Base\Util\ArrayUtil::caseInsensitiveSort($folders);

		return $folders;
	}

	/**
	 * Get the namespaces that are available on the mailserver.
	 *
	 * @return array
	 */
	public function get_namespaces() :array
	{
		// Array with the namespaces that are found.
		$nss = array();

		if($this->has_capability('NAMESPACE')){
			//IMAP ccommand

			$command = "NAMESPACE\r\n";
			$this->send_command($command);
			$result = $this->get_response(false, true);

			$namespaceCmdFound=false;

			$insideNamespace=false;

			$namespace = array('name'=>null, 'delimiter'=>null);

			foreach ($result as $vals) {
				foreach ($vals as $val) {
					if (!$namespaceCmdFound && strtoupper($val) == 'NAMESPACE') {
						$namespaceCmdFound = true;
					} else {
						switch (strtoupper($val)) {

							case '(':
								$insideNamespace = true;
								break;

							case ')':
								$insideNamespace = false;

								if(isset($namespace['name'])){
									$namespace['name'] = $this->_unescape($this->utf7_decode(trim($namespace['name'], $namespace['delimiter'])));
									$nss[] = $namespace;
									$namespace = array('name' => null, 'delimiter' => null);
								}
								break;

							default:
								if ($insideNamespace) {
									if (!isset($namespace['name'])) {
										$namespace['name'] = $val;
									} else {
										$namespace['delimiter'] = $val;
									}
								}
								break;
						}
					}
				}
			}

			return $nss;
		} else {
			return array(array('name'=>'','delimiter'=>$this->get_mailbox_delimiter()));
		}
	}


	/**
	 * Gets the mailboxes
	 *
	 * @param string $namespace
	 * @param bool $subscribed
	 * @param string $pattern
	 * @return array
 	 * @throws Exception
	 */
	public function get_folders(string $namespace='', bool $subscribed=false, string $pattern='*') :array
	{

		$this->get_capability();

		if ($subscribed) {
			$imap_command = 'LSUB';
		} else {
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
			$folder = $this->_unescape($this->utf7_decode($vals[($count - 1)]));
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
				if(!isset($folders[$parent])) {
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

		//\GO::debug($folders);

		ksort($folders);

		return $folders;
	}


	/**
	 * Before getting message a mailbox must be selected
	 *
	 * @param string $mailbox_name
	 * @return bool|array
	 * @throws Exception
	 */
	public function select_mailbox(string $mailbox_name='INBOX')
	{
		if(strlen($mailbox_name) === 0) {
			return true;
		}
		if($this->selected_mailbox && $this->selected_mailbox['name']==$mailbox_name) {
			return true;
		}

		$box = $this->addslashes($this->utf7_encode($mailbox_name));
		$this->clean($box, 'mailbox');

		\GO::debug("Selecting IMAP mailbox $box");

		$command = "SELECT \"$box\"\r\n";

		$this->send_command($command);
		$res = $this->get_response(false, true);
		$status = $this->check_response($res, true);

		if(!$status) {
			return false;
		}
		$highestmodseq=false;
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

			if (in_array('HIGHESTMODSEQ', $vals)) {
				foreach ($vals as $i => $v) {
					if (intval($v) && isset($vals[($i - 1)]) && $vals[($i - 1)] == 'HIGHESTMODSEQ') {
						$highestmodseq = $v;
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

				if (implode(' ', array_slice($vals, -2)) == 'Flags permitted.') {
					$this->permittedFlags = true;
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

		$mailbox=array();
		$mailbox['name']=$mailbox_name;
		$mailbox['uidnext'] = $uidnext;
		$mailbox['uidvalidity'] = $uidvalidity;
		$mailbox['highestmodseq'] = $highestmodseq;
		$mailbox['messages'] = $exists;
		$mailbox['flags'] = $flags;
		$mailbox['permanentflags'] = $pflags;

		$this->selected_mailbox=$mailbox;

		return $mailbox;
	}

	/**
	 * Get's the number and UID's of unseen messages of a mailbox
	 *
	 * @param <type> $folder
	 * @return <type>
	 */

	private $_unseen = [];

	/**
	 * @param false|string $mailbox
	 * @param false $nocache
	 * @return array|false|mixed
	 * @throws Exception
	 */
	public function get_unseen($mailbox=false, $nocache=false)
	{

		if(!$mailbox) {
			$mailbox = $this->selected_mailbox['name'];
		}
		if(isset($this->_unseen[$mailbox])){
			return $this->_unseen[$mailbox];
		}

		if($mailbox){
			if(!$this->select_mailbox($mailbox)){
				return false;
			}
		}
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
		$this->_unseen[$mailbox]=array('count'=>$unseen, 'uids'=>$uids);

		return $this->_unseen[$mailbox];
	}


	/**
	 * Returns a sorted list of mailbox UID's
	 *
	 * @param string $sort
	 * @param bool $reverse
	 * @param filter $filter
	 * @return array|false|int[]|string[]|void
	 * @throws Exception
	 */
	public function sort_mailbox($sort='ARRIVAL', $reverse=false, $filter='ALL')
	{
		if(empty($filter)){
			$filter = 'ALL';
		}

		if(!$this->selected_mailbox) {
			throw new Exception('No mailbox selected');
		}
		$this->get_capability();

		if (($sort == 'THREAD_R' || $sort == 'THREAD_O')) {
			if ($sort == 'THREAD_O') {
				if (stristr($this->capability, 'ORDEREDSUBJECT')) {
					$ret =  $this->thread_sort($sort, $filter);
					$this->sort_count = $ret['total'];
					// Store all search result UIDs
					// Unsure how to get UIDs here
					return $ret;
				}
				else {
					$uids=$this->server_side_sort('ARRIVAL', false, $filter);
					$this->sort_count = count($uids);
					// Store all search result UIDs
					$this->allUids = $uids;
					return $uids;
				}
			}
			if ($sort == 'THREAD_R') {
				if (stristr($this->capability, 'THREAD')) {
					$ret = $this->thread_sort($sort, $filter);
					$this->sort_count = $ret['total'];
					// Store all search result UIDs
					// Unsure how to get UIDs here
					return $ret;
				} else {
					$uids=$this->server_side_sort('ARRIVAL', false, $filter);
					$this->sort_count = count($uids);
					// Store all search result UIDs
					$this->allUids = $uids;
					return $uids;
				}
			}
		} elseif (stristr($this->capability, 'SORT')) {
			$uids=$this->server_side_sort($sort, $reverse, $filter);
			if($uids === false) {
				throw new \Exception("Sort error: " . $this->last_error());
            }
			$this->sort_count = count($uids); // <-- BAD
			// Unsure why the above has the BAD comment (?)
			// Store all search result UIDs
			$this->allUids = $uids;
			return $uids;
		}
		else {
			$uids=$this->client_side_sort($sort, $reverse, $filter);
            if($uids === false) {
                throw new \Exception("Sort error: " . $this->last_error());
            }
			$this->sort_count = count($uids);
			// Store all search result UIDs
			$this->allUids = $uids;
			return $uids;
		}
	}

	private function server_side_sort($sort, $reverse, $filter, $forceAscii=false) {
//		\GO::debug("server_side_sort($sort, $reverse, $filter)");

		$this->clean($sort, 'keyword');

		$charset = $forceAscii || !\GO\Base\Util\StringHelper::isUtf8($filter) ? 'US-ASCII' : 'UTF-8';

		$command = 'UID SORT ('.$sort.') '.$charset.' '.trim($filter)."\r\n";
		
		$this->send_command($command);
		$speedup = true;

		$res = $this->get_response(false, true, 8192, $speedup);
		$status = $this->check_response($res, true);
		if(!$status && stripos($this->last_error(), 'utf')){
			return $this->server_side_sort($sort, $reverse, $filter, true);
		}
		$uids = array();
		foreach ($res as $vals) {
			if ($vals[0] == '*' && strtoupper($vals[1]) == 'SORT') {
				array_shift($vals);
				array_shift($vals);
				$uids = array_merge($uids, $vals);
			} else {
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
	 * Search
	 *
	 * @param <type> $terms
	 * @return array uiids
	 */
	public function search($terms) :array
	{
		$this->clean($terms, 'search_str');

		/*
		 * Sending charset along doesn't work on iMailserver.
		 * Without seems to work on different servers.
		 *
		 * We had some other where it didn't work on a large Polish provider. We'll send it again.
		 * It doesn't work on o365 imap too.
		 */
		$charset = !\GO\Base\Util\StringHelper::isUtf8($terms) ? '' : 'CHARSET UTF-8 ';

		$command = 'UID SEARCH '.$charset.trim($terms)."\r\n";
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

		// Check if the imap_sort_on_date flag is set. Usually this can be set when 
		// the mailserver is a Microsoft Exchange server that 
		// does NOT support Server Side Sort
		
		if (!\GO::config()->imap_sort_on_date) {
			\GO::debug("imap::Config::imap_sort_on_date(false)");
			if ($sort == 'DATE' || $sort == 'R_DATE') {
				$sort = 'ARRIVAL';
			}
		} else {
			\GO::debug("imap::Config::imap_sort_on_date(true)");
		}

		\GO::debug("imap::client_side_sort($sort, $reverse, $filter)");

		$uid_string='1:*';
		if(!empty($filter) && $filter !='ALL'){
			$uids = $this->search($filter);
			if(!count($uids)){
				return array();
			}else
			{
				$res = [];
				$chunks = array_chunk($uids, 1000);
				foreach($chunks as $chunk) {
					$uid_string=implode(',', $chunk);
					$res = array_merge($res, $this->client_side_sort_chunk($uid_string, $sort, $reverse, $filter));
					if(!$res) {
						return false;
					}
				}
			}
		} else {
			$res = $this->client_side_sort_chunk($uid_string, $sort, $reverse, $filter);
			if(!$res) {
				return false;
			}
		}

		switch ($sort) {
//		Doesn't work on some servers. Use internal date for these.
//		Enabled because we have added GO::config()->imap_sort_on_date functionality
			case 'DATE':
			case 'R_FROM':
			case 'FROM':
			case 'R_SUBJECT':
			case 'SUBJECT':
			case 'R_DATE':
				$key = "BODY[HEADER.FIELDS";
				break;
			case 'SIZE':
			case 'R_SIZE':
				$key = "RFC822.SIZE";
				break;
			default:
				$key = "INTERNALDATE";
				break;
		}

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
						if ($sort == "DATE" || $sort == "R_DATE") {
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

		unset($res);
		natcasesort($sort_keys);
		$uids = array_keys($sort_keys);
		if ($reverse) {
			$uids = array_reverse($uids);
		}
		return $uids;


	}


	private function client_side_sort_chunk($uid_string, $sort, $reverse, $filter) {
		$this->clean($sort, 'keyword');
		$command1 = 'UID FETCH '.$uid_string.' ';
		switch ($sort) {
//		Doesn't work on some servers. Use internal date for these.
//		Enabled because we have added GO::config()->imap_sort_on_date functionality
			case 'DATE':
			case 'R_DATE':
				$command2 = "BODY.PEEK[HEADER.FIELDS (DATE)]";
				break;
			case 'SIZE':
			case 'R_SIZE':
				$command2 = "RFC822.SIZE";
				break;
			case 'FROM':
			case 'R_FROM':
				$command2 = "BODY.PEEK[HEADER.FIELDS (FROM)]";
				break;
			case 'SUBJECT':
			case 'R_SUBJECT':
				$command2 = "BODY.PEEK[HEADER.FIELDS (SUBJECT)]";
				break;
			default:
				$command2 = "INTERNALDATE";
				break;
		}
		$command = $command1.'('.$command2.")\r\n";

		$this->send_command($command);
		$res = $this->get_response(false, true);
		$status = $this->check_response($res, true);
		if(!$status) {
			return false;
		}

		return $res;

	}

	/**
	 * use the THREAD extension to get the sorted UID list and thread data
	 * @param $sort
	 * @param $filter
	 * @return array
	 * @throws Exception
	 */
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
			switch ($uid_string[0]) {
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
	 * @param string $uid
	 * @return mixed
	 */

	public function get_message_header($uid, $full_data=false, array $extraHeaders = [])
	{
		$headers = $this->get_message_headers(array($uid), $full_data, $extraHeaders);
		if(isset($headers[$uid])){
			return $headers[$uid];
		}
		return false;
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
	 * @param array $uids
	 * @return array
	 */
	public function get_message_headers(array $uids, $full_data=false, array $extraHeaders = [])  :array
	{

		if(empty($uids)) {
			return array();
		}

		$sorted_string = implode(',', $uids);
		$this->clean($sorted_string, 'uid_list');

		$flags_string = 'FLAGS';
		if ($this->server == 'imap.gmail.com') {
			$this->gmail_server = true;
			$flags_string = 'X-GM-LABELS FLAGS';
		}

		$command = 'UID FETCH '.$sorted_string.' (' . $flags_string . ' INTERNALDATE RFC822.SIZE BODY.PEEK[HEADER.FIELDS (SUBJECT FROM '.
						"DATE CONTENT-TYPE X-PRIORITY TO CC";

		if($full_data) {
			$command .= " BCC REPLY-TO DISPOSITION-NOTIFICATION-TO CONTENT-TRANSFER-ENCODING MESSAGE-ID REFERENCES IN-REPLY-TO";
		}

		if(!empty($extraHeaders)) {
			$extraHeaders = array_map("strtoupper", $extraHeaders);
			$command .= " " . implode(" ", $extraHeaders);
		}

		$command .= ")])\r\n";

		$this->send_command($command);
		$res = $this->get_response(false, true);

		$tags = array('UID' => 'uid', 'FLAGS' => 'flags', 'X-GM-LABELS' => 'flags', 'RFC822.SIZE' => 'size', 'INTERNALDATE' => 'internal_date');
		$junk = array_merge(array('SUBJECT', 'FROM', 'CONTENT-TYPE', 'TO', 'CC','BCC', '(', ')', ']', 'X-PRIORITY', 'DATE','REPLY-TO','DISPOSITION-NOTIFICATION-TO','CONTENT-TRANSFER-ENCODING', 'MESSAGE-ID', 'REFERENCES', 'IN-REPLY-TO'), $extraHeaders);

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
					'x_priority'=>3,
					'reply_to'=>'',
					'message_id'=>'',
					'references'=>'',
					'in_reply_to'=>'',
					'content_type'=>'',
					'content_type_attributes'=>array(),
					'disposition_notification_to'=>'',
					'content_transfer_encoding'=>'',
					'charset'=>'',
					'seen'=>0,
					'flagged'=>0,
					'answered'=>0,
					'forwarded'=>0,
					'has_attachments'=>0,
					'labels'=>array(),
					'deleted'=>0,
				);

				foreach($extraHeaders as $extraHeader) {
					$message[ str_replace('-','_',strtolower($extraHeader))] = "";
				}

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
								$header = str_replace('-','_',$header);

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
									$prop = str_replace('-','_',strtolower(substr($vals[$i + $n],1)));
									//\GO::debug($prop);

									//It can be a user named a label $labels
									if(isset($message[$prop]) && $prop != 'labels') {
										$message[$prop]=true;
									} else {
										$message['labels'][] = strtolower($vals[$i + $n]);
									}

									$n++;
								}
								$i += $n;
							} else {
								$prop = $tags[strtoupper($vals[$i])];

								if(isset($message[$prop]))
										$message[$prop] = trim($vals[($i + 1)]);
								$i++;
							}
						}
					}
				}
				if ($message['uid']) {
					if(isset($message['content_type'])) {
						$message['content_type']=strtolower($message['content_type']);
						if (strpos($message['content_type'], 'charset=')!==false) {
							if (preg_match("/charset\=([^\s]+)/", $message['content_type'], $matches)) {
								$message['charset'] = trim(str_replace(array('"', "'", ';'), '', $matches[1]));
							}
						}
						if(preg_match("/([^\/]*\/[^;]*)(.*)/", $message['content_type'], $matches)){
							$message['content_type']=$matches[1];
							$atts = trim($matches[2], ' ;');
							$atts=explode(';', $atts);

							for($i=0;$i<count($atts);$i++){
								$keyvalue=explode('=', $atts[$i]);
								if(isset($keyvalue[1]) && $keyvalue[0]!='boundary')
									$message['content_type_attributes'][trim($keyvalue[0])]=trim($keyvalue[1],' "');
							}
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
					$message['reply_to']=$this->mime_header_decode($message['reply_to']);
					$message['disposition_notification_to']=$this->mime_header_decode($message['disposition_notification_to']);
					
					//remove non ascii stuff. Incredimail likes iso encoded chars too :(
					if(isset($message['message_id'])) {
						$message['message_id']= preg_replace('/[[:^print:]]/', '', $message['message_id']);
					}

					if(isset($message['cc']))
						$message['cc']=$this->mime_header_decode($message['cc']);

					if(isset($message['bcc']))
						$message['bcc']=$this->mime_header_decode($message['bcc']);

					preg_match("'([^/]*)/([^ ;\n\t]*)'i", $message['content_type'], $ct);

					if (isset($ct[2]) && $ct[1] != 'text' && $ct[2] != 'alternative' && $ct[2] != 'related') {
						$message["has_attachments"] = 1;
					}

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

		return $final_headers;
	}

	/**
	 * @param string $uidRange
	 * @return array|false
	 * @throws Exception
	 */
	public function get_flags($uidRange = '1:*')
	{
		if (is_array($uidRange)) {
			$chunks = array_chunk($uidRange, 1000);

			$flags = array();
			while ($subset = array_shift($chunks)) {

				$uidStr = implode(',', $subset);
				$this->clean($uidStr, 'uid_list');

				$tmp = $this->get_flags($uidStr);
				if (is_array($tmp)) {
					$flags = array_merge($flags, $tmp);
				}
			}

			return $flags;
		}

		$command = 'UID FETCH '.$uidRange.' (FLAGS INTERNALDATE)'."\r\n";
		
		$this->send_command($command);
		$res = $this->get_response(false, false);

		$status = $this->check_response($res, false);
		if(!$status) {
			return false;
		}

		//remove status response
		array_pop($res);
		
		$data = [];
		
		foreach($res as $message) {
			//UID 17 FLAGS ( \Flagged \Seen ) INTERNALDATE 24-May-2018 13:02:43 +0000

			//or different order!
			// l * 2 FETCH ( UID 2 INTERNALDATE 30-Jan-2020 11:20:06 +0000 FLAGS ( \Seen ) )

			if(preg_match('/UID ([0-9]+)/', $message, $uidMatches)) {
				$uid = (int) $uidMatches[1];
			} else{
				return false;
			}

			if(preg_match('/FLAGS \((.*)\)/U', $message, $flagMatches)) {
				$flags = array_map('trim', explode(' ', trim($flagMatches[1])));
			}else{
				return false;
			}

			if(preg_match('/INTERNALDATE ([^\s\)]+ [^\s\)]+ [^\s\)]+)/', $message, $dateMatches)) {
				$date = $dateMatches[1];
			}else{
				return false;
			}

			$data[] = [
				'uid' => $uid,
				'flags' => $flags,
				'date' => $date
			];

		}
		
		return $data;
	}


	/**
	 * @param $start
	 * @param $limit
	 * @param $sort_field
	 * @param false $reverse
	 * @param string $query
	 * @return array
	 * @throws Exception
	 */
	public function get_message_headers_set($start, $limit, $sort_field , $reverse=false, $query='ALL')
	{
		\GO::debug("get_message_headers_set($start, $limit, $sort_field , $reverse, $query)");

		if($query=='ALL' || $query==""){
			$unseen = $this->get_unseen($this->selected_mailbox['name']);

			$key = 'sort_cache_'.$this->selected_mailbox['name'].'_'.$this->server.'_'.$sort_field;
			$key .= $reverse ? '_1' : '_0';
			
			$unseenCheck = $unseen['count'].':'.$this->selected_mailbox['messages'];
			if(!empty($this->selected_mailbox['uidnext']))
				$unseenCheck .= ':'.$this->selected_mailbox['uidnext'];

			\GO::debug($unseenCheck);
			//var_dump($unseenCheck);
			if(isset(\GO::session()->values['emailmod'][$key]['unseen']) && \GO::session()->values['emailmod'][$key]['unseen']==$unseenCheck){
					//throw new \Exception("From cache");
				\GO::debug("IMAP sort from session cache");
				$uids = \GO::session()->values['emailmod'][$key]['uids'];
				$this->sort_count=count($uids);
			}else
			{
				\GO::debug("IMAP sort from server");
				\GO::session()->values['emailmod'][$key]['unseen']=$unseenCheck;
				$uids = \GO::session()->values['emailmod'][$key]['uids'] = $this->sort_mailbox($sort_field, $reverse, $query);
			}
		}else
		{
			$uids = $this->sort_mailbox($sort_field, $reverse, $query);
		}

		\GO::debug("Count uids: ".count($uids));

		if(!is_array($uids))
			return array();

		if($limit>0)
			$uids=array_slice($uids,$start, $limit);

		$chunks = array_chunk($uids, 1000);

		$headers = array();
		while($subset = array_shift($chunks)){
			$headers = array_merge($headers, $this->get_message_headers($subset, true));
		}

		return $headers;
	}


	/**
	 * Check if the given mailbox root is valid and return it with the correct delimiter
	 *
	 * @param string $mbroot The Mailbox root. (eg. INBOX/)
	 * @return string Mailbox root with delimiter or empty string on failure
	 * @throws Exception
	 */
	function check_mbroot(string $mbroot)
	{
		$mbroot = trim($mbroot);
		if(empty($mbroot)) {
			return '';
		}

		$list = $this->get_folders('', false,'%');
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
		return '';
	}


	/**
	 * Get's an array with two keys. usage and limit in bytes.
	 *
	 * @return array|false
	 */
	public function get_quota()
	{
		if(!$this->has_capability("QUOTA")) {
			return false;
		}

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
	 * @param $uid
	 * @return array
	 * @throws Exception
	 */
	public function get_message_structure($uid) :array
	{
		$this->clean($uid, 'uid');
		$struct = array();
		$command = "UID FETCH $uid BODYSTRUCTURE\r\n";
		$this->send_command($command);
		$result = $this->get_response(false, true);

		while (isset($result[0][0]) && isset($result[0][1]) && $result[0][0] == '*' && strtoupper($result[0][1]) == 'OK') {
			array_shift($result);
		}
		$status = $this->check_response($result, true);
		array_pop($result);

		$r = [];
		while($line = array_shift($result)) {
			$r = array_merge($r, $line);
		}

		if (!isset($r[4])) {
			$status = false;
		}
		if ($status) {
			if (strtoupper($r[4]) == 'UID') {
				$response = array_slice($r, 7, -1);
			}
			else {
				$response = array_slice($r, 5, -1);
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
	 * Finds the first message part in a structure returned from
	 * get_message_structure that matches the parameters given.
	 *
	 * Useful to find the first text/plain or text/html for example to find the
	 * message body.
	 *
	 * @param $struct
	 * @param $number
	 * @param string $type
	 * @param false $subtype
	 * @param array $parts
	 * @return array|mixed
	 */
	public function find_message_parts($struct, $number, string $type='text', bool $subtype=false, array $parts=array()) :array
	{
		if (!is_array($struct) || empty($struct)) {
			return $parts;
		}
		foreach ($struct as $id => $vals) {
			if ($number && $id == $number) {
				$vals['number'] = $id;
				$parts[] = $vals;
			}
			elseif (!$number && isset($vals['type']) && $vals['type'] == $type) {
				if ($subtype) {
					if ($subtype == $vals['subtype']) {
						$vals['number'] = $id;
						$parts[] = $vals;
					}
				}
				else {
					$vals['number'] = $id;
					$parts[] = $vals;
				}
			}
			if (empty($res) && isset($vals['subs'])) {
				$this->find_message_parts($vals['subs'], $number, $type, $subtype, $parts);
			}
		}
		return $parts;
	}


	/**
	 * @param $struct
	 * @return bool
	 */
	public function has_alternative_body($struct) :bool
	{

		if (!is_array($struct) || empty($struct)) {
			return false;
		}

		if(isset($struct['type']) && $struct['type']=='message' && strtolower($struct['subtype'])=='alternative'){
			return true;
		}

		foreach ($struct as $id => $vals) {
			if(isset($vals['type']) && $vals['type']=='message' && isset($struct['subtype']) && strtolower($struct['subtype'])=='alternative'){
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
	 * Finds the first message part in a structure returned from
	 * get_message_structure that matches the parameters given.
	 *
	 * Useful to find the first text/plain or text/html for example to find the
	 * message body.
	 *
	 * @param $struct
	 * @param string $type
	 * @param string $subtype
	 * @param array $parts
	 * @return array
	 */
	public function find_body_parts($struct, string $type='text', string $subtype='html', array &$parts=array('text_found'=>false, 'parts'=>array())) :array
	{

		if (!is_array($struct) || empty($struct)) {
			return $parts;
		}

		$imgs  =array('jpg','jpeg','gif','png','bmp');
		foreach ($struct as $id => $vals) {

			//\GO::debug($vals);
			if(is_array($vals)){
				if (isset($vals['type'])){

					$vals['number'] = $id;
					//\GO::debug($vals);

					if ($vals['type'] == $type && $subtype == $vals['subtype'] && strtolower($vals['disposition'])!='attachment' && empty($vals['name'])) {

						$parts['text_found']=true;
						$parts['parts'][] = $vals;

					}elseif($vals['type']=='image' && in_array($vals['subtype'], $imgs) && $vals['disposition']=='inline' && empty($vals['id']))
					{
						//\GO::debug($vals);
						//work around ugly stuff. Some mails contain stuff with type image/gif but it's actually an html file.
						//so we double check if the image has a filename that it has a valid image extension
						$file = empty($vals['name']) ? false : new \GO\Base\Fs\File($vals['name']);
						if(!$file || $file->isImage()){

							//an inline image without ID. We'll display in the part order. Apple
							//mail sends mail like this.
							$parts['parts'][]=$vals;
						}
					}
				}

				//don't decent into message/RFC822 files. Sometimes they come nested in the body from the IMAP server.
				if (isset($vals['subs']) && (!isset($vals['subtype']) || $vals['subtype']!='rfc822')) {

//					$text_found_at_this_level = $parts['text_found'];
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
				}
			}
		}
		return $parts;
	}

	/**
	 * Find all attachment parts from a structure returned by get_message_structure
	 *
	 * @param $struct
	 * @param array $skip_ids
	 * @param array $attachments
	 * @return array
	 */
	public function find_message_attachments($struct, array $skip_ids=array(), array $attachments=array()) :array
	{
		if (!is_array($struct) || empty($struct)) {
			return $attachments;
		}

		foreach ($struct as $id => $vals) {
			if(!is_array($vals)) {
				continue;
			}

			// Strict must be true as 2.1 == 2.10 if false
			if(isset($vals['type']) && !in_array($id, $skip_ids, true)){
				$vals['number'] = $id;

				//sometimes NIL is returned from Dovecot?!?
				if($vals['id']=='NIL') {
					$vals['id'] = '';
				}

				$attachments[]=$vals;
			} elseif(isset($vals['subs'])) {
				$attachments = $this->find_message_attachments($vals['subs'],$skip_ids,	$attachments);
			}
		}
		return $attachments;
	}

	/**
	 * Decodes a message part.
	 *
	 * @param string $str
	 * @param string $encoding Can be base64 or quoted-printable
	 * @param string|null $charset If this is given then the part will be converted to UTF-8 and illegal characters will be stripped.
	 * @return string
	 */

	public function decode_message_part(string $str, string $encoding, ?string $charset=null)
	{
		switch(strtolower($encoding)) {
			case 'base64':
				$str = base64_decode($str);
				break;
			case 'quoted-printable':
				$str =  quoted_printable_decode($str);
				break;
		}

		if($charset)  {
			//some clients don't send the charset.
			if($charset=='us-ascii') {
				$charset = 'windows-1252';
			}
			$str = \GO\Base\Util\StringHelper::clean_utf8($str, $charset);
			if($charset != 'utf-8') {
				$str = str_replace($charset, 'utf-8', $str);
			}
		}
		return $str;
	}
	
	/**
	 * Decode an uuencoded attachment
	 * 
	 * @param int $uid
	 * @param int $part_no
	 * @param boolean $peek
	 * @param type $fp
	 * @return type
	 * @throws Exception
	 */
	private function _uudecode(int $uid, int $part_no, bool $peek, $fp)
	{
		$regex = "/(begin ([0-7]{1,3}) (.+))\n/";
		
		$body = $this->get_message_part($uid, $part_no, $peek);

		if (preg_match($regex, $body, $matches, PREG_OFFSET_CAPTURE)) {

			$offset = $matches[3][1] + strlen($matches[3][0]) + 1;

			$endpos = strpos($body, 'end', $offset) - $offset - 1;


			if(!$endpos){					
				throw new Exception("Invalid UUEncoded attachment in uid: ".$uid);
			}

			$att = str_replace(array("\r"), "", substr($body, $offset, $endpos));

			$data = convert_uudecode($att);

			if(!$fp){
				return $data;
			} else {
				fputs($fp, $data);
			}
		}
	}

	/**
	 * Get's a message part and returned in binary form or UTF-8 charset.
	 *
	 * @param int $uid
	 * @param string $part_no
	 * @param stirng $encoding
	 * @param string $charset
	 * @param boolean $peek
	 * @return string
	 */

	public function get_message_part_decoded($uid, $part_no, $encoding, $charset=false, $peek=false, $cutofflength=false, $fp=false) {
		\GO::debug("get_message_part_decoded($uid, $part_no, $encoding, $charset)");
		
		
		if($encoding == 'uuencode') {
			return $this->_uudecode($uid, $part_no, $peek, $fp);
		}


//		if(strtolower($encoding) == 'base64') {
//			$part = $this->get_message_part($uid, $part_no, $encoding, $peek);
//			//return base64_decode($part);
//		}

		$str = '';
		$this->get_message_part_start($uid, $part_no, $peek);


		$leftOver='';

		while ($line = $this->get_message_part_line()) {

			switch (strtolower($encoding)) {
				case 'base64':
					$line = trim($leftOver).trim($line);

					$length = strlen($line);
					$mod = strlen($line) % 4;

					if($mod == 0) {
						$leftOver = "";
					} else{
						$cutPoint = $length - $mod;
						$leftOver = substr($line, $cutPoint);
						$line = substr($line, 0, $cutPoint);
					}

					if(!$fp){
						$str .= base64_decode($line);
					}  else {
						fputs($fp, base64_decode($line));
					}

					break;

				case 'quoted-printable':
					if(!$fp){
						$str .= quoted_printable_decode($line);
					}else{
						fputs($fp, quoted_printable_decode($line));
					}
					break;				
				default:
					if(!$fp){
						$str .= $line;
					}else{
						fputs($fp, $line);
					}
					break;
			}

			if($cutofflength && strlen($line)>$cutofflength){
				break;
			}
		}

		if(!empty($leftOver))
		{
			\GO::debug($leftOver);

			if(!$fp){
				$str .= base64_decode($leftOver);
			}  else {
				fputs($fp, base64_decode($leftOver));
			}
		}


		if($charset){

			//some clients don't send the charset.
			if($charset=='us-ascii') {
				$charset = $this->findCharsetInHtmlBody($str);				
			}

			$str = \GO\Base\Util\StringHelper::clean_utf8($str, $charset);
			if($charset != 'utf-8') {
				$str = str_replace($charset, 'utf-8', $str);
			}
		}
		

		return $fp ? true : $str;
	}


	/**
	 * @param string $body
	 * @return mixed|string
	 */
	private function findCharsetInHtmlBody(string $body)
	{
		if(preg_match('/<meta.*charset=([^"\'\b]+)/i', $body, $matches)) {
			return $matches[1];
		}

		return 'windows-1252';
	}


	/**
	 * Get the full body of a message part. Obtain the partnumbers with get_message_structure.
	 *
	 * @param $uid
	 * @param $message_part omit if you want the full message
	 * @param bool|null $peek
	 * @return string
	 */
	public function get_message_part($uid, $message_part=0, ?bool $peek=false) :string
	{
		$str = '';
		$this->get_message_part_start($uid,$message_part, $peek);
		while ($line = $this->get_message_part_line()) {
			$str .= $line;
		}
		return $str;
	}

	/**
	 * Start getting a message part for reading it line by line
	 *
	 * log][GO\Base\Mail\Imap:2533]
	R: * 53 FETCH ( UID 173553 BODY[2 ] JVBERi0xLjcNCiW1tbW1DQoxIDAgb2JqDQo8PC9UeXBlL0NhdGFsb2cvUGFn
	ZXMgMiAwIFIvTGFuZyhpdC1JVCkgL1N0cnVjdFRyZWVSb290IDEzNSAwIFIv
	TWFya0luZm88PC9NYXJrZWQgdHJ1ZT4+L01ldGFkYXRhIDkzMiAwIFIvVmll
	d2VyUHJlZmVyZW5jZXMgOTMzIDAgUj4+DQplbmRvYmoNCjIgMCB
	 *
	 *
	 *  NTgvWFJlZlN0bSAyNDMyNDgwPj4NCnN0YXJ0eHJlZg0KMjQ1MzYyMA0KJSVF
	T0Y=
	)
	[log][GO\Base\Mail\Imap:2533] R: A3 OK UID FETCH completed

	 *
	 * @param <type> $uid
	 * @param $message_part
 	 * @param bool|null $peek
	 * @return <type>
	 */
	public function get_message_part_start($uid, $message_part=0, ?bool $peek=false)
	{
		$this->readFullLiteral = false;
		$this->clean($uid, 'uid');

		$peek_str = $peek ? '.PEEK' : '';

		if (empty($message_part)) {
			$command = "UID FETCH $uid BODY".$peek_str."[]\r\n";
		} else {
			$command = "UID FETCH $uid BODY".$peek_str."[$message_part]\r\n";
		}
		$this->send_command($command);
		$result = fgets($this->handle);
		
		$size = false;
		if (preg_match("/\{(\d+)\}\r\n/", $result, $matches)) {
			$size = $matches[1];
		}

		$this->message_part_size=$size;
		$this->message_part_read=0;

		return $size;
	}

	private $message_part_size;
	private $message_part_read;

	private $readFullLiteral = false;

	/**
	 * Read message part line. get_message_part_start must be called first
	 *
	 * @return <type>
	 */
	public function get_message_part_line()
	{
		$line=false;
		$leftOver = $this->message_part_size-$this->message_part_read;
		if($leftOver>0){

			//reading exact length doesn't work if the last char is just one char somehow.
			//we cut the left over later with substr.
			$blockSize = 1024;//$leftOver>1024 ? 1024 : $leftOver;
			$line = fgets($this->handle,$blockSize);
			$this->message_part_read+=strlen($line);
		}

		if ($line && $this->message_part_size < $this->message_part_read) {
			$line = substr($line, 0, ($this->message_part_read-$this->message_part_size)*-1);
		}

		if($line===false) {
			if($this->readFullLiteral) {
				//don't attempt to read response after already have done that because it will hang for a long time
				return false;
			}

			//read and check left over response.
			$response = $this->get_response(false, true);
			if(!$this->check_response($response, true)) {
				return false;
			}
			//for some imap servers that don't return the attachment size. It will read the entire attachment into memory :(
			if(isset($response[0][6]) && substr($response[0][6], 0, 4) == 'BODY' && !empty($response[0][8])) {
				$line = $response[0][8];
				$this->readFullLiteral = true;
			}
		}
		return $line;
	}

	/**
	 * @param $uid
	 * @param $path
	 * @param int $imap_part_id
	 * @param string $encoding
	 * @param false $peek
	 * @return bool
	 */
	public function save_to_file($uid, $path, $imap_part_id=-1, $encoding='', $peek=true) :bool
	{

		$fp = fopen($path, 'w+');

		if(!$fp) {
			return false;
		}
		/*
		 * Somehow fetching a message with an empty message part which should fetch it
		 * all doesn't work. (http://tools.ietf.org/html/rfc3501#section-6.4.5)
		 *
		 * That's why I first fetch the header and then the text.
		 */
		if($imap_part_id==-1){
			$header = $this->get_message_part($uid, 'HEADER', $peek)."\r\n\r\n";

			if(empty($header)) {
				return false;
			}

			if(!fputs($fp, $header)) {
				return false;
			}

			$imap_part_id='TEXT';
		}


		$this->get_message_part_decoded($uid, $imap_part_id, $encoding, false, $peek, false, $fp);

		fclose($fp);

		return true;
	}

	/**
	 * Runs $command multiple times, with $uids split up in chunks of 500 UIDs
	 * for each run of $command.
	 * @param string $command IMAP command
	 * @param array $uids Array of UIDs
	 * @param ?bool $trackErrors passed as third argument to $this->check_response()
	 * @return bool
	 * @throws Exception
	 */
	private function _runInChunks($command, $uids, $trackErrors = true): bool
	{
		$status = false;
		$uid_strings = array();
		if (empty($uids))
			return true;

		if (count($uids) > 500) {
			while (count($uids) > 500) {
				$uid_strings[] = implode(',', array_splice($uids, 0, 2));
			}
			if (count($uids)) {
				$uid_strings[] = implode(',', $uids);
			}
		} else {
			$uid_strings[] = implode(',', $uids);
		}

		foreach ($uid_strings as $uid_string) {
			if ($uid_string) {
				$this->clean($uid_string, 'uid_list');
			}
			$theCommand = sprintf($command, $uid_string);
			$this->send_command($theCommand);
			$res = $this->get_response();
			$status = $this->check_response($res, false, $trackErrors);
			if (!$status) {
				return $status;
			}
		}

		return $status;
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
	 * @param array $uids
	 * @param string $flags
	 * @param boolean $clear
	 * @return bool
	 */
	public function set_message_flag(array $uids, string $flags, $clear=false): bool
	{
		//TODO parhaps we can manage X-GM-LABEL too (but only what we can read is type like \\Starred)
		if($clear) {
			$command = "UID STORE %s -FLAGS.SILENT ($flags)\r\n";
		}else {
			$command = "UID STORE %s +FLAGS.SILENT ($flags)\r\n";
		}
		$status = $this->_runInChunks($command,$uids,false);
		return $status;
	}

	/**
	 * Copy a message from the currently selected mailbox to another mailbox
	 *
	 * @param array $uids
	 * @param string $mailbox
	 * @return bool
	 * @throws Exception
	 */
	public function copy(array $uids, $mailbox='INBOX') :bool
	{
		$this->clean($mailbox, 'mailbox');

		// commands with a percent sign already in them (e.g. a folder name) will break sprintf.
		// We prevent this by escaping it with another percent sign.
		if (str_contains($mailbox, '%')) {
			$mailbox = str_replace('%', '%%', $mailbox);
		}
		$command = "UID COPY %s \"".$this->addslashes($this->utf7_encode($mailbox))."\"\r\n";
		$status = $this->_runInChunks($command, $uids);
		return $status;
	}

	/**
	 * Move a message from the currently selected mailbox to another mailbox
	 *
	 * @param array $uids
	 * @param string $mailbox
	 * @param bool $expunge
	 * @return bool
	 * @throws Exception
	 */
	public function move(array $uids, $mailbox='INBOX', $expunge=true)
	{
		if(!$this->has_capability("MOVE")) {
			// some servers don't support UID MOVE
			if(!$this->copy($uids, $mailbox)) {
				return false;
			}

			return $this->delete($uids);
		}
		// commands with a percent sign already in them (e.g. a folder name) will break sprintf.
		// We prevent this by escaping it with another percent sign.
		if (str_contains($mailbox, '%')) {
			$mailbox = str_replace('%', '%%', $mailbox);
		}

		$this->clean($mailbox, 'mailbox');

	    $command = "UID MOVE %s \"".$this->addslashes($this->utf7_encode($mailbox))."\"\r\n";
	    $status = $this->_runInChunks($command, $uids);
	    return $status;

	}

	/**
	 * Delete messages from the currently selected mailbox
	 *
	 * @param array $uids
	 * @param bool $expunge
	 * @return bool
	 */
	public function delete(array $uids, $expunge=true)
	{
		$status = $this->set_message_flag($uids, '\Deleted \Seen');
		if(!$status) {
			return false;
		}

		return !$expunge || $this->expunge();
	}

	/**
	 * Expunge the mailbox. It will remove all the messages marked with the
	 *  \Deleted flag.
	 *
	 * @return bool
	 */
	public function expunge()
	{
		$this->send_command("EXPUNGE\r\n");
		$res = $this->get_response();
		return $this->check_response($res);
	}


	private function addslashes(string $mailbox)
	{
		// For mailserver with \ as folder delimiter
		if($this->delimiter == '\\') {
			return str_replace('"', '\"', $mailbox);
		}
		
		return $this->_escape( $mailbox);
	}

	/**
	 * Removes a mailbox
	 *
	 * @param string $mailbox
	 * @return bool
	 */
	public function delete_folder(string $mailbox) {
		$this->clean($mailbox, 'mailbox');

		$success = $this->unsubscribe($mailbox);

		$command = 'DELETE "'.$this->addslashes($this->utf7_encode($mailbox))."\"\r\n";
		$this->send_command($command);
		$result = $this->get_response(false);
		return $success;
	}

	/**
	 * @param string $mailbox
	 * @return array
	 * @deprecated / not in use!
	 * @throws Exception
	 */
	public function get_folder_tree(string $mailbox)
	{
		$this->clean($mailbox, 'mailbox');
		$delim = $this->get_mailbox_delimiter();
		return $this->get_folders($mailbox.$delim,true);
	}

	/**
	 * Rename a mailbox
	 *
	 * @param string $mailbox
	 * @param string $new_mailbox
	 * @return bool
	 * @throws Exception
	 */
	public function rename_folder(string $mailbox, string $new_mailbox): bool
	{
		$this->clean($mailbox, 'mailbox');
		$this->clean($new_mailbox, 'mailbox');

		$delim = $this->get_mailbox_delimiter();

		$children = $this->get_folders($mailbox . $delim);

		$command = 'RENAME "' . $this->addslashes($this->utf7_encode($mailbox)) . '" "' .
			$this->addslashes($this->utf7_encode($new_mailbox)) . '"' . "\r\n";

		$this->send_command($command);
		$result = $this->get_response(false);

		$status = $this->check_response($result, false);

		if ($status && $this->unsubscribe($mailbox) && $this->subscribe($new_mailbox)) {
			foreach ($children as $old_child) {
				if ($old_child['name'] != $mailbox) {
					$old_child = $old_child['name'];
					$pos = strpos($old_child, $mailbox);
					$new_child = substr_replace($old_child, $new_mailbox, $pos, strlen($mailbox));

					$this->unsubscribe($old_child);
					$this->subscribe($new_child);
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * Create a new mailbox
	 *
	 * @param string $mailbox
	 * @param bool $subscribe
	 * @return bool
	 */
	public function create_folder(string $mailbox, $subscribe=true)
	{
		$this->clean($mailbox, 'mailbox');

		$command = 'CREATE "'.$this->addslashes($this->utf7_encode($mailbox)).'"'."\r\n";

		$this->send_command($command);
		$result = $this->get_response(false);

		$status = $this->check_response($result, false);

		if(!$status) {
			return false;
		}
		return !$subscribe || $this->subscribe($mailbox);
	}


	/**
	 * Subscribe to a mailbox
	 *
	 * @param string $mailbox
	 * @return bool
	 */
	public function subscribe(string $mailbox) :bool
	{
		$command = 'SUBSCRIBE "'.$this->addslashes($this->utf7_encode($mailbox)).'"'."\r\n";
		$this->send_command($command);
		$result = $this->get_response(false, true);
		return $this->check_response($result, true);
	}

	/**
	 * Unsubscribe a mailbox
	 *
	 * @param string $mailbox
	 * @return bool
	 */
	public function unsubscribe(string $mailbox) :bool
	{
		$command = 'UNSUBSCRIBE "'.$this->addslashes($this->utf7_encode($mailbox)).'"'."\r\n";
		$this->send_command($command);
		$result = $this->get_response(false, true);
		return $this->check_response($result, true);
	}

	/**
	 * Get the next UID for the selected mailbox
	 *
	 * @return string the next UID on the IMAP server
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
	 * Get unseen and messages in an array. eg:
	 *
	 * array('messages'=>2, 'unseen'=>1);
	 *
	 * @param string $mailbox
	 * @return array|false
	 */
	public function get_status($mailbox)
	{
		$command = 'STATUS "'.$this->addslashes($this->utf7_encode($mailbox)).'" (MESSAGES UNSEEN)'."\r\n";
		$this->send_command($command);
		$result = $this->get_response(false, true);		
		
		if($result[0][1] === 'NO'){
			return false;
		}

		$vals = array_shift($result);

		$status = array('unseen'=>0, 'messages'=>0);

		$lastProp=false;
		foreach ($vals as $v) {
			if ($v == '(') {
				$flag = true;
			}
			elseif ($v == ')') {
				break;
			}
			else {
				if($lastProp=='MESSAGES'){
					$status['messages']=intval($v);
				}elseif($lastProp=='UNSEEN'){
					$status['unseen']=intval($v);
				}
			}

			$lastProp=$v;
		}

		return $status;
	}

	/**
	 * End's a line by line append operation
	 *
	 * @return bool
	 */
	public function append_end()
	{
		$result = $this->get_response(false, true);
		return  $this->check_response($result, true);
	}

	/**
	 * Feed data when after append_start is called to start an append operation
	 *
	 * @param string $string
	 * @return int|false
	 */
	public function append_feed(string $string)
	{
		return fwrite($this->handle, $string);
	}

	/**
	 * Start an append operation. Data can be fed line by line with append_feed
	 * after this function is called.
	 *
	 * @param string $mailbox
	 * @param int $size
	 * @param string $flags
	 * @param \DateTimeInterface|null $internalDate If this parameter is set, it will set the INTERNALDATE on the appended message. The parameter should be a date string that conforms to the rfc2060 specifications for a date_time value.
	 * @return bool
	 * @throws Exception
	 */
	public function append_start($mailbox, $size, $flags = "", \DateTimeInterface $internalDate = null) :bool
	{
		//Select mailbox first so we can predict the UID.
		$this->select_mailbox($mailbox);

		$this->clean($mailbox, 'mailbox');
		$this->clean($size, 'uid');
		$command = 'APPEND "'.$this->addslashes($this->utf7_encode($mailbox)).'" ('.$flags.') ';

		if(isset($internalDate)) {
			$command .= '"' . $internalDate->format("d-M-Y H:i:s O") . '" ';
		}

		$command .= '{'.$size."}\r\n";
		$this->send_command($command);
		$result = fgets($this->handle);
		if (substr($result, 0, 1) == '+') {
			return true;
		}
		return false;
	}

	/**
	 * Append a message to a mailbox
	 *
	 * @param string $mailbox
	 * @param string|File $data
	 * @param string $flags See set_message_flag
	 * @param \DateTimeInterface|null $internalDate
	 * @return boolean
	 * @throws Exception
	 */
	public function append_message($mailbox, $data, $flags="", \DateTimeInterface $internalDate = null) :bool
	{
		if ($data instanceof File) {

			if (!$this->append_start($mailbox, $data->size(), $flags, $internalDate)) {
				return false;
			}

			$fp = fopen($data->path(), 'r');

			while ($line = fgets($fp, 1024)) {
				if (!$this->append_feed($line)) {
					return false;
				}
			}

			fclose($fp);

		} else {

			if(!$this->append_start($mailbox, strlen($data), $flags, $internalDate)) {
				return false;
			}

			if(!$this->append_feed($data)) {
				return false;
			}
		}

		$this->append_feed("\r\n");

		return $this->append_end();
	}


	/**
	 * Extract uuencoded attachment from a text/plain body. Some mail clients
	 * embed attachments in the text body. This function will take them out and
	 * retrn them in an array.
	 *
	 * @param string $body
	 * @return array
	 * @deprecated or at least never used
	 *
	 */
	public function extract_uuencoded_attachments(&$body) :array
	{
		$body = str_replace("\r", '', $body);
		$regex = "/(begin ([0-7]{3}) (.+))\n(.+)\nend/Us";

		preg_match_all($regex, $body, $matches);

		$attachments = array();

		for ($i = 0; $i < count($matches[3]); $i++) {
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
				'human_size'=> \GO\Base\Util\Number::format_size($size)
			);
		}

        //remove it from the body.
	    $body = preg_replace($regex, "", $body);
		return $attachments;
	}
}
