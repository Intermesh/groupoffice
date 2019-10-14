<?php
namespace go\modules\community\ldapauthenticator\model;

use go\core\jmap\Entity;

class Server extends Entity {
	
	public $id;
	public $hostname;
	public $port = 389;
	public $encryption = "tls";
	public $ldapVerifyCertificate = true;
	
	public $usernameAttribute = "uid";
	
	public $peopleDN = "";
	public $groupsDN = "";
	
	
	public $imapHostname;
	public $imapPort;
	public $imapEncryption;
	
	public $imapValidateCertificate = true;

	public $loginWithEmail = false;

	public $smtpHostname;
	public $smtpPort;
	public $smtpUsername;
	public $smtpPassword;
	public $smtpUseUserCredentials= false;
	public $smtpValidateCertificate = true;
	public $smtpEncryption;
	
	
	/**
	 * Users must login with their full e-mail address. The domain part will be used
	 * to lookup this server profile.
	 * 
	 * @var Domain[]
	 */
	public $domains;
	
	/**
	 * New users will be added to these user groups
	 * 
	 * @var Group[]
	 */
	public $groups;

	
	/**
	 * Set username authentication is needed to lookup users / groups.
	 * 
	 * @var string
	 */
	public $username;
	
	/**
	 * Set password authentication is needed to lookup users / groups.
	 * 
	 * @var string
	 */
	protected $password;
	
	
	public function getPassword() {
		return \go\core\util\Crypt::decrypt($this->password);
	}
	
	public function setPassword($value) {
		$this->password = \go\core\util\Crypt::encrypt($value);
	}
	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable('ldapauth_server', 's')
						->addRelation("domains", Domain::class, ['id' => "serverId"])
						->addRelation("groups", Group::class, ['id' => "serverId"]);
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
	
	
	public function hasEmailAccount() {
		return $this->imapHostname != null;
	}
  
  public static function getClientName() {
    return "LdapAuthServer";
  }
	
	protected function internalSave() {
		if($this->isModified("domains")) {
			GO()->getCache()->delete("authentication-domains");
		}
		
		return parent::internalSave();
	}
	
	protected function internalDelete() {
		GO()->getCache()->delete("authentication-domains");
		
		return parent::internalDelete();
	}
}
