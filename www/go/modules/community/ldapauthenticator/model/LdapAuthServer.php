<?php
namespace go\modules\community\ldapauthenticator\model;

use go\core\jmap\Entity;

class LdapAuthServer extends Entity {
	
	public $id;
	public $hostname;
	public $port = 389;
	public $encryption = "tls";
	
	public $usernameAttribute = "uid";
	
	public $peopleDN = "";
	public $groupsDN = "";
	
	/**
	 * Users must login with their full e-mail address. The domain part will be used
	 * to lookup this server profile.
	 * 
	 * @var Domain[]
	 */
	public $domains;

	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable('ldapauth_server', 's')
						->addRelation("domains", Domain::class, ['id' => "serverId"]);
	}
	
	/**
	 * Get the URI to connect
	 * 
	 * eg. ldap://localhost:389
	 * 
	 * @return string
	 */
	public function getUri() {
		$uri = $this->encryption == 'ssl' ? 'ldaps://' : 'ldap://';
		
		$uri .= $this->hostname . ':' .$this->port;
		
		return $uri;
	}
}
