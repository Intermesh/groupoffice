<?php
namespace go\modules\community\multi_instance\model;

use Exception;
use GO\Base\ModuleCollection;
use go\core\db\Connection;
use go\core\db\Criteria;
use go\core\db\Database;
use go\core\db\Utils;
use go\core\ErrorHandler;
use go\core\fs\File;
use go\core\fs\Folder;
use go\core\http\Client;
use go\core\http\Request;
use go\core\jmap\Entity;
use go\core\Module;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\util\DateTime;
use go\core\validate\ErrorCode;
use function GO;
use GO;
use GO\Base\Module as BaseModule;

class Instance extends Entity {
	
	public ?string $id;
	public string $hostname;
	public $createdAt;
	
	/**
	 * Number of users
	 * 
	 * @var int
	 */
	public $userCount;
	
	/**
	 * Maximum amount of users
	 * 
	 * @var int
	 */
	public $usersMax;
	
	public $lastLogin;	
	public $adminDisplayName;	
	public $adminEmail; 	
	public $loginCount;	
	public $modifiedAt;

	public $version;
	
	public $enabled;
	
	/**
	 * Trails will be deactivated automatically after a configurable period.
	 * 
	 * @var boolean
	 */
	public $isTrial;
	
	/**
	 * Storage usage in bytes
	 * @var int
	 */
	public $storageUsage;
	
	
	/**
	 * Storage quota in bytes
	 * 
	 * @var int
	 */
	public $storageQuota;
	
	
	protected $welcomeMessage;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
						->addTable('multi_instance_instance');
	}

	protected static function textFilterColumns(): array
	{
		return ['hostname', 'adminEmail', 'adminDisplayName'];
	}

	protected function canCreate(): bool
	{
		return \go\modules\community\multi_instance\Module::get()->getModel()->getUserRights()->mayRead;
	}

	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()
			->add('enabled', function(Criteria $c, $value){
				$c->andWhere(['enabled' => $value]);
			})
			->add('isTrial', function(Criteria $c, $value) {
				$c->andWhere('isTrial', '=', $value);
			})
			->addColumn("adminEmail")
			->addColumn("adminDisplayName");
	}


	public function getMajorVersion() {
		if(!$this->version) {
			return null;
		}
		return substr($this->version, 0, strrpos($this->version, '.'));
	}


	/**
	 * @throws Exception
	 */
	protected function init() {
		parent::init();
		
		if(!$this->isNew()) {
			//update model from instance db once a day
			if(!isset($this->modifiedAt) || $this->modifiedAt <= new \DateTime("-10 minute")) {
				$this->getInstanceDbData();

				try {
					if ($this->isModified() && !$this->save()) {
						throw new Exception("Could not save instance data! " . var_export($this->getValidationErrors(), true));
					}
				} catch(Exception $e) {
					ErrorHandler::logException($e);
				}
			}
		}
	}
	
	protected function internalValidate() {
		
		if($this->isNew()) {
			$this->hostname = trim(strtolower($this->hostname));

			if(empty($this->hostname)) {
				$this->setValidationError('hostname', ErrorCode::REQUIRED, 'The hostname field is required');
			}

			if(Utils::userExists($this->getDbUser())) {
				$this->setValidationError('hostname', ErrorCode::UNIQUE, 'This hostname is not available (User exists).');
			} elseif(!preg_match('/^[a-z0-9-_.]*$/', $this->hostname)) {
				$this->setValidationError('hostname', ErrorCode::MALFORMED, 'The hostname was malformed');
			}elseif($this->getDbName() == go()->getDatabase()->getName()) {
				$this->setValidationError('hostname', ErrorCode::UNIQUE, 'This hostname is not available (Database exists).');
			} elseif(Utils::databaseExists($this->getDbName())) {
				$this->setValidationError('hostname', ErrorCode::UNIQUE, 'This hostname is not available (Database exists).');
			} elseif($this->isNew() && $this->getConfigFile()->getFolder()->exists()) {
				$this->setValidationError('hostname', ErrorCode::UNIQUE, 'This hostname is not available (config file exists).');
			} elseif($this->isNew() && $this->getDataFolder()->exists()) {
				$this->setValidationError('hostname', ErrorCode::UNIQUE, 'This hostname is not available (data folder exists).');
			} elseif(!$this->getConfigFile()->isWritable()) {
				$this->setValidationError('hostname', ErrorCode::FORBIDDEN, 'The configuration file is not writable');
			} elseif(!$this->getDataFolder()->isWritable()) {
				$this->setValidationError('hostname', ErrorCode::FORBIDDEN, 'The data folder is not writable');
			} elseif(!$this->getTempFolder()->isWritable()) {
				$this->setValidationError('hostname', ErrorCode::FORBIDDEN, 'The temporary files folder is not writable');
			}
		} else {
		
			if($this->isModified('hostname')) {
				$this->setValidationError('hostname', ErrorCode::FORBIDDEN, "You can't modify the hostname.");
			}
		}


		
		parent::internalValidate();
	}

	/**
	 * Get configuration file
	 * @return File
	 * @throws Exception
	 */
	public function getConfigFile(): File
	{
		return new File('/etc/groupoffice/multi_instance/' . $this->hostname . '/config.php');
	}

	/**
	 * @throws Exception
	 */
	private function getDataFolder(): Folder
	{
		return go()->getDataFolder()->getFolder('multi_instance/' . $this->hostname);
	}

	/**
	 * @throws Exception
	 */
	public static function getTrashFolder() {
		return go()->getDataFolder()->getFolder('multi_instance/_trash_')->create();
	}

	private function getTempFolder(): Folder
	{
		return go()->getTmpFolder()->getFolder('multi_instance/' . $this->hostname);
	}
	
	private function getDbName() {
		return str_replace(['.','-'], '_', $this->hostname);
	}

	private function getStudioPackage() {
		return str_replace('-', "", explode(".", $this->hostname)[0]);
	}
	
	private function getDbUser() {
		return substr($this->getDbName(), 0, 16);
	}
	
	protected function internalSave(): bool
	{
		
		if(!parent::internalSave()) {
			return false;
		}
		
		if($this->isNew()) {		
			$this->createInstance();
		}


		$instanceConfig = $this->getInstanceConfig();
		$globalConfig = $this->getGlobalConfig();
		$mergedConfig = array_merge($globalConfig, $instanceConfig);

		$studioAllowed = ModuleCollection::isAllowed("studio", "business", $mergedConfig['allowed_modules'] ?? []);

		if($studioAllowed) {
			if(!$this->getModulePackageFolder()->create()) {
				throw new Exception("Could not create module package folder in go/modules/*. Please make go/modules writable.");
			}
		} else{
			if($this->getModulePackageFolder()->exists() && $this->getModulePackageFolder()->isEmpty()) {
				$this->getModulePackageFolder()->delete();
			}
		}
		
		if($this->isModified(['storageQuota', 'usersMax', 'enabled'])) {
			$instanceConfig['quota'] = $this->storageQuota / 1024;
			$instanceConfig['max_users'] = $this->usersMax;
			$instanceConfig['enabled'] = $this->enabled;
			$this->setInstanceConfig($instanceConfig);
			$this->writeConfig();
		}
		
		//$this->createWelcomeMessage();
		
		return true;	
	}

	/**
	 * Called by install/install.php when installed from server manager
	 *
	 * @throws Exception
	 */
	public function onInstall() {
		$this->createWelcomeMessage();
		
		$this->copySystemSettings();		
		
		$this->save();
	}

	/**
	 * @throws Exception
	 */
	private function copySystemSettings() {
		$core = go()->getSettings()->toArray();

		$valuesToCopy = array (
			0 => 'locale',
			4 => 'language',
			7 => 'smtpHost',
			8 => 'smtpPort',
			9 => 'smtpUsername',
			10 => 'smtpEncryption',
			11 => 'smtpEncryptionVerifyCertificate',
			15 => 'passwordMinLength',
			16 => 'logoutWhenInactive',
			19 => 'syncChangesMaxAge',
//			22 => 'primaryColor',
//			23 => 'secondaryColor',
//			24 => 'accentColor',
			26 => 'defaultTimezone',
			27 => 'defaultDateFormat',
			28 => 'defaultTimeFormat',
			29 => 'defaultCurrency',
			30 => 'defaultFirstWeekday',
			31 => 'userAddressBookId',
			32 => 'defaultListSeparator',
			33 => 'defaultTextSeparator',
			34 => 'defaultThousandSeparator',
			35 => 'defaultDecimalSeparator',
			36 => 'defaultShortDateInList',
		);

		$coreModuleId = (new \go\core\db\Query)
						->setDbConnection($this->getInstanceDbConnection())
						->selectSingleValue('id')
						->from('core_module')
						->where(['package'=>'core', 'name'=>'core'])->single();
		
		foreach($valuesToCopy as $name) {

			$this->getInstanceDbConnection()
							->replace('core_setting', ['name' => $name, 'value' => $core[$name], "moduleId" => $coreModuleId])->execute();
		}
	}

	/**
	 * @throws Exception
	 */
	private function createWelcomeMessage() {
		
		if(isset($this->welcomeMessage)) {
			$this->getInstanceDbConnection()
							->insert("core_acl", [
									'ownedBy' => 1,
									'usedIn' => 'su_announcements.acl_id',
									'modifiedAt' => new \DateTime()
							])->execute();
			
			$aclId = $this->getInstanceDbConnection()->getPDO()->lastInsertId();
			
//			$this->getInstanceDbConnection()
//							->insert("core_acl_group", [
//									'aclId' => $aclId,
//									'groupId' => 1,
//									'level' => 50
//							])->execute();
			
			$this->getInstanceDbConnection()
							->insert("core_acl_group", [
									'aclId' => $aclId,
									'groupId' => 2,
									'level' => 10
							])->execute();
			
			$this->getInstanceDbConnection()
							->insert('su_announcements', [
									'user_id' => 1,
									'acl_id' => $aclId,
									'due_time' => 0,
									'ctime' => time(),
									'mtime' => time(),
									'title' => go()->t("Welcome to Group-Office"),
									"content" => $this->welcomeMessage
							])->execute();
		}
		
		$this->welcomeMessage = null;
		
	}


	/**
	 * @throws Exception
	 */
	private function createInstance() {

		$instanceConfig = $this->getInstanceConfig();

		$instanceConfig['db_name'] = $this->getDbName();
		if(!isset($instanceConfig['db_user'])) {
			$instanceConfig['db_user'] = $this->getDbUser();
		}
		if(!isset($instanceConfig['db_pass'])) {
			$instanceConfig['db_pass'] = bin2hex(random_bytes(8));
		}

		$dataFolder = $this->getDataFolder();
		$tmpFolder = $this->getTempFolder();	
		$configFile = $this->getConfigFile();
		$databaseCreated = $databaseUserCreated = false;
		try {

			if(!$dataFolder->create()) {
				throw new Exception("Could not create data folder");
			}
			
			if(!$tmpFolder->create()) {
				throw new Exception("Could not create temporary files folder");
			}

            go()->getDbConnection()->pauseTransactions();

			$this->createDatabase($instanceConfig['db_name']);
			$databaseCreated = true;
			$this->createDatabaseUser($instanceConfig['db_name'], $instanceConfig['db_user'], $instanceConfig['db_pass']);
			$databaseUserCreated = true;

            go()->getDbConnection()->resumeTransactions();

			if(!isset($instanceConfig['db_host'])) {
				$instanceConfig['db_host'] = go()->getConfig()['db_host'];
			}

			$instanceConfig['tmpdir'] = $tmpFolder->getPath();
			$instanceConfig['file_storage_path'] = $dataFolder->getPath();
			$instanceConfig['servermanager'] = go()->findConfigFile();
			$instanceConfig['business'] = [
				'studio' => [
					'package' => $this->getStudioPackage()
				]
			];

			$instanceConfig['allowed_modules'] = array_map(function($mod) {return $mod['package'].'/'.$mod['module'];}, array_filter($this->getAllowedModules(), function($mod) {return $mod['allowed'];}));
			$instanceConfig['allowed_modules'][] = $this->getStudioPackage() . "/*";

			$this->setInstanceConfig($instanceConfig);
			$this->writeConfig();

		} catch(Exception $e) {

            go()->getDbConnection()->resumeTransactions();
			
			//cleanup
			$tmpFolder->delete();
			$dataFolder->delete();
			$configFile->getFolder()->delete();
			if($databaseCreated) {
				$this->dropDatabase($instanceConfig['db_name']);
			}

			if($databaseUserCreated) {
				$this->dropDatabaseUser($instanceConfig['db_user']);
			}

			$this->getModulePackageFolder()->delete();
			
			parent::internalDelete((new Query())->from(self::getMapping()->getPrimaryTable()->getName())->where(['id' => $this->id]));

			// User permissions incorrect
			if($e instanceof \PDOException && strpos($e->getMessage(), "1044") !== false) {
				ErrorHandler::logException($e);
				throw new Exception("Please setup the correct permissions for the database user. See https://groupoffice.readthedocs.io/en/latest/install/extras/multi_instance.html");
			}

			throw $e;
		}
	}

	/**
	 * @return Folder
	 */
	private function getModulePackageFolder(): Folder
	{
		return go()->getEnvironment()->getInstallFolder()->getFolder("go/modules/" . $this->getStudioPackage());
	}

	private function dropDatabase($dbName): void
	{
		go()->getDbConnection()->query("DROP DATABASE IF EXISTS `".$dbName."`");
	}


	private function createDatabase($dbName): void
	{
		go()->getDbConnection()->query("CREATE DATABASE IF NOT EXISTS `".$dbName."`");
	}


	private function dropDatabaseUser($dbUser) {
		go()->getDbConnection()->query("DROP USER '" . $dbUser . "'@'%'");
	}


	private function createDatabaseUser($dbName, $dbUsername, $dbPassword)
	{
		$sql = "CREATE USER '" . $dbUsername . "' IDENTIFIED BY '" . $dbPassword . "'";
		go()->getDbConnection()->query($sql);
		$sql = "GRANT ALL PRIVILEGES ON `" . $dbName . "`.* TO '" . $dbUsername . "'@'%'";
		go()->getDbConnection()->query($sql);
		go()->getDbConnection()->query('FLUSH PRIVILEGES');
	}
	
//	private function createConfigFile($dbName, $dbUsername, $dbPassword, $tmpPath, $dataPath) {
//
//		$tpl = Module::getFolder()->getFile('config.php.tpl');
//
//		$dsn = \go\core\db\Utils::parseDSN(go()->getConfig()['core']['db']['dsn']);
//
//
//		return str_replace([
//				'{dbHost}',
//				'{dbName}',
//				'{dbUsername}',
//				'{dbPassword}',
//				'{tmpPath}',
//				'{dataPath}',
//				'{servermanager}',
//				'{studioPackage}',
//		], [
//				$dsn['options']['host'],
//				$dbName,
//				$dbUsername,
//				$dbPassword,
//				$tmpPath,
//				$dataPath,
//				go()->findConfigFile(),
//				$this->getStudioPackage()
//		],
//		$tpl->getContents());
//	}
	
	private $instanceDbConn;
	
	
	private $instanceConfig;
	private $globalConfig;
	
	private function getInstanceConfig(): array
	{
		if(!isset($this->instanceConfig)) {
			try {
				include($this->getConfigFile()->getPath());
			} catch(Exception $e) {
				ErrorHandler::log("Config file missing for instance : " . $this->hostname);
				$config = [];
			}
			/** @noinspection PhpUndefinedVariableInspection */
			$this->instanceConfig = $config;
		}		
		return $this->instanceConfig;
	}
	
	public function setInstanceConfig($config) {
		$this->instanceConfig = $config;
	}

	/**
	 * @throws Exception
	 */
	private function writeConfig(): void
	{
		$file = $this->getConfigFile();
		$tmpFile = $file->getFolder()->getFile('config.tmp');
		// Write to temp file first because when we had a full disk it happened that the config.php files were empty.
		$data = "<?php\n\$config = " . var_export($this->getInstanceConfig(), true) . ";\n";

		if(!$tmpFile->putContents($data)){
			throw new Exception("Could not write to temporary file");
		}

		if(!$file->delete() || !$tmpFile->move($file)) {
			throw new Exception("Could not write to config.php file");
		}

		if(function_exists("opcache_invalidate")) {
			opcache_invalidate($this->getConfigFile()->getPath());
		}
	}
	
	private function getGlobalConfig(): array
	{
		if(!isset($this->globalConfig)) {
			$globalConfigFile = "/etc/groupoffice/globalconfig.inc.php";
			if(file_exists($globalConfigFile)) {
				include("/etc/groupoffice/globalconfig.inc.php");
				$this->globalConfig	= $config ?? [];
			} else
			{
				$this->globalConfig	= [];
			}
		}
		
		return $this->globalConfig;
	}
	
	/**
	 * 
	 * @return Connection
	 */
	private function getInstanceDbConnection(): Connection
	{
		if(!isset($this->instanceDbConn)) {		
			
			$config = $this->getInstanceConfig();

			if(empty($config['db_name'])) {
				throw new Exception("Config not found");
			}

			$dsn = 'mysql:';

			if(!empty($config['db_socket'])) {
				$dsn .= 'unix_socket=' . $config['db_socket'];
			} else {
				$dsn .= 'host=' . ($config['db_host'] ?? "localhost") . ';port=' . ($config['db_port'] ?? 3306);
			}

			$dsn .= ';dbname=' . $config['db_name'];
			$dsn .= ';charset=utf8mb4';

			$this->instanceDbConn = new Connection($dsn, $config['db_user'], $config['db_pass']);
		}
		
		return $this->instanceDbConn;
	}


	/**
	 * @throws Exception
	 */
	public function createAccessToken() {
		$now = new DateTime();
		$expiresAt = new DateTime("+1 hour");
		
		$data = [

            "loginToken" => uniqid().bin2hex(random_bytes(16)),
            "accessToken" => uniqid().bin2hex(random_bytes(16)),
            "expiresAt" => $expiresAt,
            "userId" => 1,
            "createdAt" => $now,
		];


        if($this->getInstanceDbConnection()->getDatabase()->getTable("core_auth_token")->hasColumn('userAgent')) {
            $data["userAgent"] = "Multi Instance Module";
            $data["lastActiveAt"] = $now;
            $data["remoteIpAddress"] = $_SERVER['REMOTE_ADDR'];
        } else
        {
            //6.7 with clients
            $client = go()->getAuthState()->getUser()->currentClient();
            $db = new Database($this->getInstanceDbConnection());
            $table = $db->getTable('core_client');

            $arr = [];
            foreach($table->getColumnNames() as $col) {
                $arr[$col] = $client->$col;
            }
            unset($arr['id']);

						$arr['userId'] = 1;

            if(!$this->getInstanceDbConnection()->insert('core_client', $arr)->execute()) {
                throw new Exception("Failed to create access token");
            }

            $data['clientId'] = $this->getInstanceDbConnection()->getPDO()->lastInsertId();
            $data["CSRFToken"] = uniqid().bin2hex(random_bytes(16));
        }

		if($this->getInstanceDbConnection()->getDatabase()->getTable("core_auth_token")->hasColumn('platform')) {

            //available since 6.5

            $client = go()->getAuthState()->getUser()->currentClient();

			$data["platform"] = $client->platform;
			$data["browser"] = $client->name;
		}

		if(!$this->getInstanceDbConnection()->insert('core_auth_token', $data)->execute()) {
			throw new Exception("Failed to create access token");
		}

        $this->getInstanceDbConnection()->disconnect();
		
		return $data['accessToken'];	
	}

	/**
	 * Check if the installation was performed.
	 *
	 * @return bool
	 */
	public function isInstalled() : bool {
		try {
			return $this->getInstanceDbConnection()->getDatabase()->hasTable('core_module');
		} catch(Exception $e) {
			ErrorHandler::logException($e);
			return false;
		}
	}
	
	private function getInstanceDbData(){
		try {

			//Correct old bug
			$this->getInstanceDbConnection()->exec("DELETE FROM core_setting WHERE moduleId=0");

			$record = (new \go\core\db\Query())
						->setDbConnection($this->getInstanceDbConnection())
						->select('count(*) as userCount, max(lastLogin) as lastLogin, sum(loginCount) as loginCount')
						->from('core_user')
						->where('enabled', '=', true)
						->single();	
			
			$this->loginCount = (int) $record['loginCount'];
			$this->userCount = (int) $record['userCount'];
			$this->lastLogin = !empty($record['lastLogin']) ? new DateTime($record['lastLogin']) : null;
			
			$record = (new \go\core\db\Query())
						->setDbConnection($this->getInstanceDbConnection())
						->select('displayName, email')
						->from('core_user')
						->where('id', '=', 1)
						->single();
			
			$this->adminDisplayName = $record['displayName'];
			$this->adminEmail = $record['email'];
			
			$this->storageUsage = (int) (new \go\core\db\Query())
						->setDbConnection($this->getInstanceDbConnection())
						->selectSingleValue('value')
						->from('go_settings')
						->where('name', '=', "file_storage_usage")
						->andWhere('user_id', '=', 0)
						->single();

			$this->storageUsage += (int) (new \go\core\db\Query())
				->setDbConnection($this->getInstanceDbConnection())
				->selectSingleValue('value')
				->from('go_settings')
				->where('name', '=', "email_usage")
				->andWhere('user_id', '=', 0)
				->single();

			$this->storageUsage += (int) (new \go\core\db\Query())
				->setDbConnection($this->getInstanceDbConnection())
				->selectSingleValue('value')
				->from('go_settings')
				->where('name', '=', "database_usage")
				->andWhere('user_id', '=', 0)
				->single();

			$this->version = (new \go\core\db\Query())
						->setDbConnection($this->getInstanceDbConnection())
						->selectSingleValue('value')
						->from('core_setting')
						->where('name', '=', "databaseVersion")
						->single();
			
			$config = array_merge($this->getGlobalConfig(), $this->getInstanceConfig());
			
			$this->storageQuota = isset($config['quota']) ? $config['quota'] * 1024 : null; 
			$this->enabled = $config['enabled'] ?? true;
		}
		catch(Exception $e) {
			//ignore
      go()->getDebugger()->debug($e);
		} finally {
			try {
				$this->getInstanceDbConnection()->disconnect();
			} catch(Exception $e) {
				//ignore
				go()->getDebugger()->debug($e);
			}
    }
	}	
	
	
	/**
	 * Create a mysql dump of the installation database.
	 *
	 * @throws Exception
	 */
	private function mysqldump(): void
	{
		
		$c = $this->getInstanceConfig();
		
		$file = $this->getDataFolder()->getFile('database.sql');
		$file->delete();
			
	
		$cmd = "mysqldump --force --opt --host=" . ($c['db_host'] ?? "localhost") . " --port=" . ($c['db_port'] ?? 3306) . " --user=" . $c['db_user'] . " --password=" . $c['db_pass'] . " " . $c['db_name'] . " > \"" . $file->getPath() . "\"";
		//go()->debug($cmd);
		exec($cmd, $output, $retVar);
		
		if($retVar != 0) {
			throw new Exception("Mysqldump error: " .$retVar ." : ". implode("\n", $output));
		}
		
		if(!$file->exists()) {
			throw new Exception("Could not create MySQL dump");
		}

	}

	/**
	 * @throws Exception
	 */
	public function restoreDump(File $dumpFile): bool
	{
		$c = $this->getInstanceConfig();
		$cmd = "mysql --host=" . ($c['db_host'] ?? "localhost") . " --port=" . ($c['db_port'] ?? 3306) . " --user=" . $c['db_user'] . " --password=" . $c['db_pass'] . " " . $c['db_name'] . " < \"" . $dumpFile->getPath() . "\"";
		//go()->debug($cmd);
		exec($cmd, $output, $retVar);

		if($retVar != 0) {
			throw new Exception("Mysqldump error: " .$retVar ." : ". implode("\n", $output));
		}

		return true;
	}
	
	protected static function internalDelete(Query $query): bool
	{

		// dropping databases / users will auto commit transactions. So we pause and resume.
		go()->getDbConnection()->pauseTransactions();

		$instances = Instance::find()->mergeWith($query);

		foreach($instances as $instance) {
			try {
				//load instance config just before moving the config file. Moving the config file disables
				// the installation and prevents further changes.
				$instance->getInstanceConfig();
				$instance->getConfigFile()->move($instance->getDataFolder()->getFile('config.php')->appendNumberToNameIfExists());

				try {
					$instance->getTempFolder()->delete();
				}catch(Exception $e) {
					//ignore because a running sync process might have filled up the temp dir again.
					ErrorHandler::log("Could not delete temp folder: " . $e->getMessage());
				}

				$instance->mysqldump();

				try {
					$modPackageFolder = $instance->getModulePackageFolder();
					if ($modPackageFolder->exists()) {
						$dest = $instance->getDataFolder()->getFolder($instance->getStudioPackage() . '_MODULE_PACKAGE');
						if ($dest->exists()) {
							$dest = new Folder($dest->getPath() . '-' . uniqid());
						}
						$instance->getModulePackageFolder()->move($dest);
					}
				} catch(\Throwable $e) {
					ErrorHandler::logException($e, "Error while deleting module folder for instance ". $instance->hostname);
				}

				$instance->getConfigFile()->getFolder()->delete();

				$dest = self::getTrashFolder()->getFolder($instance->getDataFolder()->getName());
				if ($dest->exists()) {
					$dest = $dest->getParent()->getFolder($instance->getDataFolder()->getName() . '-' . uniqid());
				}
				$instance->getDataFolder()->move($dest);
			
				$instance->dropDatabaseUser($instance->getDbUser());
				$instance->dropDatabase($instance->getDbName());
			}catch(Exception $e) {
				ErrorHandler::log("Error deleting instance: ". $instance->hostname);
				ErrorHandler::logException($e);

				go()->getMailer()
					->compose()
					->setSubject("Error deleting instance: ". $instance->hostname)
					->setBody($e->getMessage())
					->send();
			}
		}

		go()->getDbConnection()->resumeTransactions();
		
		return parent::internalDelete($query);
	}
	
	
		
	public function setWelcomeMessage($html) {
		$this->welcomeMessage = $html;
	}


	/**
	 * @throws Exception
	 */
	public function upgrade(): bool
	{
		$http = new Client();

		$proto = Request::get()->isHttps() ? 'https://' : 'http://';

		$http->setOption(CURLOPT_SSL_VERIFYHOST, false);
		$http->setOption(CURLOPT_SSL_VERIFYPEER, false);

		$response = $http->get($proto . $this->hostname . '/install/upgrade.php?confirmed=1&ignore=modules&onlyIfNeeded=1');

		//echo $response['body'];

		return strstr($response['body'], '<div id="success">') !== false;
	}

	private static $availableModules;

	private static function getAvailableModules(): array
	{
		if(!isset(self::$availableModules)) {
			self::$availableModules =  GO::modules()->getAvailableModules(true);
		}

		return self::$availableModules;
	}

	/**
	 * All modules with allowed bit set;
	 * @return array
	 * @throws Exception
	 */
	public function getAllowedModules(): array
	{
		$modules = self::getAvailableModules();

		$instanceConfig = array_merge($this->getGlobalConfig(), $this->getInstanceConfig());
		$checkAllowed = false;
		if (array_key_exists('allowed_modules', $instanceConfig)) {
			$checkAllowed = true;
		}
		$returnMods = [];
		$id = 0;
		foreach ($modules as $module) {
			$mod = $module::get();
			if ($mod instanceof BaseModule) {
				$key = $mod->package() . $mod->name();
				// old Framework
				$avMod = [
					'id' => $id,
					'package' => 'legacy',
					'module' => $mod->name(),
					'title' => $mod->localizedName(),
//					'icon' => $mod->icon(),
					'localizedPackage' => ucfirst($mod->package())
				];
			} elseif ($mod instanceof Module) {
				$key = ucfirst($mod->getPackage()) . $mod->getName();
				// new Framework
				$avMod = [
					'id' => $id,
					'package' => $mod->getPackage(),
					'module' => $mod->getName(),
					'title' => $mod->getTitle(),
//					'icon' => $mod->getIcon(),
					'localizedPackage' => ucfirst($mod->getPackage())
				];
			}
			if ($checkAllowed) {
				$avMod['allowed'] = ModuleCollection::isAllowed($avMod['module'], $avMod['package'], $instanceConfig['allowed_modules']);
			} else {
				$avMod['allowed'] = true;
			}
			$returnMods[$key] = $avMod;
			$id++;
		}


		return array_values($returnMods);
	}

	/**
	 * @throws Exception
	 */
	public function setAllowedModules($allowedModules)
	{
		if($this->isNew()) {
			return;
		}

		$config = $this->getInstanceConfig();
		$config['allowed_modules'] = $allowedModules;
		$config['allowed_modules'][] = $this->getStudioPackage() . "/*";
		$this->setInstanceConfig($config);
		$this->writeConfig();

	}

	/**
	 * @throws Exception
	 */
	public function addAllowedModule(string $package, string $name): void
	{
		$instanceConfig = $this->getInstanceConfig();
		$mergedConfig = array_merge($this->getGlobalConfig(), $instanceConfig);
		if (!array_key_exists('allowed_modules', $mergedConfig)) {
			// all modules are allowed
			return;
		}

		if(ModuleCollection::isAllowed($name, $package, $mergedConfig['allowed_modules'])) {
			return;
		}

		$instanceConfig['allowed_modules'] =  $mergedConfig['allowed_modules'];
		$instanceConfig['allowed_modules'][] = $package .'/' .$name;
		$this->setInstanceConfig($instanceConfig);
		$this->writeConfig();


	}

}
