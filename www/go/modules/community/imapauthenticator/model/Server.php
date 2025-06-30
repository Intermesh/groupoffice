<?php
namespace go\modules\community\imapauthenticator\model;

use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\jmap\Entity;

class Server extends Entity {
	
	public ?string $id;
	public string $imapHostname;
	public int $imapPort = 143;
	public ?string $imapEncryption = "tls";
	
	public bool $imapValidateCertificate = true;

	public bool $removeDomainFromUsername = false;

	public ?string $smtpHostname;
	public ?int $smtpPort;
	public ?string $smtpUsername;
	
	/**
	 * SMTP Password
	 * 
	 * @var ?string
	 */
	protected ?string $smtpPassword = null;


	public function historyLog(): bool|array
	{
		$log = parent::historyLog();

		if(isset($log['smtpPassword'])) {
			$log['smtpPassword'][0] = "MASKED";
			$log['smtpPassword'][1] = "MASKED";
		}

		return $log;
	}
	
	
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
	
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
						->addTable('imapauth_server', 's')
						->addArray("domains", Domain::class, ['id' => "serverId"])
						->addArray("groups", Group::class, ['id' => "serverId"]);
	}
  
  public static function getClientName(): string
  {
    return "ImapAuthServer";
  }
	
	protected function internalSave(): bool
	{
		if($this->isModified("domains")) {
			go()->getCache()->delete("authentication-domains");
		}
		
		return parent::internalSave();
	}
	
	protected static function internalDelete(Query $query): bool
	{
		go()->getCache()->delete("authentication-domains");
		return parent::internalDelete($query);
	}
}
