<?php /** @noinspection PhpPrivateFieldCanBeLocalVariableInspection */

/** @noinspection PhpUnused */

namespace go\modules\community\ldapauthenticator\model;

use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Exception;
use go\core\jmap\Entity;
use go\core\ldap\Connection;
use go\core\ldap\Record;
use go\core\model\CronJobSchedule;
use go\core\model\Module;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\util\Crypt;
use go\core\util\DateTime;
use go\core\validate\ErrorCode;

class Server extends Entity
{

	public ?string $id;
	public ?string $hostname;
	public int $port = 389;
	public ?string $encryption = "tls";
	public bool $ldapVerifyCertificate = true;

	public string $usernameAttribute = "uid";

	public string $peopleDN = "";
	public string $groupsDN = "";


	public ?string $imapHostname;
	public ?int $imapPort;
	public ?string $imapEncryption;

	public bool $imapValidateCertificate = true;

	public bool $loginWithEmail = false;

	public ?string $smtpHostname;
	public ?int $smtpPort;
	public ?string $smtpUsername;
	public ?string $smtpPassword;
	public bool $smtpUseUserCredentials= false;
	public bool $smtpValidateCertificate = true;
	public ?string $smtpEncryption;

	public bool $syncUsers = false;
	public bool $syncUsersDelete = false;
	public ?string $syncUsersQuery;
	public bool $syncGroups = false;
	public bool $syncGroupsDelete = false;
	public ?string $syncGroupsQuery;
	public int $syncGroupsMaxDeletePercentage = 5;
	public int $syncUsersMaxDeletePercentage = 5;

	public bool $imapUseEmailForUsername = false;

	public int $followReferrals = 1;
	public int $protocolVersion = 3;




	/**
	 * Users must login with their full e-mail address. The domain part will be used
	 * to lookup this server profile.
	 *
	 * @var Domain[]
	 */
	public array $domains;
	
	/**
	 * New users will be added to these user groups
	 *
	 * @var Group[]
	 */
	public array $groups;


	/**
	 * Set username authentication is needed to lookup users / groups.
	 * 
	 * @var ?string
	 */
	public ?string $username;
	
	/**
	 * Set password authentication is needed to lookup users / groups.
	 * 
	 * @var ?string
	 */
	protected ?string $password;


	public function historyLog(): bool|array
	{
		$log = parent::historyLog();

		if (isset($log['password'])) {
			$log['password'][0] = "MASKED";
			$log['password'][1] = "MASKED";
		}

		if (isset($log['smtpPassword'])) {
			$log['smtpPassword'][0] = "MASKED";
			$log['smtpPassword'][1] = "MASKED";
		}

		return $log;
	}


	/**
	 * @throws Exception
	 */
	public function getPassword(): ?string
	{
		return isset($this->password) ? Crypt::decrypt($this->password) : null;
	}

	/**
	 * @throws EnvironmentIsBrokenException
	 */
	public function setPassword($value)
	{
		$this->password = !empty($value) ? Crypt::encrypt($value) : null;
	}

	protected static function defineMapping(): Mapping
	{
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
	public function getUri(): string
	{
		$uri = $this->encryption == 'ssl' ? 'ldaps://' : 'ldap://';

		$uri .= $this->hostname . ':' . $this->port;

		return $uri;
	}


	public function hasEmailAccount(): bool
	{
		return $this->imapHostname != null;
	}

	public static function getClientName(): string
	{
		return "LdapAuthServer";
	}

	protected function internalSave(): bool
	{
		if ($this->isModified("domains")) {
			go()->getCache()->delete("authentication-domains");
		}

		if ($this->isModified(['syncUsers', 'syncGroups'])) {
			if ($this->syncGroups || $this->syncUsers) {
				$this->runCronJob();
			}
		}

		return parent::internalSave();
	}

	protected function internalValidate()
	{
		try {
			$connection = $this->connect();
		} catch (Exception $e) {
			$this->setValidationError('hostname', ErrorCode::CONNECTION_ERROR, $e->getMessage());
			return;
		}

		try {
			$query = $this->getAuthenticationQuery("*");
			Record::find($connection, $this->peopleDN, $query)->fetch();
		} catch (Exception $e) {
			$this->setValidationError('general', ErrorCode::MALFORMED, go()->t("Failed to query user for authentication") . ": " . $e->getMessage());
		}


		if ($this->syncGroups) {
			try {
				Record::find($connection, $this->groupsDN, $this->syncGroupsQuery)->fetch();
			} catch (Exception $e) {
				$this->setValidationError('syncGroupsQuery', ErrorCode::MALFORMED, go()->t("Failed to query groups for synchronization") . ": " . $e->getMessage());
			}
		}

		parent::internalValidate();
	}

	protected static function internalDelete(Query $query): bool
	{
		go()->getCache()->delete("authentication-domains");

		return parent::internalDelete($query);
	}

	public function getAuthenticationQuery($username): string
	{
		$query = $this->usernameAttribute . "=" . $username;
		if ($this->syncUsersQuery) {
			$query = '(&' . $this->syncUsersQuery . "(" . $query . "))";
		}
		return $query;
	}

	private $connection;

	/**
	 * Connect to LDAP server
	 *
	 * @return Connection
	 * @throws Exception
	 */
	public function connect(): Connection
	{
		$this->connection = new Connection();
		if (!$this->connection->connect($this->getUri())) {
			throw new Exception("Could not connect to LDAP server");
		}

		$this->connection->setOption(LDAP_OPT_REFERRALS, $this->followReferrals);
		$this->connection->setOption(LDAP_OPT_PROTOCOL_VERSION, $this->protocolVersion);

		// timeout in 10s
//		$this->connection->setOption(LDAP_OPT_NETWORK_TIMEOUT, 10);

		if (!$this->ldapVerifyCertificate) {
			$this->connection->setOption(LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_NEVER);
		}
		if ($this->encryption == 'tls') {
			if (!$this->connection->startTLS()) {
				throw new Exception("Couldn't enable TLS: " . $this->connection->getError());
			}
		}

		if (!empty($this->username)) {

			if (!$this->connection->bind($this->username, $this->getPassword())) {
				throw new Exception("Invalid password given for '" . $this->username . "'");
			} else {
				go()->debug("Authenticated with user '" . $this->username . '"');
			}
		}

		return $this->connection;
	}

	/**
	 * @throws Exception
	 */
	private function runCronJob()
	{

		$module = Module::findByName('community', 'ldapauthenticator');

		$cron = CronJobSchedule::find()->where(['moduleId' => $module->id, 'name' => 'Sync'])->single();

		if (!$cron) {
			$cron = new CronJobSchedule();
			$cron->moduleId = $module->id;
			$cron->name = "Sync";
			$cron->expression = "0 0 * * *";
			$cron->description = "Synchronize LDAP Authenication server";
		}
		$cron->enabled = true;
		$cron->nextRunAt = new DateTime();

		if (!$cron->save()) {
			throw new Exception("Failed to save cron job: " . var_export($cron->getValidationErrors(), true));
		}
	}
}
