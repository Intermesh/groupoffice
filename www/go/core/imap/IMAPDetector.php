<?php

namespace go\core\imap;

use go\core\App;
use go\core\data\Model;



/**
 * IMAP Connection
 * 
 * Connects and communicates with an IMAP server
 * 
 * 
 * 
 * 
 * Connected to localhost.
Escape character is '^]'.
220 mschering-UX31A ESMTP Postfix (Ubuntu)
ehlo localhost
250-mschering-UX31A
250-PIPELINING
250-SIZE 20480000
250-VRFY
250-ETRN
250-STARTTLS
250-ENHANCEDSTATUSCODES
250-8BITMIME
250 DSN

 *
 * @link https://tools.ietf.org/html/rfc3501
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class IMAPDetector extends Model {
	
	
	use \go\core\validate\ValidationTrait;
	
	public $hostname;
	
	public $port;
	
	public $encryption = null;
	
	public $username;
	
	public $authenticated = false;	
	
	public $email = "";
	
	public $allowInsecure = false;
	
	private $password;	
	
	public $smtpAccount;
	
	public function __construct() {
		
		$contact = App::get()->getAuthState()->getUser()->contact;
		
		if($contact) {
			$firstEmailAddress = $contact->emailAddresses->single();
			if($firstEmailAddress) {
				$this->email = $firstEmailAddress->email;
			}
			
//			$this->fromName = $contact->name;
		}
		
	}
	
	public function setPassword($password) { 
		$this->password = $password;
	}
	
	private function getDomain() {
		$parts = explode('@', $this->email);
		
		return isset($parts[1]) ? $parts[1] : '';
	}
	
	private function getMailbox() {
		$parts = explode('@', $this->email);
		
		return isset($parts[0]) ? $parts[0] : '';
	}
	
	private function _possibleServers() {
	
		
		$hosts[] = 'imap.'.$this->getDomain();
		$hosts[] = 'mail.'.$this->getDomain();
		
		getmxrr($this->getDomain(), $mxhosts);
		
		if(!empty($mxhosts)) {
			$hosts = array_merge($hosts, $mxhosts);
		}
		
		return array_unique($hosts);
	}
	
	public function detect() {
		

		
		$servers = $this->_possibleServers();
		
		foreach($servers as $server) {			
			$connection = new Connection();
			if($response = $connection->connect($server, 143, false , 3)){
				$this->port = 143;
				$this->hostname = $server;
				if(strpos($response, 'STARTTLS') && $connection->startTLS()) {
					$this->encryption = 'tls';					
					
					$success = $this->_tryLogin($connection, $this->password);					
					$connection->disconnect();				
					
					return $success;
				}else if($this->allowInsecure) {
					$success = $this->_tryLogin($connection, $this->password);					
					$connection->disconnect();
					
					return $success;
				}
			}
			
			$connection->disconnect();	
			
			//TRY SSL
			if($response = $connection->connect($server, 993, true, 3)){
				$this->port = 993;
				$this->encryption = 'ssl';	
				$this->hostname = $server;
				
				$success = $this->_tryLogin($connection, $this->password);					
				$connection->disconnect();

				return $success;
			}

		}
		
		return false;
	}
	
	private function _tryLogin(Connection $connection, $password) {		
		
		$this->username = $this->getMailbox();
		
		if($connection->authenticate($this->username, $password)){
			
			$this->authenticated = true;
			return true;
		}
		
		
		$this->username = $this->getMailbox().'@'.$this->getDomain();
		
		if($connection->authenticate($this->username, $password)){
			
			$this->authenticated = true;
			
			return true;
		}
		
		
		return false;
		
	}

	protected function internalValidate() {
		
	}

}
