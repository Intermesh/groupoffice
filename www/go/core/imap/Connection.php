<?php
namespace go\core\imap;

use ErrorException;
use Exception;
use go\core\imap\Streamer;
use go\core\imap\Utils;

/**
 * IMAP Connection
 * 
 * Connects and communicates with an IMAP server
 *
 * @link https://tools.ietf.org/html/rfc3501
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Connection {

	private $handle;
	private $authenticated = false;
	
	const DEBUG_TYPE_IMAP = 'imap';

	/**
	 * Use start tls
	 * 
	 * @var boolean 
	 */
	private $starttls = false;
	
	
	/**
	 *
	 * @var string 
	 */
	private $capability;
	
	
	/**
	 * the selected mailbox name
	 * 
	 * @var string 
	 */
	private $selectedMailbox;
	
	
	/**
	 * Set to the last reponse line.
	 * 
	 * eg. 
	 * 
	 * A3 OK Status completed
	 * 
	 * @var string 
	 */
	public $lastCommandStatus;
	

//	public static $test;
	
	/**
	 *
	 * @var string 
	 */
	public $connectError;
	
	/**
	 *
	 * @var int 
	 */
	public $connectErrorNo;
	
	
	/**
	 * Connects to the IMAP server
	 * 
	 * @param string $server eg. imap.example.com
	 * @param int $port The port to connect to
	 * @param boolean $ssl Use SSL encryption. Use {@see startTLS()} for TLS encryption
	 * @param int $timeout The connection timeout
	 * @return boolean
	 */
	public function connect($server, $port = 143, $ssl = false, $timeout = 10) {

//		if (!isset($this->handle)) {
//		throw new \Exception('hier');
			
			
		$streamContext = stream_context_create(['ssl' => [
				"verify_peer"=>false,
				"verify_peer_name"=>false
		]]);

		$remote = $ssl ? 'ssl://' : '';			
		$remote .=  $server.":".$port;

		GO()->debug("Connection to ".$remote);

		try{
			$this->handle = stream_socket_client($remote, $this->connectErrorNo, $this->connectError, $timeout, STREAM_CLIENT_CONNECT, $streamContext);
		}catch(ErrorException $e) {
			GO()->debug($e->getMessage());
		}

		if (!is_resource($this->handle)) {	
			
			$this->handle = null;
			GO()->debug("Connection to ".$remote." failed ".$this->connectError);
			
			return false;
		}		
	
		$response = fgets($this->handle, 8192);
	
		return $response;
	}
	
	/**
	 * Enable TLS encryption
	 * 
	 * @return boolean
	 */
	public function startTLS() {
		$this->sendCommand("STARTTLS");
		$response = $this->getResponse();

		if (!$response['success']) {
			return false;
		}

		if(!stream_socket_enable_crypto($this->handle, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
			return false;
		}else
		{
			GO()->debug("TLS Crypto enabled");
		}
					
		$this->starttls = true;
		
		return true;
	}
	
	/**
	 * Disconnect from the IMAP server
	 * 
	 * @return boolean
	 */
	public function disconnect() {
		if (is_resource($this->handle)) {
			$command = "LOGOUT";
			$this->sendCommand($command);
			$this->authenticated = false;
			
			$response = $this->getResponse();	
			
			return true;
		}else {
			return false;
		}
	}

	
	/**
	 * Checks if authentication was made
	 * 
	 * @return boolean
	 */
	public function isAuthenticated(){
		return $this->authenticated;
	}

	/**
	 * Authenticate to the IMAP server
	 * 
	 * Uses plain login
	 *
	 * @return boolean
	 */
	public function authenticate($username, $password) {

		$this->sendCommand('LOGIN "' . Utils::escape($username) . '" "' . Utils::escape($password) . '"');

		$response = $this->getResponse();
		
		//returns A1 OK lastly on success
		$this->authenticated = $response['success'];		
		
		if($this->authenticated){
			
			$lastLine = array_pop($response['data'][0]);
		
			if(($startpos = strpos($lastLine, 'CAPABILITY'))!==false){
		
				$endpos=  strpos($lastLine, ']', $startpos);
				if($endpos){
					$this->capability = substr($lastLine, $startpos, $endpos-$startpos);					
				}
			}
		}		

		return $this->authenticated;
	}	
	
	/**
	 * Get's the capabilities of the IMAP server. Useful to determine if the
	 * IMAP server supports server side sorting.
	 *
	 * @param string
	 */

	public function getCapability() {
		//Cache capability in the session so this command is not used repeatedly
	
		if(!isset($this->capability)){			
			$this->sendCommand("CAPABILITY");
			$response = $this->getResponse();
			
			$this->capability = implode(' ', $response['data']);
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
	public function hasCapability($str){
		return stripos($this->getCapability(), $str)!==false;
	}
	
	
	/**
	 * Send command to IMAP
	 * 
	 * eg. sendCommand("STATUS INBOX");
	 * 
	 * @param string $command
	 * @throws Exception
	 */
	public function sendCommand($command) {
		
		$command = 'A' . $this->commandNumber() . ' ' . $command . "\r\n";

		GO()->debug('> ' . $command);
		
		return $this->fputs($command);
	}
	
	/**
	 * Write data to the IMAP stream
	 * 
	 * @param string $str
	 * @return boolean
	 * @throws Exception
	 */
	public function fputs($str){
		//$this->connect();
		
		if (!fputs($this->handle, $str)) {
			throw new Exception("Lost connection");
		}		
		
		return true;
	}
	
	
	/**
	 * Reads a single line from the IMAP server
	 * 
	 * @param int $length
	 * @param string
	 */
	public function readLine($length = 8192, $debug = true){
		
		if($length == 0) {
			return '';
		}
		
		$line = fgets($this->handle, $length);

		if($debug){
			GO()->debug('< ' . $line);	
		}
		
		
//		var_dump($line);
		return $line;
	}

	/**
	 * Returns text response in array
	 * 
	 * @param Streamer Optionally a Streamer object can be passed to stream it to a file or output for memory efficiency.
	 * @return array IMAP server response
	 * 
	 * ```````````````````````````````````````````````````````````````````````````
	 * [
	 *   'success'=>true, //Was the command successful?
	 *   'status'=>'A3 OK Status completed'
	 *   'responses'=>[] //lines returned by IMAP
	 * ]
	 * ```````````````````````````````````````````````````````````````````````````
	 * 
	 */
	public function getResponse(Streamer $streamer = null) {

		$response = [];	
		
		$responses = [];
		
		$lastCommandTag = 'A' . $this->commandCount;
		$lastCommandTagLength = strlen($lastCommandTag);

		$commandEnd = false;
		
		$data = "";
		
		$success = null;
		
		do {
			
			$chunk = $this->readLine();
			
			if(($commandEnd = $chunk === false || substr($chunk, 0, $lastCommandTagLength) === $lastCommandTag)){
				
				if(!empty($data)){
					$response[] = trim($data);
				}
				
					
				$responses[] = $response;
				$response = [];
				
//				echo 'A' . $this->commandCount . ' OK';
				if(stripos($chunk, 'A' . $this->commandCount . ' OK') !== false){
					$success = true;
				}else
				{
					$success = false;
				}
				
				$this->lastCommandStatus = $chunk;
//				$responses[] = $chunk;
				
			}else
			{
				if(substr($chunk, 0, 1) == '*'){
					//untagged response

					if(!empty($data)){
						$response[] = trim($data);

						$responses[] = $response;
						$response = [];

						$data = "";
					}
				}				

				//check for literal {<SIZE>}
				$trimmedData = trim($chunk);

				if(substr($trimmedData,-1,1) == '}' && ($startpos = strrpos($trimmedData, ' {'))){			
					//$response[] = trim($data);
					
					//$data = "";
					
					$size = substr($trimmedData, $startpos + 2, -1);					
					$chunk = substr(rtrim($chunk), 0, -(strlen($size)+2));
					$chunk .= '"'.$this->getLiteralDataResponse($size, $streamer).'"';
				}
				
				$data .= $chunk;
			}
			
			
		} while ($commandEnd === false);


		return ['success' => $success, 'status' => $this->lastCommandStatus, 'data' => $responses];
	}
	

	/**
	 * The IMAP server can respond with some data when you fetch an attachment 
	 * for example.
	 * 
	 * This data is read into a single response. Optionally a Streamer object can
	 * be passed to stream it to a file or output for memory efficiency.
	 * 
	 * eg.:
	 * 
	 * A12 UID FETCH 13 BODY.PEEK[1.2]
     * * 13 FETCH (UID 13 BODY[1.2] {312}
	 * <html>
	 * .. more data...
	 * </html>
	 * )
	 * 
	 * @param int $size
	 * @param Streamer $streamer
	 * @param string
	 */
	private function getLiteralDataResponse($size, Streamer $streamer = null) {
		
		$max = 8192 > $size ? $size : 8192;
		
		$readLength = 0;
		$data = "";
		

		GO()->debug('< .. DATA OMITTED FROM LOG ...', 'imap');	
		
		
		$leftOver = $size;
		do{
			$newMax = $max < $leftOver ? $max : $leftOver;
			
			if($newMax==1){
				//From PHP docs of
				//Reading ends when length - 1 bytes have been read, or a newline 
				//(which is included in the return value), or an EOF (whichever comes first). 
				//If no length is specified, it will keep reading from the stream until it reaches the end of the line.
				$newMax++;
			}
			
			$line = $this->readLine($newMax, false);			
			
			//GO()->debug($line, 'imap');	
			
			$readLength += strlen($line);
			
			$leftOver = $size - $readLength;
			
			if(isset($streamer)){
				$streamer->put($line);
			}else
			{			
				$data .= $line;
			}
			
		}while ($readLength < $size);			
	
		if(isset($streamer)){
			$streamer->finish();
			return true;
		}else
		{
			return $data;
		}
	}

	private $commandCount = 0;

	private function commandNumber() {
		$this->commandCount++;
		return $this->commandCount;
	}
	
	
	
	/**
	 * Get's an array with two keys. usage and limit in bytes.
	 *
	 * @return array example ['usage' => 1024, 'limit' => 2048]
	 */
	public function getQuota() {

		if(!$this->has_capability("QUOTA"))
			return false;

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
	 * Select a mailbox on the server
	 * 
	 * @param string $name
	 * @return array {@see getResponse()}
	 */
	public function selectMailbox($name) {
		$command = 'SELECT "' . Utils::escape(Utils::utf7Encode($name)) . '"';

		$this->sendCommand($command);

		$responses = $this->getResponse();

		if ($responses['success']) {
			$this->selectedMailbox = $name;
		}
		
		return $responses;
	}
	
	/**
	 * Select this mailbox on the IMAP server
	 * 
	 * @return boolean
	 */
	public function unselectMailbox() {

		$command = 'UNSELECT';

		$this->sendCommand($command);

		$response = $this->getResponse();

		if ($response['success']) {
			$this->selectedMailbox = null;
		}

		return $response['success'];
	}
	
	/**
	 * Get the selected mailbox
	 * 
	 * @param string|null
	 */
	public function getSelectedMailbox() {
		return $this->selectedMailbox;
	}
	
	
	/**
	 * Get the root mailboxes
	 * 
	 * {@see Mailbox::getChildren()}
	 * 
	 * @param boolean $subscribedOnly
	 * @return Mailbox[]
	 */
	public function getMailboxes($subscribedOnly = true) {
		$root = new Mailbox($this);
		
		return $root->getChildren($subscribedOnly);
	}
	
	
	
	/**
	 * Create a new mailbox
	 *
	 * @param string $name
	 * @param boolean $subscribe
	 * @return Mailbox
	 */
	public function createMailbox($name, $subscribe = true) {
	
		$command = 'CREATE "' . Utils::escape(Utils::utf7Encode($name)) . '"';

		$this->sendCommand($command);

		$response = $this->getResponse();
		
		if(!$response['success']) {
			throw new Exception($response['status']);
		}
		
		$mailbox = Mailbox::findByName($this, $name);
		if(!$mailbox) {
			throw new Exception("Error finding mailbox after create !?");
		}
		
		if($subscribe) {
			$mailbox->subscribe();
		}
		
		return $mailbox;
	}

}
