<?php
namespace go\modules\community\multi_instance\model;

use Exception;
use go\core\fs\File;
use go\core\jmap\Entity;
use go\core\validate\ErrorCode;
use go\modules\community\multi_instance\Module;
use function GO;

class Instance extends Entity {
	
	public $id;	
	public $hostname;	
	public $createdAt;
	public $userCount;
	public $lastLogin;	
	public $adminDisplayName;	
	public $adminEmail; 	
	public $loginCount;	
	public $modifiedAt;

	public $removedAt;

	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable('multi_instance_instance');
	}
	
	protected function init() {
		parent::init();
		
		if(!$this->isNew()) {
			//update model from instance db once a day
			if(!isset($this->modifiedAt) || $this->modifiedAt <= new \DateTime("-1 day")) {
				$this->getInstanceDbData();
				
				if($this->isModified() && !$this->internalSave()) {
					throw new \Exception("Could not save instance data! ". var_export($this->getValidationErrors(), true));
				}
			}
		}
	}
	
	protected function internalValidate() {
		
		if($this->isNew()) {
			$this->hostname = trim(strtolower($this->hostname));

			if(!preg_match('/^[a-z0-9-_\.]*$/', $this->hostname)) {
				$this->setValidationError('hostname', ErrorCode::MALFORMED, 'The hostname was malformed');
			}

			if($this->getDbName() == GO()->getDatabase()->getName()) {
				$this->setValidationError('hostname', ErrorCode::UNIQUE, 'This hostname is not available (Database exists).');
			}

			if(\go\core\db\Utils::databaseExists($this->getDbName())) {
				$this->setValidationError('hostname', ErrorCode::UNIQUE, 'This hostname is not available (Database exists).');
			}

			//do get folder for compatibility with old config.php files
			if($this->isNew() && $this->getConfigFile()->getFolder()->exists()) {
				$this->setValidationError('hostname', ErrorCode::UNIQUE, 'This hostname is not available (config file exists).');
			}

			if($this->isNew() && $this->getDataFolder()->exists()) {
				$this->setValidationError('hostname', ErrorCode::UNIQUE, 'This hostname is not available (data folder exists).');
			}

			if(!$this->getConfigFile()->isWritable()) {
				$this->setValidationError('hostname', ErrorCode::FORBIDDEN, 'The configuration file is not writable');
			}

			if(!$this->getDataFolder()->isWritable()) {
				$this->setValidationError('hostname', ErrorCode::FORBIDDEN, 'The data folder is not writable');
			}

			if(!$this->getTempFolder()->isWritable()) {
				$this->setValidationError('hostname', ErrorCode::FORBIDDEN, 'The temporary files folder is not writable');
			}
		} else {
		
			if($this->isModified('hostname')) {
				$this->setValidationError('hostname', ErrorCode::FORBIDDEN, "You can't modify the hostname.");
			}
		}
		
		return parent::internalValidate();
	}
	
	/**
	 * Get configuration file
	 * @return File
	 */
	public function getConfigFile() {
		return new File('/etc/groupoffice/multi_instance/' . $this->hostname . '/config.php');
	}
	
	private function getDataFolder() {
		return GO()->getDataFolder()->getFolder('multi_instance/' . $this->hostname);
	}
	
	private function getTempFolder() {
		return GO()->getTmpFolder()->getFolder('multi_instance/' . $this->hostname);
	}
	
	private function getDbName() {
		return str_replace(['.','-'], '_', $this->hostname);
	}
	
	private function getDbUser() {
		return substr($this->getDbName(), 0, 16);
	}
	
	protected function internalSave() {		
		
		if(!parent::internalSave()) {
			return false;
		}
		
		if(!$this->isNew()) {
			
			if($this->isModified('deletedAt')) {
				$bak = $this->getConfigFile()->getFolder()->getFile('config.php.bak');
				if($bak->exists())
				{
					if(!$bak->rename('config.php')) {
						return false;
					}
				}
			}
			
			return true;
		}
		
		$dbName =  $this->getDbName();
		$dbUsername = $this->getDbUser();	
		$dbPassword = bin2hex(openssl_random_pseudo_bytes(8));
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

		
			$this->createDatabase($dbName);
			$databaseCreated = true;
			$this->createDatabaseUser($dbName, $dbUsername, $dbPassword);
			$databaseUserCreated = true;
			
			if(!$configFile->putContents($this->createConfigFile($dbName, $dbUsername, $dbPassword, $tmpFolder->getPath(), $dataFolder->getPath()))) {
				throw new Exception("Could not write to config file");
			}
		} catch(\Exception $e) {
			
			//cleanup
			$tmpFolder->delete();
			$dataFolder->delete();
			$configFile->getFolder()->delete();
			if($databaseCreated) {
				$this->dropDatabase($dbName);
			}

			if($databaseUserCreated) {
				$this->dropDatabaseUser($dbUsername);
			}
			
			parent::internalDelete();
			
			throw $e;
		}
		
		return true;	
	}
	
	private function dropDatabase($dbName) {		
		return GO()->getDbConnection()->query("DROP DATABASE IF EXISTS `".$dbName."`");
	}
	
	private function createDatabase($dbName) {		
		return GO()->getDbConnection()->query("CREATE DATABASE IF NOT EXISTS `".$dbName."`");
	}
	
	private function dropDatabaseUser($dbUser) {
		GO()->getDbConnection()->query("DROP USER '" . $dbUser . "'@'%'");
	}
	
	private function createDatabaseUser($dbName, $dbUsername, $dbPassword) {
		$sql = "GRANT ALL PRIVILEGES ON `" . $dbName . "`.*	TO ".
								"'".$dbUsername."'@'%' ".
								"IDENTIFIED BY '" . $dbPassword . "' WITH GRANT OPTION";			

		GO()->getDbConnection()->query($sql);
		GO()->getDbConnection()->query('FLUSH PRIVILEGES');		
	}
	
	private function createConfigFile($dbName, $dbUsername, $dbPassword, $tmpPath, $dataPath) {
		
		$tpl = Module::getFolder()->getFile('config.php.tpl');
		
		$dsn = \go\core\db\Utils::parseDSN(GO()->getConfig()['db']['dsn']);

		
		return str_replace([
				'{dbHost}',
				'{dbName}',
				'{dbUsername}',
				'{dbPassword}',
				'{tmpPath}',
				'{dataPath}'
		], [
				$dsn['options']['host'],
				$dbName,
				$dbUsername,
				$dbPassword,
				$tmpPath,
				$dataPath
		],
		$tpl->getContents());		
	}
	
	private $instanceDbConn;
	
	/**
	 * 
	 * @return \go\core\db\Connection
	 */
	private function getInstanceDbConnection() {
		if(!isset($this->instanceDbConn)) {			
			include($this->getConfigFile()->getPath());
			$dsn = 'mysql:host=' . ($config['db_host'] ?? "localhost") . ';port=' . ($config['db_port'] ?? 3306) . ';dbname=' . $config['db_name'];
			$this->instanceDbConn = new \go\core\db\Connection($dsn, $config['db_user'], $config['db_pass']);
		}
		
		return $this->instanceDbConn;
	}
	
	
	public function createAccessToken() {
		$data = [
				"loginToken" => uniqid().bin2hex(openssl_random_pseudo_bytes(16)),
				"accessToken" => uniqid().bin2hex(openssl_random_pseudo_bytes(16)),
				"expiresAt" => new \DateTime("+1 hour"),
				"userAgent" => "Multi Instance Module",
				"userId" => 1,
				"createdAt" => new \DateTime(),
				"lastActiveAt" => new \DateTime(),
				"remoteIpAddress" => $_SERVER['REMOTE_ADDR']
		];
		
		if(!$this->getInstanceDbConnection()->insert('core_auth_token', $data)->execute()) {
			throw new \Exception("Failed to create access token");
		}
		
		return $data['accessToken'];	
	}
	
	private function getInstanceDbData(){
		try {
			$record = (new \go\core\db\Query())
						->setDbConnection($this->getInstanceDbConnection())
						->select('count(*) as userCount, max(lastLogin) as lastLogin, count(loginCount) as loginCount')
						->from('core_user')
						->where('enabled', '=', true)
						->single();	
			
			$this->loginCount = (int) $record['loginCount'];
			$this->userCount = (int) $record['userCount'];
			$this->lastLogin = !empty($record['lastLogin']) ? new \go\core\util\DateTime($record['lastLogin']) : null;
			
			$record = (new \go\core\db\Query())
						->setDbConnection($this->getInstanceDbConnection())
						->select('displayName, email')
						->from('core_user')
						->where('id', '=', 1)
						->single();
			
			$this->adminDisplayName = $record['displayName'];
			$this->adminEmail = $record['email'];
		}
		catch(\Exception $e) {
			//ignore
		}
	}
	
	
	
	
	protected function internalDelete() {
		$this->getTempFolder()->delete();
		$this->getDataFolder()->delete();
		$this->getConfigFile()->getFolder()->delete();
		
		$this->dropDatabaseUser($this->getDbUser());
		$this->dropDatabase($this->getDbName());
		
		return parent::internalDelete();
	}
	
	
	public function setEnabled($value) {
		include($this->getConfigFile()->getPath());
		$config['enabled'] = $value;
		
		$this->getConfigFile()->putContents("<?php\n\$config = " . var_export($config, true) . ";\n");
		
		if(function_exists("opcache_invalidate")) {
			opcache_invalidate($this->getConfigFile()->getPath());
		}
	}
	
	public function getEnabled() {
		if($this->getConfigFile()->exists()) {
			include($this->getConfigFile()->getPath());
		
			return isset($config['enabled']) ? $config['enabled'] : true;
		} else
		{
			return null;
		}
	}
}
