<?php

namespace go\core\ldap;

use go\core\Environment;

/**
 * LDAP connection
 * 
 * @license AGPL/Proprietary http://www.group-office.com/LICENSE.TXT
 * @link http://www.group-office.com
 * @copyright Copyright Intermesh BV
 * @author Merijn Schering <mschering@intermesh.nl> 
 */
class Connection {

	private $link;

	/**
	 * Connect to the LDAP server
	 * 
	 * @param string $uri eg. ldap://localhost:389
	 * @return  boolean
	 */
	public function connect($uri) {
		
		go()->debug('Connect to '.$uri);
		
		$this->link = ldap_connect($uri);

		$this->setOption(LDAP_OPT_PROTOCOL_VERSION, 3);
		$this->setOption(LDAP_OPT_REFERRALS, 0);

		return $this->link != false;
	}

	/**
	 * Set LDAP option
	 * 
	 * @see https://www.php.net/manual/en/function.ldap-set-option.php
	 * 
	 * @param int $name
	 * @param mixed $value
	 * 
	 * @return bool
	 */
	public function setOption($name, $value) {
		return ldap_set_option($this->link, $name, $value);
	}

	/**
	 * Start TLS encryption
	 * 
	 * @return boolean
	 */
	public function startTLS() {
		try {
			return ldap_start_tls($this->link);		
		} catch(\ErrorException $e) {
			return false;
		}
	}

	/**
	 * Get the last error
	 * 
	 * @return string
	 */
	public function getError() {
		return ldap_error($this->link);
	}

	/**
	 * Get the last error number
	 * 
	 * @return string
	 */
	public function getErrorNo() {
		return ldap_errno($this->link);
	}

	/**
	 * Disconnect
	 * 
	 * @return boolean
	 */
	public function disconnect() {
		return ldap_close($this->link);
	}

	/**
	 * Bind to the LDAP directory
	 * 
	 * @param string $bindRdn eg . cn=admin,dc=intermesh,dc=dev
	 * @param string $password
	 * @return boolean 
	 */
	public function bind($bindRdn, $password) {
		try {
			go()->debug("bind: " . $bindRdn);
			return ldap_bind($this->link, $bindRdn, $password);
		} catch (\ErrorException $e) {

			if($this->getErrorNo() == -1) {
				throw $e;
			}
			go()->debug($this->getErrorNo());
			//throws notice when failed
			go()->debug($e->getMessage());
			return false;
		}
	}

	/**
	 * Get the LDAP link for use with other ldap_* functions
	 * 
	 * @return resource
	 */
	public function getLink() {
		return $this->link;
	}

}
