<?php
namespace go\modules\imapauth\model;

use go\core\jmap\Entity;

class Server extends Entity {
	
	public $id;
	public $imapHostname;
	public $imapPort;
	public $imapEncryption;
	
	public $imapValidateCertificate = true;

	public $removeDomainFromUsername = false;
	
	public $createEmailAcount = true;
	
	public $smtpHostname;
	public $smtpPort;
	public $smtpPassword;
	public $smtpUseImapCredentials = false;
	
	public $domains;
	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable('imapauth_server')
						->addRelation("domains", $entityName, ['id' => "serverId"]);
	}
}
