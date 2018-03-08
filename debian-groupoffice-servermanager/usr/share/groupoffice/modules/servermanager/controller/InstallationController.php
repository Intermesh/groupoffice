<?php


namespace GO\Servermanager\Controller;

use GO;
use Exception;

class InstallationController extends \GO\Base\Controller\AbstractModelController {

	protected $model = 'GO\Servermanager\Model\Installation';
	
	protected function allowGuests() {
		return array('create','destroy', 'report','upgradeall','rename','fixquota','zpushfixstates');
	}
	
	protected function ignoreAclPermissions() {
		return array('create','destroy', 'report');
	}
	
	/**
	 * Loop through all config directories in /etc/groupoffice
	 * If it is not found in the database create a new one and call report() function
	 * @param array $params $_REQUEST object
	 */
	protected function actionImport($params){
		$folder = new \GO\Base\Fs\Folder('/etc/groupoffice');
		$items = $folder->ls();
		
		foreach($items as $item){
			if($item->isFolder() && $item->child('config.php')){
				
				if(is_dir('/home/govhosts/'.$item->name())){
					$installation = \GO\ServerManager\Model\Installation::model()->findSingleByAttribute('name', $item->name());
					if(!$installation){
						echo "Importing ".$item->name()."\n";
						$installation = new \GO\ServerManager\Model\Installation();
						$installation->ignoreExistingForImport=true;
						$installation->name=$item->name();
						$installation->save();
						$installation->loadUsageData();			

					}
				}
			}
		}
		echo "Done\n\n";
	}
	
	
	public function actionUndestroy($params){
		
		$this->checkRequiredParameters(array('name'), $params);
		
		$trashFolderGovhosts = new \GO\Base\Fs\Folder('/home/gotrash/govhosts/'.$params['name']);
		if(!$trashFolderGovhosts->exists())
			throw new Exception($trashFolderGovhosts->path().' does not exist');
		
		$trashFolderConfig = new \GO\Base\Fs\Folder('/home/gotrash/etc/groupoffice/'.$params['name']);
		if(!$trashFolderConfig->exists())
			throw new Exception($trashFolderConfig->path().' does not exist');
		
		echo "Restoring files...\n";
		$trashFolderGovhosts->move(new \GO\Base\Fs\Folder('/home/govhosts'));	
		$trashFolderConfig->move(new \GO\Base\Fs\Folder('/etc/groupoffice'));
		
		exec('chown www-data:www-data -R '.$trashFolderGovhosts->path());
		
		

		require_once('/etc/groupoffice/'.$params['name'].'/config.php');
		
		
		\GO::getDbConnection()->query("CREATE DATABASE IF NOT EXISTS `".$config['db_name']."`");	
		
		$this->_createDbUser($config);
		
		echo "Retoring database...\n";
		$cmd = 'mysql --user='.$config['db_user'].' --password='.$config['db_pass'].' '.$config['db_name'].' < /home/gotrash/mysqldump/'.$config['db_name'].'.sql';
		system($cmd);
		
		echo "Creating installation in servermanager...\n";
		$installation = new \GO\ServerManager\Model\Installation();
		$installation->ignoreExistingForImport=true;
		$installation->name=$params['name'];
		$installation->save();
		$installation->loadUsageData();					
		
		echo "Restore done!\n";
		
	}
	
	
	public function actionImportAllMigrated($params) {
		$this->checkRequiredParameters(array('source'), $params);
		
		$sourceFolder = new \GO\Base\Fs\Folder($params['source']);
		
		$children = $sourceFolder->ls();
		
		foreach($children as $child) {
			$this->actionImportMigrated(array('source' => $child->path(), 'name' => $child->name()));
		}
		
		echo "Done\n";			
	}
	
	/**
	 * /usr/share/groupoffice/groupofficecli.php -r=servermanager/installation/importMigrated --name=rpuk.groupoffice.co --source=/root/rpuk.groupoffice.net  -u=admin
	 * @param type $params
	 * @throws Exception
	 */
	public function actionImportMigrated($params){
		$this->requireCli();
		
		$this->checkRequiredParameters(array('source','name'), $params);
			
		echo "Restoring files...\n";
		
		$sourceFolder = new \GO\Base\Fs\Folder($params['source']);
		
		$rootFolder = new \GO\Base\Fs\Folder('/home/govhosts/'.$params['name']);
		if($rootFolder->exists()){
			throw new Exception("Root folder ".$rootFolder->path()." already exists!");
		}
		
		$rootFolder->create();
		$rootFolder->createLink(new \GO\Base\Fs\Folder('/usr/share/groupoffice'));
		
		$sourceFolder->move($rootFolder,'data');
		
		$configFile = $sourceFolder->child('config.php');
		
		if(!$configFile->exists())
			throw new Exception("config.php is missing");
		
		
		$configFolder = new \GO\Base\Fs\Folder('/etc/groupoffice/'.$params['name']);
		$configFolder->create();
		
		if($child = $configFolder->child('config.php')){
			throw new Exception($child->path().' already exists');
		}
		
		$configFile->move($configFolder);
		
		$mysqlDump = $sourceFolder->child('database.sql');
		if(!$mysqlDump->exists())
			throw new Exception("database.sql is missing");
		
		exec('chown www-data:www-data -R '.$sourceFolder->path());
		
		
		$installation = new \GO\ServerManager\Model\Installation();
		$installation->ignoreExistingForImport=true;
		$installation->name=$params['name'];

		require_once('/etc/groupoffice/'.$params['name'].'/config.php');
		
		$config['root_path']='/home/govhosts/'.$params['name'].'/groupoffice/';
		$config['tmpdir']='/tmp/'.$params['name'].'/';
		$config['file_storage_path']='/home/govhosts/'.$params['name'].'/data/';
		$config['id']=$params['name'];
		
		$config['db_name']=$installation->dbName;
		$config['db_user']=$installation->dbUser;
		$config['enabled']=true;
		
		\GO\Base\Util\ConfigEditor::save($configFile, $config);
		
		\GO::getDbConnection()->query("CREATE DATABASE IF NOT EXISTS `".$config['db_name']."`");	
		
		$this->_createDbUser($config);
		
		echo "Importing database...\n";
		$cmd = 'mysql --user='.$config['db_user'].' --password='.$config['db_pass'].' '.$config['db_name'].' < '.$mysqlDump->path();
		system($cmd, $ret);
		
		if($ret!=0)
			throw new Exception("Importing database failed!");
		
		echo "Creating installation in servermanager...\n";
		
		if(!$installation->save()){
			throw new Exception("Could not save installation: ".var_export($installation->getValidationErrors(), true));
		}
		$installation->loadUsageData();					
		
		echo "Restore done!\n";
		
		
	}
	
	/**
	 * Dumps database in ~/database.sql and copies config.php in ~/config.php 
	 * then it rsyncs this folder to the target machine.
	 * 
	 * /usr/share/groupoffice/groupofficecli.php -r=servermanager/installation/migrate --name=rpuk.groupoffice.net --target=ad.groupoffice.co: --disable -u=intermesh
	 * 
	 * @param type $params
	 * @throws Exception
	 */
	public function actionMigrate($params){
		
		$this->requireCli();
		
		$this->checkRequiredParameters(array('target','name'), $params);
		
		$installation = \GO\ServerManager\Model\Installation::model()->findSingleByAttribute('name',$params['name']);
		
		if($installation->name=='servermanager'){
			throw new Exception("You can't delete the servermanager installation");
		}
		
		if(!$installation)
			throw new Exception("Installation ".$params['name']." not found!");
		
		if(!$installation->validate())
			throw new Exception("Installation ".$params['name']." is invalid");
		
		$config = $installation->getConfig();
		
		if(!empty($params['disable'])) {
			echo "WARNING: Disabling installation\n";		
			$installation->setConfigVariable('enabled','0');
		}else
		{
			echo "WARNING: NOT disabling installation\n";		
		}
		
		$fsFolder = new \GO\Base\Fs\Folder($config['file_storage_path']);
		
		echo "Dumping database\n";		
		$installation->mysqldump($fsFolder->path(),'database.sql');
		echo "Done\n";
		
		
		$configFile = new \GO\Base\Fs\File($installation->configPath);
		$configFile->copy($fsFolder);
					

		$rsyncCommand = 'rsync -r -v -rltD ';
		
		if(isset($params['ssh-options']))
		{
			$rsyncCommand .= ' -e "'.$params['ssh-options'].'"';
		}
		
		echo "Sending data to ".$params['target']."\n";
		
		$rsyncCommand .= $config['file_storage_path'].' '.$params['target'].'/'.$params['name'];
		system($rsyncCommand, $status);
		
		if($status!=0)
			throw new Exception("Command exitted with failure status ".$status);
		
		echo "Done!\n";		
	}
	
	
	
	public function actionMigrateAll($params) {
		$this->checkRequiredParameters(array('target'), $params);
		$installations = GO\ServerManager\Model\Installation::model()->find();

		foreach ($installations as $installation) {
			if ($installation->getConfigWithGlobals()->disabled) {
				continue;
			}


			if ($installation->name == 'servermanager') {
				continue;
			}
			
			$params['name'] = $installation->name;

			$this->actionMigrate($params);
			
		}
		echo "Done\n";
		
	
		
	}
	
	/**
	 * Command line action that will delete the database and remove symlinks
	 * This will be executed by Installation->beforeDelete()
	 * @param array $params the $_REQUEST[]
	 * @throws Exception 
	 */
	public function actionDestroy($params){
		if(!$this->isCli())
			throw new Exception("Action servermanager/installation/delete may only be run by root on the command line");
		
		$installation = \GO\ServerManager\Model\Installation::model()->findByPk($params['id']);
		
		if($installation->name=='servermanager'){
			throw new Exception("You can't delete the servermanager installation");
		}
		
		if(!$installation)
			throw new Exception("Installation ID: ".$params['id']." not found!");
		
		if(!$installation->validate())
			throw new Exception("Installation ".$installation->name." is invalid");
		
		if(empty($installation->name))
			throw new Exception("Empty name!");
		
		$trashFolderGovhosts = new \GO\Base\Fs\Folder('/home/gotrash/govhosts');
		$trashFolderGovhosts->create();
		
		$trashFolderConfig = new \GO\Base\Fs\Folder('/home/gotrash/etc/groupoffice');
		$trashFolderConfig->create();
		
		$trashFolderMysql = new \GO\Base\Fs\Folder('/home/gotrash/mysqldump');
		$trashFolderMysql->create();
		
//		try{
			$installation->mysqldump('/home/gotrash/mysqldump');
//		}catch(Exception $e){
//			trigger_error("Failed to backup MySQL. Skipped drop of database ".$installation->dbName,E_USER_WARNING);
//		}
			
		$c = $installation->getConfig();
		
		
//		try{
			\GO::getDbConnection()->query("DROP USER '".$c['db_user']."'@'".\GO::config()->db_host."'");		
//		}catch(Exception $e){
//			trigger_error("Could not remove mysql user ".$c['db_user'],E_USER_WARNING);
//		}
//		try{
			\GO::getDbConnection()->query("DROP DATABASE `".$c['db_name']."`");
//		}catch(Exception $e){
//			trigger_error("Could not remove mysql database ".$c['db_name'],E_USER_WARNING);
//		}		
		
		$installationFolder = new \GO\Base\Fs\Folder($installation->installPath);
		$installationFolder->move($trashFolderGovhosts);
		
		$configFolder = new \GO\Base\Fs\Folder('/etc/groupoffice/'.$installation->name);
		$configFolder->move($trashFolderConfig);
		
	}	
	
	private function _getConfigFromFile($path){
		require($path);
		return $config;
	}
	
	
	public function actionRename($params){
	
		if(!$this->isCli())
			throw new Exception("Action servermanager/installation/create may only be run by root on the command line");
		
		$this->checkRequiredParameters(array("oldname","newname"), $params);		
		
		$installation = \GO\ServerManager\Model\Installation::model()->findSingleByAttribute('name', $params['oldname']);
		
		if(!$installation)
			throw new \GO\Base\Exception\NotFound();
		
		$configFolder = new \GO\Base\Fs\Folder(dirname($installation->configPath));
		$installationFolder = new \GO\Base\Fs\Folder($installation->installPath);
		
		$oldDbName = $installation->dbName;
		$oldDbUser = $installation->dbUser;
		
		require($installation->configPath);
		
		
		$installation->name = $params["newname"];
		$installation->save();		
		
		$newInstallPath = $installationFolder->parent()->path()."/".$installation->name."/";
		
		$config['id']=$installation->name;	
		$config['file_storage_path']=$newInstallPath."data/";
		$config['root_path']=$newInstallPath."groupoffice/";
		system('mv "'.$configFolder->path().'" "'.$configFolder->parent()->path()."/".$installation->name.'"');
		
		//$configFolder->move(new \GO\Base\Fs\Folder("/etc/groupoffice"), $installation->name);
		
		\GO\Base\Util\ConfigEditor::save(new \GO\Base\Fs\File($installation->configPath), $config);
		
		//$installationFolder->move(new \GO\Base\Fs\Folder("/home/govhosts"), $installation->name);
		
		system('mv "'.$installationFolder->path().'" "'.$newInstallPath.'"');
		
		echo "Installation ".$params['oldname']." was renamed to ".$params['newname']."\n";
	}
	
	/**
	 * Create database, dbuser, symlinks and configfile
	 * Only run this action as root on the commandline
	 * 
	 * @param StringHelper $params[name] name is installation to create
	 * @param StringHelper $params[tmp_config] path to temp config file
	 * @param StringHelper $params[adminpassword] ??
	 * @throws Exception if not called from CLI
	 * @throws Exception if installation is not found
	 */
	public function actionCreate($params){
		if(!$this->isCli())
			throw new Exception("Action servermanager/installation/create may only be run by root on the command line");
		
		//todo check if we are root
		
		$installation = \GO\ServerManager\Model\Installation::model()->findSingleByAttribute('name', $params['name']);
		
		if(!$installation)
			throw new Exception("Installation ".$params['name']." not found!");
		
		$configFile = new \GO\Base\Fs\File($installation->configPath);
		
		//if config file already exists then include it so we will keep the manually added config values.
		if($configFile->exists())
			$existingConfig = $installation->config;
		else
			$existingConfig = array();
		
		//create config file
		require($params['tmp_config']);
		$newConfig = $config;
		unlink($params['tmp_config']);
		$existingConfig=array_merge($existingConfig, $newConfig);		

		$this->_createFolderStructure($existingConfig, $installation);
		
		\GO\Base\Util\ConfigEditor::save($configFile, $existingConfig);
		$configFile->chown('root');
		$configFile->chgrp('www-data');
		$configFile->chmod(0660);		
		
		$this->_createDatabase($params,$installation, $existingConfig);		
		
		$configFile->chmod(0644);		
	}
	
	private function _createDatabaseContent($params, $installation, $config){
		$cmd = 'sudo -u www-data php '.\GO::config()->root_path.'install/autoinstall.php'.
						' -c='.$installation->configPath.
						' --adminusername=admin'.
						' --adminpassword="'.$params['adminpassword'].'"'.
						' --adminemail="'.$config['webmaster_email'].'" 2>&1';
		
		\GO::debug($cmd);
		
		exec($cmd, $output, $return_var);

		if($return_var!=0)
			throw new Exception(implode("\n", $output));
		
		// Create the groups for this installation that are given in the config file.
		if(!empty(\GO::config()->servermanager_auto_groups)){
			\GO::setDbConnection(
							$config['db_name'], 
							$config['db_user'], 
							$config['db_pass'], 
							$config['db_host']
							);
			
			foreach(\GO::config()->servermanager_auto_groups as $group=>$permissions){
				$this->_createGroup($group, $permissions);
			}

			\GO::setDbConnection();
		}
	}
	
	/**
	 * Create the new group for this installation
	 * 
	 * Example array for in the config file.
	 * 
	 * $config['servermanager_auto_groups']=array(
	 *	'Group1'=>array(
	 *		'modules_read'=>'addressbook,calendar',
	 *		'modules_manage'=>'tickets'
	 *	),
	 *	'Group2'=>array(
	 *		'modules_read'=>'addressbook',
	 *		'modules_manage'=>'tickets,calendar'
	 *	)
	 * );
	 * 
	 * 
	 * @param StringHelper $name The name of the new group
	 * @param array $permissions Array of permission options for the group
	 */
	private function _createGroup($name,$permissions){
		
		$group = \GO\Base\Model\Group::model()->findSingleByAttribute('name', $name);
		
		if(!$group){
			$group = new \GO\Base\Model\Group();
			$group->name = $name;
			$group->save();
		}

		if($group){
			if(!empty($permissions['modules_read']))
				$this->_setGroupRights($group,$permissions['modules_read'], 'read');
			
			if(!empty($permissions['modules_manage']))
				$this->_setGroupRights($group,$permissions['modules_manage'],'manage');
		}
	}
	
	/**
	 * Set the rights for the created group.
	 * 
	 * @param \GO\Base\Model\Group $group The group to set the rights for.
	 * @param StringHelper $modules A comma separated string with the module names.
	 * @param StringHelper $type Permission type, possible values: 'read','manage' defaults to 'read'.
	 */
	private function _setGroupRights($group,$modules,$type='read'){
		$modules =  explode(',',$modules);
		
		$permission = \GO\Base\Model\Acl::READ_PERMISSION;
		
		switch($type){
			case 'manage':		
				$permission = \GO\Base\Model\Acl::MANAGE_PERMISSION;
			break;
			case 'read':
			default:
				$permission = \GO\Base\Model\Acl::READ_PERMISSION;
		}
		
		foreach($modules as $moduleName){
			if(\GO::modules()->$moduleName)
				\GO::modules()->$moduleName->acl->addGroup($group->id,$permission);
		}
	}
	
	private function _createFolderStructure($config, $installation){
		
		$dataFolder = new \GO\Base\Fs\Folder($installation->installPath.'data');
		$dataFolder->create(0755);
		$dataFolder->chown('www-data');
		$dataFolder->chgrp('www-data');
		
		$log = new \GO\Base\Fs\Folder($installation->installPath.'data/log');
		$log->create(0755);
		$log->chown('www-data');
		$log->chgrp('www-data');
				
		$tmpFolder = new \GO\Base\Fs\Folder('/tmp/'.$installation->name);
		$tmpFolder->create(0777);
		$tmpFolder->chown('www-data');
		$tmpFolder->chgrp('www-data');
		
		$configFolder = new \GO\Base\Fs\Folder('/etc/groupoffice/'.$installation->name);
		$configFolder->create(0755);
		
		if(!file_exists($installation->installPath.'groupoffice'))
			symlink(\GO::config()->root_path, $installation->installPath.'groupoffice');
	}
	
	private function _createDatabase($params, $installation, $config){
		
		try{			
			if(!\GO\Base\Db\Utils::databaseExists($config['db_name'])){
			
				\GO::getDbConnection()->query("CREATE DATABASE IF NOT EXISTS `".$config['db_name']."`");				
				
				$this->_createDbUser($config);

				$this->_createDatabaseContent($params, $installation, $config);
			}else
			{
				if(!empty($params['adminpassword'])){
					\GO::setDbConnection($config["db_name"], $config["db_user"], $config["db_pass"]);					
					
					$admin = \GO\Base\Model\User::model()->findByPk(1, false,true,true);
					$admin->password=$params['adminpassword'];
					$admin->save();	
					
					\GO::setDbConnection();
				}
			}
		}catch(Exception $e){
			
			//$installation->delete();
			
			throw new Exception("Could not create database. Did you grant permissions to create databases to the main database user by running: \n\n".
							"REVOKE ALL PRIVILEGES ON * . * FROM 'groupoffice-com'@'localhost';\n".
							"GRANT ALL PRIVILEGES ON * . * TO 'groupoffice-com'@'localhost' WITH GRANT OPTION MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;\n\n". $e->getMessage());
		}
	}
	
	private function _createDbUser($config){
		$sql = "GRANT ALL PRIVILEGES ON `".$config['db_name']."`.*	TO ".
								"'".$config['db_user']."'@'".$config['db_host']."' ".
								"IDENTIFIED BY '".$config['db_pass']."' WITH GRANT OPTION";			

		\GO::getDbConnection()->query($sql);
		\GO::getDbConnection()->query('FLUSH PRIVILEGES');		
	}
	
	/**
	 * Load data from config file to response array to fill form fields
	 * @param type $response
	 * @param type $model
	 * @param type $params
	 * @return type 
	 */
	protected function afterLoad(&$response, &$model, &$params) {
		
		if(file_exists($model->configPath))
		{
			$c = $model->getConfigWithGlobals();

			$response['data']['enabled']=empty($c['id']) || !empty($c['enabled']);
			$response['data']['max_users'] = \GO\Base\Util\Number::unlocalize($c['max_users']);

			$response['data']['webmaster_email'] = $c['webmaster_email'];
			$response['data']['title'] = $c['title'];
			$response['data']['default_country'] = $c['default_country'];
			$response['data']['language'] = $c['language'];
			$response['data']['default_timezone'] = $c['default_timezone'];
			$response['data']['default_currency'] = $c['default_currency'];
			$response['data']['default_time_format'] = $c['default_time_format'];
			$response['data']['default_date_format'] = $c['default_date_format'];
			$response['data']['default_date_separator'] = $c['default_date_separator'];
			$response['data']['default_thousands_separator'] = $c['default_thousands_separator'];
			$response['data']['theme'] = $c['theme'];

			$response['data']['default_decimal_separator'] = $c['default_decimal_separator'];
			$response['data']['default_first_weekday'] = $c['default_first_weekday'];


			$response['data']['allow_themes'] = !empty($c['allow_themes']);
			$response['data']['allow_password_change'] = !empty($c['allow_password_change']);

			$response['data']['quota'] = \GO\Base\Util\Number::localize($c['quota']/1024/1024/1024); //in gigabytes
			$response['data']['restrict_smtp_hosts'] = $c['restrict_smtp_hosts'];
			$response['data']['serverclient_domains'] = isset($c['serverclient_domains']) ? $c['serverclient_domains'] : '';
		}
		
//		if($model->automaticInvoice == null)
//			$model->automaticInvoice = new \GO\ServerManager\Model\AutomaticInvoice();
//		
//		$response['data'] = array_merge($response['data'], $model->automaticInvoice->getAttributes());
		
		return parent::afterLoad($response, $model, $params);
	}

	/**
	 * Create a temparory config file and call the create action as root
	 */
	protected function afterSubmit(&$response, &$model, &$params, $modifiedAttributes) {
		
		$tmpConfigFile = $this->_createConfig($params, $model);
				
		$cmd = 'sudo TERM=dumb '.\GO::config()->root_path.
						'groupofficecli.php -q -r=servermanager/installation/create'.
						' -c='.\GO::config()->get_config_file().
						' --tmp_config='.$tmpConfigFile->path().
						' --name='.$model->name.	
						' --adminpassword='.$params['admin_password1'].' 2>&1';
		//throw new Exception($cmd);
		exec($cmd, $output, $return_var);		

		if($return_var!=0){
			throw new Exception(implode('<br />', $output));
		}
		
		return parent::afterSubmit($response, $model, $params, $modifiedAttributes);
	}
	
	
	protected function beforeSubmit(&$response, &$model, &$params) {
		
		if(!empty($params['admin_password1']))
		{
			if($params['admin_password1']!=$params['admin_password2'])
			{
				throw new Exception('The passwords didn\'t match. Please try again');
			}
		}
		
//		if(isset($params['enable_invoicing']) && $params['enable_invoicing']=='on')
//		{
//			if($model->automaticInvoice != null)
//				$autoInvoice = $model->automaticInvoice;
//			else
//				$autoInvoice = new \GO\ServerManager\Model\AutomaticInvoice();
//			
//			$autoInvoice->setAttributes($params);
//			
//			$autoInvoice->enable_invoicing = true;
//			$model->setAutoInvoice($autoInvoice);
//		}
//		elseif($model->automaticInvoice != null) //turn off if exists
//		{
//			$autoInvoice = $model->automaticInvoice;
//			$autoInvoice->enable_invoicing = false;
//			$model->setAutoInvoice($autoInvoice);
//		}
		
		if(isset($params['modules'])){
			$model->setAvailableModules( json_decode($params['modules'], true) );
			//unset($params['modules']);
		}
		
		return parent::beforeSubmit($response, $model, $params);
	}

	private function _createConfig($params, $model) {
		if (isset($params['modules'])) {
			$modules = json_decode($params['modules']);
			$modules[]='serverclient';
			$modules[]='users';
			$modules[]='groups';
			$modules[]='modules';
			
			$config['allowed_modules'] = implode(',', $modules);
		}
		
		
		if(!file_exists($model->configPath)){
			//only create these values on new config files.
			
			//for testing		
			$config['debug']=\GO::config()->debug;

			$config['id']=$model->dbName;
			$config['db_name']=$model->dbName;
			$config['db_user']=$model->dbUser;
			$config['db_host']=\GO::config()->db_host;
			$config['db_pass']= \GO\Base\Util\StringHelper::randomPassword(8,'a-z,A-Z,1-9');
			$config['host']='/';
			$config['root_path']=$model->installPath.'groupoffice/';
			$config['tmpdir']='/tmp/'.$model->name.'/';
			$config['file_storage_path']=$model->installPath.'data/';
		}
				
		$config['enabled']=empty($params['id']) || !empty($params['enabled']);
		$config['max_users'] = \GO\Base\Util\Number::unlocalize($params['max_users']);

		$config['webmaster_email'] = $params['webmaster_email'];
		$config['title'] = $params['title'];
		$config['default_country'] = $params['default_country'];
		$config['language'] = $params['language'];
		$config['default_timezone'] = $params['default_timezone'];
		$config['default_currency'] = $params['default_currency'];
		$config['default_time_format'] = $params['default_time_format'];
		$config['default_date_format'] = $params['default_date_format'];
		$config['default_date_separator'] = $params['default_date_separator'];
		$config['default_thousands_separator'] = $params['default_thousands_separator'];
		$config['theme'] = $params['theme'];

		$config['default_decimal_separator'] = $params['default_decimal_separator'];
		$config['default_first_weekday'] = $params['default_first_weekday'];


		$config['allow_themes'] = !empty($params['allow_themes']);
		$config['allow_password_change'] = !empty($params['allow_password_change']);

		$config['quota'] = \GO\Base\Util\Number::unlocalize($params['quota'])*1024*1024*1024;
		$config['restrict_smtp_hosts'] = $params['restrict_smtp_hosts'];
		$config['serverclient_domains'] = $params['serverclient_domains'];
		
		//throw new Exception(var_export($config, true));
				

		if (intval($config['max_users']) < 1)
			throw new Exception('You must set a maximum number of users');

		if (!\GO\Base\Util\StringHelper::validate_email($config['webmaster_email']))
			throw new Exception(\GO::t("Please enter a valid e-mail address", "servermanager"));
		
		$tmpFile = \GO\Base\Fs\File::tempFile('', 'php');
		
		if(!\GO\Base\Util\ConfigEditor::save($tmpFile, $config)){
			throw new Exception("Failed to save config file!");
		}
		
		return $tmpFile;
	}


	public function formatStoreRecord($record, $model, $store) {
		
		$record['total_usage']= $model->totalUsageText;
		$record['file_storage_usage']= $model->fileStorageUsageText;
		
		$record['database_usage']= $model->databaseUsageText;
		$record['mailbox_usage']= $model->mailboxUsageText;
		$record['count_users'] = $model->countUsers;
		$record['total_logins'] = $model->totalLogins;
		//$record['quota']=\GO\Base\Util\Number::formatSize($model->quota*1024);
		
		if(file_exists($model->configPath))
		{
			$c = $model->getConfigWithGlobals();
			if(isset($c['title'])){
				$record['quota']=\GO\Base\Util\Number::formatSize($c['quota']);
				$record['enabled']=isset($c['enabled']) ? $c['enabled'] : true;
				$record['title']=$c['title'];
				$record['webmaster_email']=$c['webmaster_email'];
				$record['max_users']=isset($c['max_users']) ? $c['max_users'] : 0;
				$record['serverclient_domains']=isset($c['serverclient_domains']) ? $c['serverclient_domains'] : '';
			}
		}
		
		return parent::formatStoreRecord($record, $model, $store);
	}
	/*
	private function _countModuleUsers($installation_id, $module_id){
		
		$findParams = \GO\Base\Db\FindParams::newInstance()
						->select('count(*) AS usercount')
						->joinModel(array('model'=>'GO\ServerManager\Model\InstallationUser', 'localField'=>'user_id','tableAlias'=>'u'))
						->single()
						->debugSql()
						->criteria(
									\GO\Base\Db\FindCriteria::newInstance()
									->addCondition('installation_id', $installation_id,'=','u')
									->addCondition('module_id', $module_id)
								);
		
		$record = \GO\ServerManager\Model\InstallationUserModule::model()->find($findParams);
		return $record->usercount;
	}
	*/
	
	/**
	 * Returns a list with all modules availible for installation
	 * Get executed when clicking Modules tab in installationdialog
	 * @param array $params the $_REQUEST
	 * @return StringHelper JSON encode array for extjs datagrid
	 */
	protected function actionModules($params){

		$installation=null;
		if(isset($params['installation_id']))
			$installation = \GO\ServerManager\Model\Installation::model()->findByPk($params['installation_id']);
		if($installation == null)
			$installation = new \GO\ServerManager\Model\Installation();

		$moduleList = $installation->getModulesList();

		$results=array();
		foreach($moduleList as $module)
			$results[\GO::t ("name", $module->name)] = $module->toArray();
		
		ksort($results); //Sort modules by name
		
		$response['results']=array_values($results);
		
		$response['total']=count($response['results']);
		
		return $response;		
	}
	
	/**
	 * Run maintenance/upgrade and clear cache for every installation
	 * @throws Exception when not run from commandline
	 */
	protected function actionUpgradeAll($params){
		
		if(!$this->isCli())
			throw new Exception("This action may only be ran on the command line.");
		
		$stmt = \GO\Servermanager\Model\Installation::model()->find();
		while($installation = $stmt->fetch()){
			
			echo "Upgrading ".$installation->name."\n";
			
			if(!$installation->config){
				echo "\nERROR: Config file ".$installation->configPath." not found\n\n";
				continue;
			}
			
			$cmd = 'sudo -u www-data '.\GO::config()->root_path.'groupofficecli.php -q -r=maintenance/upgrade -c="'.$installation->configPath.'"';
			
			system($cmd);			
			echo "Done\n\n";
			
		}
	}	
	
	/**
	 * Run maintenance/upgrade and clear cache for every installation
	 * @throws Exception when not run from commandline
	 */
	protected function actionFixQuota($params){
		
		if(!$this->isCli())
			throw new Exception("This action may only be ran on the command line.");
		
		$stmt = \GO\Servermanager\Model\Installation::model()->find();
		while($installation = $stmt->fetch()){
			
			echo "Processing ".$installation->name."\n";
			
			if(!$installation->config){
				echo "\nERROR: Config file ".$installation->configPath." not found\n\n";
				continue;
			}
			
			if(isset($installation->config['quota']))
				$installation->setConfigVariable('quota', $installation->config['quota']*1024);
			
			echo "Done\n\n";
			
		}
	}	
	
	protected function actionRunOnAll($params){
		
		if(!$this->isCli())
			throw new Exception("This action may only be ran on the command line.");
		
		$stmt = \GO\Servermanager\Model\Installation::model()->find();
		while($installation = $stmt->fetch()){
			
			echo "Running route '".$params["route"]."' on '".$installation->name."'\n";
			
			if(!file_exists($installation->configPath)){
				echo "\nERROR: Config file ".$installation->configPath." not found\n\n";
				continue;
			}
			
			require($installation->configPath);
			
			$cmd = \GO::config()->root_path.'groupofficecli.php -q -r="'.$params["route"].'" -c="'.$installation->configPath.'"';
			
			system($cmd);
						
			echo "Done\n\n";
			
		}
	}	
	
	
	protected function actionZpushFixStates(){
		
		if(!$this->isCli())
			throw new Exception("This action may only be ran on the command line.");
		
		$stmt = \GO\Servermanager\Model\Installation::model()->find();
		while($installation = $stmt->fetch()){
			
			echo "Running route 'z-push-admin.php fixstates' on '".$installation->name."'\n";		
			
			$cmd = 'php '.$installation->installPath.'groupoffice/modules/sync/z-push/z-push-admin.php -a fixstates';			
			system($cmd);
						
			echo "Done\n\n";
			
		}
	}	
	
	protected function actionSetAllowed($params){
		
		$this->requireCli();
		
		$this->checkRequiredParameters(array('module'), $params);
		
		if(!isset($params['allow'])){
			exit("--allow is required");
		}
		
//		if(!empty($allow)){
//			exit("--allow is required");
//		}
		
		$allow = !empty($params['allow']);	
		
		$stmt = \GO\Servermanager\Model\Installation::model()->find();
		while($installation = $stmt->fetch()){
			echo "Setting ".$installation->name."\n";
			$c = $installation->getConfigWithGlobals();
			if($c && !empty($c['allowed_modules'])){
				$allowed = explode(',',$c['allowed_modules']);
				$newAllowed = array();
				
				if(!$allow){					
					foreach($allowed as $module){
						if($module!=$params['module'])
							$newAllowed[]=$module;
					}
				}else
				{
					$allowed[]=$params['module'];
					$newAllowed = array_unique($allowed);
				}
				
				$installation->setConfigVariable('allowed_modules',implode(',',$newAllowed));
			}		
		}
	}
	
	protected function actionSetConfigValue($params){
		$this->requireCli();
		$this->checkRequiredParameters(array('name'), $params);
		
		if(!isset($params['value']))
			throw new Exception("Parameter value is required");
		
		$stmt = \GO\Servermanager\Model\Installation::model()->find();
		while($installation = $stmt->fetch()){
			echo "Setting ".$installation->name." config parameter ".$params['name'].'='.$params['value']."\n";
			
			$set=true;
			if(isset($params['if'])){
				
				$config = $installation->getConfigWithGlobals();
				
				if(!isset($config[$params['name']]) || $config[$params['name']]!=$params['if']){
					$set = false;
				}
			}
			if($set)
				$installation->setConfigVariable($params['name'],$params['value']);
		}	
		
		echo "All done!\n";
	}
	
	protected function actionExecuteQuery($params){
		$this->requireCli();
		$this->checkRequiredParameters(array('query'), $params);
		
		$stmt = \GO\Servermanager\Model\Installation::model()->find();
		while($installation = $stmt->fetch()){
			echo "Running query on ".$installation->name."\n";
			try{
				$installation->executeQuery($params['query']);
			}catch(Exception $e){
				echo "Error: ".$e->getMessage()."\n";
			}
		}	
		
		echo "All done!\n";
	}
	
	
	protected function actionRemoveSuspendedAndUnused($params){
		
		if(!$this->isCli())
			throw new Exception("This action may only be ran on the command line.");
		
		//unused for two months
		$lastlogin = \GO\Base\Util\Date::date_add(time(), 0, -2);
		
		
		$fp = \GO\Base\Db\FindParams::newInstance();
		$fp->getCriteria()->addCondition('lastlogin', $lastlogin,'<')->addCondition('lastlogin', null, 'IS','t',false);
		
		$count=0;
		
		$stmt = \GO\Servermanager\Model\Installation::model()->find($fp);
		while($installation = $stmt->fetch()){
			echo "Deleting ".$installation->name."\n";
			
			if(!empty($params['really']))
				$installation->delete();
			
			$count++;
		}
		
		echo "Deleted ".$count." trials\n\n";
		
		echo "Done\n\n";
	}
	
	
	/**
	 * This will test the connection with the billing module
	 * Will return succes when connection succeeds
	 * @param array $parmas the $_REQUEST object 
	 */
	protected function actionTestBilling($parmas){
		
		$response = array(
				'success'=>\GO\ServerManager\Model\AutomaticInvoice::canConnect()
		);
		return $response;
	}
	
	/**
	 * This action will be called by a cronjob that runs daily
	 * Loop through all installations see if config file excists and is enabled
	 * Check if (trail) installation is expired if not execute maintenance/servermanagerReport
	 * Send automatic email for every installation
	 * TODO: Send reports for automatic invoicing
	 * @param array $params content of $_REQUEST (empty)
	 * @throws Exception When this action is not called from Commandline
	 */
	protected function actionReport($params){
		
		if(!$this->isCli())
			throw new Exception("You may only run this command on the command line");
		
		$report = array(
				'installations'=>array(),
				'id'=>\GO::config()->id,
				'hostname'=>getHostName(),
				'ip'=>  gethostbyname(getHostName()),
				'name'=>\GO::config()->title,
				'version'=>\GO::config()->version,
				'uname'=>  php_uname(),
				'moduleCounts'=>array()
		);

		
		$installations = empty($params['installation']) ? \GO\ServerManager\Model\Installation::model()->find()->fetchAll() : \GO\ServerManager\Model\Installation::model()->findByAttribute('name',$params['installation'])->fetchAll();
		foreach($installations as $installation)
		{			
			try {
				//check if installation is expired and suspend if so
				if($installation->isExpired){
					if($installation->delete())
						echo "Installation ".$installation->name." was deleted\n";
					
					continue;
				}

			
				if(!$installation->config){
					echo "Config file does not exist for ".$installation->name."\n";
					continue;
				}
				if(isset($installation->config['enabled']) && $installation->config['enabled']==false) {
					echo "Installation ".$installation->name." is suspended\n";
					continue;
				}

				echo "Creating report for ".$installation->name."\n";

			
				if($installation->loadUsageData()){
					
					$report['installations'][]=array_merge($installation->getAttributes(), $installation->getHistoryAttributes());
				}else{
					echo "Unable to fetch data for ".$installation->name."\n";
				}

				
				
				if($installation->save())
					echo "Installation was updated\n";
				else
					echo "ERROR: failed to save new installation information\n";
				
				
				
				
			
			
			if(!$installation->isSuspended)
			{
				//run tasks for installation like log rotation and filesearch index update.
				echo "Running daily tasks for installation\n";
				$cmd ='/usr/share/groupoffice/groupofficecli.php -q -r=maintenance/servermanagerReport -c="'.$installation->configPath.'"  2>&1';				
				system($cmd);
			}

			$installation->sendAutomaticEmails();

			
				
			//send automatic invoices if enabled
//			if(!empty($installation->automaticInvoice) && $installation->automaticInvoice->enable_invoicing && $installation->automaticInvoice->shouldCreateOrder())
//			{	
//				if($installation->automaticInvoice->sendOrder())
//					echo "Order was posted to billing successfull\n";
//				else
//					echo "ERROR: Failed sending order to billing\n";
//			}
			
			}catch(Exception $e){
				echo "ERROR:\n";
				echo $e->getMessage()."\n";
				$report['errors']=(string) $e;
			}
		}
		
		$findParams = \GO\Base\Db\FindParams::newInstance()
						->select('name, count(name) AS count')
						->group('name');
		
		$stmt = \GO\ServerManager\Model\InstallationModule::model()->find($findParams);
		foreach($stmt as $module){
			$report['moduleCounts'][$module->name]=intval($module->count);
		}
		
//		var_dump($report);
		
		//$report['moduleCounts']=
		

//		if(class_exists('GO\Professional\LicenseCheck')){
//			
//			if(!isset(\GO::config()->license_name)){
//				throw new Exception('$config["license_name"] is not set. Please contact Intermesh to get your key.');
//			}
//			
//			$report['license_name']=\GO::config()->license_name;
//		}

		//Post the report to intermesh
//		if(class_exists('GO\Professional\LicenseCheck')){
//			$c = new \GO\Base\Util\HttpClient();
//			$url = 'https://intermesh.group-office.com/index.php?r=licenses/server/report';
////			$url = 'http://intermesh.intermesh.dev/index.php?r=licenses/server/report';
//			$response = $c->request($url, array(
//					'report'=>json_encode($report)
//			));
//
//			$response = json_decode($response, true);
//
//			if($response['success'])
//				echo "Report was sent to Intermesh\n";
//			else{
//				echo "ERROR: sending report to Intermesh\n";
//				var_dump($response);
//			}
//		}

		if(!empty(\GO::config()->servermanager_send_report_to)) {
			$message = new \GO\Base\Mail\Message();
			$message->setSubject("Servermanager report for ". $report['hostname']);

			$message->setBody(json_encode($report),'text/plain');
			$message->setFrom(\GO::config()->webmaster_email,"Servermanager");
			$message->addTo(\GO::config()->servermanager_send_report_to);

			\GO\Base\Mail\Mailer::newGoInstance()->send($message);
		}
				
		
		echo "Done\n\n";
	}
	
	/**
	 * Save the remote invoice connection parameters with database and try to connection
	 * @param array $params the $_REQUEST object
	 * @return boolean true if the connection was established with the remote host
	 */
	public function actionRemoteInvoiceConnection($params)
	{
		//Set the settings from params
		/*
		\GO::config()->save_setting('servermanager_invoice_host', $params['remote_invoice_host']);
		\GO::config()->save_setting('servermanager_invoice_username', $params['remote_invoice_username']);
		\GO::config()->save_setting('servermanager_invoice_password', $params['remote_invoice_password']);
		*/
		
		$response['success'] = \GO\ServerManager\Model\AutomaticInvoice::canConnect();
		return $response;
	}
	
	/**
	 * Returns a list of InstallationUsers's to display on the usage tab of an installation
	 * @param type $params 
	 */
	public function actionUsersStore($params)
	{
		$cm =  new \GO\Base\Data\ColumnModel();
		$cm->setColumnsFromModel(\GO\ServerManager\Model\InstallationUser::model());
		$cm->formatColumn('trialDaysLeft','$model->trialDaysLeft');
		
		$store = new \GO\Base\Data\Store($cm);
		$storeParams = $store->getDefaultParams($params);
		$storeParams = $storeParams->select('t.*'); //makes sure field of type TEXT get loaded
		$criteria = \GO\Base\Db\FindCriteria::newInstance()->addCondition('installation_id', $params['installation_id']);
		$storeParams->mergeWith(\GO\Base\Db\FindParams::newInstance()->criteria($criteria));
		$store->setStatement(\GO\ServerManager\Model\InstallationUser::model()->find($storeParams));
		
		$response=array("success"=>true,"results"=>array());
		$response = array_merge($response, $store->getData());
		
		return $response;
	}
	
	public function actionHistoryStore($params)
	{
		$cm =  new \GO\Base\Data\ColumnModel();
		$cm->setColumnsFromModel(\GO\ServerManager\Model\UsageHistory::model());
		$cm->formatColumn('total_usage', '$model->totalUsageText');
		$cm->formatColumn('mailbox_usage', '$model->mailboxUsageText');
		$cm->formatColumn('database_usage', '$model->databaseUsageText');
		$cm->formatColumn('file_storage_usage', '$model->fileStorageUsageText');

		$store = new \GO\Base\Data\Store($cm);
		$storeParams = $store->getDefaultParams($params);
		$criteria = \GO\Base\Db\FindCriteria::newInstance()->addCondition('installation_id', $params['installation_id']);
		$storeParams->mergeWith(\GO\Base\Db\FindParams::newInstance()->criteria($criteria));
		$store->setStatement(\GO\ServerManager\Model\UsageHistory::model()->find($storeParams));
		
		$response=array("success"=>true,"results"=>array());
		$response = array_merge($response, $store->getData());
		
		return $response;
	}
	
	
	public function actionEmailAddresses() {
		
		$installations = GO\ServerManager\Model\Installation::model()->find();
		
		$addresses = new \GO\Base\Mail\EmailRecipients();
		
		
		foreach($installations as $installation) {
			if(!$installation->getConfigWithGlobals()->disabled) {
				$addresses->addRecipient($installation->admin_email, $installation->admin_name);
			}
		}
		
		echo $addresses;
		
	}
	
	

}