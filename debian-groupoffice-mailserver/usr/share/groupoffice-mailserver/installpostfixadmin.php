#!/usr/bin/php
<?php
require('/etc/groupoffice/config.php');
require($config['root_path'].'GO.php');

\GO::setIgnoreAclPermissions();

try{	
	if(!\GO::modules()->isInstalled('postfixadmin')){
		$module = new \GO\Base\Model\Module();
		$module->id = 'postfixadmin';
		$module->save();
	}	
	
	if(!\GO::modules()->isInstalled('serverclient')){
		$module = new \GO\Base\Model\Module();
		$module->id = 'serverclient';
		$module->save();
	}	
}
catch(Exception $e){
	echo 'ERROR: '.$e->getMessage();
}

