<?php

namespace GO\Base\Ldap;


class Connection{
	
	private $_host;
	private $_port;
	private $_tls;
	
	private $_link;
	
	public function __construct($host, $port, $tls=false) {
		$this->_host=$host;
		$this->_port=$port;
		$this->_tls=$tls;
	}
	
	/**
	 * Connect to the LDAP server defined in config.php
	 * 
	 * @return \Connection
	 * @throws Exception
	 */
	public static function getDefault(){
		
		if(empty(\GO::config()->ldap_host))
			\GO::config()->ldap_host='localhost';
		
		if(empty(\GO::config()->ldap_port))
			\GO::config()->ldap_port=389;
		
		$ldapConn = new Connection(\GO::config()->ldap_host, \GO::config()->ldap_port, !empty(\GO::config()->ldap_tls));

		//support old deprecated config.
		if(!empty(\GO::config()->ldap_user))
			\GO::config()->ldap_bind_rdn=\GO::config()->ldap_user;
		
		if (!empty(\GO::config()->ldap_bind_rdn)) {
			$bound = $ldapConn->bind(\GO::config()->ldap_bind_rdn, \GO::config()->ldap_pass);
			if (!$bound)
				throw new \Exception("Failed to bind to LDAP server with RDN: " . \GO::config()->ldap_bind_rdn);
		}
		
		return $ldapConn;
	}
	
	/**
	 * Establish the LDAP connection
	 * 
	 * @return boolean 
	 */
	public function connect(){		
		if(!$this->_link){
			\GO::debug('LDAP::connect() to '.$this->_host.' on port '.$this->_port);
			$this->_link=ldap_connect($this->_host, $this->_port);
			
			if(!$this->_link)
				throw new \Exception ("LDAP connection to ".$this->_host." on ".$this->_port." failed");
			
			ldap_set_option($this->_link, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($this->_link, LDAP_OPT_REFERRALS, 0);
			
			if($this->_tls){
				\GO::debug('LDAP: Starting LDAP TLS');
				ldap_start_tls($this->_link);
			}
		}
		
		return true;		
	}
	
	/**
	 * Disconnect
	 */
	public function disconnect(){
		\GO::debug("LDAP::disconnect()");
		if($this->_link)
			ldap_close($this->_link);
	}
	
	/**
	 * Bind to the LDAP directory
	 * 
	 * @param string $bindRdn eg . cn=admin,dc=intermesh,dc=dev
	 * @param string $password
	 * @return boolean 
	 */
	public function bind($bindRdn, $password){
		\GO::debug("LDAP::bind($bindRdn, ***)");
		
		$this->connect();
		
		return @ldap_bind($this->_link, $bindRdn, $password);
	}
	
	/**
	 * Search the LDAP directory
	 * 
	 * @param string $baseDN
	 * @param string $query
	 * @param array $attributes
	 * @return Result 
	 */
	public function search($baseDN, $query, $attributes=null){
		
		\GO::debug("LDAP::search($baseDN, $query)");
		
		$this->connect();		
		
		
		if(isset($attributes))
			$searchId = ldap_search($this->_link, $baseDN, $query, $attributes);
		else
			$searchId = ldap_search($this->_link, $baseDN, $query);
		
		if(!$searchId)
			throw new \Exception("Invalid LDAP search BaseDN: $baseDN, Query: $query");
		
		return new Result($this, $searchId);
	}
	
	/**
	 * Get the connection resource identifier.
	 * 
	 * @return resource 
	 */
	public function getLink(){
		return $this->_link;
	}
}
