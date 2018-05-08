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
	
	public $modifiedAt;

	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable('multi_instance_instance');
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
				$this->setValidationError('hostname', ErrorCode::UNAUTHORIZED, 'The configuration file is not writable');
			}

			if(!$this->getDataFolder()->isWritable()) {
				$this->setValidationError('hostname', ErrorCode::UNAUTHORIZED, 'The data folder is not writable');
			}

			if(!$this->getTempFolder()->isWritable()) {
				$this->setValidationError('hostname', ErrorCode::UNAUTHORIZED, 'The temporary files folder is not writable');
			}
		} else {
		
			if($this->isModified('hostname')) {
				$this->setValidationError('hostname', ErrorCode::UNAUTHORIZED, "You can't modify the hostname.");
			}
		}
		
		return parent::internalValidate();
	}
	
	private function getConfigFile() {
		return new File('/etc/groupoffice/' . $this->hostname . '/config.ini');
	}
	
	private function getDataFolder() {
		return GO()->getDataFolder()->getFolder('multi_instance/' . $this->hostname);
	}
	
	private function getTempFolder() {
		return GO()->getTmpFolder()->getFolder('multi_instance' . $this->hostname);
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
			
			if(!$configFile->putContents($this->createIniFile($dbName, $dbUsername, $dbPassword, $tmpFolder->getPath(), $dataFolder->getPath()))) {
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
	
	private function createIniFile($dbName, $dbUsername, $dbPassword, $tmpPath, $dataPath) {
		
		$tpl = Module::getFolder()->getFile('config.ini.tpl');
		
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
	
	protected function internalDelete() {
		
		$this->getTempFolder()->delete();
		$this->getDataFolder()->delete();
		$this->getConfigFile()->getFolder()->delete();
		
		$this->dropDatabaseUser($this->getDbUser());
		$this->dropDatabase($this->getDbName());
		
		return parent::deleteHard();
	}

}