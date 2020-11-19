<?php
namespace go\modules\community\imapauthenticator\model;

use go\core\orm\Query;
use go\core\jmap\Entity;

class Server extends Entity {
	
	public $id;
	public $imapHostname;
	public $imapPort;
	public $imapEncryption;
	
	public $imapValidateCertificate = true;

	public $removeDomainFromUsername = false;

	public $smtpHostname;
	public $smtpPort;
	public $smtpUsername;
	
	/**
	 * SMTP Password
	 * 
	 * @var string
	 */
	protected $smtpPassword = null;
	
	
	public function getSmtpPassword() {
		return isset($this->smtpPassword) ? \go\core\util\Crypt::decrypt($this->smtpPassword) : null;
	}
	
	public function setSmtpPassword($value) {
		$this->smtpPassword = !empty($value) ? \go\core\util\Crypt::encrypt($value) : null;
	}
	
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
	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable('imapauth_server', 's')
						->addArray("domains", Domain::class, ['id' => "serverId"])
						->addArray("groups", Group::class, ['id' => "serverId"]);
	}
  
  public static function getClientName() {
    return "ImapAuthServer";
  }
	
	protected function internalSave() {
		if($this->isModified("domains")) {
			go()->getCache()->delete("authentication-domains");
		}
		
		return parent::internalSave();
	}
	
	protected static function internalDelete(Query $query) {
		go()->getCache()->delete("authentication-domains");
		return parent::internalDelete($query);
	}
}
