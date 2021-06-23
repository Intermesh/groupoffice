<?php
/**
 * Group-Office
 *
 * Copyright Intermesh BV.
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @license AGPL/Proprietary http://www.group-office.com/LICENSE.TXT
 * @link http://www.group-office.com
 * @copyright Copyright Intermesh BV
 * @version $Id: Number.php 7962 2011-08-24 14:48:45Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package GO.base
 */

use go\core\cache\Apcu;
use go\core\jmap\State;
use go\core\ErrorHandler;

/**
 * The main Group-Office application class. This class only contains static
 * classes to access commonly used application data like the configuration or the logged in user.
 *
 * @license AGPL/Proprietary http://www.group-office.com/LICENSE.TXT
 * @link http://www.group-office.com
 * @copyright Copyright Intermesh BV
 * @version $Id: Number.php 7962 2011-08-24 14:48:45Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package GO

 */

class GO{

	/**
	 * If you set this to true then all acl's will allow all actions. Useful
	 * for maintenance scripts.
	 *
	 * @var boolean
	 */
	public static $ignoreAclPermissions=false;
	
	
	/**
	 * Use registerErrorLogCallback to register a custom function to log errors
	 * @var array 
	 */
	private static $_errorLogCallbacks=array();
	
	
	private static $_lastReportedError=false;


	private static $_view;
	
	/**
	 * Check if a class can be used.
	 * This function checks if the class exists and if the module for the class is installed (So the tables are available, when it's an active record)
	 * 
	 * @param StringHelper $className
	 * @return boolean
	 */
	public static function classExists($className){
		
		$parts = explode('\\', $className);
		
		if(!isset($parts[1])) {
			return false;
		}
		
		$module = strtolower($parts[1]);
		
			
		if (($module != 'base' && (!GO::modules()->isInstalled($module) || !GO::modules()->isAvailable($module))) || !class_exists($className)){
			return false;
		}else
		{
			return true;
		}
//			
//		if(class_exists($className)){
//			
//			$clsParts = explode('\\',$className);
//			
//			if($clsParts[1] == 'Base' || GO::modules()->isInstalled(strtolower($clsParts[1])))
//				return true;
//		}
//	
//		return false;
	}
	
	/**
	 * If you set this to true then all acl's will allow all actions. Useful
	 * for maintenance scripts.
	 *
	 * It returns the old value.
	 *
	 * @param StringHelper $ignore
	 * @return boolean Old value
	 */
	public static function setIgnoreAclPermissions($ignore=true){
		
		\GO::debug("setIgnoreAclPermissions(".var_export($ignore, true).')');
		
		$oldValue = \GO::$ignoreAclPermissions;
		\GO::$ignoreAclPermissions=$ignore;

		return $oldValue;
	}
	
	/**
	 * Set the max execution time only if the current max execution time is lower than the given value.
	 * 
	 * Note: this may be blocked by the suhosin PHP module
	 * 
	 * @param int $seconds
	 * @return boolean
	 */
	public static function setMaxExecutionTime($seconds){
		$max = ini_get("max_execution_time");
		if($max != 0 && ($seconds==0 || $seconds>$max)){
			return ini_set("max_execution_time", $seconds);
		}else
		{
			return true;
		}
	}
	
	/**
	 * Set the memory limit in MB if the given value is higher then the current limit.
	 * 
	 * Note: this may be blocked by the suhosin PHP module
	 * 
	 * @param int $mb
	 * @return boolean
	 */
	public static function setMemoryLimit($mb){
		$max = \GO\Base\Util\Number::configSizeToMB(ini_get("memory_limit"));

		if($max > 0 && $mb>$max){
			return ini_set("memory_limit", $mb.'M');
		}else
		{
			return true;
		}
	}

	/**
	 * Get a unique ID for Group-Office. It's mainly used for the javascript window id.
	 * @return type 
	 */
	public static function getId(){
		
		$serverName = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : "unknown";
		
		//added MD5 because IE doesn't like dots I suspect
		return md5(\GO::config()->id.'AT'.$serverName);
	}
	
	/**
	 * This \GO\Base\Model\ModelCache.php mechanism can consume a lot of memory 
	 * when running large batch scripts. That's why it can be disabled.
	 *
	 * @var boolean
	 */
	public static $disableModelCache=false;

	/**
	 * Commonly used classes indexed for faster autoloading
	 * 
	 * @var array 
	 */
	private static $_classes = array (
		'GO\Base\Observable' => 'go/base/Observable.php',
		'GO\Base\Session' => 'go/base/Session.php',
		'GO\Base\Config' => 'go/base/Config.php',
		'GO\Base\Model' => 'go/base/Model.php',
		'GO\Base\Db\ActiveRecord' => 'go/base/db/ActiveRecord.php',
		'GO\Base\Model_User' => 'go/base/model/User.php',
		'GO\Base\Cache\Interface' => 'go/base/cache/Interface.php',
		'GO\Base\Cache\Disk' => 'go/base/cache/Disk.php',
		'GO\Base\Cache\Apc' => 'go/base/cache/Apc.php',
		'GO\Base\Db\ActiveStatement' => 'go/base/db/ActiveStatement.php',
		'GO\Base\Util\StringHelper' => 'go/base/util/StringHelper.php',
		'GO\Base\Model\ModelCache' => 'go/base/model/ModelCache.php',
		'GO\Base\Router' => 'go/base/Router.php',
		'GO\Base\Controller\AbstractController' => 'go/base/controller/AbstractController.php',
		'GO\Base\Model_Module' => 'go/base/model/Module.php',
		'GO\Base\Controller\AbstractModelController' => 'go/base/controller/AbstractModelController.php',
		'GO\Base\Model\Acl' => 'go/base/model/Acl.php',
		'GO\Base\Model\AclUsersGroups' => 'go/base/model/AclUsersGroups.php',
		'GO\Base\Data\AbstractStore' => 'go/base/data/AbstractStore.php',
		'GO\Base\Data\Store' => 'go/base/data/Store.php',
		'GO\Base\Data\ColumnModel' => 'go/base/data/ColumnModel.php',
		'GO\Base\Module' => 'go/base/Module.php',
		'GO\Base\Model\AbstractUserDefaultModel' => 'go/base/model/AbstractUserDefaultModel.php',
		'GO\Base\Db\FindParams' => 'go/base/db/FindParams.php',
		'GO\Base\Db\FindCriteria' => 'go/base/db/FindCriteria.php',
		'GO\Base\Util\Date' => 'go/base/util/Date.php',
		'GO\Base\Data\Column' => 'go/base/data/Column.php',
		'GO\Base\Language' => 'go/base/Language.php',
		'GO\Base\Model_ModelCollection' => 'go/base/model/ModelCollection.php',
		'GO\Base\ModuleCollection' => 'go/base/ModuleCollection.php',
		'GO\Base\Model_Setting' => 'go/base/model/Setting.php',
	);

	private static $_config;
	private static $_session;
	private static $_modules;
	private static $_router;
	private static $_request;
	private static $_environment;
	/**
	 *
	 * @var PDO
	 */
	public static $db;

	private static $_modelCache;

	/**
	 * Gets the global database connection object.
	 *
	 * @return PDO Database connection object
	 */
	public static function getDbConnection(){
		if(!isset(self::$db)){
			self::setDbConnection();
		}
		return self::$db;
	}
	
	/**
	 * Close the database connection. Beware that all active PDO statements must be set to null too
	 * in the current scope.
	 * 
	 * Wierd things happen when using fsockopen. This test case leaves the conneciton open. When removing the fputs call it seems to work.
	 * 
	 * 			
	    \GO::session()->login('admin','admin');
			
			$settings = \GO\Sync\Model\Settings::model()->findForUser(\GO::user());
			$account = \GO\Email\Model\Account::model()->findByPk($settings->account_id);
			
			
			$handle = stream_socket_client("tcp://localhost:143");
			$login = 'A1 LOGIN "admin@intermesh.dev" "admin"'."\r\n";
			fputs($handle, $login);
			fclose($handle);
			$handle=null;			
			
			echo "Test\n";
			
			\GO::unsetDbConnection();
			sleep(10);
	 */
	public static function unsetDbConnection(){
		self::$db=null;
	}

	public static function setDbConnection($dbname=false, $dbuser=false, $dbpass=false, $dbhost=false, $dbport=false, $options=array()){
				
		self::$db=null;

		if($dbname===false)
			$dbname=\GO::config()->db_name;

		if($dbuser===false)
			$dbuser=\GO::config()->db_user;

		if($dbpass===false)
			$dbpass=\GO::config()->db_pass;

		if($dbhost===false)
			$dbhost=\GO::config()->db_host;
		
		if($dbport===false)
			$dbport=\GO::config()->db_port;
		
		
		self::$db = go()->getDbConnection()->getPDO();//new \GO\Base\Db\PDO("mysql:host=$dbhost;dbname=$dbname;port=$dbport", $dbuser, $dbpass, $options);
	}

	/**
	 * Clears the:
	 * 
	 * 1. \GO::config()->cachedir folder. This folder contains mainly cached javascripts.
	 * 2. \GO\Base\Model objects cached in memory for a single script run
	 * 3. The permanent cache stored in \GO::cache()
	 * 
	 */
	public static function clearCache(){
		$old = \GO\Base\Fs\File::setAllowDeletes(true);

		\GO::config()->getCacheFolder(false)->clearContents();
		\GO::cache()->flush();
		\GO\Base\Model::clearCache();

		\GO\Base\Fs\File::setAllowDeletes($old);
	}
	
	
	public static function viewName(){
		if(isset(self::session()->values['view'])){
			return self::session()->values['view'];
		}else
		{
			return self::config()->defaultView;
		}
	}

	/**
	 *
	 * @return \GO\Base\View\Extjs3 Returns the currently selected theme.
	 * @deprecated 
	 * 
	 */
	public static function view(){
		
		$class = "GO\Base\\View\\";
		
		if(isset(\GO::session()->values['view'])){
			$class .= \GO::session()->values['view'];
		}else
		{
			$class .= \GO::config()->defaultView;
		}
		
		if(!isset(self::$_view)){
			self::$_view = new $class();
		}
		return self::$_view;//isset(\GO::session()->values['view']) ? \GO::session()->values['view'] : \GO::config()->defaultView;
	}

	public static function setView($viewName){
		\GO::session()->values['view']=$viewName;
	}

	/**
	 * Get the logged in user
	 *
	 * @return \GO\Base\Model\User The logged in user model
	 */
	public static function user(){
		return self::session()->user();
	}

	/**
	 * Returns the router that routes requests to controller actions.
	 *
	 * @return \GO\Base\Router
	 */
	public static function router() {
		if (!isset(self::$_router)) {
			self::$_router=new \GO\Base\Router();
		}
		return self::$_router;
	}
	
	/**
	 * Returns the router that routes requests to controller actions.
	 *
	 * @return \GO\Base\Request
	 */
	public static function request() {
		if (!isset(self::$_request)) {
			self::$_request=new \GO\Base\Request();
		}
		return self::$_request;
	}
	
	/**
	 * Returns the environment object that has information about the current environment Group-Office is running on.
	 *
	 * @return \GO\Base\Environment
	 */
	public static function environment() {
		if (!isset(self::$_environment)) {
			self::$_environment=new \GO\Base\Environment();
		}
		return self::$_environment;
	}
	

	/**
	 * Returns a collection of Group-Office Module objects
	 *
	 * @return \GO\Base\ModuleCollection
	 *
	 */
	public static function modules() {
		if (!isset(self::$_modules)) {
//			if(\GO::user()){
//			
//			Caching caused more problems than benefits
//			
//				if(isset(\GO::session()->values['modulesObject']) && !isset($GLOBALS['GO_CONFIG'])){
//					self::$_modules=\GO::session()->values['modulesObject'];
//				}else{
//					self::$_modules=\GO::session()->values['modulesObject']=new \GO\Base\ModuleCollection();
//				}
//			}else
//			{
//				self::$_modules=new \GO\Base\ModuleCollection();
//			}
			
			self::$_modules=new \GO\Base\ModuleCollection();
		}
		return self::$_modules;
	}

	/**
	 * Models are cached within one script run
	 *
	 * @return \GO\Base\Model\ModelCache
	 */
	public static function modelCache() {
		if (!isset(self::$_modelCache)) {
			self::$_modelCache=new \GO\Base\Model\ModelCache();
		}
		return self::$_modelCache;
	}


	private static $_cache;
	
	/**
	 * Returns cache driver. Cached items will persist between connections and are
	 * available to all users. When debug is enabled a dummy cache driver is used
	 * that caches nothing.
	 * 
	 * @return \GO\Base\Cache\CacheInterface
	 */
	public static function cache(){

        if (!isset(self::$_cache)) {
            if(!GO::isInstalled()){
              self::$_cache=new \GO\Base\Cache\None();
						}else{
							if(!isset(GO::session()->values['cacheDriver'])){
								$cachePref = array(
										"\\GO\\Base\\Cache\\Apcu",
										"\\GO\\Base\\Cache\\Disk"
								);
								foreach($cachePref as $cacheDriver){
									$cache = new $cacheDriver;
									if($cache->supported()){

										GO::debug("Using $cacheDriver cache");
										GO::session()->values['cacheDriver'] = $cacheDriver;
										self::$_cache=$cache;
										break;
									}
								}
							}else
							{
								$cacheDriver = GO::session()->values['cacheDriver'];
								GO::debug("Using $cacheDriver cache");
								self::$_cache = new $cacheDriver;
							}
						}
        }
        return self::$_cache;
    }

	/**
	 *
	 * @return \GO\Base\Config
	 */
	public static function config() {
		if (!isset(self::$_config)) {

			// TODO: improve later, This will cache the same config file for a different installation if: same domain + same $token cookie
//			if(Apcu::isSupported() && ($token = State::getClientAccessToken())) {
//				$cacheKey = 'go_old_conf_' . $token;
//				self::$_config = apcu_fetch($cacheKey);
//				if(self::$_config && self::$_config->cacheTime > filemtime(self::$_config->configPath) && (!file_exists('/etc/groupoffice/globalconfig.inc.php') || self::$_config->cacheTime > filemtime('/etc/groupoffice/globalconfig.inc.php'))) {
//					return self::$_config;
//				}
//			}

			self::$_config = new \GO\Base\Config();
			
			if(isset($cacheKey)) {
				self::$_config->cacheTime = time();

				//apcu_store($cacheKey, self::$_config);
			}

			if(!empty(GO::session()->values['debug'])) {
				go()->getDebugger()->enable();
			}
		}
		return self::$_config;
	}

	/**
	 *
	 * @return \GO\Base\Session
	 */
	public static function session() {
		if (!isset(self::$_session)) {
			self::$_session = new \GO\Base\Session();
		}
		return self::$_session;
	}

	/**
	 * The automatic class loader for Group-Office.
	 *
	 * @param StringHelper $className
	 */
	public static function autoload($className) {
		
		//for namespaces
//		$className = str_replace('\\', '_', $className);
		
		//Sometimes there's a leading \ in the $className and sometimes not.
		//Might not be true for all php versions.		
		$className = ltrim($className, '\\');
			
		if(isset(self::$_classes[$className])){
			//don't use \GO::config()->root_path here because it might not be autoloaded yet causing an infite loop.
			require(dirname(dirname(__FILE__)) . '/'.self::$_classes[$className]);
		}else
		{
//			echo "Autoloading: ".$className."\n";
			
			$filePath = false;

			if(substr($className,0,7)=='GO\\Base'){
				$arr = explode('\\', $className);
				$file = array_pop($arr).'.php';

				$path = strtolower(implode('/', $arr));
				$location =$path.'/'.$file;
				$filePath = dirname(dirname(__FILE__)) . '/'.$location;
			} else if(substr($className,0,4)=='GOFS'){
						
				$arr = explode('\\', $className);
				
				array_shift($arr);
				
				$file = array_pop($arr).'.php';
				$path = strtolower(implode('/', $arr));
				$location =$path.'/'.$file;
				$filePath = \GO::config()->file_storage_path.'php/'.$location;	
				
			} else {
				//$orgClassName = $className;
				$forGO = substr($className,0,3)=='GO\\';

				if ($forGO)
				{
					$arr = explode('\\', $className);

					//remove GO_
					array_shift($arr);

					$module = strtolower(array_shift($arr));

					if($module!='core'){
						//$file = self::modules()->$module->path; //doesn't play nice with objects in the session and autoloading
						$file = 'modules/'.$module.'/';
					}else
					{
						$file = "";
					}
					for($i=0,$c=count($arr);$i<$c;$i++){
						if($i==$c-1){
							$file .= ucfirst($arr[$i]);
							$file .='.php';
						}else
						{
							$file .= strtolower($arr[$i]).'/';
						}

					}
					
					$filePath = \go\core\Environment::get()->getInstallFolder()->getPath() .'/' . $file;
					
				}
			}

			
			if(strpos($filePath, '..')!==false){
				echo "Invalid PHP file autoloaded!";
				throw new \Exception("Invalid PHP file autoloaded!");
			}

			if(!is_file($filePath)){
				//throw new \Exception('Class '.$orgClassName.' not found! ('.$file.')');
				return false;
			}else
			{
				require($filePath);
				return true;
			}
		}
	}
	
	private static $_scriptStartTime;

	private static $initialized=false;

	/**
	 * This function inititalizes Group-Office. It starts the session,registers
	 * error logging functions, class autoloading and set's PHP defaults.
	 */
	public static function init() {

		if(self::$initialized){
			throw new \Exception("Group-Office was already initialized");
		}
		self::$initialized=true;
		
		//register our custom error handler here
//		set_error_handler(array('GO','errorHandler'));
//		register_shutdown_function(array('GO','shutdown'));

//   	spl_autoload_register(array('GO', 'autoload'));
		
		//Start session here. Important that it's called before \GO::config().
		\GO::session();
		
		if (!empty(\GO::config()->debug_usernames)) {
			$usernames = explode(',',\GO::config()->debug_usernames);
			$currentUserModel = \GO::user();
			if (!empty($currentUserModel) && in_array($currentUserModel->username,$usernames))
				\GO::config()->debug=true;
		}

		
		if(!self::isInstalled()){
			return;
		}
		
		if(\GO::config()->debug){
			self::$_scriptStartTime = \GO\Base\Util\Date::getmicrotime();			
		}
		
		date_default_timezone_set(\GO::user() ? \GO::user()->timezone : \GO::config()->default_timezone);
		
		// for exec with ZIP and UTF8 chars	
		if(!setlocale(LC_CTYPE, go()->getSettings()->getLocale())) {
			if(!setlocale(LC_CTYPE, go()->getSettings()->resetLocale()))
			{
				ErrorHandler::log("Could not automatically determine locale");
			}
		}

		if(!empty(\GO::session()->values['debug']))
			\GO::config()->debug=true;
		
		if(\GO::config()->debug || \GO::config()->debug_log){
			$log = '['.date('Y-m-d H:i').'] INIT';
			
			if(isset($_SERVER['REQUEST_METHOD'])) {
				$log .= ' '.$_SERVER['REQUEST_METHOD'].' '.$_SERVER['REQUEST_URI'];
			}
			\GO::debug($log);
		}

		if(!defined('GO_LOADED')){ //check if old Group-Office.php was loaded

			//set umask to 0 so we can create new files with mask defined in \GO::config()->file_create_mode
			umask(0);
			
			//We use UTF8 by default.
			mb_internal_encoding("UTF-8");
		}

		//Every logged on user get's a personal temp dir.
		if (!empty(self::session()->values['user_id'])) {
			self::config()->tmpdir = self::config()->getTempFolder()->path().'/';
		}
		
		GO::config()->fireEvent('init');
		
	}
	
	/**
	 * undo magic quotes if magic_quotes_gpc is enabled. It should be disabled!
	 */
	private static function _undoMagicQuotes(){
		
		if (get_magic_quotes_gpc()) {

			function stripslashes_array($data) {
				if (is_array($data)) {
					foreach ($data as $key => $value) {
						$data[$key] = stripslashes_array($value);
					}
					return $data;
				} else {
					return stripslashes($data);
				}
			}

			$_REQUEST = stripslashes_array($_REQUEST);
			$_GET = stripslashes_array($_GET);
			$_POST = stripslashes_array($_POST);
			$_COOKIE = stripslashes_array($_COOKIE);
			if(isset($_FILES))
				$_FILES = stripslashes_array($_FILES);
		}
	}

	/**
	 * Called when PHP exits.
	 */
	public static function shutdown(){
		
		$error = error_get_last();		
		if($error){			
			//Log only fatal errors because other errors should have been logged by the normal error handler
			if($error['type']==E_ERROR || $error['type']==E_CORE_ERROR || $error['type']==E_COMPILE_ERROR || $error['type']==E_RECOVERABLE_ERROR)
				self::errorHandler($error['type'], $error['message'], $error['file'], $error['line']);
		}
		
		//clear temp files on the command line because we may run as root
		if(PHP_SAPI=='cli')
			\GO::session()->clearUserTempFiles(false);
		
		\GO::debugPageLoadTime('shutdown');
		\GO::debug("--------------------\n");
	}
	
	/**
	 * Register a callback function when an error occurs. It will be called with
	 * the error message as string
	 * 
	 * @param StringHelper|array $func
	 */
	public static function registerErrorLogCallback($func){
		self::$_errorLogCallbacks[]=$func;
	}

	/**
	 * Custom error handler that logs to our own error log
	 * 
	 * @param int $errno
	 * @param StringHelper $errstr
	 * @param StringHelper $errfile
	 * @param int $errline
	 * @return boolean
	 */
	public static function errorHandler($errno, $errstr, $errfile, $errline) {
		
		//prevent that the shutdown function will log this error again.
		if(self::$_lastReportedError == $errno.$errfile.$errline)
			return;
		
		self::$_lastReportedError = $errno.$errfile.$errline;
		
		//log only errors that are in error_reporting
		$error_reporting = (bool) ini_get('error_reporting');
		if (!($error_reporting & $errno)) return;
		
		$type="Unknown error";

		switch ($errno) {
			case E_ERROR:
			case E_USER_ERROR:
					$type='Fatal error';
					break;

			case E_WARNING:
			case E_USER_WARNING:
					$type = 'Warning';
					break;

			case E_NOTICE:
			case E_USER_NOTICE:
					$type='Notice';
					break;
		}		
		
		$errorMsg="[".@date("Ymd H:i:s")."] PHP $type: $errstr in $errfile on line $errline";
		
		$user = isset(\GO::session()->values['username']) ? \GO::session()->values['username'] : 'notloggedin';
		$agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown';
		$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
		
		$errorMsg .= "\nUser: ".$user." Agent: ".$agent." IP: ".$ip."\n";
		
		if(isset($_SERVER['QUERY_STRING']))
			$errorMsg .= "Query: ".$_SERVER['QUERY_STRING']."\n";
			
		
		$backtrace = debug_backtrace();
		array_shift($backtrace); //first item is this function which we don't have to see
		
		$errorMsg .= "Backtrace:\n";
		foreach($backtrace as $o){
			
			if(!isset($o['class']))
				$o['class']='global';
			
			if(!isset($o['function']))
				$o['function']='global';
			
			if(!isset($o['file']))
				$o['file']='unknown';
			
			if(!isset($o['line']))
				$o['line']='unknown';
			
			$errorMsg .= $o['class'].'::'.$o['function'].' in file '.$o['file'].' on line '.$o['line']."\n";			
		}
		$errorMsg .= "----------------";
		
		\GO::debug($errorMsg);
//		\GO::logError($errorMsg);	
		
		foreach(self::$_errorLogCallbacks as $callback){
			call_user_func($callback, $errorMsg);
		}
		
		if(\GO::config()->debug){
			if(error_reporting() !== 0) { // This will not print suppressed (@) error messages
				echo nl2br($errorMsg);
			}
		}
		
		/* Execute PHP internal error handler too */
		return false;
	}
	
	/**
	 * Writes a string to the Group-Office error log
	 * 
	 * @param StringHelper $errorMsg
	 */
	public static function logError($errorMsg){		
		$logDir = \GO::config()->file_storage_path . 'log';
		
		if(is_writable(\GO::config()->file_storage_path)){
			if(!is_dir($logDir))
				mkdir($logDir,0755, true);

			file_put_contents($logDir. '/error.log', $errorMsg . "\n", FILE_APPEND);
		}
	}


		/**
	 * Add a log entry to syslog if enabled in config.php
	 *
	 * @param	int $level The log level. See sys_log() of the PHP docs
	 * @param	StringHelper $message The log message
	 * @access public
	 * @return void
	 */
	public static function log($level, $message) {
//		if (self::config()->log) {
//			$messages = str_split($message, 500);
//			for ($i = 0; $i < count($messages); $i++) {
//				syslog($level, $messages[$i]);
//			}
//		}
	}

	public static function infolog($message) {

		if (!empty(self::config()->info_log)) {

			if (empty(\GO::session()->values["logdircheck"])) {
				$folder = new \GO\Base\Fs\Folder(dirname(self::config()->info_log));
				$folder->create();
				\GO::session()->values["logdircheck"] = true;
			}

			$msg = '[' . date('Y-m-d H:i:s') . ']';

			if (\GO::user()) {
				$msg .= '[' . self::user()->username . '] ';
			}

			$msg.= $message;

			@file_put_contents(self::config()->info_log, $msg . "\n", FILE_APPEND);
		}
	}

	/**
	 * Check if require exists
	 *
	 * @param string $fileName
	 *
	 * @return boolean
	 */
	public static function requireExists($fileName) {
		$paths = explode(PATH_SEPARATOR, get_include_path());
		foreach ($paths as $path) {
			if (file_exists($path . DIRECTORY_SEPARATOR . $fileName)) {
				return true;
			}
		}
		return false;
	}
	
	public static function debugPageLoadTime($id){
		 $time = \GO\Base\Util\Date::getmicrotime()-self::$_scriptStartTime;
		 
		 \GO::debug("Script running at [$id] for ".$time."ms");
	}
	/**
	 * Write's to a debug log.
	 *
	 * @param string $text log entry
	 */
	public static function debug($text, $config=false) {
		if(isset($_REQUEST['r'])&& $_REQUEST['r'] == 'core/debug')
		{
			return;
		}

		return go()->debug($text, 1);

	}

	
	public static function debugCalledFrom($limit=1){
		
		\GO::debug("--");
		$trace = debug_backtrace(); 
		for($i=0;$i<$limit;$i++){
			if(isset($trace[$i+1])){
				$call = $trace[$i+1];
				
				if(!isset($call["file"]))
								$call["file"]='unknown';
				if(!isset($call["function"]))
								$call["function"]='unknown';
				
				if(!isset($call["line"]))
								$call["line"]='unknown';
				
				\GO::debug("Function: ".$call["function"]." called in file ".$call["file"]." on line ".$call["line"]);
			}
		}
		\GO::debug("--");
	}
	
	private static $_language;

	/**
	 * Translates a language variable name into the local language
	 *
	 * @param String $name Name of the translation variable
	 * @param String $module Name of the module to find the translation
	 * @param String $package Only applies if module is set to 'base'
	 * @param boolean $found Pass by reference to determine if the language variable was found in the language file.
	 */
	public static function t($name, $module='core', $package='core', &$found=false){
		
		//for backwards compatibility
		if($module != 'core' && $package == 'core') {
			$package = 'legacy';
		}
		
		if($package == null) {
			$package = 'legacy';
		}

		return self::language()->getTranslation($name, $module, $package, $found);
	}

	/**
	 *
	 * @return \GO\Base\Language
	 */
	public static function language(){
		if(!isset(self::$_language)){
			self::$_language=new \GO\Base\Language();
		}
		return self::$_language;
	}


	public static function memdiff() {
		static $int = null;

		$current = memory_get_usage();

		if ($int === null) {
			$int = $current;
		} else {
			print ($current - $int) . "\n";
			$int = $current;
		}
	}


	/**
	 * Get the static model object
	 *
	 * @param String $modelName
	 * @return \GO\Base\Db\ActiveRecord
	 */
	public static function getModel($modelName){
		//$modelName::model() does not work on php 5.2! That's why we use this function.
		
		//backwards compat
		//$modelName = str_replace('_','\\', $modelName);
		
		if(!class_exists($modelName)){			

			$entityType = \go\core\orm\EntityType::findByName($modelName);
			
			if(!$entityType) {		
				throw new \Exception("Model class '$modelName' not found in \GO::getModel()");
			}
			
			$modelName = $entityType->getClassName();
			//return $modelName;
		} 
		

		
		if(!method_exists($modelName, 'model')) {
			return new $modelName(false);
		}
		return call_user_func(array($modelName, 'model'));
	}

	/**
	 * Create a URL for an outside application. The URL will open Group-Office and
	 * launch a function.
	 * 
	 * Controller external/index will be execured.
	 *
	 * @param StringHelper $module
	 * @param function $function
	 * @param array $params
	 * @return StringHelper
	 */
	public static function createExternalUrl($module, $function, $params,$toLoginDialog=false)
	{
		//$p = 'm='.urlencode($module).'&f='.urlencode($function).'&p='.urlencode(base64_encode(json_encode($params)));

		if(\GO::config()->debug){
			if(!preg_match('/[a-z]+/', $module))
				throw new \Exception('$module param may only contain a-z characters.');

			if(!preg_match('/[a-z]+/i', $function))
				throw new \Exception('$function param may only contain a-z characters.');
		}

		$p = array('m'=>$module,'f'=>$function, 'p'=>$params);

		$r =  ""; //$toLoginDialog ? '' : 'external/index';

		$url = \GO::config()->orig_full_url.'?r='.$r.'&f='.urlencode(base64_encode(json_encode($p)));
		return $url;
	}

	/**
	 * Set the URL to redirect to after login.
	 *
	 * This is handled by the main index.php
	 *
	 * @param StringHelper $url
	 */
	public static function setAfterLoginUrl($url){
		\GO::session()->values['after_login_url']=$url;
	}

	/**
	 * Generate a controller URL.
	 *
	 * @param StringHelper $path To controller. eg. addressbook/contact/submit
	 * @param array $params eg. array('id'=>1,'someVar'=>'someValue')
	 * @param boolean $relative Defaults to true. Set to false to return an absolute URL.
	 * @param boolean $htmlspecialchars Set to true to escape special html characters. eg. & becomes &amp.
	 * @return StringHelper
	 * @param boolean $appendSecurityToken add a SecurityToken to the url.
	 * @return string
	 */
	public static function url($path='', $params=array(), $relative=true, $htmlspecialchars=false, $appendSecurityToken=true){
		$url = $relative ? "" : \GO::config()->full_url;

		if(empty($path) && empty($params)){
			return $url;
		}

		if(empty($path)){
			$amp = 'index.php?';
		}else
		{
			$url .= 'index.php?r='.$path;

			$amp = $htmlspecialchars ? '&amp;' : '&';
		}

		if(!empty($params)){
			if(is_array($params)){
				foreach($params as $name=>$value){
					$url .= $amp.$name.'='.urlencode($value);

					$amp = $htmlspecialchars ? '&amp;' : '&';
				}
			}else
			{
				$url .= $amp.$params;
			}
		}

		$amp = $htmlspecialchars ? '&amp;' : '&';

		if($appendSecurityToken && isset(\GO::session()->values['security_token']))
			$url .= $amp.'security_token='.\GO::session()->values['security_token'];

		return $url;
	}

	/**
	 * Find classes in a folder
	 *
	 * @param StringHelper $path Relative from go/base
	 * @return \ReflectionClass[]
	 */
	public static function findClasses($subfolder){

		$classes=array();
		$folder = new \GO\Base\Fs\Folder(\GO::config()->root_path.'go/base/'.$subfolder);
		if($folder->exists()){

			$items = $folder->ls();

			foreach($items as $item){
				if($item instanceof \GO\Base\Fs\File){
					$className = 'GO\Base\\'.ucfirst($subfolder).'\\'.$item->nameWithoutExtension();
					$classes[] = new \ReflectionClass($className);
				}
			}
		}

		return $classes;
	}
	
	
	/**
	 * Find classes in a folder
	 *
	 * @param StringHelper $path Relative from $config['file_storage_path'].'php/'
	 * @return \ReflectionClass[]
	 */
	public static function findFsClasses($subfolder, $subClassOf=null){

		$classes=array();
		$folder = new \GO\Base\Fs\Folder(\GO::config()->file_storage_path.'php/'.$subfolder);
		if($folder->exists()){

			$items = $folder->ls();

			foreach($items as $item){
				if($item instanceof \GO\Base\Fs\File){
					$className = 'GOFS\\';
					
					$subFolders = explode('/', $subfolder);
					
					foreach($subFolders as $sf){
						$className .= ucfirst($sf).'\\';
					}
					
					$className .= $item->nameWithoutExtension();
					
					$rc = new \ReflectionClass($className);
					
					if($subClassOf==null || $rc->isSubclassOf($subClassOf))
						$classes[] = $rc;
				}
			}
		}

		return $classes;
	}
	
	
	/**
	 * Checks if Group-Office is already installed. 
	 * 
	 * @return boolean
	 */
	public static function isInstalled(){
		return !empty(\GO::config()->db_user);
	}
	
	
	
	private static $_scripts;
	
	/**
	 * 
	 * @return \GO\Base\Html\Scripts
	 */
	public static function scripts() {
		if(!isset(self::$_scripts)){
			self::$_scripts = new \GO\Base\Html\Scripts ();
		}
		return self::$_scripts;
	}
	

	
	/**
	 * Get the license file object
	 * 
	 * @return \GO\Base\Fs\File
	 */
//	pnew \GO\Base\Fs\File(GO::config()->root_path.'groupoffice-license.txt');

	/**
	 * Checks if the main cron job is running for the task scheduler
	 * 
	 * @return boolean
	 */
	public static function cronIsRunning(){
		$utc_str = gmdate("M d Y H:i:s", time());
		$utc = strtotime($utc_str);
		return \GO::config()->get_setting('cron_last_run') > $utc-300;
	}
	
	public static function p($name){
		return self::request()->post($name);
	}
	
	public static function g($name){
		return self::request()->get($name);
	}
}
