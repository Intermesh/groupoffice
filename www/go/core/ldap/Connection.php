<?php

namespace go\core\ldap;

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
		
		GO()->debug('Connect to '.$uri, 'ldap');
		
		$this->link = ldap_connect($uri);

		ldap_set_option($this->link, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($this->link, LDAP_OPT_REFERRALS, 0);

		return $this->link != false;
	}

	/**
	 * Start TLS encryption
	 * 
	 * @return boolean
	 */
	public function startTLS() {
		try {
			return ldap_start_tls($this->link);
		} catch (\ErrorException $e) {
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
			return ldap_bind($this->link, $bindRdn, $password);
		} catch (\ErrorException $e) {
			//throws notice when failed
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
