<?php

namespace go\core {

use Exception;
	use GO;
	use GO\Base\Observable;
use go\core\auth\State as AuthState;
use go\core\cache\CacheInterface;
use go\core\db\Connection;
use go\core\db\Database;
use go\core\db\Query;
use go\core\db\Table;
use go\core\event\EventEmitterTrait;
use go\core\event\Listeners;
use go\core\exception\ConfigurationException;
use go\core\fs\Blob;
use go\core\fs\Folder;
use go\core\jmap\Request;
use go\core\jmap\State;
use go\core\mail\Mailer;
use go\core\orm\Property;
use go\core\util\ArrayObject;
use go\core\webclient\Extjs3;
use go\core\model\Settings;

use const GO_CONFIG_FILE;

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
	class App extends Module {
		
		//use SingletonTrait;

		use EventEmitterTrait;

		/**
		 * Fires when the index page loads for the web client.
		 */
		const EVENT_INDEX = 'index';

		/**
		 * Fires when the application is loaded in the <head></head> section of the webclient.
		 * Can also be used to adjust the Content Security Policy
		 */
		const EVENT_HEAD = 'head';

		/**
		 * Fires after all scripts have been loaded
		 */
		const EVENT_SCRIPTS = 'scripts';

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
			parent::__construct();
			date_default_timezone_set("UTC");

			mb_internal_encoding("UTF-8");
			mb_regex_encoding("UTF-8");

			$this->errorHandler = new ErrorHandler();
			$this->initCompatibility();

			//more code to initialize at the bottom of this file as it depends on this class being constructed
		}
		
		/**
		 * Required for app being a go\core extend
		 * 
		 * @return string
		 */
		public function getAuthor(): string
		{
			return "Intermesh BV";
		}

		/**
		 * Required for app being a go\core extend
		 * 
		 * @return string
		 */
		public static function getName(): string
		{
			return "core";
		}

		/**
		 * Required for app being a go\core extend
		 * 
		 * @return string
		 */
		public static function getPackage(): string
		{
			return "core";
		}

		/**
		 * Get version number
		 * 
		 * @return string eg. 6.4.1
		 */
		public function getVersion(): string
		{
			if(!isset($this->version)) {
				$this->version = require(Environment::get()->getInstallFolder()->getPath() . '/version.php');
			}
			return $this->version;
		}

		/**
		 * Major version
		 * 
		 * @return string eg. 6.4
		 */
		public function getMajorVersion(): string
		{
			
			return substr($this->getVersion(), 0, strrpos($this->getVersion(), '.') );
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
		public function getMailer(): Mailer
		{
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
		public function getInstaller(): Installer
		{
			if (!isset($this->installer)) {
				$this->installer = new Installer();
			}
			return $this->installer;
		}

		/**
		 * Get the data folder
		 *
		 * @return Folder
		 * @throws Exception
		 */
		public function getDataFolder(): Folder
		{
			return new Folder($this->getConfig()['file_storage_path']);
		}

		private $storageQuota;

		/**
		 * Get total space of the data folder in bytes
		 *
		 * @return float
		 * @throws ConfigurationException
		 */
		public function getStorageQuota() {
			if(!isset($this->storageQuota)) {
				$this->storageQuota = $this->getConfig()['quota'];
				if(empty($this->storageQuota)) {
					try {
						$this->storageQuota = disk_total_space($this->getConfig()['file_storage_path']);
					}
					catch(Exception $e) {
						go()->warn("Could not determine total disk space: ". $e->getMessage());
						$this->storageQuota = 0;
					}
				}
			}
			
			return $this->storageQuota;
		}		

		private $storageFreeSpace;

		/**
		 * Get free space in bytes
		 *
		 * @return float
		 * @throws ConfigurationException
		 */
		public function getStorageFreeSpace() {
			if(!isset($this->storageFreeSpace)) {
				$quota = $this->getConfig()['quota'];
				if(empty($quota)) {
					try {
						$this->storageFreeSpace = disk_free_space($this->getConfig()['file_storage_path']);
					}
					catch(Exception $e) {
						go()->warn("Could not determine free disk space: ". $e->getMessage());
						$this->storageFreeSpace = 0;
					}
				} else
				{
					$usage = (int) GO::config()->get_setting('file_storage_usage', 0, 0);
					$this->storageFreeSpace = $quota - $usage;
				}
			}

			return $this->storageFreeSpace;
		}

		/**
		 * Get the temporary files folder
		 *
		 * @return Folder
		 * @throws ConfigurationException
		 * @throws Exception
		 */
		public function getTmpFolder(): Folder
		{
			return new Folder($this->getConfig()['tmpdir']);
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
				return [];
			}
			
			require($configFile);	
			
			if(!isset($config)) {
				$config = [];
			}
			$config['configPath'] = $configFile;

			return $config;
		}


    /**
     * Get the configuration data from config.php and globalconfig.inc.php
     *
     * @return array
     * @throws ConfigurationException
     */
		public function getConfig() {

			if (isset($this->config)) {
				return $this->config;
			}

			//If acpu is supported we can use it to cache the config object.
			// if(cache\Apcu::isSupported() && ($token = State::getClientAccessToken())) {
			// 	$cacheKey = 'go_conf_' . $token;

			// 	$this->config = apcu_fetch($cacheKey);
			// 	if($this->config && $this->config['cacheTime'] > filemtime($this->config['configPath']) && (!file_exists('/etc/groupoffice/globalconfig.inc.php') || $this->config['cacheTime'] > filemtime('/etc/groupoffice/globalconfig.inc.php'))) {
			// 		if(Request::get()->getHeader('X-Debug') == "1") {
			// 			$this->config['core']['general']['debug'] = true;
			// 		}
			// 		return $this->config;
			// 	}
			// }
			
			//$config = array_merge($this->getGlobalConfig(), $this->getInstanceConfig());

			//defaults
			$config = new ArrayObject([
				"frameAncestors" => "",
				"theme" => "Paper",
				"allow_themes" => true,
				"file_storage_path" => '/home/groupoffice',
				"tmpdir" => sys_get_temp_dir() . '/groupoffice',
				"debug" => false,
				"debugEmail" => false,
				"servermanager" => false,
				"sseEnabled" => true,
				"max_users" => 0,
				'quota' => 0,
				"allowed_modules" => "",
				"product_name" => "Group-Office",

				"db_host" => "localhost",
				"db_port" => 3306,
				"db_name" => "groupoffice",
				"db_user" => "groupoffice",
				"db_pass" => ""
			]);

			$config->mergeRecursive($this->getGlobalConfig());
			$config->mergeRecursive($this->getInstanceConfig());

			if(!isset($config['debug_log'])) {
				$config['debug_log'] = !empty($config['debug']);
			}
			
			if(!isset($config['cache'])) {
				if(cache\Apcu::isSupported()) {
					$config['cache'] = cache\Apcu::class;
				} else
				{
					$config['cache'] = cache\Disk::class;
				}
			}

			if(Request::get()->getHeader('X-Debug') == "1") {
				$config['debug'] = true;
			}

			$this->config = $config->getArray();

			$this->initTCPDF();

			return $this->config;
		}

		/**
		 * The TCPDF cache path must be set before autoloading it from vendor.
		 */
		private function initTCPDF() {
			define("K_PATH_CACHE", $this->config['tmpdir'] . "/");
		}

		/**
		 * Get the database connection
		 *
		 * @return Connection
		 * @throws ConfigurationException
		 */
		public function getDbConnection() {
			if (!isset($this->dbConnection)) {
				$config = $this->getConfig();
				$dsn = 'mysql:host=' . $config['db_host'] . ';port=' . $config['db_port']  . ';dbname=' . $config['db_name'];
				$this->dbConnection = new Connection(
					$dsn, $config['db_user'], $config['db_pass']
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
		 * @return Cache\Apcu
		 * @throws ConfigurationException
		 */
		public function getCache() {
			if (!isset($this->cache)) {				
				$cls = $this->getConfig()['cache'];
				// go()->log("Using cache: " . $cls);
				$this->cache = new $cls;
			}
			return $this->cache;
		}


		/**
		 * Get a module
		 *
		 * return the module if it's installed and available.
		 *
		 * @param string $package Set to null for legacy modules
		 * @param string $name
		 * @return \go\core\model\Module
		 * @throws Exception
		 */
		public function getModule($package, $name) {

			$cacheKey = 'getModule-' . $package .'-'.$name;

			$model = go()->getCache()->get($cacheKey);

			if(isset($model)) {
				return $model;
			}

			$model = \go\core\model\Module::find()->where(['package' => $package, 'name' => $name, 'enabled' => true])->single();
			if(!$model || !$model->isAvailable()) {
				$model = false;
			}

			go()->getCache()->set($cacheKey, $model);
			
			return $model;
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

		/**
		 * Destroys all cache and reinitializes event listeners and sync state.
		 *
		 * @param boolean $onDestruct
		 * @throws ConfigurationException
		 */
		public function rebuildCache($onDestruct = false) {
			
			if($onDestruct) {				
				$this->rebuildCacheOnDestruct = $onDestruct;
				return;
			}

			$this->rebuildCacheOnDestruct = false;
			
			GO::clearCache(); //legacy

			go()->getCache()->flush(false);
			Table::destroyInstances();
			Property::clearCache();

			Settings::flushCache();

			$webclient = Extjs3::get();
			$webclient->flushCache();

			Observable::cacheListeners();

			Listeners::get()->init();

			$this->resetSyncState();

			go()->getSettings()->cacheClearedAt = time();
			go()->getSettings()->save();
			
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
		public function debug($msg, $traceBackSteps = 0) {
			$this->getDebugger()->log($msg, $traceBackSteps);
		}
		
		public function log($msg, $traceBackSteps = 0) {
			$this->getDebugger()->log($msg, $traceBackSteps);
		}
		
		public function warn($msg, $traceBackSteps = 0) {
			$this->getDebugger()->warn($msg, $traceBackSteps);
		}
		
		public function error($msg, $traceBackSteps = 0) {
			$this->getDebugger()->error($msg, $traceBackSteps);
		}
		
		public function info($msg, $traceBackSteps = 0) {
			$this->getDebugger()->info($msg, $traceBackSteps);
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
		
		/**
		 * Get the server environment
		 * 
		 * @return Environment
		 */
		public function getEnvironment() {
			return Environment::get();
		}

		/**
		 * Get the authenticated user ID
		 * 
		 * If you need to get the full user use:
		 * 
		 * ```
		 * go()->getAuthState()->getUser();
		 * ```
		 * @return int
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

		/**
		 * Find the config.php file location.
		 * 
		 * It will search for:
		 * 
		 * - 'GO_CONFIG_FILE' constant or environment variable ($_SERVER['GO_CONFIG_FILE']).
		 * - /etc/groupoffice/multi_instance/<HOSTNAME>/config.php
		 * - <GROUPOFFICEDIR>/config.php
		 * - /etc/groupoffice/config.php
		 * 
		 * @param string $name
		 * @return boolean|string
		 */
		public static function findConfigFile($name = 'config.php') {
			
			if(defined("GO_CONFIG_FILE")) {
				return GO_CONFIG_FILE;
			}
			
			//environment variable
			if(isset($_SERVER['GO_CONFIG_FILE'])) {
				return $_SERVER['GO_CONFIG_FILE'];
			}

			if (!empty($_SERVER['HTTP_HOST'])) {
				$domain = explode(':', $_SERVER['HTTP_HOST'])[0];

				//hack for wopi subdomain
				$domain = str_replace('.wopi.', '.', $domain);

				$workingFile = '/etc/groupoffice/multi_instance/' . $domain . '/' . $name;
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
		
		/**
		 * Resets all entity state so all clients must resync data.
		 */
		private function resetSyncState() {
			//reset all mod seqs
			go()->getDbConnection()->update('core_entity', ['highestModSeq' => 0])->execute();
			go()->getDbConnection()->exec("TRUNCATE TABLE core_change");
			go()->getDbConnection()->exec("TRUNCATE TABLE core_acl_group_changes");

			// Disable keys otherwise this might take very long!
			go()->getDbConnection()->exec("SET unique_checks=0; SET foreign_key_checks=0;");
			go()->getDbConnection()->insert('core_acl_group_changes', (new Query())->select("null, aclId, groupId, '0', null")->from("core_acl_group"))->execute();
			go()->getDbConnection()->exec("SET unique_checks=1; SET foreign_key_checks=1;");
		}

		/**
		 * Download method for module icons
		 * 
		 * /api/download.php?blob=core/moduleIcon/community/addressbook
		 */
		public function downloadModuleIcon($package, $name) {

			if($package == "legacy") {
				$file = go()->getEnvironment()->getInstallFolder()->getFile('modules/' . $name .'/themes/Default/images/'.$name.'.png');
				if(!$file->exists()) {
					$file = go()->getEnvironment()->getInstallFolder()->getFile('modules/' . $name .'/views/Extjs3/themes/Default/images/'.$name.'.png');
				}	

				if(!$file->exists()) {
					$file = go()->getEnvironment()->getInstallFolder()->getFile('modules/' . $name .'/themes/Default/'.$name.'.png');
				}	

				

			} else {
				$file = go()->getEnvironment()->getInstallFolder()->getFile('go/modules/' . $package . '/' . $name .'/icon.png');	
			}

			if(!$file->exists()) {
				$file = go()->getEnvironment()->getInstallFolder()->getFile('views/Extjs3/themes/Paper/img/default-avatar.svg');
			}
			$file->output(true, true, ['Content-Disposition' => 'inline; filename="module.svg"']);
		}


		/**
		 * Display's logo without authentication via /api/page.php/core/logo
		 * @throws Exception
		 */
		public function pageLogo() {
			$blob = Blob::findById(App::get()->getSettings()->logoId);

			if (!$blob) {
				echo "Not found";
				http_response_code(404);
				exit();
			}

			$blob->output(true);
		}

		private $optimizerSearchDepthSet = false;

		/**
		 * Used for global search queries to optimize execution plan
		 * Queries could hang in 'Statistics' state when inputing a large string resulting in many joins.
		 *
		 * Also see: https://mariadb.com/resources/blog/setting-optimizer-search-depth-in-mysql/
		 */
		public function setOptimizerSearchDepth() {

			if($this->optimizerSearchDepthSet) {
				return;
			}

			try {
				go()->getDbConnection()->exec("SET SESSION optimizer_search_depth=4;");

			} catch(Exception $e) {
				ErrorHandler::log("Could not set 'optimizer_search_depth' on mysql. Please configure manually. " . $e->getMessage());
			}

			$this->optimizerSearchDepthSet = true;
		}
	}

}

namespace {

	use go\core\App;
	/**
	 * @return go\core\App
	 */
	function go() {
		return App::get();
	}
	
}

