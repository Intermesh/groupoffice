<?php

namespace go\core {

use Exception;
use GO\Base\Observable;
use go\core\auth\State as AuthState;
use go\core\cache\CacheInterface;
use go\core\cache\Disk;
use go\core\db\Connection;
use go\core\db\Database;
use go\core\db\Table;
use go\core\event\Listeners;
use go\core\exception\ConfigurationException;
use go\core\fs\Folder;
use go\core\jmap\State;
use go\core\mail\Mailer;
use go\core\util\Lock;
use go\core\webclient\Extjs3;
use go\modules\core\core\model\Settings;

	/**
	 * Application class.
	 * 
	 * A singleton instance that can be accessed from anywhere in the framework with:
	 * 
	 * ```
	 * App::get()
	 * ```
	 * 
	 * 
	 * 
	 */
	class App extends Singleton {

		/**
		 *
		 * @var Connection
		 */
		private $dbConnection;

		/**
		 *
		 * @var ErrorHandler 
		 */
		private $errorHandler;

		/**
		 * @var Mailer
		 */
		private $mailer;

		/**
		 *
		 * @var CacheInterface 
		 */
		private $cache;
		
		private $version;

		protected function __construct() {
			date_default_timezone_set("UTC");

			$this->errorHandler = new ErrorHandler();
			$this->initCompatibility();

			parent::__construct();
		}
		
		public function getVersion() {
			if(!isset($this->version)) {
				$this->version = require(Environment::get()->getInstallFolder()->getPath() . '/version.php');
			}
			return $this->version;
		}

		private function initCompatibility() {
			require(Environment::get()->getInstallPath() . "/go/GO.php");
			spl_autoload_register(array('GO', 'autoload'));
		}

		/**
		 * The mail object
		 * ```
		 * $message = App::getMailer()->compose();
		 * $message->setTo()->setFrom()->setBody()->send();
		 * ```
		 * @return Mailer
		 */
		public function getMailer() {
			if (!isset($this->mailer)) {
				$this->mailer = new Mailer();
			}
			return $this->mailer;
		}

		/**
		 * Get the installer object
		 * 
		 * @return Installer
		 */
		public function getInstaller() {
			if (!isset($this->installer)) {
				$this->installer = new Installer();
			}
			return $this->installer;
		}

		/**
		 * Get the data folder
		 * 
		 * @return Folder
		 */
		public function getDataFolder() {
			return new Folder($this->getConfig()['general']['dataPath']);
		}
		
		/**
		 * Get total space of the data folder in bytes
		 * 
		 * @return float
		 */
		public function getStorageQuota() {
			$quota = $this->getConfig()['limits']['storageQuota'];
			if(empty($quota)) {
				$quota = disk_total_space($this->getConfig()['general']['dataPath']);
			}
			
			return $quota;
		}		
		
		/**
		 * Get free space in bytes
		 * 
		 * @return float
		 */
		public function getStorageFreeSpace() {
			$quota = $this->getConfig()['limits']['storageQuota'];
			if(empty($quota)) {
				return disk_free_space($this->getConfig()['general']['dataPath']);
			} else
			{
				 $usage = \GO::config()->get_setting('file_storage_usage');				 
				 return $quota - $usage;
			}
		}

		/**
		 * Get the temporary files folder
		 * 
		 * @return Folder
		 */
		public function getTmpFolder() {
			return new Folder($this->getConfig()['general']['tmpPath']);
		}

		private $config;
		
		/**
		 * Load configuration
		 * 
		 * ```
		 * "general" => [
		 * 	  "dataPath" => "/foo/bar"
		 * 	],
		 * 
		 * "db" => [
		 * 	  "dsn" => 'mysql:host=localhost;dbname=groupoffice,
		 * 	  "username" => "user",
		 * 	  "password" => "secret"
		 *   ]
		 * "limits" => [
		 * 		"maxUsers" => 0,
		 * 		"storageQuota" => 0,
		 * 		"allowedModules" => ""
		 * 	 ]
		 * ]
		 * 
		 * ```
		 * 
		 * @param array $config
		 * @return $this;
		 */
		public function setConfig(array $config) {
			$this->config = $config;
			
			return $this;
		}
		
		private function getGlobalConfig() {
			try {
				$globalConfigFile = '/etc/groupoffice/globalconfig.inc.php';
				if (file_exists($globalConfigFile)) {
					require($globalConfigFile);
				}
			}catch(\Exception $e) {
				//openbasedir might complain here. Ignore.
				
			}
			
			return $config ?? [];
		}
		
		private function getInstanceConfig() {
			$configFile = $this->findConfigFile();
			if(!$configFile) {
				
				$msg = "No config.php was found. Possible locations: \n\n";
				
				if(isset($_SERVER['HTTP_HOST'])) {
								$msg .= '/etc/groupoffice/multi_instance/' . explode(':', $_SERVER['HTTP_HOST'])[0] . "/config.php\n\n";
				}
				
				$msg .= dirname(dirname(__DIR__)) . "/config.php\n\n".
								"/etc/groupoffice/config.php";
				
				throw new Exception($msg);
			}
			
			require($configFile);	
			
			if(!isset($config)) {
				throw new ConfigurationException();
			}
			
			return $config;
		}
		

		/**
		 * Get the configuration data
		 * 
		 * ```
		 * 
		  "general" => [
		  "dataPath" => "/foo/bar"
		  ],
		  "db" => [
		  "dsn" => 'mysql:host=localhost;dbname=groupoffice,
		  "username" => "user",
		  "password" => "secret"
		  ]
		  ]
		 * ```
		 * @return array
		 */
		public function getConfig() {

			if (isset($this->config)) {
				return $this->config;
			}
			
			$config = array_merge($this->getGlobalConfig(), $this->getInstanceConfig());
			
			if(cache\Apcu::isSupported()) {
				$cacheCls = cache\Apcu::class;				
			} else
			{
				$cacheCls = cache\Disk::class;
			}
			
			$this->config = [
					"general" => [
							"dataPath" => $config['file_storage_path'] ?? '/home/groupoffice', //TODO default should be /var/lib/groupoffice
							"tmpPath" => $config['tmpdir'] ?? sys_get_temp_dir() . '/groupoffice',
							"debug" => $config['debug'] ?? false,
							"cache" => $cacheCls,
							"servermanager" => $config['servermanager'] ?? false
					],
					"db" => [
							"name" => $config['db_name'],
							"dsn" => 'mysql:host=' . ($config['db_host'] ?? "localhost") . ';port=' . ($config['db_port'] ?? 3306) . ';dbname=' . ($config['db_name'] ?? "groupoffice-com"),
							"username" => $config['db_user'] ?? "groupoffice",
							"password" => $config['db_pass'] ?? ""
					],
					"limits" => [
							"maxUsers" => $config['max_users'] ?? 0,
							"storageQuota" => $config['quota'] ?? 0,
							"allowedModules" => $config['allowed_modules'] ?? ""
					],
					"branding" => [
						"name" => $config['product_name'] ?? "GroupOffice"
					]
			];
			
			return $this->config;
		}

		/**
		 * Get the database connection
		 * 
		 * @return Connection
		 */
		public function getDbConnection() {

			if (!isset($this->dbConnection)) {
				$db = $this->getConfig()['db'];
				$this->dbConnection = new Connection(
								$db['dsn'], $db['username'], $db['password']
				);
			}
			return $this->dbConnection;
		}

		/**
		 *
		 * @var Database
		 */
		private $database;

		/**
		 * Get the database object
		 * 
		 * @return Database
		 */
		public function getDatabase() {
			if (!isset($this->database)) {
				$this->database = new Database();
			}

			return $this->database;
		}

		/**
		 *
		 * @var Installer
		 */
		private $installer;

		/**
		 * Get a simple key value caching object
		 * 
		 * @return Disk
		 */
		public function getCache() {
			if (!isset($this->cache)) {
				$cls = $this->getConfig()['general']['cache'];
				$this->cache = new $cls;
			}
			return $this->cache;
		}
		
		/**
		 * Set the cache provider
		 * 
		 * @param CacheInterface $cache
		 * @return $this
		 */
		public function setCache(CacheInterface $cache) {
			$this->cache = $cache;
			
			return $this;
		}
		
		private $rebuildCacheOnDestruct = false;
		
		public function rebuildCache($onDestruct = false) {
			
			if($onDestruct) {				
				$this->rebuildCacheOnDestruct = $onDestruct;
			}
			
			$lock = new Lock("rebuildCache");
			if($lock->lock()) {
				\GO::clearCache(); //legacy

				GO()->getCache()->flush(false);
				Table::destroyInstances();

				$webclient = new Extjs3();
				$webclient->flushCache();

				Observable::cacheListeners();
				Listeners::get()->init();
			}
		}
		
		public function __destruct() {
			if($this->rebuildCacheOnDestruct) {
				$this->rebuildCache();
			}
		}

		/**
		 * Get a simple key value caching object
		 * 
		 * @return Debugger
		 */
		public function getDebugger() {
			if (!isset($this->debugger)) {
				$this->debugger = new Debugger();
			}

			return $this->debugger;
		}

		/**
		 * Add debug output
		 * 
		 * {@see Debugger::debug()}
		 * 
		 * @todo calls that happen in jsonSerialize() are never sent to output
		 * 
		 * @param string|callable|array|object $msg
		 */
		public function debug($msg, $type = 'general', $traceBackSteps = 0) {
			$this->getDebugger()->debug($msg, $type, $traceBackSteps);
		}

		private $authState;

		/**
		 * Set the authentication state
		 * 
		 * @param AuthState $authState
		 * @return $this
		 */
		public function setAuthState(AuthState $authState) {
			$this->authState = $authState;
			
			return $this;
		}

		/**
		 * Get the authentication handler
		 * 
		 * @return State
		 */
		public function getAuthState() {
			return $this->authState;
		}

//		/**
//		 * Get the authenticated user
//		 * 
//		 * @return auth\model\User
//		 */
//		public function getUser() {
//			if ($this->getAuthState() instanceof \go\core\auth\State) {
//				return $this->authState->getUser();
//			}
//			return null;
//		}
		
		/**
		 * Get the authenticated user
		 * 
		 * @return auth\model\User
		 */
		public function getUserId() {
			if ($this->getAuthState() instanceof AuthState) {
				return $this->authState->getUserId();
			}
			return null;
		}

		/**
		 * Get the application settings
		 * 
		 * @return Settings
		 */
		public function getSettings() {
			return Settings::get();
		}

		/**
		 * Translates a language variable name into the local language.
		 * 
		 * @param String $str String to translate
		 * @param String $module Name of the module to find the translation
		 * @param String $package Only applies if module is set to 'base'
		 */
		public function t($str, $package = 'core', $module = 'core') {
			return $this->getLanguage()->t($str, $package, $module);
		}
		
		private $language;
		
		/**
		 * 
		 * @return Language
		 */
		public function getLanguage() {
			if(!isset($this->language)) {
				$this->language = new Language();
			}
			
			return $this->language;
		}

		public static function findConfigFile($name = 'config.php') {
			
			if(defined("GO_CONFIG_FILE")) {
				return GO_CONFIG_FILE;
			}
			
			//environment variable
			if(isset($_SERVER['GO_CONFIG_FILE'])) {
				return $_SERVER['GO_CONFIG_FILE'];
			}

			if (!empty($_SERVER['HTTP_HOST'])) {
				$workingFile = '/etc/groupoffice/multi_instance/' . explode(':', $_SERVER['HTTP_HOST'])[0] . '/' . $name;
				try {
					if (file_exists($workingFile)) {
						return $workingFile;
					}
				}
				catch(\Exception $e) {
					//ignore open_basedir error
				}
			}
			
			$workingFile = dirname(dirname(__DIR__)) . '/' . $name;
			try {
				if (file_exists($workingFile)) {
					return $workingFile;
				}
			}
			catch(\Exception $e) {
				//ignore open_basedir error
			}

			$workingFile = '/etc/groupoffice/' . $name;
			try {
				if (file_exists($workingFile)) {
					return $workingFile;
				}
			}
			catch(\Exception $e) {
				//ignore open_basedir error
			}
			
			return false;
		}

	}

}

namespace {

	use go\core\App;

	/**
	 * @return App
	 */
	function GO() {
		return App::get();
	}

}
