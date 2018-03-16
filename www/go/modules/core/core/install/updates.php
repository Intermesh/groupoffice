<?php
$updates["201803090847"][] = "ALTER TABLE `go_log` ADD `jsonData` TEXT NULL AFTER `message`;";

$updates["201803161130"][] = function() {
	$globalConfig = [];
	if(file_exists('/etc/groupoffice/globalconfig.inc.php')) {
		require('/etc/groupoffice/globalconfig.inc.php');
		$globalConfig = $config;
	}
	
	$configFile = go\core\App::findConfigFile('config.php');
	
	require($configFile);
	
	$config = array_merge($globalConfig, $config);
	
	$values = [
			'title' => 'title', 
			'language' => 'language',
			'webmaster_email' => 'systemEmail',
			'smtp_host' => 'smtpHost',
			'smtp_port' => 'smtpPort',
			'smtp_username' => 'smtpUsername',
			'smtp_password' => 'smtpPassword',
			'smtp_encryption' => 'smtpEncryption'			
			];

	foreach($values as $old => $new) {
		if(empty($config[$old])) {
			continue;
		}
		$sql = "replace into core_setting select id as moduleId, '".$new."' as name, :value as value from core_module where name='core'";
		$stmt = GO()->getDbConnection()->getPDO()->prepare($sql);
		$stmt->bindValue(":value", $config[$old]);
		$stmt->execute();
	}
};