<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace go\core\ldap;

use Exception;

/**
 * LDAP connection
 * 
 * @license AGPL/Proprietary http://www.group-office.com/LICENSE.TXT
 * @link http://www.group-office.com
 * @copyright Copyright Intermesh BV
 * @author Merijn Schering <mschering@intermesh.nl> 
 */
class Connection {

	private \LDAP\Connection $link;

	/**
	 * Connect to the LDAP server
	 *
	 * @param string $uri eg. ldap://localhost:389
	 * @return  boolean
	 * @throws Exception
	 */
	public function connect(string $uri): bool
	{
		
		go()->debug('Connect to '.$uri);

		if(!function_exists("ldap_connect")) {
			throw new Exception("Please install the LDAP extension for PHP if you wish to use LDAP authentication.");
		}
		$link = ldap_connect($uri);
		if(!$link)
		{
			return false;
		}
		$this->link = $link;

		return true;
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
	public function setOption(int $name, mixed $value): bool
	{
		return ldap_set_option($this->link, $name, $value);
	}

	/**
	 * Start TLS encryption
	 * 
	 * @return boolean
	 */
	public function startTLS(): bool
	{
		try {
			return ldap_start_tls($this->link);		
		} catch(Exception $e) {
			return false;
		}
	}

	/**
	 * Get the last error
	 * 
	 * @return string
	 */
	public function getError(): string
	{
		return ldap_error($this->link);
	}

	/**
	 * Get the last error number
	 * 
	 * @return int
	 */
	public function getErrorNo(): int
	{
		return ldap_errno($this->link);
	}

	/**
	 * Disconnect
	 * 
	 * @return boolean
	 */
	public function disconnect(): bool
	{
		return ldap_close($this->link);
	}

	/**
	 * Bind to the LDAP directory
	 *
	 * @param string $bindRdn eg . cn=admin,dc=intermesh,dc=dev
	 * @param string $password
	 * @return boolean
	 * @throws Exception
	 */
	public function bind(string $bindRdn, string $password): bool
	{
		try {
			go()->debug("bind: " . $bindRdn);
			return ldap_bind($this->link, $bindRdn, $password);
		} catch (Exception $e) {

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
	 * @return \LDAP\Connection
	 */
	public function getLink(): \LDAP\Connection
	{
		return $this->link;
	}

}
