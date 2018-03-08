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
 * @package GO.modules.servermanager.model
 * @copyright Copyright Intermesh BV.
 * @author Merijn Schering mschering@intermesh.nl
 */
 
/**
 * The Installation model
 * TODO: build in cost that post to billing, build modules and user usage statistics
 *
 * @package GO.modules.servermanager.model
 * @property int $id
 * @property string $name Usually the domain name 
 * @property int $ctime
 * @property int $mtime
 * @property int $max_users
 * @property int $trial_days
 * @property int $lastlogin
 * @property string $comment
 * @property string $features
 * @property string $mail_domains
 * @property string $admin_email
 * @property string $admin_name
 * @property int $status_change_time
 * @property string $configPath
 * @property string $installPath
 * @property string $token
 * 
 * @property string $url
 * 
 * @property AutomaticInvoice automaticInvoice the automatic invoice object if exists
 * @property UsageHistory currentusage the latest created usagehistory object
 */


namespace GO\ServerManager\Model;
use Exception;
use GO;
use ReflectionClass;

class Installation extends \GO\Base\Db\ActiveRecord {

	private $_config; //the config array of this installation
	
	private $_total_logins;
	private $_count_users;
	private $_modules; //an array of InstallationModule objects
	private $_currentHistory; //UsageHistory object with latest usagedata
	private $_installationUsers; //Saves installation users loaded from external database will be saved in afterSave()
	
	const STATUS_TRIAL ='trial';
	const STATUS_ACTIVE ='ignore';
	/**
	 * Ignore existing database and folder structure when importing.
	 * 
	 * @var boolean 
	 */
	public $ignoreExistingForImport=false;

	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	protected function init() {
		
		$this->columns['name']['required']=true;
		$this->columns['name']['unique']=true;
		$this->columns['name']['regex']='/^[a-z0-9-_\.]*$/';
		$this->columns['max_users']['required']=true;
		
		$this->columns['lastlogin']['gotype']='unixtimestamp';
		$this->columns['ctime']['gotype']='unixtimestamp';
		
		return parent::init();
	}

	/**
	 * Returns the table name
	 */
	public function tableName() {
		return 'sm_installations';
	}
	
	public function aclField(){
		return 'acl_id';	
	}
	
	public function getDatabaseUsageText()
	{
		return \GO\Base\Util\Number::formatSize($this->database_usage);
	}
	public function getFileStorageUsageText()
	{
		return \GO\Base\Util\Number::formatSize($this->file_storage_usage);
	}
	public function getMailboxUsageText()
	{
		return \GO\Base\Util\Number::formatSize($this->mailbox_usage);
	}
	public function getTotalUsageText()
	{
		return \GO\Base\Util\Number::formatSize($this->database_usage + $this->file_storage_usage + $this->mailbox_usage);
	}
	public function getLastUsageCheckDate()
	{
		if($this->currentusage != null)
			return $this->currentusage->ctime;
		else
			return 'Never';
	}
	public function getTotalLogins()
	{
		if($this->currentusage != null)
			return $this->currentusage->total_logins;
		else
			return '-';
	}
	public function getCountUsers()
	{
		if($this->currentusage != null)
			return $this->currentusage->count_users;
		else
			return '-';
	}
	
	public function relations() {
		return array(
			'histories' => array('type' => self::HAS_MANY, 'model' => 'GO\ServerManager\Model\UsageHistory', 'field' => 'installation_id','delete'=>true),
			'currentusage'=> array('type' => self::HAS_ONE, 'model' => 'GO\ServerManager\Model\UsageHistory', 'field' => 'installation_id', 'findParams'=>\GO\Base\Db\FindParams::newInstance()->order('id','DESC')->limit(1)),
			'users' => array('type'=>self::HAS_MANY, 'model'=>'GO\ServerManager\Model\InstallationUser', 'field'=>'installation_id','delete'=>true, 'findParams'=>  \GO\Base\Db\FindParams::newInstance()->select()),
			'modules' => array('type'=>self::HAS_MANY, 'model'=>'GO\ServerManager\Model\InstallationModule', 'field'=>'installation_id','delete'=>true),
			'automaticInvoice'=>array('type'=>self::HAS_ONE, 'model'=>'GO\ServerManager\Model\AutomaticInvoice', 'field'=>'installation_id','delete'=>true),
		);
	}
	
	/**
	 * Get the DB name for this installation
	 * @return String the Db name based in the installation name
	 */
	protected function getDbName(){
		$name=strtolower(trim($this->name));
		$name=str_replace(array('.','-'),'_',$name);
		return $name;
	}
	
	/**
	 * Get the db username from db name cut of at 16 character if needed
	 * @return String the DB username based in the dbName
	 */
	protected function getDbUser(){
		return substr($this->dbName,0,16);
	}
	
	protected function getInstallPath(){
		if(empty($this->name))
			throw new Exception("Name empty in installation!");
		
		return '/home/govhosts/'.$this->name.'/';
	}
	
	/**
	 * read the list with allows modules from this config file of the installation
	 * and explore is to an array
	 * @return array $allowedModules an array with allowed module keys
	 */
	public function getAllowedModules()
	{
		$allowedModules = array();
		
		$c = $this->getConfigWithGlobals();
//		var_dump($c);
//		exit();
	
		if(!isset($c['allowed_modules']))
			$c['allowed_modules']="";

		$allowedModules = explode(',', $c['allowed_modules']);
		return $allowedModules;
	}
	
	/**
	 * Returns all available modules that can be activated
	 * Find InstallationModules that are saved in the database and merge them with the rest
	 * @return array(InstallationModule) 
	 */
	public function getModulesList()
	{
		$result = array();
		
		$allModules = \GO::modules()->getAvailableModules(true);
		foreach($allModules as $moduleClass)
		{
			$module = new $moduleClass;

			$installationModule = new InstallationModule();
			$installationModule->installation_id = $this->id;			
			$installationModule->installation = $this;
			$installationModule->name = $module->id();
			
			if(!$installationModule->isHidden()){
				$result[$installationModule->name] = $installationModule;
			}
		}
		
		$databaseModules = $this->modules->fetchAll();
		foreach($databaseModules as $dbmodule)
		{
			$result[$dbmodule->name] = $dbmodule;
		}
		
		return $result;
	}
	
	/**
	 * Set all database action for Installation modules
	 * All excisting modules should be changed
	 * All none excisting module that are set should be added
	 * 
	 * @param array $modules array of module name strings
	 */
	public function setAvailableModules($modules)
	{
		if(!isset($this->_modules)) //load modules from database if not done yet
		{
			$this->_modules = array();
			$stmt = $this->modules;
			while($module = $stmt->fetch())
				$this->_modules[$module->name] = $module;
		}
		
		//set posted modules to true
		foreach($this->_modules as &$module)
		{
			if(in_array($module->name, $modules))
				$module->enabled = true;
			else
				$module->enabled = false;
		}
		
		//add all new modules that are not yet in db
		foreach($modules as $modulename)
		{
			if(!isset($this->_modules[$modulename]))
			{
				$module = new InstallationModule();
				$module->enabled = true;
				$module->name = $modulename;
				$this->_modules[$modulename] = $module;
			}
		}
	}
	
	/**
	 * Get the path to the config file of an installation
	 * 
	 * @return StringHelper path to config file
	 */
	protected function getConfigPath(){		
		return !empty($this->name) ? '/etc/groupoffice/'.$this->name.'/config.php' : false;
	}
	
	/**
	 * The url of the installation
	 * @return StringHelper URL
	 */
	protected function getUrl(){
		$protocol = empty(\GO::config()->servermanager_ssl) ? 'http' : 'https';
		return $protocol.'://'.$this->name;
	}
	
	/**
	 * Get the content of config file of this installation
	 * If file not exisits return false;
	 * @return mixed $config array in config.php or false if not exists 
	 */
	public function getConfig(){
		if($this->_config!==null)
			return $this->_config; 
		else
		{
//			var_dump($this->configPath);
			if(!$this->configPath || !file_exists($this->configPath)){
				return array();
			} else {
				$config=array();
				require($this->configPath);
				$this->_config = $config;
				return $this->_config;
			}
		}
	}
	
	/**
	 * Get the configuration values merged with the globalconfig.inc.php values
	 * 
	 * @return array
	 */
	public function getConfigWithGlobals(){		
		$c = $this->config;
		if(file_exists('/etc/groupoffice/globalconfig.inc.php')){
			require('/etc/groupoffice/globalconfig.inc.php');
			if(isset($config))
				$c = $c ? array_merge($config, $c) : $config;
		}
		
		$configReflection = new ReflectionClass(GO::config());	
		$defaults = $configReflection->getDefaultProperties();
		
		return array_merge($defaults, $c);
	}
	
	/**
	 * Create a mysql dump of the installation database.
	 * 
	 * @param StringHelper $outputDir
	 * @param StringHelper $filename Optional filename. If omitted then $config['db_name'] will be used.
	 * @return boolean
	 * @throws Exception
	 */
	public function mysqldump($outputDir, $filename=null){
		$c = $this->getConfig();
		
		if(empty($c['db_name'])) {
			throw new \Exception("Could not dump database because DB name is empty!");
		}
		
		
		if(!isset($filename))
			$filename=$c['db_name'].".sql";
		
		$outputFile=rtrim($outputDir,'/')."/".$filename;
	
		$cmd = "mysqldump  --default-character-set=utf8 --force --opt --user=".$c['db_user']." --password=".$c['db_pass']." ".$c['db_name']." > \"$outputFile\"";
		\GO::debug($cmd);
		exec($cmd, $output,$retVar);
		
		if($retVar != 0)
			throw new Exception("Mysqldump error: ".implode("\n", $output));
		
		if(!file_exists($outputFile))
			throw new Exception("Could not create MySQL dump");
		
		return true;
	}
	
	/**
	 * Set a config.php variable for this installation
	 * 
	 * @param StringHelper $name
	 * @param mixed $value
	 * @return booleam
	 */
	public function setConfigVariable($name, $value){
		
		$this->getConfig();
		
		$file = new \GO\Base\Fs\File($this->configPath);
		
		$this->_config[$name]=$value;
		
		return \GO\Base\Util\ConfigEditor::save($file, $this->_config);
	}
	
	public function validate() {
		if(empty($this->dbName))
			$this->setValidationError('name','Name is invalid');
		
		if($this->isNew && !$this->ignoreExistingForImport){
			if(file_exists('/var/lib/mysql/'.$this->dbName) || file_exists('/etc/apache2/sites-enabled/'.$this->name) || is_dir($this->installPath))
				$this->setValidationError ('name', \GO::t('duplicateHost','servermanager'));
		}
		
		if (!$this->isNew && empty($this->modules)) {
			$this->setValidationError ('modules',"Please select the allowed modules");
		}
		
							
		return parent::validate();
	}
	
	public function defaultAttributes() {		
		$attr = parent::defaultAttributes();
		
		$attr['max_users'] = isset(\GO::config()->servermanager_max_users) ? \GO::config()->servermanager_max_users : 3;
		
		return $attr;
	}

	/**
	 * Before the installation gets deleted from the database we'll do some cleanup
	 * cleanup is done on the commandprompt with root access.
	 * It will delete the database and remove the symlinks to the installation
	 * @return boolean true
	 * @throws Exception if the executed command fails we'll throw an exeption
	 */
	protected function beforeDelete() {
		
		if(file_exists($this->configPath)){
			//throw new Exception("Error: Could not find installation configuration.");
		
			$cmd = 'sudo TERM=dumb '.\GO::config()->root_path.
							'groupofficecli.php -r=servermanager/installation/destroy'.
							' -c='.\GO::config()->get_config_file().
							' --id='.$this->id;

			exec($cmd, $output, $return_var);	

			if($return_var!=0){
				throw new Exception(implode("\n", $output));
			}
		}
		
		return parent::beforeDelete();
	}
	
	
//	protected function afterDbInsert() {
//		if(class_exists("GO\Professional\LicenseCheck"))
//		{
//			$lc = new \GO\Professional\LicenseCheck();
//			$this->token = $lc->generateToken($this);
//			
//			return true;
//		}
//	}
	
	/**
	 * check if the installation is expired
	 * @return boolean true of installation is more then 30 old and in trial status. 
	 */
	public function getIsExpired()
	{
		return ($this->status == Installation::STATUS_TRIAL && 
						$this->ctime<\GO\Base\Util\Date::date_add(time(),-40));

	}
	
	/**
	 * check is installation is suspended
	 * @return boolean true if the installation enabled value in config is set tot false
	 */
	public function getIsSuspended()
	{
		return isset($this->config['enabled']) && !$this->config['enabled'];
	}
	
	/**
	 * will write enabled=false to the config file
	 * @return boolean true is installation was suspended
	 */
	public function suspend()
	{
		if(!$this->isSuspended) //if not already suspended
		{
			$this->_config['enabled']=false;
			//saves config in before/afterSave()
			return true;
		} 
		else
			return false;
	}
	
	/**
	 * Will create and save a new UsageHistory object and load current usage data 
	 * This should be called once a day by actionReport()
	 */
	public function loadUsageData()
	{
		if($this->isNew)
			throw new Exception('Can not load usage data for a new installation');
		if(!$this->config)
			return false;
		
		if(!isset($this->config['file_storage_path']))
			return false;
		
		//if(isset($this->config['max_users']))
			//$this->max_users=$this->config['max_users'];
		
		$history = new UsageHistory();
		$history->installation_id = $this->id;
		
		//recalculated the size of the file folder
		$fsp = rtrim($this->config['file_storage_path'], '/');
		if(is_link($fsp)) {
						$fsp = realpath(readlink($fsp));
		}
		$folder = new \GO\Base\Fs\Folder($fsp);

		$this->file_storage_usage = $history->file_storage_usage = $folder->calculateSize();
		//Recalculate the size of the database and mailbox
		$this->database_usage = $history->database_usage = $this->_calculateDatabaseSize();
		$this->mailbox_usage = $history->mailbox_usage = $this->_calculateMailboxUsage();
		
		$this->quota = (int) $this->config['quota'];
		
		
		
		$this->_loadFromInstallationDatabase();
		
		$history->count_users = $this->_count_users;
		$history->total_logins = $this->_total_logins;
		
		$this->_currentHistory = $history;
		
//		var_dump($this->columns);
//		
//		var_dump($this->getModifiedAttributes());

		return true;
	}
	
	public function getHistoryAttributes()
	{
		if(!isset($this->_currentHistory))
			throw new Exception('no new usage data loaded');
		return $this->_currentHistory->getAttributes();
	}
	
//	private function _loadModuleData()
//	{
//		// conect to installation database
//		// reconnect to servermanager database
//		// set data from db
//
//		//load modules from installation database with ctime
//		$modules = \GO\Base\Model\Module::model()->find(\GO\Base\Db\FindParams::newInstance()->ignoreAcl());
//		foreach($modules as $module)
//		{
//			if(empty($this->first_installation_time))
//				$this->first_installation_time = $module->ctime;
//		}
//	}
	
//	/**
//	 * Returns an array with latest usage data
//	 * @return array usage data
//	 */
//	public function report(){
//		
//		$report = $this->getAttributes();
//		
//		$findParams = \GO\Base\Db\FindParams::newInstance()
//						->select('module_id, count(*) AS usercount')
//						->joinModel(array('model'=>'GO\ServerManager\Model\InstallationUser',  'localField'=>'user_id','tableAlias'=>'u'))
//						->group(array('module_id'))
//						->criteria(
//										\GO\Base\Db\FindCriteria::newInstance()
//										->addCondition('installation_id', $this->id,'=','u')										
//										);
//		
//		$stmt = InstallationUserModule::model()->find($findParams);
//		
//		$report['modules']=$stmt->fetchAll(PDO::FETCH_ASSOC);
//		
//		return $report;
//	}
	
	/**
	 * Load data from the installations database
	 * this will load the users used and the modules they have access to
	 * this will load the installed module with there ctime
	 * this will load last login, total users and total logins
	 * @return $installationUsers Array(InstallationUser)
	 * @throws Exception 
	 */
	
	private $_moduleUserCount = array();
	
	private function _loadFromInstallationDatabase()
	{
		if($this->isNew)
			throw new Exception('Can not load userdata for a new installation');
		
		//dummy object for SHOW COLUMN from servermanager database
		$installationUser = new InstallationUser();
		
		//prevent model caching and switch to installation database.
		\GO::$disableModelCache=true;
		try{
			\GO::setDbConnection(
					$this->config['db_name'], 
					$this->config['db_user'], 
					$this->config['db_pass'], 
					$this->config['db_host']
				);

			$adminUser = \GO\Base\Model\User::model()->findByPk(1); //find admin user
			$this->admin_email=$adminUser->email;
			$this->admin_name=$adminUser->name;

			$findParams = \GO\Base\Db\FindParams::newInstance()
							->select('count(*) as count, max(lastlogin) AS lastlogin, sum(logins) as total_logins');
			$findParams->getCriteria()->addCondition('enabled', true);
			$record = \GO\Base\Model\User::model()->findSingle($findParams);	//find lastlogin, usercount and total login					
			$this->lastlogin = intval($record->lastlogin);
			$this->_count_users = intval($record->count);		
			$this->_total_logins = intval($record->total_logins);
			
			$allowedModules = empty($this->config['allowed_modules']) ? array() : explode(',', $this->config['allowed_modules']);
			$this->_installationUsers=array();
			$stmt = \GO\Base\Model\User::model()->find(\GO\Base\Db\FindParams::newInstance()->ignoreAcl());
			
			while($user = $stmt->fetch()){
				$installationUser = new InstallationUser();
				$installationUser->installation_id=$this->id;
				$installationUser->setAttributesFromUser($user);
				
				$oldIgnore = \GO::setIgnoreAclPermissions(false);
				
				$modStmt = \GO\Base\Model\Module::model()->find(\GO\Base\Db\FindParams::newInstance()->permissionLevel(\GO\Base\Model\Acl::READ_PERMISSION, $user->id));
				while($module = $modStmt->fetch()){			
					if(empty($allowedModules) || in_array($module->id, $allowedModules)){
						$installationUser->addModule($module->id);
						
						if(!isset($this->_moduleUserCount[$module->id]))
							$this->_moduleUserCount[$module->id]=0;
						
						$this->_moduleUserCount[$module->id]++;
					}
				}
				$modStmt=null;
				
				
				\GO::setIgnoreAclPermissions($oldIgnore);

				$this->_installationUsers[]=$installationUser;
			}
			
			
			//unset stmt to clean up connections
			$stmt=null;
			//\GO::config()->save_setting('mailbox_usage', $this->mailbox_usage);
			//\GO::config()->save_setting('file_storage_usage', $this->file_storage_usage);
			//\GO::config()->save_setting('database_usage', $this->database_usage);
		}catch(Exception $e){
			\GO::setDbConnection();
			$stmt=null;
			$modStmt=null;
			if(isset($oldIgnore))
				\GO::setIgnoreAclPermissions($oldIgnore);
			throw new Exception($e->getMessage());
		}		
		
		\GO::config()->save_setting('mailbox_usage', $this->mailbox_usage);
		\GO::config()->save_setting('file_storage_usage', $this->file_storage_usage);
		\GO::config()->save_setting('database_usage', $this->database_usage);
		
		//reconnect to servermanager database
		\GO::setDbConnection();
		
		//force saving because the modules and users must be saved in aftersave
		$this->forceSave();

	}
	
	/**
	 * Run raw SQL query on installation database.
	 * 
	 * @param StringHelper $query
	 * @return boolean
	 * @throws Exception
	 */
	public function executeQuery($query){
		try{
			\GO::setDbConnection(
					$this->config['db_name'], 
					$this->config['db_user'], 
					$this->config['db_pass'], 
					$this->config['db_host']
				);
			
			$ret = \GO::getDbConnection()->query($query);
			
			//reconnect to servermanager database
			\GO::setDbConnection();
			
		}catch(Exception $e){
			\GO::setDbConnection();						
			throw new Exception($e->getMessage());
		}		
		
		return $ret;
		
	}
	
	/**
	 * calculate the size of the mailboxes if they are used.
	 * @return double the mailbox size in bytes?
	 */
	private function _calculateMailboxUsage(){
		$mailbox_usage=0;
		$this->mail_domains=isset($this->config['serverclient_domains']) ? $this->config['serverclient_domains'] : '';
		
		if(!empty(\GO::config()->serverclient_server_url) && !empty($this->config['serverclient_domains'])) {
			$c = new \GO\Serverclient\HttpClient();
//			$c->postfixLogin();

			$url = "postfixadmin/domain/getUsage";
			$response = $c->request(
					$url, 
					array('domains'=>json_encode(explode(",",$this->config['serverclient_domains'])))
			);

			$result = json_decode($response);
			
			if(!$result) {
				echo "Could not calculate get mailbox usage: ($url) ".$response."\n\n";				
				return 0;
			}
			$mailbox_usage=$result->usage*1024;			
		}

		return $mailbox_usage;
	}

	/**
	 * Calculate the database size of the database name in config file of installation
	 * @return double Database size in bytes
	 */
	private function _calculateDatabaseSize(){
		$stmt =\GO::getDbConnection()->query("SHOW TABLE STATUS FROM `".$this->config["db_name"]."`;");

		$database_usage=0;
		while($r=$stmt->fetch()){
			$database_usage+=$r['Data_length'];
			$database_usage+=$r['Index_length'];
		}
		
		return $database_usage;
	}
	
	
	private function _sendTrialtimeMails()
	{
		$module_stmt = $this->modules;
		foreach($module_stmt as $module)
		{
			if ($module->trialDaysLeft == 30 || $module->trialDaysLeft == 7)
				$module->sendTrialTimeLeftMail();
		}

		foreach($this->getTrialUsers() as $user)
		{
			if ($user->trialDaysLeft == 30 || $user->trialDaysLeft == 7)
				$user->sendTrialTimeLeftMail();
		}
	}
	
	
	/**
	 * Find all automatic email that should be send and send the ones that should be send today
	 * This function should be called once a day by a cronjob for every installation
	 * @param int $nowUnixTime time()?
	 * @return boolean $success true if all mails successfull send
	 */
	public function sendAutomaticEmails($nowUnixTime=false) {
		
//		$this->_sendTrialtimeMails();
		
		if (!is_int($nowUnixTime))
			$nowUnixTime = time();
		
		$autoEmailsStmt = AutomaticEmail::model()
			->find(
				\GO\Base\Db\FindParams::newInstance()
					->select('t.*')
					->criteria(
						\GO\Base\Db\FindCriteria::newInstance()
							->addCondition('active','1')
					)
			);
		
		$success = true;
		
		while ($autoEmailModel = $autoEmailsStmt->fetch()) {
			
			//Send the mail only if the creation time of the installation + the number of days is today.
			$dayStart = \GO\Base\Util\Date::date_add($nowUnixTime,-$autoEmailModel->days);
			$dayStart = \GO\Base\Util\Date::clear_time($dayStart);
			$dayEnd = \GO\Base\Util\Date::date_add($dayStart,1);			
			
//			echo $autoEmailModel->name.' '.date('c', $dayStart).' - '.date('c', $dayEnd)."\n";
			
//			echo "Installation time: ".date('c', $installationModel->ctime)."\n";
			
			if ($this->ctime>=$dayStart && $this->ctime<$dayEnd) {
				
				echo "Sending message ".$autoEmailModel->name." to ".$this->admin_email."\n";
				
				$message = \GO\Base\Mail\Message::newInstance()
					->loadMimeMessage($autoEmailModel->mime)
					->addTo($this->admin_email, $this->admin_name)
					->addBcc(\GO::config()->webmaster_email, \GO::config()->product_name)
					->setFrom(\GO::config()->webmaster_email, \GO::config()->product_name);

				$body = $this->_parseTags(
					$message->getBody(),
					array('installation'=>$this,'automaticemail'=>$autoEmailModel)
				);
				
				$message->setBody($body);

				$success = $success && \GO\Base\Mail\Mailer::newGoInstance()->send($message);
			}
		}
		return $success;
	}
	
	/**
	 * Parses string using tag combinations of the form:
	 * 'modelname:attributename' replaced by the value of $model->attribute
	 * @param String $string String to be parsed
	 * @param array $models Array of ActiveRecords. Keys will be the prefixes (the
	 * modelname part mentioned above).
	 * @return String Parsed string.
	 */
	private function _parseTags($string,array $models) {
		$attributes = array();
		foreach ($models as $tagPrefix => $model) {
			$attributes = array_merge($attributes,$this->_addPrefixToKeys($model->getAttributes(),$tagPrefix.':'));
		}
		$templateParser = new \GO\Base\Util\TemplateParser();
		return $templateParser->parse($string, $attributes);
	}
	
	/**
	 * Puts the prefix $tagPrefix before each key in the $array.
	 * @param array $array
	 * @param StringHelper $tagPrefix
	 * @return array
	 */
	private function _addPrefixToKeys(array $array,$tagPrefix) {
		$outputArray = array();
		foreach ($array as $k => $v) {
			$outputArray[$tagPrefix.$k] = $v;
		}
		return $outputArray;
	}
	
	public function getTrialUsers()
	{
		$trialUsers = array();
		$stmt = $this->users;
		foreach($stmt->fetchAll() as $user)
		{
			if($user->isTrial())
				$trialUsers[] = $user;
		}
		return $trialUsers;
	}
	public function getPayedUsers()
	{
		$payedUsers = array();
		$stmt = $this->users;
		foreach($stmt->fetchAll() as $user)
		{
			if(!$user->isTrial())
				$payedUsers[] = $user;
		}
		return $payedUsers;
	}
	
	/**
	 * Returns the amount that should be payed for the user account 
	 */
	public function getUserPrice()
	{
		$userprices = UserPrice::findAll();
		$highest_count = 0;
		$price = 0;
		foreach($userprices as $userprice)
		{
			if($userprice->max_users <= $this->getPayedUsers() && $userprice->max_users > $highest_count)
			{
				$highest_count = $userprice->max_users;
				$price = $userprice->price_per_month;
			}
		}
		return $price;
	}
	
	/**
	 * 
	 * @return UsageHistory
	 */
	public function getLastHistory(){
		$fp = \GO\Base\Db\FindParams::newInstance()
						->single()->order('id','DESC');
		return UsageHistory::model()->find($fp);
	}
	
	/**
	 * Save the config file of in the installation if it has been modified 
	 * Save module information is it has been set
	 * Save history object if it has been build
	 * @return boolean true if all got saved
	 */
	protected function afterSave($wasnew)
	{
		$success= true;
		
		//NOTE: write the config is done in afterSubmit() calling an controller action as root
		
		//save module information
		if(is_array($this->_modules))
		{
			foreach($this->_modules as $module)
			{
				$module->installation_id = $this->id;
				$success = $success && $module->save();
			}
		}
		
//		var_dump($this->_moduleUserCount);
		
		if(!$wasnew)
		{
			//save new user data of an installation
			if(is_array($this->_installationUsers))
			{
				//Drop all installation user for this installation and insert the new ones base on loaded data
				InstallationUser::model()->deleteByAttribute('installation_id', $this->id);
				foreach($this->_installationUsers as $user){
					$user->installation_id = $this->id;
					$success = $success && $user->save();
				}
			}
			
			foreach($this->_moduleUserCount as $module_id=>$usercount){
				$module = InstallationModule::model()->findSingleByAttributes(array(
						'name'=>$module_id,
						'installation_id'=>$this->id
				));
				
				if(!$module)
				{
					$module = new InstallationModule();
					$module->name=$module_id;
					$module->installation_id=$this->id;
					$module->enabled=true;
				}
				$module->usercount=$usercount;
				$module->save();
				
			}
			
			//save latest usage history if exists
			if($this->_currentHistory != null){
				
//				$insert = true;
//				$lastHistory = $this->getLastHistory();
//				if($lastHistory){
//					$lastAtt = $lastHistory->getAttributes('raw');
//					$newAtt = $this->_currentHistory->getAttributes('raw');
//					unset($lastAtt['id'],$lastAtt['ctime'],$lastAtt['mtime']);
//					unset($newAtt['id'],$newAtt['ctime'],$newAtt['mtime']);
//					$insert = $lastAtt != $newAtt;
//					
//					var_dump($lastAtt);
//					var_dump($newAtt);
//					
//				}
//				
				$success=$success && $this->_currentHistory->save();
			}
		}
		
		//save automatic invoicing setting
		if(isset($this->_autoInvoice))
		{
			$this->_autoInvoice->installation_id = $this->id;
			$success=$success && $this->_autoInvoice->save();
		}
		
		return $success;
	}
	
	private $_autoInvoice;
	public function setAutoInvoice(AutomaticInvoice $value)
	{
		$this->_autoInvoice = $value;
	}
	
}