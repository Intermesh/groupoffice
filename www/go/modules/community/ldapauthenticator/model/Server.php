<?php
namespace go\modules\community\ldapauthenticator\model;

use go\core\jmap\Entity;
use go\core\ldap\Connection;
use go\core\orm\Query;
use go\core\util\DateTime;

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

	public $syncUsers = false;
	public $syncUsersQuery;
	public $syncGroups = false;
	public $syncGroupsQuery;

	public $imapUseEmailForUsername = false;

	public $followReferrals = 1;
	public $protocolVersion = 3;

	
	
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
		return isset($this->password) ? \go\core\util\Crypt::decrypt($this->password) : null;
	}
	
	public function setPassword($value) {
		$this->password = !empty($value) ? \go\core\util\Crypt::encrypt($value) : null;
	}
	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable('ldapauth_server', 's')
						->addArray("domains", Domain::class, ['id' => "serverId"])
						->addArray("groups", Group::class, ['id' => "serverId"]);
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
			go()->getCache()->delete("authentication-domains");
		}

		if($this->isModified(['syncUsers', 'syncGroups'])) {
			if($this->syncGroups || $this->syncUsers){
				$this->runCronJob();
			}
		}
		
		return parent::internalSave();
	}
	
	protected static function internalDelete(Query $query) {
		go()->getCache()->delete("authentication-domains");
		
		return parent::internalDelete($query);
	}

	private $connection;

	/**
	 * Connect to LDAP server
	 * 
	 * @return Connection
	 */
	public function connect() {
		$this->connection = new Connection();
		if(!$this->connection->connect($this->getUri())) {
			throw new \Exception("Could not connect to LDAP server");
		}

		$this->connection->setOption(LDAP_OPT_REFERRALS, $this->followReferrals);
		$this->connection->setOption(LDAP_OPT_PROTOCOL_VERSION, $this->protocolVersion);


		if(!$this->ldapVerifyCertificate) {
			$this->connection->setOption(LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_NEVER);
		}
		if($this->encryption == 'tls') {
			if(!$this->connection->startTLS()) {
				throw new \Exception("Couldn't enable TLS: " . $this->connection->getError());
			}			
		}	

		if (!empty($this->username)) {			
			
			if (!$this->connection->bind($this->username, $this->getPassword())) {				
				throw new \Exception("Invalid password given for '".$this->username."'");
			} else
			{
				go()->debug("Authenticated with user '" . $this->username . '"');
			}
		}

		return $this->connection;
	}

	private function runCronJob() {

		$module = \go\core\model\Module::findByName('community', 'ldapauthenticator');

		$cron = \go\core\model\CronJobSchedule::find()->where(['moduleId' => $module->id, 'name' => 'Sync'])->single();

		if(!$cron) {
			$cron = new \go\core\model\CronJobSchedule();
			$cron->moduleId = $module->id;
			$cron->name = "Sync";
			$cron->expression = "0 0 * * *";
			$cron->description = "Synchronize LDAP Authenication server";
		}
		$cron->enabled = true;
		$cron->nextRunAt = new DateTime();
		
		if(!$cron->save()) {
			throw new \Exception("Failed to save cron job: " . var_export($cron->getValidationErrors(), true));
		}
	}
}
