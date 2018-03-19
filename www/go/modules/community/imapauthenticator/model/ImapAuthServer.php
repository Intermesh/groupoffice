<?php
namespace go\modules\community\imapauthenticator\model;

use go\core\jmap\Entity;

class ImapAuthServer extends Entity {
	
	public $id;
	public $imapHostname;
	public $imapPort;
	public $imapEncryption;
	
	public $imapValidateCertificate = true;

	public $removeDomainFromUsername = false;

	public $smtpHostname;
	public $smtpPort;
	public $smtpPassword;
	public $smtpUseUserCredentials= false;
	public $smtpValidateCertificate = true;
	public $smtpEncryption;
	
	public $domains;
	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable('imapauth_server', 's')
						->addRelation("domains", Domain::class, ['id' => "serverId"]);
	}
}
